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

{if $blockreviewsreviewson == 1}

{if $blockreviewsposition == "left"}



<div class="block
            {if $blockreviewsbrev_slider == 1}owl_blockreviews_reviews_type_carousel{/if}
            blockmanufacturer {if $blockreviewsis17 == 1}block-categories hidden-sm-down{/if}" >
		<h4 class="title_block {if $blockreviewsis14 == 0}margin-bottom-5{/if} {if $blockreviewsis17 == 1}text-uppercase{/if}">
			{l s='Last Product Reviews' mod='blockreviews'}
			
			{if $blockreviewsrsson == 1}
				<a class="margin-left-5" href="{$blockreviewsrss_url|escape:'htmlall':'UTF-8'}" title="{l s='RSS Feed' mod='blockreviews'}" target="_blank">
					<img src="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/blockreviews/views/img/feed.png" alt="{l s='RSS Feed' mod='blockreviews'}" />
				</a>
			{/if}
		</h4>
		<div class="block_content block-items-data">
			{if count($blockreviewsreviews)>0}

                <div class="blockreviews-articles-block">


                    {if $blockreviewsbrev_slider == 1 && (count($blockreviewsreviews) > $blockreviewsbrev_sl)}<ul class="owl-carousel owl-theme">{/if}

                    {foreach from=$blockreviewsreviews item=review name=myLoop1}


                        {if $blockreviewsbrev_slider == 1}

                            {if ($smarty.foreach.myLoop1.index % $blockreviewsbrev_sl == 0) || $smarty.foreach.myLoop1.first}
                                <div>
                            {/if}

                        {/if}

                            <div class="blockreviews-item-block">

                            <div class="rItem">

                                <div class="{if $blockreviewsis16 == 1}avatar-block{else}avatar-block15{/if}">
                                    <a href="{$review.product_link|escape:'htmlall':'UTF-8'}"
                                       title="{$review.product_name|escape:'htmlall':'UTF-8'}"
                                            >
                                        <img src="{$review.product_img|escape:'htmlall':'UTF-8'}" title="{$review.product_name|escape:'htmlall':'UTF-8'}"
                                             alt = "{$review.product_name|escape:'htmlall':'UTF-8'}" class="img-responsive-custom border-image-review" />
                                    </a>
                                </div>

                                <span class="float-right margin-bottom-5 title-block-review {if $blockreviewsis16 == 1}review-block-text{else}review-block-text15{/if}">
                                    <a href="{$review.product_link|escape:'htmlall':'UTF-8'}"
                                       title="{$review.product_name|escape:'htmlall':'UTF-8'}"
                                            >
                                        {$review.product_name|escape:'htmlall':'UTF-8'}
                                    </a>
                                </span>
                                {if $blockreviewsis16 == 1}<div class="clear"></div>{/if}

                                <div class="margin-bottom-5">

                                    <div class="font-size-11 float-left {if $blockreviewsis16 == 1}review-block-text{else}review-block-text15{/if}">
                                        {if $review.active == 1}
                                            {$review.text_review|substr:0:100|escape:'htmlall':'UTF-8'}
                                            {if strlen($review.text_review)>100}...{/if}
                                        {else}
                                            {l s='Review is pending moderation' mod='blockreviews'}
                                        {/if}

                                    </div>
                                    <div class="clear"></div>
                                </div>

                                <small class="float-left">{$review.date_add|date_format|escape:'htmlall':'UTF-8'}</small>
                                {if $review.active == 1}
                                    <div class="ratingBox float-right">

                                    <span class="float-left margin-right-5">
                                    {if $review.rating != 0}
                                        {section name=bar loop=5 start=0}
                                            {if $smarty.section.bar.index < $review.rating}
                                                <img src = "{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/blockreviews/views/img/ico-star.png"  alt="{$smarty.section.bar.index|escape:'htmlall':'UTF-8'}" />
                                            {else}
                                                <img src = "{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/blockreviews/views/img/ico-star-grey.png"  alt="{$smarty.section.bar.index|escape:'htmlall':'UTF-8'}" />
                                            {/if}
                                        {/section}
                                    {else}
                                        {section name=bar loop=5 start=0}
                                        <img src = "{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/blockreviews/views/img/ico-star-grey.png"  alt="{$smarty.section.bar.index|escape:'htmlall':'UTF-8'}" />
                                    {/section}
                                    {/if}
                                    </span>

                                        {if $blockreviewsx_reviews == 1}
                                            <span class="count_reviews">
                                        <a href="{$review.product_link|escape:'htmlall':'UTF-8'}"
                                           title="{$review.product_name|escape:'htmlall':'UTF-8'} - {$review.count_reviews|escape:'htmlall':'UTF-8'}"
                                                >({$review.count_reviews|escape:'htmlall':'UTF-8'})</a>
                                    </span>
                                        {/if}

                                    </div>
                                {/if}

                                <div class="clear"></div>
                            </div>

                                {if $blockreviewsbrev_slider == 1}

                                {if ($smarty.foreach.myLoop1.index % $blockreviewsbrev_sl == $blockreviewsbrev_sl - 1) || $smarty.foreach.myLoop1.last}
                                    </div>
                                {/if}

                                {/if}

                            </div>





                        {/foreach}

                        {if $blockreviewsbrev_slider == 1 && (count($blockreviewsreviews) > $blockreviewsbrev_sl)}</ul>{/if}

                    <div class="text-align-center margin-top-15">
                        <a href="{$blockreviewsrev_all|escape:'htmlall':'UTF-8'}" class="btn btn-default button button-small {if $blockreviewsis17 == 1}button-small-blockreviews{/if} {if $blockreviewsis14 == 1}margin-0-auto{/if}"
                           title="{l s='View All Reviews' mod='blockreviews'}"><span>{l s='View All Reviews' mod='blockreviews'}</span></a>
                    </div>

                </div>



	    	{else}
	    		<div class="padding-5 text-align-center">
					{l s='There are not Product Reviews yet.' mod='blockreviews'}
				</div>
	    	{/if}
	    </div>
</div>



{/if}

{/if}



