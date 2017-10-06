<?php
/**
 *  Order Fees
 *
 *  @author    motionSeed <ecommerce@motionseed.com>
 *  @copyright 2017 motionSeed. All rights reserved.
 *  @license   https://www.motionseed.com/en/license-module.html
 */

class Cart extends CartCore
{
    public $current_type = null;
    
    public function getPackageShippingCost(
        $id_carrier = null,
        $use_tax = true,
        Country $default_country = null,
        $product_list = null,
        $id_zone = null
    ) {
        $total = 0;
        $return = false;
        
        Hook::exec('actionCartGetPackageShippingCost', array(
            'object' => &$this,
            'id_carrier' => &$id_carrier,
            'use_tax' => &$use_tax,
            'default_country' => &$default_country,
            'product_list' => &$product_list,
            'id_zone' => &$id_zone,
            'total' => &$total,
            'return' => &$return
        ));
        
        if ($return) {
            return (float) Tools::ps_round((float) $total, 2);
        }
        
        return parent::getPackageShippingCost(
            $id_carrier,
            $use_tax,
            $default_country,
            $product_list,
            $id_zone
        ) + (float) Tools::ps_round((float) $total, 2);
    }

    public function getCartRulesSort(&$a, &$b)
    {
        return strcmp($b['is_fee'], $a['is_fee']);
    }

    public function getCartRules($filter = CartRule::FILTER_ACTION_ALL)
    {
        $result = parent::getCartRules($filter);
        usort($result, array($this, 'getCartRulesSort'));
        return $result;
    }

    public function getOrderTotal(
        $with_taxes = true,
        $type = Cart::BOTH,
        $products = null,
        $id_carrier = null,
        $use_cache = true
    ) {
        if (in_array($type, array(self::BOTH, self::ONLY_DISCOUNTS))) {
            $this->fees_total_discounts = array();
            $this->current_type = $type;
        }
        
        $total = parent::getOrderTotal($with_taxes, $type, $products, $id_carrier, $use_cache);
        
        if ($type == self::ONLY_DISCOUNTS) {
            $this->current_type = null;
            
            if (!empty($this->fees_total_discounts)) {
                return array_sum($this->fees_total_discounts);
            }
        }
        
        return $total;
    }

    public function addCartRule($id_cart_rule)
    {
        $results = Hook::exec('actionCartRuleAdd', array(
            'object' => &$this,
            'id_cart_rule' => &$id_cart_rule
        ), null, true);
        
        if (is_array($results)) {
            foreach ($results as $result) {
                if ($result !== null) {
                    return $result;
                }
            }
        }
        
        return parent::addCartRule($id_cart_rule);
    }

    public function removeCartRule($id_cart_rule)
    {
        $results = Hook::exec('actionCartRuleRemove', array(
            'object' => &$this,
            'id_cart_rule' => &$id_cart_rule
        ), null, true);
        
        if (is_array($results)) {
            foreach ($results as $result) {
                if ($result !== null) {
                    return $result;
                }
            }
        }
        
        return parent::removeCartRule($id_cart_rule);
    }
    
    public function getOrderedCartRulesIds($filter = CartRule::FILTER_ACTION_ALL)
    {
        $cache_key = 'Cart::getOrderedCartRulesIds_' . $this->id . '-' . $filter . '-ids';
        if (!Cache::isStored($cache_key)) {
            $result = Db::getInstance()->executeS(
                'SELECT cr.`id_cart_rule`
				FROM `' . _DB_PREFIX_ . 'cart_cart_rule` cd
				LEFT JOIN `' . _DB_PREFIX_ . 'cart_rule` cr ON cd.`id_cart_rule` = cr.`id_cart_rule`
				LEFT JOIN `' . _DB_PREFIX_ . 'cart_rule_lang` crl ON (
					cd.`id_cart_rule` = crl.`id_cart_rule`
					AND crl.id_lang = ' . (int) $this->id_lang . '
				)
				WHERE `id_cart` = ' . (int) $this->id . '
                    AND is_fee = 0
				' . ($filter == CartRule::FILTER_ACTION_SHIPPING ? 'AND free_shipping = 1' : '') . '
				' . ($filter == CartRule::FILTER_ACTION_GIFT ? 'AND gift_product != 0' : '') . '
				' . ($filter == CartRule::FILTER_ACTION_REDUCTION ? 'AND (reduction_percent != 0 OR reduction_amount != 0)' : '')
                . ' ORDER BY cr.priority ASC'
            );
            Cache::store($cache_key, $result);
        } else {
            $result = Cache::retrieve($cache_key);
        }

        return $result;
    }
}
