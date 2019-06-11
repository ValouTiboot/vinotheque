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

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        Hook::exec('actionCartRuleCtor', array(
            'object' => &$this
        ));
        
        parent::__construct($id, $id_lang, $id_shop);
    }

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

            $reduction_amount = (15*1.2)+$add_price;
            if ($use_tax && $this->reduction_amount != $reduction_amount)
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
}
