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

{foreach from=$reviews item=review}
    <div>
        <table class="prfb-table-reviews">
            <tr>
                <td class="prfb-left">
                    <div class="prfb-name">{$review.customer_name|escape:'html':'UTF-8'}</div>
                    <br/>
                    {if $blockreviewsipon == 1}
                        {if $review.active == 1}
                            {if strlen($review.ip) > 0}
                                <span class="prfb-time">{l s='IP:' mod='blockreviews'} {$review.ip|escape:'htmlall':'UTF-8'}</span>
                                <br/>
                                <br/>
                            {/if}
                        {/if}
                    {/if}
                    <span class="prfb-time">{$review.date_add|date_format|escape:'htmlall':'UTF-8'}</span>


                    {if $review.active == 1}
                        <br/>

                        <div class="text-align-center rating-total-for-item">
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

{literal}
<script type="text/javascript">
        jQuery(document).ready(init_rating);

</script>
{/literal}