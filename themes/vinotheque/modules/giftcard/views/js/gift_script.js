/*
* DISCLAIMER
*
* Do not edit, add or redistribute this file without permissions.
*
*  @author    FMM Modules
*  @copyright 2017 FMM Modules
*  @version   1.4.0
*/
$('document').ready( function()
{
	if (typeof giftType !== 'undefined' && giftType)
	{
		$('#product-description-short').html($('#product-description').html());

		//***************** check gift car type on product page.
		$('.add-to-cart').hide();
		var price_elem = $('#gift-card-wrapper');
		if (price_elem.length)
		{
			if (giftType == 'dropdown')
			{
				price_elem.hide().after(
					$('#dropdown_price').show()
				);
			}
			else if (giftType == 'fixed')
			{
				$('.product-prices').show();
			}
			else if (giftType == 'range')
			{
				$('#range_price').show().appendTo(price_elem);
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
		}

		if (giftType == 'fixed')
			$('.product-prices').show();
		else
			$('.product-prices').hide();

		$('#' + giftType + '_price').prependTo('#add-to-cart-or-refresh');

		prestashop.on('updateProduct', function(e)
		{
			_getGiftPrice(giftType, parseFloat($('#gift_card_price').val()));
		});

		_validatePrice(parseFloat($('#gift_card_price').val()), giftType);
	}


	// ************** find gift products on home and category pages.
	var subURL = base_url + "?fc=module&module=giftcard&controller=ajax";
	$('.product-miniature').map(function()
	{
		var thisActiveBlock = $(this);
		pid = $(this).data('id-product');
		$.ajax({
				type		: "POST",
				cache		: false,
				dataType 	: "json",
				url			: subURL,
				data		:
				{
					action 		: 'ProductExists',
					id_product	: pid
				},

				success	: function(data)
				{
					var pid = parseInt(data);
					if(pid > 0)
						thisActiveBlock.find('.price').hide();
				},

				error : function(XMLHttpRequest, textStatus, errorThrown)
				{
					console.log(errorThrown);
				}
			});
	  });


});

$(document).on('click', '.bootstrap-touchspin-up, .bootstrap-touchspin-down', function(e)
{
	if (typeof giftType !== 'undefined' && giftType)
		_getGiftPrice(giftType, parseFloat($('#gift_card_price').val()))
});

$(document).on('click', '.quick-view', function(e)
{
	var pid = $(this).closest('.js-product-miniature').data('id-product');
	var _ctype = _getCtype(pid);
	if (typeof _ctype !== 'undefined' && _ctype)
		_getGiftPrice(_ctype, parseFloat($('#gift_card_price').val()))
});

function _validatePrice(val, giftType)
{
	val = parseFloat(val);
	$('.add-to-cart').show();
	$('#price_error').hide();
	$('#gift_card_price').attr('value', val);
	if (isNaN(val))
	{
		$('#gift_card_price').val('');
		$('#price_error').show();
		$('.add-to-cart').hide();
		$('#gift_card_price').focus();
	}

	if (giftType == 'range')
	{
		$('.add-to-cart').show();
		var min = parseFloat($('#range_min').val());
		var max = parseFloat($('#range_max').val());
		if (!val || (val < min) || (val > max))
		{
			$('#gift_card_price').focus();
			$('#price_error').show();
			$('.add-to-cart').hide();
		}
	}
}

function _getGiftPrice(_ctype, _sprice)
{
	if (_ctype != 'fixed')
	{
		setTimeout(function()
		{
			$.post(ajax_URL , { ajax: '1', action: 'get_gift_price', id_product: pid, card_type: _ctype, current_price: _sprice }, null, 'json')
			.then(function (resp)
			{
				$('.gift_card').remove();
				$('.product-prices').hide();
				$('#add-to-cart-or-refresh').prepend(resp.gift_prices);

			});
		}, 500);
	}
}

function _getCtype(pid)
{
	var cType = '';
	$.ajax({
			type		: "GET",
			cache		: false,
			async 		: false,
			dataType 	: "json",
			url			: base_url + "?fc=module&module=giftcard&controller=ajax",
			data		:
			{
				action 		: 'get_gift_type',
				id_product	: pid
			},

			success	: function(resp)
			{
				if(resp && resp.gift_type)
					cType = resp.gift_type;
			},
		});
	return cType;
}