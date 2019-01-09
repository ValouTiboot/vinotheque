<?php
/**
 *  Order Fees
 *
 *  @author    motionSeed <ecommerce@motionseed.com>
 *  @copyright 2017 motionSeed. All rights reserved.
 *  @license   https://www.motionseed.com/en/license-module.html
 */
class CartRule extends CartRuleCore
{
    /*
    * module: orderfees
    * date: 2018-11-19 10:30:54
    * version: 1.8.9
    */
    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        Hook::exec('actionCartRuleCtor', array(
            'object' => &$this
        ));
        
        parent::__construct($id, $id_lang, $id_shop);
    }
    /*
    * module: orderfees
    * date: 2018-11-19 10:30:54
    * version: 1.8.9
    */
    public function getContextualValue(
        $use_tax,
        Context $context = null,
        $filter = null,
        $package = null,
        $use_cache = true
    ) {
        $current_filter = $filter;

        $products = Context::getContext()->cart->getProducts();
        $add_price = 0;
        $add_price_tax_exc = 0;
        if (preg_match('@primeur@i', $this->name))
        {
            foreach ($products as $product)
            {
                if ($product['wine'])
                {
                    if ($use_tax)
                    {
                        $add_price += (($product['total']*1.5)/100)*1.2;
                        $add_price_tax_exc += ($product['total']*1.5)/100;
                    }
                }
            }

            if ($use_tax)
            {
                $this->unit_value_tax_exc -= 15+$add_price_tax_exc;
                $this->reduction_amount += $add_price;
                // $this->unit_value_tax_inc -= $this->reduction_amount;
                $this->unit_value_real -= $this->reduction_amount;
            }
        }

        $results = Hook::exec('actionCartRuleGetContextualValueBefore', array(
            'object' => &$this,
            'context' => &$context,
            'use_tax' => &$use_tax,
            'filter' => &$filter,
            'current_filter' => $current_filter,
            'package' => &$package,
            'use_cache' => &$use_cache
        ), null, true);
        
        if (is_array($results)) {
            foreach ($results as $result) {
                if ($result !== null) {
                    return $result;
                }
            }
        }
        
        $contextual_value = parent::getContextualValue($use_tax, $context, $filter, $package, $use_cache);

        $results = Hook::exec('actionCartRuleGetContextualValueAfter', array(
            'object' => &$this,
            'context' => &$context,
            'use_tax' => &$use_tax,
            'filter' => &$filter,
            'current_filter' => $current_filter,
            'package' => &$package,
            'use_cache' => &$use_cache,
            'contextual_value' => &$contextual_value
        ), null, true);
        
        if (is_array($results)) {
            foreach ($results as $result) {
                if ($result !== null) {
                    return $result;
                }
            }
        }
        
        return $contextual_value;
    }
    /*
    * module: orderfees
    * date: 2018-11-19 10:30:54
    * version: 1.8.9
    */
    public function checkValidity(
        Context $context,
        $alreadyInCart = false,
        $display_error = true,
        $check_carrier = true
    ) {
        $results = Hook::exec('actionCartRuleCheckValidity', array(
            'object' => &$this,
            'context' => &$context,
            'alreadyInCart' => &$alreadyInCart,
            'display_error' => &$display_error,
            'check_carrier' => &$check_carrier
        ), null, true);
        
        if (is_array($results)) {
            foreach ($results as $result) {
                if ($result !== null) {
                    return (!$display_error) ? false : Tools::displayError($result['message']);
                }
            }
        }
        return parent::checkValidity($context, $alreadyInCart, $display_error, $check_carrier);
    }
    /*
    * module: orderfees
    * date: 2018-11-19 10:30:54
    * version: 1.8.9
    */
    public static function getCustomerCartRules(
        $id_lang,
        $id_customer,
        $active = false,
        $includeGeneric = true,
        $inStock = false,
        Cart $cart = null,
        $free_shipping_only = false,
        $highlight_only = false
    ) {
        $result = parent::getCustomerCartRules(
            $id_lang,
            $id_customer,
            $active,
            $includeGeneric,
            $inStock,
            $cart,
            $free_shipping_only,
            $highlight_only
        );
        
        foreach ($result as $key => $cart_rule) {
            if ($cart_rule['is_fee'] > 0) {
                unset($result[$key]);
            }
        }
        
        return $result;
    }
    
    /*
    * module: orderfees
    * date: 2018-11-19 10:30:54
    * version: 1.8.9
    */
    public function getAssociatedRestrictions(
        $type,
        $active_only,
        $i18n,
        $offset = null,
        $limit = null,
        $search_cart_rule_name = ''
    ) {
        $result = Hook::exec('actionAssociatedRestrictions' . Tools::ucfirst($type), array(
            'object' => &$this,
            'type' => &$type,
            'active_only' => &$active_only,
            'i18n' => &$i18n,
            'offset' => &$offset,
            'limit' => &$limit,
            'search_cart_rule_name' => &$search_cart_rule_name
        ), null, true);
        
        if (is_array($result) && $associated_restrictions = reset($result)) {
            return $associated_restrictions;
        }
        
        return parent::getAssociatedRestrictions(
            $type,
            $active_only,
            $i18n,
            $offset,
            $limit,
            $search_cart_rule_name
        );
    }

    protected function checkProductRestrictions(Context $context, $return_products = false, $display_error = true, $already_in_cart = false)
    {
        $selected_products = array();

        // Check if the products chosen by the customer are usable with the cart rule
        if ($this->product_restriction) {
            $product_rule_groups = $this->getProductRuleGroups();
            foreach ($product_rule_groups as $id_product_rule_group => $product_rule_group) {
                $eligible_products_list = array();
                if (isset($context->cart) && is_object($context->cart) && is_array($products = $context->cart->getProducts())) {
                    foreach ($products as $product) {
                        $eligible_products_list[] = (int)$product['id_product'].'-'.(int)$product['id_product_attribute'];
                    }
                }
                if (!count($eligible_products_list)) {
                    return (!$display_error) ? false : $this->trans('You cannot use this voucher in an empty cart', array(), 'Shop.Notifications.Error');
                }

                $product_rules = $this->getProductRules($id_product_rule_group);
                foreach ($product_rules as $product_rule) {
                    switch ($product_rule['type']) {
                        case 'attributes':
                            $cart_attributes = Db::getInstance()->executeS('
                            SELECT cp.quantity, cp.`id_product`, pac.`id_attribute`, cp.`id_product_attribute`
                            FROM `'._DB_PREFIX_.'cart_product` cp
                            LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON cp.id_product_attribute = pac.id_product_attribute
                            WHERE cp.`id_cart` = '.(int)$context->cart->id.'
                            AND cp.`id_product` IN ('.implode(',', array_map('intval', $eligible_products_list)).')
                            AND cp.id_product_attribute > 0');
                            $count_matching_products = 0;
                            $matching_products_list = array();
                            foreach ($cart_attributes as $cart_attribute) {
                                if (in_array($cart_attribute['id_attribute'], $product_rule['values'])) {
                                    $count_matching_products += $cart_attribute['quantity'];
                                    if ($already_in_cart && $this->gift_product == $cart_attribute['id_product']
                                        && $this->gift_product_attribute == $cart_attribute['id_product_attribute']) {
                                        --$count_matching_products;
                                    }
                                    $matching_products_list[] = $cart_attribute['id_product'].'-'.$cart_attribute['id_product_attribute'];
                                }
                            }
                            if ($count_matching_products < $product_rule_group['quantity']) {
                                return (!$display_error) ? false : $this->trans('You cannot use this voucher with these products', array(), 'Shop.Notifications.Error');
                            }
                            $eligible_products_list = $this->filterProducts($eligible_products_list, $matching_products_list, $product_rule['type']);
                            break;
                        case 'products':
                            $cart_products = Db::getInstance()->executeS('
                            SELECT cp.quantity, cp.`id_product`
                            FROM `'._DB_PREFIX_.'cart_product` cp
                            WHERE cp.`id_cart` = '.(int)$context->cart->id.'
                            AND cp.`id_product` IN ('.implode(',', array_map('intval', $eligible_products_list)).')');
                            $count_matching_products = 0;
                            $matching_products_list = array();
                            foreach ($cart_products as $cart_product) {
                                if (in_array($cart_product['id_product'], $product_rule['values'])) {
                                    $count_matching_products += $cart_product['quantity'];
                                    if ($already_in_cart && $this->gift_product == $cart_product['id_product']) {
                                        --$count_matching_products;
                                    }
                                    $matching_products_list[] = $cart_product['id_product'].'-0';
                                }
                            }
                            if ($count_matching_products < $product_rule_group['quantity']) {
                                return (!$display_error) ? false : $this->trans('You cannot use this voucher with these products', array(), 'Shop.Notifications.Error');
                            }
                            $eligible_products_list = $this->filterProducts($eligible_products_list, $matching_products_list, $product_rule['type']);
                            break;
                        case 'categories':
                            $cart_categories = Db::getInstance()->executeS('
                            SELECT cp.quantity, cp.`id_product`, cp.`id_product_attribute`, catp.`id_category`
                            FROM `'._DB_PREFIX_.'cart_product` cp
                            LEFT JOIN `'._DB_PREFIX_.'category_product` catp ON cp.id_product = catp.id_product
                            WHERE cp.`id_cart` = '.(int)$context->cart->id.'
                            AND cp.`id_product` IN ('.implode(',', array_map('intval', $eligible_products_list)).')
                            AND cp.`id_product` <> '.(int)$this->gift_product);
                            $count_matching_products = 0;
                            $matching_products_list = array();
                            
                            foreach ($cart_categories as $cart_category) {
                                if (in_array($cart_category['id_category'], $product_rule['values'])
                                    /**
                                     * We also check that the product is not already in the matching product list,
                                     * because there are doubles in the query results (when the product is in multiple categories)
                                     */
                                    && !in_array($cart_category['id_product'].'-'.$cart_category['id_product_attribute'], $matching_products_list)) {
                                    $count_matching_products += $cart_category['quantity'];
                                    $matching_products_list[] = $cart_category['id_product'].'-'.$cart_category['id_product_attribute'];
                                }
                            }
                            if ($count_matching_products < $product_rule_group['quantity']) {
                                return (!$display_error) ? false : $this->trans('You cannot use this voucher with these products', array(), 'Shop.Notifications.Error');
                            }
                            // Attribute id is not important for this filter in the global list, so the ids are replaced by 0
                            foreach ($matching_products_list as &$matching_product) {
                                $matching_product = preg_replace('/^([0-9]+)-[0-9]+$/', '$1-0', $matching_product);
                            }
                            $eligible_products_list = $this->filterProducts($eligible_products_list, $matching_products_list, $product_rule['type']);
                            break;
                        case 'manufacturers':
                            $cart_manufacturers = Db::getInstance()->executeS('
                            SELECT cp.quantity, cp.`id_product`, p.`id_manufacturer`
                            FROM `'._DB_PREFIX_.'cart_product` cp
                            LEFT JOIN `'._DB_PREFIX_.'product` p ON cp.id_product = p.id_product
                            WHERE cp.`id_cart` = '.(int)$context->cart->id.'
                            AND cp.`id_product` IN ('.implode(',', array_map('intval', $eligible_products_list)).')');
                            $count_matching_products = 0;
                            $matching_products_list = array();
                            foreach ($cart_manufacturers as $cart_manufacturer) {
                                if (in_array($cart_manufacturer['id_manufacturer'], $product_rule['values'])) {
                                    $count_matching_products += $cart_manufacturer['quantity'];
                                    $matching_products_list[] = $cart_manufacturer['id_product'].'-0';
                                }
                            }
                            if ($count_matching_products < $product_rule_group['quantity']) {
                                return (!$display_error) ? false : $this->trans('You cannot use this voucher with these products', array(), 'Shop.Notifications.Error');
                            }
                            $eligible_products_list = $this->filterProducts($eligible_products_list, $matching_products_list, $product_rule['type']);
                            break;
                        case 'suppliers':
                            $cart_suppliers = Db::getInstance()->executeS('
                            SELECT cp.quantity, cp.`id_product`, p.`id_supplier`
                            FROM `'._DB_PREFIX_.'cart_product` cp
                            LEFT JOIN `'._DB_PREFIX_.'product` p ON cp.id_product = p.id_product
                            WHERE cp.`id_cart` = '.(int)$context->cart->id.'
                            AND cp.`id_product` IN ('.implode(',', array_map('intval', $eligible_products_list)).')');
                            $count_matching_products = 0;
                            $matching_products_list = array();
                            foreach ($cart_suppliers as $cart_supplier) {
                                if (in_array($cart_supplier['id_supplier'], $product_rule['values'])) {
                                    $count_matching_products += $cart_supplier['quantity'];
                                    $matching_products_list[] = $cart_supplier['id_product'].'-0';
                                }
                            }
                            if ($count_matching_products < $product_rule_group['quantity']) {
                                return (!$display_error) ? false : $this->trans('You cannot use this voucher with these products', array(), 'Shop.Notifications.Error');
                            }
                            $eligible_products_list = $this->filterProducts($eligible_products_list, $matching_products_list, $product_rule['type']);
                            break;
                    }

                    if (!count($eligible_products_list)) {
                        return (!$display_error) ? false : $this->trans('You cannot use this voucher with these products', array(), 'Shop.Notifications.Error');
                    }
                }
                $selected_products = array_merge($selected_products, $eligible_products_list);
            }
        }

        if ($return_products) {
            return $selected_products;
        }
        return (!$display_error) ? true : false;
    }

    protected function filterProducts($products, $eligibleProducts, $ruleType)
    {
        //If the two same array, no verification todo.
        if ($products === $eligibleProducts) {
            return $products;
        }
        $return = array();
        // Attribute id is not important for this filter in the global list
        // so the ids are replaced by 0
        if (in_array($ruleType, array('products', 'categories', 'manufacturers', 'suppliers'))) {
            $productsList = explode(':', preg_replace("#\-[0-9]+#", "-0", implode(':', $products)));
        } else {
            $productsList = $products;
        }
        foreach ($productsList as $k => $product) {
            if (in_array($product, $eligibleProducts)) {
                $return[] = $products[$k];
            }
        }
        return $return;
    }
}
