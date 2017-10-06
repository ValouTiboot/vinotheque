{*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2017 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<!-- MODULE referralbyphone -->
<div id="ref_link">
    <a class="fancybox" href="#ref_link_block">
    {if $referralbyphoneis17 == 1}
    <i class="material-icons">supervisor_account</i>
    {else}
    <i class="icon-group"></i>
    {/if}
    
    {l s='Share Sponsor Link'  mod='referralbyphone'}</a>
</div>
<div style="display:none">
    <div id="ref_link_block">{l s='Copy & Share Your Custom Sponsor Link'  mod='referralbyphone'}<br/>{$ref_link|escape:'htmlall':'UTF-8'}</div>
</div>
<!-- END : MODULE referralbyphone -->