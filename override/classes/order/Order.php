<?php


class Order extends OrderCore
{
    public $id_order_dubos;

    public function __construct($id = null, $id_lang = null)
    {
        Order::$definition['fields']['id_order_dubos'] = array('type' => self::TYPE_STRING, 'validate' => 'isString');

        parent::__construct($id, $id_lang);
    }

    public static function getIdByDubos($id_order_dubos)
    {
        return Db::getInstance()->getValue("SELECT `id_order` FROM `" . _DB_PREFIX_ . "orders` WHERE `id_order_dubos`='" . pSQL($id_order_dubos) . "'");
    }
/*
    public function add($autodate = true, $null_values = false)
    {
        // Generate GUID
        $data    = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        $guid    = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        // Save GUID
        $this->id_order_dubos = $guid;

        parent::add($autodate, $null_values);

        // if (\Module::isInstalled('wservices') && \Module::isEnabled('wservices'))
        // {
        //     $wservices = \Module::getInstanceByName('wservices');
        //     $wservices->publishOrder($this, 'INS');
        // }

        return true;
    }
*/
}