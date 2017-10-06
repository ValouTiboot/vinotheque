{*
* Order Fees
*
* @author    motionSeed <ecommerce@motionseed.com>
* @copyright 2017 motionSeed. All rights reserved.
* @license   https://www.motionseed.com/en/license-module.html
*}
<div class="panel" id="fees_part" style="display:none;">
    <div class="panel-heading">
        <i class="icon-folder-open-alt"></i>
        {l s='Fees and Reductions' mod='orderfees'}
    </div>
    <div class="form-group">
        <label class="control-label col-lg-3">
            {l s='Search for a fee or reduction' mod='orderfees'}
        </label>
        <div class="col-lg-9">
            <div class="row">
                <div class="col-lg-6">
                    <div class="input-group">
                        <input type="text" id="fees_search" value="" />
                        <div class="input-group-addon">
                            <i class="icon-search"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <span class="form-control-static">{l s='Or' mod='orderfees'}&nbsp;</span>
                    <a class="fancybox btn btn-default" href="{$link->getAdminLink('AdminOrderFees')|escape:'html':'UTF-8'}&amp;addcart_rule&amp;liteDisplaying=1&amp;submitFormAjax=1#">
                        <i class="icon-plus-sign-alt"></i>
                        {l s='Add new fee or reduction' mod='orderfees'}
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <table class="table" id="fees_list">
            <thead>
                <tr>
                    <th><span class="title_box">{l s='Name' mod='orderfees'}</span></th>
                    <th><span class="title_box">{l s='Description' mod='orderfees'}</span></th>
                    <th><span class="title_box">{l s='Value' mod='orderfees'}</span></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
    <div id="fees_err" class="alert alert-warning" style="display:none;"></div>
</div>
                    
<script id="fee_tpl" type="text/template">
    <tr>
        <td>[name]</td>
        <td>[description]</td>
        <td>[value]</td>
        <td class="text-right"><a href="#" class="btn btn-default delete_discount" rel="[id_discount]"><i class="icon-remove text-danger"></i>&nbsp;{l s='Delete' mod='orderfees'}</a></td></tr>
    </tr>
</script>
                    
<script type="text/javascript">
    (function($) {
        $(function() {
            var template = function(tpl, data) {
                return tpl.replace(/\[([^\]]+)?\]/g, function($1, $2) { return data[$2]; });
            }
            
            var setupCustomerOriginal = setupCustomer;

            setupCustomer = function(idCustomer) {
                $('#fees_part').show();
                
                setupCustomerOriginal(idCustomer);
            };
            
            var updateCartVouchersOriginal = updateCartVouchers;

            updateCartVouchers = function(vouchers) {
                if (typeof(vouchers) === 'object') {
                    var tpl = $('#fee_tpl').html();
                    
                    vouchers = $.grep(vouchers, function(item){
                        if (item.is_fee > 0) {
                            item.value = item.value_real.replace('-', '');
                            
                            $('#fees_list tbody').html(template(tpl, item));
                            
                            return false;
                        }
                        
                        return true;
                    });
                }
                
                updateCartVouchersOriginal(vouchers);
            };
            
            $("#fees_search").autocomplete(
                "{$link->getAdminLink('AdminOrderFees')|escape:'javascript':'UTF-8'}",
                {
                    minChars: 3,
                    max: 15,
                    width: 250,
                    selectFirst: false,
                    scroll: false,
                    dataType: "json",
                    formatItem: function(data, i, max, value, term) {
                        return value;
                    },
                    parse: function(data) {
                        if (!data.found) {
                            $('#fees_err').html('{l s='No fee was found' mod='orderfees'}').show();
                        } else {
                            $('#fees_err').hide();
                        }
                        
                        var mytab = new Array();
                            
                        for (var i = 0; i < data.vouchers.length; i++) {
                            mytab[mytab.length] = { data: data.vouchers[i], value: data.vouchers[i].name + (data.vouchers[i].code.length > 0 ? ' - ' + data.vouchers[i].code : '')};
                        }
                        
                        return mytab;
                    },
                    extraParams: {
                        ajax: "1",
                        token: "{getAdminToken tab='AdminOrderFees'}",
                        tab: "AdminOrderFees",
                        action: "searchCartRuleVouchers"
                    }
                }).result(function(event, data, formatted) {
                    $('#fees_search').val(data.name);
                    
                    add_cart_rule(data.id_cart_rule);
                    
                    $('#fees_search').val('');
                    $('#vouchers_err').hide();
                });
        });
    })(jQuery);
</script>