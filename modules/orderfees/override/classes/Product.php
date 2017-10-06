<?php
/**
 *  Order Fees
 *
 *  @author    motionSeed <ecommerce@motionseed.com>
 *  @copyright 2017 motionSeed. All rights reserved.
 *  @license   https://www.motionseed.com/en/license-module.html
 */

class Product extends ProductCore
{
    public static function priceCalculation(
        $id_shop,
        $id_product,
        $id_product_attribute,
        $id_country,
        $id_state,
        $zipcode,
        $id_currency,
        $id_group,
        $quantity,
        $use_tax,
        $decimals,
        $only_reduc,
        $use_reduc,
        $with_ecotax,
        &$specific_price,
        $use_group_reduction,
        $id_customer = 0,
        $use_customer_price = true,
        $id_cart = 0,
        $real_quantity = 0
    ) {
        $total = 0;
        $return = false;
        
        Hook::exec('actionProductPriceCalculation', array(
            'id_shop' => &$id_shop,
            'id_product' => &$id_product,
            'id_product_attribute' => &$id_product_attribute,
            'id_country' => &$id_country,
            'id_state' => &$id_state,
            'zipcode' => &$zipcode,
            'id_currency' => &$id_currency,
            'id_group' => &$id_group,
            'quantity' => &$quantity,
            'use_tax' => &$use_tax,
            'decimals' => &$decimals,
            'zipcode' => &$zipcode,
            'total' => &$total,
            'return' => &$return
        ));
        
        if ($return) {
            return (float) Tools::ps_round((float) $total, 2);
        }
        
        return parent::priceCalculation(
            $id_shop,
            $id_product,
            $id_product_attribute,
            $id_country,
            $id_state,
            $zipcode,
            $id_currency,
            $id_group,
            $quantity,
            $use_tax,
            $decimals,
            $only_reduc,
            $use_reduc,
            $with_ecotax,
            $specific_price,
            $use_group_reduction,
            $id_customer,
            $use_customer_price,
            $id_cart,
            $real_quantity
        ) + (float) Tools::ps_round((float) $total, 2);
    }
}
