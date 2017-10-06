<?php
/**
 *  Order Fees
 *
 *  @author    motionSeed <ecommerce@motionseed.com>
 *  @copyright 2017 motionSeed. All rights reserved.
 *  @license   https://www.motionseed.com/en/license-module.html
 */

function upgrade_module_1_7_3($module)
{
    $module->upgradeVersion('1.7.3');
    
    $result = true;
    
    // Upgrade database
    $result &= $module->upgradeDB();

    return $result;
}
