function checkDiscountForm() {
    if (parseInt($("#discounts_active_val").val()) == 0)
        return true;

    if (parseInt($("#discounts_different_val2").val()) == 1) {
        if (parseInt($("#discounts_tranche").val()) >= 1) {
            if (parseInt($("#discounts_min_1").val().length) == 0) {
                alert(min_1);
                return false;
            }
            if ($("#discounts_type_1").val() != 'shipping' && parseInt($("#discounts_value_1").val().length) == 0) {
                alert(value_1);
                return false;
            }
            if (parseInt($("#discounts_validity_days_1").val().length) == 0) {
                alert(valid_1);
                return false;
            }
        }
        if (parseInt($("#discounts_tranche").val()) >= 2) {
            if (parseInt($("#discounts_min_2").val().length) == 0) {
                alert(min_2);
                return false;
            }
            if ($("#discounts_type_2").val() != 'shipping' && parseInt($("#discounts_value_2").val().length) == 0) {
                alert(value_2);
                return false;
            }
            if (parseInt($("#discounts_validity_days_2").val().length) == 0) {
                alert(valid_2);
                return false;
            }
        }
        if (parseInt($("#discounts_tranche").val()) >= 3) {
            if (parseInt($("#discounts_min_3").val().length) == 0) {
                alert(min_3);
                return false;
            }
            if ($("#discounts_type_3").val() != 'shipping' && parseInt($("#discounts_value_3").val().length) == 0) {
                alert(value_3);
                return false;
            }
            if (parseInt($("#discounts_validity_days_3").val().length) == 0) {
                alert(valid_3);
                return false;
            }
        }
    } else {
        if ($("#discounts_type").val() != 'shipping' && parseInt($("#discounts_value").val().length) == 0) {
            alert(val);
            return false;
        }
        if (parseInt($("#discounts_validity_days").val().length) == 0) {
            alert(valid);
            return false;
        }
    }
}

function previewTemplate(template_id, wich_remind, token_cartabandonment) {
    $.ajax({
            type: "POST",
            url: "../modules/cartabandonmentpro/ajax_previewTemplate.php",
            async: false,
            data: {
                template_id: template_id,
                token_cartabandonment: token_cartabandonment,
                wich_remind: wich_remind,
                language: $("#language").val()
            }
        })
        .done(function(msg) {
            if (!msg)
                alert("Erreur lors de la récupération du template.");
            else {
                $("#myModal").show("fast");
                $("#modalContent").html(msg);
                $("#backgroundModal").show("fast");
            }
        });
}

function closePreview() {
    $("#myModal").hide('fast');
    $("#backgroundModal").hide('slow');
}

function deleteTemplate(template_id, token_cartabandonment, id_lang) {
    $.ajax({
            type: "POST",
            url: "../modules/cartabandonmentpro/ajax_deleteTemplate.php",
            data: {
                template_id: template_id,
                token_cartabandonment: token_cartabandonment,
                id_lang: id_lang
            }
        })
        .done(function(msg) {
            if (!msg)
                alert("Erreur lors de la supression du template.");
            $("#edit_template").hide();
            $("#newTemplate").hide();
        });
}

function activateTemplate(template_id, active, token_cartabandonment) {
    $.ajax({
            type: "POST",
            url: "../modules/cartabandonmentpro/ajax_activateTemplate.php",
            data: {
                template_id: template_id,
                active: active,
                token_cartabandonment: token_cartabandonment
            }
        })
        .done(function(msg) {
            if (!msg) {
                alert("Erreur lors de l'activation du template.");
                return false;
            }
            window.location.reload();
        });
}

function isInt(val) {
    if (parseInt(val) != val) return false;
    return true;
}

function setDays(wichReminder, val, token, id_shop) {
    if (!isInt(val)) {
        var remindTxt = getRemindTxt(wichReminder);
        $("#" + remindTxt + "_reminder_days").val(val.substring(0, val.length - 1));
        return false;
    }
    $.ajax({
            type: "POST",
            url: "../modules/cartabandonmentpro/ajax_reminder.php",
            data: {
                wichReminder: wichReminder,
                val: val,
                token_cartabandonment: token,
                action: 'setDays',
                id_shop: id_shop
            }
        })
        .done(function(msg) {
            if (!msg)
                alert("Erreur lors de la modification.");
            else
                fixMaxReminder();
        });
}

function setHours(wichReminder, val, token, id_shop) {
    if (!isInt(val)) {
        var remindTxt = getRemindTxt(wichReminder);
        $("#" + remindTxt + "_reminder_hours").val(val.substring(0, val.length - 1));
        return false;
    }
    $.ajax({
            type: "POST",
            url: "../modules/cartabandonmentpro/ajax_reminder.php",
            data: {
                wichReminder: wichReminder,
                val: val,
                token_cartabandonment: token,
                action: 'setHours',
                id_shop: id_shop
            }
        })
        .done(function(msg) {
            if (!msg)
                alert("Erreur lors de la modification.");
        });
}

function getRemindTxt(wichReminder) {
    switch (wichReminder) {
        case 1:
            var remindTxt = 'first';
            break;
        case 2:
            var remindTxt = 'second';
            break;
        case 3:
            var remindTxt = 'third';
            break;
    }
    return remindTxt;
}

function setActive(wichReminder, token, id_shop, active) {
    var remindTxt = getRemindTxt(wichReminder);
    $.ajax({
            type: "POST",
            url: "../modules/cartabandonmentpro/ajax_reminder.php",
            data: {
                wichReminder: wichReminder,
                val: active,
                token_cartabandonment: token,
                action: 'setActive',
                id_shop: id_shop
            }
        })
        .done(function(msg) {
            if (!msg) {
                alert("Erreur lors de la modification.");
                return false;
            }
            $("#" + wichReminder + "_reminder").val(active);

            refreshWichTemplate();
            fixMaxReminder();

            if (active == 0) {
                $("#" + remindTxt + "_reminder_days").prop('disabled', true);
                $("#" + remindTxt + "_reminder_hours").prop('disabled', true);
            } else {
                $("#" + remindTxt + "_reminder_days").prop('disabled', false);
                $("#" + remindTxt + "_reminder_hours").prop('disabled', false);
            }
        });
}

function setNewsletter(token, id_shop, active) {
    if (active) {
        $('.newletter_alert').hide();
    } else {
        $('.newletter_alert').show();
    }
    $.ajax({
            type: "POST",
            url: "../modules/cartabandonmentpro/ajax_reminder.php",
            data: {
                val: active,
                token_cartabandonment: token,
                action: 'setNewsletter',
                id_shop: id_shop
            }
        })
        .done(function(msg) {
            if (!msg) {
                alert("Oops... Something went wrong!");
                return false;
            }
        });
}

function setDiscountsActive(val) {
    $("#discounts_active").val(active);
}

function setVal(action, val) {
    $.ajax({
        type: 'POST',
        url: '../modules/cartabandonmentpro/setVal.php',
        data: {
            action: action,
            val: val,
            token_cartabandonment: token
        },
        success: function(msg) {
            if (!msg || msg === 0 || msg === '0')
                alert("Erreur lors de la modification.");
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(jqXHR);
            console.log(textStatus);
            console.log(errorThrown);
        }
    });
}

function discountsActive(val) {
    if (val == 1)
        $('#discounts_configure').show('slow');
    else
        $('#discounts_configure').hide('slow');
    $("#discounts_active_val").val(val);
}

function fixMaxReminder() {

    var max = +$('[name=max_reminder]').val();

    if ($('#1_reminder').val() > 0)
        if (+$('#first_reminder_days').val() > max)
            max = +$('#first_reminder_days').val();

    if ($('#2_reminder').val() > 0)
        if (+$('#second_reminder_days').val() > max)
            max = +$('#second_reminder_days').val();

    if ($('#3_reminder').val() > 0)
        if (+$('#third_reminder_days').val() > max)
            max = +$('#third_reminder_days').val();

    if (max == +$('[name=max_reminder]').val())
        return;

    $('[name=max_reminder]').attr('value', max + 1).trigger('blur');
}

function setMaxReminder(val, token) {

    // Check if max is superior to first reminder
    if ($('#1_reminder').val() > 0)
        if (+$('#first_reminder_days').val() >= val) {
            alert(alerts_lang['max_reminder_inferior_to_active_reminders']);
            return;
        }
    // Check if max is superior to first reminder
    if ($('#2_reminder').val() > 0)
        if (+$('#second_reminder_days').val() >= val) {
            alert(alerts_lang['max_reminder_inferior_to_active_reminders']);
            return;
        }
    // Check if max is superior to first reminder
    if ($('#3_reminder').val() > 0)
        if (+$('#third_reminder_days').val() >= val) {
            alert(alerts_lang['max_reminder_inferior_to_active_reminders']);
            return;
        }

    $.ajax({
            type: "POST",
            url: "../modules/cartabandonmentpro/ajax_reminder.php",
            data: {
                val: val,
                token_cartabandonment: token,
                action: 'setMaxReminder'
            }
        })
        .done(function(msg) {
            if (!msg)
                alert("Erreur lors de la modification.");
        });
}

function tplSame(val) {
    $("#tpl_same").val(val);
    if (val == 1)
        $("#wich_template_content").hide();
    else
        $("#wich_template_content").show();
}

function changeTemplate() {
    var selected = $("#wich_template").val();
    $(".picto_model").hide();
    $(".picto_tpl_" + selected).show();
    $(".models").hide();
    $("#model_" + $("#model" + selected).val() + "_" + selected).show();
    $(".template_names").hide();
    $("#template_name_" + selected).show();

    $("#discount_template").val(selected);
    // $(".discounts_diff_val").hide();
    // $("#discounts_diff_val_"+selected).show('fast');
    // $(".active").hide();
    // $("#active_"+selected).show('fast');
}

function changeLanguage() {
    var selectedLanguage = $("#language").val();
    var url = window.location.href;
    url = url.replace(/#.*/, '');

    if (url.indexOf('id_lang') > -1) {
        url = url.replace(/id_lang=\d*/, 'id_lang=' + selectedLanguage);
    } else {
        url = url + '&id_lang=' + selectedLanguage
    }

    if (url.indexOf('cartabandonment_conf') < 0) {
        url = url + '&cartabandonment_conf=1'
    }

    $("#id_lang").val(selectedLanguage);
    window.location.href = url;
}

function setModel(model) {
    if ($("#tpl_same").val() == 1) {
        $("#model1").val(model);
        $("#model2").val(model);
        $("#model3").val(model);
    } else {
        var wich_reminder = $("#wich_template").val();
        $("#model" + wich_reminder).val(model);
    }
}

function refreshWichTemplate() {
    var selected = false;
    for (var x = 1; x <= 3; x++) {
        if (+$("#" + x + "_reminder").val() == 0) {
            $('.tpl_list_' + x).hide();
            $("#wich_template_" + x).hide();
        } else {
            $("#wich_template_" + x).show();
            $('.tpl_list_' + x).show();
            if (!selected) {
                selected = true;
                $("#wich_template_" + x).attr('selected', 'selected');
                changeTemplate();
            }
        }
    }
}

function discountsDiffVal(val) {
    if (val == 1) {
        $('#same_discounts').hide();
        $('#different_discounts').show('slow');
    } else {
        $('#different_discounts').hide();
        $('#same_discounts').show('slow');
    }
    $("#discounts_different_val2").val(val);
}

// Sometimes, TinyMCE is launched too soon.
// So, we wait for the page to be loaded and we add an extra timeout, just to be sure.
window.addEventListener("load", function() {
    setTimeout(function() {
        if (+tinyMCE.majorVersion < 4) {
            $('.models textarea').addClass('rte');
            tinySetup();
        } else {
            tinySetup({
                editor_selector: "models textarea"
            });
        }
    }, 500);
});

$(function() {

    $(".list-group-item").on('click', function() {
        $(this).parent().find('.active').removeClass('active');
        $(this).addClass('active');
    });

    $('.alert-success').hide();
    // Load functions
    var colors = new Array();
    $(".color").each(function() {
        var color = new jscolor.color(document.getElementById($(this).attr('id')));
        colors[colors.lenght] = color;
    });
    var selectedLanguage = $("#language").val();
    $(".tpl_list").hide();
    $("." + selectedLanguage).show();
    refreshWichTemplate();

    $('[name^=color_picker_]').on('change', function() {
        $(this).parents('[id^=color_]').first().css("background-color", '#' + $(this).val());
        return false;
    });

    $('.lang_toggle').click(function(e) {

        $('.lang_toggle').removeClass('btn-primary');
        $('.lang_toggle').addClass('btn-default');
        $(this).addClass('btn-primary');
        var lang = $(this).attr('toggle_lang');
        $('.lang_toggle_' + lang).addClass('btn-primary');

        $('.multilang').addClass('hidden');
        var toggle = $(this).attr('toggle');
        $('.' + lang + '_container').removeClass('hidden');
    });

    $(window).scrollTop(0);

    $('#discounts_tranche').change(
        function() {
            var val = $(this).val();
            if ($(this).val() == 1) {
                $('#discount_2').hide('slow');
                $('#discount_3').hide('slow');
                $('#discount_1').show('slow');
            } else if ($(this).val() == 2) {
                $('#discount_3').hide('slow');
                $('#discount_1').show('slow');
                $('#discount_2').show('slow');
            } else if ($(this).val() == 3) {
                $('#discount_1').show('slow');
                $('#discount_2').show('slow');
                $('#discount_3').show('slow');
            }
            setVal('CARTABAND_DIF_DISC_' + tpl, 1);
        }
    );

    $(".discounts_type").click(
        function() {
            $(".discounts_type").removeClass("btn-primary").addClass("btn-default");
            var val = $(this).val();
            $(this).addClass("btn-primary");
            var tpl = tpl_selected;
            if (val == 'percent')
                $("#value_operator").html("%");
            else
                $("#value_operator").html(currency);
            if (val == 'shipping')
                $("#same_value").hide('fast');
            else
                $("#same_value").show('fast');
            $("#discounts_type").val(val);
        }
    );

    $(".diff_type").change(
        function() {
            console.log($(this).next('div').children('table'));
            if ($(this).val() == 'shipping')
                $(this).next('div').children('table').find('.value').hide('slow');
            else
                $(this).next('div').children('table').find('.value').show('slow');
        }
    );
    $("#discounts_type_1").click(
        function() {
            // $(".discounts_type_1").removeClass("btn-primary").addClass("btn-default");
            var val = $(this).val();
            // $("#discounts_type_1_"+val).addClass("btn-primary");

            if (val == 'shipping')
                $("#value_1").hide('fast');
            else if (val == 'percent') {
                console.log('percent');
                $("#value_operator_1").html("%");
                $("#value_1").show('fast');
            } else {
                console.log('currency');
                $("#value_operator_1").html(currency);
                $("#value_1").show('fast');
            }
            // $("#discounts_type_1").val(val);
        }
    );

    $("#discounts_type_2").click(
        function() {
            // $(".discounts_type_2").removeClass("btn-primary").addClass("btn-default");
            var val = $(this).val();
            // $("#discounts_type_2_"+val).addClass("btn-primary");
            if (val == 'shipping')
                $("#value_2").hide('fast');
            else if (val == 'percent') {
                $("#value_operator_2").html("%");
                $("#value_2").show('fast');
            } else {
                $("#value_operator_2").html(currency);
                $("#value_2").show('fast');
            }
            //$("#discounts_type_2").val(val);
        }
    );

    $("#discounts_type_3").click(
        function() {
            // $(".discounts_type_3").removeClass("btn-primary").addClass("btn-default");
            var val = $(this).val();
            // $("#discounts_type_3_"+val).addClass("btn-primary");
            if (val == 'shipping')
                $("#value_3").hide('fast');
            else if (val == 'percent') {
                $("#value_operator_3").html("%");
                $("#value_3").show('fast');
            } else {
                $("#value_operator_3").html(currency);
                $("#value_3").show('fast');
            }
            // $("#discounts_type_3").val(val);
        }
    );

    $('.choose-discount').click(function() {
        $('#discounts_template').val($(this).attr('data-val'));
        $("#template_chose").submit();
    });

    $("#discounts_template").change(
        function() {
            $("#template_chose").submit();
        }
    );

    $('.alert-cartabandonment').show();
    if (edit === 1) {
        $('#alertSave').show();

        setTimeout(function() {
            $('.alert-success').hide('slow');
        }, 5000);
    }
    $('#submitCron').click(function() {
        $('#alertSaveCron').show();
        setTimeout(function() {
            $('.alert-success').hide('slow');
        }, 5000);
    });
});

function mailTest(id_lang, id_shop, token) {
    $.ajax({
            type: "POST",
            url: "../modules/cartabandonmentpro/ajax_mailTest.php",
            data: {
                id_lang: id_lang,
                id_shop: id_shop,
                token: token,
                mail: $("#test_mail").val(),
                amount: $("#test_amount").val()
            }
        })
        .done(function(msg) {
            alert(msg);
        });
}

function discountsValidity(val) {
    if (val == 'date') {
        $('#div_discounts_validity_days').hide();
        $('#div_discounts_validity_date').show('slow');
    } else {
        $('#div_discounts_validity_date').hide();
        $('#div_discounts_validity_days').show('slow');
    }
}
