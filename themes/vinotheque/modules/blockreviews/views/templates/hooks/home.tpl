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

<div {if $blockreviewsis17 == 0}id="left_column"{/if}>
<div id="blockreviews_block_left"
         class="block
                {if $blockreviewsbrev_sliderh == 1}owl_blockreviews_home_reviews_type_carousel{/if}
                {if $blockreviewsis17 == 1}block-categories{/if}
                {if $blockreviewsis16 == 1 && $blockreviewsis17 == 0}blockmanufacturer16{else}blockmanufacturer{/if}
                margin-top-10">
		<h4 class="title_block {if $blockreviewsis17 == 1}text-uppercase h6{/if} {if $blockreviewsis14 == 0}margin-bottom-5{/if}">
			<div class="blockreviews-float-left">
			{l s='Last Product Reviews' mod='blockreviews'}
			</div>
			<div class="{if $blockreviewsis14 == 0}blockreviews-float-left{else}float-left{/if} margin-left-5">
			{if $blockreviewsrsson == 1}
				<a href="{$blockreviewsrss_url|escape:'htmlall':'UTF-8'}" title="{l s='RSS Feed' mod='blockreviews'}" target="_blank">
					<img src="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/blockreviews/views/img/feed.png" alt="{l s='RSS Feed' mod='blockreviews'}" />
				</a>
			{/if}
			</div>
			<div class="clear"></div>
		</h4>
		<div class="block_content block-items-data">
			{if count($blockreviewsreviews)>0}

            <div class="blockreviews-articles-block">

{*{$blockreviewsbrev_sliderh|@var_dump}{count($blockreviewsreviews)|@var_dump}{$blockreviewsbrev_slh|@var_dump}*}

                {*{if $blockreviewsbrev_sliderh == 1 && (count($blockreviewsreviews) > $blockreviewsbrev_slh)}<ul class="owl-carousel owl-theme">{/if}*}
                {if $blockreviewsbrev_sliderh == 1}<ul class="owl-carousel owl-theme">{/if}

                    {foreach from=$blockreviewsreviews item=review name=myLoop}

                        <div class="rItem reviews-width-auto {if $blockreviewsbrev_sliderh == 1}border-none-reviews{/if}">
                            <div>
                                <div class="{if $blockreviewsbrev_sliderh == 0}float-left avatar-block-home"{/if} img-block-reviews">
                                    <a href="{$review.product_link|escape:'htmlall':'UTF-8'}"
                                       title="{$review.product_name|escape:'htmlall':'UTF-8'}"
                                            >
                                        <img src="{$review.product_img|escape:'htmlall':'UTF-8'}" title="{$review.product_name|escape:'htmlall':'UTF-8'}"
                                             alt = "{$review.product_name|escape:'htmlall':'UTF-8'}"
                                             class="img-responsive-custom border-image-review"
                                                />
                                    </a>
                                </div>


                                <span class="margin-bottom-5 title-block-review {if $blockreviewsbrev_sliderh == 1}padding-top-5{else}float-right review-block-text-home{/if}">
                                    <a href="{$review.product_link|escape:'htmlall':'UTF-8'}"
                                       title="{$review.product_name|escape:'htmlall':'UTF-8'}"
                                            >
                                        {$review.product_name|escape:'htmlall':'UTF-8'}
                                    </a>

                                    {if $review.active == 1}
                                    <div class="ratingBox {if $blockreviewsbrev_sliderh == 0}float-right{/if}">

                                        <span class="float-left margin-right-5">
                                        {if $review.rating != 0}
                                            {section name=bar loop=5 start=0}
                                                {if $smarty.section.bar.index < $review.rating}
                                                    <img src = "{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/blockreviews/views/img/ico-star.png" alt="{$smarty.section.bar.index|escape:'htmlall':'UTF-8'}"/>
                                                {else}
                                                    <img src = "{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/blockreviews/views/img/ico-star-grey.png" alt="{$smarty.section.bar.index|escape:'htmlall':'UTF-8'}" />
                                                {/if}
                                            {/section}
                                        {else}
                                            {section name=bar loop=5 start=0}
                                            <img src = "{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/blockreviews/views/img/ico-star-grey.png" alt="{$smarty.section.bar.index|escape:'htmlall':'UTF-8'}" />
                                        {/section}
                                        {/if}
                                        </span>

                                        {if $blockreviewsx_reviewsh == 1}
                                            <span class="count_reviews">
                                                <a href="{$review.product_link|escape:'htmlall':'UTF-8'}"
                                                   title="{$review.product_name|escape:'htmlall':'UTF-8'} - {$review.count_reviews|escape:'htmlall':'UTF-8'}"
                                                        >({$review.count_reviews|escape:'htmlall':'UTF-8'})</a>
                                            </span>
                                        {/if}

                                        <div class="clear"></div>

                                    </div>
                                    {/if}

                                </span>

                                <div class="font-size-11 float-left {if $blockreviewsbrev_sliderh == 0}review-block-text-home{/if}">
                                    {if $review.active == 1}
                                        {$review.text_review|substr:0:245|escape:'htmlall':'UTF-8'}
                                        {if strlen($review.text_review)>245}...{/if}
                                    {else}
                                        {l s='Review is pending moderation' mod='blockreviews'}
                                    {/if}
                                </div>
                                <div class="clear"></div>
                            </div>






                            <small class="float-right margin-right-5">{$review.date_add|date_format|escape:'htmlall':'UTF-8'}</small>

                            <div class="clear"></div>
                        </div>


                    {/foreach}




                    {*{if $blockreviewsbrev_sliderh == 1  && (count($blockreviewsreviews) > $blockreviewsbrev_slh)}</ul>{/if}*}
            {if $blockreviewsbrev_sliderh == 1}</ul>{/if}

                <div class="clear"></div>
                <div class="rev-view-all float-right">
                    <a href="{$blockreviewsrev_all|escape:'html':'UTF-8'}"
                       class="btn btn-default button button-small {if $blockreviewsis17 == 1}button-small-blockreviews{/if}"
                            >
                        <span>{l s='View All Reviews' mod='blockreviews'}</span>
                    </a>
                </div>
                <div class="clear"></div>


                </div>

	    	{else}
	    		<div class="padding-5 text-align-center">
					{l s='There are not Product Reviews yet.' mod='blockreviews'}
				</div>
	    	{/if}
	    </div>
</div>


    {literal}
    <script type="text/javascript">
        var blockreviews_number_home_reviews_slider = {/literal}{$blockreviewsbrev_slh|escape:'htmlall':'UTF-8'}{literal};
    </script>
    {/literal}

</div>

