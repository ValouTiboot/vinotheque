{*
* Order Fees
*
* @author    motionSeed <ecommerce@motionseed.com>
* @copyright 2017 motionSeed. All rights reserved.
* @license   https://www.motionseed.com/en/license-module.html
*}
{foreach $fees as $fee}
{cycle values='#FFF,#DDD' assign=bgcolor}
    <tr>
        <td class="white">
            {$fee.name|escape:'html':'UTF-8'}
        </td>

        <td class="center white">
            {$fee.tax_rate|rtrim:'0'|rtrim:'.'|escape:'html':'UTF-8'} %
        </td>

        {if $display_tax_bases_in_breakdowns}
            <td class="right white">
                {$price->format($fee.value_tax_excl)|escape:'html':'UTF-8'}
            </td>
        {/if}

        <td class="right white">
            {$price->format($fee.value - $fee.value_tax_excl)|escape:'html':'UTF-8'}
        </td>
    </tr>
{/foreach}