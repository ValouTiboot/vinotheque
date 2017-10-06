{*
* @version 1.0
* @author 202-ecommerce
* @copyright 2014-2015 202-ecommerce
* @license ?
*}
<div class="coupon_div_outer">
<p class="block-promo promo-highlighted" id="title_div">
    Profitez de nos offres exclusives :
</p>
<ul class="js-discount card-block promo-discounts" id="align_ul">
{foreach from=$discountsCustom item=discount name=myLoop}
{if $discount->orders}
	<li class="cart-summary-line">
	 <span class="label">
	   <span class="code">{$discount->code|escape:'html':'UTF-8'}</span>
	   {if $discount->reduction_percent > 0}
         {$discount->reduction_percent|escape:'html':'UTF-8'}%
       {elseif $discount->reduction_amount}
         {$discount->reduction_amount|escape:'htmlall':'UTF-8'}
       {else}
         {l s='Free shipping' mod='totloyaltyadvanced'}
       {/if}
	</li>
{/if}
{/foreach}
<div class="coupon_div">
</ul>
<script  src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script type="text/javascript">
jQuery(document).on('click','#promo-code .btn-primary',function(){
    location.reload();
});
jQuery(document).on('click','.promo-name .cart-summary-line a',function(){
    location.reload();
});
</script>