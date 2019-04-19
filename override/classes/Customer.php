<?php


class Customer extends CustomerCore
{
    public $id_customer_dubos;

    public function __construct($id_customer = null, $idLang = null, $idShop = null)
    {
        Customer::$definition['fields']['id_customer_dubos'] = array('type' => self::TYPE_STRING, 'validate' => 'isString');

        parent::__construct($id_customer, $idLang, $idShop);
    }

    public static function getIdByDubos($id_customer_dubos)
    {
        return Db::getInstance()->getValue("SELECT `id_customer` FROM `" . _DB_PREFIX_ . "customer` WHERE `id_customer_dubos`='" . pSQL($id_customer_dubos) . "'");
    }

    public function add($autodate = true, $null_values = false)
    {
        // Generate GUID
        $data    = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        $guid    = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        // Save GUID
        $this->id_customer_dubos = $guid;

        parent::add($autodate, $null_values);

        if (\Module::isInstalled('wservices') && \Module::isEnabled('wservices'))
        {
            $wservices = \Module::getInstanceByName('wservices');
            $wservices->publishCustomer($this, 'INS');
        }

        return true;
    }

    public function update($null_values = false)
    {       
        parent::update($null_values);

        if (\Module::isInstalled('wservices') && \Module::isEnabled('wservices'))
        {
            $wservices = \Module::getInstanceByName('wservices');
            $wservices->publishCustomer($this, 'UPD');
        }

        return true;
    }

    public function delete()
    {
        $this->active = '9';

        if (\Module::isInstalled('wservices') && \Module::isEnabled('wservices'))
        {
            $wservices = \Module::getInstanceByName('wservices');
            $wservices->publishCustomer($this, 'UPD');
        }

        parent::delete();

        return true;
    }
}