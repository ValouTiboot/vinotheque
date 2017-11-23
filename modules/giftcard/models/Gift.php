<?php
/**
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    FMM Modules
*  @copyright 2017 FMM Modules
*  @license   FMM Modules
*  @version   1.4.1
*/

class Gift extends ObjectModel
{
    public static $definition = array(
        'table' => 'gift_card',
        'primary' => 'id_gift_card',
        'fields' => array(
            'id_gift_card'          => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'id_product'            => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'id_customer'           => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'id_cart_rule'          => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'id_discount_product'   => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'id_attribute'          => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'card_name'             => array('type' => self::TYPE_STRING),
            'qty'                   => array('type' => self::TYPE_INT),
            'status'                => array('type' => self::TYPE_INT),
            'length'                => array('type' => self::TYPE_INT),
            'from'                  => array('type' => self::TYPE_STRING, 'validate' => 'isNegativePrice'),
            'to'                    => array('type' => self::TYPE_STRING, 'validate' => 'isNegativePrice'),
            'free_shipping'         => array('type' => self::TYPE_INT),
            'value_type'            => array('type' => self::TYPE_INT),
            'card_value'            => array('type' => self::TYPE_STRING),
            'vcode_type'            => array('type' => self::TYPE_STRING),
            'reduction_type'        => array('type' => self::TYPE_STRING),
            'reduction_amount'      => array('type' => self::TYPE_STRING),
            'reduction_tax'         => array('type' => self::TYPE_INT),
            'name'                  => array('type' => self::TYPE_STRING),
            'email'                 => array('type' => self::TYPE_STRING),
            'msg'                   => array('type' => self::TYPE_STRING),
            'link_rewrite'          => array('type' => self::TYPE_STRING),
            'id_image'              => array('type' => self::TYPE_INT),
            'id_cart'               => array('type' => self::TYPE_INT),
            'id_order'              => array('type' => self::TYPE_INT),
            'selected_price'        => array('type' => self::TYPE_FLOAT),
            'reduction_currency'    => array('type' => self::TYPE_INT),
            ),
        );

    public static function createTable()
    {
        //** Creating Database Tables
        return (Gift::giftCardTable() &&
            Gift::giftCardCustomer() &&
            Gift::orderedGiftCards() &&
            Gift::giftCardShop());
    }

    public static function giftCardTable()
    {
        //** Creating Database Tables
        $sql = true;
        $sql &= Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'gift_card`(
                `id_gift_card`          int(10) unsigned NOT NULL auto_increment,
                `id_product`            int(10) unsigned NOT NULL,
                `id_discount_product`   int(10) unsigned NOT NULL,
                `id_attribute` int(10)  unsigned NOT NULL,
                `card_name`             TEXT,
                `qty`                   int(10),
                `status`                int(2),
                `length`                int(10),
                `free_shipping`         int(2),
                `from`                  DATE,
                `to`                    DATE,
                `value_type`            TEXT,
                `card_value`            TEXT,
                `vcode_type`            TEXT,
                `reduction_type`        TEXT,
                `reduction_amount`      TEXT,
                `reduction_currency`    int(10),
                `reduction_tax`         int(2),
                PRIMARY KEY             (`id_gift_card`))
        ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8');

        return $sql;
    }

    public static function giftCardCustomer()
    {
        //** Creating Database Tables
        $sql = true;
        $sql &= Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'gift_card_customer`(
                `id_cart_rule`          int(10) unsigned NOT NULL,
                `id_customer`           int(10) unsigned NOT NULL,
                `id_cart`               int(10) unsigned,
                `id_order`              int(10) unsigned,
                `id_product`            int(10) unsigned,
                `link_rewrite`          TEXT,
                `id_image`              int(10) unsigned,
                PRIMARY KEY             (`id_cart_rule`, `id_customer`))
        ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8');
        return $sql;
    }

    public static function orderedGiftCards()
    {
        //** Creating Database Tables
        $sql = true;
        $sql &= Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ordered_gift_cards`(
                `id_cart`               int(10) unsigned NOT NULL,
                `id_order`              int(10) unsigned NOT NULL,
                `id_customer`           int(10) unsigned NOT NULL,
                `id_product`            int(10),
                `selected_price`        DECIMAL(20,6))
        ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8');
        return $sql;
    }

    public static function giftCardShop()
    {
        //** Creating Database Tables
        $sql = true;
        $sql &= Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'gift_card_shop`(
                `id_gift_card`      int(10) unsigned NOT NULL,
                `id_shop`           int(10) unsigned NOT NULL,
                PRIMARY KEY         (`id_gift_card`, `id_shop`))
        ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8');
        return $sql;
    }

    public static function dropTable()
    {
        if (Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'gift_card`')
            && Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'gift_card_customer`')
            && Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'ordered_gift_cards`')
            && Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'gift_card_shop`')) {
            return true;
        }
        return false;
    }

    public static function isExists($id_product)
    {
        if (!$id_product) {
            return false;
        }
        return (bool)Db::getInstance()->getValue('SELECT `id_product`
            FROM `'._DB_PREFIX_.'gift_card` WHERE id_product = '.pSQL((int)$id_product));
    }

    public function insertGiftCard($id_product, $card_name, $qty, $to, $from, $status, $length, $card_value, $value_type, $free_shipping, $id_discount_product, $reduction_type, $reduction_amount, $reduction_tax, $id_attribute, $reduction_currency, $vcode_type = 'ALPHANUMERIC')
    {
        $sql = 'INSERT INTO `'._DB_PREFIX_.'gift_card` (`id_product`, `card_name`, `qty`, `from`, `to`, `status`, `length`, `card_value`, `value_type`, `free_shipping`, `id_discount_product`, `reduction_type`, `reduction_amount`, `reduction_tax`, `id_attribute`, `reduction_currency`, `vcode_type`)
        VALUES('.pSQL((int)$id_product).', "'.pSQL((string)$card_name).'", '.pSQL((int)$qty).', "'.pSQL((string)$from).'", "'.pSQL((string)$to).'", '.pSQL((int)$status).', '.pSQL((int)$length).', "'.pSQL((string)$card_value).'", "'.pSQL((string)$value_type).'", '.pSQL((int)$free_shipping).', '.pSQL((int)$id_discount_product).', "'.pSQL((string)$reduction_type).'", "'.pSQL((string)$reduction_amount).'", '.pSQL((int)$reduction_tax).', '.pSQL((int)$id_attribute).', '.pSQL((int)$reduction_currency).', "'.pSQL($vcode_type).'")';

        if (Db::getInstance()->execute($sql)) {
            return Db::getInstance()->Insert_ID();
        }
    }

    public function insertCustomer($id_cart_rule, $id_cart, $id_order, $id_product, $id_customer, $link_rewrite, $id_image)
    {
        $sql = 'INSERT INTO `'._DB_PREFIX_.'gift_card_customer` (`id_cart_rule`, `id_cart`, `id_order`, `id_product`, `id_customer`, `link_rewrite`, `id_image`)
        VALUES('.pSQL((int)$id_cart_rule).', '.pSQL((int)$id_cart).', '.pSQL((int)$id_order).', '.pSQL((int)$id_product).', '.pSQL((int)$id_customer).', "'.pSQL((string)$link_rewrite).'", '.pSQL((int)$id_image).')';

        if (Db::getInstance()->execute($sql)) {
            return Db::getInstance()->Insert_ID();
        }
    }

    public function orderGC($id_cart, $id_order, $id_customer, $id_product, $selected_price)
    {
        $sql = 'INSERT INTO `'._DB_PREFIX_.'ordered_gift_cards` (`id_order`, `id_cart`, `id_customer`, `id_product`, `selected_price`)
                VALUES('.pSQL($id_order).', '.pSQL($id_cart).', '.pSQL($id_customer).', '.pSQL($id_product).', '.(float)$selected_price.')';

        if (Db::getInstance()->execute($sql))
            return Db::getInstance()->Insert_ID();
    }

    public function updateGiftCard($id_gift_card, $id_product, $card_name, $qty, $to, $from, $status, $length, $card_value, $value_type, $free_shipping, $id_discount_product, $reduction_type, $reduction_amount, $reduction_tax, $reduction_currency, $vcode_type)
    {
        $sql = 'UPDATE `'._DB_PREFIX_.'gift_card`
                SET `card_name`             = "'.pSQL((string)$card_name).'",
                    `qty`                   = '.pSQL((int)$qty).',
                    `from`                  = "'.pSQL((string)$from).'",
                    `to`                    = "'.pSQL((string)$to).'",
                    `status`                = '.pSQL((int)$status).',
                    `length`                = '.pSQL((int)$length).',
                    `vcode_type`            = "'.pSQL($vcode_type).'",
                    `card_value`            = "'.pSQL((string)$card_value).'",
                    `value_type`            = "'.pSQL((string)$value_type).'",
                    `free_shipping`         = '.pSQL((int)$free_shipping).',
                    `id_discount_product`   = "'.pSQL((int)$id_discount_product).'",
                    `reduction_type`        = "'.pSQL((string)$reduction_type).'",
                    `reduction_amount`      = "'.pSQL((string)$reduction_amount).'",
                    `reduction_tax`         = '.pSQL((int)$reduction_tax).',
                    `reduction_currency`    = '.pSQL((int)$reduction_currency).'
                WHERE   `id_gift_card`      = '.pSQL((int)$id_gift_card).'
                AND     `id_product`        = '.pSQL((int)$id_product);

        if (Db::getInstance()->execute($sql)) {
            return true;
        }
        return false;
    }

    public function updateProductQty($id_product, $qty)
    {
        return (bool)Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'stock_available`
            SET `quantity` ='.pSQL((int)$qty).'
            WHERE id_product = '.pSQL((int)$id_product));
    }

    public function setProductPrice($id_product, $price, $name, $id_lang)
    {
        $sql = 'UPDATE `'._DB_PREFIX_.'product`
                SET `price` ='.pSQL((float)$price).'
                WHERE id_product = '.pSQL((int)$id_product);

        $qry = 'UPDATE `'._DB_PREFIX_.'product_lang`
                SET `name` = "'.pSQL((string)$name).'"
                WHERE id_product = '.pSQL((int)$id_product).'
                AND id_lang = '.pSQL((int)$id_lang);

        if (Db::getInstance()->execute($sql) && Db::getInstance()->execute($qry)) {
            return true;
        }
    }

    public function setCategory($id_product)
    {
        $pos = 0;
        $id_category = 2;
        $pos = (int)Gift::getPosition($id_category);
        $pos += 1;
        $sql = 'INSERT INTO`'._DB_PREFIX_.'category_product`(`id_category`, `id_product`, `position`)
                VALUES('.pSQL((int)$id_category).', '.pSQL((int)$id_product).', '.pSQL((int)$pos).')';

        if (Db::getInstance()->execute($sql)) {
            return Db::getInstance()->Insert_ID();
        }
    }

    public function setVoucherQty($id_cart_rule, $qty)
    {
        return (bool)Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'cart_rule`
            SET `quantity` ='.pSQL((int)$qty).'
            WHERE id_cart_rule = '.pSQL((int)$id_cart_rule));
    }

    public function getPosition($id_category)
    {
        return (int)Db::getInstance()->getValue('SELECT MAX(position) AS pos
            FROM `'._DB_PREFIX_.'category_product`
            WHERE id_category = '.pSQL((int)$id_category));
    }

    public function getId_image($id_product)
    {
        return (int)Db::getInstance()->getValue('SELECT `id_image`
            FROM `'._DB_PREFIX_.'image` WHERE cover = 1
            AND id_product = '.pSQL((int)$id_product));
    }

    public function getIdCartRule($vcode)
    {
        return (int)Db::getInstance()->getValue('SELECT `id_cart_rule`
            FROM `'._DB_PREFIX_.'cart_rule`
            WHERE code = "'.pSQL((string)$vcode).'"');
    }

    public function getProductDetail($id_product, $id_cart, $id_order, $id_customer)
    {
        return Db::getInstance()->getRow('SELECT g.*, go.`selected_price`
            FROM `'._DB_PREFIX_.'gift_card` g
            LEFT JOIN `'._DB_PREFIX_.'ordered_gift_cards` go
                ON (g.id_product = go.id_product)
            WHERE g.id_product = '.pSQL((int)$id_product).'
            AND go.id_cart = '.pSQL((int)$id_cart).'
            AND go.id_order = '.pSQL((int)$id_order).'
            AND go.id_customer = '.pSQL((int)$id_customer));
    }

    public function getVoucherByCustomerId($id_customer, $id_lang = null, $id_shop = null)
    {
        if (!$id_shop) {
            $id_shop = (int)Context::getContext()->shop->id;
        }
        if (!$id_lang) {
            $id_lang = (int)Context::getContext()->language->id;
        }

        Db::getInstance()->execute('DELETE cr.*, crl.*, ccr.*
            FROM `'._DB_PREFIX_.'cart_rule` cr
            LEFT JOIN `'._DB_PREFIX_.'cart_rule_lang` crl
            ON cr.id_cart_rule = crl.id_cart_rule
            LEFT JOIN `'._DB_PREFIX_.'cart_cart_rule` ccr
            ON cr.id_cart_rule = ccr.id_cart_rule
            WHERE cr.quantity = 0');

        return Db::getInstance()->executeS('SELECT cr.*, pl.`name`, gcc.`id_customer`, pl.`link_rewrite`, image_shop.`id_image` AS id_image
            FROM `'._DB_PREFIX_.'cart_rule` cr
            LEFT JOIN `'._DB_PREFIX_.'cart_rule_lang` crl
                ON (cr.`id_cart_rule` = crl.id_cart_rule AND crl.id_lang = '.pSQL((int)$id_lang).')
            LEFT JOIN `'._DB_PREFIX_.'gift_card_customer` gcc
                ON (cr.`id_cart_rule` = gcc.id_cart_rule)
            LEFT JOIN `'._DB_PREFIX_.'image` i '.Shop::addSqlAssociation('image', 'i').'
                ON (gcc.`id_product` = i.`id_product` AND image_shop.`cover` = 1)
            LEFT JOIN `'._DB_PREFIX_.'product_lang` pl
                ON (gcc.`id_product` = pl.`id_product` AND pl.id_lang = '.pSQL((int)$id_lang).' AND pl.id_shop = '.pSQL((int)$id_shop).')
            WHERE gcc.id_customer = '.pSQL((int)$id_customer));
    }

    public static function getAllCards($id_lang)
    {
        //** Function to get values from Database and display the values in backend table
        Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'gift_card`
            WHERE id_product NOT IN (SELECT `id_product` FROM `'._DB_PREFIX_.'product`)');

        $row = Db::getInstance()->executeS('SELECT *
            FROM `'._DB_PREFIX_.'gift_card` gc
            INNER JOIN `'._DB_PREFIX_.'product` p
            ON (gc.id_product = p.id_product)');

        if ($row) {
            foreach ($row as &$result) {
                $product = new Product($result['id_product'], true, (int)$id_lang);
                $currency = new Currency($result['reduction_currency']);
                $image = Product::getCover($result['id_product']);
                $result['id_image'] = $image['id_image'];
                $result['link_rewrite'] = $product->link_rewrite;
                $result['iso_code'] = $currency->iso_code;
                $result['giftcard_product'] = (array)$product;
            }
        }
        return $row;
    }

    public static function getGiftCard($id_product, $id_gift_card, $id_lang)
    {
        //** Update quantity before getting gift card
        $sql1 = 'UPDATE `'._DB_PREFIX_.'gift_card`
            SET `qty` ='.(int)StockAvailable::getQuantityAvailableByProduct($id_product);
            Db::getInstance()->execute($sql1);
        //** Function to get values from Database and display the values in backend table
        $sql2 = 'SELECT *
            FROM `'._DB_PREFIX_.'gift_card`
            Where id_product = '.pSQL((int)$id_product).'
            AND id_gift_card = '.pSQL((int)$id_gift_card);

        $row = Db::getInstance()->executeS($sql2);
        foreach ($row as &$result) {
            $result['discount_product'] = Product::getProductName($result['id_discount_product'], null, (int)$id_lang);
        }
        return $row;
    }

    public function deleteCard($id_gift_card, $id_product)
    {
        return (bool)Db::getInstance()->execute('DELETE gc.*, p.*
            FROM `'._DB_PREFIX_.'gift_card` gc
            LEFT JOIN `'._DB_PREFIX_.'product` p
                ON gc.id_product = p.id_product
            WHERE gc.id_product ='.pSQL((int)$id_product).'
            AND gc.id_gift_card ='.pSQL((int)$id_gift_card));
    }

    public static function deleteByProduct($id_product)
    {
        if (!$id_product) {
            return false;
        }

        return (bool)Db::getInstance()->Execute('DELETE gc.*, gcs.*, gco.*
            FROM `'._DB_PREFIX_.'gift_card` gc
            LEFT JOIN `'._DB_PREFIX_.'gift_card_shop` gcs ON (gc.id_gift_card = gcs.id_gift_card)
            LEFT JOIN `'._DB_PREFIX_.'ordered_gift_card` gco ON (gc.id_product = gcs.id_product)
            WHERE gc.id_product = '.(int)$id_product);
    }

    public static function updateGiftCardField($field_name, $id_product, $value)
    {
        if (empty($field_name) || !$id_product) {
            return false;
        }
        return (bool)Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'gift_card`
            SET `'.$field_name.'` = '.(int)$value.' WHERE id_product = '.(int)$id_product);
    }

    public static function getCardValue($id_product)
    {
        return Db::getInstance()->getRow('SELECT card_value, value_type
            FROM `'._DB_PREFIX_.'gift_card`
            Where id_product = '.pSQL((int)$id_product).'
            ORDER BY id_product');
    }

    public static function getCartsRuleById($id_cart_rule, $id_lang)
    {
        return Db::getInstance()->executeS('SELECT cr.*, crl.*
            FROM '._DB_PREFIX_.'cart_rule cr
            LEFT JOIN '._DB_PREFIX_.'cart_rule_lang crl
            ON (cr.id_cart_rule = crl.id_cart_rule AND crl.id_lang = '.(int)$id_lang.')
            WHERE cr.id_cart_rule = '.(int)$id_cart_rule);
    }

    public static function getCustomerById($id_customer)
    {
        return Db::getInstance()->executeS('SELECT `email`, `firstname`, `lastname`
            FROM `'._DB_PREFIX_.'customer`
            WHERE id_customer = '.(int)$id_customer);
    }

    public static function getOrderIdsByCartId($id_cart)
    {
        return (int)Db::getInstance()->getValue('SELECT id_order
            FROM '._DB_PREFIX_.'orders WHERE `id_cart` = '.(int)$id_cart);
    }

    public static function getOrderedGiftCards($id_customer)
    {
        return Db::getInstance()->ExecuteS('SELECT DISTINCT(id_cart)
            FROM '._DB_PREFIX_.'ordered_gift_cards
            WHERE id_order NOT IN(
                SELECT id_order
                FROM '._DB_PREFIX_.'gift_card_customer
                WHERE id_customer = '.(int)$id_customer.')');
    }

    public static function sendAlert($rules, $id_customer)
    {
        $id_lang = Context::getContext()->language->id;
        $module = new GiftCard();
        if (Customer::customerIdExistsStatic($id_customer)) {
            $customer = Gift::getCustomerById($id_customer);
        }

        $html = '';
        if (!empty($customer)) {
            $html .= '<div class="cart_summary">
                    <table class="table" id="orderProducts" cellspacing="0" cellpadding="0"
                        style="background: none repeat scroll 0 0 #EAEBEC;
                        border: 1px solid #CCCCCC;
                        border-radius: 3px;
                        box-shadow: 0 1px 2px #D1D1D1;
                        color: #444444;
                        font-family: Arial,Helvetica,sans-serif;
                        font-size: 12px;
                        margin: 20px;
                        text-shadow: 1px 1px 0 #FFFFFF;">
                        <thead>
                            <tr>
                                <th class="center" style="background: -moz-linear-gradient(center top , #EDEDED, #EBEBEB) repeat scroll 0 0 rgba(0, 0, 0, 0);
                                border-bottom: 1px solid #E0E0E0;
                                border-top: 1px solid #FAFAFA;
                                padding: 21px 25px 22px;"><span class="title_box">'.$module->l('Gift Card', 'Gift').'</span></th>
                                <th class="text-right fixed-width-md" style="background: -moz-linear-gradient(center top , #EDEDED, #EBEBEB) repeat scroll 0 0 rgba(0, 0, 0, 0);
                                border-bottom: 1px solid #E0E0E0;
                                border-top: 1px solid #FAFAFA;
                                padding: 21px 25px 22px;"><span class="title_box">'.$module->l('Coupon code', 'Gift').'</span></th>
                                <th class="text-center fixed-width-md" style="background: -moz-linear-gradient(center top , #EDEDED, #EBEBEB) repeat scroll 0 0 rgba(0, 0, 0, 0);
                                border-bottom: 1px solid #E0E0E0;
                                border-top: 1px solid #FAFAFA;
                                padding: 21px 25px 22px;"><span class="title_box">'.$module->l('Quantity', 'Gift').'</span></th>
                                <th class="text-center fixed-width-md" style="background: -moz-linear-gradient(center top , #EDEDED, #EBEBEB) repeat scroll 0 0 rgba(0, 0, 0, 0);
                                border-bottom: 1px solid #E0E0E0;
                                border-top: 1px solid #FAFAFA;
                                padding: 21px 25px 22px;"><span class="title_box">'.$module->l('Expire date', 'Gift').'</span></th>
                            </tr>
                        </thead>
                        <tbody>';

            foreach ($rules as $rule) {
                $cart_rule = Gift::getCartsRuleById($rule, $id_lang);
                $cart_rule = array_shift($cart_rule);

                $html   .= '<tr>
                                <td class="center" style="background: -moz-linear-gradient(center top , #FBFBFB, #FAFAFA) repeat scroll 0 0 rgba(0, 0, 0, 0);
                                    border-bottom: 1px solid #E0E0E0;
                                    border-left: 1px solid #E0E0E0;
                                    border-top: 1px solid #FFFFFF;
                                    padding: 18px;">'.$cart_rule['name'].'</td>
                                <td class="text-right" style="background: -moz-linear-gradient(center top , #FBFBFB, #FAFAFA) repeat scroll 0 0 rgba(0, 0, 0, 0);
                                    border-bottom: 1px solid #E0E0E0;
                                    border-left: 1px solid #E0E0E0;
                                    border-top: 1px solid #FFFFFF;
                                    padding: 18px;">'.$cart_rule['code'].'</td>
                                <td class="text-right" style="background: -moz-linear-gradient(center top , #FBFBFB, #FAFAFA) repeat scroll 0 0 rgba(0, 0, 0, 0);
                                    border-bottom: 1px solid #E0E0E0;
                                    border-left: 1px solid #E0E0E0;
                                    border-top: 1px solid #FFFFFF;
                                    padding: 18px;">'.$cart_rule['quantity'].'</td>
                                <td class="text-center" style="background: -moz-linear-gradient(center top , #FBFBFB, #FAFAFA) repeat scroll 0 0 rgba(0, 0, 0, 0);
                                    border-bottom: 1px solid #E0E0E0;
                                    border-left: 1px solid #E0E0E0;
                                    border-top: 1px solid #FFFFFF;
                                    padding: 18px;">'.$cart_rule['date_to'].'</td>
                            </tr>';
            }
            $html .= '</tbody>
                            </table><br/></div>';

            $customer = array_shift($customer);
            $template_vars = array(
                    '{email}'       => $customer['email'],
                    '{lname}'       => $customer['lastname'],
                    '{fname}'       => $customer['firstname'],
                    '{shop_name}'   => Configuration::get('PS_SHOP_NAME'),
                    '{detail}'      => $html,
                    );

            $result = Mail::Send((int)$id_lang,
                            'my_giftcards',
                            Mail::l('Your Purchased Gift cards', (int)$id_lang),
                            $template_vars,
                            $customer['email'],
                            null,
                            null,
                            null,
                            null,
                            null,
                            _PS_MODULE_DIR_.'giftcard/mails/',
                            false);
            if ($result) {
                return true;
            }
            return false;
        }
    }

    public static function removeAssocShops($id_product)
    {
        if (!$id_product) {
            return false;
        }
        return (bool)Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'product_shop`
            WHERE id_product = '.(int)$id_product);
    }

    public static function updateGiftShops($id_product, $id_shop, $id_category_default = 2, $id_tax_rules_group = 0, $active = 1, $price = 0.0)
    {
        return (bool)Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'product_shop`(`id_product`, `id_shop`, `id_category_default`, `id_tax_rules_group`, `redirect_type`, `active`, `price`, `date_add`)
            VALUES('.(int)$id_product.', '.(int)$id_shop.', '.(int)$id_category_default.', '.(int)$id_tax_rules_group.', "404", '.(int)$active.', '.(float)$price.',  NOW())');
    }

    public static function getShopsByProduct($id_product)
    {
        $row = Db::getInstance()->executeS('SELECT `id_shop`
            FROM `'._DB_PREFIX_.'product_shop`
            WHERE `id_product` = '.(int)$id_product.'
            GROUP BY `id_shop`');

        $result = array();
        if ($row) {
            foreach ($row as $res) {
                $result[] = $res['id_shop'];
            }
        }
        return $result;
    }

    public static function getOrderStateHistory($id_order)
    {
        $result = Db::getInstance()->ExecuteS('SELECT `id_order_state`
            FROM `'._DB_PREFIX_.'order_history`
            WHERE `id_order` = '.(int)$id_order);

        if ($result) {    
            foreach ($result as &$res) {
                $res = array_shift($res);
            }
        }
        return $result;
    }

    public static function restrictVoucherToShop($id_cart_rule, $id_shop)
    {
        $row = array('id_cart_rule' => (int)$id_cart_rule, 'id_shop' => (int)$id_shop);
        return Db::getInstance()->insert('cart_rule_shop', $row, false, true, Db::INSERT_IGNORE);
    }
}
