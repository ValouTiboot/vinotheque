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
{if $blockreviewshooktodisplay == "product_footer"}




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


        {if $blockreviewsis17 == 1 && $blockreviewsajax == 1}
        {literal}
            <script type="text/javascript">
                jQuery(document).ready(init_rating);
            </script>
        {/literal}
        {/if}



{/if}




{/if}


