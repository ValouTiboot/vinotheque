/**
 *  Order Fees
 *
 *  @author    motionSeed <ecommerce@motionseed.com>
 *  @copyright 2017 motionSeed. All rights reserved.
 *  @license   https://www.motionseed.com/en/license-module.html
 */

/* global restrictions */
/* global token */
/* global currentToken */
/* global dimension_rule_groups_counter */
/* global dimension_rule_counters */
/* global zipcode_rule_groups_counter */
/* global zipcode_rule_counters */
/* global message_errors_zipcode_select_country */
/* global message_errors_dimension_select_dimension */
/* global package_rule_groups_counter */
/* global package_rule_counters */
/* global message_errors_package_range_start */
/* global message_errors_package_range_end */
/* global message_errors_package_range */
/* global message_errors_package_unit */
(function ($) {
    $(function () {
        restrictions.push('payment');

        toggleCartRuleFilter($('#payment_restriction'));

        $('#payment_restriction').click(function () {
            toggleCartRuleFilter(this);
        });
        $('#payment_select_remove').click(function () {
            removeCartRuleOption(this);
        });
        $('#payment_select_add').click(function () {
            addCartRuleOption(this);
        });

        $(document).ajaxSend(function (event, jqxhr, settings) {
            var components = settings.url.split('?');
            
            if (typeof components[1] === 'undefined') {
                return;
            }
            
            var params = components[1].split("&").map(function (n) {
                return n = n.split("="), this[n[0]] = n[1], this
            }.bind({}))[0];

            if (params['controller'] === 'AdminCartRules') {
                params['controller'] = 'AdminOrderFees';
                params['token'] = token;
            }

            settings.url = components[0] + '?' + $.param(params);
        });

        // Dimension restriction
        toggleCartRuleFilter($('#dimension_restriction'));

        $('#dimension_restriction').click(function () {
            toggleCartRuleFilter(this);
        });
        
        // Zipcode restriction
        toggleCartRuleFilter($('#zipcode_restriction'));

        $('#zipcode_restriction').click(function () {
            toggleCartRuleFilter(this);
        });
        
        // Package restriction
        toggleCartRuleFilter($('#package_restriction'));

        $('#package_restriction').click(function () {
            toggleCartRuleFilter(this);
        });
        
        $('#package_restriction_div').on('change', '.package-units', function() {
            var tr = $(this).closest('tr');
            var unit_selected = $('option:selected', this);
            
            $('.package-units-predefined option', tr).each(function() {
                if ($(this).val() === '' || $(this).data('unit') === unit_selected.val()) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });

            $('.package-units-predefined').val($('.package-units-predefined option:first').val());
            
            $('.package-unit', tr).text(unit_selected.val());
            $('.input-group-addon.package-unit-weight', tr).text(unit_selected.data('weight'));
            $('input.package-unit-weight', tr).val(unit_selected.data('weight'));
        });
        
        $('.package-units').change();
        
        $('.package-unit + input').each(function() {
            var tr = $(this).closest('tr');
            var value = $(this).val();
            
            if ($('.package-units-predefined option[value="' + value + '"]', tr).length) {
                $('.package-units-predefined').val(value);
                
                $(this).val('');
            }
        });
        
        // Tax Rule
        $('.select_tax_rules_group').on('change', function() {
            $('#apply_tax_rules_group')[$(this).val() != $(this).data('show-for') ? 'slideUp' : 'slideDown']();
        }).change();
        
        $('input[name=apply_discount]').change(function() {
            var value = 0;
            
            if ($(this).val() == 'percent') {
                value = $('.select_tax_rules_group').data('show-for');
            }
            
            $('.select_tax_rules_group').val(value).change();
        });
    });
})(jQuery);

function addDimensionRuleGroup()
{
    $('#dimension_rule_group_table').show();
    dimension_rule_groups_counter += 1;
    dimension_rule_counters[dimension_rule_groups_counter] = 0;

    $.get(
            'ajax-tab.php',
            {controller: 'AdminCartRules', token: currentToken, newDimensionRuleGroup: 1, dimension_rule_group_id: dimension_rule_groups_counter},
    function (content) {
        if (content !== "")
            $('#dimension_rule_group_table').append(content);
    }
    );
}

function removeDimensionRuleGroup(id)
{
    $('#dimension_rule_group_' + id + '_tr').remove();
}

function addDimensionRule(dimension_rule_group_id)
{
    var type = $('#dimension_rule_type_' + dimension_rule_group_id).val();
    
    // Check
    if (type === '') {
        alert(message_errors_dimension_select_dimension);
        
        return;
    }
    
    if (typeof dimension_rule_counters[dimension_rule_group_id] === 'undefined') {
        dimension_rule_counters[dimension_rule_group_id] = 0;
    }
    
    dimension_rule_counters[dimension_rule_group_id] += 1;
    
    if (type !== '')
        $.get(
                'ajax-tab.php',
                {
                    controller: 'AdminCartRules',
                    token: currentToken,
                    newDimensionRule: 1,
                    dimension_rule_type: type,
                    dimension_rule_group_id: dimension_rule_group_id,
                    dimension_rule_id: dimension_rule_counters[dimension_rule_group_id]
                },
        function (content) {
            if (content !== "")
                $('#dimension_rule_table_' + dimension_rule_group_id).append(content);
        }
        );
}

function removeDimensionRule(dimension_rule_group_id, dimension_rule_id)
{
    $('#dimension_rule_' + dimension_rule_group_id + '_' + dimension_rule_id + '_tr').remove();
}

function addZipcodeRuleGroup()
{
    $('#zipcode_rule_group_table').show();
    zipcode_rule_groups_counter += 1;
    zipcode_rule_counters[zipcode_rule_groups_counter] = 0;

    $.get(
            'ajax-tab.php',
            {controller: 'AdminCartRules', token: currentToken, newZipcodeRuleGroup: 1, zipcode_rule_group_id: zipcode_rule_groups_counter},
    function (content) {
        if (content !== "")
            $('#zipcode_rule_group_table').append(content);
    }
    );
}

function removeZipcodeRuleGroup(id)
{
    $('#zipcode_rule_group_' + id + '_tr').remove();
}

function addZipcodeRule(zipcode_rule_group_id)
{
    var type = $('#zipcode_rule_type_' + zipcode_rule_group_id).val();
    
    // Check
    if (type === '') {
        alert(message_errors_zipcode_select_country);
        
        return;
    }
    
    if (typeof zipcode_rule_counters[zipcode_rule_group_id] === 'undefined') {
        zipcode_rule_counters[zipcode_rule_group_id] = 0;
    }
    
    zipcode_rule_counters[zipcode_rule_group_id] += 1;
    
    if (type !== '')
        $.get(
                'ajax-tab.php',
                {
                    controller: 'AdminCartRules',
                    token: currentToken,
                    newZipcodeRule: 1,
                    zipcode_rule_type: type,
                    zipcode_rule_group_id: zipcode_rule_group_id,
                    zipcode_rule_id: zipcode_rule_counters[zipcode_rule_group_id]
                },
        function (content) {
            if (content !== "") {
                $('#zipcode_rule_table_' + zipcode_rule_group_id).append(content);
            }
        }
        );
}

function removeZipcodeRule(zipcode_rule_group_id, zipcode_rule_id)
{
    $('#zipcode_rule_' + zipcode_rule_group_id + '_' + zipcode_rule_id + '_tr').remove();
}

function addPackageRuleGroup()
{
    $('#package_rule_group_table').show();
    package_rule_groups_counter += 1;
    package_rule_counters[package_rule_groups_counter] = 0;

    $.get(
            'ajax-tab.php',
            {
                controller: 'AdminCartRules',
                token: currentToken,
                newPackageRuleGroup: 1,
                package_rule_group_id: package_rule_groups_counter
            },
    function (content) {
        if (content !== "")
            $('#package_rule_group_table').append(content);
    }
    );
}

function removePackageRuleGroup(id)
{
    $('#package_rule_group_' + id + '_tr').remove();
}

function addPackageRule(package_rule_group_id)
{
    var range_start = parseFloat($('#package_rule_range_start_' + package_rule_group_id).val());
    var range_end = parseFloat($('#package_rule_range_end_' + package_rule_group_id).val());
    
    // Check
    if (range_start === '') {
        alert(message_errors_package_range_start);
        
        return;
    } else if (range_end === '') {
        alert(message_errors_package_range_end);
        
        return;
    } else if (range_end < range_start) {
        alert(message_errors_package_range);
        
        return;
    } else if ($('#package_rule_group_unit_predefined_' + package_rule_group_id).val() === '' && $('#package_rule_group_ratio_' + package_rule_group_id).val() === '') {
        alert(message_errors_package_unit);
        
        return;
    }
    
    if (typeof package_rule_counters[package_rule_group_id] === 'undefined') {
        package_rule_counters[package_rule_group_id] = 0;
    }
    
    package_rule_counters[package_rule_group_id] += 1;
    
    if ($('#package_rule_type_' + package_rule_group_id).val() !== 0)
        $.get(
                'ajax-tab.php',
                {
                    controller: 'AdminCartRules',
                    token: currentToken,
                    newPackageRule: 1,
                    package_rule_unit_weight: $('#package_rule_group_unit_weight_' + package_rule_group_id).val(),
                    package_rule_range_start: range_start,
                    package_rule_range_end: range_end,
                    package_rule_group_id: package_rule_group_id,
                    package_rule_id: package_rule_counters[package_rule_group_id]
                },
        function (content) {
            if (content !== "")
                $('#package_rule_table_' + package_rule_group_id).append(content);
        });
}

function removePackageRule(package_rule_group_id, package_rule_id)
{
    $('#package_rule_' + package_rule_group_id + '_' + package_rule_id + '_tr').remove();
}