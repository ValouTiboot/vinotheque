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
                <span class="custom-checkbox">
                    <input type="checkbox" id="fees_address_{$fee.id_cart_rule|intval}" name="fees[]" value="{$fee.id_cart_rule|intval}" {if $fee["is_checked"]}checked="checked"{/if} />
                    <span><i class="material-icons checkbox-checked">&#xE5CA;</i></span>
                    <label for="fees_address_{$fee.id_cart_rule|intval}">
                        <span class="name">{$fee.name|escape:'html':'UTF-8'}</span>
                        
                        <span class="price">
                            {if $tax->includeTaxes()}
                                {$price->format($fee["obj"]->unit_value_real*-1)|escape:'html':'UTF-8'}
                            {else}
                                {$price->format($fee["obj"]->unit_value_tax_exc*-1)|escape:'html':'UTF-8'}
                            {/if}
                        </span>
                    </label>
                </span>
            {else}
                <label>
                    <span class="name">{$fee.name|escape:'html':'UTF-8'}</span>&nbsp;&nbsp;
                    <span class="price">
                        {if $tax->includeTaxes()}
                            {$price->format($fee["obj"]->unit_value_real*-1)|escape:'html':'UTF-8'}
                        {else}
                            {$price->format($fee["obj"]->unit_value_tax_exc*-1)|escape:'html':'UTF-8'}
                        {/if}
                    </span>
                </label>
            {/if}
        </p>
    {/foreach}
</div>
<hr style="" />
{/if}
