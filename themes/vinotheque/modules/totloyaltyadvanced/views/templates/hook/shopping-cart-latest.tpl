{*
* @version 1.0
* @author 202-ecommerce
* @copyright 2014-2015 202-ecommerce
* @license ?
*}

<!-- MODULE Loyalty -->

<div id="loyalty">
	<i class="icon-v-bottle"></i>
	{if $points > 0}
		{l s='En validant ce panier vous cumulez' mod='totloyaltyadvanced'} <b>
		{if $points > 1}{l s='%d  points' sprintf=[$points] mod='totloyaltyadvanced'}{else}{l s='%d  point' sprintf=[$points] mod='totloyaltyadvanced'}{/if}</b>
		{l s='fidélité' mod='totloyaltyadvanced'}{if isset($guest_checkout) && $guest_checkout}<sup>*</sup>{/if}<br />
		{if isset($guest_checkout) && $guest_checkout}<sup>*</sup> {l s='Not available for Instant checkout order' mod='totloyaltyadvanced'}{/if}
	{else}
		{l s='Add some products to your shopping cart to collect some loyalty points.' mod='totloyaltyadvanced'}
	{/if}
	{if isset($vouchpoints) && $vouchpoints}
		<fieldset>
			<p>
			 <a href="{$link->getModuleLink('totloyaltyadvanced', 'default', ['process' => 'transformpoints1'])|escape:'htmlall':'UTF-8'}" onclick="return confirm('{l s='Are you sure you want to transform your points into vouchers?' mod='totloyaltyadvanced' js=1}');" class="cta_btn">
					{l s='Transform my points into a voucher of' mod='totloyaltyadvanced'}
			<span class="price">{$voucherOld|escape:'htmlall':'UTF-8'}</span>.
		 </a>
		</p>
		</fieldset>
	{/if}
</div>

<!-- END : MODULE Loyalty -->
