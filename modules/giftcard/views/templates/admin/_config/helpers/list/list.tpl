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
{block name="input"}
	{if isset($smarty.get.msg) AND $smarty.get.msg}
	<div class="alert-messages">
		{if $smarty.get.msg == 1}
			<div class="conf alert alert-success">{l s='Successfully Creation' mod='giftcard' }</div>
		{elseif $smarty.get.msg == 2}
			<div class="conf alert alert-success">{l s='Deleted Successfully' mod='giftcard' }</div>
		{elseif $smarty.get.msg == 3}
			<div class="conf alert alert-success">{l s='Update Successfully' mod='giftcard' }</div>
		{else}	
			<div class="conf error alert alert-danger">{l s='Something went wrong. Try later.' mod='giftcard' }</div>
		{/if}
	</div>
	{/if}
		<div class="panel">
			<fieldset>
				<legend class="panel-heading"> <img src="{$smarty.const.__PS_BASE_URI__|escape:'htmlall':'UTF-8'}modules/giftcard/views/img/list.png" alt="">{l s='My Gift Cards' mod='giftcard'}</legend>
					<table class="table table-filter-templates" style="width:100%;">
						<thead>
							<tr>
								<th class="center"><span class="title_box"><strong>&nbsp;</strong></span></th>
								<th class="center"><span class="title_box"><strong>{l s='Image' mod='giftcard'}</strong></span></th>
								<th class="center"><span class="title_box"><strong>{l s='Card Name' mod='giftcard'}</strong></span></th>
								<th class="center"><span class="title_box"><strong>{l s='Qty' mod='giftcard'}</strong></span></th>
								<th class="center"><span class="title_box"><strong>{l s='Value Type' mod='giftcard'}</strong></span></th>
								<th class="center"><span class="title_box"><strong>{l s='Discount Type' mod='giftcard'}</strong></span></th>
								<th class="center"><span class="title_box"><strong>{l s='Discount Currency' mod='giftcard'}</strong></span></th>
								<th class="center"><span class="title_box"><strong>{l s='Expire Date' mod='giftcard'}</strong></span></th>
								<th class="center"><span class="title_box"><strong>{l s='Status' mod='giftcard' mod='giftcard' }</strong></span></th>
								<th class="center"><span class="title_box text-right"><strong>{l s='Action' mod='giftcard'}</strong></span></th>
							</tr>
						</thead>
						<tbody>
						{foreach from=$Gift_Card item=card}
						<form action="{$currentIndex|escape:'htmlall':'UTF-8'}&amp;token={$currentToken|escape:'htmlall':'UTF-8'}&amp;update" class="defaultForm" name="giftcard_form" id="giftcard_form" method="POST">
							<tr >
								<td class="center" style="padding:10px;width:50px;">{$card.id_gift_card|escape:'htmlall':'UTF-8'}</td class="center">
								<td class="center" style="padding:10px;width:50px;"><img src="{$link->getImageLink({$card.link_rewrite|escape:'htmlall':'UTF-8'}, {$card.id_image|escape:'htmlall':'UTF-8'}, 'home_default')|escape:'htmlall':'UTF-8'}" alt="{$card.card_name|escape:'htmlall':'UTF-8'}" class="imgm img-thumbnail" width="64"></td class="center">
								<td class="center">{$card.giftcard_product.name|escape:'htmlall':'UTF-8'}</td>
								<td class="center">{$stock_avail->getQuantityAvailableByProduct($card.id_product)|escape:'htmlall':'UTF-8'}</td>
								<td class="center">{$card.value_type|escape:'htmlall':'UTF-8'}</td>
								<td class="center">{$card.reduction_type|escape:'htmlall':'UTF-8'}</td>
								<td class="center">{$card.iso_code|escape:'htmlall':'UTF-8'}</td>
								<td class="center">{$card.to|escape:'htmlall':'UTF-8'}</td>
								<td class="center">
								{if $card.status == 1}
									<label class="t" for="active_on">
										<img src="../img/admin/enabled.gif" alt="Enabled" title="Enabled" />
									</label>
								{else}
									<label class="t" for="active_off">
										<img src="../img/admin/disabled.gif" alt="Disabled" title="Disabled" />
									</label>
								{/if}
								</td>
								{if $version >= 1.6}
									<td class="text-right">
										<div class="btn-group-action">
											<div class="btn-group pull-right">
												<a class="edit btn btn-default" title="{l s='Edit' mod='giftcard'}" href="{$link->getAdminLink('AdminGift')|escape:'htmlall':'UTF-8'}&amp;id_product={$card.id_product|intval}&amp;id_gift_card={$card.id_gift_card|intval}&amp;updateGift">
													<i class="icon-pencil"></i> {l s='Edit' mod='giftcard'}
												</a>
												<button data-toggle="dropdown" class="btn btn-default dropdown-toggle">
													<i class="icon-caret-down"></i>&nbsp;
												</button>
												<ul class="dropdown-menu">
													<li>
														<a class="delete" title="{l s='Delete' mod='giftcard'}" href="{$link->getAdminLink('AdminGift')|escape:'htmlall':'UTF-8'}&amp;id_product={$card.id_product|intval}&amp;id_gift_card={$card.id_gift_card|intval}&amp;deleteGift">
															<i class="icon-trash"></i> {l s='Delete' mod='giftcard'}
														</a>
													</li>
												</ul>
											</div>
										</div>
									</td>
								{else}
									<td class="center">
										<input type="submit" name="updateGift" value="" title="{l s='Edit' mod='giftcard'}" class="button" style="background:url({$smarty.const.__PS_BASE_URI__|escape:'htmlall':'UTF-8'}img/admin/edit.gif)no-repeat;width:16px;height:16px;border:none;cursor:pointer;"/>

										<input type="submit" name="deleteGift" value="" title="{l s='Delete' mod='giftcard'}" class="button" style="background:url({$smarty.const.__PS_BASE_URI__|escape:'htmlall':'UTF-8'}img/admin/delete.gif)no-repeat;width:16px;height:16px;border:none;cursor:pointer;"/>
									</td>
								{/if}
							</tr>
							<input type="hidden" name="id_gift_card" value="{$card.id_gift_card|escape:'htmlall':'UTF-8'}"/>
							<input type="hidden" name="id_product" value="{$card.id_product|escape:'htmlall':'UTF-8'}"/>
							<input type="hidden" name="id_attribute" value="{$card.id_attribute|escape:'htmlall':'UTF-8'}"/>
						</form>
						{/foreach}
						</tbody>
					</table>
			</fieldset>
		</div>
{/block}