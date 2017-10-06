<?php
/**
 *  Order Fees
 *
 *  @author    motionSeed <ecommerce@motionseed.com>
 *  @copyright 2017 motionSeed. All rights reserved.
 *  @license   https://www.motionseed.com/en/license-module.html
 */

function upgrade_module_1_7_2($module)
{
    $module->upgradeVersion('1.7.2');
    
    $result = true;

    // Replace PaymentModule.php override
    $result &= $module->upgradeOverride('PaymentModule');
    
    // Set payment templates list
    $result &= Configuration::updateValue(
        'MS_ORDERFEES_PAYMENT_TPLS',
        'payment.tpl,payment_std.tpl,express_checkout_payment.tpl'
    );
    
    // Set display method for payment fees / reductions
    $result &= Configuration::updateValue('MS_ORDERFEES_PAYMENT_DISPLAY_METHOD', 'amount');
    
    // Add actionValidateOrder hook
    $result &= $module->registerHook('actionValidateOrder');
    
    // Add actionGetIDZoneByAddressID hook
    $result &= $module->registerHook('actionGetIDZoneByAddressID');

    // Add actionObjectCartUpdateBefore hook
    $result &= $module->registerHook('actionObjectCartUpdateBefore');
    
    // Upgrade database
    $result &= $module->upgradeDB();

    return $result;
}
