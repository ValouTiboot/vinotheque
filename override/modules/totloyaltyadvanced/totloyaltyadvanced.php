<?php

class TotLoyaltyAdvancedOverride extends TotLoyaltyAdvanced
{
	/* Hook display on shopping cart summary */

    public function hookdisplayShoppingCartFooter($params)
    {
        include_once(_PS_ROOT_DIR_.'/modules/'.$this->name.'/LoyaltyModuleAdvanced.php');
        include_once(_PS_ROOT_DIR_.'/modules/'.$this->name.'/LoyaltyStateModuleAdvanced.php');

        if (Tools::getValue('btnTransform')) {
            LoyaltyModuleAdvanced::transformPoints($this->context->link->getPageLink('order'));
        }

        if (Validate::isLoadedObject($params['cart'])) {
            $currency = Currency::getCurrency((int)$this->context->cart->id_currency);
            $points = self::getCartNbPoints($params['cart']);
            $ver= _PS_VERSION_;
            $finalver = explode(".", $ver);
            if (($finalver[1]==7)) {
                $vouch = Tools::displayPrice(LoyaltyModuleAdvanced::getVoucherValue(LoyaltyModuleAdvanced::getPointsByCustomer((int)$this->context->cart->id_customer), $currency['id_currency']));
            }
            if (($finalver[1]==5) || ($finalver[1]==6)) {
                $vouch = LoyaltyModuleAdvanced::getVoucherValue(LoyaltyModuleAdvanced::getPointsByCustomer((int)$params['delivery']->id_customer));
            }
            $vouchpoints = LoyaltyModuleAdvanced::getPointsByCustomer((int)$this->context->cart->id_customer);
            $this->smarty->assign(
                array(
                    'points'         => (int)$points,
                    'voucher'        => Tools::displayPrice(LoyaltyModuleAdvanced::getVoucherValue((int)$points), $currency),
                    'guest_checkout' => (int)Configuration::get('PS_GUEST_CHECKOUT_ENABLED'),
                    'voucherOld'     => $vouch,
                    'vouchpoints'    => $vouchpoints,
                    )
            );

        } else {
            $this->smarty->assign(array('points' => 0));
        }

        $ver= _PS_VERSION_;
        $finalver = explode(".", $ver);

        if (($finalver[1]==7)) {
            return $this->display(__FILE__, 'shopping-cart-latest.tpl');
        } else {
            return $this->display(__FILE__, 'shopping-cart.tpl');
        }
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
                if ($product['wine'])
                	continue;

                $loyalty = LoyaltyAdvanced::getLoyaltyByIDProduct($product['id_product'], true);
                if (Validate::isLoadedObject($loyalty)) {
                    $points += LoyaltyModuleAdvanced::calculLoyalty(
                        $loyalty->loyalty,
                        ($taxes_enabled == PS_TAX_EXC ? $product['price'] : $product['price_wt'])
                    ) * (int)($product['cart_quantity']);
                } else {
                    $total += ($taxes_enabled == PS_TAX_EXC ? $product['price'] : $product['price_wt']) * (int)($product['cart_quantity']);
                }
            }

            foreach ($cart->getCartRules(false) as $cart_rule) {
            	if (preg_match('@primeur@i', $cart_rule['name']))
            		continue;
                $total -= $cart_rule['value_real'];
            }
        }

        $context->shop = new Shop($current_shop_id);
        return LoyaltyModuleAdvanced::getNbPointsByPrice($total) + $points;
    }
}