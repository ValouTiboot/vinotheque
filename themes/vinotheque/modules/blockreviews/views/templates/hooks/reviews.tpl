{*
/**
 * StorePrestaModules SPM LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 *
 /*
 * 
 * @author    StorePrestaModules SPM
 * @category seo
 * @package blockreviews
 * @copyright Copyright StorePrestaModules SPM
 * @license   StorePrestaModules SPM
 */
*}

<div id="reviews_block_footer_product">

{if $blockreviewsptabs_type == 1}
<h3 class="col-12 page_title" id="#idTab666">{l s='Reviews' mod='blockreviews'} <span id="count-review-tab">({$nbReviews|escape:'htmlall':'UTF-8'})</span>
</h3>
{/if}



<div id="idTab666"  class="{if $blockreviewsis17 == 1}{if $blockreviewsptabs_type != 3}block-categories{else}tab-pane fade in{/if}{else}tab-pane{/if}">



<div id="reviews-list">

{if $reviews}

{foreach from=$reviews item=review}
<div>
    <table class="prfb-table-reviews">
        <tr>
            <td class="prfb-left">
                <div class="prfb-name">{$review.customer_name|escape:'html':'UTF-8'}</div>
                {if $blockreviewsipon == 1}
                    {if $review.active == 1}
                        {if strlen($review.ip) > 0}
                            <span class="prfb-time">{l s='IP:' mod='blockreviews'} {$review.ip|escape:'htmlall':'UTF-8'}</span>
                        {/if}
                    {/if}
                {/if}
                <span class="prfb-time">{$review.date_add|date_format:'%d/%m/%Y'}</span>
                {if $review.active == 1}
                <div class="rating-total-for-item">
                    <div class="rating-total-for-item-part-l">
                        <div class="rating">{$review.rating|escape:'htmlall':'UTF-8'}</div>
                    </div>
                    <div class="rating-total-for-item-part-r">
                        <div class="rating-stars-total">
                            (<span>{$review.rating|escape:'htmlall':'UTF-8'}</span>/<span>5</span>)&nbsp;
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>
                {/if}
            </td>

            {if $review.active == 1}

            <td class="prfb-right">
                {if $blockreviewssubjecton == 1}<div class="h3">{$review.subject|escape:'htmlall':'UTF-8'}</div>{/if}
                <div class="rvTxt">
                    <span>
                        {$review.text_review|nl2br nofilter}
                    </span>

                    {if $review.is_show == 1}
                    <div class="admin-reply-on-review">
                        <div class="owner-date-reply">{l s='Administrator' mod='blockreviews'}: </div>
                        {$review.response|nl2br nofilter}
                    </div>
                    {/if}

                </div>
            </td>

            {else}
            <td class="prfb-right">
                <div class="rvTxt">
                    {l s='Review is pending moderation' mod='blockreviews'}
                </div>
            </td>
            {/if}
        </tr>
        {if $review.active == 1}
            {if $blockreviewsrecommendedon == 1}
                <tr>
                    <td class="prfb-left-bottom">&nbsp;</td>
                    <td class="prfb-right-bottom" >

                        <div class="recommended">
                            <span>{l s='Recommended to buy:' mod='blockreviews'}</span>
                            {if $review.recommended_product == 1}
                            <b class="yes">{l s='Yes' mod='blockreviews'}</b>
                            {else}
                            <b class="no">{l s='No' mod='blockreviews'}</b>
                            {/if}
                        </div>
                            <div class="prfb-clear"></div>
                    </td>
                </tr>
            {/if}
        {/if}
    </table>
</div>
{/foreach}

	<div id="reviews-paging1">{$pagenav nofilter}</div>
	<div class="prfb-clear"></div>
{else}
	<p class="align_center">{l s='No customer reviews for the moment.' mod='blockreviews'}</p>
{/if}


</div>

<div id="reviews-paging"></div>

{literal}
<script type="text/javascript">
var module_dir = '{/literal}{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/blockreviews/{literal}';
</script>
{/literal}

{if $blockreviewsis_ps15 == 0}
{literal}
<script type="text/javascript" src="{/literal}{$base_dir_ssl|escape:'htmlall':'UTF-8'}{literal}modules/blockreviews/views/js/r_stars.js"></script>
{/literal}
{/if}

{literal}
<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function(event) {
        jQuery(document).ready(init_rating);
    });
</script>
{/literal}


    <br/>

{if $blockreviewsid_customer == 0 && $blockreviewssettings == 'reg'}
<div class="no-registered">

{l s='Only registered user can add review.' mod='blockreviews'}
<a rel="nofollow" href="{$blockreviewsmacc|escape:'htmlall':'UTF-8'}"
		class="button {if $blockreviewsis_ps15 == 0}no-reg-button-14{/if} {if $blockreviewsis17 == 1}button-small-blockreviews{/if}"
        >{l s='Login' mod='blockreviews'}</a>
						
</div>




{elseif $blockreviewsis_buy == 0 && $blockreviewssettings == 'buy'}
<div class="no-registered">
			<div class="text-no-reg">
						{l s='Only users who already bought the product can add review.' mod='blockreviews'}
			</div>
</div>

{else}

{if $blockreviewsis_add == 1}

<div class="advertise-text-review">
	{l s='You have already add review for this product' mod='blockreviews'}
</div>

{else}

<div class="button-bottom-add-review">
	 <a href="#" class="" onclick="show_form_review(1);return false;">{l s='Add review' mod='blockreviews'}</a>
</div>

<div id="add-review-form">
	<div class="title-rev clearfix">
		<a href="#" class="float-right btn-success-custom-hide" onclick="show_form_review(0);return false"><i class="fa fa-times"></i></a>
	</div>

    <div id="body-add-blockreviews-form">
        <div class="form-group row"> 
    	   <label for="rat_rel" class="form-label-control col-md-3">{l s='Rating:' mod='blockreviews'}<sup class="blockreviews-req">*</sup></label>
           <div class="col-md-6">           
    			<span class="rat" style="cursor:pointer; padding-left: 3px;">
    				<span onmouseout="read_rating_review_shop('rat_rel');">
                        <img  style='margin-left: -3px;' onmouseover="_rating_efect_rev(1,0,'rat_rel')" onmouseout="_rating_efect_rev(1,1,'rat_rel')" onclick = "rating_review_shop('rat_rel',1); rating_checked=true;" src="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/blockreviews/views/img/star-ps-empty.png" alt=""  id="img_rat_rel_1" />
    					<img  style='margin-left: -3px;' onmouseover="_rating_efect_rev(2,0,'rat_rel')" onmouseout="_rating_efect_rev(2,1,'rat_rel')" onclick = "rating_review_shop('rat_rel',2); rating_checked=true;" src="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/blockreviews/views/img/star-ps-empty.png" alt=""  id="img_rat_rel_2" />
    					<img  style='margin-left: -3px;' onmouseover="_rating_efect_rev(3,0,'rat_rel')" onmouseout="_rating_efect_rev(3,1,'rat_rel')" onclick = "rating_review_shop('rat_rel',3); rating_checked=true;" src="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/blockreviews/views/img/star-ps-empty.png" alt=""  id="img_rat_rel_3" />
    					<img  style='margin-left: -3px;' onmouseover="_rating_efect_rev(4,0,'rat_rel')" onmouseout="_rating_efect_rev(4,1,'rat_rel')" onclick = "rating_review_shop('rat_rel',4); rating_checked=true;" src="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/blockreviews/views/img/star-ps-empty.png" alt=""  id="img_rat_rel_4" />
    					<img  style='margin-left: -3px;' onmouseover="_rating_efect_rev(5,0,'rat_rel')" onmouseout="_rating_efect_rev(5,1,'rat_rel')" onclick = "rating_review_shop('rat_rel',5); rating_checked=true;" src="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/blockreviews/views/img/star-ps-empty.png" alt=""  id="img_rat_rel_5" />
    				</span>
    			</span>
    			<input type="hidden" id="rat_rel" name="rat_rel" value="0"/>
                <div class="errorTxtAdd" id="error_rat_rel"></div>
           </div>
        </div>

		{if !$blockreviewsislogged}
        <div class="form-group row">
            <label for="name-review" class="form-label-control col-md-3">{l s='Name:' mod='blockreviews'}<sup class="blockreviews-req">*</sup></label>
            <div class=""col-md-6>            
    			<input type="text" name="name-review" id="name-review" class="blockreviews-input" onkeyup="check_inpNameReview();" onblur="check_inpNameReview();" />
                <div class="errorTxtAdd" id="error_name-review"></div>
            </div>
        </div>
        <div class="form-group row">
			<label for="email-review" class="form-label-control col-md-3">{l s='Email:' mod='blockreviews'}<sup class="blockreviews-req">*</sup></label>
			<div class="col-md-6">            
                <input type="text" name="email-review" id="email-review" class="blockreviews-input" onkeyup="check_inpEmailReview();" onblur="check_inpEmailReview();"/>
                <div class="errorTxtAdd" id="error_email-review"></div>
            </div>
        </div>
		{/if}

        {if $blockreviewssubjecton == 1}
        <div class="form-group row">
            <label for="subject-review" class="form-label-control col-md-3">{l s='Subject:' mod='blockreviews'}<sup class="blockreviews-req">*</sup></label>
            <div class="col-md-6">            
    			<input type="text" name="subject-review" id="subject-review" class="blockreviews-input" onkeyup="check_inpSubjectReview();" onblur="check_inpSubjectReview();" />
                <div class="errorTxtAdd" id="error_subject-review"></div>
            </div>
        </div>
        {/if}

        <div class="form-group row">
            <label for="text-review" class="form-label-control col-md-3">{l s='Text:' mod='blockreviews'}<sup class="blockreviews-req">*</sup></label>
            <div class="col-md-6">            
    			<textarea class="blockreviews-textarea" id="text-review" name="text-review" cols="42" rows="7" onkeyup="check_inpMsgReview();" onblur="check_inpMsgReview();"></textarea>
                <div class="errorTxtAdd" id="error_text-review"></div>
            </div>
        </div>

        {if $blockreviewsrecommendedon == 1}
		<label>{l s='Do you recommend this product to buy?' mod='blockreviews'} </label>
			<div class="recommended-review">
				<span class="yes-review {if $blockreviewsis17 == 1}recommeded17{/if}">
					<input type="radio"  name="recommended-review" value="1" checked="checked"/>&nbsp;{l s='Yes' mod='blockreviews'}
				</span>
				<span class="no-review {if $blockreviewsis17 == 1}recommeded17{/if}">
					<input type="radio"  name="recommended-review" value="0"/>&nbsp;{l s='No' mod='blockreviews'}
				</span>
			</div>
        {/if}

        {* gdpr *}
        {hook h='displayGDPRConsent' mod='psgdpr' id_module=$id_module}
        {* gdpr *}


		{if $blockreviewsis_captcha == 1}
        <div class="form-group row">
    		<label for="inpCaptchaReview" class="form-label-control col-md-3">{l s='Captcha' mod='blockreviews'}&nbsp;<sup class="blockreviews-req">*</sup></label>
            <div class="col-md-6"> 
    			<img width="100" height="26" class="float-left" id="secureCodReview" src="{$blockreviewscaptcha_url nofilter}" alt="Captcha"/>
    			<input type="text" class="inpCaptchaReview float-left" id="inpCaptchaReview" size="6" onkeyup="check_inpCaptchaReview();" onblur="check_inpCaptchaReview();" />
    			<div class="clr"></div>
    			<div id="error_inpCaptchaReview" class="errorTxtAdd"></div>	
            </div>
        </div>
		{/if}
    </div>
	<div id="footer-add-blockreviews-form-blockreviews" class="text-right">
		<input type="button" class="btn btn-primary" value="{l s='Add review' mod='blockreviews'}" onclick="add_review()"/>
    </div>
</div>



{literal}
<script type="text/javascript">


    // gdpr
    setTimeout(function() {
        $('#footer-add-blockreviews-form-blockreviews').find('input[type="submit"]').removeAttr('disabled');
    }, 1000);
    // gdpr


    function field_gdpr_change_blockreviews(){
        // gdpr
        var gdpr_blockreviews = $('#psgdpr_consent_checkbox_{/literal}{$id_module|escape:'htmlall':'UTF-8'}{literal}');

        var is_gdpr_blockreviews = 1;

        if(gdpr_blockreviews.length>0){

            if(gdpr_blockreviews.prop('checked') == true) {
                $('.gdpr_module_{/literal}{$id_module|escape:'htmlall':'UTF-8'}{literal} .psgdpr_consent_message').removeClass('error-label');
            } else {
                $('.gdpr_module_{/literal}{$id_module|escape:'htmlall':'UTF-8'}{literal} .psgdpr_consent_message').addClass('error-label');
                is_gdpr_blockreviews = 0;
            }

            $('#psgdpr_consent_checkbox_{/literal}{$id_module|escape:'htmlall':'UTF-8'}{literal}').on('click', function(){
                if(gdpr_blockreviews.prop('checked') == true) {
                    $('.gdpr_module_{/literal}{$id_module|escape:'htmlall':'UTF-8'}{literal} .psgdpr_consent_message').removeClass('error-label');
                } else {
                    $('.gdpr_module_{/literal}{$id_module|escape:'htmlall':'UTF-8'}{literal} .psgdpr_consent_message').addClass('error-label');
                }
            });

        }

        //gdpr

        return is_gdpr_blockreviews;
    }

    var rating_checked = false;


    function check_inpRatingReview()
    {

        if(!rating_checked){
            field_state_change_blockreviews('rat_rel','failed', '{/literal}{$blockreviewsmsg6|escape:'htmlall':'UTF-8'}{literal}');
            return false;
        }
        field_state_change_blockreviews('rat_rel','success', '');
        return true;
    }

    {/literal}{if !$blockreviewsislogged}{literal}
    function check_inpNameReview()
    {

        var name_review = trim(document.getElementById('name-review').value);

        if (name_review.length == 0)
        {
            field_state_change_blockreviews('name-review','failed', '{/literal}{$blockreviewsmsg3|escape:'htmlall':'UTF-8'}{literal}');
            return false;
        }
        field_state_change_blockreviews('name-review','success', '');
        return true;
    }


    function check_inpEmailReview()
    {

        var email_review = trim(document.getElementById('email-review').value);

        if (email_review.length == 0)
        {
            field_state_change_blockreviews('email-review','failed', '{/literal}{$blockreviewsmsg4|escape:'htmlall':'UTF-8'}{literal}');
            return false;
        }
        field_state_change_blockreviews('email-review','success', '');
        return true;
    }
    {/literal}{/if}{literal}


    {/literal}{if $blockreviewssubjecton == 1}{literal}
    function check_inpSubjectReview()
    {

        var subject_review = trim(document.getElementById('subject-review').value);

        if (subject_review.length == 0)
        {
            field_state_change_blockreviews('subject-review','failed', '{/literal}{$blockreviewsmsg5|escape:'htmlall':'UTF-8'}{literal}');
            return false;
        }
        field_state_change_blockreviews('subject-review','success', '');
        return true;
    }
    {/literal}{/if}{literal}

    function check_inpMsgReview()
    {

        var subject_review = trim(document.getElementById('text-review').value);

        if (subject_review.length == 0)
        {
            field_state_change_blockreviews('text-review','failed', '{/literal}{$blockreviewsmsg2|escape:'htmlall':'UTF-8'}{literal}');
            return false;
        }
        field_state_change_blockreviews('text-review','success', '');
        return true;
    }


    {/literal}{if $blockreviewsis_captcha == 1}{literal}
    function check_inpCaptchaReview()
    {

        var inpCaptchaReview = trim(document.getElementById('inpCaptchaReview').value);

        if (inpCaptchaReview.length != 6)
        {
            field_state_change_blockreviews('inpCaptchaReview','failed', '{/literal}{$blockreviewsmsg1|escape:'htmlall':'UTF-8'}{literal}');
            return false;
        }
        field_state_change_blockreviews('inpCaptchaReview','success', '');
        return true;
    }
    {/literal}{/if}{literal}



function add_review(){
	var _rating_review = $('#rat_rel').val();
	var _subject_review = $('#subject-review').val();
	var _text_review = $('#text-review').val();
	var _name_review = $('#name-review').val();
	var _email_review = $('#email-review').val();
	{/literal}{if $blockreviewsis_captcha == 1}{literal}
	var _captcha = $('#inpCaptchaReview').val();
	{/literal}{/if}{literal}
			
	var _recommended_review;
	
	if ($("input[name='recommended-review']:checked").val() == '1') {
		_recommended_review = 1;
	}
	else{
		_recommended_review = 0;
	}



    var is_rating = check_inpRatingReview();

    {/literal}{if !$blockreviewsislogged}{literal}
    var is_name_review = check_inpNameReview();
    var is_email_review = check_inpEmailReview();
    {/literal}{/if}{literal}


    {/literal}{if $blockreviewssubjecton == 1}{literal}
    var is_subject_review = check_inpSubjectReview();
    {/literal}{/if}{literal}


    var is_msg_review =check_inpMsgReview();


    {/literal}{if $blockreviewsis_captcha == 1}{literal}
    var is_captcha_review = check_inpCaptchaReview();
    {/literal}{/if}{literal}


    // gdpr
    var is_gdpr_blockreviews = field_gdpr_change_blockreviews();

    if(is_rating

            && is_gdpr_blockreviews //gdpr

            {/literal}{if !$blockreviewsislogged}{literal}
            && is_name_review && is_email_review
            {/literal}{/if}{literal}

            && is_msg_review

            {/literal}{if $blockreviewssubjecton == 1}{literal}
            && is_subject_review
            {/literal}{/if}{literal}


            {/literal}{if $blockreviewsis_captcha == 1}{literal}
            && is_captcha_review
            {/literal}{/if}{literal}
    ){
		
	$('#reviews-list').css('opacity',0.5);
	$('#add-review-form').css('opacity',0.5);
	$('#block-reviews-left-right').css('opacity',0.5);

     $('#footer-add-blockreviews-form-blockreviews input').attr('disabled','disabled');
	
	
	$.post(
            '{/literal}{$blockreviewsajax_url nofilter}{literal}',

			{action:'addreview',
			 rating:_rating_review,
			 subject:_subject_review,
			 name:_name_review,
			 email:_email_review,
			 text_review:_text_review,
			 id_product:{/literal}{$blockreviewsid_product|escape:'htmlall':'UTF-8'}{literal},
		 	 id_customer:{/literal}{$blockreviewsid_customer|escape:'htmlall':'UTF-8'}{literal},
	 	 	 recommended_product:_recommended_review,
		 	 {/literal}{if $blockreviewsis_captcha == 1}{literal}
		 	 captcha:_captcha,
			 {/literal}{/if}{literal}
	 	 	 link:"{/literal}{$blockreviewsurl_ssl|escape:'htmlall':'UTF-8'}{$smarty.server.HTTP_HOST|escape:'htmlall':'UTF-8'}{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}{literal}"
		 	 
			 }, 
	function (data) {
		$('#reviews-list').css('opacity',1);
		$('#add-review-form').css('opacity',1);
		$('#block-reviews-left-right').css('opacity',1);

        $('#footer-add-blockreviews-form-blockreviews input').removeAttr('disabled');
		
		if (data.status == 'success') {

			$('#reviews-list').html('');
			var paging = $('#reviews-list').prepend(data.params.content);
	    	$(paging).hide();
	    	$(paging).fadeIn('slow');

			$('#reviews-paging').html('');
			var paging = $('#reviews-paging').prepend(data.params.paging);
	    	$(paging).hide();
	    	$(paging).fadeIn('slow');


			var count_review = data.params.count_reviews;

			$('#count-review-tab').html('');
			$('#count-review-tab').html('('+count_review+')');

			$('#count_review_main').html('');
			$('#count_review_main').html(count_review);

			var avg_rating = data.params.avg_rating;
			$('#review_block .rating').html('');
			$('#review_block .rating').html(avg_rating);

			var avg_decimal = data.params.avg_decimal;
			$('#avg_decimal').html('');
			$('#avg_decimal').html(avg_decimal);
			
			jQuery(document).ready(init_rating);

			{/literal}{if $blockreviewsis_onereview == 0}{literal}
				$('#add-review-form').html('');
				
				var is_onereview_null ='<div class="advertise-text-review is_one_review">'+
				'{/literal}{l s='You have already add review for this product' mod='blockreviews'}{literal}'+
				'</div>';
			$('#add-review-form').html(is_onereview_null);
		
			{/literal}{else}{literal}
				show_form_review(0);
			{/literal}{/if}{literal}
			
			$('#subject-review').val('');
			$('#text-review').val('');
			$('#name-review').val('');
			$('#email-review').val('');
			$('#inpCaptchaReview').val('');
			$('#rat_rel').val('');
			

			{/literal}{if $blockreviewsis_captcha == 1}{literal}
			var count = Math.random();
			document.getElementById('secureCodReview').src = "";
			document.getElementById('secureCodReview').src = "{/literal}{$blockreviewscaptcha_url|escape:'url'}{literal}{/literal}{if $blockreviewsis_rewrite == 1}?{else}&{/if}{literal}re=" + count;
			{/literal}{/if}{literal}
			
			$(window).scrollTop(630);

			
					
		} else {
			
				var error_type = data.params.error_type;

				if(error_type == 2){

                    field_state_change_blockreviews('email-review','failed', '{/literal}{$blockreviewsmsg8|escape:'htmlall':'UTF-8'}{literal}');
                    return false;
				}

				{/literal}{if $blockreviewsis_captcha == 1}{literal}
				if(error_type == 3){
                    field_state_change_blockreviews('inpCaptchaReview','failed', '{/literal}{$blockreviewsmsg7|escape:'htmlall':'UTF-8'}{literal}');
                    var count = Math.random();
                    document.getElementById('secureCodReview').src = "";
                    document.getElementById('secureCodReview').src = "{/literal}{$blockreviewscaptcha_url nofilter}{literal}{/literal}{if $blockreviewsis_rewrite == 1}?{else}&{/if}{literal}re=" + count;

                    return false;
				}
				{/literal}{/if}{literal}
				

				{/literal}{if $blockreviewsis_captcha == 1}{literal}
				var count = Math.random();
				document.getElementById('secureCodReview').src = "";
				document.getElementById('secureCodReview').src = "{/literal}{$blockreviewscaptcha_url nofilter}{literal}{/literal}{if $blockreviewsis_rewrite == 1}?{else}&{/if}{literal}re=" + count;
				{/literal}{/if}{literal}
			
		}
	}, 'json');

    }
}
</script>
{/literal}
{/if}
{/if}
</div>

{if $blockreviewsreviewson == 1}
    {if $blockreviewshooktodisplay == "product_footer" && $blockreviewsis17 == 1}
            <div id="review_block">
                <div class="text-align-center">



                        <div class="rating">{$avg_rating|escape:'htmlall':'UTF-8'}</div>
                        <div class="rev-text">
                            <span id="avg_decimal">{$avg_decimal|escape:'htmlall':'UTF-8'}</span>/<span>5</span> - <span id="count_review_main">{$nbReviews|escape:'htmlall':'UTF-8'}</span> {$textReview|escape:'htmlall':'UTF-8'}
                        </div>

                </div>

                <div class="text-align-center margin-top-10">
                    <a href="{$blockreviewsurl_ssl|escape:'htmlall':'UTF-8'}{$smarty.server.HTTP_HOST|escape:'htmlall':'UTF-8'}{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}#idTab666"
                       id="idTab666-my-click" class="btn-custom btn-success-custom" >
                        <b>{l s='Add review' mod='blockreviews'}</b>
                    </a>
                </div>
            </div>
    {/if}
{/if}
</div>
