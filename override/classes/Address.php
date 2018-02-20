<?php

class Address extends AddressCore
{
	public $id_address_dubos;

	public $rank = 1;

    public function __construct($id_address = null, $idLang = null, $idShop = null)
    {
        Address::$definition['fields']['id_address_dubos'] = array('type' => self::TYPE_INT, 'validate' => 'isInt');
        Address::$definition['fields']['rank'] = array('type' => self::TYPE_INT, 'validate' => 'isInt');

        parent::__construct($id_address, $idLang, $idShop);
    }

	public function add($autodate = true, $null_values = false)
    {
        $rank = Db::getInstance()->getValue("SELECT `rank` FROM `" . _DB_PREFIX_ . "address` WHERE `id_customer`='" . pSQL($this->id_customer) . "' ORDER BY `rank` DESC");
        $this->rank = ($rank !== false ? (int)$rank+1 : 1);

        parent::add($autodate, $null_values);
	if (\Module::isInstalled('wservices') && \Module::isEnabled('wservices'))
        {
                $wservices = \Module::getInstanceByName('wservices');
	        $wservices->publishCustomer(new Customer($this->id_customer), 'INS');
	}

        return true;
    }

    public function update($null_values = false)
    {
        parent::update($null_values);
	if (\Module::isInstalled('wservices') && \Module::isEnabled('wservices'))
        {
                $wservices = \Module::getInstanceByName('wservices');
	        $wservices->publishCustomer(new Customer($this->id_customer), 'UPD');
	}

        return true;
    }

    public function delete()
    {
	if (\Module::isInstalled('wservices') && \Module::isEnabled('wservices'))
	{
		$wservices = \Module::getInstanceByName('wservices');
        	$wservices->deleteAddress($this);
	}

	parent::delete();
        return true;
    }
}
