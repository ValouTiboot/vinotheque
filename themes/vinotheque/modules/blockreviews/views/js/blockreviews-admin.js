/**
 * SPM
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 *
 /*
 * 
 * @author    SPM
 * @category seo
 * @package blockreviews
 * @copyright Copyright SPM
 * @license   SPM
 */

function blockreviews_list(id_review,action,value,token){

    if(action == 'active') {
        $('#activeitem' + id_review).html('<img src="../img/admin/../../modules/blockreviews/views/img/loader.gif" />');
    }

    $.post(ajax_url_blockreviews,
        { id_review:id_review,
            action:action,
            value: value,
            token: token
        },
        function (data) {
            if (data.status == 'success') {


                var data = data.params.content;

                if(action == 'active'){

                    $('#activeitem'+id_review).html('');
                    if(value == 0){
                        var img_ok = 'ok';
                        var action_value = 1;
                    } else {
                        var img_ok = 'no_ok';
                        var action_value = 0;
                    }
                    var html = '<span class="label-tooltip" data-original-title="Click here to activate or deactivate review on your site" data-toggle="tooltip">'+
                            '<a href="javascript:void(0)" onclick="blockreviews_list('+id_review+',\'active\', '+action_value+',\''+token+'\');" style="text-decoration:none">'+
                        '<img src="../img/admin/../../modules/blockreviews/views/img/'+img_ok+'.gif" />'+
                        '</a>'+
                    '</span>';
                    $('#activeitem'+id_review).html(html);


                    // add code for alert message //
                    if(value == 0) {
                        var message_active = 'activated';
                    } else {
                        var message_active = 'deactivated';
                    }
                    $('.bootstrap .alert').remove();
                    $('.custom-success-message').remove();
                    var html_success = '<div class="custom-success-message flash-message-list alert alert-success">'+
                        '<ul>'+
                        '<li>Item #'+id_review+' successfully '+message_active+'.</li>'+
                        '</ul>'+
                        '</div>';
                    $('#form-blockreviews').before(html_success);
                    // add code for alert message //


                }

            } else {
                alert(data.message);

            }
        }, 'json');
}



// remove add new  button //
$('document').ready( function() {

    $('#desc-blockreviews-new').css('display','none');


});
// remove add new  button //

