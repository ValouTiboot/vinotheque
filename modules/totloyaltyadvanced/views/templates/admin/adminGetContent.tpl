{*
* @version 1.0
* @author 202-ecommerce
* @copyright 2014-2015 202-ecommerce
* @license ?
*}
	<fieldset class="bg_table" id="totloyaltyadvanced">
		<legend><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/help.png" alt="">{l s='Help' mod='totloyaltyadvanced'}</legend>
		<p>"{$DisplayName|escape:'html':'UTF-8'}" {l s=' please click' mod='totloyaltyadvanced'}
</p> 

		<div>
			<a href="{$url|escape:'html':'UTF-8'}" class="cta">{l s='here' mod='totloyaltyadvanced'} {l s='to change configuration' mod='totloyaltyadvanced'}</a>
		</div><br />
		
		<h3>{l s='List and change customers loyalty points' mod='totloyaltyadvanced'}</h3>
		<p>{l s='Please refer to Customers > Loyalty points list' mod='totloyaltyadvanced'}.</p>

		<h3>{l s='Define product specific reward points amount' mod='totloyaltyadvanced'}</h3>
		<p>
		{l s='Please edit product, then go to' mod='totloyaltyadvanced'} {$DisplayName|escape:'html':'UTF-8'} {l s='and set amount, either as fixed amount or multiplier' mod='totloyaltyadvanced'}.</p>
	</fieldset>
