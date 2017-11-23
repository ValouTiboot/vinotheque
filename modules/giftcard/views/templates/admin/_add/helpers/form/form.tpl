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

{include file='./gift_script.tpl'}

{block name="input"}
	<!-- <div class="panel clearfix"> -->

{include file='./gift_alert_msgs.tpl'}
<div class="well">
	<fieldset>
		{include file='./gift_tabs.tpl'}
	    <!-- Tab Content -->
		<div class="col-lg-10 panel">
		{foreach from=$Gift item=card}
			<form action="{$currentIndex|escape:'htmlall':'UTF-8'}&token={$currentToken|escape:'htmlall':'UTF-8'}" class="defaultForm form-horizontal col-lg-12 clearfix" name="giftcards_request_form" id="giftcard_request_form" method="post" enctype="multipart/form-data">
				<!-- Gift Product -->
				{include file='./gift_product.tpl'}

				<!-- Gift Coupon -->
				{include file='./gift_coupon.tpl'}

				<!-- Shops -->
				{include file='./gift_shops.tpl'}

				<!-- footer buttons -->
				{include file="./gift_footer.tpl"}
			</form>
		{/foreach}
		</div>
		<div class="clearfix"></div>
	</fieldset>
</div>
<div class="clearfix"></div>

{include file="./backoffice_css.tpl"}
{/block}