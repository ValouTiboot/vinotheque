<?php


class OrderDetail extends OrderDetailCore
{
    public $id_order_detail_dubos;

    public function __construct($id = null, $id_lang = null, $context = null)
    {
        OrderDetail::$definition['fields']['id_order_detail_dubos'] = array('type' => self::TYPE_STRING, 'validate' => 'isString');

        parent::__construct($id, $id_lang);
    }

    public static function getIdByDubos($id_order_detail_dubos)
    {
        return Db::getInstance()->getValue("SELECT `id_order_detail` FROM `" . _DB_PREFIX_ . "order_detail` WHERE `id_order_detail_dubos`='" . pSQL($id_order_detail_dubos) . "'");
    }

    public function add($autodate = true, $null_values = false)
    {
        // Generate Order GUID
        $data       = openssl_random_pseudo_bytes(16);
        $data[6]    = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8]    = chr(ord($data[8]) & 0x3f | 0x80);
        $order_guid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        // Save Order GUID
        $query  = "UPDATE " . _DB_PREFIX_ . "orders SET id_order_dubos = '" . $order_guid . "' WHERE id_order=" . $this->id_order;
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute($query);

        // Generate OrderDetail GUID
        $data              = openssl_random_pseudo_bytes(16);
        $data[6]           = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8]           = chr(ord($data[8]) & 0x3f | 0x80);
        $order_detail_guid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        // Save OrderDetail GUID
        $this->id_order_detail_dubos = $order_detail_guid;

        parent::add($autodate, $null_values);

        // if (\Module::isInstalled('wservices') && \Module::isEnabled('wservices'))
        // {
        //     $wservices = \Module::getInstanceByName('wservices');
        //     $wservices->publishOrder($this, 'INS');
        // }

        return true;
    }

    /**
     * Check the order status
     * @param array $product
     * @param int $id_order_state
     */
    protected function checkProductStock($product, $id_order_state)
    {
        if ($id_order_state != Configuration::get('PS_OS_CANCELED') && $id_order_state != Configuration::get('PS_OS_ERROR')) {
            $update_quantity = true;
            if (!StockAvailable::dependsOnStock($product['id_product'])) {
                if ($this->context->cart->id_carrier == '1')
                {
                    $current_qty = StockAvailable::getShopQuantityAvailableByProduct($product['id_product'], $product['id_product_attribute']);

                    $update_quantity = StockAvailable::updateShopQuantity($product['id_product'], $product['id_product_attribute'], $current_qty-(int)$product['cart_quantity']);
                }
                else
                    $update_quantity = StockAvailable::updateQuantity($product['id_product'], $product['id_product_attribute'], -(int)$product['cart_quantity']);
            }

            if ($update_quantity) {
                $product['stock_quantity'] -= $product['cart_quantity'];
            }

            if ($product['stock_quantity'] < 0 && Configuration::get('PS_STOCK_MANAGEMENT')) {
                $this->outOfStock = true;
            }
            Product::updateDefaultAttribute($product['id_product']);
        }
    }
}