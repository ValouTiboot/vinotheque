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

{block name='page_title'}
  {l s='Mon compte' d='Shop.Theme.Customeraccount'}
{/block}

{block name='page_content_container'}
  <section id="content" class="page-content container page-my-account">

    {block name="account_link_list"}
      <ul class="link-list row">
        <li class="col-lg-4 col-sm-6 col-12">
          <a id="identity-link" href="{$urls.pages.identity}">
            <i class="icon-v-user"></i>
            <span>{l s='Mes informations' d='Shop.Theme.Customeraccount'}</span>
          </a>
        </li>

        {if $customer.addresses|count}
          <li class="col-lg-4 col-sm-6 col-12">
            <a id="addresses-link" href="{$urls.pages.addresses}">
              <i class="icon-v-maps"></i>
              <span>{l s='Mes adresses' d='Shop.Theme.Customeraccount'}</span>
            </a>
          </li>
        {else}
          <li class="col-lg-4 col-sm-6 col-12">
            <a id="address-link" href="{$urls.pages.address}">
              <i class="icon-v-maps"></i>
              <span>{l s='Add first address' d='Shop.Theme.Customeraccount'}</span>
            </a>
          </li>
        {/if}

        {if !$configuration.is_catalog}
          <li class="col-lg-4 col-sm-6 col-12">
            <a id="history-link" href="{$urls.pages.history}">
              <i class="icon-v-planning"></i>
              <span>{l s='Order history and details' d='Shop.Theme.Customeraccount'}</span>
            </a>
          </li>
        {/if}

        {if !$configuration.is_catalog}
          <li class="col-lg-4 col-sm-6 col-12">
            <a id="order-slips-link" href="{$urls.pages.order_slip}">
              <i class="icon-v-sheet"></i>
              <span>{l s='Credit slips' d='Shop.Theme.Customeraccount'}</span>
            </a>
          </li>
        {/if}

        {if $configuration.voucher_enabled && !$configuration.is_catalog}
          <li class="col-lg-4 col-sm-6 col-12">
            <a id="discounts-link" href="{$urls.pages.discount}">
              <i class="icon-v-pricetag"></i>
              <span>{l s='Vouchers' d='Shop.Theme.Customeraccount'}</span>
            </a>
          </li>
        {/if}

        {if $configuration.return_enabled && !$configuration.is_catalog}
          <li class="col-lg-4 col-sm-6 col-12">
            <a id="returns-link" href="{$urls.pages.order_follow}">
              <i class="icon-v-truck"></i>
              <span>{l s='Merchandise returns' d='Shop.Theme.Customeraccount'}</span>
            </a>
          </li>
        {/if}

        {block name='display_customer_account'}
          {hook h='displayCustomerAccount'}
        {/block}

      </ul>
    {/block}

  </section>
{/block}

{block name='page_footer'}
	{block name='my_account_links'}
      <div class="text-center">
        <a href="{$logout_url}" >
			{l s='Sign out' d='Shop.Theme.Actions'}
        </a>
      </div>
	{/block}
{/block}
