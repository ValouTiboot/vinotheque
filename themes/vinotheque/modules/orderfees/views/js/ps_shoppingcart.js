/**
 *  Order Fees
 *
 *  @author    motionSeed <ecommerce@motionseed.com>
 *  @copyright 2017 motionSeed. All rights reserved.
 *  @license   https://www.motionseed.com/en/license-module.html
 */

/* global prestashop */
(function($) {
    $(function() {
        $('html').on('change', 'input[name="fees[]"]', function() {
            var refreshURL = $('#fees-url').val();
            var requestData = {
                action: 'update'
            };
            
            requestData[($(this).prop('checked') ? 'option_add' : 'option_remove')] = $(this).val();
            
            $.post(refreshURL, requestData).then(function (resp) {
                prestashop.emit('updateCart');
                $('#fees_cart_button').html($('input[name="fees[]"]').prop('checked') ? fee_delete : fee_add)
            }).fail(function (resp) {
                prestashop.emit('handleError', {eventType: 'updateCart', resp: resp});
            });
        });

        prestashop.on('updateCart', function(){
            $.get(fes_ajax_url, {ajax: true}, function(data){$('#cart_fees').replaceWith(data.resp)}, 'json');
        });

    });
})(jQuery);
