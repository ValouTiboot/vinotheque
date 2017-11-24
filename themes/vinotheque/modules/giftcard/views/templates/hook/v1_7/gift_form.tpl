<form action="{$urls.pages.cart}" data-link-action="add-voucher" method="post">
	<input type="hidden" name="token" value="{$static_token}">
	<input type="hidden" name="addDiscount" value="1">
	<label>{l s='Have you got any gift card ?' mod='giftcard'}</label>
	<input class="form-control" type="text" name="discount_name" placeholder="{l s='Gift card' mod='giftcard'}">
	<button class="btn btn-secondary" type="submit"><span>{l s='Add' mod='giftcard'}</span></button>
</form>