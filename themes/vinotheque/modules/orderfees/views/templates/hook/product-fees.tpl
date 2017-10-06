{*
* Options
*
* @author    motionSeed <ecommerce@motionseed.com>
* @copyright 2017 motionSeed. All rights reserved.
* @license   https://www.motionseed.com/en/license-module.html
*}
{if isset($fees) && count($fees) > 0}
<div id="orderfees_fees_list" class="product_attributes clearfix">
    {foreach $fees as $fee}            
            <div>
                <span class="dark cart_discount">{$fee.name|escape:'html':'UTF-8'}</span>&nbsp;&nbsp;
                <!--<span>
                    {if !$priceDisplay}
                        {displayPrice price=$fee.value_real}
                    {else}
                        {displayPrice price=$fee.value_tax_exc}
                    {/if}
                </span>-->
            </div>
    {/foreach}
</div>
{/if}
