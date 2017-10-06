{*
* Order Fees
*
* @author    motionSeed <ecommerce@motionseed.com>
* @copyright 2017 motionSeed. All rights reserved.
* @license   https://www.motionseed.com/en/license-module.html
*}
{if isset($fees) && count($fees)}
<tbody id="orderfees_cart_list">
    {foreach $fees as $fee}
        <tr class="cart_discount {if $fee@last}last_item{elseif $fee@first}first_item{else}item{/if}" id="{if ($fee["obj"]->is_fee & $module->getConstant('IS_OPTION')) && ($fee["obj"]->display_selectable & $module->getConstant('CONTEXT_CART'))}cart_option_{else}cart_discount_{/if}{$fee.id_cart_rule|intval}">
            <td class="cart_discount_name" colspan="{if $PS_STOCK_MANAGEMENT}3{else}2{/if}">
                {if ($fee["obj"]->is_fee & $module->getConstant('IS_OPTION')) && ($fee["obj"]->display_selectable & $module->getConstant('CONTEXT_CART'))}
                    <p class="checkbox">
                        <input type="checkbox" id="fees_{$fee.id_cart_rule|intval}" name="fees[]" value="{$fee.id_cart_rule|intval}" {if $fee["is_checked"]}checked="checked"{/if} />&nbsp;

                        <label for="fees_{$fee.id_cart_rule|intval}">
                            <span class="dark">{$fee.name|escape:'html':'UTF-8'}</span>&nbsp;&nbsp;
                        </label>
                    </p>
                {else}
                    <span class="dark">{$fee.name|escape:'html':'UTF-8'}</span>&nbsp;&nbsp;
                {/if}
            </td>
            <td class="cart_discount_price">
                <span class="price-discount">
                {if !$priceDisplay}{displayPrice price=$fee["obj"]->unit_value_real*-1}{else}{displayPrice price=$fee["obj"]->unit_value_tax_exc*-1}{/if}
                </span>
            </td>
            <td class="cart_discount_delete">{$fee["obj"]->quantity|intval}</td>
            <td class="price_discount_del text-center">
            </td>
            <td class="cart_discount_price">
                <span class="price-discount price">
                    {if !$priceDisplay}{displayPrice price=$fee.value_real*-1}{else}{displayPrice price=$fee.value_tax_exc*-1}{/if}
                </span>
            </td>
        </tr>
    {/foreach}
</tbody>
{/if}