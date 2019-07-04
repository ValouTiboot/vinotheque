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

if (!defined('_PS_VERSION_'))
    exit;

include_once(dirname(__FILE__).'/models/Gift.php');
class GiftCard extends Module
{
    public $tab_class = 'GiftCard';
    public $tab_module = 'giftcard';
    public $tab_name = 'Gift Cards';
    public $msg = 0;
    public $tpl_version = 'v1_6';
    public function __construct()
    {
        $this->name = 'giftcard';
        $this->tab = 'front_office_features';
        $this->version = '1.4.1';
        $this->author = 'FMM Modules';
        $this->bootstrap = true;
        $this->module_key = '26c0ea03bb9df50375ba49227d63e4d7';
        parent::__construct();

        if (Tools::version_compare(_PS_VERSION_, '1.7.0.0', '>=') == true) {
            $this->tpl_version = 'v1_7';
        }

        $this->displayName = $this->l('Gift Cards');
        $this->description = $this->l('This module allows you to create gift cards in your shop. Customers can order them and send as a gift to anyone.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }

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
            || !Configuration::updateValue('GIFT_APPROVAL_STATUS', '2')
            || !Gift::createTable()
            || !copy(_PS_MODULE_DIR_.'giftcard/views/img/GiftCard.gif', _PS_MODULE_DIR_.'giftcard/GiftCard.gif')) {
            return false;
        }
        return true;
    }

    public function uninstall()
    {
        if (!$this->removeTab($this->tab_class)) {
            return false;
        }

        if (parent::uninstall()
            && $this->unregisterHook('header')
            && $this->unregisterHook('footer')
            && $this->unregisterHook('newOrder')
            && $this->unregisterHook('backOfficeHeader')
            && $this->unregisterHook('actionCartSave')
            && $this->unregisterHook('actionProductDelete')
            && $this->unregisterHook('actionProductUpdate')
            && $this->unregisterHook('displayMyAccountBlock')
            && $this->unregisterHook('displayCustomerAccount')
            && $this->unregisterHook('displayProductButtons')
            && $this->unregisterHook('displayOrderConfirmation')
            && $this->unregisterHook('actionOrderStatusPostUpdate')
            && Configuration::deleteByName('GIFT_APPROVAL_STATUS')
            && Gift::dropTable()
            && @unlink(_PS_MODULE_DIR_.'giftcard/GiftCard.gif')) {
            return true;
        }
        return false;
    }

    private function addTab($tab_name, $tab_class, $id_parent)
    {
        //** @function to add tab in admin backend
        $tab = new Tab();
        $tab->class_name = $tab_class;
        $tab->id_parent = $id_parent;
        $tab->module = $this->tab_module;
        $tab->name[(int)Configuration::get('PS_LANG_DEFAULT')] = $tab_name;
        $tab->add();

        $subtab = new Tab();
        $subtab->class_name = 'AdminCreateGift';
        $subtab->id_parent = Tab::getIdFromClassName($tab_class);
        $subtab->module = $this->tab_module;
        $subtab->name[(int)Configuration::get('PS_LANG_DEFAULT')] = $this->l('Add Gift Card');
        $subtab->add();

        $subtab1 = new Tab();
        $subtab1->class_name = 'AdminGift';
        $subtab1->id_parent = Tab::getIdFromClassName($tab_class);
        $subtab1->module = $this->tab_module;
        $subtab1->name[(int)Configuration::get('PS_LANG_DEFAULT')] = $this->l('View');
        $subtab1->add();

        return true;
    }

    private function removeTab($tab_class)
    {
        //** @function to remove the tab
        $id_tab = Tab::getIdFromClassName($tab_class);
        if ($id_tab != 0) {
            $tab = new Tab($id_tab);
            $tab->delete();
            return true;
        }
        $id_tab1 = Tab::getIdFromClassName('AdminCreateGift');
        if ($id_tab1 != 0) {
            $tab = new Tab($id_tab1);
            $tab->delete();
            return true;
        }
        $id_tab2 = Tab::getIdFromClassName('AdminGift');
        if ($id_tab2 != 0) {
            $tab = new Tab($id_tab2);
            $tab->delete();
            return true;
        }

        return false;
    }

    public function getid_tabFromClassName($tab_class)
    {
        return (int)Db::getInstance()->getValue('SELECT id_tab FROM '._DB_PREFIX_.'tab WHERE class_name="'.pSQL((string)$tab_class).'"');
    }

    private function existsTab($tab_class)
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT id_tab AS id
            FROM `'._DB_PREFIX_.'tab` WHERE LOWER(`class_name`) = \''.pSQL((string)$tab_class).'\'');
        if (count($result) == 0) {
            return false;
        }
        return true;
    }

    public function getContent()
    {
        $current_index = $this->context->link->getAdminLink('AdminModules', false);
        $current_token = Tools::getAdminTokenLite('AdminModules');
        $action_url = $current_index.'&configure='.$this->name.'&token='.$current_token.'&tab_module='.$this->tab.'&module_name='.$this->name;

        if (Tools::isSubmit('updateConfiguration')) {
            $approval_states = (Tools::getValue('approval_states')) ? implode(',', Tools::getValue('approval_states')) : '';
            Configuration::updateValue('GIFT_APPROVAL_STATUS', $approval_states);
            $this->context->controller->confirmations[] = $this->l('Settings successfully updated');
        }

        $approval_states = (Configuration::get('GIFT_APPROVAL_STATUS')? explode(',', Configuration::get('GIFT_APPROVAL_STATUS')) : '');
        $this->context->smarty->assign(array(
            'states' => OrderState::getOrderStates($this->context->employee->id_lang),
            'ps_version' => _PS_VERSION_,
            'approval_states' => $approval_states,
            'action_url' => $action_url,
        ));
        return  $this->display(__FILE__, 'config.tpl');
    }

    public function hookDisplayBackOfficeHeader()
    {
        $this->context->controller->addCSS($this->_path.'views/css/admin.css');
    }

    public function hookHeader()
    {
        $this->context->smarty->assign(array('ps_version' => _PS_VERSION_));
        $force_ssl = (Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE'));
        $this->context->smarty->assign(array(
            'base_dir' => _PS_BASE_URL_.__PS_BASE_URI__,
            'base_dir_ssl' => _PS_BASE_URL_SSL_.__PS_BASE_URI__,
            'force_ssl' => $force_ssl
            )
        );
        
        if (true == Tools::version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $this->context->controller->registerJavascript(
                'modules-giftcard', 'modules/'.$this->name.'/views/js/gift_script.js',
                array('position' => 'bottom', 'priority' => 300)
            );
            return $this->display(__FILE__, $this->tpl_version.'/gift_variables.tpl');
        }
    }

    public function hookFooter()
    {
        if (Tools::version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $force_ssl = (Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE'));
            $this->context->smarty->assign(array(
                'base_dir' => _PS_BASE_URL_.__PS_BASE_URI__,
                'base_dir_ssl' => _PS_BASE_URL_SSL_.__PS_BASE_URI__,
                'force_ssl' => $force_ssl
                )
            );
        }
        $this->context->smarty->assign(array(
            'ps_version' => _PS_VERSION_,
            'PS_REWRITING_SETTINGS' => (int)Configuration::get('PS_REWRITING_SETTINGS'),
            'PS_VERSION' => (Tools::version_compare(_PS_VERSION_, '1.6', '>=')) ? 1 : 0
        ));
        return $this->display(__FILE__, $this->tpl_version.'/hideAddtoCart.tpl');
    }

    public function hookDisplayCustomerAccount()
    {
        return $this->display(__FILE__, $this->tpl_version.'/my-account.tpl');
    }

    public function hookDisplayMyAccountBlock($params)
    {
        return $this->hookDisplayCustomerAccount($params);
    }

    public function hookdisplayProductButtons()
    {
        $action = '';
        $card_type = '';
        $preselected_price = '';
        if ($id_product = (int)Tools::getValue('id_product')) {
            $product = new Product($id_product, true, $this->context->language->id, $this->context->shop->id);
        }

        if (Tools::getIsset('action') && Tools::getValue('action') == 'get_gift_price') {
            $action = 'get_gift_price';
            $card_type = (string)Tools::getValue('card_type');
            $preselected_price = (float)Tools::getValue('current_price');
        }

        $vals = Gift::getCardValue($product->id);
        if (Tools::version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $force_ssl = (Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE'));
            $this->context->smarty->assign(array(
                'base_dir' => _PS_BASE_URL_.__PS_BASE_URI__,
                'base_dir_ssl' => _PS_BASE_URL_SSL_.__PS_BASE_URI__,
                'force_ssl' => $force_ssl
                )
            );
        }

        if (!empty($vals) && Gift::isExists($id_product)) {
            $price_display = (int)Product::getTaxCalculationMethod((int)$this->context->cookie->id_customer);
            $prices_tax_excl = explode(',', $vals['card_value']);
            $prices_tax_incl = array();

            foreach ($prices_tax_excl as $price) {
                if ((!$price_display || $price_display == 2) && $product->id_tax_rules_group == 0) {
                    $prices_tax_incl[] = $this->calculateGiftPrice($price, $id_product, false);
                } else {
                    $prices_tax_incl[] = $this->calculateGiftPrice($price, $id_product, true);
                }
            }

            $this->context->smarty->assign(
                array(
                    'values'            => ((!Configuration::get('PS_TAX') && $product->id_tax_rules_group > 0)? $prices_tax_incl : $prices_tax_excl),
                    'prices_tax'        => ((!$price_display && $product->id_tax_rules_group > 0)? $prices_tax_incl : $prices_tax_excl),
                    'type'              => $vals['value_type'],
                    'pid'               => $product->id,
                    'ps_version'        => _PS_VERSION_,
                    'tax_enabled'       => Configuration::get('PS_TAX'),
                    'product_tax'       => (int)$product->id_tax_rules_group,
                    'display_tax_label' => 1,
                    'priceDisplay'      => (int)$price_display,
                    'id_module'         => $this->id,
                    'preselected_price' => $preselected_price
                ));

            if (!empty($action) && $action == 'get_gift_price' && !empty($card_type) && $card_type != 'fixed') {
                return $this->display(__FILE__, 'views/templates/hook/'.$this->tpl_version.'/'.$card_type.'.tpl');
            } else {
                return $this->display(__FILE__, 'views/templates/hook/'.$this->tpl_version.'/gift_card.tpl');
            }
        }
    }

    public function hookActionCartSave($params)
    {
        $id_product = (int)Tools::getValue('id_product');
        if (Gift::isExists($id_product)) {
            $new_price = (float)Tools::getValue('giftcard_price');
            $this->setPrice($id_product, $new_price);
        }
    }

    public function hookActionProductDelete()
    {
        $id_product = (int)Tools::getValue('id_product');
        if (Gift::isExists($id_product)) {
            $product = new Product($id_product);
            if ($product->delete()) {
                Gift::deleteByProduct($id_product);
            }
        }
    }

    public function hookActionProductUpdate()
    {
        $id_product = (int)Tools::getValue('id_product');
        if (Gift::isExists($id_product)) {
            $product = new Product($id_product);
            Gift::updateGiftCardField('qty', $id_product, $product->quantity);
            Gift::updateGiftCardField('status', $id_product, $product->active);
        }
    }

    public function hookNewOrder($params)
    {
        $cart = $params['cart'];
        $id_cart = $cart->id;
        $id_order = $params['order']->id;
        $id_customer = $cart->id_customer;
        $products = $cart->getProducts();
        $price_display = (int)Product::getTaxCalculationMethod((int)$id_customer);

        if (!empty($products)) {
            foreach ($products as $product) {
                if (Gift::isExists($product['id_product'])) {
                    if ($price_display) {
                        Gift::orderGC($id_cart, $id_order, $id_customer, $product['id_product'], $product['price']);
                    } else {
                        Gift::orderGC($id_cart, $id_order, $id_customer, $product['id_product'], $product['price_wt']);
                    }
                }
            }
        }
    }

    public function hookActionOrderStatusPostUpdate($params)
    {
        $id_order       = (int)$params['id_order'];
        $id_cart        = $params['cart']->id;
        $id_order_state = (int)Tools::getValue('id_order_state');

        $approval_states = (Configuration::get('GIFT_APPROVAL_STATUS')? explode(',', Configuration::get('GIFT_APPROVAL_STATUS')) : array());
        $history = (Gift::getOrderStateHistory($id_order))? Gift::getOrderStateHistory($id_order) : array();
        $intersect = array_intersect($approval_states, $history);
        if (!empty($approval_states) && in_array($id_order_state, $approval_states) && empty($intersect)) {
            self::generateVoucher($id_cart);
        }
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

    protected function calculateGiftPrice($price, $id_product, $use_tax = true, $use_group_reduction = false, $use_reduc = false, $decimals = 2)
    {
        $product = new Product((int)$id_product, true, (int)$this->context->language->id);
        $id_customer = (int)$this->context->customer->id;

        // Initializations
        $id_group = null;
        if ($id_customer) {
            $id_group = Customer::getDefaultGroupId((int)$this->context->customer->id);
        }

        if (!$id_group) {
            $id_group = (int)Group::getCurrent()->id;
        }

        // Tax
        $id_address = $this->context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')};
        $address = new Address($id_address);

        $id_shop = (int)$this->context->shop->id;
        $id_currency = (int)$this->context->currency->id;
        $id_country = (int)$address->id_country;

        $tax_manager = TaxManagerFactory::getManager($address, Product::getIdTaxRulesGroupByIdProduct((int)$id_product, $this->context));
        $product_tax_calculator = $tax_manager->getTaxCalculator();

        // Add Tax
        if ($use_tax)
            $price = $product_tax_calculator->addTaxes($price);

        // Eco Tax
        if (!isset($product->ecotax) && $product->ecotax) {
            if ($id_currency) {
                $ecotax = Tools::convertPrice($product->ecotax, $id_currency);
            }
            if ($use_tax) {
                // reinit the tax manager for ecotax handling
                $tax_manager = TaxManagerFactory::getManager($address, (int)Configuration::get('PS_ECOTAX_TAX_RULES_GROUP_ID'));
            }
            $ecotax_tax_calculator = $tax_manager->getTaxCalculator();
            $price += $ecotax_tax_calculator->addTaxes($ecotax);
        }

        // Reduction
        $specific_price = SpecificPrice::getSpecificPrice(
            (int)$id_product,
            $id_shop,
            $id_currency,
            $id_country,
            $id_group,
            1);

        $specific_price_reduction = 0;
        if ($specific_price && $use_reduc) {
            if ($specific_price['reduction_type'] == 'amount') {
                $reduction_amount = $specific_price['reduction'];
                if (!$specific_price['id_currency']) {
                    $reduction_amount = Tools::convertPrice($reduction_amount, $id_currency);
                }

                $specific_price_reduction = $reduction_amount;

                // Adjust taxes if required
                if (!$use_tax && $specific_price['reduction_tax']) {
                    $specific_price_reduction = $product_tax_calculator->removeTaxes($specific_price_reduction);
                }
                if ($use_tax && !$specific_price['reduction_tax']) {
                    $specific_price_reduction = $product_tax_calculator->addTaxes($specific_price_reduction);
                }
            } else {
                $specific_price_reduction = $price * $specific_price['reduction'];
            }
        }

        if ($use_reduc) {
            $price -= $specific_price_reduction;
        }

        // Group reduction
        if ($use_group_reduction) {
            $reduction_from_category = GroupReduction::getValueForProduct($id_product, $id_group);
            if ($reduction_from_category !== false) {
                $group_reduction = $price * (float)$reduction_from_category;
            } else {
                $group_reduction = (($reduc = Group::getReductionByIdGroup($id_group)) != 0) ? ($price * $reduc / 100) : 0;
            }
            $price -= $group_reduction;
        }

        $price = Tools::ps_round($price, $decimals);
        if ($price < 0) {
            $price = 0;
        }
        return $price;
    }

    private function setPrice($id_product, $new_price)
    {
        if (!empty($id_product) && !empty($new_price) && $new_price != 0) {
            $product = new Product($id_product);
            $product->price = $new_price;
            $product->update(true);
        }
    }
}
