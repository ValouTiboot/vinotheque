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
<div id="dropdown_price" class="product-variants gift_card">
	<div class="product-variants-item">
		<label>{l s='MONTANT' mod='giftcard'}</label>
		<select class="product-price form-control form-control-select" name="giftcard_price" id="gift_card_price">
			{foreach from=$prices_tax item=price key=k}
				<option value="{$values[$k]|escape:'htmlall':'UTF-8'}" {if isset($preselected_price) AND $preselected_price AND $preselected_price == $values[$k]}selected="selected"{/if}>
					{Tools::displayPrice($price)|escape:'htmlall':'UTF-8'}
				</option>
			{/foreach}
		</select>
	</div>
</div>