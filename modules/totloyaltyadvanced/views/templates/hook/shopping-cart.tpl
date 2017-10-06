{*
* @version 1.0
* @author 202-ecommerce
* @copyright 2014-2015 202-ecommerce
* @license ?
*}

<!-- MODULE Loyalty -->
<p id="loyalty">
	<img src="{$module_template_dir|escape:'html':'UTF-8'}views/img/loyalty.gif" alt="{l s='loyalty' mod='totloyaltyadvanced'}" class="icon" />
	{if $points > 0}
		{l s='By checking out this shopping cart you can collect up to' mod='totloyaltyadvanced'} <b>
		{if $points > 1}{l s='%d  points' sprintf=$points mod='totloyaltyadvanced'}{else}{l s='%d  point' sprintf=$points mod='totloyaltyadvanced'}{/if}</b>
		{l s='that can be converted into a voucher of' mod='totloyaltyadvanced'} {convertPrice price=$voucher}{if isset($guest_checkout) && $guest_checkout}<sup>*</sup>{/if}.<br />
		{if isset($guest_checkout) && $guest_checkout}<sup>*</sup> {l s='Not available for Instant checkout order' mod='totloyaltyadvanced'}{/if}
	{else}
		{l s='Add some products to your shopping cart to collect some loyalty points.' mod='totloyaltyadvanced'}
	{/if}
</p>
{if $voucherOld}
	<fieldset>
		<p>
         <a href="{$link->getModuleLink('totloyaltyadvanced', 'default', ['process' => 'transformpoints1'])|escape:'htmlall':'UTF-8'}" onclick="return confirm('{l s='Are you sure you want to transform your points into vouchers?' mod='totloyaltyadvanced' js=1}');" class="cta_btn">
				{l s='Transform my points into a voucher of' mod='totloyaltyadvanced'}
	    <span class="price">{convertPrice price=$voucherOld}</span>.
 	 </a>
	</p>
	</fieldset>
{/if}
<!-- END : MODULE Loyalty -->
