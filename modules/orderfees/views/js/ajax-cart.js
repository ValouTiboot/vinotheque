/**
 *  Order Fees
 *
 *  @author    motionSeed <ecommerce@motionseed.com>
 *  @copyright 2017 motionSeed. All rights reserved.
 *  @license   https://www.motionseed.com/en/license-module.html
 */

/* global ajaxCart */
/* global priceDisplayMethod */
/* global baseUri */
/* global static_token */
/* global updateCartSummary */
(function($) {
    $(function() {
        var refreshVouchersOriginal = ajaxCart.refreshVouchers;

        ajaxCart.refreshVouchers = function(jsonData) {
            refreshVouchersOriginal(jsonData);

            if (typeof (jsonData.discounts) !== 'undefined' && jsonData.discounts.length > 0) {
                $('#ajax_block_fees_overlay').empty();
                $('#block_cart_fees .bloc_cart_voucher.is_fee').each(function(){$(this).remove();});
                
                for (i = 0; i < jsonData.discounts.length; i++) {
                    if (jsonData.discounts[i].is_fee > 0) {        
                        var id_element = jsonData.discounts[i].id;
                        
                        if (!$('#fees_blockcart_' + id_element).length) {
                            $('#block_cart_fees').append($(
                                    '<tr class="bloc_cart_voucher is_fee" id="bloc_cart_voucher_' + jsonData.discounts[i].id + '">'
                                    + '<td></td>'
                                    + '<td class="quantity">' + jsonData.discounts[i].quantity + 'x</td>'
                                    + '<td class="name" title="' + jsonData.discounts[i].description + '">' + jsonData.discounts[i].name + '</td>'
                                    + '<td class="price">' + (parseFloat(jsonData.discounts[i].price_float) <= 0 ? jsonData.discounts[i].price.replace('-', '') : '-' + jsonData.discounts[i].price)  + '</td>'
                                    + '<td class="delete"></td>'
                                    + '</tr>'
                                    ));

                            // Add fees on overlay
                            $('#ajax_block_fees_overlay').append($(
                                    '<div class="layer_cart_row">'
                                    + '<strong class="dark">' + jsonData.discounts[i].name + ' </strong>'
                                    + '<span class="ajax_block_fees">' + jsonData.discounts[i].price.replace('-', '') + '</span>'
                                    + '</div>'
                                    ));
                        }
                        
                        // Update quantity
                        if ($('#cart_option_' + id_element).length) {
                            $('#cart_option_' + id_element + ' td.cart_discount_price span.price.price-discount').html(jsonData.discounts[i].price.replace('-', ''));
                            
                            $('#cart_option_' + id_element + ' td.cart_discount_delete').html(jsonData.discounts[i].quantity);
                            $('#bloc_cart_voucher_' + id_element + ' .quantity').html(jsonData.discounts[i].quantity + 'x');
                        } else {
                            if (jsonData.discounts[i].unit_value_real !== '!') {
                                $('#cart_discount_' + id_element + ' td.cart_discount_price span:not(.price).price-discount').html(jsonData.discounts[i].unit_price.replace('-', ''));
                            }

                            $('#cart_discount_' + id_element + ' td.cart_discount_delete').html(jsonData.discounts[i].quantity);
                        }
                    }
                }

                $('.vouchers').show();
            }
        };
        
        var updatePayments = function(summary) {
            // GGBank
            $('#ggbank_form input[name=amt]').val(summary.total_price);
        };
        
        $('html').on('change', 'input[name="fees[]"]', function() {
            var param = ($(this).prop('checked') ? 'option_add' : 'option_remove') + '=' + $(this).val();
            
            var options_cb = $("input[name='fees[]'][value='" + $(this).val() + "']").prop('checked', $(this).prop('checked'));
            
            if (typeof($.uniform) !== 'undefined') {
                $.uniform.update(options_cb);
            }
            
            $.ajax({
                    type: 'POST',
                    headers: { "cache-control": "no-cache" },
                    url: baseUri + '?rand=' + new Date().getTime(),
                    async: true,
                    cache: false,
                    dataType : "json",
                    data: 'controller=cart&ajax=true&delete=-1&summary=true&' + param + '&token=' + static_token,
                    success: function(jsonData) {
                            if (typeof(updateCartSummary) !== 'undefined') {
                                // Inject a fake discount object to avoid page reload
                                if (!jsonData.summary.discounts.length) {
                                    jsonData.summary.discounts = [{id_discount: -1}];
                                }

                                updateCartSummary(jsonData.summary);
                            }
                            
                            ajaxCart.updateCart(jsonData);
                            
                            updatePayments(jsonData.summary);
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                            alert("TECHNICAL ERROR: \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);
                    }
            });
        });
    });
})(jQuery);
