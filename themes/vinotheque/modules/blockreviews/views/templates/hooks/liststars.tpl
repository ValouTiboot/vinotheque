{*
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

*}

{if ($blockreviewsis_starscat == 1 && $count_review > 0) || ($blockreviewsis_starscat == 0)}
<div class="clear"></div>
<div class="reviews_list_stars">
    <span class="star_content clearfix">
        {section name=ratid loop=5}
            {if $smarty.section.ratid.index < $avg_rating}
            <img src="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/blockreviews/views/img/rstar1.png" class="img-star-category"
                alt="{$smarty.section.ratid.index|escape:'htmlall':'UTF-8'}"/>
            {else}
            <img src="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/blockreviews/views/img/rstar2.png" class="img-star-category"
                alt="{$smarty.section.ratid.index|escape:'htmlall':'UTF-8'}"/>
            {/if}
        {/section}
        <span class="count-rev-lists margin-left-5">({$count_review|escape:'htmlall':'UTF-8'})</span>
    </span>

</div>
{/if}


