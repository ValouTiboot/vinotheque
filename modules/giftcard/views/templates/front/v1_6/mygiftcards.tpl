{*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    FMM Modules
*  @copyright 2017 FMM Modules
*  @version   1.4.0
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
{include file="$tpl_dir./errors.tpl"}
<div class="block">
{if isset($smarty.get.msg) AND $smarty.get.msg == 1}<div class="success conf alert alert-success"><p>{l s='Your gift card has been sent successfully.' mod='giftcard'}</p></div>{/if}
{capture name=path}<a href="{$link->getPageLink('my-account', true)|escape:'htmlall':'UTF-8'}">{l s='My account' mod='giftcard'}</a><span class="navigation-pipe">{$navigationPipe|escape:'htmlall':'UTF-8'}</span>{l s='My Giftcards' mod='giftcard'}{/capture}
{if $ps_version < 1.6}{include file="$tpl_dir./breadcrumb.tpl"}{/if}
<h4 class="block title_block">{l s='My Gift Cards' mod='giftcard'}</h4>
	<div id="block-giftcard" class="block-center table-responsive">
		{if count($coupens) > 0}
		<table class="table table-bordered {if $ps_version < 1.6}std{/if}" id="order-list">
				<thead>
					<tr>
						<th class="first_item">{l s='Card' mod='giftcard'}</th>
						<th class="item">{l s='Code' mod='giftcard'}</th>
						<th class="item">{l s='Qty' mod='giftcard'}</th>
						<th class="item">{l s='Discount' mod='giftcard'}</th>
						<th class="item">{l s='To' mod='giftcard'}</th>
						<th class="item">{l s='Email' mod='giftcard'}</th>
						<th class="item">{l s='Expire Date' mod='giftcard'}</th>
						<th class="item">{l s='Send' mod='giftcard'}</th>
					</tr>
				</thead>
			<tbody>
				{foreach from=$coupens item=card}
					<form action="{$link->getModuleLink('giftcard','mygiftcards')|escape:'htmlall':'UTF-8'}" method="post" name="giftcard_send_to_friend" id="from_giftcard">
						<tr style="text-align:center">
							<td>
								<center><span><img style="height:50px;" alt="" src="{$link->getImageLink($card.link_rewrite, $card.id_image, 'small_default')|escape:'htmlall':'UTF-8'}"></span>
								<p>{$card.name|escape:'htmlall':'UTF-8'}<p></center>
							</td>
							<td>
								{$card.code|escape:'htmlall':'UTF-8'}
							</td>
							<td>
								{$card.quantity|escape:'htmlall':'UTF-8'}
							</td>
							<td>
								{if isset($card.reduction_percent) AND $card.reduction_percent != 0}{$card.reduction_percent|escape:'htmlall':'UTF-8'}{l s='%' mod='giftcard'}{elseif isset($card.reduction_amount) AND $card.reduction_amount != 0}{convertPrice price=$card.reduction_amount|escape:'htmlall':'UTF-8'}{else}{l s='0' mod='giftcard'}{/if}
							</td>
							<td>
								<input class="form-control" type="text" name="friend_name" value="" style="height:25px;padding:0px 5px 0px 5px;" required/>
							</td>
							<td>
								<input class="form-control" type="text" name="friend_email" value="" style="height:25px;padding:0px 5px 0px 5px;" required/>
							</td>
							<td>
								{$card.date_to|escape:'htmlall':'UTF-8'}
							</td>
							<td>
								<input class="button exclusive" type="submit" name="send_giftcard" value=" Send "/>
							</td>
						</tr>
						<input type="hidden" name="giftcard_name" value="{$card.name|escape:'htmlall':'UTF-8'}"/>
						<input type="hidden" name="vcode" value="{$card.code|escape:'htmlall':'UTF-8'}"/>
						<input type="hidden" name="id_coupen" value="{$card.id_cart_rule|escape:'htmlall':'UTF-8'}"/>
						<input type="hidden" name="expire_date" value="{$card.date_to|escape:'htmlall':'UTF-8'}"/>
					</form>
				{/foreach}
			</tbody>
		</table>
		{else}
			<div class="alert alert-warning warning">
				<center>{l s='You did not purchased any Gift card yet.' mod='giftcard'}</center>
			</div>
		{/if}
	</div>
	<ul class="footer_links">
		<li>
		{if $ps_version < 1.6}
			<a href="{$link->getPageLink('my-account', true)|escape:'htmlall':'UTF-8'}">
				<img src="{$img_dir|escape:'htmlall':'UTF-8'}icon/my-account.gif" alt="{l s='Back to Your Account' mod='giftcard'}" class="icon" />
			</a>
		{/if}
			<a href="{$link->getPageLink('my-account', true)|escape:'htmlall':'UTF-8'}" class="btn btn-primary">
				<span><i class="icon-chevron-left"></i> {l s='Back to Your Account' mod='giftcard'}</span>
			</a>
		</li>
		<li class="f_right">
		{if $ps_version < 1.6}
			<a href="{$base_dir|escape:'htmlall':'UTF-8'}">
				<img src="{$img_dir|escape:'htmlall':'UTF-8'}icon/home.gif" alt="{l s='Home' mod='giftcard'}" class="icon" />
			</a>
		{/if}
			<a href="{$base_dir|escape:'htmlall':'UTF-8'}" class="btn btn-primary">
				<span><i class="icon-home"></i> {l s='Home' mod='giftcard'}</span>
			</a>
		</li>
	</ul>
</div>
