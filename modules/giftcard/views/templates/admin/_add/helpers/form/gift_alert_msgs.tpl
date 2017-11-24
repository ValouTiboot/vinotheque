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
<div {if $msg == 0} style="display:none;"{/if}>
	{if $msg == 1}
		<div class="conf alert alert-success">{l s='Gift Card saved successfully' mod='giftcard' }</div>
	{elseif $msg == 2}
		<div class="conf alert alert-success">{l s='Settings Updated successfully' mod='giftcard' }</div>	
	{elseif $msg == 3 }
		<div class="conf error alert alert-danger">{l s='Invalid date' mod='giftcard' }</div>
	{elseif $msg == 4 }
		<div class="conf error alert alert-danger">{l s='Invalid discount amount/percentage' mod='giftcard' }</div>
	{elseif $msg == 5}	
		<div class="conf error alert alert-danger">{l s='Specific a discount product' mod='giftcard' }</div>
	{elseif $msg == 6}	
		<div class="conf error alert alert-danger">{l s='Invalid Gift card value' mod='giftcard' }</div>
	{/if}
</div>