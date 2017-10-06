{*
* @version 1.0
* @author 202-ecommerce
* @copyright 2014-2015 202-ecommerce
* @license ?
*} 
<div class="panel product-tab custom-class-edit" style="padding:15px;">
	<h4>{$name|escape:'html':'UTF-8'}</h4>
	<div class="separation"></div>
	<div class="hint" style="position: auto;display: block;">
		{l s='You can use two types of syntax:' mod='totloyaltyadvanced'}
		<ul style="list-style-type:initial;padding-left: 1.7em;">
			<li>{l s='"x2" : multiplier of points' mod='totloyaltyadvanced'}</li>
			<li>{l s='"31" : number of points' mod='totloyaltyadvanced'}</li>
		</ul>
	</div>
	<table>
		<tbody>
			<tr>
				<td valign="top"><label for="loyalty" style="width:300px;">{l s='If you want the special loyalty for this product :' mod='totloyaltyadvanced'}</label></td>
				<td>
					<input type="hidden" name="loyalty_filled" value="1">
					<input type="text" name="loyalty" id="loyalty" value="{$loyalty->loyalty|escape:'html':'UTF-8'}" >
					<p class="preference_description">{l s='Leave empty for default value' mod='totloyaltyadvanced'}.</p>
				</td>
			</tr>
			<tr>
				<td valign="top"><label for="date_begin" style="width:300px;">{l s='Temporary campaign:' mod='totloyaltyadvanced'}</label></td>
				<td>
					<input type="date" name="date_begin" id="date_begin" value="{$loyalty->date_begin|escape:'html':'UTF-8'}"> {l s='to' mod='totloyaltyadvanced'} <input type="date" name="date_finish" id="date_finish" value="{$loyalty->date_finish|escape:'html':'UTF-8'}">
					<p class="preference_description">{l s='No fill fields to infinity period' mod='totloyaltyadvanced'}.</p>
				</td>
			</tr>
		</tbody>
	</table>
	{if version_compare($smarty.const._PS_VERSION_, '1.6', '>')}
	<div class="panel-footer" style="padding-bottom: 15px;">
		<button type="submit" name="submitAddproduct" class="btn btn-default pull-right"><i class="process-icon-save"></i>{l s='Save' mod='totloyaltyadvanced'}</button>
		
	</div>
	{/if}
</div>
