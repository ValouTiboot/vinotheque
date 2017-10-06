{*
* Order Fees
*
* @author    motionSeed <ecommerce@motionseed.com>
* @copyright 2017 motionSeed. All rights reserved.
* @license   https://www.motionseed.com/en/license-module.html
*}
{assign var="shipping_discount_tax_incl" value="0"}
{foreach $fees as $fee}
{cycle values='#FFF,#DDD' assign=bgcolor}
        <tr class="discount">
                <td class="white" colspan="2">
                        {$fee.name|escape:'html':'UTF-8'}
                </td>
                <td class="center white">{$fee.tax_rate|rtrim:'0'|rtrim:'.'|escape:'html':'UTF-8'} %</td>
                <td class="right white">
                    {$price->format($fee.unit_value_tax_exc)|escape:'html':'UTF-8'}
                </td>
                <td class="center white">{$fee.quantity|intval}</td>
                <td class="right white">
                    {$price->format($fee.value_tax_excl)|escape:'html':'UTF-8'}
                </td>
        </tr>
{/foreach}