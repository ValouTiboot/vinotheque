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

{extends file='page.tpl'}

{block name='notifications'}
  {include file='_partials/notifications.tpl'}
{/block}

{block name='content'}
  <section id="content" class="container">
    <h1 class="page_title">{l s='Shopping Cart' d='Shop.Theme.Checkout'}</h1>

    <div class="cart_navigation row">
		{block name='continue_shopping'}
          <a class="btn btn-secondary" href="{$urls.pages.index}">
            < {l s='Continue shopping' d='Shop.Theme.Actions'}
          </a>
          <a class="btn btn-secondary pull-right" href="#">
            {l s='Sauvegarder mon panier' d='Shop.Theme.Actions'}
          </a>
		{/block}
    </div>

    <div class="row">
      <div class="col-12 col-lg-8">
        {block name='cart_summary'}
          {render file='checkout/checkout-process.tpl' ui=$checkout_process}
        {/block}
      </div>
      <div id="checkout_details" class="col-12 col-lg-4">
        {block name='cart_summary'}
          {include file='checkout/_partials/cart-summary.tpl' cart=$cart}
        {/block}
      </div>
    </div>

  </section>
{/block}
