{*
* @version 1.0
* @author 202-ecommerce
* @copyright 2014-2015 202-ecommerce
* @license ?
*}
<script type="text/javascript">
jQuery(document).on('click','#promo-code .btn-primary',function(){
    location.reload();
});
jQuery(document).on('click','.promo-name .cart-summary-line a',function(){
    location.reload();
});
jQuery(document).ready(function(){
  var pro = $('.cart_navigation_extra .coupon_div_outer').html();
  $('#cart_voucher #voucher').append(pro);
});
</script>
<div class="coupon_div_outer" id="cart_total_price">
    <div class="cart_voucher">
        <p class="block-promo promo-highlighted" id="title_div">
            <strong>Profitez de nos offres exclusives :</strong>
        </p>
        <div class="js-discount card-block promo-discounts" id="display_cart_vouchers">
            {foreach $discountsCustom as $k=>$discount}
                {if $discount->orders}
                    <span class="code voucher_name" data-code="{$discount->code|escape:'html':'UTF-8'}">
                        {$discount->code|escape:'html':'UTF-8'}
                    </span>
                    {if $discount->reduction_percent > 0}
                        {$discount->reduction_percent|escape:'html':'UTF-8'}%
                    {elseif $discount->reduction_amount}
                        {$discount->reduction_amount|escape:'htmlall':'UTF-8'}
                    {else}
                        {l s='Free shipping' mod='totloyaltyadvanced'}
                    {/if} <br />
                {/if}
            {/foreach}
        </div>
    </div>
</div>