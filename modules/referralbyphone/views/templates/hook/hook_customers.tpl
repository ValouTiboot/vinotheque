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

<div class="clear">&nbsp;</div>
<h2>{l s='Referral program Pluse' mod='referralbyphone'} {count($friends)|intval}</h2>
<h3>
	{if isset($sponsor)}
		{l s='Customer\'s sponsor:' mod='referralbyphone'}
		<a href="index.php?tab=AdminCustomers&amp;id_customer={$sponsor->id}&amp;viewcustomer&amp;token={$token}">{$sponsor->firstname} {$sponsor->lastname} {$sponsor_ref_code}</a>
	{else}
		{l s='No one has sponsored this customer.' mod='referralbyphone'}
	{/if}
</h3>
<h3>{count($friends)|intval} {if count($friends) > 1}{l s='Sponsored customers:' mod='referralbyphone''}{else}{l s='Sponsored customer:' mod='referralbyphone'}{/if}</h3>
<p>
{l s='Customer Referral Code' mod='referralbyphone'}: <b>{$customer_ref_code}</b> </br >
{l s='Customer Referral Link' mod='referralbyphone'}: <b>{$sponsor_url}</b>
</p>
<table cellspacing="0" cellpadding="0" class="table">
	<tr>
		<th class="center">{l s='ID' mod='referralbyphone'}</th>
		<th class="center">{l s='Name' mod='referralbyphone'}</th>
		<th class="center">{l s='Email' mod='referralbyphone'}</th>
		<th class="center">{l s='Registration date' mod='referralbyphone'}</th>
		<th class="center">{l s='Customers sponsored by this friend' mod='referralbyphone'}</th>
		<th class="center">{l s='Placed orders' mod='referralbyphone'}</th>
		<th class="center">{l s='Customer account created' mod='referralbyphone'}</th>
	</tr>
	{foreach key=key item=friend from=$friends}
	<tr{if $key++ % 2} class="alt_row"{/if}{if $friend['id_customer']} style="cursor: pointer" onclick="document.location='?tab=AdminCustomers&amp;id_customer={$friend.id_customer}&amp;viewcustomer&amp;token={$token}'"{/if}>
		<td class="center">{if $friend.id_customer}{$friend.id_customer}{else}--{/if}</td>
		<td>{$friend.firstname} {$friend.lastname}</td>
		<td>{$friend.email}</td>
		<td align="right">{$friend.date_add}</td>
		<td>{$friend.sponsored_friend_count|intval}</td>
		<td align="right">{$friend.orders_count|intval}</td>
		<td align="center">{if $friend.id_customer}<img src="{$admin_image_dir}enabled.gif" />{else}<img src="{$admin_image_dir}disabled.gif"/>{/if}</td>
	</tr>
	{/foreach}
</table>
