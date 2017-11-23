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
<div id="range_price" class="product-variants card gift_card">
	<div class="product-variants-item">
		<span class="giftcard_custom_price" style="font-size:16px;margin-left:5px;">{l s='Enter Price: ' mod='giftcard'}</span>
		<input id="gift_card_price" class="product-price input-group form-control" name="giftcard_price" style="width: 40%;margin-left:5px;" value="{if isset($preselected_price) AND $preselected_price}{$preselected_price|escape:'htmlall':'UTF-8'}{/if}" onkeyup="_validatePrice($(this).val(), 'range')"/>
		{if $type == 'range'}
		<div class="amount" style="font-size:10px;margin-left:5px;">{l s='Enter value between' mod='giftcard'} {Tools::displayPrice($values[0])|escape:'htmlall':'UTF-8'} {l s=' and ' mod='giftcard'} {Tools::displayPrice($values[1])|escape:'htmlall':'UTF-8'}
			<div id="price_error" class="alert alert-danger error" style="width: 98%;display:none;">
				<p style="color:#CE1F21;font-size:18px;margin-left:5px;">{l s='Invalid Price' mod='giftcard'}</p>
			</div>
			<input type="hidden" id="range_min" value="{$values[0]|escape:'htmlall':'UTF-8'}"/>
			<input type="hidden" id="range_max" value="{$values[1]|escape:'htmlall':'UTF-8'}"/>
		</div>
		{/if}
	</div>
</div>
