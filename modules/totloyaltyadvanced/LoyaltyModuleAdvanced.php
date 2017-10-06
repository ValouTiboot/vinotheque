<?php
/**
* NOTICE OF LICENSE
*
* This source file is subject to a commercial license from 202 ecommerce
* Use, copy, modification or distribution of this source file without written
* license agreement from 202 ecommerce is strictly forbidden.
*
* @author    202 ecommerce <contact@202-ecommerce.com>
* @copyright Copyright (c) 202 ecommerce 2014
* @license   Commercial license
*
* Support <support@202-ecommerce.com>
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__).'/classes/TotLoyalty.php';

class LoyaltyModuleAdvanced extends TotLoyalty
{

    public function save($null_values = false, $autodate = true)
    {
        parent::save($null_values, $autodate);
    }



    public static function getOrderNbPoints($order)
    {
        if (!Validate::isLoadedObject($order)) {
            return false;
        }
        return self::getCartNbPoints(new Cart((int)$order->id_cart));
    }

    public static function getCartNbPoints($cart, $new_product = null)
    {
        $total = 0;
        $points = 0;
        if (Validate::isLoadedObject($cart)) {

            $context = Context::getContext();
            $current_shop_id = $context->shop->id;
            $context->cart = $cart;
            $context->customer = new Customer($context->cart->id_customer);
            $context->language = new Language($context->cart->id_lang);
            $context->shop = new Shop($context->cart->id_shop);
            $context->currency = new Currency($context->cart->id_currency, null, $context->shop->id);
            $cart_products = $cart->getProducts();
            $taxes_enabled = Product::getTaxCalculationMethod();
            if (isset($new_product) && !empty($new_product)) {

                $cart_products_new = array();
                $cart_products_new['id_product'] = (int)$new_product->id;
                if ($taxes_enabled == PS_TAX_EXC) {
                    $cart_products_new['price'] = number_format($new_product->getPrice(false, (int)$new_product->getDefaultIdProductAttribute()), 2, '.', '');

                } else {
                    $cart_products_new['price_wt'] = number_format($new_product->getPrice(true, (int)$new_product->getDefaultIdProductAttribute()), 2, '.', '');
                }
                $cart_products_new['cart_quantity'] = 1;
                $cart_products_new['id_shop'] = $new_product->id_shop;
                $cart_products[] = $cart_products_new;
            }

            foreach ($cart_products as $product) {

                if (!(int)(Configuration::get('PS_LOYALTY_NONE_AWARD')) && Product::isDiscounted((int)$product['id_product'])) {

                    if (isset(Context::getContext()->smarty) && is_object($new_product) && $product['id_product'] == $new_product->id) {
                        Context::getContext()->smarty->assign('no_pts_discounted', 1);
                    }
                    continue;
                }

                $loyalty = LoyaltyAdvanced::getLoyaltyByIDProduct($product['id_product'], true);
                if (Validate::isLoadedObject($loyalty)) {
                    $points += self::calculLoyalty(
                        $loyalty->loyalty,
                        ($taxes_enabled == PS_TAX_EXC ? $product['price'] : $product['price_wt'])
                    ) * (int)($product['cart_quantity']);
                } else {
                    $total += ($taxes_enabled == PS_TAX_EXC ? $product['price'] : $product['price_wt']) * (int)($product['cart_quantity']);
                }
            }
            foreach ($cart->getCartRules(false) as $cart_rule) {
                $total -= $cart_rule['value_real'];
            }
        }

        $context->shop = new Shop($current_shop_id);
        return self::getNbPointsByPrice($total) + $points;
    }

    private static function calculLoyalty($loyalty, $price)
    {
        $multiplicateur = array();
        if (preg_match('#^x([0-9]+)$#is', $loyalty, $multiplicateur)) {
            return self::getNbPointsByPrice($price) * $multiplicateur[1];

        } else {
            return $loyalty;
        }
    }

    public static function getVoucherValue($nb_points, $id_currency = null)
    {
        return parent::getVoucherValue($nb_points, $id_currency);
    }

    public static function getAllByIdCustomer($id_customer, $id_lang, $only_validate = false, $pagination = false, $nb = 10, $page = 1)
    {
        return parent::getAllByIdCustomer($id_customer, $id_lang, $only_validate, $pagination, $nb, $page);
    }

    public static function getDiscountByIdCustomer($id_customer, $last = false)
    {
        $query = '
          SELECT f.id_cart_rule AS id_cart_rule, f.date_upd AS date_add
          FROM `'._DB_PREFIX_.'totloyalty` f
          LEFT OUTER JOIN `'._DB_PREFIX_.'orders` o ON (f.`id_order` = o.`id_order`) AND o.`valid` = 1
          WHERE f.`id_customer` = '.(int)($id_customer).'
          AND f.`id_cart_rule` > 0';
        if ($last === true) {
            $query .= ' ORDER BY f.id_loyalty DESC LIMIT 0,1';
        }
        $query .= ' GROUP BY f.id_cart_rule';

        return Db::getInstance()->executeS($query);
    }



    /* Register all transaction in a specific history table */

    private function historize()
    {
        Db::getInstance()->execute('
          INSERT INTO `'._DB_PREFIX_.'totloyalty_history` (`id_loyalty`, `id_loyalty_state`, `points`, `date_add`)
          VALUES ('.(int)($this->id).', '.(int)($this->id_loyalty_state).', '.(int)($this->points).', NOW())');
    }
    public static function transformPoints($redirect = null)
    {
        $customer_points = (int)TotLoyalty::getPointsByCustomer((int)Context::getContext()->customer->id);
        if ($customer_points > 0) {
            /* Generate a voucher code */
            $voucher_code = null;
            do {
                $voucher_code = 'FID'.rand(1000, 100000);
            } while (CartRule::cartRuleExists($voucher_code));

            // Voucher creation and affectation to the customer
            $cart_rule = new CartRule();
            $cart_rule->code = $voucher_code;
            $cart_rule->id_customer = (int)Context::getContext()->customer->id;
            $cart_rule->reduction_currency = (int)Context::getContext()->currency->id;
            $cart_rule->reduction_amount = TotLoyalty::getVoucherValue((int)$customer_points);
            $cart_rule->quantity = 1;
            $cart_rule->highlight = 1;
            $cart_rule->quantity_per_user = 1;
            $cart_rule->reduction_tax = (bool)Configuration::get('PS_LOYALTY_TAX');

            // If merchandise returns are allowed, the voucher musn't be usable before this max return date
            $date_from = Db::getInstance()->getValue('
			SELECT UNIX_TIMESTAMP(date_add) n
			FROM '._DB_PREFIX_.'totloyalty
			WHERE id_cart_rule = 0 AND id_customer = '.(int)Context::getContext()->cookie->id_customer.'
			ORDER BY date_add DESC');

            if (Configuration::get('PS_ORDER_RETURN')) {
                $date_from += 60 * 60 * 24 * (int)Configuration::get('PS_ORDER_RETURN_NB_DAYS');
            }
            //$validvoucher = (int)Configuration::get('PS_LOYALTY_VALIDITY_PERIOD');
            $cart_rule->date_from = date('Y-m-d H:i:s', $date_from);
            //$cart_rule->date_to = date('Y-m-d H:i:s', strtotime($cart_rule->date_from .' + '.$validvoucher.' days'));
            $cart_rule->date_to = date('Y-m-d H:i:s', strtotime($cart_rule->date_from.' +1 year'));

            $cart_rule->minimum_amount = (float)Configuration::get('PS_LOYALTY_MINIMAL');
            $cart_rule->minimum_amount_currency = (int)Context::getContext()->currency->id;
            $cart_rule->active = 1;

            $categories = Configuration::get('PS_LOYALTY_VOUCHER_CATEGORY');
            if ($categories != '' && $categories != 0) {
                $categories = explode(',', Configuration::get('PS_LOYALTY_VOUCHER_CATEGORY'));
            } else {
                die (Tools::displayError());
            }

            $languages = Language::getLanguages(true);
            $default_text = Configuration::get('PS_LOYALTY_VOUCHER_DETAILS', (int)Configuration::get('PS_LANG_DEFAULT'));

            foreach ($languages as $language) {
                $text = Configuration::get('PS_LOYALTY_VOUCHER_DETAILS', (int)$language['id_lang']);
                $cart_rule->name[(int)$language['id_lang']] = $text ? (string)($text) : (string)($default_text);
            }


            $contains_categories = is_array($categories) && count($categories);
            if ($contains_categories) {
                $cart_rule->product_restriction = 1;
            }
            $cart_rule->add();

            //Restrict cartRules with categories
            if ($contains_categories) {

                //Creating rule group
                $id_cart_rule = (int)$cart_rule->id;
                $sql = "INSERT INTO "._DB_PREFIX_."cart_rule_product_rule_group (id_cart_rule, quantity) VALUES ('$id_cart_rule', 1)";
                Db::getInstance()->execute($sql);
                $id_group = (int)Db::getInstance()->Insert_ID();

                //Creating product rule
                $sql = "INSERT INTO "._DB_PREFIX_."cart_rule_product_rule (id_product_rule_group, type) VALUES ('$id_group', 'categories')";
                Db::getInstance()->execute($sql);
                $id_product_rule = (int)Db::getInstance()->Insert_ID();

                //Creating restrictions
                $values = array();
                foreach ($categories as $category) {
                    $category = (int)$category;
                    $values[] = "('$id_product_rule', '$category')";
                }
                $values = implode(',', $values);
                $sql = "INSERT INTO "._DB_PREFIX_."cart_rule_product_rule_value (id_product_rule, id_item) VALUES $values";
                Db::getInstance()->execute($sql);
            }



            // Register order(s) which contributed to create this voucher
            if (!TotLoyalty::registerDiscount($cart_rule)) {
                $cart_rule->delete();
            }

            Tools::redirect(
                is_null($redirect)
                ? Context::getContext()->link->getModuleLink('totloyaltyadvanced', 'default', array('process' => 'summary'))
                : $redirect
            );
        }
    }
}
