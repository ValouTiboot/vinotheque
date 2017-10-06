{*
* Order Fees
*
* @author    motionSeed <ecommerce@motionseed.com>
* @copyright 2017 motionSeed. All rights reserved.
* @license   https://www.motionseed.com/en/license-module.html
*}
{if count($fees)}
    <tbody>
        {foreach from=$fees item=fee name=feeLoop}
        <tr class="{if $smarty.foreach.feeLoop.last}last_item{elseif $smarty.foreach.feeLoop.first}first_item{else}item{/if}" id="cart_discount_{$fee.id_cart_rule|intval}">
            <td><strong>{$fee.name|escape:'html':'UTF-8'}</strong></td>
            <td>{$fee.quantity|intval}</td>
            <td class="text-xs-right">
                {if $tax->includeTaxes()}
                    {$price->format($fee.unit_value_real)|escape:'html':'UTF-8'}
                {else}
                    {$price->format($fee.unit_value_tax_exc)|escape:'html':'UTF-8'}
                {/if}
            </td>
            <td class="text-xs-right">
                {if $tax->includeTaxes()}
                    {$price->format($fee.value)|escape:'html':'UTF-8'}
                {else}
                    {$price->format($fee.value_tax_excl)|escape:'html':'UTF-8'}
                {/if}
            </td>
        </tr>
        {/foreach}
    </tbody>
{/if}