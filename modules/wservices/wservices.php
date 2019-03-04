<?php

if (!defined('_PS_VERSION_'))
    exit;

ini_set('default_socket_timeout', -1);

require_once(_PS_MODULE_DIR_ . 'wservices/classes/RedisConnect.php');

class Wservices extends Module
{
    public function __construct()
    {
        $this->name                   = 'wservices';
        $this->tab                    = 'front_office_features';
        $this->version                = '1.0.0';
        $this->author                 = 'Yateo - Valentin THIBAULT';
        $this->need_instance          = 0;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->module_table           = 'wservices';

        parent::__construct();

        $this->displayName = $this->l('WebServices Redis');
        $this->description = $this->l('Enables WebServices for synchronicity.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        if (!Configuration::get('WSERVICES'))
            $this->warning = $this->l('No name provided for wservices module.');
    }

    public function install()
    {
        if (Shop::isFeatureActive())
            Shop::setContext(Shop::CONTEXT_ALL);

        if (!parent::install() 
            || !$this->installDB() 
            || !Configuration::updateValue('WSERVICES', 'toto')
            // || !$this->registerHook('actionObjectAddAfter')
            // || !$this->registerHook('actionObjectUpdateAfter')
            // || !$this->registerHook('actionObjectDeleteBefore')
        )
            return false;
        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall() || !Configuration::deleteByName('WSERVICES'))
            return false;
        return true;
    }

    public function installDB()
    {
        return Db::getInstance()->execute("
            CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . $this->module_table . "` (
              `id_wservices` int(11) NOT NULL AUTO_INCREMENT,
              `transaction` varchar(255) NOT NULL,
              `model` varchar(255) NOT NULL,
              `type` varchar(255) NOT NULL,
              `transaction` TEXT NOT NULL,
              `way` varchar(10) NOT NULL,
              `date_add` datetime NOT NULL,
              `date_upd` datetime NOT NULL,
              PRIMARY KEY (`id_wservices`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
        ");
    }

    public function add($data, $way, $err = false)
    {
        if ($err)
            $this->publishError($data, $err);

        Db::getInstance()->insert(
            $this->module_table, array(
                'id_json'     => pSQL($data['NoJSON']),
                'model'       => pSQL($data['Modèle']),
                'type'        => pSQL($data['Type']),
                'transaction' => pSQL(json_encode($data, JSON_UNESCAPED_UNICODE)),
                'way'         => pSQL($way),
                'date_add'    => date('Y-m-d H:i:s'),
                'date_upd'    => date('Y-m-d H:i:s'),
            )
        );

        $redis_connect = new RedisConnect();
        $redis = $redis_connect->connect();
        $redis->zDeleteRangeByScore('mt:CM_Site1_' . $data['Modèle'], $data['NoJSON'], $data['NoJSON']);

        $this->logData($data);

        return Db::getInstance()->Insert_ID();
    }

    public function logData($data)
    {
        return file_put_contents(_PS_MODULE_DIR_ . 'wservices/logs/' . $data['Modèle'] .'/' . $data['NoJSON'] . '_' . $data['IdTransaction'] . '.txt', print_r($data, true));
    }

    public function genPassword($char = 8)
    {
        $password = "";
        $tab      = array_merge(range('a','z'), range('A','Z'), range('0','9')); 
        $nb       = count($tab); 
        
        for($i = 0; $i <= $char; $i++)
            $password .= $tab[rand(0,$nb-1)];
 
        return $password;
    }

    public function guidv4()
    {
        if (function_exists('openssl_random_pseudo_bytes') === false)
            return; 

        $data    = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // set bits 6-7 to 10
        
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public function TransactionExists($value)
    {
        // return Db::getInstance()->getValue("SELECT `id_wservices` FROM `" . _DB_PREFIX_ . $this->module_table . "` WHERE `id_json`='" . pSQL($value) . "'");
        return Db::getInstance()->getValue("SELECT `id_wservices` FROM `" . _DB_PREFIX_ . $this->module_table . "` WHERE `transaction` LIKE '%\"IdTransaction\":\"" . pSQL($value) . "%'");
    }

    // public function hookActionObjectCustomerAddAfter($params)
    // {
    //     return $this->publishCustomer($params['object'], 'INS');
    // }

    // public function hookActionCustomerAccountUpdate($params)
    // {
    //     return $this->publishCustomer($params['customer'], 'UPD');
    // }

    // public function hookActionObjectAddAfter($params)
    // {
    //     $object = $params['object'];
    //     $type = 'INS';

    //     if (get_class($object) == 'Address' || get_class($object) == 'Customer')
    //     {
    //         if (get_class($object) == 'Address')
    //         {
    //             $type = 'UPD';
    //             $object = new Customer($object->id_customer);
    //         }

    //         return $this->publishCustomer($object, $type);
    //     }
    // }

    // public function hookActionObjectUpdateAfter($params)
    // {
    //     $object = $params['object'];

    //     if (get_class($object) == 'Address' || get_class($object) == 'Customer')
    //     {
    //         if (get_class($object) == 'Address')
    //             $object = new Customer($object->id_customer);

    //         return $this->publishCustomer($object, 'UPD');
    //     }
    // }

    // public function hookActionObjectDeleteBefore($params)
    // {
    //     $object = $params['object'];
    //     $del = 'customer';

    //     if (get_class($object) == 'Address' || get_class($object) == 'Customer')
    //     {
    //         $addresses = $this->getCustomerAddresses($object->id_customer);

    //         if (get_class($object) == 'Address')
    //         {
    //             foreach ($addresses as &$address)
    //             if ($object->id == $address['id_address'])
    //                 $address['active'] = '9';

    //             $del = 'address';
    //             $object = new Customer($object->id_customer);
    //         }

    //         return $this->publishCustomer($object, 'UPD', $addresses, $del);
    //     }
    // }

    public function deleteAddress($object)
    {
        $addresses = $this->getCustomerAddresses($object->id_customer);

        foreach ($addresses as &$address)
        if ($object->id_address_dubos == $address['noadresse'])
            $address['active'] = '9';

        return $this->publishCustomer(new Customer($object->id_customer), 'UPD', $addresses);
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

    public function getCustomerAddresses($id_customer)
    {
        $return = array();
        $sql = Db::getInstance()->executeS("
            SELECT 
                A.`id_address`, A.`id_address_dubos` AS noadresse, A.`alias`, A.`address1`, A.`address2`, A.`city`, A.`postcode`, A.`phone`, A.`phone_mobile`, A.`company`, A.`firstname`, A.`lastname`, A.`other`, A.`active`, C.`iso_code` AS id_country, A.`rank`
            FROM `" . _DB_PREFIX_ . "address` A
            LEFT JOIN `" . _DB_PREFIX_ . "country` C ON C.`id_country`=A.`id_country`
            WHERE `id_customer`='" . pSQL($id_customer) . "'
        ");

        foreach ($sql as $res)
        {
            $res['noadresse'] = !empty($res['noadresse']) ? $res['noadresse'] : $res['id_address'];
            unset($res['id_address']);
            $return[$res['noadresse']] = $res;
        }

        return $return;
    }

    public function toNurl($str)
    {
        $str = htmlentities($str, ENT_NOQUOTES, 'utf-8');

        $str = preg_replace('#\&([A-za-z])(?:acute|cedil|circ|grave|ring|tilde|uml)\;#', '\1', $str);
        $str = preg_replace('#\&([A-za-z]{2})(?:lig)\;#', '\1', $str); // pour les ligatures e.g. '&oelig;'
        $str = preg_replace('#\&[^;]+\;#', '', $str); // supprime les autres caractères (&[X]acute;)
        $str = preg_replace('([^a-zA-Z0-9-_])', '-', $str); // remplace tous ce qui n'est pas alphanumérique (et - et _)
        while (strlen($str) != strlen(($str = str_replace('--', '-', $str))));

        return strtolower($str);
    }

    // Envoi des clients
    public function publishCustomer($customer, $type, $addresses = array())
    {
        if (!count($addresses))
            $addresses = $this->getCustomerAddresses($customer->id);

        // Get customer's loyalty
        $consumed_points    = 0;
        $acquired_points    = 0;
        $remaining_points   = 0;
        $customer_loyalties = $this->getCustomerLoyalties($customer->id);

        if (isset($customer_loyalties) && !empty($customer_loyalties))
        {
            // 1 = En attente de validation
            // 2 = Disponible
            // 3 = Annulés
            // 4 = Déjà convertis
            // 5 = Non disponbile sur produits remisés
            foreach ($customer_loyalties as $key => $value) {
                if ($value['id_loyalty_state'] == 4)
                    $consumed_points += $value['points'];

                if ($value['id_loyalty_state'] == 2 || $value['id_loyalty_state'] == 4)
                    $acquired_points += $value['points'];
            }
            $remaining_points = $acquired_points - $consumed_points;
        }
        
        $trans = array(
            'NoJSON'          => '',
            'IdTransaction'   => md5(microtime()),
            'Modèle'          => 'CLT',
            'Type'            => $type,
            'DateTransaction' => date('Y-m-d H:i:s'),
            'Transaction'     => array(
                '0' => array(
                    'clients' => array(
                        (!empty($customer->id_customer_dubos) ? $customer->id_customer_dubos : $customer->id) => array(
                            'notiers'           => (!empty($customer->id_customer_dubos) ? $customer->id_customer_dubos : ''),
                            'lastname'          => $customer->lastname,
                            'firstname'         => $customer->firstname,
                            'email'             => $customer->email,
                            'id_gender'         => $customer->id_gender,
                            'passwd'            => '',
                            'birthday'          => ($customer->birthday == 'null' || is_null($customer->birthday) || $customer->birthday == '0000-00-00' ? '' : $customer->birthday),
                            'active'            => (!is_null($customer->active) && $customer->active) ? '1' : '0',
                            'optin'             => (!is_null($customer->optin) && $customer->optin) ? '1' : '0',
                            'newsletter'        => (!is_null($customer->newsletter) && $customer->newsletter) ? '1' : '0',
                            'siret'             => (!is_null($customer->siret) ? $customer->siret : ''),
                            'ape'               => (!is_null($customer->ape) ? $customer->ape : ''),
                            'date_add'          => $customer->date_add,
                            'NbPointsConsommes' => (string)$consumed_points,
                            'NbPointsAcquits'   => (string)$acquired_points,
                            'NbPointsRestants'  => (string)$remaining_points,
                            'adresses'          => $addresses
                        ),
                    ),
                ),
            )
        );

        $trans['NoJSON'] = $this->add($trans, 'set');

        return $this->publish($trans);
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
            O.`id_order_dubos` as IdOrderDubos,
            O.`date_add` as DateCommande,
            CD.`id_customer_dubos` as NoClientLivre,
            CONCAT(CD.`lastname`, ' ', CD.`firstname`) as NomClientLivre,
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
            O.`gift_message` as CommentairesCadeau,
            OS.`id_order_state` as EtatCommande,
            O.`total_paid_tax_excl` as MttTotalHT,
            O.`total_paid_tax_incl` as MttTotalTTC,
            O.`total_products` as MttTotalMarchandiseHT," .
            // *** Transport *** //
            "OC.`id_carrier` as CodeTransporteur,
            TRG.`code` as CodeNiveauTaxe,
            OC.`shipping_cost_tax_excl` as MontantFraisPortHT,
            OC.`shipping_cost_tax_incl` as MontantFraisPortTTC," .
            // *** Paiement *** //
            "O.`module` as CodeModePaiement,
            O.`date_add` as DatePaiement,
            (SELECT SUM(PL.`points`) FROM `ps_totloyalty` PL HAVING PL.`id_customer`=NoClientLivre) as NbPointFidelite,
            O.`total_paid` as MttRegleTTC,
            O.`current_state` as CurrentState," .
            // *** Remises *** //
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
            /** COMMANDE **/
            $num_commande = $value['NoCommande'];
            $order['commande'][$num_commande]['NoCommande'] = $value['IdOrderDubos'];
            $order['commande'][$num_commande]['DateCommande'] = $value['DateCommande'];
            $order['commande'][$num_commande]['CodeTransporteur'] =$value['CodeTransporteur'];
            $order['commande'][$num_commande]['NoClientLivre'] = $value['NoClientLivre'];
            $order['commande'][$num_commande]['NomClientLivre'] = $value['NomClientLivre'];
            $order['commande'][$num_commande]['NomContactClientLivre'] = $value['NomClientLivre'];
            $order['commande'][$num_commande]['EmailContactLivre'] = $value['EmailContactLivre'];
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
            $order['commande'][$num_commande]['CommentairesExpedition'] = '';
            $order['commande'][$num_commande]['CommentairesCadeau'] = ($value['CommentairesCadeau']) ? $value['CommentairesCadeau'] : '';

            /** TRANSPORT **/
            $order['commande'][$num_commande]['Transport'] = [];
            $montant_frais_port_ht = $value['MontantFraisPortHT'];
            if ($montant_frais_port_ht > 0)
            {
                $order['commande'][$num_commande]['Transport'][1]['CodeTransporteur'] = ($value['CodeTransporteur']) ? $value['CodeTransporteur'] : '';
                $order['commande'][$num_commande]['Transport'][1]['CodePointRelai'] = '';
                $order['commande'][$num_commande]['Transport'][1]['CodeNiveauTaxe'] = ($value['CodeNiveauTaxe']) ? $value['CodeNiveauTaxe'] : 'EXO';
                $order['commande'][$num_commande]['Transport'][1]['CodeElement'] = 'P001';
                $order['commande'][$num_commande]['Transport'][1]['MttHT'] = ($value['MontantFraisPortHT']) ? $value['MontantFraisPortHT'] : '';
                $order['commande'][$num_commande]['Transport'][1]['MttTTC'] = ($value['MontantFraisPortTTC']) ? $value['MontantFraisPortTTC'] : '';
            }

            /** REMISES **/
            $order['commande'][$num_commande]['Remises'] = [];
            if ($value['MontantRemiseHT'] > 0)
            {
                $order['commande'][$num_commande]['Remises'][1]['CodeNiveauTaxe'] = ($value['CodeNiveauTaxe']) ? $value['CodeNiveauTaxe'] : 'NOR';
                $order['commande'][$num_commande]['Remises'][1]['CodeElement'] = 'REMI';
                $order['commande'][$num_commande]['Remises'][1]['MttHTRemise'] = $value['MontantRemiseHT'];
                $order['commande'][$num_commande]['Remises'][1]['MttTTCRemise'] = $value['MontantRemiseTTC'];
                $montantTaxeRemises = $value['MontantRemiseTTC'] - $value['MontantRemiseHT'];
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

            $order['commande'][$num_commande]['Paiement'][1]['MttRegleTTC'] = $value['MttRegleTTC'];
            $order['commande'][$num_commande]['Paiement'][1]['NoCoupon']['idSynchro'] = $date_v->getTimestamp();

            if($value['DatePaiement'] != "0000-00-00 00:00:00") 
                $order['commande'][$num_commande]['Paiement'][1]['DatePaiement'] = $value['DatePaiement'];
            $order['commande'][$num_commande]['Paiement'][1]['DateEcheance'] = ($value['DatePaiement']) ? $value['DatePaiement'] : '';
            $order['commande'][$num_commande]['Paiement'][1]['NoCoupon'] = '';
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
            $order['commande'][$num_commande]['Marchandise'][$value['NoLigne']]['CodeNiveauTaxe'] = $value['CodeNiveauTaxe'];
            $order['commande'][$num_commande]['Marchandise'][$value['NoLigne']]['TauxTaxe'] = ($primeur) ? '0' : '0.2';
            $order['commande'][$num_commande]['Marchandise'][$value['NoLigne']]['CodeArticle'] = $value['CodeArticle'];
            $order['commande'][$num_commande]['Marchandise'][$value['NoLigne']]['CodeConditionnement'] = $value['CodeConditionnement'];
            $order['commande'][$num_commande]['Marchandise'][$value['NoLigne']]['PrixConditionnement'] = $value['PrixConditionnement'];
            $order['commande'][$num_commande]['Marchandise'][$value['NoLigne']]['Poids'] = $value['product_weight'];
            $order['commande'][$num_commande]['Marchandise'][$value['NoLigne']]['QteCommandee'] = $value['product_quantity'];
            $order['commande'][$num_commande]['Marchandise'][$value['NoLigne']]['PrixUnitaire'] = $value['unit_price_tax_excl'];
            $order['commande'][$num_commande]['Marchandise'][$value['NoLigne']]['PrixUnitaireNet'] = $value['unit_price_tax_excl'];
            $order['commande'][$num_commande]['Marchandise'][$value['NoLigne']]['MttHT'] = strval($order['commande'][$num_commande]['Marchandise'][$value['NoLigne']]['PrixUnitaireNet'] * $value['product_quantity']);
            $order['commande'][$num_commande]['Marchandise'][$value['NoLigne']]['MttTTC'] = $value['total_price_tax_incl'];
            $order['commande'][$num_commande]['Marchandise'][$value['NoLigne']]['NoTarif'] = '';

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

        // SET TRANSPORT TAUX TAXEgetOrderById
        if ($montant_frais_port_ht > 0)
            $order['commande'][$num_commande]['Transport'][1]['TauxTaxe'] = ($primeur || $order['commande'][$num_commande]['Transport'][1]['CodeNiveauTaxe'] == 'EXO') ? '0' : '0.2';

        return $order;
    }

    // Envoi de commande
    public function publishOrder($order, $type)
    {
        $trans = array(
            'NoJSON'          => '',
            'IdTransaction'   => md5(microtime()),
            'Modèle'          => 'CMD',
            'Type'            => $type,
            'DateTransaction' => date('Y-m-d H:i:s'),
            'Transaction'     => array($this->getOrderById($order->id))
        );

        $trans['NoJSON'] = $this->add($trans, 'set');

        return $this->publish($trans);
    }

    // Envoi de statuts de commande
    public function publishOrderState($order, $type)
    {
        $id_order_dubos = Db::getInstance()->getValue("SELECT `id_order_dubos` FROM `" . _DB_PREFIX_ . "orders` WHERE `id_order`='" . pSQL($order->id_order) . "'");

        $trans = array(
            'NoJSON'          => '',
            'IdTransaction'   => md5(microtime()),
            'Modèle'          => 'RCM',
            'Type'            => $type,
            'DateTransaction' => date('Y-m-d H:i:s'),
            'Transaction'     => array(
                '0' => array(
                    'retour_commande' => array(
                        $id_order_dubos => array(
                            'IdSynchro'    => $id_order_dubos,
                            'EtatCommande' => $order->id_order_state
                        ),
                    ),
                ),
            )
        );

        $trans['NoJSON'] = $this->add($trans, 'set');

        return $this->publish($trans);
    }

    // Envoi d'erreur
    public function publishError($data, $err)
    {
        $trans = array(
            'NoJSON'          => '',
            'IdTransaction'   => md5(microtime()),
            'Modèle'          => 'ERR',
            'Type'            => 'INS',
            'DateTransaction' => date('Y-m-d H:i:s'),
            'Transaction'     => array(
                'erreur' => array(
                    (isset($data['IdTransaction'])) ? $data['IdTransaction'] : 0 => array(
                        'IdTransaction'   => (isset($data['IdTransaction'])) ? $data['IdTransaction'] : '', // Id de la transaction qui cause l’erreur
                        'CodeModele'      => (isset($data['Modèle'])) ? $data['Modèle'] : '', // Code du modèle de JSON
                        'TypeTransaction' => (isset($data['Type'])) ? $data['Type'] : '', // Type de la transaction UPD ou INS
                        'Description'     => $err, // Message de l’erreur
                        'Criticite'       => '3', // Information, 2 = Attention, 3 = erreur
                        'Commentaires'    => '',
                        'DateTransaction' => date('Y-m-d H:i:s'), // Date de la transaction
                    ),
                ),
            )
        );

        $trans['NoJSON'] = $this->add($trans, 'set');

        return $this->publish($trans);
    }

    // Fonction d'envoi dans REDIS
    public function publish($data)
    {
        // Redis Init
        $redis_connect = new RedisConnect();
        // Redis Connect
        $redis = $redis_connect->connect();
        // Redis Add
        $redis->zAdd('mt:CC_Site1_' . $data['Modèle'], $data['NoJSON'], json_encode($data, JSON_UNESCAPED_UNICODE));
        // Redis Publish
        $redis->publish('CC_Site1_' . $data['Modèle'], json_encode($data, JSON_UNESCAPED_UNICODE));
        // Redis Close
        $redis->close();

        return true;
    }

}
