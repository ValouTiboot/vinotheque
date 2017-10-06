{*
* Options
*
* @author    motionSeed <ecommerce@motionseed.com>
* @copyright 2017 motionSeed. All rights reserved.
* @license   https://www.motionseed.com/en/license-module.html
*}
{if isset($fees) && count($fees) > 0}
<div id="orderfees_list" class="product_attributes clearfix">
    {foreach $fees as $fee}
        <p class="checkbox">
            {if ($fee["obj"]->is_fee & $module->getConstant('IS_OPTION')) && ($fee["obj"]->display_selectable & $module->getConstant('CONTEXT_PRODUCT'))}
            <input type="checkbox" id="fees_product_{$fee.id_cart_rule|intval}" name="fees[]" value="{$fee.id_cart_rule|intval}" {if !($fee["obj"]->is_fee & $module->getConstant('IS_CHECKED'))}checked="checked"{/if} />&nbsp;
            {/if}
            
            <label for="fees_product_{$fee.id_cart_rule|intval}">
                <span class="dark cart_discount" id="cart_fee_{$fee.id_cart_rule|intval}">{$fee.name|escape:'html':'UTF-8'}</span>&nbsp;&nbsp;
                <span>{displayPrice price=$fee.value_tax_exc*-1}
                </span>
            </label>
        </p>
    {/foreach}
</div>
{/if}
