{*
* Order Fees
*
* @author    motionSeed <ecommerce@motionseed.com>
* @copyright 2017 motionSeed. All rights reserved.
* @license   https://www.motionseed.com/en/license-module.html
*}
{foreach $fees as $fee}
    {cycle values='#FFF,#DDD' assign=bgcolor}
    <tr class="product">
        <td class="center"> &nbsp;</td>
        <td class="white right">{$fee.name|escape:'html':'UTF-8'}</td>
        <td class="center">{$fee["obj"]->quantity|intval}</td>
    </tr>
{/foreach}