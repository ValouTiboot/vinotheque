<?php

if (!defined('_PS_VERSION_'))
	exit;

ini_set('default_socket_timeout', -1);

require_once(_PS_MODULE_DIR_ . 'wservices/classes/RedisConnect.php');

class Wservices extends Module
{
	public function __construct()
	{
		$this->name = 'wservices';
		$this->tab = 'front_office_features';
		$this->version = '1.0.0';
		$this->author = 'Yateo - Valentin THIBAULT';
		$this->need_instance = 0;
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
		$this->module_table = 'wservices';

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
    	return Db::getInstance()->execute("CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . $this->module_table . "` (
			  `id_wservices` int(11) NOT NULL AUTO_INCREMENT,
			  `transaction` varchar(255) NOT NULL,
			  `model` varchar(255) NOT NULL,
			  `type` varchar(255) NOT NULL,
              `transaction` TEXT NOT NULL,
			  `way` varchar(10) NOT NULL,
			  `date_add` datetime NOT NULL,
			  `date_upd` datetime NOT NULL,
			  PRIMARY KEY (`id_wservices`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");
    }

    public function add($data, $way)
    {
    	Db::getInstance()->insert($this->module_table, array(
    		'id_json' => pSQL($data['NoJSON']),
    		'model' => pSQL($data['Modèle']),
            'type' => pSQL($data['Type']),
    		'transaction' => pSQL(json_encode($data, JSON_UNESCAPED_UNICODE)),
    		'way' => pSQL($way),
    		'date_add' => date('Y-m-d H:i:s'),
    		'date_upd' => date('Y-m-d H:i:s'),
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
        return file_put_contents(_PS_MODULE_DIR_ . 'wservices/logs/' . $data['NoJSON'] . '_' . $data['IdTransaction'] . '.txt', print_r($data, true));
    }

    public function genPassword($char = 8)
    {
    	$password = "";
    	$tab = array_merge(range('a','z'), range('A','Z'), range('0','9')); 
		$nb = count($tab); 
        
        for($i = 0; $i <= $char; $i++)
            $password .= $tab[rand(0,$nb-1)];
 
        return $password;
    }

    public function guidv4()
    {
        if (function_exists('openssl_random_pseudo_bytes') === false)
            return; 

        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // set bits 6-7 to 10
        
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public function TransactionExists($idJson)
    {
    	return Db::getInstance()->getValue("SELECT `id_wservices` FROM `" . _DB_PREFIX_ . $this->module_table . "` WHERE `id_json`='" . pSQL($idJson) . "'");
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


    /*
    *   Doit recuperer l'obejct Order dans le $params pour ensuite appeler une methode "publishOrder" qui enverra le JSON à redis
    **/
    public function hookActionObjectOrderAddAfter($params)
    {
        ;
    }

    public function publishOrder($order)
    {
        ;
    }

    public function deleteAddress($object)
    {
        $addresses = $this->getCustomerAddresses($object->id_customer);

        foreach ($addresses as &$address)
        if ($object->id == $address['id_address'])
            $address['active'] = '9';

        return $this->publishCustomer(new Customer($object->id_customer), 'UPD', $addresses);
    }

    public function publishCustomer($customer, $type, $addresses = array())
    {
        if (!count($addresses))
            $addresses = $this->getCustomerAddresses($customer->id);

        // Get customer's loyalty
        $consumed_points = 0;
        $acquired_points = 0;
        $remaining_points = 0;
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
            'NoJSON' => '',
            'IdTransaction' => md5(microtime()),
            'Modèle' => 'CLT',
            'Type' => $type,
            'DateTransaction' => date('Y-m-d H:i:s'),
            'Transaction' => array(
                '0' => array(
                    'clients' => array(
                        (!empty($customer->id_customer_dubos) ? $customer->id_customer_dubos : $customer->id) => array(
                            'notiers' => (!empty($customer->id_customer_dubos) ? $customer->id_customer_dubos : ''),
                            'lastname' => $customer->lastname,
                            'firstname' => $customer->firstname,
                            'email' => $customer->email,
                            'id_gender' => $customer->id_gender,
                            'passwd' => '',
                            'birthday' => ($customer->birthday == 'null' || is_null($customer->birthday) || $customer->birthday == '0000-00-00' ? '' : $customer->birthday),
                            'active' => $customer->active,
                            'optin' => $customer->optin,
                            'newsletter' => (!is_null($customer->newsletter) ? $customer->newsletter : ''),
                            'siret' => (!is_null($customer->siret) ? $customer->siret : ''),
                            'ape' => (!is_null($customer->ape) ? $customer->ape : ''),
                            'NbPointsConsommes' => (string)$consumed_points,
                            'NbPointsAcquits' => (string)$acquired_points,
                            'NbPointsRestants' => (string)$remaining_points,
                            'adresses' => $addresses
                        ),
                    ),
                ),
            )
        );

        // echo '<pre>';
        // print_r($trans);
        // die();
        $trans['NoJSON'] = $this->add($trans, 'set');

        return $this->publish($trans);
    }

    public static function getCustomerLoyalties($id_customer)
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
            $res['noadresse'] = !empty($res['noadresse']) ? $res['noadresse'] : '';
            $return[($res['noadresse'] != '' ? $res['noadresse'] : $res['id_address'])] = $res;
        }

        return $return;
    }

    public function publish($data)
    {
        $redis_connect = new RedisConnect();
        $redis = $redis_connect->connect();
        $redis->zAdd('mt:CC_Site1_' . $data['Modèle'], $data['NoJSON'], json_encode($data, JSON_UNESCAPED_UNICODE));
        $redis->publish('CC_Site1_' . $data['Modèle'], json_encode($data, JSON_UNESCAPED_UNICODE));
        $redis->close();

        return;
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
}