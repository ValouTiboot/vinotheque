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
<legend class="panel-heading"><img src="{$smarty.const.__PS_BASE_URI__|escape:'htmlall':'UTF-8'}modules/giftcard/views/img/crd.png" alt="">{if $Gift != NULL }{l s=' Edit Gift Card' mod='giftcard'}{else}{l s=' Add Gift Card' mod='giftcard'}{/if}</legend>
<div class="col-lg-2" id="gift-card">
 	<div class="productTabs">
		<ul class="tab">
			<li class="tab-row">
				<a class="gift_card_page selected" id="giftcard_link_fonts" href="javascript:displayDesignTab('fonts');">{l s='Gift Settings' mod='giftcard'}</a>
			</li>
			<li class="tab-row">
				<a class="gift_card_page" id="giftcard_link_color" href="javascript:displayDesignTab('color');">{l s='Coupon Settings' mod='giftcard'}</a>
			</li>
			{if isset($shops) AND $shops}
				<li class="tab-row">
					<a class="gift_card_page" id="giftcard_link_shops" href="javascript:displayDesignTab('shops');">
						{l s='Shops' mod='giftcard'}
					</a>
				</li>
			{/if}
		</ul>
	</div>
</div>