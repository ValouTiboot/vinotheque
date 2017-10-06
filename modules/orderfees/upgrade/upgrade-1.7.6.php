<?php
/**
 *  Order Fees
 *
 *  @author    motionSeed <ecommerce@motionseed.com>
 *  @copyright 2017 motionSeed. All rights reserved.
 *  @license   https://www.motionseed.com/en/license-module.html
 */

function upgrade_module_1_7_6($module)
{
    $module->upgradeVersion('1.7.6');
    
    $result = true;
    
    // Add displayCartRuleOrderPaymentOption hook
    $result &= $module->registerHook('displayCartRuleOrderPaymentOption');
    
    // Add actionObjectOrderCartRuleAddAfter hook
    $result &= $module->registerHook('actionObjectOrderCartRuleAddAfter');
    
    // Add displayCartRuleInvoiceProductTaxTab hook
    $result &= $module->registerHook('displayCartRuleInvoiceProductTaxTab');
    
    // Add displayCartRuleAdminOrders hook
    $result &= $module->registerHook('displayCartRuleAdminOrders');
    
    // Add hook on invoice.tax-tab.tpl
    $tpl_path = $module::getPdfTemplatePath('invoice.tax-tab');
    
    if (Tools::file_exists_cache($tpl_path)) {
        $content = $module->templateReplace(
            '{if !$has_line}',
            '{hook h="displayCartRuleInvoiceTaxTab" order=$order}
                                {if !$has_line}',
            Tools::file_get_contents($tpl_path)
        );
        
        file_put_contents($tpl_path, $content);
    }

    // Upgrade database
    $result &= $module->upgradeDB();
    
    // Set display for fees on invoice tax tab
    $result &= Configuration::updateValue('MS_ORDERFEES_DISPLAY_INVOICE_TAX_TAB', '0');
    
    // Set reduction aware
    $result &= Configuration::updateValue('MS_ORDERFEES_REDUCTION_AWARE', '0');
    
    // Replace Cart.php override
    $result &= $module->upgradeOverride('Cart');

    return $result;
}
