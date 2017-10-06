{*
* Order Fees
*
* @author    motionSeed <ecommerce@motionseed.com>
* @copyright 2017 motionSeed. All rights reserved.
* @license   https://www.motionseed.com/en/license-module.html
*}
<table id="block_cart_fees" class="{if $fees|@count == 0} unvisible{/if}">
        {foreach from=$fees item=fee}
            <tr class="bloc_cart_voucher {if !($fee["obj"]->is_fee & $module->getConstant('IS_OPTION')) || !($fee["obj"]->display_selectable & $module->getConstant('CONTEXT_CART'))}is_fee{/if}" id="bloc_cart_voucher_{$fee.id_cart_rule|intval}">
                <td>
                    {if ($fee["obj"]->is_fee & $module->getConstant('IS_OPTION')) && ($fee["obj"]->display_selectable & $module->getConstant('CONTEXT_CART'))}
                        <p class="checkbox">
                            <input type="checkbox" id="fees_blockcart_{$fee.id_cart_rule|intval}" name="fees[]" value="{$fee.id_cart_rule|intval}" {if $fee["is_checked"]}checked="checked"{/if} />&nbsp;
                        </p>
                    {/if}
                </td>
                <td class="quantity">{$fee["obj"]->quantity|intval}x</td>
                <td class="name" title="{$fee.description|escape:'html':'UTF-8'}">
                    {$fee.name|escape:'html':'UTF-8'}
                </td>
                <td class="price">
                    {if $priceDisplay == 1}{convertPrice price=$fee.value_tax_exc*-1}{else}{convertPrice price=$fee.value_real*-1}{/if}
                </td>
                <td class="delete">
                </td>
            </tr>
        {/foreach}
</table>
