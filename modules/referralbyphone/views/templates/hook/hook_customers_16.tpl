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
<div class="col-lg-12">
	<div class="panel">
		<div class="panel-heading">{l s='Referral program' mod='referralbyphone'} <span class="badge">{count($friends)|intval}</span></div>
		{strip}
			<div class="panel-heading">
				{if isset($sponsor)}
					{l s='Customer\'s sponsor:' mod='referralbyphone'}
					&nbsp;<a href="index.php?tab=AdminCustomers&amp;id_customer={$sponsor->id}&amp;viewcustomer&amp;token={$token}">{$sponsor->firstname} {$sponsor->lastname} {$sponsor_ref_code}</a>
				{else}
					{l s='No one has sponsored this customer.' mod='referralbyphone'}
				{/if}
			</div>
		{/strip}
		<div class="panel-heading"><span class="badge">{count($friends)|intval}</span> {if count($friends) > 1}{l s='Sponsored customers:' mod='referralbyphone'}{else}{l s='Sponsored customer:' mod='referralbyphone'}{/if}</div>
		<p>
{l s='Customer Referral Code' mod='referralbyphone'}: <b>{$customer_ref_code}</b> </br >
{l s='Customer Referral Link' mod='referralbyphone'}: <b>{$sponsor_url}</b>
</p>
		<table class="table">
			<thead>
				<tr>
					<th><span class="title_box">{l s='ID' mod='referralbyphone'}</span></th>
					<th><span class="title_box">{l s='Name' mod='referralbyphone'}</span></th>
					<th><span class="title_box">{l s='Email' mod='referralbyphone'}</span></th>
					<th><span class="title_box">{l s='Registration date' mod='referralbyphone'}</span></th>
					<th><span class="title_box">{l s='Customers sponsored by this friend' mod='referralbyphone'}</span></th>
					<th><span class="title_box">{l s='Placed orders' mod='referralbyphone'}</span></th>
					<th><span class="title_box">{l s='Customer account created' mod='referralbyphone'}</span></th>
				</tr>
			</thead>
			<tbody>
				{foreach key=key item=friend from=$friends}
				<tr{if $key++ % 2} class="alt_row"{/if}{if $friend['id_customer']} onclick="document.location='?controller=AdminCustomers&amp;id_customer={$friend.id_customer|intval}&amp;viewcustomer&amp;token={$token}'"{/if}>
					<td>{if $friend.id_customer}{$friend.id_customer}{else}--{/if}</td>
					<td>{$friend.firstname} {$friend.lastname}</td>
					<td>{$friend.email}</td>
					<td>{$friend.date_add}</td>
					<td>{$friend.sponsored_friend_count|intval}</td>
					<td>{$friend.orders_count|intval}</td>
					<td>{if $friend.id_customer}<i class="icon-check list-action-enable action-enabled"></i>{else}<i class="icon-remove list-action-enable action-disabled"></i>{/if}</td>
				</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
</div>
