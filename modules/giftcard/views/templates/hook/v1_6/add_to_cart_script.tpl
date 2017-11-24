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
{literal}
<script type="text/javascript">
$(document).ready(function()
{
		var addToCartEvent = true;
		var price_elem = $('#our_price_display');
		if (giftType == 'dropdown')
			_triggerAddToCart();
		if (price_elem.length)
		{
			price_elem.attr('id', 'our_price_display_old');

			if (giftType == 'dropdown')
			{
				price_elem.hide().after(
					$('#dropdown_price').show()
				);
			}
			else if (giftType == 'fixed')
			{
				$('#our_price_display_old').show();
			}
			else if (giftType == 'range')
			{
				price_elem.hide().after(
				$('#range_price').show()
				);
			}
			$('#gift_card_price').keydown(function(e)
			{
				var key = e.charCode || e.keyCode || 0;
				return (key == 8 || 
						key == 9 ||
						key == 46 ||
						key == 110 ||
						key == 190 ||
						(key >= 35 && key <= 40) ||
						(key >= 48 && key <= 57) ||
						(key >= 96 && key <= 105));
			});

			$('#gift_card_price').before('<span class="giftcard_custom_price" style="font-size:16px;">'+ price_label +'</span>');
			button.mousedown(function()
			{
				$('#price_error').hide();
				priceValidate = true;
				var val = parseFloat($('#gift_card_price').val());
				$('#gift_card_price').attr('value', val);
				if (isNaN(val))
				{
					//alert('Invalid amount!');
					$('#gift_card_price').val('');
					$('#price_error').show();
					$('#gift_card_price').focus();
					priceValidate = false;
					return false;
				}

				if (giftType == 'range')
				{
					console.log(1111)
					var min = $('#range_min').val();
					var max = $('#range_max').val();
					if (val < min || val == 0)
					{
						$('#gift_card_price').focus();
						$('#price_error').show();
						priceValidate = false;
						return false;
					}

					if (val > max || val == 0)
					{
						$('#gift_card_price').focus();
						$('#price_error').show();
						priceValidate = false;
						return false;
					}
				}
				button.trigger('click');
			});
		}
		if (giftType == 'range')
			_triggerAddToCart();
		$('#old_price, #reduction_percent, #reduction_amount').hide();


});

function _triggerAddToCart()
{
		button.mousedown(function(e)
		{
			console.log(222)
			button.unbind('click');
			e.stopImmediatePropagation();
			button.click(function(event)
			{
				console.log(333)
				//event.stopImmediatePropagation();
				if (priceValidate && giftValidate)
				{
						var quantity = $('#quantity_wanted').val();
						//ajaxCart.add(pid, null, true, null, ((quantity && quantity != null) ? quantity : 1), null);

						var giftPrice = $('#gift_card_price').val();
						$.ajax({
							type		: "POST",
							headers 	: { "cache-control": "no-cache" },
							cache		: false,
							async		: false,
							dataType	: "json",
							url			: baseUri + '?rand=' + new Date().getTime(),
							data		: 'controller=cart&add=1&ajax=true&qty='
										+ ((quantity && quantity != null) ? quantity : 1)
										+ '&id_product='+ pid
										+ '&token='+ token
										+ '&ipa=0'
										+ '&allow_refresh=1&giftcard_price='+ giftPrice,
							success: function(jsonData,textStatus,jqXHR)
							{
								ajaxCart.updateCartEverywhere(jsonData);
								var idProduct = pid;
								var idCombination =$('#idCombination').val();
								var callerElement = null;
								var quantity = $('#quantity_wanted').val();
								var whishlist = null;

								if (whishlist && !jsonData.errors)
									WishlistAddProductCart(whishlist[0], idProduct, idCombination, whishlist[1]);

								// add the picture to the cart
								var $element = $(callerElement).parent().parent().find('a.product_image img,a.product_img_link img');
								if (!$element.length)
									$element = $('#bigpic');
								var $picture = $element.clone();
								var pictureOffsetOriginal = $element.offset();

								if ($picture.size() || $picture.length)
									$picture.css({'position': 'absolute', 'top': pictureOffsetOriginal.top, 'left': pictureOffsetOriginal.left});

								var pictureOffset = $picture.offset();
								if ($('#cart_block, .cart_block').offset.top && $('#cart_block, .cart_block').offset.left)
									var cartBlockOffset = $('#cart_block, .cart_block').offset();
								else
									var cartBlockOffset = $('#shopping_cart').offset();

								// Check if the block cart is activated for the animation
								if (cartBlockOffset != undefined && ($picture.size() || $picture.length))
								{
									$picture.appendTo('body');
									$picture.css({ 'position': 'absolute', 'top': $picture.css('top'), 'left': $picture.css('left'), 'z-index': 4242 })
									.animate({ 'width': $element.attr('width')*0.66, 'height': $element.attr('height')*0.66, 'opacity': 0.2, 'top': cartBlockOffset.top + 30, 'left': cartBlockOffset.left + 15 }, 1000)
									.fadeOut(100, function() {
										ajaxCart.updateCartInformation(jsonData, addedFromProductPage);
										$(this).remove();
									});
								}
								else
									ajaxCart.updateCartInformation(jsonData, addedFromProductPage);

								//window.location = redirect;
							},
							error: function(XMLHttpRequest, textStatus, errorThrown)
							{
								alert("Impossible to add the product to the cart.\n\ntextStatus: '" + textStatus + "'\nerrorThrown: '" + errorThrown + "'\nresponseText:\n" + XMLHttpRequest.responseText);
								//reactive the button when adding has finished
								if (addedFromProductPage)
									$('body#product p#add_to_cart input').removeAttr('disabled').addClass('exclusive').removeClass('exclusive_disabled');
								else
									$(callerElement).removeAttr('disabled');
							}
						});

				}
				return false;
			});
			isClicked = true;
			$.fancybox.close();

		});
}
</script>
{/literal}