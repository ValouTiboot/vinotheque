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

<div id="giftcard_color" class="gift_card tab-pane" style="display:none;">
    <h3 class="tab"><i class="icon-credit-card"></i> {l s='Gift Coupon' mod='giftcard'}</h3><div class="separation"></div>
			<!-- Discount -->
			<label class="form-group control-label col-lg-3">
				<span class="label-tooltip" data-toggle="tooltip" title="{l s='Apply discount on gift card.' mod='giftcard'}">{l s='Discount Type : ' mod='giftcard'}</span>
			</label>
			<div class="form-group margin-form ">
				<div class="col-lg-9">	
					<input type="radio" value="percent" id="apply_discount_percent" name="apply_discount" {if $card != null AND isset($card.reduction_type) AND $card.reduction_type == 'percent'}checked="checked"{/if}>
					<label for="apply_discount_percent" class="t">
						<img style="cursor:pointer" title="Enabled" alt="Enabled" src="../img/admin/enabled.gif">{l s='Percent (%)' mod='giftcard'}</label>
					<input type="radio" value="amount" id="apply_discount_amount" name="apply_discount" {if $card != null AND $card.reduction_type == 'amount'}checked="checked"{/if}>
					<label for="apply_discount_amount" class="t">
						<img style="cursor:pointer" title="Enabled" alt="Enabled" src="../img/admin/enabled.gif">{l s='Amount' mod='giftcard'}</label>

				</div>
			</div>
				<div id="apply_discount_percent_div" {if $card != null AND $card.reduction_type == 'percent'}style="display:block;"{/if}style="display:none;">
					<label class="form-group control-label col-lg-3">
						{l s='Value(s) : ' mod='giftcard'}
					</label>
				<div class="form-group margin-form">
					<!-- pecentage value for fixed price -->
					<div id="percent_fixed" class="col-lg-6" style="display:none;">
						<div class="input-group col-lg-6">
							<span class="input-group-addon">{l s='%' mod='giftcard'}</span>
							<input type="text" name="reduction_percent_fixed" id="reduction_percent" value="{if $card != null AND isset($card.reduction_amount) AND $card.reduction_type == 'percent'}{$card.reduction_amount|escape:'htmlall':'UTF-8'}{/if}">	
						</div>
					</div>

					<!-- percentage values for dropdown list -->
					<div  id="percent_dropdown" class="col-lg-6" style="display:none;">
						<div class="input-group">
							<span class="input-group-addon">{l s='%' mod='giftcard'}</span>
							<input type="text" name="reduction_percent_dropdown" {if $card != null AND isset($card.reduction_amount) AND $card.reduction_type == 'percent'}value="{$card.reduction_amount|escape:'htmlall':'UTF-8'}"{else}value=""{/if}>
						</div>
							<p class="preference_description help-block hint-block" style="padding-top:3px;">{l s='Example: 5,10,15,20 (use comma separater. Th percentage will be applied respectively.)' mod='giftcard'}</p>
					</div>

					<!-- percentage values for rage type -->
					<div id="percent_range" class="input-group"  style="display:none;">
						{if $card != null AND !empty($card.reduction_amount) AND $card.value_type == 'range' AND $card.reduction_type == 'percent'}
							{assign var=per value=","|explode:$card.reduction_amount}
						{/if}

						<div class="col-lg-4">
							<div class="input-group">
								<span class="input-group-addon">{l s='%' mod='giftcard'}</span>
								<input type="text" name="min_percent" {if $card != null AND !empty($card.reduction_amount) AND $card.value_type == 'range' AND $card.reduction_type == 'percent'}value="{if !empty($per) AND $per}{$per[0]|escape:'htmlall':'UTF-8'}{/if}"{/if}/><span class="input-group-addon">{l s='Min' mod='giftcard'}</span>
							</div>
						</div>
						<div class="col-lg-4">
							<div class="input-group">
								<span class="input-group-addon">{l s='%' mod='giftcard'}</span>
								<input type="text" name="max_percent"{if $card != null AND !empty($card.reduction_amount) AND $card.value_type == 'range' AND $card.reduction_type == 'percent'}value="{if !empty($per) AND $per}{$per[1]|escape:'htmlall':'UTF-8'}{/if}"{/if}/><span class="input-group-addon">{l s='Max' mod='giftcard'}</span>
							</div>
						</div>
						<p class="preference_description help-block hint-block" style="padding-top:-5px;">{l s='Select min and max percentage value.' mod='giftcard'}</p>
					</div>
				</div>
				</div>

				<div class="clearfix"></div>
				<div id="apply_discount_to_div" {if $card != null AND isset($card.reduction_type) AND ($card.reduction_type == 'amount' OR $card.reduction_type == 'percent')}style="display:block;"{/if}style="display:none;">
					<label class="form-group control-label col-lg-3">
						<span class="label-tooltip" data-toggle="tooltip" title="{l s='You can apply specified amount/percentage of discount to the whole order or you can choose a specific product.' mod='giftcard'}">{l s='Apply discount to : ' mod='giftcard'}</span>
					</label>
					<div class="margin-form">
						<div class="col-lg-9 form-group">
							<input type="radio" value="order" id="apply_discount_to_order" name="apply_discount_to" {if !empty($card) AND $card.id_discount_product == 0 }checked="checked"{/if}>
							<label for="apply_discount_to_order" class="t">{l s='Order (without shipping)' mod='giftcard'}</label>
							<input type="radio" value="specific" id="apply_discount_to_product" name="apply_discount_to" {if !empty($card) AND !empty($card.id_discount_product)}checked="checked"{/if}>
							<label for="apply_discount_to_product" class="t">{l s='Specific product' mod='giftcard'}</label>
						</div>
					</div>
					<div id="apply_discount_to_product_div" {if $card != null AND isset($card.id_discount_product) AND $card.id_discount_product != 0}style="display:block;"{/if}style="display:none;">
						<label class="form-group control-label col-lg-3">{l s='Product : ' mod='giftcard'}</label>
						<div class="margin-form">
							<div class="col-lg-7">
								<div class="input-group">
									<input type="text" style="width:400px" {if $card != null AND isset($card.discount_product)}value="{$card.discount_product|escape:'htmlall':'UTF-8'}"{/if} value="" name="reductionProductFilter" id="reductionProductFilter" autocomplete="off" class="ac_input">
									<span class="input-group-addon"><i class="icon-search"></i></span>
								</div>
								<input type="hidden" name="reduction_product" id="reduction_product" {if $card != null AND isset($card.id_discount_product)}value="{$card.id_discount_product|escape:'htmlall':'UTF-8'}"{/if}>
								<input id="spy" type="hidden" value="{$link->getPageLink('search')|escape:'htmlall':'UTF-8'}" />
								<input id="lang_spy" type="hidden" value="{$id_lang|escape:'htmlall':'UTF-8'}" />
								<p class="preference_description help-block hint-block" style="padding-top:3px;">{l s='(Begin typing the first letters of the product name, then select the product from the drop-down list.)' mod='giftcard'}</p>
							</div>
						</div>
					</div>
				</div>

				<div class="clearfix"></div>
				<label class="form-group control-label col-lg-3 required">
				<span class="label-tooltip" data-toggle="tooltip" title="{l s='Discount will be given in selected currency' mod='giftcard'}">{l s='Discount currrency : ' mod='giftcard'}</span>
				</label>
				<div class="form-group margin-form ">
					<div class="col-lg-2">
						<select name="reduction_currency" >
						{foreach from=$currencies item='currency'}
							<option value="{$currency.id_currency|escape:'htmlall':'UTF-8'}" {if !empty($card) AND $card.reduction_currency == $currency.id_currency}selected="selected"{elseif $default_currency == $currency.id_currency}selected="selected"{/if}>{$currency.iso_code|escape:'htmlall':'UTF-8'}</option>
						{/foreach}
						</select>
					</div>
				</div>

				<div class="clearfix"></div>
				<label class="form-group control-label col-lg-3 required">
				<span class="label-tooltip" data-toggle="tooltip" title="{l s='Tax will be applied on this gift card' mod='giftcard'}">{l s='Tax : ' mod='giftcard'}</span>
				</label>
				<div class="form-group margin-form ">
					<div class="col-lg-3">
						<select name="reduction_tax">
							<option value="0" {if !empty($card) AND $card.reduction_tax == 0}selected="selected"{/if}>{l s='Tax excluded' mod='giftcard'}</option>
							<option value="1" {if !empty($card) AND $card.reduction_tax == 1}selected="selected"{/if}>{l s='Tax included' mod='giftcard'}</option>
						</select>
					</div>
				</div>

			<div class="clearfix"></div>
			<label class="form-group control-label col-lg-3 required">
				<span class="label-tooltip" data-toggle="tooltip" title="{l s='Specified length of code will be generated, default length is 14.' mod='giftcard'}">{l s='Coupon code Length : ' mod='giftcard'}</span>
			</label>
			<div class="form-group margin-form ">
				<div class="col-lg-9">
				<input id="length" type="text" name="length" style="width:10%;" {if $card != null AND isset($card.length)}value="{$card.length|escape:'htmlall':'UTF-8'}"{/if} placeholder="14"/>
				<p class="preference_description help-block hint-block" style="padding-top:3px;">{l s='Minimum length is 4 and maximum length is 20.' mod='giftcard'}</p>
			</div>
			</div>
			<div class="clearfix"></div>

			<!-- Vcode type -->
			<label class="form-group control-label col-lg-3">
				<span class="label-tooltip" data-toggle="tooltip" title="{l s='Coupon code type(i.e Numeric or Alphanumeric).' mod='giftcard'}">{l s='Coupon code Type : ' mod='giftcard'}</span>
			</label>
			<div class="form-group margin-form ">
				<div class="col-lg-9">	
					<input type="radio" value="NUMERIC" id="vcode_type_num" name="vcode_type" {if $card != null AND isset($card.vcode_type) AND $card.vcode_type == 'NUMERIC'}checked="checked"{/if}>
					<label for="vcode_type_num" class="t">
						<img style="cursor:pointer" title="{l s='Numeric' mod='giftcard'}" alt="{l s='Numeric' mod='giftcard'}" src="../img/admin/enabled.gif">{l s='Numeric (i.e 12345)' mod='giftcard'}</label>
					<input type="radio" value="ALPHANUMERIC" id="vcode_type_alphanum" name="vcode_type" {if $card != null AND $card.vcode_type == 'ALPHANUMERIC'}checked="checked"{/if}>
					<label for="vcode_type_alphanum" class="t">
						<img style="cursor:pointer" title="{l s='Alphanumeric' mod='giftcard'}" alt="{l s='Alphanumeric' mod='giftcard'}" src="../img/admin/enabled.gif">{l s='Alphanumeric  (i.e ABC123)' mod='giftcard'}</label>
				</div>
			</div>

			<div class="clearfix"></div>
				<label class="form-group control-label col-lg-3">{l s='Status : ' mod='giftcard'}</label>
			<div class="form-group margin-form ">
				<div class="col-lg-6">
					<span class="switch prestashop-switch fixed-width-lg">
						<input type="radio" name="status" id="status_on" value="1" {if isset($product) AND $product.active == 1}checked="checked"{/if}/>
						<label class="t" for="status_on">{if $version < 1.6}<img src="../img/admin/enabled.gif" alt="Enabled" title="Enabled" />{else}{l s='Yes' mod='giftcard'}{/if}</label>
						<input type="radio" name="status" id="status_off" value="0" {if isset($product) AND $product.active == 0}checked="checked"{/if}/>
						<label class="t" for="status_off">{if $version < 1.6}<img src="../img/admin/disabled.gif" alt="Disabled" title="Disabled" />{else}{l s='No' mod='giftcard'}{/if}</label>
						<a class="slide-button btn"></a>
					</span>
				</div>
			</div>

			<label class="form-group control-label col-lg-3">
				<span class="label-tooltip" data-toggle="tooltip" title="{l s='The default period is one month.' mod='giftcard'}">
					{l s='Valid : ' mod='giftcard'}
				</span>
			</label>
			<div class="form-group margin-form ">
				<div class="col-lg-9" style="display: inline-flex;">
					<div class="col-lg-4">
					 <div class="input-group">
						<span class="input-group-addon">{l s='From' mod='giftcard'}</span>
						<input type="text" class="datepicker input-medium" name="from" {if $card != null AND isset($card.from)}value = "{$card.from|escape:'htmlall':'UTF-8'}"{/if} value="" />
						<span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
					 </div>
					</div>
					<div class="col-lg-4">
					 <div class="input-group">
						<span class="input-group-addon">{l s='To' mod='giftcard'}</span>
						<input type="text" class="datepicker input-medium" name="to" {if $card != null AND isset($card.to)}value = "{$card.to|escape:'htmlall':'UTF-8'}"{/if} value="" />
						<span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
					</div>
					</div>
				</div>
			</div>

			<script type="text/javascript">
				$(document).ready(function(){

					$('.datepicker').datepicker({
						prevText: '',
						nextText: '',
						dateFormat: 'yy-mm-dd',

						// Define a custom regional settings in order to use PrestaShop translation tools
						currentText: '{l s='Now' mod='giftcard' js=1}',
						closeText: '{l s='Done' mod='giftcard' js=1}',
						ampm: false,
						amNames: ['AM', 'A'],
						pmNames: ['PM', 'P'],
						timeFormat: 'hh:mm:ss tt',
						timeSuffix: '',
						timeOnlyTitle: '{l s='Choose Time' mod='giftcard' js=1}',
						timeText: '{l s='Time' mod='giftcard' js=1}',
						hourText: '{l s='Hour' mod='giftcard' js=1}',
						minuteText: '{l s='Minute' mod='giftcard' js=1}',
					});
				});
			</script>

			<label class="control-label col-lg-3">{l s='Free shipping : ' mod='giftcard'}</label>
			<div class="form-group margin-form ">
				<div class="col-lg-6">
					<span class="switch prestashop-switch fixed-width-lg">
						<input type="radio" name="free_shipping" id="free_shipping_on" value="1" {if !empty($card) AND $card.free_shipping == 1}checked="checked"{/if}/>
						<label class="t" for="free_shipping_on">{if $version < 1.6}<img src="../img/admin/enabled.gif" alt="Enabled" title="Enabled" />{else}{l s='Yes' mod='giftcard'}{/if}</label>
						<input type="radio" name="free_shipping" id="free_shipping_off" value="0" {if !empty($card) AND $card.free_shipping == 0}checked="checked"{/if}/>
						<label class="t" for="free_shipping_off">{if $version < 1.6}<img src="../img/admin/disabled.gif" alt="Disabled" title="Disabled" />{else}{l s='No' mod='giftcard'}{/if}</label>
						<a class="slide-button btn"></a>
					</span>
				</div>
			</div>
	<div class="clearfix"></div>
</div><div class="clearfix"></div>