{*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @author Snegurka <site@web-esse.ru>
*  @copyright  2007-2017 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{extends 'customer/page.tpl'}

{block name="page_title"}{l s='My wishlists' mod='advansedwishlist'}{/block}

{block name="page_content"}

    <div id="mywishlist">
        <script>
            var baseDir = '{$base_dir|addslashes}';
            var static_token = '{$static_token|addslashes}';
            var isLogged = true;
            var advansedwishlist_controller_url = '{$advansedwishlist_controller_url|addslashes}';
            var mywishlist_url= '{$mywishlist_url|addslashes}';

        </script>
		{if $advansedwishlistis17 != 1}
			{capture name=path}
                <a href="{$link->getPageLink('my-account', true)|escape:'html':'UTF-8'}">
					{l s='My account' mod='advansedwishlist'}
                </a>
                <span class="navigation-pipe">
            {$navigationPipe|escape:'htmlall':'UTF-8'}
        </span>
                <span class="navigation_page">
            {l s='My wishlists' mod='advansedwishlist'}
        </span>
			{/capture}

            <h1 class="page-heading">{l s='My wishlists' mod='advansedwishlist'}</h1>
		{/if}
		{if $id_customer|intval neq 0}
            <section class="wishlist-form">
                <form method="post" id="form_wishlist">
                    <section>
                        <h3 class="page-subheading">{l s='New wishlist' mod='advansedwishlist'}</h3>
                        <div class="form-group row">
                            <input type="hidden" name="token" value="{$token|escape:'html':'UTF-8'}" />
                            <label class="col-md-3 form-control-label" for="name">
								{l s='Name' mod='advansedwishlist'}
                            </label>
                            <div class="col-md-6">
                                <input type="text" id="name" name="name" class="form-control" value="{if isset($smarty.post.name) and $errors|@count > 0}{$smarty.post.name|escape:'html':'UTF-8'}{/if}" />
                            </div>
                        </div>
                        <p class="submit text-center">
                            <button id="submitWishlist" class="btn btn-primary" type="submit" name="submitWishlist">
                                {l s='Save' mod='advansedwishlist'}
                            </button>
                        </p>
                    </section>
                </form>
            </section>
			{if $wishlists}
                <div id="block-history" class="block-center">
                    <table class="table table-bordered">
                        <thead class="thead-default">
                        <tr>
                            <th class="first_item">{l s='Name' mod='advansedwishlist'}</th>
                            <th class="item mywishlist_first">{l s='Qty' mod='advansedwishlist'}</th>
                            <th class="item mywishlist_first">{l s='Viewed' mod='advansedwishlist'}</th>
                            <th class="item mywishlist_second">{l s='Created' mod='advansedwishlist'}</th>
                            <th class="item mywishlist_second">{l s='Direct Link' mod='advansedwishlist'}</th>
                            <th class="item mywishlist_second">{l s='Default' mod='advansedwishlist'}</th>
                            <th class="last_item mywishlist_first">{l s='Delete' mod='advansedwishlist'}</th>
                        </tr>
                        </thead>
                        <tbody>
						{section name=i loop=$wishlists}
                            <tr id="wishlist_{$wishlists[i].id_wishlist|intval}">
                                <td style="width:200px;">
                                    <a href="#" onclick="javascript:event.preventDefault();WishlistManage('block-order-detail', '{$wishlists[i].id_wishlist|intval}');">
										{$wishlists[i].name|truncate:30:'...'|escape:'htmlall':'UTF-8'}
                                    </a>
                                </td>
                                <td class="bold align_center">
									{assign var=n value=0}
									{foreach from=$nbProducts item=nb name=i}
										{if $nb.id_wishlist eq $wishlists[i].id_wishlist}
											{assign var=n value=$nb.nbProducts|intval}
										{/if}
									{/foreach}
									{if $n}
										{$n|intval}
									{else}
                                        0
									{/if}
                                </td>
                                <td>{$wishlists[i].counter|intval}</td>
                                <td>{$wishlists[i].date_add|date_format:"%Y-%m-%d"|escape:'htmlall':'UTF-8'}</td>
                                <td>
                                    <a href="#" onclick="javascript:event.preventDefault();WishlistManage('block-order-detail', '{$wishlists[i].id_wishlist|intval}');">
										{l s='View' mod='advansedwishlist'}
                                    </a>
                                </td>
                                <td class="wishlist_default">
									{if isset($wishlists[i].default) && $wishlists[i].default == 1}
                                        <p class="is_wish_list_default">
											{if $advansedwishlistis17 == 1}
                                                <i class="material-icons">assignment_turned_in</i>
											{else}
                                                <i class="icon icon-check-square"></i>
											{/if}
                                        </p>
									{else}
                                        <a href="#" onclick="javascript:event.preventDefault();(WishlistDefault('wishlist_{$wishlists[i].id_wishlist|intval}', '{$wishlists[i].id_wishlist|intval}'));">
											{if $advansedwishlistis17 == 1}
                                                <i class="material-icons">check_box_outline_blank</i>
											{else}
                                                <i class="icon icon-square"></i>
											{/if}
                                        </a>
									{/if}
                                </td>
                                <td class="wishlist_delete">
                                    <a class="icon" href="#" onclick="javascript:event.preventDefault();return (WishlistDelete('wishlist_{$wishlists[i].id_wishlist|intval}', '{$wishlists[i].id_wishlist|intval}', '{l s='Do you really want to delete this wishlist ?' mod='advansedwishlist' js=1}'));">
										{if $advansedwishlistis17 == 1}
                                            <i class="material-icons">delete</i>
										{else}
                                            <i class="icon-remove"></i>
										{/if}

                                    </a>
                                </td>
                            </tr>
						{/section}
                        </tbody>
                    </table>
                </div>
                <div id="block-order-detail">&nbsp;</div>
			{/if}
		{/if}
    </div>

{/block}