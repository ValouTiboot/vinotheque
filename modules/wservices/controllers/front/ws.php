<?php

ini_set('default_socket_timeout', -1);
@ini_set('display_errors', 'on');
error_reporting(E_ALL);

require_once(_PS_MODULE_DIR_ . 'wservices/classes/RedisConnect.php');

use PrestaShop\PrestaShop\Adapter\ServiceLocator;

class WserviceswsModuleFrontController extends ModuleFrontController
{
    public $images_url = 'http://pre.vinotheque-bordeaux.com/ftp/Images/';

    public function initContent()
    {
        parent::initContent();

        if (Tools::getIsset('action') && Tools::getValue('action') == 'get_customers')
            $this->getCustomers();
        else if (Tools::getIsset('action') && Tools::getValue('action') == 'get_product')
            $this->getProductById(Tools::getValue('id_product'));
        else if (Tools::getIsset('action') && Tools::getValue('action') == 'get_order')
            $this->getOrderById(Tools::getValue('id_order'));
        else if (Tools::getIsset('action') && Tools::getValue('action') == 'get_cart_rule')
            $this->getCartRuleById(Tools::getValue('id_order'));
        else if (Tools::getIsset('action') && Tools::getValue('action') == 'get_redis_datas')
            $this->getRedisDatasCron();

        $this->receiver();
    }

    public function getRedisDatasCron()
    {
        $channels = array('mt:CM_Site1_CLT', 'mt:CM_Site1_PRD', 'mt:CM_Site1_TRF', 'mt:CM_Site1_STK', 'mt:CM_Site1_RCM');

        $url = 'https://pre.vinotheque-bordeaux.com/index.php?fc=module&module=wservices&controller=ws';

        $redis_connect = new RedisConnect();
        $redis = $redis_connect->connect();

        foreach($channels as $channel)
        {
            $list = $redis->zRange($channel, 0, 200);
            if (!empty($list))
            {
                foreach ($list as $json)
                {
                    $data = array('data' => $json);

                    $data_array = json_decode($json, true);

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                    curl_exec($ch);
                    curl_close($ch);

                    $redis->zDeleteRangeByScore($channel, $data_array['NoJSON'], $data_array['NoJSON']);
                }
            }
        }

        $redis->close();
        die();
    }

    public function getCustomers()
    {
        $customers = Customer::getCustomers();
        foreach ($customers as &$customer)
        {
            // Get customer's informations
            $current_customer = new Customer($customer['id_customer']);

            // START Get customer's loyalty //
            $customer['NbPointsConsommés'] = 0;
            $customer['NbPointsAcquits'] = 0;
            $customer['NbPointsRestants'] = 0;
            $customer['loyalty'] = $this->getCustomerLoyalties($customer['id_customer']);

            if (isset($customer['loyalty']) && !empty($customer['loyalty']))
            {
                // 1 = En attente de validation
                // 2 = Disponible
                // 3 = Annulés
                // 4 = Déjà convertis
                // 5 = Non disponbile sur produits remisés
                foreach ($customer['loyalty'] as $key => $value) {
                    if ($value['id_loyalty_state'] == 4)
                        $customer['NbPointsConsommés'] += $value['points'];

                    if ($value['id_loyalty_state'] == 2 || $value['id_loyalty_state'] == 4)
                        $customer['NbPointsAcquits'] += $value['points'];
                }
                $customer['NbPointsRestants'] = $customer['NbPointsAcquits'] - $customer['NbPointsConsommés'];
            }
            unset($customer['loyalty']);
            // END Get customer's loyalty //

            // Get customer's addresses
            $customer['addresses'] = $current_customer->getSimpleAddresses(Context::getContext()->language->id);
        }

        echo json_encode($customers, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function getCustomerLoyalties($id_customer)
    {
        $query = '
        SELECT f.id_order AS id, f.date_add AS date, (o.total_paid - o.total_shipping) total_without_shipping, f.points, f.id_loyalty, f.id_loyalty_state, fsl.name state
        FROM `'._DB_PREFIX_.'totloyalty` f
        LEFT JOIN `'._DB_PREFIX_.'orders` o ON (f.id_order = o.id_order)
        LEFT JOIN `'._DB_PREFIX_.'totloyalty_state_lang` fsl ON (f.id_loyalty_state = fsl.id_loyalty_state)
        WHERE f.id_customer = '.(int)($id_customer);

        $query .= ' GROUP BY f.id_loyalty ';

        return Db::getInstance()->executeS($query);
    }

    public function getProductById($id_product)
    {
        $product = Db::getInstance()->getRow("
            SELECT 
                P.*, PL.`name`, PL.`description`, PL.`description_short`
            FROM `" . _DB_PREFIX_ . "product` P
            LEFT JOIN `" . _DB_PREFIX_ . "product_lang` PL ON P.`id_product`=PL.`id_product`
            WHERE P.`id_product`='" . pSQL($id_product) . "' AND PL.`id_lang`='" . pSQL(Context::getContext()->language->id) . "'
        ");

        $default_category = new Category($product['id_category_default']);
        $product['id_category_default'] = $default_category->name[Context::getContext()->language->id];

        $product['categories'] = array();

        $categories = Product::getProductCategoriesFull($id_product);
        foreach ($categories as $category)
            $product['categories'][$category['id_category']] = $category['name'];

        $pictogram = Db::getInstance()->executeS("SELECT P.`slug` FROM `" . _DB_PREFIX_ . "pictogram` P LEFT JOIN `" . _DB_PREFIX_ . "pictogram_product` PP ON PP.`id_pictogram`=P.`id_pictogram` WHERE PP.`id_product`='" . pSQL($id_product) . "';");

        $product['pictogram'] = array();
        foreach ($pictogram as $picto)
            $product['pictogram'][] = $picto['slug'];

        $foodandwine = Db::getInstance()->executeS("SELECT F.`slug` FROM `" . _DB_PREFIX_ . "foodandwine` F LEFT JOIN `" . _DB_PREFIX_ . "foodandwine_product` PP ON PP.`id_foodandwine`=F.`id_foodandwine` WHERE PP.`id_product`='" . pSQL($id_product) . "';");

        $product['foodandwine'] = array();
        foreach ($foodandwine as $food)
            $product['foodandwine'][] = $food['slug'];

        $product['attributes'] = Db::getInstance()->executeS("SELECT * FROM `" . _DB_PREFIX_ . "product_attribute` WHERE `id_product`='" . pSQL(Tools::getValue('id_product')) . "' ORDER BY `id_product_attribute`");

        foreach ($product['attributes'] as &$product_attribute)
        {
            $name = '';
            $product_attribute['default_on'] = is_null($product_attribute['default_on']) ? '0' : $product_attribute['default_on'];

            $combinations = Db::getInstance()->executeS("SELECT `id_attribute` FROM `" . _DB_PREFIX_ . "product_attribute_combination` WHERE `id_product_attribute`='" . pSQL($product_attribute['id_product_attribute']) . "'");

            foreach ($combinations as $id_attribute)
            {
                $name .= Db::getInstance()->getValue("SELECT AGL.`name` FROM `" . _DB_PREFIX_ . "attribute_group_lang` AGL LEFT JOIN `" . _DB_PREFIX_ . "attribute` A ON A.`id_attribute_group`=AGL.`id_attribute_group` WHERE A.`id_attribute`='" . pSQL($id_attribute['id_attribute']) . "'") . ' : ';
                $name .= Db::getInstance()->getValue("SELECT `name` FROM `" . _DB_PREFIX_ . "attribute_lang` WHERE `id_attribute`='" . pSQL($id_attribute['id_attribute']) . "' AND `id_lang`='1'") . ' - ';
            }

            $product_attribute['name'] = substr($name, 0, -3);
        }

        $features = Db::getInstance()->executeS("
            SELECT
                FL.`name`, FVL.`value`
            FROM `" . _DB_PREFIX_ . "feature_product` FP
            LEFT JOIN `" . _DB_PREFIX_ . "feature_lang` FL ON (FL.`id_feature`=FP.`id_feature` AND FL.`id_lang`='1')
            LEFT JOIN `" . _DB_PREFIX_ . "feature_value_lang` FVL ON (FVL.`id_feature_value`=FP.`id_feature_value` AND FVL.`id_lang`='1')
            WHERE FP.`id_product`='" . pSQL($product['id_product']) . "'
        ");

        $product['features'] = array();
        foreach ($features as $key => $feature)
        {
            $product['features'][$key]['feature'] = $feature['name'];
            $product['features'][$key]['value'] = $feature['value'];
        }

        $product['specific_price'] = Db::getInstance()->executeS("SELECT * FROM `" . _DB_PREFIX_ . "specific_price` WHERE `id_product`='" . pSQL(Tools::getValue('id_product')) . "'");

        echo json_encode($product, JSON_UNESCAPED_UNICODE);
        file_put_contents('product.txt', json_encode($product, JSON_UNESCAPED_UNICODE));
        die();
    }

    public function getOrderById($id_order)
    {
        // Instance of DateTime object
        $date_v = new DateTime();

        // START GET ORDER //
        // Build QUERY
        $sql = new DbQuery();
        // SELECT
        $sql->select(
            // *** Entête de commande *** //
            "O.`id_order` as NoCommande,
            O.`reference` as RefPrestashop,
            O.`id_order_dubos` as IdOrderDubos,
            O.`date_add` as DateCommande," .
            // CD.`id_customer` as IdCustomer,
            "CD.`id_customer_dubos` as NoClientLivre,
            CONCAT(CD.`lastname`, ' ', CD.`firstname`) as NomClientLivre,
            AD.`company` as NomPointRelai,
            AD.`id_address_dubos` as NoAdresseClientLivre,
            AD.`phone` as NoTelephone1ClientLivre,
            AD.`phone_mobile` as NoTelephone2ClientLivre,
            AD.`address1` as Adresse1ClientLivre,
            AD.`address2` as Adresse2ClientLivre,
            AD.`postcode` as CodePostalClientLivre,
            AD.`city` as VilleClientLivre,
            CO.`iso_code` as PaysClientLivre,
            CD.`email` as EmailContactLivre,
            CI.`id_customer_dubos` as NoClientFacture,
            CI.`email` as EmailContactFacture,
            CONCAT(CI.`lastname`, ' ', CI.`firstname`) as NomClientFacture,
            AI.`id_address_dubos` as NoAdresseClientFacture,
            AI.`phone` as NoTelephone1ClientFacture,
            AI.`phone_mobile` as NoTelephone2ClientFacture,
            AI.`address1` as Adresse1ClientFacture,
            AI.`address2` as Adresse2ClientFacture,
            AI.`postcode` as CodePostalClientFacture,
            AI.`city` as VilleClientFacture,
            COI.`iso_code` as PaysClientFacture,
            O.`gift` as EstCadeau,
            CU.`iso_code` as CodeDevise,
            CO.`iso_code` as CodePays,
            Z.`code_dubos` as CodeRegimeTaxe,
            O.`gift_message` as CommentairesCadeau," .
            // commentaireexpedition as CommentairesExpedition,
            "OS.`id_order_state` as EtatCommande,
            O.`total_paid_tax_excl` as MttTotalHT,
            O.`total_paid_tax_incl` as MttTotalTTC,
            O.`total_products` as MttTotalMarchandiseHT," .
            // O.`total_products_wt` as MttTotalMarchandiseTTC,
            // O.`id_cart` as IdCart

            // *** Point relais chronopost *** //
            // CodePointRelais
            // Adresse1PointRelais
            // Adresse2PointRelais
            // CodePostalPointRelais
            // VillePointRelais

            // *** Transport *** //
            "OC.`id_carrier` as CodeTransporteur,
            AD.`other` as CodePointRelai,
            TRG.`code` as CodeNiveauTaxe,
            OC.`shipping_cost_tax_excl` as MontantFraisPortHT,
            OC.`shipping_cost_tax_incl` as MontantFraisPortTTC," .
            // O.`delivery_date` as DateLivraisonPrevue,

            // *** Paiement *** //
            "O.`module` as CodeModePaiement, " .
            /* case
                WHEN O.`payment` = 'Payment by check' OR O.`payment` = 'Chèque' OR O.`payment` = 'Bank wire'
                THEN ''
                ELSE OP.`date_add`
            end as DatePaiement, */
            "O.`date_add` as DatePaiement,
            (SELECT SUM(PL.`points`) FROM `ps_totloyalty` PL HAVING PL.`id_customer`=NoClientLivre) as NbPointFidelite,
            O.`total_paid` as MttRegleTTC,
            O.`current_state` as CurrentState," .

            // *** Remises *** //
            // CR.`reduction_percent` as TauxRemise,
            // CodeNiveauTaxe ???
            // MontantBaseRemiseHT ???
            "O.`total_discounts_tax_excl` as MontantRemiseHT,
            O.`total_discounts_tax_incl` as MontantRemiseTTC
        ");
        // FROM
        $sql->from('orders', 'O');
        // JOIN
        $sql->leftJoin('address', 'AD', 'O.`id_address_delivery`=AD.`id_address`');
        $sql->leftJoin('customer', 'CD', 'AD.`id_customer`=CD.`id_customer`');
        $sql->leftJoin('address', 'AI', 'O.`id_address_invoice`=AI.`id_address`');
        $sql->leftJoin('customer', 'CI', 'AI.`id_customer`=CI.`id_customer`');
        $sql->leftJoin('currency', 'CU', 'CU.`id_currency`=O.`id_currency`');
        $sql->leftJoin('country', 'CO', 'CO.`id_country`=AD.`id_country`');
        $sql->leftJoin('zone', 'Z', 'Z.`id_zone`=CO.`id_zone`');
        $sql->leftJoin('country', 'COI', 'COI.`id_country`=AI.`id_country`');
        $sql->leftJoin('order_state_lang', 'OS', 'OS.`id_order_state`=O.`current_state`');
        $sql->leftJoin('order_carrier', 'OC', 'OC.`id_order`=O.`id_order`');
        $sql->leftJoin('carrier_tax_rules_group_shop', 'CTRG', 'CTRG.`id_carrier`=OC.`id_carrier`');
        $sql->leftJoin('tax_rules_group', 'TRG', 'TRG.`id_tax_rules_group`=CTRG.`id_tax_rules_group`');
        $sql->leftJoin('order_payment', 'OP', 'OP.`order_reference`=O.`reference`');
        $sql->leftJoin('totloyalty', 'PL', 'PL.`id_order`=O.`id_order`');
        // WHERE
        $sql->where('O.`id_order` = ' . pSQL($id_order));

        $order = [];
        $order_array = Db::getInstance()->ExecuteS($sql);

        if (empty($order_array))
            die("Order not found");

        foreach ($order_array as $value)
        {
            /** GET CUSTOMER's PRESTASHOP ID **/
            // $order['IdCustomer'] = $value['IdCustomer'];

            /** COMMANDE **/
            $num_commande = $value['NoCommande'];
            $order['commande'][$num_commande]['RefPrestashop'] = $value['RefPrestashop'];
            $order['commande'][$num_commande]['NoCommande'] = $value['IdOrderDubos'];
            $order['commande'][$num_commande]['DateCommande'] = $value['DateCommande'];
            $order['commande'][$num_commande]['CodeTransporteur'] =$value['CodeTransporteur'];
            $order['commande'][$num_commande]['NoClientLivre'] = ($value['NoClientLivre']) ? $value['NoClientLivre'] : $value['NoClientFacture'];
            $order['commande'][$num_commande]['NomClientLivre'] = ($value['NomClientLivre']) ? $value['NomClientLivre'] : $value['NomPointRelai'];
            $order['commande'][$num_commande]['NomContactClientLivre'] = ($value['NomClientLivre']) ? $value['NomClientLivre'] : $value['NomClientFacture'];
            $order['commande'][$num_commande]['EmailContactLivre'] = ($value['EmailContactLivre']) ? $value['EmailContactLivre'] : $value['EmailContactFacture'];
            $order['commande'][$num_commande]['NoTelephone1ClientLivre'] = $value['NoTelephone1ClientLivre'];
            $order['commande'][$num_commande]['NoTelephone2ClientLivre'] = $value['NoTelephone2ClientLivre'];
            $order['commande'][$num_commande]['NoAdresseClientLivre'] = $value['NoAdresseClientLivre'];
            $order['commande'][$num_commande]['Adresse1ClientLivre'] = $value['Adresse1ClientLivre'];
            $order['commande'][$num_commande]['Adresse2ClientLivre'] = $value['Adresse2ClientLivre'];
            $order['commande'][$num_commande]['Adresse3ClientLivre'] = '';
            $order['commande'][$num_commande]['CodePostalClientLivre'] = $value['CodePostalClientLivre'];
            $order['commande'][$num_commande]['VilleClientLivre'] = $value['VilleClientLivre'];
            $order['commande'][$num_commande]['PaysClientLivre'] = $value['PaysClientLivre'];
            $order['commande'][$num_commande]['CodeRegimeTaxe'] = ($value['PaysClientFacture'] == 'FR') ? 'FRA' : $value['CodeRegimeTaxe'];
            $order['commande'][$num_commande]['NoClientFacture'] = $value['NoClientFacture'];
            $order['commande'][$num_commande]['NomClientFacture'] = $value['NomClientFacture'];
            $order['commande'][$num_commande]['NoAdresseClientFacture'] = $value['NoAdresseClientFacture'];
            $order['commande'][$num_commande]['NomContactClientFacture'] = $value['NomClientFacture'];
            $order['commande'][$num_commande]['NoTelephone1ClientFacture'] = $value['NoTelephone1ClientFacture'];
            $order['commande'][$num_commande]['NoTelephone2ClientFacture'] = $value['NoTelephone2ClientFacture'];
            $order['commande'][$num_commande]['Adresse1ClientFacture'] = $value['Adresse1ClientFacture'];
            $order['commande'][$num_commande]['Adresse2ClientFacture'] = $value['Adresse2ClientFacture'];
            $order['commande'][$num_commande]['Adresse3ClientFacture'] = '';
            $order['commande'][$num_commande]['CodePostalClientFacture'] = $value['CodePostalClientFacture'];
            $order['commande'][$num_commande]['VilleClientFacture'] = $value['VilleClientFacture'];
            $order['commande'][$num_commande]['PaysClientFacture'] = $value['PaysClientFacture'];
            $order['commande'][$num_commande]['EstCadeau'] = $value['EstCadeau'];
            $order['commande'][$num_commande]['EstPrimeur'] = '0';
            $order['commande'][$num_commande]['EstCautionBancaire'] = '0';
            $order['commande'][$num_commande]['MttCautionBancaire'] = '0.000000';
            $order['commande'][$num_commande]['CodeDevise'] = $value['CodeDevise'];
            $order['commande'][$num_commande]['CodePays'] = $value['CodePays'];
            $order['commande'][$num_commande]['EtatCommande'] = ($value['EtatCommande']) ? $value['EtatCommande'] : '';
            $order['commande'][$num_commande]['MttTotalMarchandiseHT'] = $value['MttTotalMarchandiseHT'];
            $order['commande'][$num_commande]['MttTotalHT'] = $value['MttTotalHT'];
            $order['commande'][$num_commande]['MttTotalTTC'] = $value['MttTotalTTC'];
            $order['commande'][$num_commande]['CommentairesExpedition'] = ''; // selon le module du transporteur mais pas de champ prévu en front
            $order['commande'][$num_commande]['CommentairesCadeau'] = ($value['CommentairesCadeau']) ? $value['CommentairesCadeau'] : '';

            /** TRANSPORT **/
            $order['commande'][$num_commande]['Transport'] = [];
            $montant_frais_port_ht = $value['MontantFraisPortHT'];
            if ($montant_frais_port_ht > 0)
            {
                $order['commande'][$num_commande]['Transport'][1]['CodeTransporteur'] = ($value['CodeTransporteur']) ? $value['CodeTransporteur'] : '';
                $order['commande'][$num_commande]['Transport'][1]['CodePointRelai'] = ($value['CodePointRelai']) ? $value['CodePointRelai'] : '';
                $order['commande'][$num_commande]['Transport'][1]['CodeNiveauTaxe'] = ($value['CodeNiveauTaxe']) ? $value['CodeNiveauTaxe'] : 'EXO';
                $order['commande'][$num_commande]['Transport'][1]['CodeElement'] = 'P001';
                // $order['commande'][$num_commande]['Transport'][1]['MontantTaxe'] = $value['MontantFraisPortTTC'] - $value['MontantFraisPortHT'];
                // $order['commande'][$num_commande]['Transport'][1]['DateLivraisonPrevue'] = $value['DateLivraisonPrevue'];
                $order['commande'][$num_commande]['Transport'][1]['MttHT'] = ($value['MontantFraisPortHT']) ? $value['MontantFraisPortHT'] : '';
                $order['commande'][$num_commande]['Transport'][1]['MttTTC'] = ($value['MontantFraisPortTTC']) ? $value['MontantFraisPortTTC'] : '';
            }

            /** CHRONOPOST **/
            // $order['commande'][$num_commande]['Chronopost'][1]['CodePointRelai'] = '';
            // $order['commande'][$num_commande]['Chronopost'][1]['Adresse1PointRelai'] = '';
            // $order['commande'][$num_commande]['Chronopost'][1]['Adresse2PointRelai'] = '';
            // $order['commande'][$num_commande]['Chronopost'][1]['CodePostalPointRelai'] = '';
            // $order['commande'][$num_commande]['Chronopost'][1]['VillePointRelai'] = '';

            /** REMISES **/
            $order['commande'][$num_commande]['Remises'] = [];
            if ($value['MontantRemiseHT'] > 0)
            {
                $order['commande'][$num_commande]['Remises'][1]['CodeNiveauTaxe'] = ($value['CodeNiveauTaxe']) ? $value['CodeNiveauTaxe'] : 'NOR';
                // $order['commande'][$num_commande]['Remises'][1]['TauxRemise'] = $value['TauxRemise'];
                // $order['commande'][$num_commande]['Remises'][1]['MontantBaseRemiseHT'] = '';
                $order['commande'][$num_commande]['Remises'][1]['CodeElement'] = 'REMI';
                $order['commande'][$num_commande]['Remises'][1]['MttHTRemise'] = $value['MontantRemiseHT'];
                $order['commande'][$num_commande]['Remises'][1]['MttTTCRemise'] = $value['MontantRemiseTTC'];
                // $order['commande'][$num_commande]['Remises'][1]['MontantTaxe'] = $value['MontantRemiseTTC'] - $value['MontantRemiseHT'];
                // $order['commande'][$num_commande]['Remises'][1]['TauxTaxe'] = '0.2';
                $montantTaxeRemises = $value['MontantRemiseTTC'] - $value['MontantRemiseHT'];
                // $order['commande'][$num_commande]['Remises'][1]['MontantTaxe'] = $value['MontantRemiseTTC'] - $value['MontantRemiseHT'];
                $order['commande'][$num_commande]['Remises'][1]['TauxTaxe'] = strval(number_format($montantTaxeRemises / $value['MontantRemiseHT'], 1));
            }

            /** PAIEMENT **/
            $order['commande'][$num_commande]['Paiement'][1]['EstRegle'] = ($value['CurrentState'] == 2 ) ? '1' : '0';

            if ($value['CodeModePaiement'] == 'ps_checkpayment')
                $order['commande'][$num_commande]['Paiement'][1]['CodeModePaiement'] = '1';
            elseif ($value['CodeModePaiement'] == 'paypal')
                $order['commande'][$num_commande]['Paiement'][1]['CodeModePaiement'] = '2';
            elseif ($value['CodeModePaiement'] == 'stripe_official')
                $order['commande'][$num_commande]['Paiement'][1]['CodeModePaiement'] = '3';
            elseif ($value['CodeModePaiement'] == 'ps_wirepayment')
                $order['commande'][$num_commande]['Paiement'][1]['CodeModePaiement'] = '4';
            
            $array_id_waiting_order_state = array(1,10,11);
            if(in_array($value['CurrentState'], $array_id_waiting_order_state))
                $order['commande'][$num_commande]['Paiement'][1]['DatePaiement'] = ($value['DatePaiement']) ? $value['DatePaiement'] : '';
            
            if($value['DatePaiement'] != "0000-00-00 00:00:00")
                $order['commande'][$num_commande]['Paiement'][1]['DatePaiement'] = $value['DatePaiement'];

            $order['commande'][$num_commande]['Paiement'][1]['MttRegleTTC'] = $value['MttRegleTTC'];
            $order['commande'][$num_commande]['Paiement'][1]['NoCoupon'] = '';
            // $order['commande'][$num_commande]['Paiement'][1]['NoCoupon']['idSynchro'] = $date_v->getTimestamp();
            $order['commande'][$num_commande]['Paiement'][1]['DateEcheance'] = ($value['DatePaiement']) ? $value['DatePaiement'] : '';
            // $order['commande'][$num_commande]['Paiement'][1]['IdChèqueCadeau'] = '';
            // $order['commande'][$num_commande]['Paiement'][1]['NbPointFidélité'] = $value['NbPointFidélité'];
        }
        // END GET ORDER //

        // START GET ORDER CART RULE //
        $sql = "
            SELECT OCL.*, CR.`id_cart_rule`, CR.`code`
            FROM  `" . _DB_PREFIX_ . "order_cart_rule` OCL
            LEFT JOIN `" . _DB_PREFIX_ . "cart_rule` CR ON CR.`id_cart_rule`=OCL.`id_cart_rule`
            WHERE OCL.`id_order` = " . $order_array[0]['NoCommande'] . "
        ";
        $order_cart_rules_array = Db::getInstance()->ExecuteS($sql);

        if (!empty($order_cart_rules_array))
        {
            $product_rules_group = [];
            foreach ($order_cart_rules_array as $key => $order_cart_rule)
            {
                
                $cart_rule_obj = new CartRule($order_cart_rule['id_cart_rule']);
                $order['commande'][$num_commande]['Paiement'][1]['NoCoupon'] = (!is_null($order_cart_rule['code']) ? $order_cart_rule['code'] : '0');
                if (!empty($cart_rule_obj->getProductRuleGroups()))
                {
                    $product_rules_group[$key] = $cart_rule_obj->getProductRuleGroups();

                    foreach ($cart_rule_obj->getProductRuleGroups() as $k => $v)
                    {
                        $product_rules_group[$key][$v['id_product_rule_group']]['cart_rule'] = $order_cart_rule;

                        $sql = "
                            SELECT pr.`type` 
                            FROM " . _DB_PREFIX_ . "cart_rule_product_rule pr 
                            WHERE pr.id_product_rule_group = " . $v['id_product_rule_group'];

                        $product_rule_type = Db::getInstance()->ExecuteS($sql);
                        $product_rules_group[$key][$v['id_product_rule_group']]['type'] = $product_rule_type[0]['type'];
                    }
                }
                else
                    $order['commande'][$num_commande]['Paiement'][1]['NoCoupon'] = (!is_null($order_cart_rule['code']) ? $order_cart_rule['code'] : '0');
                    // $order['commande'][$num_commande]['Paiement'][1]['NoCoupon'] = $order_cart_rule['code'];
            }
        }
        $order['commande'][$num_commande]['Paiement'][1]['NoCoupon'] = '0';
        // END GET ORDER CART RULE //

        // START GET ORDER DETAILS //
        $sql = "
            SELECT OD.*, P.`wine`, P.`id_product` AS ID_PRODUCT, OD.`id_order_detail_dubos` as NoLigne, PA.`id_product_attribute_dubos` as CodeArticle, PA.`id_packaging` as CodeConditionnement, PA.`packaging_price` as PrixConditionnement, TRG.`code` as CodeNiveauTaxe
            FROM `" . _DB_PREFIX_ . "order_detail` as OD
            INNER JOIN `" . _DB_PREFIX_ . "product` P ON P.`id_product`=OD.`product_id`
            LEFT JOIN `" . _DB_PREFIX_ . "product_attribute` PA ON PA.`id_product_attribute`=OD.`product_attribute_id`
            LEFT JOIN `" . _DB_PREFIX_ . "tax_rules_group` TRG ON TRG.`id_tax_rules_group`=OD.`id_tax_rules_group`
            WHERE OD.`id_order`='" . pSQL($id_order) . "' 
        ";

        $order_details = Db::getInstance()->ExecuteS($sql);

        $primeur = false;
        foreach ($order_details as $key => $value)
        {
            if ($value['wine'])
                $primeur = true;
            else
                $primeur = false;

            /** MARCHANDISE **/
            $order['commande'][$num_commande]['Marchandise'][$value['NoLigne']]['NoCommande'] = $num_commande;
            $order['commande'][$num_commande]['Marchandise'][$value['NoLigne']]['NoLigne'] = $value['NoLigne'];
            // $order['commande']['Marchandise'][$value['NoLigne']]['CodeRegimeTaxe'] = $order['commande']['CodeRegimeTaxe'];
            $order['commande'][$num_commande]['Marchandise'][$value['NoLigne']]['CodeNiveauTaxe'] = $value['CodeNiveauTaxe'];
            $order['commande'][$num_commande]['Marchandise'][$value['NoLigne']]['TauxTaxe'] = ($primeur) ? '0' : '0.2';
            $order['commande'][$num_commande]['Marchandise'][$value['NoLigne']]['CodeArticle'] = $value['CodeArticle'];
            $order['commande'][$num_commande]['Marchandise'][$value['NoLigne']]['CodeConditionnement'] = $value['CodeConditionnement'];
            $order['commande'][$num_commande]['Marchandise'][$value['NoLigne']]['PrixConditionnement'] = $value['PrixConditionnement'];
            $order['commande'][$num_commande]['Marchandise'][$value['NoLigne']]['Poids'] = $value['product_weight'];
            $order['commande'][$num_commande]['Marchandise'][$value['NoLigne']]['QteCommandee'] = $value['product_quantity'];
            $order['commande'][$num_commande]['Marchandise'][$value['NoLigne']]['PrixUnitaire'] = $value['unit_price_tax_excl'];
            // $order['commande'][$num_commande]['Marchandise'][$value['NoLigne']]['PrixUnitaireNet'] = $value['unit_price_tax_incl'];
            $order['commande'][$num_commande]['Marchandise'][$value['NoLigne']]['PrixUnitaireNet'] = $value['unit_price_tax_excl'];
            // $order['commande'][$num_commande]['Marchandise'][$value['NoLigne']]['MttHT'] = $value['total_price_tax_excl'];
            $order['commande'][$num_commande]['Marchandise'][$value['NoLigne']]['MttHT'] = strval($order['commande'][$num_commande]['Marchandise'][$value['NoLigne']]['PrixUnitaireNet'] * $value['product_quantity']);
            $order['commande'][$num_commande]['Marchandise'][$value['NoLigne']]['MttTTC'] = $value['total_price_tax_incl'];
            // $unit_price_tax = $value['total_price_tax_incl'] - $value['unit_price_tax_excl'];
            // $order['commande'][$num_commande]['Marchandise'][$value['NoLigne']]['MttTTC'] = strval(($order['commande'][$num_commande]['Marchandise'][$value['NoLigne']]['PrixUnitaireNet'] + $unit_price_tax) * $value['product_quantity']);
            $order['commande'][$num_commande]['Marchandise'][$value['NoLigne']]['NoTarif'] = ''; // TODO (Prestashop n'a pas d'id de tarif)
            // $order['commande'][$num_commande]['Marchandise'][$value['NoLigne']]['NoTarif']['idSynchro'] = $date_v->getTimestamp(); // TODO (Prestashop n'a pas d'id de tarif)

            /** REMISE PAR LIGNE **/
            $product_rule = [];
            // Get product IDs where can apply voucher
            if (!empty($product_rules_group))
                foreach ($product_rules_group as $product_rule_group)
                    if (!empty($product_rule_group))
                        foreach ($product_rule_group as $v)
                            if (isset($v['cart_rule']) && $v['type'] == 'products')
                            {
                                $product_rule = $v['cart_rule'];
                                $product_rule['product_ids'] = $v['product_rules'][key($v['product_rules'])]['values'];
                            }

            // Initialize Remise array
            $order['commande'][$num_commande]['Marchandise'][$value['NoLigne']]['Remise'] = [];

            // Check if voucher is applicated on the product
            if (!empty($product_rule) && in_array($value['ID_PRODUCT'], $product_rule['product_ids']))
            {
                $order['commande'][$num_commande]['Marchandise'][$value['NoLigne']]['Remise'][$value['NoLigne']]['NoLigne'] = $value['NoLigne'];
                $order['commande'][$num_commande]['Marchandise'][$value['NoLigne']]['Remise'][$value['NoLigne']]['CodeNiveauTaxe'] = $value['CodeNiveauTaxe'];
                $order['commande'][$num_commande]['Marchandise'][$value['NoLigne']]['Remise'][$value['NoLigne']]['TauxTaxe'] = ($primeur) ? '0' : '0.2';
                $order['commande'][$num_commande]['Marchandise'][$value['NoLigne']]['Remise'][$value['NoLigne']]['CodeElement'] = 'REMI';
                $order['commande'][$num_commande]['Marchandise'][$value['NoLigne']]['Remise'][$value['NoLigne']]['MontantRemiseHT'] = $product_rule['value_tax_excl'];
                $order['commande'][$num_commande]['Marchandise'][$value['NoLigne']]['Remise'][$value['NoLigne']]['MontantRemiseTTC'] = $product_rule['value'];
                // Remove MtRemise
                $order['commande'][$num_commande]['Remises'][1]['MttHTRemise'] -= $product_rule['value_tax_excl'];
                $order['commande'][$num_commande]['Remises'][1]['MttTTCRemise'] -= $product_rule['value'];
                $order['commande'][$num_commande]['MttTotalMarchandiseHT'] -= $product_rule['value_tax_excl'];
            }
        }
        // END GET ORDER DETAILS //

        // Empty Heading Discount if 0
        if (!empty($order['commande'][$num_commande]['Remises']) && $order['commande'][$num_commande]['Remises'][1]['MttHTRemise'] <= 0)
            $order['commande'][$num_commande]['Remises'] = [];

        // SET IS PRIMEUR
        if ($primeur)
        {
            $order['commande'][$num_commande]['EstPrimeur'] = '1';
            $order['commande'][$num_commande]['CodeRegimeTaxe'] = 'SPT';
        }

        // SET TRANSPORT TAUX TAXE
        if ($montant_frais_port_ht > 0)
            $order['commande'][$num_commande]['Transport'][1]['TauxTaxe'] = ($primeur || $order['commande'][$num_commande]['Transport'][1]['CodeNiveauTaxe'] == 'EXO') ? '0' : '0.2';

        $trans = array(
            'NoJSON'          => mt_rand(),
            'IdTransaction'   => md5(microtime()),
            'Modèle'          => 'CMD',
            'Type'            => 'INS',
            'DateTransaction' => date('Y-m-d H:i:s'),
            'Transaction'     => array(
                $order
            )
        );

        $this->module->add($trans, 'set');

        unset($order['IdCustomer']);

        header('Content-Type: application/json');
        echo json_encode($trans, JSON_PRETTY_PRINT);
        echo json_encode($trans, JSON_UNESCAPED_UNICODE);
        die;
    }

    public function getCartRuleById($id_order)
    {
        // BUILD QUERY
        $sql = new DbQuery();
        // SELECT
        $sql->select("
            CR.`code` as RéférenceInterne,
            CR.`date_add` as DateCoupon,
            O.`date_add` as DateUtilisation,
            TRG.`code` as CodeNiveauTaxe,
            -- CR.`` as CodeTauxTaxe,
            OCR.`tax_rate` as TauxTaxe,
            OCR.`value_tax_excl` as MttHt,
            SUM(OCR.`value` - OCR.`value_tax_excl`) as MttTaxe,
            OCR.`value` as MttTTC,
            TL.`points` as NbPointFidélité,
            C.`email` as CourrielDestinataire,
            O.`gift_message` as MessageDestinataire,
            -- WS.`transaction` as IdSynchroCommande,
            CR.`gift_product` as Type,
            CR.`active` as Statut
        ");

        // FROM
        // $sql->from('cart_rule', 'CR');
        $sql->from('orders', 'O');
        // JOIN
        $sql->leftJoin('order_cart_rule', 'OCR', 'OCR.`id_order`=O.`id_order`');
        $sql->leftJoin('cart_rule', 'CR', 'OCR.`id_cart_rule`=CR.`id_cart_rule`');
        $sql->leftJoin('cart_rule_lang', 'CRL', 'CR.`id_cart_rule`=CRL.`id_cart_rule`');
        // $sql->leftJoin('orders', 'O', 'O.`id_order`=' . $id_order);
        $sql->leftJoin('order_carrier', 'OC', 'OC.`id_order`=O.`id_order`');
        $sql->leftJoin('carrier_tax_rules_group_shop', 'CTRG', 'CTRG.`id_carrier`=OC.`id_carrier`');
        $sql->leftJoin('tax_rules_group', 'TRG', 'TRG.`id_tax_rules_group`=CTRG.`id_tax_rules_group`');
        $sql->leftJoin('totloyalty', 'TL', 'O.`id_order`=TL.`id_order`');
        $sql->leftJoin('customer', 'C', 'O.`id_customer`=C.`id_customer`');
        // WHERE
        $sql->where('O.`id_order`=' . pSQL($id_order));
        $sql->where('CRL.`id_lang`=' . pSQl(Context::getContext()->language->id));
        // EXECUTE QUERY
        $cart_rule = Db::getInstance()->ExecuteS($sql);


        if ($cart_rule[0]['Type'] == 0)
            $cart_rule[0]['Type'] = 'coupon';
        else
            $cart_rule[0]['Type'] = 'gift';

        foreach ($cart_rule[0] as $key => $value)
            if (is_null($value))
                $cart_rule[0][$key] = '';

        // $cart_rule = array(
        //  0 => array(
        //      'RéférenceInterne' => '422X449H',
        //      'DateCoupon' => '2018-07-01 16:00:00',
        //      'DateUtilisation' => '2018-08-01 10:07:11',
        //      'CodeNiveauTaxe' => 'NOR',
        //      // 'CodeTauxTaxe' => '',
        //      'TauxTaxe' => '0.2',
        //      'MttHt' => '0.00',
        //      'MttTaxe' => '0',
        //      'MttTTC' => '0',
        //      'NbPointFidélité' => '39',
        //      'CourrielDestinataire' => 'olivier@yateo.com',
        //      'MessageDestinataire' => '',
        //      'IdSynchroCommande' => '',
        //      'Type' => 'coupon',
        //      'Statut' => '1',
        //  )
        // );

        $trans = array(
            'NoJSON'          => mt_rand(),
            'IdTransaction'   => md5(microtime()),
            'Modèle'          => 'FID',
            'Type'            => 'INS',
            'DateTransaction' => date('Y-m-d H:i:s'),
            'Transaction'     => $cart_rule
        );

        $this->module->add($trans, 'set');

        header('Content-Type: application/json');
        // echo json_encode($trans, JSON_PRETTY_PRINT);
        echo json_encode($trans, JSON_UNESCAPED_UNICODE);
        die;
    }

    public function receiver()
    {
        $err = false;
        $data = '';

        try
        {
            // Prévoir un envoi de mail a wisy et yateo
            if (!isset($_POST['data']) || empty($_POST['data']))
                throw new Exception('Not a valid json string: Le json envoyé est vide');

            $data = json_decode($_POST['data'], true);

            // Prévoir un envoi de mail a wisy et yateo
            if (is_null($data) || $data === false)
                throw new Exception('Not a valid json string: Le json envoyé n\'est pas valide');

            // Prévoir un envoi de mail a wisy et yateo
            if (!isset($data['IdTransaction']) || empty($data['IdTransaction']))
                throw new Exception('Not a valid json string: Le champ IdTransaction est vide');

            // Prévoir un envoi de mail a wisy et yateo
            if ($this->module->TransactionExists($data['IdTransaction']))
                throw new Exception('Erreur: Un json portant le même IdTransaction a déjà été reçus');

            // Get customer
            if ($data['Modèle'] == 'CLT' || $data['Modèle'] == 'CLIENT')
            {
                if ($data['Type'] == 'INS' || $data['Type'] == 'INSERT')
                if (!$this->addCustomer($data['Transaction']))
                    $this->updateCustomer($data['Transaction']);

                if ($data['Type'] == 'UPD' || $data['Type'] == 'UPDATE')
                    $this->updateCustomer($data['Transaction']);
            }

            // Get customer feedback
            // if ($data['Modèle'] == 'RCL')
            //     $this->customerReturn($data['Transaction']);

            // Get product
            if ($data['Modèle'] == 'PRD')
            {
                if ($data['Type'] == 'INS' || $data['Type'] == 'INSERT')
                    $this->saveProduct($data['Transaction']);

                if ($data['Type'] == 'UPD' || $data['Type'] == 'UPDATE')
                    $this->saveProduct($data['Transaction'], true);
            }

            // Get rate
            if ($data['Modèle'] == 'TRF')
                $this->addSpecifiquePrice($data['Transaction']);

            // Get stock
            if ($data['Modèle'] == 'STK')
            {
                if ($data['Type'] == 'INS' || $data['Type'] == 'INSERT')
                    $this->saveStock($data['Transaction']);

                if ($data['Type'] == 'UPD' || $data['Type'] == 'UPDATE')
                    $this->saveStock($data['Transaction'], true);
            }

            // Get order status
            if ($data['Modèle'] == 'RCM')
                $this->saveOrderState($data['Transaction']);
        }
        catch(Exception $e)
        {
            $err = $e->getMessage();
        }

        $this->module->add($data, 'get', $err);
        die();
    }

    public function addCustomer($data)
    {
        foreach ($data[0]['clients'] as $c_key => $c_val) 
        {
            if ($c_key === 0)
                throw new Exception('Not a valid json string: Ajout d\'un crochet sur la balise client');

            if (!Validate::isEmail($c_val['email']))
                throw new Exception('Not a valid json string: Email invalide');

            // Vérifie si le couple email/notiers existe, si oui on met à jour le user sinon le script d'insertion continue
            if (($exists = Db::getInstance()->getValue("SELECT `id_customer` FROM `" . _DB_PREFIX_ . "customer` WHERE `email`='" . pSQL($c_val['email']) . "' AND  `id_customer_dubos`='" . pSQL($c_val['notiers']) . "'")) == true)
                return false;

            $password = $this->module->genPassword();
            $crypto = ServiceLocator::get('\\PrestaShop\\PrestaShop\\Core\\Crypto\\Hashing');

            Db::getInstance()->insert('customer', array(
                    'id_customer_dubos' => $c_val['notiers'],
                    'lastname' => pSQL($c_val['lastname']),
                    'firstname' => pSQL($c_val['firstname']),
                    'email' => pSQL($c_val['email']),
                    'passwd' => pSQL($crypto->hash($password)),
                    'id_gender' => pSQL($c_val['id_gender']),
                    'birthday' => pSQL($c_val['birthday']),
                    'active' => pSQL($c_val['active']),
                    'optin' => 0,
                    'newsletter' => 0,
                    'siret' => pSQL($c_val['siret']),
                    'ape' => pSQL($c_val['ape']),
                    // 'date_add' => date('Y-m-d H:i:s'),
                    'date_add' => pSQL($c_val['date_add']),
                    'date_upd' => date('Y-m-d H:i:s'),
                )
            );

            $id_customer = Db::getInstance()->Insert_ID();

            /////////////////////////
            // Faire envoi de mail //
            // pour l'envoi du mdp //
            /////////////////////////

            if (isset($data[0]['clients'][$c_key]['adresses']) && count($data[0]['clients'][$c_key]['adresses']))
            {
                foreach ($data[0]['clients'][$c_key]['adresses'] as $a_key => $a_val)
                {
                    if ($a_key === 0)
                        throw new Exception('Not a valid json string: Ajout d\'un crochet sur la balise adresses');

                    $id_country = Db::getInstance()->getValue("SELECT `id_country` FROM `" . _DB_PREFIX_ . "country` WHERE `iso_code`='" . pSQL($a_val['id_country']) . "'");
                    $rank = Db::getInstance()->getValue("SELECT `rank` FROM `" . _DB_PREFIX_ . "address` WHERE `id_customer`='" . pSQL($id_customer) . "' ORDER BY `rank` DESC");
                    $rank = ($rank !== false ? (int)$rank+1 : 1);

                    $address = array(
                        'id_address_dubos' => pSQL($a_val['noadresse']),
                        'id_customer' => pSQL($id_customer),
                        'alias' => pSQL($a_val['alias']),
                        'address1' => pSQL($a_val['address1']),
                        'address2' => pSQL($a_val['address2']),
                        'city' => pSQL($a_val['city']),
                        'postcode' => pSQL($a_val['postcode']),
                        'phone' => pSQL($a_val['phone']),
                        'phone_mobile' => pSQL($a_val['phone_mobile']),
                        'company' => pSQL($a_val['company']),
                        'lastname' => pSQL($a_val['lastname']),
                        'firstname' => pSQL($a_val['firstname']),
                        'other' => pSQL($a_val['other']),
                        'active' => pSQL($a_val['active']),
                        'id_country' => pSQL(Db::getInstance()->getValue("SELECT `id_country` FROM `" . _DB_PREFIX_ . "country` WHERE `iso_code`='" . pSQL($a_val['id_country']) . "'")),
                        'date_upd' => date('Y-m-d H:i:s'),
                        'rank' => pSQL($a_val['rank']),
                    );

                    if (($exists = Db::getInstance()->getValue("SELECT `id_address` FROM `" . _DB_PREFIX_ . "address` WHERE `id_customer`='" . pSQL($id_customer) ."' AND `id_address_dubos`='" . pSQL($a_val['noadresse']) . "'")) == false)
                        Db::getInstance()->insert('address', $address);
                    else
                        Db::getInstance()->update('address', $address, "`id_address_dubos`='" . pSQL($a_val['noadresse']) . "'");
                }
            }

            if (isset($c_val['NbPointsConsommes']) && $c_val['NbPointsConsommes'] > 0)
            {
                Db::getInstance()->insert('totloyalty', array(
                        'id_loyalty_state' => pSQL(4),
                        'id_customer' => pSQL($id_customer),
                        'id_order' => pSQL(0),
                        'id_cart_rule' => pSQL(0),
                        'points' => pSQL($c_val['NbPointsConsommes']),
                        'date_add' => date('Y-m-d H:i:s'),
                        'date_upd' => date('Y-m-d H:i:s'),
                    )
                );
            }

            if (isset($c_val['NbPointsAcquits']) && $c_val['NbPointsAcquits'] > 0)
            {
                Db::getInstance()->insert('totloyalty', array(
                        'id_loyalty_state' => pSQL(2),
                        'id_customer' => pSQL($id_customer),
                        'id_order' => pSQL(0),
                        'id_cart_rule' => pSQL(0),
                        'points' => pSQL($c_val['NbPointsAcquits']),
                        'date_add' => date('Y-m-d H:i:s'),
                        'date_upd' => date('Y-m-d H:i:s'),
                    )
                );
            }

        }

        return true;
    }

    public function updateCustomer($data)
    {
        if (isset($data[0]['clients']))
        {
            foreach ($data[0]['clients'] as $c_key => $c_val) 
            {
                if ($c_key === 0)
                    throw new Exception('Not a valid json string: Ajout d\'un crochet sur la balise client');

                // if (Customer::customerExists($c_val['email']) === 'Invalid email')
                // {
                //  $this->module->add($data, 'get', 'Invalid email.');
                //  die;
                // }

                // if (!Customer::customerExists($c_val['email']))
                //  $this->addCustomer($data);

                // Vérifie si l'email reçus est valide
                if (!Validate::isEmail($c_val['email']))
                    throw new Exception('Not a valid json string: Email invalide');

                // Vérifie si le couple email/notiers existe, si non on crée le user sinon le script de mise à jour continue
                if (($exists = Db::getInstance()->getValue("SELECT `id_customer` FROM `" . _DB_PREFIX_ . "customer` WHERE `email`='" . pSQL($c_val['email']) . "' AND  `id_customer_dubos`='" . pSQL($c_val['notiers']) . "'")) == false)
                    $this->addCustomer($data);

                if ($c_val['active'] == '9')
                    return Db::getInstance()->delete('customer', "`id_customer_dubos`='" . pSQL($c_val['notiers']) . "'");

                Db::getInstance()->update('customer', array(
                        'lastname' => pSQL($c_val['lastname']),
                        'firstname' => pSQL($c_val['firstname']),
                        'email' => pSQL($c_val['email']),
                        'id_gender' => pSQL($c_val['id_gender']),
                        'birthday' => pSQL($c_val['birthday']),
                        'active' => pSQL($c_val['active']),
                        'optin' => 0,
                        'newsletter' => 0,
                        'siret' => pSQL($c_val['siret']),
                        'ape' => pSQL($c_val['ape']),
                        'date_upd' => date('Y-m-d H:i:s'),
                    ),
                    "`id_customer_dubos`='" . pSQL($c_val['notiers']) . "'"
                );

                $id_customer = Db::getInstance()->getValue("SELECT `id_customer` FROM `" . _DB_PREFIX_ . "customer` WHERE `id_customer_dubos`='" . pSQL($c_val['notiers']) . "'");

                if (isset($data[0]['clients'][$c_key]['adresses']) && count($data[0]['clients'][$c_key]['adresses']))
                {
                    foreach ($data[0]['clients'][$c_key]['adresses'] as $a_key => $a_val)
                    {
                        if ($a_key === 0)
                            throw new Exception('Not a valid json string: Ajout d\'un crochet sur la balise adresses');

                        if ($a_val['active'] == '9')
                        {
                            Db::getInstance()->delete('address', "`id_address_dubos`='" . pSQL($a_val['noadresse']) . "'");
                            continue;
                        }

                        $address = array(
                            'id_address_dubos' => pSQL($a_val['noadresse']),
                            'id_customer' => pSQL($id_customer),
                            'alias' => pSQL($a_val['alias']),
                            'address1' => pSQL($a_val['address1']),
                            'address2' => pSQL($a_val['address2']),
                            'city' => pSQL($a_val['city']),
                            'postcode' => pSQL($a_val['postcode']),
                            'phone' => pSQL($a_val['phone']),
                            'phone_mobile' => pSQL($a_val['phone_mobile']),
                            'company' => pSQL($a_val['company']),
                            'lastname' => pSQL($a_val['lastname']),
                            'firstname' => pSQL($a_val['firstname']),
                            'other' => pSQL($a_val['other']),
                            'active' => pSQL($a_val['active']),
                            'id_country' => pSQL(Db::getInstance()->getValue("SELECT `id_country` FROM `" . _DB_PREFIX_ . "country` WHERE `iso_code`='" . pSQL($a_val['id_country']) . "'")),
                            'date_upd' => date('Y-m-d H:i:s'),
                            'rank' => pSQL($a_val['rank']),
                        );

                        if (($exists = Db::getInstance()->getValue("SELECT `id_address` FROM `" . _DB_PREFIX_ . "address` WHERE `id_customer`='" . pSQL($id_customer) ."' AND `id_address_dubos`='" . pSQL($a_val['noadresse']) . "'")) == false)
                            Db::getInstance()->insert('address', $address);
                        else
                            Db::getInstance()->update('address', $address, "`id_customer`='" . pSQL($id_customer) ."' AND `id_address_dubos`='" . pSQL($a_val['noadresse']) . "'");
                    }
                }
            }
        }

        return true;
    }

    // public function customerReturn($data)
    // {
    //     if (isset($data[0]['retour_clients']))
    //     {
    //         foreach ($data[0]['retour_clients'] as $c_key => $c_val)
    //         {
    //             Db::getInstance()->query("UPDATE `" . _DB_PREFIX_ . "customer` SET `id_customer_dubos`='" . pSQL($c_key) . "' WHERE `email`='" . pSQL($c_val['email']) . "'");
    //             $customer = Customer::getCustomersByEmail($c_val['email']);
                
    //             if (isset($c_val['retour_adresses']))
    //             {
    //                 foreach ($c_val['retour_adresses'] as $a_key => $a_val)
    //                 {
    //                     if ($a_key === 0)
    //                         throw new Exception('Not a valid json string: Ajout d\'un crochet sur la balise retour_adresses');

    //                     Db::getInstance()->query("UPDATE `" . _DB_PREFIX_ . "address` SET `id_address_dubos`='" . pSQL($a_key) . "' WHERE `rank`='" . pSQL($a_val['rank']) . "' AND `id_customer`='" . pSQL($customer[0]['id_customer']) . "'");
    //                 }
    //             }
    //         }
    //     }
    // }

    public function saveProduct($data, $upd = false)
    {
        foreach ($data[0]['produits'] as $ref => $product)
        {
            if ($ref === 0)
                throw new Exception('Not a valid json string: Ajout d\'un crochet sur la balise produits');


            // CATEGORIES
            $categories = array();
            if (isset($product['categories']) && count($product['categories']) > 0)
            {
                $i = 1;
                foreach ($product['categories'] as $c_key => $cat_tree)
                {
                    if ($c_key === 0)
                        throw new Exception('Not a valid json string: Ajout d\'un crochet sur la balise categories');

                    $is_parent = true;
                    $id_parent = 2;
                    foreach ($cat_tree['value'] as $cats)
                    {
                        $id_cat = false;
                        $id_category = $cats['nocategorie'];
                        $name_category = $cats['categorie'];

                        if (!$is_parent)
                            $id_parent = Db::getInstance()->getValue("SELECT `id_category` FROM `" . _DB_PREFIX_ . "category` WHERE `id_category`='" . pSQL($categories[$i-1]) . "'");

                        if ($id_category != '')
                            $id_cat = Db::getInstance()->getValue("SELECT `id_category` FROM `" . _DB_PREFIX_ . "category` WHERE `id_category_dubos`='" . pSQL($id_category) . "'");

                        if ($id_cat === false)
                        {
                            $category = new Category();
                            $category->name[1] = $name_category;
                            $category->id_parent = $id_parent;
                            $category->link_rewrite[1] = $this->module->toNurl($name_category);
                            $category->id_category_dubos = $id_category;
                            $category->add();
                            $id_cat = $category->id;
                        }

                        $categories[$i] = $id_cat;
                        $is_parent = false;
                        $i++;
                    }
                }
            }
            else
                throw new Exception('Not a valid json string: Article sans catégorie');


            // PROPRIÉTÉ
            if (isset($product['property']) && $product['property'] != '')
            {
                $id_property = false;

                if ($product['property_id'] != '')
                    $id_property = Db::getInstance()->getValue("SELECT `id_category` FROM `" . _DB_PREFIX_ . "category` WHERE `id_category_dubos`='" . pSQL($product['property_id']) . "'");

                if ($id_property === false)
                {
                    $category = new Category();
                    $category->name[1] = $product['property_name'];
                    $category->description[1] = $product['property'];
                    $category->id_parent = 3;
                    $category->active = 1;
                    $category->link_rewrite[1] = $this->module->toNurl($product['property_name']);
                    $category->id_category_dubos = $product['property_id'];
                    $category->add();
                    $id_property = $category->id;
                }

                array_push($categories, $id_property);
            }


            // PRODUIT
            $id_category_default = Db::getInstance()->getValue("SELECT `id_category` FROM `" . _DB_PREFIX_ . "category` WHERE `id_category_dubos`='" . pSQL($product['id_category_default']) . "'");

            // Récupère l'id_taxe_rule_group si la clef id_tax_rules_group est bien renseignée et que le produit n'est pas primeur
            $id_tax_rules_group = ($product['id_tax_rules_group'] != '' && $product['wine'] != 1) ? Db::getInstance()->getValue("SELECT `id_tax_rules_group` FROM `" . _DB_PREFIX_ . "tax_rules_group` WHERE `" . _DB_PREFIX_ . "tax_rules_group`.`code`='" . pSQL($product['id_tax_rules_group']) . "' AND `" . _DB_PREFIX_ . "tax_rules_group`.`deleted`=0") : '0';

            $product_id = Product::getIdByRef($product['reference']);
            $object = new Product($product_id ? $product_id : null);

            if ($product['active'] == '9')
                $product_active = 0;
            else
                $product_active = 1;

            $object->reference = $product['reference'];
            $object->id_second_wine = $product['id_second_wine'];
            $object->id_category_default = (int)$id_category_default;
            $object->id_tax_rules_group = (int)$id_tax_rules_group;
            $object->active = $product_active;
            $object->wine = (int)$product['wine'];
            $object->wine_date = $product['wine_date'];
            $object->wine_delivery = $product['wine_delivery'];
            $object->shop_quantity = $product['shop_quantity'];
            $object->price = str_replace(',', '.', $product['price']);
            $object->quantity = $product['reference'];
            // $object->out_of_stock = $product['out_of_stock'];
            $object->available_date = $product['available_date'];
            $object->available_later = $product['available_later'];
            $object->link_rewrite[1] = $this->module->toNurl($this->rDQuote($product['name']));
            $object->name[1] = $this->rDQuote($product['name']);
            $object->calling[1] = $this->rDQuote($product['calling']);
            $object->calling_picture_big = $product['calling_picture_big'];
            $object->calling_picture_small = $product['calling_picture_small'];
            $object->property[1] = $this->rDQuote($product['property']);
            $object->property_picture = $product['property_picture'];
            $object->description[1] = $this->rDQuote($product['description']);
            $object->description_short[1] = $this->rDQuote($product['description_short']);
            $object->date_add = $product['date_add'];
            $object->save();

            if ($upd)
                $object->updateCategories($categories, true);
            else
                $object->addToCategories($categories);

            // StockAvailable::setProductOutOfStock((int)$object->id, $product['out_of_stock'], 1);
            StockAvailable::setQuantity((int)$object->id, 0, $product['quantity']);


            // LOYALTY
            if ($product['wine'] == 1)
            {
                $id_totloyaltyadvanced = Db::getInstance()->getValue("SELECT `id_totloyaltyadvanced` FROM `" . _DB_PREFIX_ . "totloyaltyadvanced` WHERE `id_product`='" . pSQL($object->id) . "'");

                if ($id_totloyaltyadvanced === false)
                {
                    Db::getInstance()->insert(
                        'totloyaltyadvanced',
                        array(
                            'id_product'  => $object->id,
                            'loyalty'     => 0,
                            'date_begin'  => '0000-00-00',
                            'date_finish' => '0000-00-00',
                        )
                    );
                }
            }


            // PICTOGRAM
            Db::getInstance()->execute("DELETE FROM `" . _DB_PREFIX_ . "pictogram_product` WHERE `id_product`='" . pSQL($object->id) . "'");

            if (isset($product['pictogram']) && count($product['pictogram']) > 0)
            {
                foreach ($product['pictogram'] as $picto)
                {
                    if (is_array($picto))
                        throw new Exception('Not a valid json string: Ajout d\'un crochet sur la balise pictogram');

                    $id_picto = Db::getInstance()->getValue("SELECT `id_pictogram` FROM `" . _DB_PREFIX_ . "pictogram` WHERE `slug`='" . pSQL(trim($picto)) . "'");

                    if ($id_picto === false)
                    {
                        Db::getInstance()->insert('pictogram', array('slug' => pSQL(trim($picto))));
                        $id_picto = Db::getInstance()->Insert_ID();
                    }

                    Db::getInstance()->insert('pictogram_product', array('id_product' => (int)$object->id, 'id_pictogram' => (int)$id_picto));
                }
            }


            // FOODANDWINE
            Db::getInstance()->execute("DELETE FROM `" . _DB_PREFIX_ . "foodandwine_product` WHERE `id_product`='" . pSQL($object->id) . "'");

            if (isset($product['features']) && count($product['features']) > 0)
            {
                foreach ($product['foodandwine'] as $foodandwine_picto)
                {
                    if (is_array($foodandwine_picto))
                        throw new Exception('Not a valid json string: Ajout d\'un crochet sur la balise foodandwine');

                    $id_picto = Db::getInstance()->getValue("SELECT `id_foodandwine` FROM `" . _DB_PREFIX_ . "foodandwine` WHERE `slug`='" . pSQL(trim($foodandwine_picto)) . "'");

                    if ($id_picto === false)
                    {
                        Db::getInstance()->insert('foodandwine', array('slug' => pSQL(trim($foodandwine_picto))));
                        $id_picto = Db::getInstance()->Insert_ID();
                    }

                    Db::getInstance()->insert('foodandwine_product', array('id_product' => (int)$object->id, 'id_foodandwine' => (int)$id_picto));
                }
            }


            // FEATURES
            if (isset($product['features']) && count($product['features']) > 0)
            {
                foreach ($product['features'] as $f_key => $feat)
                {
                    if ($f_key === 0)
                        throw new Exception('Not a valid json string: Ajout d\'un crochet sur la balise features');

                    if (empty($feat['feature']))
                        continue;

                    $id_feat = Db::getInstance()->getValue("SELECT `id_feature` FROM `" . _DB_PREFIX_ . "feature_lang` WHERE `id_lang`=1 AND `name`='" . pSQL(trim($feat['feature'])) . "'");

                    if ($id_feat === false)
                    {
                        $feature = new Feature();
                        $feature->name[1] = trim($feat['feature']);
                        $feature->add();
                        $id_feat = $feature->id;
                    }

                    foreach ($feat['value'] as $value)
                    {
                        if (empty($value))
                            continue;

                        // if (trim($feat['feature']) == 'Cépages')
                        //     $value = preg_replace('@([0-9]{1,3}%\s)@', '', $value);

                        $id_feature_value = Db::getInstance()->getValue("
                            SELECT FVL.`id_feature_value` 
                            FROM `" . _DB_PREFIX_ . "feature_value_lang` FVL 
                            LEFT JOIN `" . _DB_PREFIX_ . "feature_value` FV ON FVL.`id_feature_value`=FV.`id_feature_value`  
                            WHERE `id_lang`=1 AND `value`='" . pSQL(trim($value)) . "' AND FV.`id_feature`='" . pSQL($id_feat) . "'");

                        if ($id_feature_value === false)
                        {
                            $feature_value = new FeatureValue();
                            $feature_value->id_feature = $id_feat;
                            $feature_value->custom = 0;
                            $feature_value->value[1] = trim($value);
                            $feature_value->add();
                            $id_feature_value = $feature_value->id;
                        }

                        $feature_product = Db::getInstance()->getValue("
                            SELECT *
                            FROM `" . _DB_PREFIX_ . "feature_product` FP
                            WHERE FP.`id_feature`='" . pSQL($id_feat) . "' AND FP.`id_product`='" . pSQL($object->id) . "' AND FP.`id_feature_value`='" . pSQL($id_feature_value) . "'"
                        );

                        if (!$feature_product)
                            Db::getInstance()->insert('feature_product', array('id_feature' => $id_feat, 'id_product' => $object->id, 'id_feature_value' => $id_feature_value));
                    }

                    $values = implode(', ', $feat['value']);
                    // if ($feat['feature'] == 'Cépages')
                    //     $object->grape[1] = $values;
                    // else if ($feat['feature'] == 'Récompenses')
                    //     $object->reward[1] = $values;
                    // else if ($feat['feature'] == 'Notations')
                    //     $object->notation[1] = $values;
                    if ($feat['feature'] == 'Récompenses')
                        $object->reward[1] = $values;                    

                    $object->update();
                }
            }
            else
                throw new Exception('Not a valid json string: Article sans feature');


            // NOTATIONS
            if (isset($product['notation']) && count($product['notation']) > 0)
            {
                $id_feature = Db::getInstance()->getValue("SELECT `id_feature` FROM `" . _DB_PREFIX_ . "feature_lang` WHERE `id_lang`=1 AND `name`='" . pSQL('Notations') . "'");

                // Si l'attribut Notations n'existe pas
                if ($id_feature === false)
                {
                    $feature = new Feature();
                    $feature->name[1] = pSQL('Notations');
                    $feature->add();
                    $id_feature = $feature->id;
                }

                $values = '';
                foreach ($product['notation'] as $n_key => $n_value)
                {
                    if (empty($n_value))
                        continue;

                    $id_feature_value = Db::getInstance()->getValue("
                        SELECT FVL.`id_feature_value` 
                        FROM `" . _DB_PREFIX_ . "feature_value_lang` FVL 
                        LEFT JOIN `" . _DB_PREFIX_ . "feature_value` FV ON FVL.`id_feature_value`=FV.`id_feature_value`  
                        WHERE `id_lang`=1 AND `value`='" . pSQL($n_value['value'][$n_key]['libelle']) . "' AND FV.`id_feature`='" . pSQL($id_feature) . "'");

                    if ($id_feature_value === false)
                    {
                        $feature_value = new FeatureValue();
                        $feature_value->id_feature = $id_feature;
                        $feature_value->custom = 0;
                        $feature_value->value[1] = pSQL($n_value['value'][$n_key]['libelle']);
                        $feature_value->add();
                        $id_feature_value = $feature_value->id;
                    }

                    $feature_product = Db::getInstance()->getValue("
                        SELECT *
                        FROM `" . _DB_PREFIX_ . "feature_product` FP
                        WHERE FP.`id_feature`='" . pSQL($id_feature) . "' AND FP.`id_product`='" . pSQL($object->id) . "' AND FP.`id_feature_value`='" . pSQL($id_feature_value) . "'"
                    );

                    if (!$feature_product)
                        Db::getInstance()->insert('feature_product', array('id_feature' => $id_feature, 'id_product' => $object->id, 'id_feature_value' => $id_feature_value));

                    $values .= $n_value['value'][$n_key]['libelle'] . ' ' . $n_value['value'][$n_key]['value'] . ', ';
                }

                if ($values != '')
                {
                    $object->notation[1] = substr($values, 0, -2);
                    $object->update();
                }
            }


            // CEPAGES
            if (isset($product['cepage']) && count($product['cepage']) > 0)
            {
                $id_feature = Db::getInstance()->getValue("SELECT `id_feature` FROM `" . _DB_PREFIX_ . "feature_lang` WHERE `id_lang`=1 AND `name`='" . pSQL('Cépages') . "'");

                // Si l'attribut Cépages n'existe pas
                if ($id_feature === false)
                {
                    $feature = new Feature();
                    $feature->name[1] = pSQL('Cépages');
                    $feature->add();
                    $id_feature = $feature->id;
                }

                $values = '';
                foreach ($product['cepage'] as $c_key => $c_value)
                {
                    if (empty($c_value))
                        continue;

                    $id_feature_value = Db::getInstance()->getValue("
                        SELECT FVL.`id_feature_value` 
                        FROM `" . _DB_PREFIX_ . "feature_value_lang` FVL 
                        LEFT JOIN `" . _DB_PREFIX_ . "feature_value` FV ON FVL.`id_feature_value`=FV.`id_feature_value`  
                        WHERE `id_lang`=1 AND `value`='" . pSQL($c_value['value'][$c_key]['libelle']) . "' AND FV.`id_feature`='" . pSQL($id_feature) . "'");

                    if ($id_feature_value === false)
                    {
                        $feature_value = new FeatureValue();
                        $feature_value->id_feature = $id_feature;
                        $feature_value->custom = 0;
                        $feature_value->value[1] = pSQL($c_value['value'][$c_key]['libelle']);
                        $feature_value->add();
                        $id_feature_value = $feature_value->id;
                    }

                    $feature_product = Db::getInstance()->getValue("
                        SELECT *
                        FROM `" . _DB_PREFIX_ . "feature_product` FP
                        WHERE FP.`id_feature`='" . pSQL($id_feature) . "' AND FP.`id_product`='" . pSQL($object->id) . "' AND FP.`id_feature_value`='" . pSQL($id_feature_value) . "'"
                    );

                    if (!$feature_product)
                        Db::getInstance()->insert('feature_product', array('id_feature' => $id_feature, 'id_product' => $object->id, 'id_feature_value' => $id_feature_value));

                    $values .= (int)$c_value['value'][$c_key]['value'] . '% ' . $c_value['value'][$c_key]['libelle'] . ', ';
                }

                if ($values != '')
                {
                    $object->grape[1] = substr($values, 0, -2);
                    $object->update();
                }
            }


            // ATTRIBUTES AND COMBINATIONS
            // Get specific price for combinations if update and before delete
            $sp_combination = array();
            // $qty_combination = array();
            // if (isset($product['attributes']) && count($product['attributes']) > 0 && count($product['attributes'][0]) > 0)
            if (isset($product['attributes']) && count($product['attributes']) > 0)
            {
                // foreach ($product['attributes'][0] as $key => $attr)
                foreach ($product['attributes'] as $key => $attr)
                {
                    if ($key === 0)
                        throw new Exception('Not a valid json string: Ajout d\'un crochet sur la balise attributes');

                    $attr_id = Db::getInstance()->getValue("SELECT `id_product_attribute` FROM `" . _DB_PREFIX_ . "product_attribute` WHERE `id_product_attribute_dubos`='" . pSQL($attr['id_product_attribute']) . "' AND `id_packaging`='" . pSQL($attr['id_conditionnement']) . "'");

                    $sp = Db::getInstance()->getRow("SELECT * FROM `" . _DB_PREFIX_ . "specific_price` WHERE `id_product_attribute`='" . pSQL($attr_id) . "'");

                    if ($attr_id && $sp)
                        $sp_combination[$attr['id_product_attribute'] . '|' . $attr['id_conditionnement']] = $sp;

                    // if (!$object->wine)
                    //  @$qty_combination[$attr['id_product_attribute']] += $attr['quantity'];
                }
            }
            else
                throw new Exception('Not a valid json string: Article sans attribut');


            // DELETE COMBINATIONS
            $object->deleteProductAttributes();

            if (isset($product['attributes']) && count($product['attributes']) > 0)
            {
                $pictures_association = array();

                foreach ($product['attributes'] as $key => $attr)
                {
                    if ($attr['active'] == 9)
                    {
                        $product_attribute_id = Db::getInstance()->getValue("SELECT `id_product_attribute` FROM `" . _DB_PREFIX_ . "product_attribute` WHERE `id_product_attribute_dubos`='" . pSQL($attr['id_product_attribute']) . "' AND `id_packaging`='" . pSQL($attr['id_conditionnement']) . "'");

                        if ($product_attribute_id)
                            Db::getInstance()->delete('product_attribute', "`id_product_attribute`='" . pSQL($product_attribute_id) . "'");
                    }
                    else
                    {
                        $ex = explode("|", $attr['name']);
                        $id_attributes = array();

                        // QUANTITY POUR LES NON PRIMEUR DE MÊME DECLI
                        foreach ($ex as $at)
                        {
                            $a = explode(':', $at);

                            if (empty($a[0]) || empty($a[1]))
                                continue;

                            if (!$object->wine)
                            {
                                if(preg_match('@Conditionnement@i', $a[0]))
                                    continue;

                                // if ($product['cache_default_attribute'] == $attr['id_product_attribute'] . '|' . $attr['id_conditionnement'])
                                //  continue;

                                // if ((count($product['attributes']) > 1) && ($product['cache_default_attribute'] == $attr['id_product_attribute'] . '|' . $attr['id_conditionnement']))
                                //  continue;

                                // if (isset($product['attributes'][$key]) && preg_match('@' . $a[1] . '@', $product['attributes'][$key]['name']))
                                //  continue;

                                if (isset($product['attributes'][0][$key-1]) && preg_match('@' . $a[1] . '@', $product['attributes'][0][$key-1]['name']))
                                    continue;
                            }

                            $id_attribute_group = Db::getInstance()->getValue("SELECT `id_attribute_group` FROM `" . _DB_PREFIX_ . "attribute_group_lang` WHERE `id_lang`=1 AND `name`='" . pSQL(trim($a[0])) . "'");

                            if ($id_attribute_group === false)
                            {
                                $attribute_group = new AttributeGroup();
                                $attribute_group->name[1] = trim($a[0]);
                                $attribute_group->public_name[1] = trim($a[0]);
                                $attribute_group->group_type = 'select';
                                $attribute_group->add();
                                $id_attribute_group = $attribute_group->id;
                            }

                            $id_attribute = Db::getInstance()->getValue("
                                SELECT AL.`id_attribute` 
                                FROM `" . _DB_PREFIX_ . "attribute_lang` AL 
                                LEFT JOIN `" . _DB_PREFIX_ . "attribute` A ON A.`id_attribute`=AL.`id_attribute`
                                WHERE `id_lang`=1 AND `name`='" . pSQL(trim($a[1])) . "' AND A.`id_attribute_group`='" . pSQL($id_attribute_group) . "'");

                            if ($id_attribute === false)
                            {
                                $attribute = new Attribute();
                                $attribute->name[1] = trim($a[1]);
                                $attribute->id_attribute_group = $id_attribute_group;
                                $attribute->add();
                                $id_attribute = $attribute->id;
                            }

                            $id_attributes[] = $id_attribute;
                        }

                        if (count($id_attributes))
                        {
                            $product_attribute_id = Db::getInstance()->getValue("SELECT `id_product_attribute` FROM `" . _DB_PREFIX_ . "product_attribute` WHERE `id_product_attribute_dubos`='" . pSQL($attr['id_product_attribute']) . "' AND `id_packaging`='" . pSQL($attr['id_conditionnement']) . "'");

                            $combination = new Combination($product_attribute_id ? $product_attribute_id : null);
                            $combination->id_product = $object->id;
                            $combination->reference = $attr['reference'];
                            $combination->shop_quantity = $attr['shop_quantity'];
                            // $combination->active = $attr['active'];
                            // $combination->price = !$object->wine ? str_replace(',','.', $attr['price']) :  str_replace(',','.', $attr['price']+$attr['packaging_price']);
                            $combination->price = !$object->wine ? str_replace(',','.', $attr['price']) : str_replace(',','.', $attr['price']);
                            $combination->packaging_price = $object->wine ? str_replace(',','.', $attr['packaging_price']) : '0';
                            $combination->weight = $attr['packaging_weight'];
                            // $combination->minimal_quantity = $attr['minimal_quantity'];
                            $combination->minimal_quantity = 1;
                            $combination->quantity = $attr['quantity'];
                            $combination->available_date = $attr['available_date'];
                            $combination->id_product_attribute_dubos = $attr['id_product_attribute'];
                            $combination->default_on = $product['cache_default_attribute'] == $attr['id_product_attribute'] . '|' . $attr['id_conditionnement'] ? 1 : 0;
                            $combination->id_packaging = $attr['id_conditionnement'];
                            $combination->save();

                            $combination->setAttributes($id_attributes);

                            // StockAvailable::setProductOutOfStock((int)$object->id, $attr['out_of_stock'], null, $combination->id);
                            StockAvailable::setQuantity((int)$object->id, $combination->id, isset($qty_combination[$attr['id_product_attribute']]) ? $qty_combination[$attr['id_product_attribute']] : $attr['quantity']);
                            StockAvailable::setShopQuantity((int)$object->id, $combination->id, isset($qty_combination[$attr['id_product_attribute']]) ? $qty_combination[$attr['id_product_attribute']] : $attr['shop_quantity']);

                            if (isset($sp_combination[$attr['id_product_attribute'] . '|' . $attr['id_conditionnement']]))
                            {
                                $specific_price = new SpecificPrice();
                                foreach ($sp_combination[$attr['id_product_attribute'] . '|' . $attr['id_conditionnement']] as $k => $v)
                                if ($k != 'id_specific_price')
                                    $specific_price->{$k} = $v;

                                $specific_price->id_product_attribute = $combination->id;
                                $specific_price->save();
                            }

                            if ($combination->default_on)
                            {
                                $object->cache_default_attribute = $combination->id;
                                $object->update();
                            }

                            $pictures_association[$attr['id_product_attribute']][] = $combination->id;
                        }
                    }
                }
            }


            // IMAGES
            $object->deleteImages();
            Image::deleteCover($object->id);

            if (isset($product['images']) && count($product['images']) > 0)
            {
                foreach ($product['images'] as $i_key => $img)
                {
                    if ($i_key === 0)
                        throw new Exception('Not a valid json string: Ajout d\'un crochet sur la balise images');

                    // if (empty($img['url']) || !file_exists($this->images_url.'chateau-capbern-gasqueton-002.jpg'))
                    if (empty($img['url']))
                        continue;

                    $image = new Image();
                    $image->id_product = (int)$object->id;
                    $image->position = Image::getHighestPosition($object->id) + 1;
                    $image->cover = $img['cover'] == '1' ? 1 : 0;
                    $image->legend[1] = $this->rDQuote($img['legend']);
                    $image->add();

                    foreach ($img['value'] as $img_attr)
                    if (isset($pictures_association[$img_attr]))
                    foreach ($pictures_association[$img_attr] as $id_attr)
                        Db::getInstance()->insert('product_attribute_image', array('id_product_attribute' => $id_attr, 'id_image' => $image->id));

                    if (!self::copyImg($object->id, $image->id, $this->images_url.$img['url']))
                    {
                        $image->delete();
                        continue;
                    }
                }
            }
        }

        return true;
    }

    public function addSpecifiquePrice($data)
    {
        if (isset($data[0]['tarif']) && count($data[0]['tarif']))
        {
            foreach ($data[0]['tarif'] as $s_price)
            {
                $id_product = ($s_price['id_product'] ? Product::getIdByRef($s_price['id_product']) : 0);

                $product = new Product($id_product);
                $id_customer = ($s_price['id_customer'] ? Customer::getIdByDubos($s_price['id_customer']) : 0);
                $attr = explode('|', $s_price['id_product_attribute']);
                $id_product_attribute = Db::getInstance()->getValue("SELECT `id_product_attribute` FROM `" . _DB_PREFIX_ . "product_attribute` WHERE `id_product_attribute_dubos`='" . pSQL($attr[0]) . "' AND `id_packaging`='" . pSQL($attr[1]) . "'");

                if (!$product->wine && !$id_product_attribute)
                    continue;

                $sp_id = Db::getInstance()->getValue("SELECT `id_specific_price` FROM `" . _DB_PREFIX_ . "specific_price` WHERE `id_product_attribute`='" . pSQL($id_product_attribute) . "'");

                $sp = new SpecificPrice($sp_id ? $sp_id : null);
                $sp->id_shop = 0;
                $sp->id_currency = 0;
                $sp->id_country = 0;
                $sp->id_group = 0;
                $sp->id_product = $id_product;
                $sp->id_product_attribute = $id_product_attribute;
                $sp->id_customer = $id_customer;
                $sp->reduction_type = $s_price['reduction_type'];
                $sp->reduction_tax = $s_price['reduction_tax'];
                $sp->reduction = $s_price['reduction_type'] == 'percentage' ? $s_price['reduction'] / 100 : $s_price['reduction'];
                $sp->from = empty($s_price['from']) ? '0000-00-00 00:00:00' : $s_price['from'];
                $sp->to = empty($s_price['to']) ? '0000-00-00 00:00:00' : $s_price['to'];
                $sp->price = $s_price['price'] == '0' ? '-1.000000' : str_replace(',', '.', $s_price['price']);
                $sp->from_quantity = $s_price['from_quantity'];
                $sp->save();
            }
        }

        return true;
    }

    public function saveStock($data, $upd = false)
    {
        foreach ($data[0]['Stock'] as $key => $value)
        {
            $product = Db::getInstance()->getRow("
                SELECT PA.`id_product_attribute`, PA.`id_product`
                FROM `" . _DB_PREFIX_ . "product_attribute` PA
                WHERE PA.`id_product_attribute_dubos`='" . pSQL($value['id_product_attribute']) . "'
            ");

            if ($upd)
            {
                Db::getInstance()->update('stock_available', array(
                        'quantity' => pSQL($value['quantity']),
                        'shop_quantity' => pSQL($value['shop_quantity']),
                    ),
                    "`id_product_attribute`='" . pSQL($product['id_product_attribute']) . "'"
                );
            }
            else
            {
                Db::getInstance()->insert('stock_available', array(
                    'id_product_attribute_dubos' => pSQL($value['id_product_attribute']),
                    'id_product' => pSQL($product['id_product']),
                    'id_product_attribute' => pSQL($product['id_product_attribute']),
                    'id_shop' => pSQL(1),
                    'id_shop_group' => pSQL(0),
                    'quantity' => pSQL($value['quantity']),
                    'shop_quantity' => pSQL($value['shop_quantity']),
                    'depends_on_stock' => pSQL(0),
                    'out_of_stock' => pSQL(2),
                ));
            }
        }

        return true;
    }

    public function saveOrderState($data)
    {
        foreach ($data[0]['retour_commande'] as $key => $value)
        {
            $order = Db::getInstance()->getRow("
                SELECT O.`id_order`
                FROM `" . _DB_PREFIX_ . "orders` O
                WHERE O.`id_order_dubos`='" . pSQL($value['IdSynchro']) . "'
            ");

            Db::getInstance()->update('orders', array(
                    'current_state' => pSQL($value['EtatCommande'])
                ),
                "`id_order`='" . pSQL($order['id_order']) . "'"
            );

            Db::getInstance()->insert('order_history', array(
                'id_employee' => pSQL(0),
                'id_order' => pSQL($order['id_order']),
                'id_order_state' => pSQL($value['EtatCommande']),
                'date_add' => date('Y-m-d H:i:s')
            ));
        }

        return true;
    }

    protected static function copyImg($id_entity, $id_image = null, $url = '', $entity = 'products', $regenerate = true)
    {
        $tmpfile = tempnam(_PS_TMP_IMG_DIR_, 'ps_import');
        $watermark_types = explode(',', Configuration::get('WATERMARK_TYPES'));

        switch ($entity) {
            default:
            case 'products':
                $image_obj = new Image($id_image);
                $path = $image_obj->getPathForCreation();
                break;
            case 'categories':
                $path = _PS_CAT_IMG_DIR_.(int)$id_entity;
                break;
            case 'manufacturers':
                $path = _PS_MANU_IMG_DIR_.(int)$id_entity;
                break;
            case 'suppliers':
                $path = _PS_SUPP_IMG_DIR_.(int)$id_entity;
                break;
            case 'stores':
                $path = _PS_STORE_IMG_DIR_.(int)$id_entity;
                break;
        }

        $url = urldecode(trim($url));
        $parced_url = parse_url($url);

        if (isset($parced_url['path'])) {
            $uri = ltrim($parced_url['path'], '/');
            $parts = explode('/', $uri);
            foreach ($parts as &$part) {
                $part = rawurlencode($part);
            }
            unset($part);
            $parced_url['path'] = '/'.implode('/', $parts);
        }

        if (isset($parced_url['query'])) {
            $query_parts = array();
            parse_str($parced_url['query'], $query_parts);
            $parced_url['query'] = http_build_query($query_parts);
        }

        if (!function_exists('http_build_url')) {
            require_once(_PS_TOOL_DIR_.'http_build_url/http_build_url.php');
        }

        $url = http_build_url('', $parced_url);

        $orig_tmpfile = $tmpfile;

        if (Tools::copy($url, $tmpfile)) {
            // Evaluate the memory required to resize the image: if it's too much, you can't resize it.
            if (!ImageManager::checkImageMemoryLimit($tmpfile)) {
                @unlink($tmpfile);
                return false;
            }

            $tgt_width = $tgt_height = 0;
            $src_width = $src_height = 0;
            $error = 0;
            ImageManager::resize($tmpfile, $path.'.jpg', null, null, 'jpg', false, $error, $tgt_width, $tgt_height, 5, $src_width, $src_height);
            $images_types = ImageType::getImagesTypes($entity, true);

            if ($regenerate) {
                $previous_path = null;
                $path_infos = array();
                $path_infos[] = array($tgt_width, $tgt_height, $path.'.jpg');
                foreach ($images_types as $image_type) {
                    $tmpfile = self::get_best_path($image_type['width'], $image_type['height'], $path_infos);

                    if (ImageManager::resize(
                        $tmpfile,
                        $path.'-'.stripslashes($image_type['name']).'.jpg',
                        $image_type['width'],
                        $image_type['height'],
                        'jpg',
                        false,
                        $error,
                        $tgt_width,
                        $tgt_height,
                        5,
                        $src_width,
                        $src_height
                    )) {
                        // the last image should not be added in the candidate list if it's bigger than the original image
                        if ($tgt_width <= $src_width && $tgt_height <= $src_height) {
                            $path_infos[] = array($tgt_width, $tgt_height, $path.'-'.stripslashes($image_type['name']).'.jpg');
                        }
                        if ($entity == 'products') {
                            if (is_file(_PS_TMP_IMG_DIR_.'product_mini_'.(int)$id_entity.'.jpg')) {
                                unlink(_PS_TMP_IMG_DIR_.'product_mini_'.(int)$id_entity.'.jpg');
                            }
                            if (is_file(_PS_TMP_IMG_DIR_.'product_mini_'.(int)$id_entity.'_'.(int)Context::getContext()->shop->id.'.jpg')) {
                                unlink(_PS_TMP_IMG_DIR_.'product_mini_'.(int)$id_entity.'_'.(int)Context::getContext()->shop->id.'.jpg');
                            }
                        }
                    }
                    if (in_array($image_type['id_image_type'], $watermark_types)) {
                        Hook::exec('actionWatermark', array('id_image' => $id_image, 'id_product' => $id_entity));
                    }
                }
            }
        } else {
            @unlink($orig_tmpfile);
            return false;
        }
        unlink($orig_tmpfile);
        return true;
    }

    protected static function get_best_path($tgt_width, $tgt_height, $path_infos)
    {
        $path_infos = array_reverse($path_infos);
        $path = '';
        foreach ($path_infos as $path_info) {
            list($width, $height, $path) = $path_info;
            if ($width >= $tgt_width && $height >= $tgt_height) {
                return $path;
            }
        }
        return $path;
    }

    public function rDQuote($str)
    {
        return str_replace('`', '"', $str);
    }

}