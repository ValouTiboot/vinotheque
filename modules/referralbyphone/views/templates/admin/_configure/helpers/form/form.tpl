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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2017 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{extends file="helpers/form/form.tpl"}
{block name="field"}
	{if $input.type == 'discount_value'}
		<div class="col-lg-2">
			<table class="table table-condensed {$input.class|escape:'html':'UTF-8'}" id="discount_value">
				<thead>
					<tr>
						<th>{l s='Currency' mod='referralbyphone'}</th>
						<th>{l s='Voucher amount' mod='referralbyphone'}</th>
					</tr>
				</thead>
				{foreach from=$currencies item=currency}
					<tr>
						<td>{$currency.name|escape:'html':'UTF-8'}</td>
						<td>
							<div class="input-group">
								<input type="text" name="{$input.name|escape:'html':'UTF-8'}[{$currency.id_currency|intval}]" id="{$input.id|escape:'html':'UTF-8'}[{$currency.id_currency|intval}]" value="{$fields_value[$input.name|escape:'htmlall':'UTF-8'][{$currency.id_currency}]|intval}">
								<span class="input-group-addon">
								{$currency.sign|escape:'html':'UTF-8'}
								</span>
							</div>
						</td>
					</tr>
				{/foreach}
			</table>
		</div>
	{/if}
	{$smarty.block.parent}
{/block}