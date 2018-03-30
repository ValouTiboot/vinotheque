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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2017 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{if $products}

	{if !$refresh}
	<div class="wishlistLinkTop">
	
        <a id="hideWishlist" class="button_account icon pull-right" href="#" onclick="WishlistVisibility('wishlistLinkTop', 'Wishlist'); return false;" rel="nofollow" title="{l s='Close this wishlist' mod='advansedwishlist'}">
            <i class="icon-remove"></i>
        </a>
        
        <ul class="clearfix display_list">
        
            <li>
                <a  id="hideBoughtProducts" class="button_account" href="#" onclick="WishlistVisibility('wlp_bought', 'BoughtProducts'); return false;" title="{l s='Hide products' mod='advansedwishlist'}">
                    {l s='Hide products' mod='advansedwishlist'}
                </a>
                <a id="showBoughtProducts" class="button_account" href="#" onclick="WishlistVisibility('wlp_bought', 'BoughtProducts'); return false;" title="{l s='Show products' mod='advansedwishlist'}">
                    {l s='Show products' mod='advansedwishlist'}
                </a>
            </li>
            
            {if count($productsBoughts)}
                <li>
                    <a id="hideBoughtProductsInfos" class="button_account" href="#" onclick="WishlistVisibility('wlp_bought_infos', 'BoughtProductsInfos'); return false;" title="{l s='Hide products' mod='advansedwishlist'}">
                        {l s='Hide bought products info' mod='advansedwishlist'}
                    </a>
                    <a id="showBoughtProductsInfos" class="button_account" href="#" onclick="WishlistVisibility('wlp_bought_infos', 'BoughtProductsInfos'); return false;" title="{l s='Show products' mod='advansedwishlist'}">
                        {l s='Show bought products info' mod='advansedwishlist'}
                    </a>
                </li>
            {/if}
            
        </ul>
        
        <p class="wishlisturl form-group">
            <label>{l s='Permalink' mod='advansedwishlist'}:</label>
            <input type="text" class="form-control" value="{$link->getModuleLink('advansedwishlist', 'view', ['token' => $token_wish])|escape:'html':'UTF-8'}" readonly="readonly"/>
        </p>
        <p class="submit">
            <div id="showSendWishlist">
                <a class="btn btn-secondary" href="#" onclick="WishlistVisibility('wl_send', 'SendWishlist'); return false;" title="{l s='Send this wishlist' mod='advansedwishlist'}">
                    <span>{l s='Send this wishlist' mod='advansedwishlist'}</span>
                </a>
            </div>
        </p>
        
	{/if}
	
	<div class="wlp_bought">
		<ul class="clearfix wlp_bought_list row">
		
		{foreach from=$products item=product name=i}
			<li id="wlp_{$product.id_product|escape:'htmlall':'UTF-8'}_{$product.id_product_attribute|escape:'htmlall':'UTF-8'}" class="col-lg-3 clearfix address {if $smarty.foreach.i.index % 2}alternate_{/if}item">
				<a href="javascript:;" class="lnkdel" onclick="WishlistProductManage('wlp_bought', 'delete', '{$id_wishlist|escape:'htmlall':'UTF-8'}', '{$product.id_product|escape:'htmlall':'UTF-8'}', '{$product.id_product_attribute|escape:'htmlall':'UTF-8'}', $('#quantity_{$product.id_product|escape:'htmlall':'UTF-8'}_{$product.id_product_attribute|escape:'htmlall':'UTF-8'}').val(), $('#priority_{$product.id_product|escape:'htmlall':'UTF-8'}_{$product.id_product_attribute|escape:'htmlall':'UTF-8'}').val());" title="{l s='Delete' mod='advansedwishlist'}">
				<i class="icon-times-circle"></i></a>
				<div class="clearfix">
					<div class="product_image">
						<a href="{$link->getProductlink($product.id_product, $product.link_rewrite, $product.category_rewrite)|escape:'htmlall':'UTF-8'}" title="{l s='Product detail' mod='advansedwishlist'}">
							{assign var=cover value="-"|explode:$product.cover}
							{if $cover[1] !== ''}
								<img src="{$link->getImageLink($product.link_rewrite, $product.cover, 'medium_default')|escape:'htmlall':'UTF-8'}" alt="{$product.name|escape:'html':'UTF-8'}" />
							{else}
								<img src="{$link->getImageLink($product.link_rewrite, 'fr-default', 'large_default')|escape:'htmlall':'UTF-8'}" alt="{$product.name|escape:'html':'UTF-8'}" />
							{/if}
						</a>
					</div>
					<div class="product_infos">
						<p id="s_title" class="product_name">{$product.name|truncate:30:'...'|escape:'html':'UTF-8'}</p>
						<div class="wishlist_product_detail">
						{if isset($product.attributes_small)}
							<p class="text-center"><a href="{$link->getProductlink($product.id_product, $product.link_rewrite, $product.category_rewrite)|escape:'htmlall':'UTF-8'}" title="{l s='Product detail' mod='advansedwishlist'}">{$product.attributes_small|escape:'html':'UTF-8'}</a></p>
						{else}
							<br />
						{/if}
							{l s='Quantity' mod='advansedwishlist'}:<input class="form-control" type="text" id="quantity_{$product.id_product|escape:'htmlall':'UTF-8'}_{$product.id_product_attribute|escape:'htmlall':'UTF-8'}" value="{$product.quantity|intval}" size="3"  />
							{l s='Priority' mod='advansedwishlist'}:
							<select class="form-control" id="priority_{$product.id_product|escape:'htmlall':'UTF-8'}_{$product.id_product_attribute|escape:'htmlall':'UTF-8'}">
								<option value="0"{if $product.priority eq 0} selected="selected"{/if}>{l s='High' mod='advansedwishlist'}</option>
								<option value="1"{if $product.priority eq 1} selected="selected"{/if}>{l s='Medium' mod='advansedwishlist'}</option>
								<option value="2"{if $product.priority eq 2} selected="selected"{/if}>{l s='Low' mod='advansedwishlist'}</option>
							</select>
							{if $wishlists|count > 1}
								{l s='Move'  mod='advansedwishlist'}:
								<br />
                                {foreach name=wl from=$wishlists item=wishlist}
                                    {if $smarty.foreach.wl.first}
                                       <select class="wishlist_change_button form-control">
                                       <option>---</option>
                                    {/if}
                                    {if $id_wishlist != {$wishlist.id_wishlist}}
	                                        <option title="{$wishlist.name|escape:'htmlall':'UTF-8'}" value="{$wishlist.id_wishlist|escape:'htmlall':'UTF-8'}" data-id-product="{$product.id_product|escape:'htmlall':'UTF-8'}" data-id-product-attribute="{$product.id_product_attribute|escape:'htmlall':'UTF-8'}" data-quantity="{$product.quantity|intval}" data-priority="{$product.priority|escape:'htmlall':'UTF-8'}" data-id-old-wishlist="{$id_wishlist|escape:'htmlall':'UTF-8'}" data-id-new-wishlist="{$wishlist.id_wishlist|escape:'htmlall':'UTF-8'}">
	                                                {l s='Move to %s'|sprintf:$wishlist.name mod='advansedwishlist'}
	                                        </option>
                                    {/if}
                                    {if $smarty.foreach.wl.last}
                                        </select>
                                        <br />
                                    {/if}
                                {/foreach}
                            {/if}
						</div>
					</div>
				</div>
				<div class="btn_action">
					<a href="javascript:;" class="exclusive lnksave btn btn-secondary" onclick="WishlistProductManage('wlp_bought_{$product.id_product_attribute|escape:'htmlall':'UTF-8'}', 'update', '{$id_wishlist|escape:'htmlall':'UTF-8'}', '{$product.id_product|escape:'htmlall':'UTF-8'}', '{$product.id_product_attribute|escape:'htmlall':'UTF-8'}', $('#quantity_{$product.id_product|escape:'htmlall':'UTF-8'}_{$product.id_product_attribute|escape:'htmlall':'UTF-8'}').val(), $('#priority_{$product.id_product|escape:'htmlall':'UTF-8'}_{$product.id_product_attribute|escape:'htmlall':'UTF-8'}').val());" title="{l s='Save' mod='advansedwishlist'}">{l s='Save' mod='advansedwishlist'}</a>
				    {capture}add=1&amp;id_product={$product.id_product|intval}{if isset($product.id_product_attribute) && $product.id_product_attribute}&amp;ipa={$product.id_product_attribute|intval}{/if}{if isset($static_token)}&amp;token={$static_token|escape:'htmlall':'UTF-8'}{/if}{/capture}
					<br>
					<br>
					<a class="add_cart ajax_add_to_cart_button exclusive btn btn-primary" data-id-product-attribute="{$product.id_product_attribute|intval}" data-id-product="{$product.id_product|intval}" data-minimal_quantity="{if isset($product.product_attribute_minimal_quantity) && $product.product_attribute_minimal_quantity >= 1}{$product.product_attribute_minimal_quantity|intval}{else}{$product.quantity|intval}{/if}"
                    class="button ajax_add_to_cart_button btn btn-default" href="{$link->getPageLink('cart', true, NULL, $smarty.capture.default, false)|escape:'html':'UTF-8'}" rel="nofollow" title="{l s='Add to cart' mod='advansedwishlist'}">{l s='Add to cart' mod='advansedwishlist'}</a>
				</div>
			</li>
		{/foreach}
		
		</ul>
	</div>
	{if !$refresh}
	<form method="post" class="wl_send std" onsubmit="return (false);" style="display: none;">
		<a id="hideSendWishlist" class="button_account btn icon"  href="#" onclick="WishlistVisibility('wl_send', 'SendWishlist'); return false;" rel="nofollow" title="{l s='Close this wishlist' mod='advansedwishlist'}">
			<i class="icon-remove"></i>
		</a>
		<fieldset>
			<p class="required">
				<label for="email1">{l s='Email' mod='advansedwishlist'}1 <sup>*</sup></label>
				<input type="text" name="email1" id="email1" />
			</p>
			{section name=i loop=11 start=2}
			<p>
				<label for="email{$smarty.section.i.index|escape:'htmlall':'UTF-8'}">{l s='Email' mod='advansedwishlist'}{$smarty.section.i.index|escape:'htmlall':'UTF-8'}</label>
				<input type="text" name="email{$smarty.section.i.index|escape:'htmlall':'UTF-8'}" id="email{$smarty.section.i.index|escape:'htmlall':'UTF-8'}" />
			</p>
			{/section}
			<p class="submit">
				<input class="button" type="submit" value="{l s='Send' mod='advansedwishlist'}" name="submitWishlist" onclick="WishlistSend('wl_send', '{$id_wishlist|escape:'htmlall':'UTF-8'}', 'email');" />
			</p>
			<p class="required">
				<sup>*</sup> {l s='Required field' mod='advansedwishlist'}
			</p>
		</fieldset>
	</form>
	{if count($productsBoughts)}
	<table class="wlp_bought_infos hidden std">
		<thead>
			<tr>
				<th class="first_item">{l s='Product' mod='advansedwishlist'}</th>
				<th class="item">{l s='Quantity' mod='advansedwishlist'}</th>
				<th class="item">{l s='Offered by' mod='advansedwishlist'}</th>
				<th class="last_item">{l s='Date' mod='advansedwishlist'}</th>
			</tr>
		</thead>
		<tbody>
		{foreach from=$productsBoughts item=product name=i}
			{foreach from=$product.bought item=bought name=j}
			{if $bought.quantity > 0}
				<tr>
					<td class="first_item">
						<span style="float:left;"><img src="{$link->getImageLink($product.link_rewrite, $product.cover, 'small')|escape:'html'}" alt="{$product.name|escape:'html':'UTF-8'}" /></span>
						<span style="float:left;">
							{$product.name|truncate:40:'...'|escape:'html':'UTF-8'}
						{if isset($product.attributes_small)}
							<br /><i>{$product.attributes_small|escape:'html':'UTF-8'}</i>
						{/if}
						</span>
					</td>
					<td class="item align_center">{$bought.quantity|intval}</td>
					<td class="item align_center">{$bought.firstname|escape:'htmlall':'UTF-8'} {$bought.lastname|escape:'htmlall':'UTF-8'}</td>
					<td class="last_item align_center">{$bought.date_add|date_format:"%Y-%m-%d"|escape:'htmlall':'UTF-8'}</td>
				</tr>
			{/if}
			{/foreach}
		{/foreach}
		</tbody>
	</table>
	{/if}
	{/if}
{else}
	<p class="warning">{l s='No products' mod='advansedwishlist'}</p>
{/if}
