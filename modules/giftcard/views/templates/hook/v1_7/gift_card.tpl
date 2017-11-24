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
<link rel="stylesheet" type="text/css" href="{$smarty.const.__PS_BASE_URI__|escape:'htmlall':'UTF-8'}modules/giftcard/views/css/gift_card.css">
<script type="text/javascript">
var giftType 	= "{$type|escape:'htmlall':'UTF-8'}";
var pid 		= parseInt("{$pid|escape:'htmlall':'UTF-8'}");
var prices_tax	= {$prices_tax[0]};
</script>
{if count($values) > 0}
<div id="gift-card-wrapper" class="product-variants" style="display:none">
	{if $type == 'dropdown'}
		{include file='./dropdown.tpl'}
	{elseif $type == 'range'}
		{include file='./range.tpl'}
	{/if}
</div>
{/if}
