<?php
/**
 *  Order Fees
 *
 *  @author    motionSeed <ecommerce@motionseed.com>
 *  @copyright 2017 motionSeed. All rights reserved.
 *  @license   https://www.motionseed.com/en/license-module.html
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

if (!class_exists('MotionSeedModule')) {
    include_once(dirname(__FILE__) . '/helpers/motionseed-module/MotionSeedModule.php');
}

class OrderFees extends MotionSeedModule
{
    const IS_FEE = 1;
    const IN_SHIPPING = 2;
    
    const IS_REDUCTION = 4;
    
    const IS_OPTION = 8;
    const IS_CHECKED = 16;
    
    const CONTEXT_CART = 1;
    const CONTEXT_PRODUCT = 2;
    const CONTEXT_PAYMENT = 4;
    const CONTEXT_CARRIER = 8;
    const CONTEXT_MAIL = 16;
    const CONTEXT_PDF = 32;
    const CONTEXT_ADDRESS = 64;
    const CONTEXT_CONFIRMATION = 128;
    const CONTEXT_ALL = 255;
    
    const DATE_FROM_MASK = '0001-01-01 00:00:00';
    const DATE_TO_MASK = '9999-01-01 00:00:00';
    
    const TAX_NONE = 0;
    const TAX_RULE = 2;
    
    const FREE_SHIPPING = 32;
    const IN_PRODUCT_PRICE = 64;
    const QUANTITY_PER_PRODUCT = 256;
    const REDUCTION_AWARE = 512;
    
    const IS_SHIPPING = 34; // IN_SHIPPING + FREE_SHIPPING
    
    const DISABLE_CHECKING = -1;
    
    const CLEAN = 1;
    const IS_ORDER = 2;
    
    public static $WEIGHT_UNITS = array(
        'kg' => 1.0,
        'T' => 1000.0,
        'lb' => 0.45359237,
        'kip' => 453.59237
    );
    
    public static $VOLUME_UNITS = array(
        'm3' => 1.0,
        'cm3' => 0.000001,
        'in3' => 0.000016,
        'ft3' => 0.028317,
        'yd3' => 0.764555
    );
    
    public $weight_unit_default = null;
    public $volume_unit_default = null;
    
    public function __construct()
    {
        $this->name = 'orderfees';
        $this->tab = 'pricing_promotion';
        $this->version = '1.8.9';
        $this->author = 'motionSeed';
        $this->need_instance = 0;
        $this->ps_versions_compliancy['min'] = '1.6.0.0';

        parent::__construct();
        
        $this->displayName = $this->l('Options, Fees and Discounts');
        $this->description = $this->l('Add any kind of options, fees or discounts to your client\'s order');

        $this->error = false;
        $this->secure_key = Tools::encrypt($this->name);
        $this->module_key = '4c0a83cf8d16bec8068ffd6d9ffdeeed';
        
        $this->configurations = array(
            array(
                'name' => 'MS_ORDERFEES_CONDITIONS_DISPLAY_SKU',
                'label' => 'Display SKU on Products Selection',
                'default' => '0'
            ),
            array(
                'name' => 'MS_ORDERFEES_PAYMENT_TPLS',
                'label' => 'List of payment templates',
                'default' => 'payment.tpl,payment_std.tpl,express_checkout_payment.tpl'
            ),
            array(
                'name' => 'MS_ORDERFEES_PAYMENT_DISPLAY_METHOD',
                'label' => 'Display method used for payment fees / reductions',
                'default' => 'amount'
            ),
            array(
                'name' => 'MS_ORDERFEES_DISPLAY_INVOICE_TAX_TAB',
                'label' => 'Display fees on invoice tax tab',
                'default' => '0'
            ),
            array(
                'name' => 'MS_ORDERFEES_REDUCTION_AWARE',
                'label' => 'Reduction aware mode',
                'default' => '1'
            )
        );
        
        $this->weight_unit_default = Tools::strtolower(Configuration::get('PS_WEIGHT_UNIT'));
        $this->volume_unit_default = Tools::strtolower(Configuration::get('PS_DIMENSION_UNIT')) . '3';
        
        $this->type_context = Shop::getContext();
        $this->old_context = Context::getContext();
        
        $this->reduction_aware = (bool)Configuration::get('MS_ORDERFEES_REDUCTION_AWARE');
    }
    
    public function getContent()
    {
        Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminOrderFees'));
    }

    public function registerHooks()
    {
        return parent::registerHooks()
            && $this->registerHook('actionAdminCartRulesListingFieldsModifier')
            && $this->registerHook('actionObjectCartRuleUpdateBefore')
            && $this->registerHook('actionAssociatedRestrictionsPayment')
            && $this->registerHook('actionCartRuleCtor')
            && $this->registerHook('actionCartRuleCheckValidity')
            && $this->registerHook('actionCartRuleGetContextualValueBefore')
            && $this->registerHook('actionCartRuleGetContextualValueAfter')
            && $this->registerHook('actionCartRuleAdd')
            && $this->registerHook('actionCartRuleRemove')
            && $this->registerHook('actionCartGetPackageShippingCost')
            && $this->registerHook('actionAdminCartsControllerHelperDisplay')
            && $this->registerHook('actionAdminOrdersControllerHelperDisplay')
            && $this->registerHook('actionValidateOrder')
            && $this->registerHook('actionGetIDZoneByAddressID')
            && $this->registerHook('actionObjectCartUpdateBefore')
            && $this->registerHook('actionObjectOrderCartRuleAddAfter')
            && $this->registerHook('actionProductPriceCalculation')
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayAdminCartsView')
            && $this->registerHook('displayAdminOrder')
            && $this->registerHook('displayCartRuleBlockCart')
            && $this->registerHook('displayCartRuleBlockCartLayer')
            && $this->registerHook('displayCartRuleShoppingCart')
            && $this->registerHook('displayCartRuleOrderDetail')
            && $this->registerHook('displayCartRuleOrderPayment')
            && $this->registerHook('displayCartRuleInvoiceProductTab')
            && $this->registerHook('displayCartRuleInvoiceB2B')
            && $this->registerHook('displayCartRuleDeliverySlipProductTab')
            && $this->registerHook('displayCartRuleOrderSlipProductTab')
            && $this->registerHook('displayCartRuleAdminOrders')
            && $this->registerHook('displayCartRuleOrderPaymentOption')
            && $this->registerHook('displayCartRuleInvoiceTaxTab')
            && $this->registerHook('displayCartRuleCartVoucher')
            && $this->registerHook('displayCartRuleOrderConfirmation')
            && $this->registerHook('displayCartRuleOrderDetailReturn')
            && $this->registerHook('displayCartRuleOrderDetailNoReturn')
            && $this->registerHook('displayPaymentTop')
            && $this->registerHook('displayCartRuleAddress')
            && $this->registerHook('displayBeforeCarrier')
            && $this->registerHook('displayCartRuleProductAttributes')
            && $this->registerHook('displayCartRuleProductFees');
    }
    
    public function hookActionAdminCartRulesListingFieldsModifier($params)
    {
        $params['where'] = ' AND is_fee = 0';
    }
    
    public function hookActionObjectCartRuleUpdateBefore($params)
    {
        $object = $params['object'];
        
        if ($object->is_fee & self::IS_FEE) {
            $object->quantity = 1;
        }
    }
    
    public function hookActionAssociatedRestrictionsPayment($params)
    {
        $object = $params['object'];
        $type = $params['type'];
        $offset = $params['offset'];
        $limit = $params['limit'];
        $active_only = $params['active_only'];
        
        $array = array('selected' => array(), 'unselected' => array());
        
        $hook_payment = Tools::version_compare('1.7', _PS_VERSION_) ? 'paymentOptions' : 'Payment';
        
        if (Db::getInstance()->getValue(
            'SELECT `id_hook` FROM `'._DB_PREFIX_.'hook` WHERE `name` = \'displayPayment\''
        )) {
            $hook_payment = 'displayPayment';
        }

        if ($offset !== null && $limit !== null) {
            $sql_limit = ' LIMIT '.(int)$offset.', '.(int)($limit+1);
        } else {
            $sql_limit = '';
        }

        if (!Validate::isLoadedObject($object) || $object->{$type.'_restriction'} == 0) {
            $array['selected'] = Db::getInstance()->executeS(
                'SELECT t.*, 1 as selected
                FROM `'._DB_PREFIX_.'module` t
                INNER JOIN `'._DB_PREFIX_.'hook_module` hm ON hm.`id_module` = t.`id_module`
                INNER JOIN `'._DB_PREFIX_.'hook` h ON hm.`id_hook` = h.`id_hook` 
                WHERE h.`name` = \''.pSQL($hook_payment).'\'
                    '.($active_only ? ' AND t.active = 1' : '').
                ' GROUP BY t.id_module
                ORDER BY t.name ASC ' . bqSQL($sql_limit)
            );
        } else {
            $resource = Db::getInstance()->query(
                'SELECT t.*, IF(crt.id_module IS NULL, 0, 1) as selected
                FROM `'._DB_PREFIX_.'module` t
                INNER JOIN `'._DB_PREFIX_.'hook_module` hm ON hm.`id_module` = t.`id_module`
                INNER JOIN `'._DB_PREFIX_.'hook` h ON hm.`id_hook` = h.`id_hook` 
                LEFT JOIN (
                    SELECT id_module FROM `'._DB_PREFIX_.'cart_rule_'. bqSQL($type) .'`
                        WHERE id_cart_rule = '.(int)$object->id.'
                    ) crt
                    ON t.id_module = crt.id_module
                WHERE h.`name` = \''.pSQL($hook_payment).'\' '.($active_only ? ' AND t.active = 1' : '').
                ' GROUP BY t.id_module
                ORDER BY t.name ASC ' . bqSQL($sql_limit),
                false
            );
            
            while ($row = Db::getInstance()->nextRow($resource)) {
                $array[($row['selected'] || $object->{$type.'_restriction'} == 0) ? 'selected' : 'unselected'][] = $row;
            }
        }
        
        return $array;
    }
    
    public function hookActionCartRuleCtor($params)
    {
        $object = &$params['object'];
        
        if (isset($object->is_fee)) {
            return;
        }
        
        Cache::clean('objectmodel_def_CartRule');
        
        $object->is_fee = 0;
        $object->display_visible = 0;
        $object->display_selectable = 0;
        $object->payment_restriction = 0;
        $object->dimension_restriction = 0;
        $object->zipcode_restriction = 0;
        $object->package_restriction = 0;
        
        // Quantity
        $object->quantity = 1;
        $object->unit_value_real = 0;
        $object->unit_value_tax_exc = 0;
        
        // Maximum amount
        $object->maximum_amount = 0;
        $object->maximum_amount_tax = 0;
        $object->maximum_amount_currency = 0;
        $object->maximum_amount_shipping = 0;
        
        // Tax Rule
        $object->tax_rules_group = 0;
        
        if (!property_exists($object, 'definition')) {
            $object::$definition = array('fields' => array());
        }
        
        $object::$definition['fields']['is_fee'] = array(
            'type' => $object::TYPE_INT,
            'validate' => 'isInt'
        );
        $object::$definition['fields']['display_visible'] = array(
            'type' => $object::TYPE_INT,
            'validate' => 'isInt'
        );
        $object::$definition['fields']['display_selectable'] = array(
            'type' => $object::TYPE_INT,
            'validate' => 'isInt'
        );
        $object::$definition['fields']['payment_restriction'] = array(
            'type' => $object::TYPE_BOOL,
            'validate' => 'isBool'
        );
        $object::$definition['fields']['dimension_restriction'] = array(
            'type' => $object::TYPE_BOOL,
            'validate' => 'isBool'
        );
        $object::$definition['fields']['zipcode_restriction'] = array(
            'type' => $object::TYPE_BOOL,
            'validate' => 'isBool'
        );
        
        $object::$definition['fields']['maximum_amount'] = array(
            'type' => $object::TYPE_FLOAT,
            'validate' => 'isFloat'
        );
        $object::$definition['fields']['maximum_amount_tax'] = array(
            'type' => $object::TYPE_BOOL,
            'validate' => 'isBool'
        );
        $object::$definition['fields']['maximum_amount_currency'] = array(
            'type' => $object::TYPE_INT,
            'validate' => 'isInt'
        );
        $object::$definition['fields']['maximum_amount_shipping'] = array(
            'type' => $object::TYPE_BOOL,
            'validate' => 'isBool'
        );
        $object::$definition['fields']['reduction_percent'] = array(
            'type' => $object::TYPE_FLOAT,
            'validate' => 'isFloat'
        );
        $object::$definition['fields']['reduction_amount'] = array(
            'type' => $object::TYPE_FLOAT,
            'validate' => 'isFloat'
        );
        $object::$definition['fields']['package_restriction'] = array(
            'type' => $object::TYPE_BOOL,
            'validate' => 'isBool'
        );
        $object::$definition['fields']['reduction_tax'] = array(
            'type' => $object::TYPE_FLOAT,
            'validate' => 'isFloat'
        );
        $object::$definition['fields']['tax_rules_group'] = array(
            'type' => $object::TYPE_FLOAT,
            'validate' => 'isFloat'
        );
    }
    
    public function hookActionCartRuleCheckValidity(&$params)
    {
        $object = $params['object'];
        $context = $params['context'];
        
        if ($object->is_fee & self::IS_FEE) {
            if ($object->is_fee & self::IS_OPTION) {
                if (Tools::getIsset('option_remove') && $object->id == (int)Tools::getValue('option_remove')) {
                    unset($context->cookie->{'enable_option_' . $object->id});

                    unset($_POST['option_remove']);
                }

                if (Tools::getIsset('option_add') && $object->id == (int)Tools::getValue('option_add')) {
                    $context->cookie->{'enable_option_' . $object->id} = 1;

                    unset($_POST['option_add']);
                }
            }
        
            if (($object->is_fee & self::IS_OPTION)
                && !isset($context->cookie->{'enable_option_' . $object->id})
                && $context->cart->current_type !== self::DISABLE_CHECKING
            ) {
                return array(
                    'message' => 'This option is not selected for this order'
                );
            }
            
            if ($context->cart->id_customer) {
                $quantity_used = Db::getInstance()->getValue(
                    'SELECT count(*)
                        FROM ' . _DB_PREFIX_ . 'orders o
                        LEFT JOIN ' . _DB_PREFIX_ . 'order_cart_rule od
                            ON o.id_order = od.id_order
                        WHERE o.id_customer = ' . (int) $context->cart->id_customer . '
                            AND od.id_cart_rule = ' . (int) $object->id . '
                            AND ' . (int) Configuration::get('PS_OS_ERROR') . ' != o.current_state'
                );
            
                if ($quantity_used + 1 > $object->quantity_per_user) {
                    $object->quantity_per_user = max($object->quantity_per_user, $quantity_used + 1);
                }
            }
            
            if ($object->date_to == self::DATE_TO_MASK) {
                $object->date_to = date('Y-m-d h:i:s', strtotime('+1 year'));
            }
            
            // Payment restriction for Paypal
            if ($object->payment_restriction
                && !isset($this->controller->module)
                && get_class($this->context->controller) == 'FrontController'
            ) {
                $id_cart_rule = (int)Db::getInstance()->getValue(
                    'SELECT crp.id_cart_rule
                    FROM '._DB_PREFIX_.'cart_rule_payment crp
                    INNER JOIN '._DB_PREFIX_.'module m
                        ON crp.id_module = m.id_module
                            AND crp.id_cart_rule = ' . (int)$object->id . '
                            AND m.active = 1
                            AND m.name = "paypal"'
                );
                
                if (!$id_cart_rule) {
                    return array(
                        'message' => 'You cannot use this fee with this payment module'
                    );
                }
            } elseif ($object->payment_restriction) {
                if (!isset($context->controller->module)) {
                    return array(
                        'message' => 'You must choose a payment method before applying this fee to your order'
                    );
                }
                
                $id_cart_rule = (int)Db::getInstance()->getValue(
                    'SELECT crp.id_cart_rule
                    FROM '._DB_PREFIX_.'cart_rule_payment crp
                    INNER JOIN '._DB_PREFIX_.'module m
                        ON crp.id_module = m.id_module
                            AND crp.id_cart_rule = ' . (int)$object->id . '
                            AND m.active = 1
                            AND m.name = "' . pSQL($context->controller->module->name) . '"'
                );
                
                if (!$id_cart_rule) {
                    return array(
                        'message' => 'You cannot use this fee with this payment module'
                    );
                }
            }
            
            // Minimum amount
            if ((int)$object->minimum_amount) {
                $minimum_amount = $object->minimum_amount;
                if ($object->minimum_amount_currency != Context::getContext()->currency->id) {
                    $minimum_amount = Tools::convertPriceFull(
                        $minimum_amount,
                        new Currency($object->minimum_amount_currency),
                        Context::getContext()->currency
                    );
                }

                $cartTotal = $context->cart->getOrderTotal($object->minimum_amount_tax, Cart::ONLY_PRODUCTS);
                if ($object->minimum_amount_shipping) {
                    $cartTotal += $context->cart->getOrderTotal($object->minimum_amount_tax, Cart::ONLY_SHIPPING);
                }
                $products = $context->cart->getProducts();
                $cart_rules = $context->cart->getCartRules();

                foreach ($cart_rules as &$cart_rule) {
                    if ($cart_rule['gift_product']) {
                        foreach ($products as &$product) {
                            if (empty($product['gift'])
                                && $product['id_product'] == $cart_rule['gift_product']
                                && $product['id_product_attribute'] == $cart_rule['gift_product_attribute']
                            ) {
                                $cartTotal = Tools::ps_round(
                                    $cartTotal - $product[$object->minimum_amount_tax ? 'price_wt' : 'price'],
                                    (int) $context->currency->decimals * _PS_PRICE_COMPUTE_PRECISION_
                                );
                            }
                        }
                    }
                }

                if ($cartTotal < $minimum_amount) {
                    return array(
                        'message' => 'Minimum amount not reached'
                    );
                }
            }
            
            // Maximum amount
            if ((int)$object->maximum_amount) {
                $maximum_amount = $object->maximum_amount;
                if ($object->maximum_amount_currency != Context::getContext()->currency->id) {
                    $maximum_amount = Tools::convertPriceFull(
                        $maximum_amount,
                        new Currency($object->maximum_amount_currency),
                        Context::getContext()->currency
                    );
                }

                $cartTotal = $context->cart->getOrderTotal($object->maximum_amount_tax, Cart::ONLY_PRODUCTS);
                
                if ($object->maximum_amount_shipping) {
                    $cartTotal += $context->cart->getOrderTotal($object->maximum_amount_tax, Cart::ONLY_SHIPPING);
                }

                if ($cartTotal >= $maximum_amount) {
                    return array(
                        'message' => 'Maximum amount reached'
                    );
                }
            }
            
            // Zipcode restriction
            if ($object->zipcode_restriction) {
                if (!$context->cart->id_address_delivery) {
                    return array(
                        'message' => 'You must choose a delivery address before applying this fee to your order'
                    );
                }
                
                $address = Db::getInstance()->getRow(
                    'SELECT a.id_country, a.postcode
                        FROM '._DB_PREFIX_.'address a
                        WHERE a.id_address = ' . (int)$context->cart->id_address_delivery
                );
                
                $id_country = $address['id_country'];
                $postcode = trim(Tools::strtolower($address['postcode']));
            
                $zipcode_rule_groups = $this->getZipcodeRuleGroups($object);
                
                foreach (array_keys($zipcode_rule_groups) as $id_zipcode_rule_group) {
                    $zipcode_rules = $this->getZipcodeRules($object, $id_zipcode_rule_group);
                    
                    foreach ($zipcode_rules as $zipcode_rule) {
                        if ($zipcode_rule['type'] != '' && $zipcode_rule['type'] != $id_country) {
                            continue;
                        }
                        
                        $operator = $zipcode_rule['operator'];
                        $values = explode(',', $zipcode_rule['value']);
                        
                        foreach ($values as $value) {
                            $value = trim(Tools::strtolower($value));
                            
                            if ($operator == 'begin') {
                                if (strpos($postcode, $value) === 0) {
                                    continue 3;
                                }
                            } elseif ($operator == 'end') {
                                if (strrpos($postcode, $value) + Tools::strlen($value) === Tools::strlen($postcode)) {
                                    continue 3;
                                }
                            } else {
                                $cmp = ($postcode > $value ? 1 : ($postcode == $value ? 0 : - 1));
                                
                                if ($operator == '=' && $cmp == 0) {
                                    continue 3;
                                } elseif ($operator == '>' && $cmp > 0) {
                                    continue 3;
                                } elseif ($operator == '<' && $cmp < 0) {
                                    continue 3;
                                } elseif ($operator == '>=' && $cmp >= 0) {
                                    continue 3;
                                } elseif ($operator == '<=' && $cmp <= 0) {
                                    continue 3;
                                } elseif ($operator == '!=' && $cmp != 0) {
                                    continue 3;
                                }
                            }
                        }
                    }
                    
                    return array(
                        'message' => 'You cannot use this fee with these zipcodes'
                    );
                }
            }
            
            // Dimension restriction
            if ($object->dimension_restriction) {
                $dimensions_available = array('width', 'height', 'depth', 'weight', 'volume');
                $dimensions_products = array('product' => array(), 'all' => array());
                
                $products = $context->cart->getProducts();
                
                foreach ($products as $product) {
                    foreach ($dimensions_available as $dim) {
                        if (isset($product[$dim])) {
                            $dimensions_products['product'][$dim][] = $product[$dim];
                            
                            if (!isset($dimensions_products['all'][$dim])) {
                                $dimensions_products['all'][$dim][0] = 0;
                            }
                            
                            $dimensions_products['all'][$dim][0] += ($product[$dim] * $product['quantity']);
                        }
                    }
                    
                    $volume = $product['height'] * $product['width'] * $product['depth'];
                    
                    $dimensions_products['product']['volume'][] = $volume;
                    
                    if (!isset($dimensions_products['all']['volume'])) {
                        $dimensions_products['all']['volume'][0] = 0;
                    }
                    
                    $dimensions_products['all']['volume'][0] += ($volume * $product['quantity']);
                }
                
                if (!empty($dimensions_products['product'])) {
                    $dimension_rule_groups = $this->getDimensionRuleGroups($object);
                
                    foreach ($dimension_rule_groups as $id_dimension_rule_group => $dimension_rule_group) {
                        $base = $dimension_rule_group['base'];
                        $dimension_rules = $this->getDimensionRules($object, $id_dimension_rule_group);

                        foreach ($dimension_rules as $dimension_rule) {
                            $type = $dimension_rule['type'];
                            $operator = $dimension_rule['operator'];
                            $values = explode(',', $dimension_rule['value']);

                            foreach ($values as $value) {
                                $dimensions = $dimensions_products[$base][$type];
                                $value = trim(Tools::strtolower($value));

                                foreach ($dimensions as $dimension) {
                                    $cmp = ($dimension > $value ? 1 : ($dimension == $value ? 0 : - 1));

                                    if ($operator == '=' && $cmp == 0) {
                                        continue 4;
                                    } elseif ($operator == '>' && $cmp > 0) {
                                        continue 4;
                                    } elseif ($operator == '<' && $cmp < 0) {
                                        continue 4;
                                    } elseif ($operator == '>=' && $cmp >= 0) {
                                        continue 4;
                                    } elseif ($operator == '<=' && $cmp <= 0) {
                                        continue 4;
                                    } elseif ($operator == '!=' && $cmp != 0) {
                                        continue 4;
                                    }
                                }
                            }
                        }

                        return array(
                            'message' => 'You cannot use this fee with these dimensions'
                        );
                    }
                }
            }
        }
    }
    
    public function hookActionCartRuleGetContextualValueBefore(&$params)
    {
        $object = $params['object'];
        
        if (!($object->is_fee & self::IS_FEE)) {
            return;
        }
        
        if ($object->reduction_product == 0) {
            $params['filter'] = CartRule::FILTER_ACTION_ALL_NOCAP;
        }
        
        if ($object->reduction_tax == self::TAX_RULE) {
            $object->last_use_tax = $params['use_tax'];
            
            if ($object->reduction_percent) {
                $params['use_tax'] = false;
            } else {
                $params['use_tax'] = true;
            }
        }
    }
    
    public function hookActionCartRuleGetContextualValueAfter($params)
    {
        $object = $params['object'];
        $context = $params['context'];
        $use_tax = $params['use_tax'];
        $current_filter = $params['current_filter'];
        $contextual_value = &$params['contextual_value'];
        
        if ($object->is_fee & self::IS_SHIPPING) {
            $contextual_value = 0;
        } elseif (($object->is_fee & self::IS_FEE) && $object->package_restriction > 0) {
            $contextual_value = 0;
        } elseif (($object->is_fee & self::IS_FEE) && $current_filter != CartRule::FILTER_ACTION_ALL_NOCAP) {
            $type = ($object->is_fee & self::IS_REDUCTION) ? 1 : -1;
            
            if ($context->cart->current_type != Cart::ONLY_DISCOUNTS) {
                $contextual_value = $contextual_value*$type;
            } else {
                $contextual_value = 0;
            }
        } elseif (($object->is_fee & self::IS_FEE) && ($object->is_fee & self::IS_REDUCTION)) {
            $contextual_value = $contextual_value*-1;
        }
        
        if (isset($object->last_use_tax)) {
            $tax_address_type = Configuration::get('PS_TAX_ADDRESS_TYPE');
            $tax_rules_group = $object->tax_rules_group;
            
            if ($object->reduction_percent) {
                if ($object->last_use_tax) {
                    $address = null;

                    if (is_object($context->cart) && $context->cart->{$tax_address_type} != null) {
                        $address = $context->cart->{$tax_address_type};
                    }

                    $contextual_value *= (1 + ($this->getTaxesRate($tax_rules_group, new Address($address)) / 100));
                    
                    $object->unit_value_real = $contextual_value;
                } else {
                    $object->unit_value_tax_exc = $contextual_value;
                }
            } else {
                if (!$object->last_use_tax) {
                    $address = null;

                    if (is_object($context->cart) && $context->cart->{$tax_address_type} != null) {
                        $address = $context->cart->{$tax_address_type};
                    }

                    $contextual_value /= (1 + ($this->getTaxesRate($tax_rules_group, new Address($address)) / 100));

                    $object->unit_value_tax_exc = $contextual_value;
                } else {
                    $object->unit_value_real = $contextual_value;
                }
            }
        } else {
            if ($use_tax) {
                $object->unit_value_real = $contextual_value;
            } else {
                $object->unit_value_tax_exc = $contextual_value;
            }
        }
        
        if (($object->is_fee & self::IS_OPTION)
            && !isset($context->cookie->{'enable_option_' . $object->id})
            && $context->cart->current_type == Cart::BOTH
        ) {
            $contextual_value = 0;
        }
        
        if (!$object->is_fee && $this->reduction_aware && ($context->cart->current_type != self::REDUCTION_AWARE)) {
            $cart_amount = $context->cart->getOrderTotal($use_tax, Cart::ONLY_PRODUCTS);
            
            if ($contextual_value >= $cart_amount) {
                $fees_total = 0;
                
                $fees = $this->getFeesByCart($context->cart);
                
                $dummy = self::REDUCTION_AWARE;
                $this->swapVariables($dummy, $context->cart->current_type);
                
                foreach ($fees as $fee) {
                    $cart_rule = new CartRule($fee['id_cart_rule']);

                    $fees_total += $cart_rule->getContextualValue($use_tax, $context);
                }
                
                $reduction_amount = $object->getContextualValue($use_tax, $context, CartRule::FILTER_ACTION_ALL_NOCAP);
                
                $this->swapVariables($dummy, $context->cart->current_type);
                
                $contextual_value = min($contextual_value - $fees_total, $reduction_amount);
                
                $context->cart->fees_total_discounts[$object->id] = $contextual_value;
            }
        }
        
        if ($object->is_fee & self::QUANTITY_PER_PRODUCT) {
            $reflection = new ReflectionMethod('CartRule', 'checkProductRestrictions');
            $reflection->setAccessible(true);
            
            $products_restrictions = $reflection->invoke($object, $context, true, false);
        
            if (is_array($products_restrictions)) {
                $all_products = (count($products_restrictions) == 0);
                $quantity = 0;
                
                foreach ($context->cart->getProducts() as $product) {
                    $key = (int)$product['id_product'].'-'.(int)$product['id_product_attribute'];

                    if ($all_products || in_array($key, $products_restrictions)) {
                        $quantity += $product['cart_quantity'];
                    }
                }
                
                $object->quantity = $quantity;
                $contextual_value *= $quantity;
            }
        }
    }
    
    public function hookActionCartRuleAdd($params)
    {
        $id_cart_rule = $params['id_cart_rule'];
        
        $cart_rule = new CartRule($id_cart_rule, Configuration::get('PS_LANG_DEFAULT'));
        
        if (!($cart_rule->is_fee & self::IS_OPTION)) {
            return;
        }
        
        $cookie = &$this->context->cookie;
        $used_option_value = 1;
            
        if (isset($this->context->cart) && is_object($this->context->cart)) {
            $used_option_value = $this->context->cart->id;
        }
        
        if (isset($cookie->{'used_option_' . $cart_rule->id})
            && $cookie->{'used_option_' . $cart_rule->id} != $used_option_value) {
            unset($cookie->{'enable_option_' . $cart_rule->id});
            unset($cookie->{'used_option_' . $cart_rule->id});
        }
        
        if (($cart_rule->is_fee & self::IS_CHECKED) && !isset($cookie->{'used_option_' . $cart_rule->id})) {
            $cookie->{'enable_option_' . $cart_rule->id} = 1;
        }
        
        $cookie->{'used_option_' . $cart_rule->id} = $used_option_value;
    }
    
    public function hookActionCartRuleRemove($params)
    {
        $id_cart_rule = $params['id_cart_rule'];
        
        if (Tools::getIsset('deleteDiscount')) {
            $id_cart_rule = (int) Tools::getValue('deleteDiscount');
            $cart_rule = new CartRule($id_cart_rule, Configuration::get('PS_LANG_DEFAULT'));
            if ($cart_rule->id && ($cart_rule->is_fee & self::IS_FEE)) {
                return true;
            }
        }
    }
    
    public function hookActionCartGetPackageShippingCost($params)
    {
        $object = $params['object'];
        
        $items = Db::getInstance()->executeS(
            'SELECT cr.id_cart_rule, c.id_carrier, cr.package_restriction
            FROM '._DB_PREFIX_.'cart_rule cr
            LEFT JOIN '._DB_PREFIX_.'cart_cart_rule ccr
                ON cr.id_cart_rule = ccr.id_cart_rule
                    AND ccr.id_cart = ' . (int) $object->id . '
            LEFT JOIN '._DB_PREFIX_.'cart_rule_carrier crc
                ON cr.id_cart_rule = crc.id_cart_rule 
            LEFT JOIN '._DB_PREFIX_.'carrier c 
                ON c.id_reference = crc.id_carrier
                    AND c.deleted = 0
            WHERE cr.is_fee & ' . (int) self::IS_FEE . '
                AND (cr.is_fee & ' . (int) self::IS_SHIPPING . ' OR cr.package_restriction > 0)
                AND cr.active = 1
            ORDER BY cr.priority ASC'
        );
        
        if (empty($items)) {
            return;
        }
        
        $product_list = $object->getProducts();
        $total = &$params['total'];
        $use_tax = &$params['use_tax'];
        $return = &$params['return'];
        $id_carrier = $params['id_carrier'];
        $weight = 0;
        $volume = 0;
        
        // Address
        if (Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_invoice') {
            $cart = &$params['object'];
            $address_id = (int)$cart->id_address_invoice;
        } elseif (count($product_list)) {
            $prod = current($product_list);
            $address_id = (int)$prod['id_address_delivery'];
        } else {
            $address_id = null;
        }
        if (!Address::addressExists($address_id)) {
            $address_id = null;
        }
        
        // Products
        foreach ($product_list as $product) {
            if (isset($product['weight'])) {
                $weight += $product['weight'];
            }

            if (isset($product['height']) && isset($product['width']) && isset($product['depth'])) {
                $volume += (($product['height'] * $product['width'] * $product['depth']) * $product['cart_quantity']);
            }
        }
        
        // Carrier
        if (empty($id_carrier)) {
            $id_carrier = Configuration::get('PS_CARRIER_DEFAULT');
        }

        $carrier = new Carrier((int)$id_carrier, Configuration::get('PS_LANG_DEFAULT'));

        foreach ($items as &$item) {
            if ($item['id_carrier'] == null || $item['id_carrier'] == $id_carrier) {
                $cart_rule = new CartRule($item['id_cart_rule']);
                
                if ($cart_rule->cart_rule_restriction) {
                    $other_items = $items;
                    
                    foreach ($other_items as $index => $other_item) {
                        if ($cart_rule->id == $other_item['id_cart_rule']) {
                            continue;
                        }
                        
                        $combinable = Db::getInstance()->getValue(
                            'SELECT id_cart_rule_1
                            FROM '._DB_PREFIX_.'cart_rule_combination
                            WHERE (
                                id_cart_rule_1 = ' . (int) $cart_rule->id . '
                                AND id_cart_rule_2 = ' . (int) $other_item['id_cart_rule'] . '
                            ) OR (
                                id_cart_rule_2 = ' . (int) $cart_rule->id . '
                                AND id_cart_rule_1 = ' . (int) $other_item['id_cart_rule'] . '
                            )'
                        );
                        
                        if (!$combinable) {
                            unset($items[$index]);
                        }
                    }
                    
                    $cart_rule->cart_rule_restriction = 0;
                }
                    
                if (!$cart_rule->checkValidity($this->context, false, false, false)) {
                    continue;
                }
                
                if ($cart_rule->is_fee & self::FREE_SHIPPING) {
                    $total = 0;
                    $return = true;
                    
                    return;
                }
                    
                if ($item['package_restriction'] > 0) {
                    $package_rule_groups = $this->getPackageRuleGroups($cart_rule);

                    foreach ($package_rule_groups as $package_rule_group) {
                        $volumetric_weight = 0;

                        switch ($package_rule_group['unit']) {
                            case 'kg/m3':
                                $volumetric_weight = $this->volumeTo($volume, 'm3') * $package_rule_group['ratio'];
                                break;
                            case 'cm3/kg':
                                $volumetric_weight = $this->volumeTo($volume, 'cm3') / $package_rule_group['ratio'];
                                break;
                        }

                        $weight = max($volumetric_weight, $this->weightTo($weight, 'kg'));
                        $id_package_rule_group = $package_rule_group['id_package_rule_group'];

                        $package_rule = Db::getInstance()->getRow(
                            'SELECT pr.*, pr.currency,
                                IF(
                                    pr.divider = 1,
                                    pr.value,
                                    ((CEIL(' . pSQL($weight) . ' / pr.round) * pr.round) * pr.value) / pr.divider 
                                ) AS price
                                FROM '._DB_PREFIX_.'cart_rule_package_rule pr
                                WHERE pr.id_package_rule_group = ' . (int)$id_package_rule_group . '
                                    AND CEIL(' . pSQL($weight) . ' / pr.round) * pr.round
                                        BETWEEN pr.range_start AND pr.range_end'
                        );

                        $carrier_tax = 0;

                        // Select carrier tax
                        if ($use_tax && !Tax::excludeTaxeOption()) {
                            $address = Address::initialize((int)$address_id);

                            if (Configuration::get('PS_ATCP_SHIPWRAP')) {
                                // With PS_ATCP_SHIPWRAP, pre-tax price is deduced
                                // from post tax price, so no $carrier_tax here
                                // even though it sounds weird.
                                $carrier_tax = 0;
                            } else {
                                $carrier_tax = $carrier->getTaxesRate($address);
                            }
                        }

                        $package_rule['price'] *= 1 + ($carrier_tax / 100);

                        $total += (float)Tools::ps_round(
                            (float)$package_rule['price'],
                            (Currency::getCurrencyInstance(
                                (int)$package_rule['currency']
                            )->decimals * _PS_PRICE_DISPLAY_PRECISION_)
                        );
                    }
                } else {
                    $cart_rule->is_fee &= ~$this->getConstant('IN_SHIPPING');

                    $total += $cart_rule->getContextualValue(
                        $use_tax,
                        Context::getContext(),
                        CartRule::FILTER_ACTION_ALL_NOCAP
                    );
                }
            }
        }
        
        $total = max($total, 0);
    }
    
    public function hookActionAdminCartsControllerHelperDisplay($params)
    {
        $controller = &$params['controller'];
        
        $cart = $controller->tpl_view_vars['cart'];
        
        $controller->tpl_view_vars['discounts'] = $this->getCartRulesByCart($cart);
    }
    
    public function hookActionAdminOrdersControllerHelperDisplay(&$params)
    {
        $controller = $params['controller'];
        
        if (!property_exists($controller, 'currentIndex')) {
            $controller::$currentIndex = Tools::getAdminTokenLite('AdminOrders');
        }
        
        if (Tools::isSubmit('id_order') && Tools::getValue('id_order') > 0) {
            $order = new Order(Tools::getValue('id_order'));
            
            if (!Validate::isLoadedObject($order)) {
                $controller->errors[] = Tools::displayError('The order cannot be found within your database.');
            }
        }
        
        if (Tools::isSubmit('submitNewFee') && isset($order)) {
            if ($controller->tabAccess['edit'] === '1') {
                if (!Tools::getValue('fee_name')) {
                    $controller->errors[] = Tools::displayError('You must specify a name in order to create a new fee');
                } else {
                    if ($order->hasInvoice()) {
                        if (!Tools::isSubmit('fee_all_invoices')) {
                            $order_invoice = new OrderInvoice(Tools::getValue('fee_invoice'));
                            if (!Validate::isLoadedObject($order_invoice)) {
                                throw new PrestaShopException('Can\'t load Order Invoice object');
                            }
                        }
                    }
                    $cart_rules = array();
                    switch (Tools::getValue('fee_type')) {
                        case 1:
                            if (Tools::getValue('fee_value') < 100) {
                                if (isset($order_invoice)) {
                                    $cart_rules[$order_invoice->id]['value_tax_incl'] = Tools::ps_round(
                                        $order_invoice->total_paid_tax_incl * Tools::getValue('fee_value') / 100,
                                        2
                                    );
                                    
                                    $cart_rules[$order_invoice->id]['value_tax_excl'] = Tools::ps_round(
                                        $order_invoice->total_paid_tax_excl * Tools::getValue('fee_value') / 100,
                                        2
                                    );
                                    $this->applyFeeOnInvoice(
                                        $order_invoice,
                                        $cart_rules[$order_invoice->id]['value_tax_incl'],
                                        $cart_rules[$order_invoice->id]['value_tax_excl']
                                    );
                                } elseif ($order->hasInvoice()) {
                                    $order_invoices_collection = $order->getInvoicesCollection();
                                    foreach ($order_invoices_collection as $order_invoice) {
                                        $cart_rules[$order_invoice->id]['value_tax_incl'] = Tools::ps_round(
                                            $order_invoice->total_paid_tax_incl * Tools::getValue('fee_value') / 100,
                                            2
                                        );
                                        
                                        $cart_rules[$order_invoice->id]['value_tax_excl'] = Tools::ps_round(
                                            $order_invoice->total_paid_tax_excl * Tools::getValue('fee_value') / 100,
                                            2
                                        );
                                        $this->applyFeeOnInvoice(
                                            $order_invoice,
                                            $cart_rules[$order_invoice->id]['value_tax_incl'],
                                            $cart_rules[$order_invoice->id]['value_tax_excl']
                                        );
                                    }
                                } else {
                                    $cart_rules[0]['value_tax_incl'] = Tools::ps_round(
                                        $order->total_paid_tax_incl * Tools::getValue('fee_value') / 100,
                                        2
                                    );
                                    
                                    $cart_rules[0]['value_tax_excl'] = Tools::ps_round(
                                        $order->total_paid_tax_excl * Tools::getValue('fee_value') / 100,
                                        2
                                    );
                                }
                            } else {
                                $controller->errors[] = Tools::displayError('Fee value is invalid');
                            }
                            break;
                        case 2:
                            if (isset($order_invoice)) {
                                $cart_rules[$order_invoice->id]['value_tax_incl'] = Tools::ps_round(
                                    Tools::getValue('fee_value'),
                                    2
                                );
                                
                                $cart_rules[$order_invoice->id]['value_tax_excl'] = Tools::ps_round(
                                    Tools::getValue('fee_value') / (1 + ($order->getTaxesAverageUsed() / 100)),
                                    2
                                );
                                $this->applyFeeOnInvoice(
                                    $order_invoice,
                                    $cart_rules[$order_invoice->id]['value_tax_incl'],
                                    $cart_rules[$order_invoice->id]['value_tax_excl']
                                );
                            } elseif ($order->hasInvoice()) {
                                $order_invoices_collection = $order->getInvoicesCollection();
                                foreach ($order_invoices_collection as $order_invoice) {
                                    $cart_rules[$order_invoice->id]['value_tax_incl'] = Tools::ps_round(
                                        Tools::getValue('fee_value'),
                                        2
                                    );
                                    
                                    $cart_rules[$order_invoice->id]['value_tax_excl'] = Tools::ps_round(
                                        Tools::getValue('fee_value') / (1 + ($order->getTaxesAverageUsed() / 100)),
                                        2
                                    );
                                    $this->applyFeeOnInvoice(
                                        $order_invoice,
                                        $cart_rules[$order_invoice->id]['value_tax_incl'],
                                        $cart_rules[$order_invoice->id]['value_tax_excl']
                                    );
                                }
                            } else {
                                $cart_rules[0]['value_tax_incl'] = Tools::ps_round(Tools::getValue('fee_value'), 2);
                                $cart_rules[0]['value_tax_excl'] = Tools::ps_round(
                                    Tools::getValue('fee_value') / (1 + ($order->getTaxesAverageUsed() / 100)),
                                    2
                                );
                            }
                            break;
                        default:
                            $controller->errors[] = Tools::displayError('Fee type is invalid');
                    }
                    $res = true;
                    
                    foreach ($cart_rules as &$cart_rule) {
                        $cartRuleObj = new CartRule();
                        $cartRuleObj->is_fee = self::IS_FEE;
                        $cartRuleObj->display_visible = self::CONTEXT_ALL;
                        $cartRuleObj->date_from = date(
                            'Y-m-d H:i:s',
                            strtotime('-1 hour', strtotime($order->date_add))
                        );
                        $cartRuleObj->date_to = date('Y-m-d H:i:s', strtotime('+1 hour'));
                        $cartRuleObj->name[Configuration::get('PS_LANG_DEFAULT')] = Tools::getValue('fee_name');
                        $cartRuleObj->quantity = 1;
                        $cartRuleObj->partial_use = 0;
                        $cartRuleObj->quantity_per_user = 1;
                        if (Tools::getValue('fee_type') == 1) {
                            $cartRuleObj->reduction_percent = Tools::getValue('fee_value');
                        } elseif (Tools::getValue('fee_type') == 2) {
                            $cartRuleObj->reduction_amount = $cart_rule['value_tax_excl'];
                        } elseif (Tools::getValue('fee_type') == 3) {
                            $cartRuleObj->free_shipping = 1;
                        }
                        $cartRuleObj->active = 0;
                        
                        if ($res = $cartRuleObj->add()) {
                            $cart_rule['id'] = $cartRuleObj->id;
                        } else {
                            break;
                        }
                    }
                    if ($res) {
                        foreach ($cart_rules as $id_order_invoice => $cart_rule) {
                            $order_cart_rule = new OrderCartRule();
                            $order_cart_rule->id_order = $order->id;
                            $order_cart_rule->id_cart_rule = $cart_rule['id'];
                            $order_cart_rule->id_order_invoice = $id_order_invoice;
                            $order_cart_rule->name = Tools::getValue('fee_name');
                            $order_cart_rule->value = $cart_rule['value_tax_incl'];
                            $order_cart_rule->value_tax_excl = $cart_rule['value_tax_excl'];
                            $res &= $order_cart_rule->add();
                            $order->total_paid += $order_cart_rule->value;
                            $order->total_paid_tax_incl += $order_cart_rule->value;
                            $order->total_paid_tax_excl += $order_cart_rule->value_tax_excl;
                        }
                        $res &= $order->update();
                    }
                    if ($res) {
                        Tools::redirectAdmin(
                            $controller::$currentIndex . '&id_order=' . $order->id
                            . '&vieworder&conf=4&token='. $controller->token
                        );
                    } else {
                        $controller->errors[] = Tools::displayError('An error occurred on OrderCartRule creation');
                    }
                }
            } else {
                $controller->errors[] = Tools::displayError('You do not have permission to edit here.');
            }
        } elseif (Tools::isSubmit('submitDeleteFee') && isset($order)) {
            if ($controller->tabAccess['edit'] === '1') {
                $order_cart_rule = new OrderCartRule(Tools::getValue('id_order_cart_rule'));
                if (Validate::isLoadedObject($order_cart_rule) && $order_cart_rule->id_order == $order->id) {
                    if ($order_cart_rule->id_order_invoice) {
                        $order_invoice = new OrderInvoice($order_cart_rule->id_order_invoice);
                        if (!Validate::isLoadedObject($order_invoice)) {
                            throw new PrestaShopException('Can\'t load Order Invoice object');
                        }
                        $order_invoice->total_paid_tax_excl += $order_cart_rule->value_tax_excl;
                        $order_invoice->total_paid_tax_incl += $order_cart_rule->value;
                        $order_invoice->update();
                    }
                    $order->total_paid += $order_cart_rule->value;
                    $order->total_paid_tax_incl += $order_cart_rule->value;
                    $order->total_paid_tax_excl += $order_cart_rule->value_tax_excl;
                    $order_cart_rule->delete();
                    $order->update();
                    Tools::redirectAdmin(
                        $controller::$currentIndex . '&id_order=' . $order->id
                        . '&vieworder&conf=4&token=' . $controller->token
                    );
                } else {
                    $controller->errors[] = Tools::displayError('Cannot edit this Fee');
                }
            } else {
                $controller->errors[] = Tools::displayError('You do not have permission to edit here.');
            }
        }
        
        $order = $controller->tpl_view_vars['order'];
        $controller->tpl_view_vars['fees'] = $this->getFeesByOrder($order);
        
        foreach ($controller->tpl_view_vars['discounts'] as $index => $discount) {
            $object = new CartRule($discount['id_cart_rule']);
            
            if ($object->is_fee & self::IS_FEE) {
                unset($controller->tpl_view_vars['discounts'][$index]);
            }
        }
    }
    
    public function hookActionValidateOrder($params)
    {
        $order = $params['order'];
        $cart = $params['cart'];
        
        $cart_rules = $cart->getCartRules();
        
        foreach ($cart_rules as $cart_rule) {
            $package = array(
                'id_carrier' => $order->id_carrier,
                'id_address' => $order->id_address_delivery,
                'products' => $order->product_list
            );
            
            $values = array(
                'tax_incl' => $cart_rule['obj']->getContextualValue(
                    true,
                    $this->context,
                    CartRule::FILTER_ACTION_ALL_NOCAP,
                    $package
                ),
                'tax_excl' => $cart_rule['obj']->getContextualValue(
                    false,
                    $this->context,
                    CartRule::FILTER_ACTION_ALL_NOCAP,
                    $package
                )
            );
            
            if (!$values['tax_excl']) {
                $order->addCartRule(
                    $cart_rule['obj']->id,
                    $cart_rule['obj']->name,
                    $values,
                    0,
                    $cart_rule['obj']->free_shipping
                );
            }
        }
    }
    
    public function paymentRestrictionRefresh()
    {
        CartRule::autoRemoveFromCart();
        CartRule::autoAddToCart();
    }

    public function hookActionGetIDZoneByAddressID()
    {
        static $once = true;
        
        if ($once) {
            $once = false;
            
            $this->paymentRestrictionRefresh();
        }
    }
        
    public function hookActionObjectCartUpdateBefore()
    {
        // Paypal payment restriction refresh
        if (get_class($this->context->controller) == 'FrontController') {
            $this->paymentRestrictionRefresh();
        }
    }
    
    public function hookActionObjectOrderCartRuleAddAfter($params)
    {
        $object = $params['object'];
        
        if (!isset($this->context->cart) || !is_object($this->context->cart)) {
            return;
        }
        
        $cart = $this->context->cart;
        $cart_rules = $cart->getCartRules();
        
        $address = null;
                
        if ($cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')} != null) {
            $address = $cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')};
        }
        
        foreach ($cart_rules as $cart_rule) {
            if ($cart_rule['id_cart_rule'] == $object->id_cart_rule) {
                $tax_rate = $this->getTaxesRate($cart_rule['obj']->tax_rules_group, new Address($address));
                
                $update_data = array(
                    'quantity = ' . pSQL($cart_rule['obj']->quantity),
                    'unit_value_real = ' . pSQL($cart_rule['obj']->unit_value_real * -1),
                    'unit_value_tax_exc = ' . pSQL($cart_rule['obj']->unit_value_tax_exc * -1),
                    'tax_rate = ' . pSQL($tax_rate)
                );
                
                $sql = 'UPDATE ' . _DB_PREFIX_ . 'order_cart_rule
                            SET ' . implode(', ', $update_data) . '
                        WHERE id_order_cart_rule = ' . (int) $object->id;

                return Db::getInstance()->execute($sql);
            }
        }
    }
    
    public function hookActionProductPriceCalculation(&$params)
    {
        $id_product = $params['id_product'];
        
        if (!isset($this->product_price[$id_product])) {
            $this->product_price[$id_product] = 0;
            
            $fees = $this->getFeesByProduct($params['id_product'], self::IN_PRODUCT_PRICE, false);
            
            if (!empty($fees)) {
                $use_tax = $params['use_tax'];
                
                foreach ($fees as $fee) {
                    $this->product_price[$id_product] += ($use_tax ? $fee['value_real'] : $fee['value_tax_exc']);
                }
            }
        }
        
        $params['total'] = $this->product_price[$id_product];
    }

    public function hookDisplayHeader()
    {
        $this->paymentRestrictionRefresh();
        
        if ((int) Configuration::get('PS_BLOCK_CART_AJAX')) {
            if (Tools::version_compare('1.7', _PS_VERSION_)) {
                $this->context->controller->registerJavascript(
                    'modules-orderfees-shoppingcart',
                    'modules/'.$this->name.'/views/js/ps_shoppingcart.js',
                    array('position' => 'bottom', 'priority' => 300)
                );
            } else {
                $this->context->controller->addJS(($this->_path) . 'views/js/ajax-cart.js');
            }
        }
    }
    
    public function hookDisplayAdminOrder($params)
    {
        $order = new Order((int) $params['id_order']);
        
        if (!Validate::isLoadedObject($order)) {
            return;
        }
        
        $controller = $this->context->controller;
        
        if (!property_exists($controller, 'currentIndex')) {
            $controller::$currentIndex = Tools::getAdminTokenLite('AdminOrders');
        }
        
        $this->context->smarty->assign(array(
            'fees' => $this->getFeesByOrder($order),
            'order' => $order,
            'currency' => Currency::getCurrencyInstance($order->id_currency),
            'can_edit' => ($controller->tabAccess['edit'] == 1),
            'current_index' => $controller::$currentIndex,
            'current_id_lang' => $this->context->language->id,
            'invoices_collection' => $order->getInvoicesCollection()
        ));
        
        return $this->display(__FILE__, 'admin-order.tpl');
    }
    
    public function hookDisplayAdminCartsView()
    {
        $cart = new Cart((int) Tools::getValue('id_cart'));
        
        if (!Validate::isLoadedObject($cart)) {
            return;
        }
        
        $this->context->smarty->assign(array(
            'fees' => $this->getFeesByCart($cart),
            'currency' => Currency::getCurrencyInstance($cart->id_currency)
        ));
        
        return $this->display(__FILE__, 'admin-cart.tpl');
    }
    
    public function hookDisplayCartRuleInvoiceProductTab($params)
    {
        return $this->displayFeesOnPDF($params, 'invoice-product-tab.tpl');
    }
    
    public function hookDisplayCartRuleInvoiceB2B($params)
    {
        return $this->displayFeesOnPDF($params, 'invoice-b2b.tpl');
    }
    
    public function hookDisplayCartRuleDeliverySlipProductTab($params)
    {
        return $this->displayFeesOnPDF($params, 'delivery-slip-product-tab.tpl');
    }
    
    public function hookDisplayCartRuleOrderSlipProductTab($params)
    {
        return $this->displayFeesOnPDF($params, 'order-slip-product-tab.tpl');
    }
    
    public function hookDisplayCartRuleAdminOrders()
    {
        return $this->display(__FILE__, 'admin-order-form.tpl');
    }
    
    public function displayFeesOnPDF($params, $template, $context = self::CONTEXT_PDF)
    {
        $order = $params['order'];
        
        $this->context->smarty->assign(array(
            'order' => $order,
            'fees' => $this->getFeesByOrder($order, $context)
        ));
        
        $discounts = $params['discounts'];
        
        if (!count($discounts) || empty($discounts))
            return false;

        foreach ($discounts as $index => $discount) {
            $object = new CartRule($discount['id_cart_rule']);
            
            if ($object->is_fee & self::IS_FEE) {
                unset($discounts[$index]);
            }
        }
        
        $params['smarty']->assign('cart_rules', $discounts);
        
        if (Tools::version_compare('1.7', _PS_VERSION_)) {
            $price_formatter = new PrestaShop\PrestaShop\Adapter\Product\PriceFormatter();
        
            $this->context->smarty->assign(array(
                'tax' => new TaxConfiguration(),
                'price' => $price_formatter
            ));
            
            // return $this->display(__FILE__, '1.7/' . $template);
        }
        
        return $this->display(__FILE__, $template);
    }
    
    public function hookDisplayCartRuleBlockCart(&$params)
    {
        return $this->displayFees($params, 'blockcart.tpl', self::CONTEXT_CART, self::CLEAN);
    }
    
    public function hookDisplayCartRuleBlockCartLayer(&$params)
    {
        return $this->displayFees($params, 'blockcart-layer.tpl', self::CONTEXT_CART);
    }
    
    public function hookDisplayCartRuleShoppingCart(&$params)
    {
        return $this->displayFees($params, 'shopping-cart.tpl', self::CONTEXT_CART, self::CLEAN);
    }
    
    public function hookDisplayCartRuleOrderDetail(&$params)
    {
        return $this->displayFees($params, 'order-detail.tpl', self::CONTEXT_ALL, self::CLEAN | self::IS_ORDER);
    }
    
    public function hookDisplayCartRuleOrderPayment(&$params)
    {
        return $this->displayFees($params, 'order-payment.tpl', self::CONTEXT_PAYMENT, self::CLEAN);
    }
    
    public function hookDisplayCartRuleCartVoucher(&$params)
    {
        $params['discounts'] = $this->context->cart->getCartRules();
        
        $result = $this->displayFees($params, 'cart-voucher.tpl', self::CONTEXT_CART, self::CLEAN);
        
        $cart = $params['smarty']->getTemplateVars('cart');
        
        $price_formatter = new PrestaShop\PrestaShop\Adapter\Product\PriceFormatter();
        
        foreach ($params['discounts'] as $index => &$discount) {
            if (($discount['is_fee'] & self::IS_FEE) && ($discount['is_fee'] & self::CONTEXT_CART)
                || ($discount['is_fee'] & self::IS_SHIPPING)) {
                unset($params['discounts'][$index]);
                
                continue;
            }
            
            if (isset($discount['reduction_percent']) && $discount['reduction_amount'] == '0.00') {
                $discount['reduction_formatted'] = $discount['reduction_percent'].'%';
            } elseif (isset($discount['reduction_amount']) && $discount['reduction_amount'] > 0) {
                $discount['reduction_formatted'] = $price_formatter->format($discount['reduction_amount']);
            }

            $discount['reduction_formatted'] = '-'.$discount['reduction_formatted'];
            $discount['delete_url'] = $this->context->link->getPageLink(
                'cart',
                true,
                null,
                array(
                    'deleteDiscount' => $discount['id_cart_rule'],
                    'token' => Tools::getToken(false),
                )
            );
        }
        
        $cart['vouchers']['added'] = $params['discounts'];
        $params['smarty']->assign('cart', $cart);
        
        return $result;
    }
    
    public function hookDisplayCartRuleOrderConfirmation(&$params)
    {
        return $this->displayFees(
            $params,
            'order-confirmation.tpl',
            self::CONTEXT_CONFIRMATION,
            self::CLEAN | self::IS_ORDER
        );
    }
    
    public function hookDisplayCartRuleOrderDetailReturn(&$params)
    {
        return $this->displayFees(
            $params,
            'order-detail-return.tpl',
            self::CONTEXT_ALL,
            self::CLEAN | self::IS_ORDER
        );
    }
    
    public function hookDisplayCartRuleOrderDetailNoReturn(&$params)
    {
        return $this->displayFees(
            $params,
            'order-detail-no-return.tpl',
            self::CONTEXT_ALL,
            self::CLEAN | self::IS_ORDER
        );
    }
    
    public function hookDisplayPaymentTop(&$params)
    {
        if (!Tools::version_compare('1.7', _PS_VERSION_)) {
            return;
        }
        
        $price_formatter = new PrestaShop\PrestaShop\Adapter\Product\PriceFormatter();
        
        $this->context->smarty->assign(array(
            'tax' => new TaxConfiguration(),
            'price' => $price_formatter
        ));
        
        $payment_options = $params['smarty']->getTemplateVars('payment_options');
        
        foreach ($payment_options as $module_name => &$payment_option) {
            foreach ($payment_option as &$option) {
                $items = Db::getInstance()->executeS(
                    'SELECT cr.*
                    FROM '._DB_PREFIX_.'cart_rule cr
                    INNER JOIN '._DB_PREFIX_.'cart_rule_payment crp
                        ON cr.id_cart_rule = crp.id_cart_rule 
                            AND cr.is_fee > 0 
                            AND cr.payment_restriction = 1
                            AND cr.active = 1
                    INNER JOIN '._DB_PREFIX_.'module m
                        ON m.id_module = crp.id_module
                            AND m.active = 1
                            AND m.name = \'' . pSQL($module_name) . '\''
                );

                if ($items) {
                    $cart_rules = array();
                    $total = 0;
                    $shop_is_active = Shop::isFeatureActive();

                    foreach ($items as $item) {
                        if ($item['shop_restriction'] && $shop_is_active) {
                            $id_shop = Db::getInstance()->getValue(
                                'SELECT id_shop FROM '._DB_PREFIX_.'cart_rule_shop
                                    WHERE id_cart_rule = '.(int)$item['id_cart_rule'].'
                                        AND id_shop = '.(int)$this->context->shop->id
                            );

                            if (!$id_shop) {
                                return;
                            }
                        }

                        $item['obj'] = new CartRule($item['id_cart_rule']);

                        $is_fee = $item['obj']->is_fee;

                        $item['obj']->is_fee &= ~$this->getConstant('IN_SHIPPING');

                        $total += $item['obj']->getContextualValue(
                            true,
                            $this->context,
                            CartRule::FILTER_ACTION_ALL_NOCAP
                        );

                        $item['obj']->is_fee = $is_fee;

                        $cart_rules[] = $item;
                    }

                    Hook::exec('actionPaymentModuleDisplay', array(
                        'object' => &$this,
                        'total' => &$total,
                        'cart_rules' => $cart_rules,
                        'file' => $option,
                        'template' => null
                    ));

                    $this->context->smarty->assign(array(
                        'total' => $total,
                        'cart_rules' => $cart_rules,
                        'module' => $this,
                        'display_method' => Configuration::get('MS_ORDERFEES_PAYMENT_DISPLAY_METHOD')
                    ));

                    $option['call_to_action_text'] .= html_entity_decode(
                        $this->display(__FILE__, '1.7/payment.tpl')
                    );
                }
            }
        }
        
        $params['smarty']->assign('payment_options', $payment_options);
    }
    
    public function hookDisplayCartRuleAddress(&$params)
    {
        return $this->displayFees($params, 'address.tpl', self::CONTEXT_ADDRESS);
    }
    
    public function hookDisplayBeforeCarrier(&$params)
    {
        return $this->displayFees($params, 'carrier.tpl', self::CONTEXT_CARRIER);
    }
    
    public function hookDisplayCartRuleProductAttributes(&$params)
    {
        return $this->displayFees($params, 'product.tpl', self::CONTEXT_PRODUCT);
    }
    
    public function hookDisplayCartRuleProductFees($params)
    {
        $fees = $this->getFeesByProduct($params['product']);
        
        if (empty($fees)) {
            return;
        }
        
        $this->context->smarty->assign(array(
            'fees' => $fees,
            'module' => $this
        ));
        
        if (Tools::version_compare('1.7', _PS_VERSION_)) {
            $price_formatter = new PrestaShop\PrestaShop\Adapter\Product\PriceFormatter();
        
            $this->context->smarty->assign(array(
                'tax' => new TaxConfiguration(),
                'price' => $price_formatter
            ));
            
            return $this->display(__FILE__, '1.7/' . 'product-fees.tpl');
        }
        
        return $this->display(__FILE__, 'product-fees.tpl');
    }
    
    public function displayFees(&$params, $template, $context = self::CONTEXT_ALL, $setting = false)
    {
        if ($setting & self::IS_ORDER) {
            $order = $params['order'];
            
            $params['discounts'] = $this->getFeesByOrder(is_array($order) ? $order['id'] : $order);
        } else {
            $this->context->cart->current_type = self::DISABLE_CHECKING;
        
            CartRule::autoRemoveFromCart();
            CartRule::autoAddToCart();

            $params['discounts'] = $this->context->cart->getCartRules();
        }
        
        $fees = array();
        $discounts = $params['discounts'];
        $cookie = Context::getContext()->cookie;
        
        foreach ($discounts as $index => $discount) {
            if (!($discount['is_fee'] & self::IS_FEE)) {
                continue;
            }
            
            if ($discount['is_fee'] & self::IS_OPTION) {
                $option_selected = isset($cookie->{'enable_option_' . $discount['id_cart_rule']});

                if (($discount['display_selectable'] & $context)
                    || (($discount['display_visible'] & $context) && $option_selected)
                ) {
                    $discount['is_checked'] = isset($cookie->{'enable_option_' . $discount['id_cart_rule']});

                    $fees[] = $discount;
                }
            } else {
                if ($discount['display_visible'] & $context) {
                    $fees[] = $discount;
                }
            }

            if ($setting & self::CLEAN) {
                unset($discounts[$index]);
            }

            if ($discount['is_fee'] & self::IS_SHIPPING) {
                unset($discounts[$index]);
            }
        }
        
        $this->context->smarty->assign(array(
            'fees' => $fees,
            'module' => $this
        ));
        
        if (isset($params['smarty'])) {
            $params['smarty']->assign('discounts', $discounts);
        }
        
        if (Tools::version_compare('1.7', _PS_VERSION_)) {
            $price_formatter = new PrestaShop\PrestaShop\Adapter\Product\PriceFormatter();
        
            $this->context->smarty->assign(array(
                'tax' => new TaxConfiguration(),
                'price' => $price_formatter
            ));
            
            return $this->display(__FILE__, '1.7/' . $template);
        }
        
        return $this->display(__FILE__, $template);
    }
    
    public function hookDisplayCartRuleOrderPaymentOption($params)
    {
        $payment_option = $params['payment_option'];
        
        if ($payment_option instanceof Core_Business_Payment_PaymentOption) {
            $module_name = $payment_option->getModuleName();
            
            $items = Db::getInstance()->executeS(
                'SELECT cr.*
                FROM '._DB_PREFIX_.'cart_rule cr
                INNER JOIN '._DB_PREFIX_.'cart_rule_payment crp
                    ON cr.id_cart_rule = crp.id_cart_rule 
                        AND cr.is_fee > 0 
                        AND cr.payment_restriction = 1
                        AND cr.active = 1
                INNER JOIN '._DB_PREFIX_.'module m
                    ON m.id_module = crp.id_module
                        AND m.active = 1
                        AND m.name = \'' . pSQL($module_name) . '\''
            );
            
            if ($items) {
                $cart_rules = array();
                $total = 0;
                $shop_is_active = Shop::isFeatureActive();
                
                foreach ($items as $item) {
                    if ($item['shop_restriction'] && $shop_is_active) {
                        $id_shop = Db::getInstance()->getValue(
                            'SELECT id_shop FROM '._DB_PREFIX_.'cart_rule_shop
                                WHERE id_cart_rule = '.(int)$item['id_cart_rule'].'
                                    AND id_shop = '.(int)Context::getContext()->shop->id
                        );
                        
                        if (!$id_shop) {
                            return;
                        }
                    }
                    
                    $item['obj'] = new CartRule($item['id_cart_rule']);
                    
                    $is_fee = $item['obj']->is_fee;
                    $item['obj']->is_fee &= ~$this->getConstant('IN_SHIPPING');
                    
                    $total += $item['obj']->getContextualValue(
                        true,
                        $this->context,
                        CartRule::FILTER_ACTION_ALL_NOCAP
                    );
                    
                    $item['obj']->is_fee = $is_fee;
                    
                    $cart_rules[] = $item;
                }
                
                Hook::exec('actionPaymentModuleDisplay', array(
                    'object' => &$this,
                    'total' => &$total,
                    'cart_rules' => $cart_rules,
                    'file' => null,
                    'template' => null
                ));
                
                $this->context->smarty->assign(array(
                    'total' => $total,
                    'cart_rules' => $cart_rules,
                    'module' => $this,
                    'display_method' => Configuration::get('MS_ORDERFEES_PAYMENT_DISPLAY_METHOD')
                ));
                
                return $this->display('orderfees', 'payment.tpl');
            }
        }
    }
    
    public function hookDisplayCartRuleInvoiceTaxTab($params)
    {
        if (!Configuration::get('MS_ORDERFEES_DISPLAY_INVOICE_TAX_TAB')) {
            return;
        }
        
        $params['discounts'] = $this->getFeesByOrder($params['order']);
        
        if (count($params['discounts']) > 0) {
            $params['smarty']->assign('has_line', true);
        }
        
        return $this->displayFeesOnPDF($params, 'invoice-tax-tab.tpl');
    }
    
    public function getFeesByOrder($order, $context = self::CONTEXT_ALL)
    {
        $order = !is_numeric($order) ? $order : new Order($order);
        
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT ocr.*, cr.*, crl.`id_lang`, crl.`name`
            FROM `' . _DB_PREFIX_ . 'order_cart_rule` ocr
            LEFT JOIN `' . _DB_PREFIX_ . 'cart_rule` cr
                ON cr.`id_cart_rule` = ocr.`id_cart_rule`
            LEFT JOIN `'._DB_PREFIX_.'cart_rule_lang` crl
                ON cr.`id_cart_rule` = crl.`id_cart_rule`
					AND crl.id_lang = ' . (int) Context::getContext()->language->id . '
            WHERE (cr.is_fee & ' . (int) self::IS_FEE . ')
                AND (cr.display_visible & ' . (int) $context . ')
                AND ocr.`id_order` = ' . (int) $order->id
        );
        
        foreach ($result as &$row) {
            $row['obj'] = new CartRule($row['id_cart_rule'], (int) $order->id);
        }
        
        return $result;
    }
    
    public function getFeesByCart($cart)
    {
        $cart = !is_numeric($cart) ? $cart : new Cart($cart);
        
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT *
                FROM `' . _DB_PREFIX_ . 'cart_cart_rule` cd
                LEFT JOIN `' . _DB_PREFIX_ . 'cart_rule` cr ON cd.`id_cart_rule` = cr.`id_cart_rule`
                LEFT JOIN `' . _DB_PREFIX_ . 'cart_rule_lang` crl ON (
                    cd.`id_cart_rule` = crl.`id_cart_rule`
                    AND crl.id_lang = ' . (int) $cart->id_lang . '
                    )
                WHERE (cr.is_fee & ' . (int) self::IS_FEE . ') AND `id_cart` = ' . (int) $cart->id
        );
        
        $virtual_context = Context::getContext()->cloneContext();
        $virtual_context->cart = $cart;
        
        foreach ($result as &$row) {
            $row['obj'] = new CartRule($row['id_cart_rule'], (int) $cart->id_lang);
            $row['value_real'] = $row['obj']->getContextualValue(true, $virtual_context);
            $row['value_tax_exc'] = $row['obj']->getContextualValue(false, $virtual_context);
            $row['id_discount'] = $row['id_cart_rule'];
            $row['description'] = $row['name'];
        }
        
        return $result;
    }
    
    public function getFeesByProduct($product, $filter = null, $visibility = self::CONTEXT_PRODUCT)
    {
        if (is_int($product)) {
            $product = new Product($product);
        }
        
        if (!Validate::isLoadedObject($product)) {
            return;
        }
                
        $context = Context::getContext()->cloneContext();
        $fees = array();

        $items = Db::getInstance()->executeS(
            'SELECT cr.id_cart_rule, crl.name
            FROM '._DB_PREFIX_.'cart_rule cr
            LEFT JOIN `'._DB_PREFIX_.'cart_rule_lang` crl ON (
                cr.`id_cart_rule` = crl.`id_cart_rule`
                AND crl.id_lang = ' . (int) $context->language->id . '
            )
            WHERE cr.is_fee & ' . (int) self::IS_FEE . '
                ' . ($visibility ? 'AND cr.display_visible &' . (int) $visibility : '') . '
                ' . ($filter ? 'AND cr.is_fee &' . (int) $filter : '') . '
                AND cr.active = 1'
        );
        
        if (empty($items)) {
            return;
        }
        
        $cartNbProducts = Closure::bind(function() { self::$_nbProducts[$this->id] = 1; }, $context->cart, 'Cart');
        
        $cartNbProducts();

        foreach ($items as $item) {
            $cart_rule = new CartRule($item['id_cart_rule']);
            
            if ($cart_rule->product_restriction) {
                $product_rule_groups = $cart_rule->getProductRuleGroups();
                
                foreach ($product_rule_groups as $id_product_rule_group => $product_rule_group) {
                    $eligible = false;

                    $product_rules = $cart_rule->getProductRules($id_product_rule_group);

                    foreach ($product_rules as $product_rule) {
                        switch ($product_rule['type']) {
                            case 'attributes':
                                // Noop
                                break;
                            case 'products':
                                if (in_array($product->id, $product_rule['values'])) {
                                    $eligible = true;
                                }
                                
                                break;
                            case 'categories':
                                $res = array_intersect($product->getCategories(), $product_rule['values']);
                                if (!empty($res)) {
                                    $eligible = true;
                                }
                                
                                break;
                            case 'manufacturers':
                                if (in_array($product->id_manufacturer, $product_rule['values'])) {
                                    $eligible = true;
                                }
                                
                                break;
                            case 'suppliers':
                                if (in_array($product->id_supplier, $product_rule['values'])) {
                                    $eligible = true;
                                }
                                
                                break;
                        }
                    }
                    
                    if (!$eligible) {
                        continue 2;
                    }
                }
                
                $cart_rule->product_restriction = 0;
            }
      
            if (!$cart_rule->checkValidity($context, false, false, false)) {
                continue;
            }
            
            $fees[] = array(
                'obj' => $cart_rule,
                'name' => $item['name'],
                'value_real' => $cart_rule->getContextualValue(
                    true,
                    $context,
                    CartRule::FILTER_ACTION_ALL_NOCAP
                ),
                'value_tax_exc' => $cart_rule->getContextualValue(
                    false,
                    $context,
                    CartRule::FILTER_ACTION_ALL_NOCAP
                ),
            );
        }
        
        return $fees;
    }
    
    public function getCartRulesByCart($cart)
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT *
                FROM `' . _DB_PREFIX_ . 'cart_cart_rule` cd
                LEFT JOIN `' . _DB_PREFIX_ . 'cart_rule` cr ON cd.`id_cart_rule` = cr.`id_cart_rule`
                LEFT JOIN `' . _DB_PREFIX_ . 'cart_rule_lang` crl ON (
                    cd.`id_cart_rule` = crl.`id_cart_rule`
                    AND crl.id_lang = ' . (int) $cart->id_lang . '
                    )
                WHERE cr.is_fee = 0 AND `id_cart` = ' . (int) $cart->id
        );
        
        $virtual_context = Context::getContext()->cloneContext();
        $virtual_context->cart = $cart;
        
        foreach ($result as &$row) {
            $row['obj'] = new CartRule($row['id_cart_rule'], (int) $cart->id_lang);
            $row['value_real'] = $row['obj']->getContextualValue(true, $virtual_context);
            $row['value_tax_exc'] = $row['obj']->getContextualValue(false, $virtual_context);
            $row['id_discount'] = $row['id_cart_rule'];
            $row['description'] = $row['name'];
        }
        
        return $result;
    }
    
    public function applyFeeOnInvoice($order_invoice, $value_tax_incl, $value_tax_excl)
    {
        $order_invoice->total_paid_tax_incl += $value_tax_incl;
        $order_invoice->total_paid_tax_excl += $value_tax_excl;
        $order_invoice->update();
    }
    
    public function getDimensionRuleGroups($object)
    {
        if (!Validate::isLoadedObject($object) || $object->dimension_restriction == 0) {
            return array();
        }

        $dimensionRuleGroups = array();
        
        $result = Db::getInstance()->executeS(
            'SELECT * FROM '._DB_PREFIX_.'cart_rule_dimension_rule_group WHERE id_cart_rule = '.(int)$object->id
        );
        
        foreach ($result as $row) {
            if (!isset($dimensionRuleGroups[$row['id_dimension_rule_group']])) {
                $dimensionRuleGroups[$row['id_dimension_rule_group']] = array(
                    'id_dimension_rule_group' => $row['id_dimension_rule_group'],
                    'base' => $row['base']
                );
            }
            $dimensionRuleGroups[$row['id_dimension_rule_group']]['dimension_rules'] = $this->getDimensionRules(
                $object,
                $row['id_dimension_rule_group']
            );
        }
        return $dimensionRuleGroups;
    }

    public function getDimensionRules($object, $id_dimension_rule_group)
    {
        if (!Validate::isLoadedObject($object) || $object->dimension_restriction == 0) {
            return array();
        }

        $dimensionRules = array();
        $results = Db::getInstance()->executeS(
            'SELECT *
                FROM '._DB_PREFIX_.'cart_rule_dimension_rule pr
                WHERE pr.id_dimension_rule_group = ' . (int) $id_dimension_rule_group
        );
        
        foreach ($results as $row) {
            $dimensionRules[$row['id_dimension_rule']] = array(
                'type' => $row['type'],
                'operator' => $row['operator'],
                'value' => $row['value']
            );
        }
        
        return $dimensionRules;
    }
    
    public function getZipcodeRuleGroups($object)
    {
        if (!Validate::isLoadedObject($object) || $object->zipcode_restriction == 0) {
            return array();
        }

        $zipcodeRuleGroups = array();
        
        $result = Db::getInstance()->executeS(
            'SELECT * FROM '._DB_PREFIX_.'cart_rule_zipcode_rule_group WHERE id_cart_rule = '.(int)$object->id
        );
        
        foreach ($result as $row) {
            if (!isset($zipcodeRuleGroups[$row['id_zipcode_rule_group']])) {
                $zipcodeRuleGroups[$row['id_zipcode_rule_group']] = array(
                    'id_zipcode_rule_group' => $row['id_zipcode_rule_group']
                );
            }
            $zipcodeRuleGroups[$row['id_zipcode_rule_group']]['zipcode_rules'] = $this->getZipcodeRules(
                $object,
                $row['id_zipcode_rule_group']
            );
        }
        return $zipcodeRuleGroups;
    }

    public function getZipcodeRules($object, $id_zipcode_rule_group)
    {
        if (!Validate::isLoadedObject($object) || $object->zipcode_restriction == 0) {
            return array();
        }

        $zipcodeRules = array();
        $results = Db::getInstance()->executeS(
            'SELECT *
                FROM '._DB_PREFIX_.'cart_rule_zipcode_rule pr
                WHERE pr.id_zipcode_rule_group = ' . (int) $id_zipcode_rule_group
        );
        
        foreach ($results as $row) {
            $zipcodeRules[$row['id_zipcode_rule']] = array(
                'type' => $row['type'],
                'operator' => $row['operator'],
                'value' => $row['value']
            );
        }
        
        return $zipcodeRules;
    }
    
    public function getPackageRuleGroups($object)
    {
        if (!Validate::isLoadedObject($object) || $object->package_restriction == 0) {
            return array();
        }

        $packageRuleGroups = array();
        
        $result = Db::getInstance()->executeS(
            'SELECT * FROM '._DB_PREFIX_.'cart_rule_package_rule_group WHERE id_cart_rule = '.(int)$object->id
        );
        
        foreach ($result as $row) {
            if (!isset($packageRuleGroups[$row['id_package_rule_group']])) {
                $packageRuleGroups[$row['id_package_rule_group']] = array(
                    'id_package_rule_group' => $row['id_package_rule_group'],
                    'unit' => $row['unit'],
                    'unit_weight' => $row['unit_weight'],
                    'ratio' => $row['ratio']
                );
            }
            $packageRuleGroups[$row['id_package_rule_group']]['package_rules'] = $this->getPackageRules(
                $object,
                $row['id_package_rule_group']
            );
        }
        return $packageRuleGroups;
    }

    public function getPackageRules($object, $id_package_rule_group)
    {
        if (!Validate::isLoadedObject($object) || $object->package_restriction == 0) {
            return array();
        }

        $packageRules = array();
        $results = Db::getInstance()->executeS(
            'SELECT *
                FROM '._DB_PREFIX_.'cart_rule_package_rule pr
                WHERE pr.id_package_rule_group = '.(int)$id_package_rule_group.'
                ORDER BY pr.range_start ASC'
        );
        
        foreach ($results as $row) {
            $packageRules[$row['id_package_rule']] = array(
                'range_start' => $row['range_start'],
                'range_end' => $row['range_end'],
                'range_start' => $row['range_start'],
                'round' => $row['round'],
                'divider' => $row['divider'],
                'currency' => $row['currency'],
                'tax' => $row['tax'],
                'value' => $row['value']
            );
        }
        
        return $packageRules;
    }
    
    public function weightTo($value, $to_unit, $from_unit = null)
    {
        if (!$from_unit) {
            $from_unit = $this->weight_unit_default;
        }
        
        return ($value * self::$WEIGHT_UNITS[$from_unit]) / self::$WEIGHT_UNITS[$to_unit];
    }
    
    public function volumeTo($value, $to_unit, $from_unit = null)
    {
        if (!$from_unit) {
            $from_unit = $this->volume_unit_default;
        }
        
        return  ($value * self::$VOLUME_UNITS[$from_unit]) / self::$VOLUME_UNITS[$to_unit];
    }
    
    public function getActualCurrency()
    {
        if ($this->type_context == Shop::CONTEXT_SHOP) {
            Shop::setContext($this->type_context, $this->old_context->shop->id);
        } elseif ($this->type_context == Shop::CONTEXT_GROUP) {
            Shop::setContext($this->type_context, $this->old_context->shop->id_shop_group);
        }

        $currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));

        Shop::setContext(Shop::CONTEXT_ALL);

        return $currency;
    }
    
    /**
     * Returns tax rate.
     *
     * @param Address|null $address
     * @return float The total taxes rate applied to the product
     */
    public function getTaxesRate($tax_rules_group, Address $address = null)
    {
        if (!$address || !$address->id_country) {
            $address = Address::initialize();
        }

        $tax_manager = TaxManagerFactory::getManager($address, $tax_rules_group);
        $tax_calculator = $tax_manager->getTaxCalculator();

        return $tax_calculator->getTotalRate();
    }
}
