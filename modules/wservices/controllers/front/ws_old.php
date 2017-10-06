<?php

ini_set('default_socket_timeout', -1);

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
			$current_customer = new Customer($customer['id_customer']);
			$customer['addresses'] = $current_customer->getSimpleAddresses(Context::getContext()->language->id);
		}
		echo json_encode($customers, JSON_UNESCAPED_UNICODE);
		die();
	}

	public function getOrderById($id_order)
	{
		$order = new Order($id_order);
		echo '<pre>';
		print_r($order);
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

		echo '<pre>';
		print_r($product);
		// echo json_encode($product, JSON_UNESCAPED_UNICODE);
		// file_put_contents('product.txt', json_encode($product, JSON_UNESCAPED_UNICODE));
		die();
	}

	public function receiver()
	{
		// $_POST['data'] = '{"NoJSON":"156","IdTransaction":"16e5788deac84a6cbb008370914b9c22","Modèle":"PRD","Type":"INS","DateTransaction":"2017-06-13","Transaction":[{"produits":{"M2012000761":{"reference":"M2012000761","id_second_wine":"","id_category_default":"","cache_default_attribute":"36427|125","active":"1","name":"CHEVALIER DE LASCOMBES","wine":"0","wine_date":"2014-01-21 12:40:32","wine_delivery":"2015-01-31 00:00:00","available_date":"","quantity":"0","shop_quantity":"0","price":"0","id_tax_rules_group":"","available_later":"Commande en cours...","property_picture":"","calling_picture_big":"","calling_picture_small":"","calling":"","property":"Aux environs de 1625, la famille LASCOMBES implante cette maison bourgeoise constituée d\'un simple rez-de-chaussée, dont subsistent aujourd\'hui les salons actuels et une partie de l\'aile ouest du Château. C\'est dans ces murs que nacquit le Chevalier Antoine de Lascombes que son second vin `CHEVALIER DE LASCOMBES` commémore. rnLe Château tel qu\'il est aujourd\'hui a été édifié entre 1875 et 1880 en venant s\'adosser au nord sur la construction existante.rnAprès différents propriétaires qui ne contribuèrent guère à sa réputation, il devient dans les années 50 la propriété d\'Alexis LICHINE et c\'est sous son impulsion que LASCOMBES entamera sa restructuration. rnSuite à la récente acquisition par le fond d\'investissement américain Colony Capital, la renommée du château ne pourra encore que grandir.rnDans le numéro du 4 avril 1838 du journal ` Le Producteur `, nous lisons ces lignes qui consacrent l’ancienneté de cette réputation : ` Le domaine (Lascombes) possède de vieilles vignes et de très bons cépages sur un terrain précieux. Ses vins sont réputés de qualité si supérieure qu’ils peuvent rivaliser avec le château Margaux `. Les vins produits par Lascombes se trouvent être parmi les plus demandés et appréciés sur le marché anglo-saxon, et notamment aux États-Unis, où le château Lascombes fait figure d’ambassadeur des vins français. Après divers achats et remembrements, la propriété commande 50 hectares, lui permettant de produire près de 244 tonneaux. Des chais, récemment construits, permettent un stockage de plus de 800 barriques au sol, et ils sont certainement parmi les plus beaux du Médoc. Des visiteurs du monde entier viennent en admirer les installations. Depuis 1986, un cuvier impressionnant de 32 cuves a mis les techniques modernes au service des traditions médocaines. En effet, si l’électronique régule les températures de fermentation, des hommes et des femmes, comme autrefois, trient à la main les raisins coupés par les 200 vendangeurs.","description":"","description_short":"Chevalier de Lascombes","pictogram":["Notation"],"images":[{"1":{"cle":"1","url":"chevalier-de-lascombes.jpg","legend":"CHEVALIER DE LASCOMBES","cover":"1","value":["36427","36960","37583"]}}],"attributes":[{"1":{"cle":"1","id_product_attribute":"36427","reference":"M2012000761","shop_quantity":"12","name":"Format : Bouteille 0,75 L | Conditionnement : Caisse bois de 2","active":"1","price":"22,0833","minimal_quantity":"12","quantity":"117","available_date":"","packaging_price":"1,22","id_conditionnement":"26"},"2":{"cle":"2","id_product_attribute":"36427","reference":"M2012000761","shop_quantity":"12","name":"Format : Bouteille 0,75 L | Conditionnement : Caisse bois de 12","active":"1","price":"22,0833","minimal_quantity":"12","quantity":"117","available_date":"","packaging_price":"0","id_conditionnement":"125"},"3":{"cle":"3","id_product_attribute":"36427","reference":"M2012000761","shop_quantity":"12","name":"Format : Bouteille 0,75 L | Conditionnement : Caisse bois de 6","active":"1","price":"22,0833","minimal_quantity":"12","quantity":"117","available_date":"","packaging_price":"0,46","id_conditionnement":"128"},"4":{"cle":"4","id_product_attribute":"36427","reference":"M2012000761","shop_quantity":"12","name":"Format : Bouteille 0,75 L | Conditionnement : Caisse bois de 3","active":"1","price":"22,0833","minimal_quantity":"12","quantity":"117","available_date":"","packaging_price":"0,77","id_conditionnement":"129"},"5":{"cle":"5","id_product_attribute":"36427","reference":"M2012000761","shop_quantity":"12","name":"Format : Bouteille 0,75 L | Conditionnement : Caisse bois de 1","active":"1","price":"22,0833","minimal_quantity":"12","quantity":"117","available_date":"","packaging_price":"1,89","id_conditionnement":"168"},"6":{"cle":"6","id_product_attribute":"36427","reference":"M2012000761","shop_quantity":"12","name":"Format : Bouteille 0,75 L | Conditionnement : Carton de 12","active":"1","price":"22,0833","minimal_quantity":"12","quantity":"117","available_date":"","packaging_price":"0","id_conditionnement":"174"},"7":{"cle":"7","id_product_attribute":"36427","reference":"M2012000761","shop_quantity":"12","name":"Format : Bouteille 0,75 L | Conditionnement : Carton de 6","active":"1","price":"22,0833","minimal_quantity":"12","quantity":"117","available_date":"","packaging_price":"0","id_conditionnement":"183"},"8":{"cle":"8","id_product_attribute":"36427","reference":"M2012000761","shop_quantity":"12","name":"Format : Bouteille 0,75 L | Conditionnement : Carton de 1","active":"1","price":"22,0833","minimal_quantity":"12","quantity":"117","available_date":"","packaging_price":"0","id_conditionnement":"298"},"9":{"cle":"9","id_product_attribute":"36960","reference":"M2012000761","shop_quantity":"0","name":"Format : Magnum 1,50 L | Conditionnement : Caisse bois de 6","active":"9","price":"32,4917","minimal_quantity":"12","quantity":"0","available_date":"","packaging_price":"0,46","id_conditionnement":"127"},"10":{"cle":"10","id_product_attribute":"36960","reference":"M2012000761","shop_quantity":"0","name":"Format : Magnum 1,50 L | Conditionnement : Caisse bois de 3","active":"9","price":"32,4917","minimal_quantity":"12","quantity":"0","available_date":"","packaging_price":"2,05","id_conditionnement":"130"},"11":{"cle":"11","id_product_attribute":"36960","reference":"M2012000761","shop_quantity":"0","name":"Format : Magnum 1,50 L | Conditionnement : Caisse bois de 1","active":"9","price":"32,4917","minimal_quantity":"12","quantity":"0","available_date":"","packaging_price":"4,6","id_conditionnement":"131"},"12":{"cle":"12","id_product_attribute":"36960","reference":"M2012000761","shop_quantity":"0","name":"Format : Magnum 1,50 L | Conditionnement : Carton de 6","active":"9","price":"32,4917","minimal_quantity":"12","quantity":"0","available_date":"","packaging_price":"0","id_conditionnement":"181"},"13":{"cle":"13","id_product_attribute":"37583","reference":"M2012000761","shop_quantity":"0","name":"Format : Demie 0,375 L | Conditionnement : Caisse bois de 24","active":"9","price":"11,25","minimal_quantity":"12","quantity":"0","available_date":"","packaging_price":"0,6","id_conditionnement":"126"},"14":{"cle":"14","id_product_attribute":"37583","reference":"M2012000761","shop_quantity":"0","name":"Format : Demie 0,375 L | Conditionnement : Caisse bois de 12","active":"9","price":"11,25","minimal_quantity":"12","quantity":"0","available_date":"","packaging_price":"0,6","id_conditionnement":"166"},"15":{"cle":"15","id_product_attribute":"37583","reference":"M2012000761","shop_quantity":"0","name":"Format : Demie 0,375 L | Conditionnement : Carton de 24","active":"9","price":"11,25","minimal_quantity":"12","quantity":"0","available_date":"","packaging_price":"0,6","id_conditionnement":"177"}}],"features":[{"1":{"cle":"1","feature":"Apogée","value":[""]},"2":{"cle":"2","feature":"Appellation","value":["Margaux"]},"3":{"cle":"3","feature":"Cépages","value":[""]},"4":{"cle":"4","feature":"Classification","value":["2ème vin"]},"5":{"cle":"5","feature":"Couleur","value":["Rouge"]},"6":{"cle":"6","feature":"Millésime","value":["2012"]},"7":{"cle":"7","feature":"Mode de culture","value":[""]},"8":{"cle":"8","feature":"Niveau de garde","value":[""]},"9":{"cle":"9","feature":"Notations","value":["Revue du Vin de France 13/20","Decanter 16"]},"10":{"cle":"10","feature":"Récompenses","value":[""]},"11":{"cle":"11","feature":"Région","value":["Bordeaux"]},"12":{"cle":"12","feature":"Température de service","value":[""]}}]}}}]}';

		// $_POST['data'] = '{"NoJSON":"39","IdTransaction":"ed65186cea587c393ce4475de39cf70b","Modèle":"PRD","Type":"UPD","DateTransaction":"2017-02-22 17:05:33","Transaction":[{"produits":{"M2008002203":{"reference":"M2008002203","id_second_wine":"M2008001287","id_category_default":"Bordeaux","cache_default_attribute":"28855|125","active":"1","name":"Château BEL AIR de SIRAN UPD","wine":"0","wine_date":"","wine_delivery":"","available_date":"2017-04-30 00:00:00","quantity":"0","shop_quantity":"0","price":"0","id_tax_rules_group":"NOR","available_later":"Commande en cours...","property_picture":"chateau-de-myrat.jpg","calling_picture_big":"bel-air-siran.jpg","calling_picture_small":"bel-air-siran.jpg","calling":"Commentaires appellation ","property":"Historique propriété ","description":"Description Description ","description_short":"Bel Air de Siran","categories":["Bio","Bordeaux","Primeurs","Rouge"],"pictogram":["Notation","Récompenses concours","Vin biologique"],"foodandwine":["Charcuterie","Poisson en sauce ","Poisson fumé "],"images":[{"1":{"cle":"1","url":"bel-air-siran.jpg","legend":"BEL AIR DE SIRAN","cover":"1","value":["28855"]},"2":{"cle":"2","url":"bel-air-siran.jpg","legend":"BEL AIR DE SIRAN","cover":"","value":["28855"]},"3":{"cle":"3","url":"bel-air-siran.jpg","legend":"BEL AIR DE SIRAN","cover":"","value":["28855"]}}],"attributes":[{"2":{"cle":"2","id_product_attribute":"28855","reference":"M2008002203","shop_quantity":"17","name":"Format : Bouteille 0,75 L | Conditionnement : Caisse bois de 12","active":"1","price":"9,4167","minimal_quantity":"12","quantity":"26","available_date":"","packaging_price":"0","id_conditionnement":"125"},"1":{"cle":"1","id_product_attribute":"28855","reference":"M2008002203","shop_quantity":"17","name":"Format : Bouteille 0,75 L | Conditionnement : Caisse bois de 6","active":"1","price":"9,4167","minimal_quantity":"12","quantity":"26","available_date":"","packaging_price":"0,46","id_conditionnement":"128"}}],"features":[{"1":{"cle":"1","feature":"Apogée","value":["2010"]},"2":{"cle":"2","feature":"Appellation","value":["Margaux"]},"3":{"cle":"3","feature":"Cépages","value":["80% Cabernet","20% Merlot"]},"4":{"cle":"4","feature":"Classification","value":["Cru classé"]},"5":{"cle":"5","feature":"Couleur","value":["Rouge"]},"6":{"cle":"6","feature":"Millésime","value":["2008"]},"7":{"cle":"7","feature":"Mode de culture","value":["Biologique"]},"8":{"cle":"8","feature":"Niveau de garde","value":["De 2010 à 2015"]},"9":{"cle":"9","feature":"Notations","value":["Wine spectator 98","Robert Parker 98+"]},"10":{"cle":"10","feature":"Récompenses","value":["Coup de coeur Guide Hachette","Concours de Bordeaux Vins d’Aquitaine "]},"11":{"cle":"11","feature":"Région","value":["Bordeaux"]},"12":{"cle":"12","feature":"Température de service","value":["11-12°C"]}}]}}}]}';

		// $_POST['data'] = '{"NoJSON":"165","IdTransaction":"b6c6e6899b734447b90620767995f280","Modèle":"TRF","Type":"INS","DateTransaction":"2017-06-13","Transaction":[{"tarif":{"1":{"cle":"1","id_product_attribute":"36427|26","id_product":"M2012000761","reduction_type":"percentage","reduction_tax":"1","reduction":"10","from":"2016-05-27 14:21:19","to":"","price":"0","id_customer":"0","from_quantity":"0"},"2":{"cle":"2","id_product_attribute":"36427|125","id_product":"M2012000761","reduction_type":"percentage","reduction_tax":"1","reduction":"10","from":"2016-05-27 14:21:19","to":"","price":"0","id_customer":"0","from_quantity":"0"},"3":{"cle":"3","id_product_attribute":"36427|128","id_product":"M2012000761","reduction_type":"percentage","reduction_tax":"1","reduction":"10","from":"2016-05-27 14:21:19","to":"","price":"0","id_customer":"0","from_quantity":"0"},"4":{"cle":"4","id_product_attribute":"36427|129","id_product":"M2012000761","reduction_type":"percentage","reduction_tax":"1","reduction":"10","from":"2016-05-27 14:21:19","to":"","price":"0","id_customer":"0","from_quantity":"0"},"5":{"cle":"5","id_product_attribute":"36427|168","id_product":"M2012000761","reduction_type":"percentage","reduction_tax":"1","reduction":"10","from":"2016-05-27 14:21:19","to":"","price":"0","id_customer":"0","from_quantity":"0"},"6":{"cle":"6","id_product_attribute":"36427|174","id_product":"M2012000761","reduction_type":"percentage","reduction_tax":"1","reduction":"10","from":"2016-05-27 14:21:19","to":"","price":"0","id_customer":"0","from_quantity":"0"},"7":{"cle":"7","id_product_attribute":"36427|183","id_product":"M2012000761","reduction_type":"percentage","reduction_tax":"1","reduction":"10","from":"2016-05-27 14:21:19","to":"","price":"0","id_customer":"0","from_quantity":"0"},"8":{"cle":"8","id_product_attribute":"36427|298","id_product":"M2012000761","reduction_type":"percentage","reduction_tax":"1","reduction":"10","from":"2016-05-27 14:21:19","to":"","price":"0","id_customer":"0","from_quantity":"0"}}}]}';

		// $_POST['data'] = '{"NoJSON":"98","IdTransaction":"ba945f94721f456a9cc95a94cd34f90d","Modèle":"CLT","Type":"INS","DateTransaction":"2017-05-19","Transaction":[{"clients":{"7052":{"notiers":"7052","lastname":"GUILLOU","firstname":"Ronan","email":"ronan_guillou@hotmail.com","id_gender":"1","passwd":"","birthday":"1977-01-19 00:00:00","active":"1","optin":"0","newsletter":"0","siret":"   ","ape":"","adresses":[{"24298":{"noadresse":"24298","alias":"BERGERAC FRANCE","address1":"95, rue Anatole FRANCE","address2":"","city":"BERGERAC","postcode":"24100","phone":"","phone_mobile":"","company":"","firstname":"Ronan","lastname":"GUILLOU","other":"","active":"1","id_country":"FR","rank":""},"46317":{"noadresse":"46317","alias":"TULLE FRANCE","address1":"Rue du 19 juin 1944","address2":"","city":"TULLE","postcode":"19000","phone":"","phone_mobile":"","company":"INITIO","firstname":"Ronan","lastname":"GUILLOU","other":"","active":"1","id_country":"FR","rank":""},"46317":{"noadresse":"46317","alias":"TULLE FRANCE","address1":"Rue du 19 juin 1944","address2":"","city":"TULLE","postcode":"19000","phone":"","phone_mobile":"","company":"INITIO","firstname":"Ronan","lastname":"GUILLOU","other":"","active":"1","id_country":"FR","rank":""},"46317":{"noadresse":"46317","alias":"TULLE FRANCE","address1":"Rue du 19 juin 1944","address2":"","city":"TULLE","postcode":"19000","phone":"","phone_mobile":"","company":"INITIO","firstname":"Ronan","lastname":"GUILLOU","other":"","active":"1","id_country":"FR","rank":""},"46317":{"noadresse":"46317","alias":"TULLE FRANCE","address1":"Rue du 19 juin 1944","address2":"","city":"TULLE","postcode":"19000","phone":"","phone_mobile":"","company":"INITIO","firstname":"Ronan","lastname":"GUILLOU","other":"","active":"1","id_country":"FR","rank":""}}]},"7106":{"notiers":"7106","lastname":"GUILLOU","firstname":"Ronan","email":"ronan_guillou@hotmail.com","id_gender":"1","passwd":"","birthday":"1977-01-19 00:00:00","active":"1","optin":"0","newsletter":"0","siret":"","ape":"","adresses":[{"6578":{"noadresse":"6578","alias":"24100 BERGERAC","address1":"95, RUE ANATOLE FRANCE","address2":"","city":"BERGERAC","postcode":"24100","phone":"0553239275","phone_mobile":"","company":"Mr GUILLOU Ronan","firstname":"Ronan","lastname":"GUILLOU","other":"","active":"1","id_country":"FR","rank":""},"6579":{"noadresse":"6579","alias":"24100 BERGERAC","address1":"95, RUE ANATOLE FRANCE","address2":"","city":"BERGERAC","postcode":"24100","phone":"0553239275","phone_mobile":"","company":"Mr GUILLOU Ronan","firstname":"Ronan","lastname":"GUILLOU","other":"","active":"0","id_country":"FR","rank":""},"6578":{"noadresse":"6578","alias":"24100 BERGERAC","address1":"95, RUE ANATOLE FRANCE","address2":"","city":"BERGERAC","postcode":"24100","phone":"0553239275","phone_mobile":"","company":"Mr GUILLOU Ronan","firstname":"Ronan","lastname":"GUILLOU","other":"","active":"1","id_country":"FR","rank":""},"31981":{"noadresse":"31981","alias":"BERGERAC FRANCE","address1":"1, rue paul bert","address2":"","city":"BERGERAC","postcode":"24100","phone":"","phone_mobile":"","company":"","firstname":"Ronan","lastname":"GUILLOU","other":"","active":"1","id_country":"FR","rank":""},"31981":{"noadresse":"31981","alias":"BERGERAC FRANCE","address1":"1, rue paul bert","address2":"","city":"BERGERAC","postcode":"24100","phone":"","phone_mobile":"","company":"","firstname":"Ronan","lastname":"GUILLOU","other":"","active":"1","id_country":"FR","rank":""}}]},"16262":{"notiers":"16262","lastname":"GUILLOU","firstname":"Ronan","email":"ronan_guillou@hotmail.com","id_gender":"1","passwd":"","birthday":"1977-01-19 00:00:00","active":"1","optin":"0","newsletter":"0","siret":"","ape":"",]},"27005":{"notiers":"27005","lastname":"GUILLOU","firstname":"Ronan","email":"ronan_guillou@hotmail.com","id_gender":"1","passwd":"","birthday":"1977-01-19 00:00:00","active":"1","optin":"0","newsletter":"0","siret":"","ape":"",]}}}]}';

		if (!isset($_POST['data']))
			return false;

		$data = json_decode($_POST['data'], true);

		if (is_null($data) || $data === false)
		{
			// envoi de mail a wisy et yateo
			die('Not a valid json string');
		}
		
		// $data = $data[0];

		if (!isset($data['IdTransaction']))
			die('Transaction not set.');

		// if ($this->module->TransactionExists($data['NoJSON']))
		// 	die('Transaction already exists.');

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

		$this->module->add($data, 'get');
		die();
	}

	public function addCustomer($data)
	{
		// foreach ($data as $k_client => $clients)
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
			$object->property[1] = $this->rDQuote($product['property']);
			$object->description[1] = $this->rDQuote($product['description']);
			$object->description_short[1] = $this->rDQuote($product['description_short']);
			$object->save();

			// StockAvailable::setProductOutOfStock((int)$object->id, $product['out_of_stock'], 1);
			StockAvailable::setQuantity((int)$object->id, 0, $product['quantity']);

			// CATEGORIES
			$categories = array('2');
			if (isset($product['categories']))
			{
				foreach ($product['categories'] as $cat)
				{
					if (empty($cat))
						continue;

					$id_cat = Db::getInstance()->getValue("SELECT `id_category` FROM `" . _DB_PREFIX_ . "category_lang` WHERE `id_lang`='1' AND `name`='" . pSQL($cat) . "'");

					if ($id_cat === false)
					{
						$category = new Category();
						$category->name[1] = $cat;
						$category->id_parent = 2;
						$category->link_rewrite[1] = $this->module->toNurl($cat);
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
				$id_picto = Db::getInstance()->getValue("SELECT `id_pictogram` FROM `" . _DB_PREFIX_ . "pictogram` WHERE `slug`='" . pSQL($picto) . "'");

				if ($id_picto === false)
				{
					Db::getInstance()->insert('pictogram', array('slug' => pSQL($picto)));
					$id_picto = Db::getInstance()->Insert_ID();
				}

				Db::getInstance()->insert('pictogram_product', array('id_product' => (int)$object->id, 'id_pictogram' => (int)$id_picto));
			}

			// FOODANDWINE

			Db::getInstance()->execute("DELETE FROM `" . _DB_PREFIX_ . "foodandwine_product` WHERE `id_product`='" . pSQL($object->id) . "'");

			if (isset($product['foodandwine']) && count($product['foodandwine']) > 0)
			foreach ($product['foodandwine'] as $picto)
			{
				$id_picto = Db::getInstance()->getValue("SELECT `id_foodandwine` FROM `" . _DB_PREFIX_ . "foodandwine` WHERE `slug`='" . pSQL($picto) . "'");

				if ($id_picto === false)
				{
					Db::getInstance()->insert('foodandwine', array('slug' => pSQL($picto)));
					$id_picto = Db::getInstance()->Insert_ID();
				}

				Db::getInstance()->insert('foodandwine_product', array('id_product' => (int)$object->id, 'id_foodandwine' => (int)$id_picto));
			}

			// FEATURES 

			$object->deleteProductFeatures();

			if (isset($product['features']) && count($product['features'][0]) > 0)
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
			if (isset($product['attributes']) && count($product['attributes'][0]) > 0)
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

			if (isset($product['attributes']) && count($product['attributes'][0]) > 0)
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
						$combination->price = !$object->wine ? str_replace(',','.', $attr['price']) :  str_replace(',','.', $attr['price']+$attr['packaging_price']);
						$combination->packaging_price = $object->wine ? str_replace(',','.', $attr['packaging_price']) : '0';
						$combination->minimal_quantity = $attr['minimal_quantity'];
						$combination->quantity = $attr['quantity'];
						$combination->available_date = $attr['available_date'];
						$combination->id_product_attribute_dubos = $attr['id_product_attribute'];
						$combination->default_on = $product['cache_default_attribute'] == $attr['id_product_attribute'] . '|' . $attr['id_conditionnement'] ? 1 : 0;
						$combination->id_packaging = $attr['id_conditionnement'];
						$combination->save();

						$combination->setAttributes($id_attributes);

						// StockAvailable::setProductOutOfStock((int)$object->id, $attr['out_of_stock'], null, $combination->id);
						StockAvailable::setQuantity((int)$object->id, $combination->id, isset($qty_combination[$attr['id_product_attribute']]) ? $qty_combination[$attr['id_product_attribute']] : $attr['quantity']);

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
			foreach ($product['images'][0] as $img)
			{
				if (empty($img['url']) || !file_exists($this->images_url.'chateau-capbern-gasqueton-002.jpg'))
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

		echo 'OK' . "\n";
		// echo '<pre>';
		// print_r($data);
		// die();
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
		die();
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