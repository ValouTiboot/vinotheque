<?php
/**
* 2007-2017 PrestaShop
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2017 PrestaShop SA
* @license   http://addons.prestashop.com/en/content/12-terms-and-conditions-of-use
* International Registered Trademark & Property of PrestaShop SA
*/

class RedirectController extends ModuleFrontController
{
    public function __construct()
    {
    }

    public function redirect()
    {
        $id_cart = Tools::getValue('id_cart');
        $token   = Tools::getValue('token_cart');
        $wichRemind = Tools::getValue('wichRemind');
        $link = Tools::getValue('link');

        if (!$id_cart || !$wichRemind) {
            Tools::redirect(__PS_BASE_URI__.'order.php?step=0');
        }

        if ($token != md5(_COOKIE_KEY_.'recover_cart_'.(int)$id_cart)) {
            Tools::redirect(__PS_BASE_URI__.'order.php?step=0');
        }

        // Get GAnalytics tags if needed
        $params = array();
        if (Tools::getValue('utm_source')) {
            $params['utm_source'] = Tools::getValue('utm_source');
        }
        if (Tools::getValue('utm_medium')) {
            $params['utm_medium'] = Tools::getValue('utm_medium');
        }
        if (Tools::getValue('utm_campaign')) {
            $params['utm_campaign'] = Tools::getValue('utm_campaign');
        }

        switch ($link) {
            case 'cart':
                $query = '
                    SELECT `lastname`, `firstname`, `passwd`, `email`, `id_currency`,
                        `id_cart`, ca.id_customer, cu.secure_key
                    FROM `'._DB_PREFIX_.'customer` `cu`
                    LEFT JOIN `'._DB_PREFIX_.'cart` `ca`
                    ON `ca`.`id_customer` = `cu`.`id_customer` AND `ca`.`secure_key` = `cu`.`secure_key`
                    WHERE `ca`.`id_cart`='.(int)$id_cart;
                $result = DB::getInstance()->getRow($query);

                if (!empty($result)) {
                    $customer = new Customer($result['id_customer']);

                    $context = Context::getContext();
                    $context->cookie->id_cart = $id_cart;
                    $context->cookie->id_customer = (int)$customer->id;
                    $context->cookie->customer_lastname = $customer->lastname;
                    $context->cookie->customer_firstname = $customer->firstname;
                    $context->cookie->logged = 1;
                    $context->cookie->is_guest = $customer->is_guest;
                    $context->cookie->passwd = $customer->passwd;
                    $context->cookie->email = $customer->email;
                    $this->context = $context;

                    $query = "UPDATE "._DB_PREFIX_."cartabandonment_remind SET click_cart = 1 WHERE wich_remind = ".(int)$wichRemind." AND id_cart = ".(int)$id_cart;
                    Db::getInstance()->Execute($query);
                }
                Tools::redirect(__PS_BASE_URI__.'order.php?step=0&'.http_build_query($params));
            case 'shop':
                if ($id_cart && $wichRemind) {
                    $query = "
                        UPDATE "._DB_PREFIX_."cartabandonment_remind
                        SET click = 1
                        WHERE wich_remind = ".(int)$wichRemind."
                            AND id_cart = ".(int)$id_cart;
                    Db::getInstance()->Execute($query);
                }
                Tools::redirect(__PS_BASE_URI__.'index.php?'.http_build_query($params));
            case 'unsubscribe':
                $id_customer = Db::getInstance()->getValue('
                        SELECT c.id_customer
                        FROM `' . _DB_PREFIX_ . 'cart` ca
                        JOIN ' . _DB_PREFIX_ . 'customer c ON ca.id_customer = c.id_customer
                        WHERE ca.id_cart = ' . (int)$id_cart);

                if (!$id_customer) {
                    die('Error');
                }

                $query = "INSERT INTO "._DB_PREFIX_."cartabandonment_unsubscribe VALUES (".(int) $id_customer.");";
                if (Db::getInstance()->Execute($query)) {
                    die('OK');
                } else {
                    die('Error');
                }

            default:
                Tools::redirect(__PS_BASE_URI__.'order.php?step=0&'.http_build_query($params));
        }
    }
}
