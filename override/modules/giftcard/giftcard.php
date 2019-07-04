<?php

class GiftCardOverride extends GiftCard
{
	public function install()
    {
        if (!$this->existsTab($this->tab_class)) {
            if (!$this->addTab($this->tab_name, $this->tab_class, 0)) {
                return false;
            }
        }

        if (!parent::install()
            || !$this->registerHook('header')
            || !$this->registerHook('footer')
            || !$this->registerHook('backOfficeHeader')
            || !$this->registerHook('actionCartSave')
            || !$this->registerHook('actionProductDelete')
            || !$this->registerHook('actionProductUpdate')
            || !$this->registerHook('displayProductButtons')
            || !$this->registerHook('displayMyAccountBlock')
            || !$this->registerHook('displayCustomerAccount')
            || !$this->registerHook('displayOrderConfirmation')
            || !$this->registerHook('actionOrderStatusPostUpdate')
            || !$this->registerHook('newOrder')
            || !$this->registerHook('displayCheckoutForm')
            || !Configuration::updateValue('GIFT_APPROVAL_STATUS', '2')
            || !Gift::createTable()
            || !copy(_PS_MODULE_DIR_.'giftcard/views/img/GiftCard.gif', _PS_MODULE_DIR_.'giftcard/GiftCard.gif')) {
            return false;
        }
        return true;
    }

	public function hookDisplayCheckoutForm($params)
    {
        return $this->display(__FILE__, 'views/templates/hook/'.$this->tpl_version.'/gift_form.tpl');
    }

    public static function generateVoucher($id_cart, $callee = 'hook')
    {
        $id_order = Gift::getOrderIdsByCartId($id_cart);
        $order = new Order((int)$id_order);
        $cart = new Cart((int)$id_cart);
        $all_products = $cart->getProducts();
        $id_customer = (int)$cart->id_customer;
        $cart_rules = array();
        $model = new Gift();
        $languages = Language::getLanguages(true);

        if ($id_customer != Context::getContext()->cookie->id_customer)
            return;

        $paid = true;
        if ($callee == 'front') {
            $paid = $order->hasBeenPaid();
        }

        if (($all_products != null) && $paid) {
            foreach ($all_products as $product) {
                if ($model->isExists((int)$product['id_product']) != false) {
                    $prod_detail = $model->getProductDetail($product['id_product'], $id_cart, $id_order, $id_customer);
                    $gift_product = new Product((int)$product['id_product'], true);
                    $voucher = new CartRule();
                    $vcode = Tools::passwdGen($prod_detail['length'], $prod_detail['vcode_type']);

                    //** Initializing Voucher
                    foreach ($languages as $lang) {
                        $voucher->name[$lang['id_lang']]    = $gift_product->name[$lang['id_lang']];//$prod_detail['card_name'];
                    }

                    $voucher->date_from             = $prod_detail['from'];
                    $voucher->date_to               = $prod_detail['to'];
                    $voucher->quantity              = $product['cart_quantity'];
                    $voucher->quantity_per_user     = $product['cart_quantity'];
                    $voucher->free_shipping         = $prod_detail['free_shipping'];
                    $voucher->reduction_currency    = $prod_detail['reduction_currency'];
                    $voucher->active                = $prod_detail['status'];
                    $voucher->date_add              = date('Y-m-d H:i:s');
                    $voucher->reduction_product     = $prod_detail['id_discount_product'];
                    $voucher->code                  = $vcode;
                    $voucher->minimum_amount_currency = $prod_detail['reduction_currency'];

                    if ($prod_detail['reduction_type'] == 'amount') {
                        $voucher->reduction_amount  = (float)$prod_detail['selected_price'];
                        $voucher->reduction_tax     = $prod_detail['reduction_tax'];
                        $voucher->reduction_percent = 0;
                    } elseif ($prod_detail['reduction_type'] == 'percent') {
                        if ($prod_detail['value_type'] == 'range') {
                            $val = explode(',', $prod_detail['card_value']); // range values
                            $pri = explode(',', $prod_detail['reduction_amount']); // percentage values
                            $cal = (float)((float)$pri[0] / (float)$val[0]) * (float)$prod_detail['selected_price'];
                        } elseif ($prod_detail['value_type'] == 'dropdown') {
                            $val = explode(',', $prod_detail['card_value']); // range values
                            $pri = explode(',', $prod_detail['reduction_amount']); // percentage values

                            foreach ($val as $k => $v) {
                                $value = (float)$v;
                                if ($value == $prod_detail['selected_price']) {
                                    $cal = (float)$pri[$k];
                                    break;
                                }
                            }
                        } else {
                            $cal = (float)$prod_detail['reduction_amount'];
                        }
                        
                        $voucher->reduction_percent = $cal;
                        $voucher->reduction_amount  = 0;
                        $voucher->reduction_tax     = 0;

                        $voucher->shop_restriction = (Shop::isFeatureActive())? 1: 0;
                    }

                    if ($voucher->add()) {
                        $id_cart_rule = $model->getIdCartRule($vcode);
                        if ($id_cart_rule) {
                            if (Shop::isFeatureActive()) {
                                Db::getInstance()->delete('cart_rule_shop', '`id_cart_rule` = '.(int)$id_cart_rule);
                                $product_shops = Gift::getShopsByProduct($product['id_product']);
                                foreach ($product_shops as $id_shop) {
                                    Gift::restrictVoucherToShop($id_cart_rule, $id_shop);
                                }
                            }
                            $id_image = $model->getId_image($product['id_product']);
                            $model->insertCustomer($id_cart_rule, $id_cart, $id_order, $product['id_product'], $id_customer, $product['link_rewrite'], $id_image);
                            array_push($cart_rules, $id_cart_rule);
                        }
                    }
                } else {
                    continue;
                }
            }
            if (!empty($cart_rules)) {
                $model->sendAlert($cart_rules, $id_customer);
            }
        }
    }
}