<?php


class Customer extends CustomerCore
{
	public $id_customer_dubos;

	public function __construct($id_customer = null, $idLang = null, $idShop = null)
    {
        Customer::$definition['fields']['id_customer_dubos'] = array('type' => self::TYPE_INT, 'validate' => 'isInt');
        
        parent::__construct($id_customer, $idLang, $idShop);
    }

	public static function getIdByDubos($id_customer_dubos)
	{
		return Db::getInstance()->getValue("SELECT `id_customer` FROM `" . _DB_PREFIX_ . "customer` WHERE `id_customer_dubos`='" . pSQL($id_customer_dubos) . "'");
	}

	public function add($autodate = true, $null_values = false)
    {        
        parent::add($autodate, $null_values);
        Module::getInstanceByName('wservices')->publishCustomer($this, 'INS');
        return true;
    }

    public function update($null_values = false)
    {       
        parent::update($null_values);
        Module::getInstanceByName('wservices')->publishCustomer($this, 'UPD');
        return true;
    }

	public function delete()
    {
    	$this->active = '9';
        Module::getInstanceByName('wservices')->publishCustomer($this, 'UPD');
        parent::delete();
        return true;
    }
}