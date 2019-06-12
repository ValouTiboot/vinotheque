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

{if $blockreviewsptabs_type == 2 || $blockreviewsptabs_type == 3}
<li {if $blockreviewsptabs_type == 3}class="nav-item"{/if}>
	<a id="idTab666-my" href="#idTab666" data-toggle="tab" class="idTabHrefShort {if $blockreviewsptabs_type == 3}nav-link{/if}">{l s='Reviews' mod='blockreviews'} <span id="count-review-tab">({$nbReviews|escape:'htmlall':'UTF-8'})</span></a>
</li>
{/if}
