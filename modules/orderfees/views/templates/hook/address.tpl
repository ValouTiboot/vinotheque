{*
* Order Fees
*
* @author    motionSeed <ecommerce@motionseed.com>
* @copyright 2017 motionSeed. All rights reserved.
* @license   https://www.motionseed.com/en/license-module.html
*}
{if isset($fees) && count($fees)}
<div id="orderfees_list">
    {foreach $fees as $fee}
        <p class="checkbox">
            {if ($fee["obj"]->is_fee & $module->getConstant('IS_OPTION')) && ($fee["obj"]->display_selectable & $module->getConstant('CONTEXT_ADDRESS'))}
                <input type="checkbox" id="fees_{$fee.id_cart_rule|intval}" name="fees[]" value="{$fee.id_cart_rule|intval}" {if $fee["is_checked"]}checked="checked"{/if} />&nbsp;
            {/if}

            <label for="fees_{$fee.id_cart_rule|intval}">
                <span class="dark cart_discount" id="cart_fee_{$fee.id_cart_rule|intval}">{$fee.name|escape:'html':'UTF-8'}</span>&nbsp;&nbsp;
                <span>
                    {if !$priceDisplay}
                        {displayPrice price=$fee.value_real*-1}
                    {else}
                        {displayPrice price=$fee.value_tax_exc*-1}
                    {/if}
                </span>
            </label>
        </p>
    {/foreach}
</div>
<hr style="" />
{/if}
