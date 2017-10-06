<?php
/**
 *  Order Fees
 *
 *  @author    motionSeed <ecommerce@motionseed.com>
 *  @copyright 2017 motionSeed. All rights reserved.
 *  @license   https://www.motionseed.com/en/license-module.html
 */

function upgrade_module_1_8_5($module)
{
    $result = true;
    
    // Add hook : displayCartRuleProductFees
    $result &= $module->registerHook('displayCartRuleProductFees');
    
    // Add hook : actionProductPriceCalculation
    $result &= $module->registerHook('actionProductPriceCalculation');
    
    // Add Product.php override
    $result &= $module->addOverride('Product');

    return $result;
}
