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

function blockreviews_open_tab(){
    // for first tab style //
    $.each($('#more_info_tabs li'), function(key, val) {
        $(this).children().removeClass("selected");
    });

    $('#idTab666-my').addClass('selected');

    for(i=0;i < $('#more_info_sheets').children().length;i++){
        $('#more_info_sheets').children(i).addClass("block_hidden_only_for_screen");
    }
    $('#idTab666').removeClass('block_hidden_only_for_screen');

    // for first tab style //


    // for second tab style //
    if($('.nav-tabs').length>0) {

        $.each($('.nav-tabs li'), function (key, val) {
            $(this).removeClass("active");
            $(this).children().removeClass("active"); // for ps 1.7
        });

        $('#idTab666-my').parent().addClass('active');

        for (i = 0; i < $('.tab-content').children().length; i++) {
            $('.tab-content').children(i).removeClass("active");
            $('.tab-content').children(i).removeClass("in");
        }
        $('#idTab666').addClass('in');
        $('#idTab666').addClass('active');
        $('#idTab666-my').addClass('active'); // for ps 1.7

    }
    // for second tab style //
}


$(document).ready(function() {

    var is_bug_blockreviews = 0;

    if(is_bug_blockreviews) {

        $('#idTab666-my-click').click(function(){
            setTimeout(function () {
                $('#availability_statut').css('display', 'none');
                $('#add_to_cart').css('display', 'block');


            }, 1000);
        });
    }



    $('#idTab666-my-click').click(function(){

        blockreviews_open_tab();


        $('.button-bottom-add-review').hide(200);
        $('#add-review-form').show(200);



    });

});



function show_form_review(par){
    if(par == 1){
        $('.button-bottom-add-review').hide(200);
        $('#add-review-form').show(200);
    } else {
        $('.button-bottom-add-review').show(200);
        $('#add-review-form').hide(200);
    }
}



function PageNav( page,id_product ){

    $('#reviews-list').css('opacity',0.5);
    $('#reviews-paging').css('opacity',0.5);


    $.post(page_nav_ajax_url_blockreviews,
        {action:'pagenavsite',
            page:page,
            id_product:id_product
        },
        function (data) {
            if (data.status == 'success') {


                $('#reviews-list').css('opacity',1);
                $('#reviews-paging').css('opacity',1);

                $('#reviews-list').html('');
                var content = $('#reviews-list').prepend(data.params.content);
                $(content).hide();
                $(content).fadeIn('slow');

                $('#reviews-paging').html('');
                var paging = $('#reviews-paging').prepend(data.params.paging);
                $(paging).hide();
                $(paging).fadeIn('slow');


            } else {
                alert(data.message);
            }
        }, 'json');
}