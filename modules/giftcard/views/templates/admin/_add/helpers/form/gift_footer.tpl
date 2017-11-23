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
{if $version < 1.6}
<div class="col-lg-9">
	<input id="mybutton" type="submit" name="SaveGift" value="Save" class="button btn btn-default pull-right" />
</div>
{else}
	<div class="panel-footer">
		<a href="{$link->getAdminLink('AdminGift')|escape:'htmlall':'UTF-8'}" class="btn btn-default"><i class="process-icon-cancel"></i> {l s='Cancel' mod='giftcard'}</a>
		<button type="submit" name="SaveGift" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save' mod='giftcard'}</button>
	</div>
{/if}

<div class="form-group margin-form ">
	<div class="col-lg-3">
		<input type="hidden" name="id_product" {if $card != null AND isset($card.id_product)}value="{$card.id_product|escape:'htmlall':'UTF-8'}"{/if}value="">
		<input type="hidden" name="gid" {if $card != null AND isset($card.id_gift_card)}value="{$card.id_gift_card|escape:'htmlall':'UTF-8'}"{/if}value="">
		<input type="hidden" name="aid" {if $card != null AND isset($card.id_attribute)}value="{$card.id_attribute|escape:'htmlall':'UTF-8'}"{/if}value="">
	</div>
</div>