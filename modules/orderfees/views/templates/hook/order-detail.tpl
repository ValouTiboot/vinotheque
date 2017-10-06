{*
* Order Fees
*
* @author    motionSeed <ecommerce@motionseed.com>
* @copyright 2017 motionSeed. All rights reserved.
* @license   https://www.motionseed.com/en/license-module.html
*}
{foreach from=$fees item=fee}
    <tr class="item">
        <td colspan="2">{$fee.name|escape:'html':'UTF-8'}</td>
        <td><span class="order_qte_span">{$fee.quantity|intval}</span></td>
        <td>{convertPriceWithCurrency price=$fee.unit_value_real currency=$currency}</td>
        <td>{convertPriceWithCurrency price=$fee.value currency=$currency}</td>
    </tr>
{/foreach}
