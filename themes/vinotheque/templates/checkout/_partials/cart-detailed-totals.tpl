{**
 * 2007-2017 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2017 PrestaShop SA
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}
{block name='cart_detailed_totals'}
  <div class="cart-detailed-totals">
    <div class="cart-subtotals">
      {foreach from=$cart.subtotals item="subtotal"}
        {if $subtotal != NULL && $subtotal.type != 'tax'}
          <div class="{$subtotal.type}">
            <span class="label">{$subtotal.label}</span>
            <span class="value">{$subtotal.value}</span>
            {if $subtotal.type == 'shipping'}
              {hook h='displayExpressCheckoutCustom'}
            {/if}
          </div>
        {/if}
      {/foreach}
    </div>

    {block name='cart_voucher'}
        <div class="cart-voucher-details">
            <a href="#" data-toggle="collapse" data-target="#cart-voucher-form">
				{l s='Vous avez un bon de réduction' d='Shop.Theme.Actions'}?
            </a>
        </div>
        {include file='checkout/_partials/cart-voucher.tpl'}
    {/block}

    <div class="cart-total">
      <span class="label">{$cart.totals.total.label}</span>
      <span class="value">{$cart.totals.total.value}</span>
      {if $subtotal.type === 'shipping'}
          {hook h='displayCheckoutSubtotalDetails' subtotal=$subtotal}
      {/if}
    </div>

    <div class="cart-subtotals">
		{foreach from=$cart.subtotals item="subtotal"}
			{if $subtotal.type == 'tax' }
              <div class="{$subtotal.type}">
                <span class="label">{$subtotal.label}</span>
                <span class="value">{$subtotal.value}</span>
              </div>
			{/if}
		{/foreach}
    </div>
  </div>
{/block}
