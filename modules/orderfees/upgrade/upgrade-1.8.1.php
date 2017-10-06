<?php
/**
 *  Order Fees
 *
 *  @author    motionSeed <ecommerce@motionseed.com>
 *  @copyright 2017 motionSeed. All rights reserved.
 *  @license   https://www.motionseed.com/en/license-module.html
 */

function upgrade_module_1_8_1($module)
{
    $module->upgradeVersion('1.8.1');
    
    $result = true;

    // Upgrade database
    $result &= $module->upgradeDB();
    
    // Update template : blockcart-json.tpl
    if (Module::getInstanceByName('blockcart')) {
        $tpl_path = Module::getInstanceByName('blockcart')->getTemplatePath('blockcart-json.tpl');
        
        if (Tools::file_exists_cache($tpl_path)) {
            $content = $module->templateReplace(
                '{$discount.value_real|json_encode}{/if}',
                '{$discount.value_real|json_encode}{/if},"is_fee":{$discount.is_fee|intval}',
                Tools::file_get_contents($tpl_path)
            );

            file_put_contents($tpl_path, $content);
        }
    }
    
    // Update template : order-address.tpl
    $tpl_path = _PS_THEME_DIR_ . 'order-address.tpl';
    
    if (Tools::file_exists_cache($tpl_path)) {
        $content = $module->templateReplace(
            '<div class="addresses',
            '{hook h="displayCartRuleAddress"}<div class="addresses',
            Tools::file_get_contents($tpl_path)
        );

        file_put_contents($tpl_path, $content);
    }
    
    // Add hook : displayBeforeCarrier
    $module->registerHook('displayBeforeCarrier');

    return $result;
}
