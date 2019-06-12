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


{if $blockreviewsrsson == 1}
<link rel="alternate" type="application/rss+xml" href="{$blockreviewsrss_url nofilter}" />
{/if}

{literal}
    <script type="text/javascript">
        //<![CDATA[

        {/literal}{if $blockreviewsis17 == 1}{literal}
        var baseDir = '{/literal}{$base_dir_ssl nofilter}{literal}';
        {/literal}{/if}{literal}

        var page_nav_ajax_url_blockreviews = '{/literal}{$blockreviewsajax_url nofilter}{literal}';
        //]]>
    </script>
{/literal}


{/if}