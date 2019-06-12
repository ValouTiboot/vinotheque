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

{if $blockreviewsid_customer != 0}

<li {if $blockreviewsis_ps15 == 0}style="background: url('{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/blockreviews/views/img/settings_reviews.gif')  no-repeat scroll 0 0 transparent;"{/if}>
	
	<a href="{$blockreviewsaccount_url|escape:'htmlall':'UTF-8'}"
	   title="{l s='Reviews' mod='blockreviews'}">
	   <img class="icon" src="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/blockreviews/views/img/settings_reviews.gif" />
	   	{l s='My Reviews' mod='blockreviews'}
	  </a> 
</li>

{/if}
