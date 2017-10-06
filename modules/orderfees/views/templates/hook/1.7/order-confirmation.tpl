{*
* Order Fees
*
* @author    motionSeed <ecommerce@motionseed.com>
* @copyright 2017 motionSeed. All rights reserved.
* @license   https://www.motionseed.com/en/license-module.html
*}
{if count($fees)}
    <tbody>
        <tr>
            <td colspan="4">
                {foreach from=$fees item=fee name=feeLoop}
                <div class="order-line fee-line row {if $smarty.foreach.feeLoop.last}last_item{elseif $smarty.foreach.feeLoop.first}first_item{else}item{/if}" id="cart_discount_{$fee.id_cart_rule|intval}">
                    <div class="col-sm-6 col-xs-3 text-xs-left">
                        {$fee.name|escape:'html':'UTF-8'}
                    </div>
                    <div class="col-sm-6 col-xs-12 qty">
                        <div class="row">
                            <div class="col-xs-5 text-sm-right text-xs-left">
                                {if $tax->includeTaxes()}
                                    {$price->format($fee.unit_value_real)|escape:'html':'UTF-8'}
                                {else}
                                    {$price->format($fee.unit_value_tax_exc)|escape:'html':'UTF-8'}
                                {/if}
                            </div>
                            <div class="col-xs-2 text-xs-left">{$fee.quantity|intval}</div>
                            <div class="col-xs-5 text-xs-right bold">
                                {if $tax->includeTaxes()}
                                    {$price->format($fee.value)|escape:'html':'UTF-8'}
                                {else}
                                    {$price->format($fee.value_tax_excl)|escape:'html':'UTF-8'}
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>
                {/foreach}
            </td>
        </tr>
    </tbody>
{/if}