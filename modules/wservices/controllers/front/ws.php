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

		$this->receiver();
	}

	public function getCustomers()
	{
		$customers = Customer::getCustomers();
		foreach ($customers as &$customer)
		{
			// Get customer's informations
			$current_customer = new Customer($customer['id_customer']);

			// Get customer's loyalty
			$customer['NbPointsConsommés'] = 0;
			$customer['NbPointsAcquits'] = 0;
			$customer['NbPointsRestants'] = 0;
			$customer['loyalty'] = $this->getLoyaltiesByIdCustomer($customer['id_customer']);

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

			// Get customer's addresses
			$customer['addresses'] = $current_customer->getSimpleAddresses(Context::getContext()->language->id);
		}

		echo json_encode($customers, JSON_UNESCAPED_UNICODE);
		die();
	}

	public function getOrderById($id_order)
	{
		$sql = "
			SELECT 
				-- *** Entête de commande ***
				O.`id_order` as NoCommande,
				O.`date_add` as DateCommande,
				CD.`id_customer_dubos` as NoClientLivre,
				CONCAT(CD.`lastname`, ' ', CD.`firstname`) as NomClientLivre,
				AD.`phone` as NoTelephone1ClientLivre,
				AD.`phone_mobile` as NoTelephone2ClientLivre,
				AD.`address1` as Adresse1ClientLivre,
				AD.`address2` as Adresse2ClientLivre,
				AD.`postcode` as CodePostalClientLivre,
				AD.`city` as VilleClientLivre,
				CO.`iso_code` as PaysClientLivre,
				CD.`email` as EmailContactLivre,
				AD.`id_address_dubos` as NoAdresseClientLivre,
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
				Z.`name` as CodeRegimeTaxe,
				O.`gift_message` as CommentairesCadeau,
				-- commentaireexpedition as CommentairesExpedition,
				OS.`id_order_state` as EtatCommande,
				O.`total_paid_tax_excl` as MttTotalHT,
				O.`total_paid_tax_incl` as MttTotalTTC,
				O.`total_products` as MttTotalMarchandiseHT,
				O.`total_products_wt` as MttTotalMarchandiseTTC,

				-- *** Point relai chronopost (installer le module) ***
				-- CodePointRelai
				-- Adresse1PointRelai
				-- Adresse2PointRelai
				-- CodePostalPointRelai
				-- VillePointRelai

				-- *** Transport ***
				OC.`id_carrier` as CodeTransporteur,
				TRG.`code` as CodeNiveauTaxe,
				OC.`shipping_cost_tax_excl` as MontantFraisPortHT,
				OC.`shipping_cost_tax_incl` as MontantFraisPortTTC,
				O.`delivery_date` as DateLivraisonPrevue,

				-- *** Paiement ***
				O.`payment` as CodeModePaiement,
				case
					WHEN O.`payment` = 'Payment by check' OR O.`payment` = 'Chèque' OR O.`payment` = 'Bank wire'
					THEN ''
					ELSE OP.`date_add`
				end as DatePaiement,
				-- IdChèqueCadeau (installer le module chèque cadeau)
				(
					SELECT SUM(PL.`points`) 
					FROM `ps_totloyalty` PL
					HAVING PL.`id_customer`=NoClientLivre
				) as NbPointFidelite,
				O.`total_paid` as MttRegleTTC,
				O.`current_state` as CurrentState,

				-- *** Remises ***
				CR.`reduction_percent` as TauxRemise,
				-- CodeNiveauTaxe ???
				-- MontantBaseRemiseHT ???
				O.`total_discounts_tax_excl` as MontantRemiseHT,
				O.`total_discounts_tax_incl` as MontantRemiseTTC
			FROM `" . _DB_PREFIX_ . "orders` O
			LEFT JOIN `" . _DB_PREFIX_ . "address` AD ON O.`id_address_delivery`=AD.`id_address`
			LEFT JOIN `" . _DB_PREFIX_ . "customer` CD ON AD.`id_customer`=CD.`id_customer`
			LEFT JOIN `" . _DB_PREFIX_ . "address` AI ON O.`id_address_invoice`=AI.`id_address`
			LEFT JOIN `" . _DB_PREFIX_ . "customer` CI ON AI.`id_customer`=CI.`id_customer`
			LEFT JOIN `" . _DB_PREFIX_ . "currency` CU ON CU.`id_currency`=O.`id_currency`
			LEFT JOIN `" . _DB_PREFIX_ . "country` CO ON CO.`id_country`=AD.`id_country`
			LEFT JOIN `" . _DB_PREFIX_ . "zone` Z ON Z.`id_zone`=CO.`id_zone`
			LEFT JOIN `" . _DB_PREFIX_ . "country` COI ON COI.`id_country`=AI.`id_country`
			LEFT JOIN `" . _DB_PREFIX_ . "order_state_lang` OS ON OS.`id_order_state`=O.`current_state`
			LEFT JOIN `" . _DB_PREFIX_ . "order_carrier` OC ON OC.`id_order`=O.`id_order`
			LEFT JOIN `" . _DB_PREFIX_ . "carrier_tax_rules_group_shop` CTRG ON CTRG.`id_carrier`=OC.`id_carrier`
			LEFT JOIN `" . _DB_PREFIX_ . "tax_rules_group` TRG ON TRG.`id_tax_rules_group`=CTRG.`id_tax_rules_group`
			LEFT JOIN `" . _DB_PREFIX_ . "order_payment` OP ON OP.`order_reference`=O.`reference`
			LEFT JOIN `" . _DB_PREFIX_ . "totloyalty` PL ON PL.`id_order`=O.`id_order`
			LEFT JOIN `" . _DB_PREFIX_ . "order_cart_rule` OCL ON OCL.`id_order`=O.`id_order`
			LEFT JOIN `" . _DB_PREFIX_ . "cart_rule` CR ON CR.`id_cart_rule`=OCL.`id_cart_rule`
			WHERE O.`id_order`='" . pSQL($id_order) . "'
		";

		$order = [];
		$order_array = Db::getInstance()->ExecuteS($sql);
		foreach ($order_array as $value)
		{
			/** COMMANDE **/
			$order['commande']['NoCommande'] = $value['NoCommande'];
			$order['commande']['DateCommande'] = $value['DateCommande'];
			$order['commande']['CodeTransporteur'] =$value['CodeTransporteur'];
			$order['commande']['NoClientLivre'] = $value['NoClientLivre'];
			$order['commande']['NomClientLivre'] = $value['NomClientLivre'];
			$order['commande']['NomContactClientLivre'] = $value['NomClientLivre'];
			$order['commande']['EmailContactLivre'] = $value['EmailContactLivre'];
			$order['commande']['NoTelephone1ClientLivre'] = $value['NoTelephone1ClientLivre'];
			$order['commande']['NoTelephone2ClientLivre'] = $value['NoTelephone2ClientLivre'];
			$order['commande']['NoAdresseClientLivre'] = $value['NoAdresseClientLivre'];
			$order['commande']['Adresse1ClientLivre'] = $value['Adresse1ClientLivre'];
			$order['commande']['Adresse2ClientLivre'] = $value['Adresse2ClientLivre'];
			$order['commande']['Adresse3ClientLivre'] = '';
			$order['commande']['CodePostalClientLivre'] = $value['CodePostalClientLivre'];
			$order['commande']['VilleClientLivre'] = $value['VilleClientLivre'];
			$order['commande']['PaysClientLivre'] = $value['PaysClientLivre'];
			$order['commande']['CodeRegimeTaxe'] = $value['CodeRegimeTaxe'];
			$order['commande']['NoClientFacture'] = $value['NoClientFacture'];
			$order['commande']['NomClientFacture'] = $value['NomClientFacture'];
			$order['commande']['NoAdresseClientFacture'] = $value['NoAdresseClientFacture'];
			$order['commande']['NomContactClientFacture'] = $value['NomClientFacture'];
			$order['commande']['NoTelephone1ClientFacture'] = $value['NoTelephone1ClientFacture'];
			$order['commande']['NoTelephone2ClientFacture'] = $value['NoTelephone2ClientFacture'];
			$order['commande']['Adresse1ClientFacture'] = $value['Adresse1ClientFacture'];
			$order['commande']['Adresse2ClientFacture'] = $value['Adresse2ClientFacture'];
			$order['commande']['Adresse3ClientFacture'] = '';
			$order['commande']['CodePostalClientFacture'] = $value['CodePostalClientFacture'];
			$order['commande']['VilleClientFacture'] = $value['VilleClientFacture'];
			$order['commande']['PaysClientFacture'] = $value['PaysClientFacture'];
			$order['commande']['EstCadeau'] = $value['EstCadeau'];
			$order['commande']['EstPrimeur'] = 0;
			$order['commande']['EstCautionBancaire'] = 0;
			$order['commande']['CodeDevise'] = $value['CodeDevise'];
			$order['commande']['CodePays'] = $value['CodePays'];
			$order['commande']['EtatCommande'] = $value['EtatCommande'];
			$order['commande']['MttTotalMarchandiseHT'] = $value['MttTotalMarchandiseHT'];
			$order['commande']['MttTotalMarchandiseTTC'] = $value['MttTotalMarchandiseTTC'];
			$order['commande']['MttTotalHT'] = $value['MttTotalHT'];
			$order['commande']['MttTotalTTC'] = $value['MttTotalTTC'];
			$order['commande']['CommentairesExpedition'] = ''; // selon le module du transporteur mais pas de champ prévu en front
			$order['commande']['CommentairesCadeau'] = $value['CommentairesCadeau'];

			/** TRANSPORT **/
			$order['commande']['Transport']['CodeTransporteur'] = $value['CodeTransporteur'];
			$order['commande']['Transport']['CodePointRelai'] = '';
			$order['commande']['Transport']['CodeNiveauTaxe'] = $value['CodeNiveauTaxe'];
			$order['commande']['Transport']['CodeElement'] = 'P001';
			$order['commande']['Transport']['MttHTRemise'] = $value['MontantFraisPortHT'];
			// $order['commande']['Transport']['MontantTaxe'] = $value['MontantFraisPortTTC'] - $value['MontantFraisPortHT'];
			$order['commande']['Transport']['MttTTCRemise'] = $value['MontantFraisPortTTC'];
			// $order['commande']['Transport']['DateLivraisonPrevue'] = $value['DateLivraisonPrevue'];
			
			/** CHRONOPOST **/
			// $order['commande']['Chronopost']['CodePointRelai'] = '';
			// $order['commande']['Chronopost']['Adresse1PointRelai'] = '';
			// $order['commande']['Chronopost']['Adresse2PointRelai'] = '';
			// $order['commande']['Chronopost']['CodePostalPointRelai'] = '';
			// $order['commande']['Chronopost']['VillePointRelai'] = '';

			/** REMISES **/
			$order['commande']['Remises']['CodeNiveauTaxe'] = $value['CodeNiveauTaxe'];
			// $order['commande']['Remises']['TauxRemise'] = $value['TauxRemise'];
			// $order['commande']['Remises']['MontantBaseRemiseHT'] = '';
			$order['commande']['Remises']['MontantRemiseHT'] = $value['MontantRemiseHT'];
			$order['commande']['Remises']['MontantTaxe'] = $value['MontantRemiseTTC'] - $value['MontantRemiseHT'];
			$order['commande']['Remises']['MontantRemiseTTC'] = $value['MontantRemiseTTC'];
			
			/** PAIEMENT **/
			$order['commande']['Paiement']['EstRegle'] = ($value['CurrentState'] == 2 ) ? 1 : 0;
			$order['commande']['Paiement']['CodeModePaiement'] = $value['CodeModePaiement'];
			$order['commande']['Paiement']['DatePaiement'] = $value['DatePaiement'];
			$order['commande']['Paiement']['MttRegleTTC'] = $value['MttRegleTTC'];
			$order['commande']['Paiement']['NoCoupon'] = ''; // TODO (Récupérer leur numéro de coupon)
			// $order['commande']['Paiement']['IdChèqueCadeau'] = ''; // installer le module pour les chèques cadeaux
			// $order['commande']['Paiement']['NbPointFidélité'] = $value['NbPointFidélité'];
		}

		$sql = "
			SELECT OD.*, P.`wine`, PA.`id_product_attribute` as NoLigne, PA.`id_packaging` as CodeConditionnement, TRG.`code` as CodeNiveauTaxe
			FROM `" . _DB_PREFIX_ . "order_detail` as OD
			LEFT JOIN `" . _DB_PREFIX_ . "product` P ON P.`id_product`=OD.`product_id`
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
			$order['commande']['Marchandise'][$key]['NoCommande'] = $order['commande']['NoCommande'];
			$order['commande']['Marchandise'][$key]['NoLigne'] = $value['NoLigne'];
			// $order['commande']['Marchandise'][$key]['CodeRegimeTaxe'] = $order['commande']['CodeRegimeTaxe'];
			$order['commande']['Marchandise'][$key]['CodeNiveauTaxe'] = $value['CodeNiveauTaxe'];
			$order['commande']['Marchandise'][$key]['TauxTaxe'] = ($primeur) ? 0 : 20;
			$order['commande']['Marchandise'][$key]['CodeArticle'] = $value['product_reference'];
			$order['commande']['Marchandise'][$key]['CodeConditionnement'] = $value['CodeConditionnement'];
			$order['commande']['Marchandise'][$key]['Poids'] = $value['product_weight'];
			$order['commande']['Marchandise'][$key]['QteCommandee'] = $value['product_quantity'];
			$order['commande']['Marchandise'][$key]['PrixUnitaire'] = $value['unit_price_tax_excl'];
			$order['commande']['Marchandise'][$key]['PrixUnitaireNet'] = $value['unit_price_tax_incl'];
			$order['commande']['Marchandise'][$key]['MttHT'] = $value['total_price_tax_excl'];
			$order['commande']['Marchandise'][$key]['MttTTC'] = $value['total_price_tax_incl'];
			$order['commande']['Marchandise'][$key]['NoTarif'] = ''; // TODO (Prestashop n'a pas d'id de tarif)

			/** REMISE PAR LIGNE **/
			$remise_produit = (($value['unit_price_tax_excl']*$value['reduction_percent'])/100)*$value['product_quantity'];
			$order['commande']['Marchandise'][$key]['Remise']['CodeNiveauTaxe'] = ($remise_produit) ? $value['CodeNiveauTaxe'] : '';
			$order['commande']['Marchandise'][$key]['Remise']['TauxTaxe'] = ($remise_produit) ? ($primeur) ? 0 : 20 : '';
			$order['commande']['Marchandise'][$key]['Remise']['CodeElement'] = ($remise_produit) ? 'REMI' : '';
			$order['commande']['Marchandise'][$key]['Remise']['MontantBaseRemiseHT'] = ($remise_produit) ? $value['unit_price_tax_excl']*$value['product_quantity'] : '';
			$order['commande']['Marchandise'][$key]['Remise']['MontantRemiseHT'] = ($remise_produit) ? (($value['unit_price_tax_excl']*$value['reduction_percent'])/100)*$value['product_quantity'] : '';
			$order['commande']['Marchandise'][$key]['Remise']['MontantTaxe'] = ($remise_produit) ? $value['total_price_tax_incl'] - $value['total_price_tax_excl'] : '';
			$order['commande']['Marchandise'][$key]['Remise']['MontantRemiseTTC'] = ($remise_produit) ? (($value['unit_price_tax_incl']*$value['reduction_percent'])/100)*$value['product_quantity'] : '';
		}

		$order['commande']['EstPrimeur'] = ($primeur) ? 1 : 0;
		$order['commande']['Transport']['TauxTaxe'] = ($primeur) ? 0 : 20;

		$cmd = array(
            'NoJSON' => 'TODO_GET_INSERT_ID',
            'IdTransaction' => md5(microtime()),
            'Modèle' => 'CMD',
            'Type' => 'INS',
            'DateTransaction' => date('Y-m-d H:i:s'),
            'Transaction' => array(
                $order
            )
        );

		echo '<pre>';
		echo json_encode($cmd, JSON_PRETTY_PRINT);
		// echo json_encode($cmd, JSON_UNESCAPED_UNICODE);
		// var_dump(file_put_contents('order.txt', json_encode($cmd, JSON_UNESCAPED_UNICODE)));
		die();
	}

	public function getProductById($id_product)
	{
		// $product = new Product(Tools::getValue('id_product'));

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

		// echo '<pre>';
		// print_r($product);
		echo json_encode($product, JSON_UNESCAPED_UNICODE);
		file_put_contents('product.txt', json_encode($product, JSON_UNESCAPED_UNICODE));
		die();
	}

	public function receiver()
	{
		// $_POST['data'] = '{"NoJSON":"971633","IdTransaction":"90566db908bc43808aecabd30efc9e1d","Modèle":"PRD","Type":"UPD","DateTransaction":"/Date(1504096095950-0000)/","Transaction":[{"produits":{"M2014003137":{"reference":"M2014003137","id_second_wine":"","id_category_default":"Vins étrangers","cache_default_attribute":"42509","active":"1","name":"LAIBACH THE LADY BIRD","wine":"0","wine_date":"","wine_delivery":"","available_date":"","quantity":"0","shop_quantity":"0","price":"0","id_tax_rules_group":"NOR","available_later":"Commande en cours...","property_picture":"","calling_picture_big":"","calling_picture_small":"","calling":"Situées à 50 Km de Cape Town, cette appellation au climat méditerranéen est très connue pour ses vins rouges produits à base du cépage du Cabernet Sauvignon. Ces vins possèdent une belle couleur rubis, un nez aromatique, fruité et fleuri, avec des arômes de violette, de groseilles et de prunes. en bouche, l\'équilibre entre la fraîcheur et les tannins et idéal. Ce vin dévoile toute sa splendeur dans sa jeunesse.","property":"","description":"Au nez des notes de petits fruits rouges frais, cuir et épices. En bouche le vin a une belle fraicheur, des fruits bien présents et des tannins soyeux. C\'est un vin bien équilibré, élégant et complexe.rnA consommer 2 à 10 ans après vendange.","description_short":"","categories":["AFRIQUE DU SUD","Stellenbosch","Stellenbosch","Vins étrangers"],"pictogram":[],"foodandwine":[],"images":[{"1":{"cle":"1","url":"laibach-lady-bird.jpg","legend":"LAIBACH THE LADY BIRD","cover":"1","value":["42509"]}}],"attributes":[{"1":{"cle":"1","out_of_stock":"0","id_product_attribute":"42509","reference":"M2014003137","shop_quantity":"5","name":"Format : Bouteille 0,75 L","active":"1","price":"16.6667","packaging_weight":"0.000","minimal_quantity":"1.00","quantity":"17","available_date":"","packaging_price":"0.00","id_conditionnement":""}}],"features":[{"1":{"cle":"1","feature":"Apogée","value":[""]},"2":{"cle":"2","feature":"Appellation","value":["Stellenbosch"]},"3":{"cle":"3","feature":"Classification","value":[""]},"4":{"cle":"4","feature":"Couleur","value":["Rouge"]},"5":{"cle":"5","feature":"Degré","value":["14.000"]},"6":{"cle":"6","feature":"Millésime","value":["2014"]},"7":{"cle":"7","feature":"Mode de culture","value":[""]},"8":{"cle":"8","feature":"Niveau de garde","value":[""]},"9":{"cle":"9","feature":"Région","value":["Stellenbosch"]},"10":{"cle":"10","feature":"Température de service","value":["15-16°C"]}}]}}}]}';

		// $_POST['data'] = '{"NoJSON":"165","IdTransaction":"b6c6e6899b734447b90620767995f280","Modèle":"TRF","Type":"INS","DateTransaction":"2017-06-13","Transaction":[{"tarif":{"1":{"cle":"1","id_product_attribute":"36427|26","id_product":"M2012000761","reduction_type":"percentage","reduction_tax":"1","reduction":"10","from":"2016-05-27 14:21:19","to":"","price":"0","id_customer":"0","from_quantity":"0"},"2":{"cle":"2","id_product_attribute":"36427|125","id_product":"M2012000761","reduction_type":"percentage","reduction_tax":"1","reduction":"10","from":"2016-05-27 14:21:19","to":"","price":"0","id_customer":"0","from_quantity":"0"},"3":{"cle":"3","id_product_attribute":"36427|128","id_product":"M2012000761","reduction_type":"percentage","reduction_tax":"1","reduction":"10","from":"2016-05-27 14:21:19","to":"","price":"0","id_customer":"0","from_quantity":"0"},"4":{"cle":"4","id_product_attribute":"36427|129","id_product":"M2012000761","reduction_type":"percentage","reduction_tax":"1","reduction":"10","from":"2016-05-27 14:21:19","to":"","price":"0","id_customer":"0","from_quantity":"0"},"5":{"cle":"5","id_product_attribute":"36427|168","id_product":"M2012000761","reduction_type":"percentage","reduction_tax":"1","reduction":"10","from":"2016-05-27 14:21:19","to":"","price":"0","id_customer":"0","from_quantity":"0"},"6":{"cle":"6","id_product_attribute":"36427|174","id_product":"M2012000761","reduction_type":"percentage","reduction_tax":"1","reduction":"10","from":"2016-05-27 14:21:19","to":"","price":"0","id_customer":"0","from_quantity":"0"},"7":{"cle":"7","id_product_attribute":"36427|183","id_product":"M2012000761","reduction_type":"percentage","reduction_tax":"1","reduction":"10","from":"2016-05-27 14:21:19","to":"","price":"0","id_customer":"0","from_quantity":"0"},"8":{"cle":"8","id_product_attribute":"36427|298","id_product":"M2012000761","reduction_type":"percentage","reduction_tax":"1","reduction":"10","from":"2016-05-27 14:21:19","to":"","price":"0","id_customer":"0","from_quantity":"0"}}}]}';

		// $_POST['data'] = '{"NoJSON":"74246","IdTransaction":"48bd49f1de79450e8d0f6a2b9e8830de","Modèle":"CLT","Type":"INS","DateTransaction":"/Date(1504705322303-0000)/","Transaction":[{"clients":{"22424":{"notiers":"22424","lastname":"AUBURTIN","firstname":"Jean-luc","email":"auburtin.jean-luc@orange.fr","id_gender":"1","passwd":"jla1993","birthday":"1953-10-02 00:00:00","active":"1","optin":"0","newsletter":"0","siret":"","ape":"","NbPointsConsommes":"0","NbPointsAcquits":"976","NbPointsRestants":"976","adresses":[{"1":{"rank":"1","noadresse":"29105","alias":"57570 EVRANGE","address1":"3, rue du Puits","address2":"","city":"EVRANGE","postcode":"57570","phone":"","phone_mobile":"","company":"Mr AUBURTIN Jean-Luc","firstname":"Jean-luc","lastname":"AUBURTIN","other":"","active":"1","id_country":"FR"},"2":{"rank":"2","noadresse":"29106","alias":"57570 EVRANGE","address1":"3, rue du Puits","address2":"","city":"EVRANGE","postcode":"57570","phone":"","phone_mobile":"","company":"Mr AUBURTIN Jean-Luc","firstname":"Jean-luc","lastname":"AUBURTIN","other":"","active":"1","id_country":"FR"},"3":{"rank":"3","noadresse":"29106","alias":"57570 EVRANGE","address1":"3, rue du Puits","address2":"","city":"EVRANGE","postcode":"57570","phone":"","phone_mobile":"","company":"Mr AUBURTIN Jean-Luc","firstname":"Jean-luc","lastname":"AUBURTIN","other":"","active":"1","id_country":"FR"}}]}}}]}';

		// $_POST['data'] = '{"NoJSON":"38754","IdTransaction":"6f9ec0f0a00a48c2931f99b2dce681b5","Modèle":"STK","Type":"UPD","DateTransaction":"/Date(1509357180903-0000)/","Transaction":[{"Stock":{"36626":{"id_product_attribute":"36626","shop_quantity":"15","quantity":"16"}}}]}';

		if (!isset($_POST['data']))
			return false;

		$data = json_decode($_POST['data'], true);

		if (is_null($data) || $data === false)
		{
			// envoi de mail a wisy et yateo
			die('Not a valid json string');
		}

		if (!isset($data['IdTransaction']))
			die('Transaction not set.');

		// if ($this->module->TransactionExists($data['IdTransaction']))
		if ($this->module->TransactionExists($data['NoJSON']))
			die('Transaction already exists.');

		if ($data['Modèle'] == 'CLT' || $data['Modèle'] == 'CLIENT')
		{
			if ($data['Type'] == 'INS' || $data['Type'] == 'INSERT')
			if (!$this->addCustomer($data['Transaction']))
				die('Customer already exists.');

			if ($data['Type'] == 'UPD' || $data['Type'] == 'UPDATE')
				$this->updateCustomer($data['Transaction']);
		}

		if ($data['Modèle'] == 'RCL')
			$this->customerReturn($data['Transaction']);

		if ($data['Modèle'] == 'PRD' || $data['Modèle'] == 'PRODUIT')
		{
			if ($data['Type'] == 'INS' || $data['Type'] == 'INSERT')
				$this->saveProduct($data['Transaction']);

			if ($data['Type'] == 'UPD' || $data['Type'] == 'UPDATE')
				$this->saveProduct($data['Transaction'], true);
		}

		if ($data['Modèle'] == 'TRF' || $data['Modèle'] == 'TARIF')
			$this->addSpecifiquePrice($data['Transaction']);

		if ($data['Modèle'] == 'CMD' || $data['Modèle'] == 'COMMANDE')
		{
			if ($data['Type'] == 'INS' || $data['Type'] == 'INSERT')
			if (!$this->saveOrder($data['Transaction']));
				die('Order already exists.');

			if ($data['Type'] == 'UPD' || $data['Type'] == 'UPDATE')
				$this->updateOrder($data['Transaction']);
		}

		if ($data['Modèle'] == 'STK' || $data['Modèle'] == 'STOCK')
		{
			if ($data['Type'] == 'INS' || $data['Type'] == 'INSERT')
				$this->saveStock($data['Transaction']);

			if ($data['Type'] == 'UPD' || $data['Type'] == 'UPDATE')
				$this->saveStock($data['Transaction'], true);
		}

		$this->module->add($data, 'get');
		die();
	}

	public function addCustomer($data)
	{
		foreach ($data[0]['clients'] as $c_key => $c_val) 
		{
			if (Customer::customerExists($c_val['email']))
				return false;

			$password = $this->module->genPassword();
			$crypto = ServiceLocator::get('\\PrestaShop\\PrestaShop\\Core\\Crypto\\Hashing');

			Db::getInstance()->insert('customer', array(
					'id_customer_dubos' => $c_key,
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
					'date_add' => date('Y-m-d H:i:s'),
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
				foreach ($data[0]['clients'][$c_key]['adresses'][0] as $a_key => $a_val)
				{					
					// $address->alias = 'Mon adresse';
					$id_country = Db::getInstance()->getValue("SELECT `id_country` FROM `" . _DB_PREFIX_ . "country` WHERE `iso_code`='" . pSQL($a_val['id_country']) . "'");
					$rank = Db::getInstance()->getValue("SELECT `rank` FROM `" . _DB_PREFIX_ . "address` WHERE `id_customer`='" . pSQL($id_customer) . "' ORDER BY `rank` DESC");
    				$rank = ($rank !== false ? (int)$rank+1 : 1);

					Db::getInstance()->insert('address', array(
							'id_address_dubos' => pSQL($a_key),
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
							'id_country' => pSQL($id_country),
							'rank' => pSQL($rank),
							'date_add' => date('Y-m-d H:i:s'),
							'date_upd' => date('Y-m-d H:i:s'),
						)
					);

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
				if (!Customer::customerExists($c_val['email']))
					return false;

				if ($c_val['active'] == '9')
					return Db::getInstance()->delete('customer', "`id_customer_dubos`='" . pSQL($c_key) . "'");

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
					"`id_customer_dubos`='" . pSQL($c_key) . "'"
				);

				$id_customer = Db::getInstance()->getValue("SELECT `id_customer` FROM `" . _DB_PREFIX_ . "customer` WHERE `id_customer_dubos`='" . pSQL($c_key) . "'");

				if (isset($data[0]['clients'][$c_key]['adresses']) && count($data[0]['clients'][$c_key]['adresses']))
				{
					foreach ($data[0]['clients'][$c_key]['adresses'][0] as $a_key => $a_val)
					{					
						if ($a_val['active'] == '9')
						{
							Db::getInstance()->delete('address', "`id_address_dubos`='" . pSQL($a_key) . "'");
							continue;
						}

						$address = array(
							'id_address_dubos' => pSQL($a_key),
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
							// 'rank' => pSQL($a_val['rank']),
						);

						if (($exists = Db::getInstance()->getValue("SELECT `id_address` FROM `" . _DB_PREFIX_ . "address` WHERE `id_address_dubos`='" . pSQL($a_key) . "'")) === false)
							Db::getInstance()->insert('address', $address);
						else
							Db::getInstance()->update('address', $address, "`id_address_dubos`='" . pSQL($a_key) . "'");
					}
				}
			}
		}	

		return true;
	}

	public function customerReturn($data)
	{
		if (isset($data[0]['retour_clients']))
		{
			foreach ($data[0]['retour_clients'] as $c_key => $c_val)
			{
				Db::getInstance()->query("UPDATE `" . _DB_PREFIX_ . "customer` SET `id_customer_dubos`='" . pSQL($c_key) . "' WHERE `email`='" . pSQL($c_val['email']) . "'");
				$customer = Customer::getCustomersByEmail($c_val['email']);
				
				if (isset($c_val['retour_adresses']))
				{
					foreach ($c_val['retour_adresses'][0] as $a_key => $a_val)
						Db::getInstance()->query("UPDATE `" . _DB_PREFIX_ . "address` SET `id_address_dubos`='" . pSQL($a_key) . "' WHERE `rank`='" . pSQL($a_val['rank']) . "' AND `id_customer`='" . pSQL($customer[0]['id_customer']) . "'");
				}
			}
		}
	}

	public function saveProduct($data, $upd = false)
	{
		// echo '<pre>';
		// print_r($data);
		// die();

		foreach ($data[0]['produits'] as $ref => $product)
		{
			$id_category_default = Db::getInstance()->getValue("SELECT `id_category` FROM `" . _DB_PREFIX_ . "category_lang` WHERE `name`='" . pSQL($product['id_category_default']) . "' AND `id_lang`=1");
			$id_tax_rules_group = Db::getInstance()->getValue("SELECT `id_tax_rules_group` FROM `" . _DB_PREFIX_ . "tax_rules_group` WHERE `code`='" . pSQL($product['id_tax_rules_group']) . "'");
			// $id_second_wine = Db::getInstance()->getValue("SELECT `reference` FROM `" . _DB_PREFIX_ . "product` WHERE `reference`='" . pSQL($product['id_second_wine']) . "'");

			$product_id = Product::getIdByRef($product['reference']);
			$object = new Product($product_id ? $product_id : null);

			if ($product['active'] == '9')
				$object->delete();

			$object->reference = $product['reference'];
			$object->id_second_wine = $product['id_second_wine'];
			$object->id_category_default = (int)$id_category_default;
			$object->id_tax_rules_group = (int)$id_tax_rules_group;
			$object->active = (int)$product['active'];
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
			$object->save();

			// StockAvailable::setProductOutOfStock((int)$object->id, $product['out_of_stock'], 1);
			StockAvailable::setQuantity((int)$object->id, 0, $product['quantity']);

			// CATEGORIES
			// $categories = array('2');
			// if (isset($product['categories']))
			// {
			// 	foreach ($product['categories'] as $cat)
			// 	{
			// 		if (empty($cat))
			// 			continue;

			// 		$id_cat = Db::getInstance()->getValue("SELECT `id_category` FROM `" . _DB_PREFIX_ . "category_lang` WHERE `id_lang`='1' AND `name`='" . pSQL($cat) . "'");

			// 		if ($id_cat === false)
			// 		{
			// 			$category = new Category();
			// 			$category->name[1] = $cat;
			// 			$category->id_parent = 2;
			// 			$category->link_rewrite[1] = $this->module->toNurl($cat);
			// 			$category->add();
			// 			$id_cat = $category->id;
			// 		}

			// 		$categories[] = $id_cat;
			// 	}	
			// }

			// if ($upd)
			// 	$object->updateCategories($categories, true);
			// else
			// 	$object->addToCategories($categories);

			$categories = array('2');
			if (isset($product['categories']))
			{
				foreach ($product['categories'] as $cat)
				{
					$arr = explode("|", $cat);
					$id_category = $arr[0];
					$name_category = $arr[1];

					if (empty($cat))
						continue;

					$id_cat = Db::getInstance()->getValue("SELECT `id_category` FROM `" . _DB_PREFIX_ . "category` WHERE `id_category_dubos`='" . pSQL($id_category) . "'");

					if ($id_cat === false)
					{
						$category = new Category();
						$category->name[1] = $name_category;
						$category->id_parent = 2;
						$category->link_rewrite[1] = $this->module->toNurl($name_category);
						$category->id_category_dubos = $id_category;
						$category->add();
						$id_cat = $category->id;
					}

					$categories[] = $id_cat;
				}
			}

			if ($upd)
				$object->updateCategories($categories, true);
			else
				$object->addToCategories($categories);

			// PICTOGRAM

			Db::getInstance()->execute("DELETE FROM `" . _DB_PREFIX_ . "pictogram_product` WHERE `id_product`='" . pSQL($object->id) . "'");

			if (isset($product['pictogram']) && count($product['pictogram']) > 0)
			foreach ($product['pictogram'] as $picto)
			{
				$id_picto = Db::getInstance()->getValue("SELECT `id_pictogram` FROM `" . _DB_PREFIX_ . "pictogram` WHERE `slug`='" . pSQL(trim($picto)) . "'");

				if ($id_picto === false)
				{
					Db::getInstance()->insert('pictogram', array('slug' => pSQL(trim($picto))));
					$id_picto = Db::getInstance()->Insert_ID();
				}

				Db::getInstance()->insert('pictogram_product', array('id_product' => (int)$object->id, 'id_pictogram' => (int)$id_picto));
			}

			// FOODANDWINE

			Db::getInstance()->execute("DELETE FROM `" . _DB_PREFIX_ . "foodandwine_product` WHERE `id_product`='" . pSQL($object->id) . "'");

			if (isset($product['features']) && count($product['features']) > 0 && count($product['features'][0]) > 0)
			foreach ($product['foodandwine'] as $picto)
			{
				$id_picto = Db::getInstance()->getValue("SELECT `id_foodandwine` FROM `" . _DB_PREFIX_ . "foodandwine` WHERE `slug`='" . pSQL(trim($picto)) . "'");

				if ($id_picto === false)
				{
					Db::getInstance()->insert('foodandwine', array('slug' => pSQL(trim($picto))));
					$id_picto = Db::getInstance()->Insert_ID();
				}

				Db::getInstance()->insert('foodandwine_product', array('id_product' => (int)$object->id, 'id_foodandwine' => (int)$id_picto));
			}

			// FEATURES 

			$object->deleteProductFeatures();

			if (isset($product['features']) && count($product['features']) > 0 && count($product['features'][0]) > 0)
			{
				foreach ($product['features'][0] as $feat)
				{
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

						if (trim($feat['feature']) == 'Cépages')
							$value = preg_replace('@([0-9]{1,3}%\s)@', '', $value);

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

						Db::getInstance()->insert('feature_product', array('id_feature' => $id_feat, 'id_product' => $object->id, 'id_feature_value' => $id_feature_value));
					}

					$values = implode(', ', $feat['value']);
					if ($feat['feature'] == 'Cépages')
						$object->grape[1] = $values;
					else if ($feat['feature'] == 'Récompenses')
						$object->reward[1] = $values;
					else if ($feat['feature'] == 'Notation')
						$object->notation[1] = $values;

					$object->update();
				}
			}

			// ATTRIBUTES AND COMBINATIONS

			// Get specific price for combinations if update and before delete
			$sp_combination = array();
			// $qty_combination = array();
			if (isset($product['attributes']) && count($product['attributes']) > 0 && count($product['attributes'][0]) > 0)
			foreach ($product['attributes'][0] as $key => $attr)
			{

				$attr_id = Db::getInstance()->getValue("SELECT `id_product_attribute` FROM `" . _DB_PREFIX_ . "product_attribute` WHERE `id_product_attribute_dubos`='" . pSQL($attr['id_product_attribute']) . "' AND `id_packaging`='" . pSQL($attr['id_conditionnement']) . "'");

				$sp = Db::getInstance()->getRow("SELECT * FROM `" . _DB_PREFIX_ . "specific_price` WHERE `id_product_attribute`='" . pSQL($attr_id) . "'");

				if ($attr_id && $sp)
					$sp_combination[$attr['id_product_attribute'] . '|' . $attr['id_conditionnement']] = $sp;

				// if (!$object->wine)
				// 	@$qty_combination[$attr['id_product_attribute']] += $attr['quantity'];
			}

			// delete combinations
			$object->deleteProductAttributes();

			if (isset($product['attributes']) && count($product['attributes']) > 0 && count($product['attributes'][0]) > 0)
			{
				$pictures_association = array();
				foreach ($product['attributes'][0] as $key => $attr)
				{
					$ex = explode("|", $attr['name']);
					$id_attributes = array();

					// quantity pour les non primeur de meme decli
					foreach ($ex as $at)
					{
						$a = explode(':', $at);

						if (empty($a[0]) || empty($a[1]))
							continue;

						if (!$object->wine)
						{
							if(preg_match('@Conditionnement@i', $a[0]))
								continue;

							// if ($product['cache_default_attribute'] != $attr['id_product_attribute'] . '|' . $attr['id_conditionnement'])
							// 	continue;

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
							$Attribute = new Attribute();
							$Attribute->name[1] = trim($a[1]);
							$Attribute->id_attribute_group = $id_attribute_group;
							$Attribute->add();
							$id_attribute = $Attribute->id;
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
						$combination->active = $attr['active'];
						// $combination->price = !$object->wine ? str_replace(',','.', $attr['price']) :  str_replace(',','.', $attr['price']+$attr['packaging_price']);
						$combination->price = !$object->wine ? str_replace(',','.', $attr['price']) :  str_replace(',','.', $attr['price']);
						// $combination->packaging_price = $object->wine ? str_replace(',','.', $attr['packaging_price']) : '0';
						$combination->packaging_price = 0;
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

			// IMAGES

			$object->deleteImages();

			if (isset($product['images']) && isset($product['images'][0]) && count($product['images'][0]) > 0)
			{
				foreach ($product['images'][0] as $img)
				{
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

		// echo 'OK' . "\n";
		// echo '<pre>';
		// print_r($data);
		// die();
		return true;
	}

	public function addSpecifiquePrice($data)
	{
		// echo '<pre>';
		// print_r($data);
		// die();

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
		// echo '<pre>';
		// print_r($data);
		// die();
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
				$product = Db::getInstance()->getRow("
					SELECT PA.`id_product_attribute`, PA.`id_product`
					FROM `" . _DB_PREFIX_ . "product_attribute` PA
					WHERE PA.`id_product_attribute_dubos`='" . pSQL($value['id_product_attribute']) . "'
				");

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
					)
				);
			}
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