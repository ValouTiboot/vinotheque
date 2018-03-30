<form id="add-voucher" action="{$urls.pages.cart}" data-link-action="add-voucher" method="post">
	<input type="hidden" name="token" value="{$static_token}">
	<input type="hidden" name="addDiscount" value="1">
	<div class="form-group row ">
		<label class="col-md-4 form-control-label required">
			{l s='Have you got any gift card ?' mod='giftcard'}
		</label>

		<div id="add-voucher-input" class="col-md-4">
			<input class="form-control" type="text" name="discount_name" placeholder="{l s='Gift card' mod='giftcard'}">
		</div>

		<div class="col-md-2">
			<button class="btn btn-secondary" type="submit"><span>{l s='Add' mod='giftcard'}</span></button>
		</div>
	</div>
	<div class="notification notification-error js-error">
		<div class="js-error-text"></div>
	</div>
</form>
