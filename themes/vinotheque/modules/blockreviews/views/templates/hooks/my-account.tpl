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

    {if $blockreviewsis17 == 0}
        <li>
    {/if}


    <a href="{$blockreviewsaccount_url|escape:'htmlall':'UTF-8'}"
       title="{l s='Reviews' mod='blockreviews'}" {if $blockreviewsis17 == 1}class="col-lg-4 col-md-6 col-sm-6"{/if}>

        {if $blockreviewsis17 == 1}<span class="link-item">{/if}

            {if $blockreviewsis16 == 1}<i {if $blockreviewsis17 == 1}class="material-icons"{/if}>{/if}
                <img class="icon" src="{$base_dir_ssl|escape:'htmlall':'UTF-8'}modules/blockreviews/views/img/settings_reviews.gif" />
                {if $blockreviewsis16 == 1}</i>{/if}


            {if $blockreviewsis16 == 1 && $blockreviewsis17 == 0}<span>{/if}
                {l s='My Reviews' mod='blockreviews'}
                {if $blockreviewsis16 == 1 && $blockreviewsis17 == 0}</span>{/if}


            {if $blockreviewsis17 == 1}</span>{/if}

    </a>

    {if $blockreviewsis17 == 0}
        </li>
    {/if}

{/if}





