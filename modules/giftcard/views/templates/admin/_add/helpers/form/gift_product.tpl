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

<div id="giftcard_fonts" class="gift_card tab-pane">
<h3 class="tab"><i class="icon-gift"></i> {l s='Gift Product' mod='giftcard'}</h3><div class="separation"></div>	
<label class="control-label col-lg-3 required">
	<span class="label-tooltip" data-toggle="tooltip" title="{l s='This name will be displayed in the cart summary, as well as on the invoice.' mod='giftcard'}">{l s='Gift Card Name : ' mod='giftcard'}</span>
</label>
<div class="form-group margin-form col-lg-9">
	<div class="col-lg-8">
	{assign var=divLangName value='cpara&curren;dd'}
	{foreach from=$languages item=language}
	<div class="lang_{$language.id_lang|escape:'htmlall':'UTF-8'} col-lg-9" id="cpara_{$language.id_lang|escape:'htmlall':'UTF-8'}" style="display: {if $language.id_lang == $current_lang} block{else}none{/if};float: left;">

		<input id="name_{$language.id_lang|escape:'htmlall':'UTF-8'}" type="text" name="card_name_{$language.id_lang|escape:'htmlall':'UTF-8'}" {if $product != null AND isset($product.name)}value="{$product.name[$language.id_lang]|escape:'htmlall':'UTF-8'}"{/if} value=""/>

	</div>
	{/foreach}
	<div class="col-lg-2">{$module->displayFlags($languages, $current_lang, $divLangName, 'cpara', true)}{* html code *}</div>
	</div>
	<p class="col-lg-12 preference_description help-block hint-block" style="padding-top:3px;">{l s='Your Product name will be considered as your Gift card name.' mod='giftcard'}</p>
</div><div class="clearfix"></div>

<label class="form-group control-label col-lg-3">
    <span data-html="true" data-original-title="{l s='The description will be displayed on gift card product detail page.' mod='giftcard'}" class="label-tooltip" data-toggle="tooltip" title="">{l s='Description' mod='giftcard'}</span>
</label>
<div class="col-lg-8 form-group margin-form">
    <div class="col-lg-12">
        {include
        file="./textarea_lang.tpl"
        languages=$languages
        input_name='product_description'
        class="autoload_rte"
        input_value=$product.description}
    </div>
    <div class="clearfix"></div>
</div>

<label class="control-label col-lg-3">
	<span class="label-tooltip" data-toggle="tooltip">{l s='Upload image : ' mod='giftcard'}</span>
</label>
<div class="form-group margin-form">
	<div class="col-lg-6">
		<input id="gift-image" class="btn btn-default" type="file" name="giftimage" value=""/>
		<p class="preference_description help-block hint-block" style="padding-top:3px;">{l s='Format: JPG, GIF, PNG. Filesize: 8.00 MB max.' mod='giftcard'}</p>
	</div>
</div>

<div id="preview" class="form-group" style="display:none;">
	<label class="control-label col-lg-3">
		<span class="label-tooltip" data-toggle="tooltip">{l s='Preview : ' mod='giftcard'}</span>
	</label>
	<div class="col-lg-7">
		<img id="image-thumb" src="" class="img img-thumbnail" width="300">
	</div>
</div>
{if isset($product) AND isset($product.id_cover) AND $product.id_cover}
<div id="product-image" class="form-group col-lg-push-3 col-lg-7 margin-form">
	<img id="image-thumb" src="{$link->getImageLink($product.link_rewrite[$id_lang], $product.id_cover, 'home_default')|escape:'htmlall':'UTF-8'}" class="img img-thumbnail" width="300">
</div><div class="clearfix"></div>
{/if}

<label class="form-group control-label col-lg-3 required">
	<span class="label-tooltip" data-toggle="tooltip" title="{l s='specify the number of gift card instances.' mod='giftcard'}">{l s='Card Quantity : ' mod='giftcard'}</span>
</label>
<div class="form-group margin-form">
<div class="col-lg-2">
	<input id="qty" type="text" name='qty' {if $product AND isset($product.quantity)}value="{$product.quantity|escape:'htmlall':'UTF-8'}"{/if}/>
</div>
</div><div class="clearfix"></div>
<label class="form-group control-label col-lg-3">
	<span class="label-tooltip" data-toggle="tooltip" title="{l s='Select type of price for your gift card.' mod='giftcard'}">{l s='Price Type : ' mod='giftcard'}</span>
</label>
<div class="form-group margin-form ">
	<div class="col-lg-9">
		<input type="radio" value="dropdown" id="dropdown" name="value_type" {if $card != null AND isset($card.value_type) AND $card.value_type == 'dropdown'}checked="checked"{/if}/>
		<label for="dropdown" class="t">{l s='Dropdown' mod='giftcard'} </label>

		<input type="radio" value="fixed" id="fixed" name="value_type" {if $card != null AND isset($card.value_type) AND $card.value_type == 'fixed' OR $card == null}checked="checked"{/if}/>
		<label for="fixed" class="t">{l s='Fixed' mod='giftcard'} </label>

		<input type="radio" value="range" id="range" name="value_type" {if $card != null AND isset($card.value_type) AND $card.value_type == 'range'}checked="checked"{/if} />
		<label for="range" class="t">{l s='Range' mod='giftcard'} </label>
	</div>
</div>
<label class="form-group control-label col-lg-3"><span class="label-tooltip" data-toggle="tooltip" title="{l s='Set price for your gift card.' mod='giftcard'}">{l s='Card Price : ' mod='giftcard'}</span></label>
<div >
	<div class="col-lg-6">
		<input id="card_val" type="text" name="card_value" {if $card != null AND isset($card.card_value) AND ($card.value_type == 'dropdown' OR $card.value_type == 'fixed')}value="{$card.card_value|escape:'htmlall':'UTF-8'}"{elseif isset($card.card_value) AND ($card.value_type == 'range')}style="display:none;"{/if} value=""/>
		<div id="dropdown_div" class="form-group margin-form "{if $card != null AND isset($card.value_type) AND $card.value_type == 'dropdown'}style="display:block"{/if}style="display:none">
			<p class="preference_description help-block hint-block" style="padding-top:3px;">{l s='Example: 10,50,100,200 (use comma (,) as a separater to make your dropdown list.)' mod='giftcard'}</p>
		</div>
		<div id="fixed_div" class="form-group margin-form " {if $card == null OR (isset($card.value_type) AND $card.value_type == 'fixed')}style="display:block"{/if}style="display:none">
			<p class="preference_description help-block hint-block" style="padding-top:3px;">{l s='Example: 100 (enter single numeric value.)' mod='giftcard'}</p>
		</div>
		<div id="range_div" class="form-group margin-form " {if $card != null AND !empty($card.card_value) AND $card.value_type == 'range'}style="display:inline-flex;margin-top:-5px;"{/if} style="display:none;">
			{if $card != null AND !empty($card.card_value) AND $card.value_type == 'range'}
				{assign var=vals value=","|explode:$card.card_value}
			{/if}
			<div class="col-lg-6">
				<div class="input-group">
					<span class="input-group-addon">{l s='Min' mod='giftcard'}</span>
					<input type="text" id="range_val" name="min" {if $card != null AND !empty($card.card_value) AND $card.value_type == 'range'}value="{$vals[0]|escape:'htmlall':'UTF-8'}"{/if}/>
				</div>
			</div>
			<div class="col-lg-6">
				<div class="input-group">
					<span class="input-group-addon">{l s='Max' mod='giftcard'}</span>
					<input type="text" id="range_val" name="max"{if $card != null AND !empty($card.card_value) AND $card.value_type == 'range'}value="{$vals[1]|escape:'htmlall':'UTF-8'}"{/if}/>
				</div>
			</div>
			<p class="preference_description help-block hint-block" style="padding-top:-5px;">{l s='Select min and max values for your gift card.' mod='giftcard'}</p>
		</div>
	</div>
</div><div class="clearfix"></div>

<label class="col-lg-3 control-label form-group">{l s='Tax rule' mod='giftcard'}</label>
<div class="col-lg-6">
	<select name="id_tax_rules_group" id="id_tax_rules_group" {if $tax_exclude_taxe_option}disabled="disabled"{/if} >
		<option value="0">{l s='No Tax' mod='giftcard'}</option>
		{foreach from=$tax_rules_groups item=tax_rules_group}
			<option value="{$tax_rules_group.id_tax_rules_group|escape:'htmlall':'UTF-8'}" {if isset($product) AND $product AND isset($product.id_tax_rules_group) AND $product.id_tax_rules_group == $tax_rules_group.id_tax_rules_group}selected="selected"{/if} >
				{$tax_rules_group['name']|htmlentitiesUTF8|escape:'htmlall':'UTF-8'}
			</option>
		{/foreach}
	</select>
</div>
</div><div class="clearfix"></div>