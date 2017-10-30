{*
* 2007-2016 PrestaShop
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
*  @copyright  2007-2016 PrestaShop SA

*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div class="customer-service col-md-4">
    <h4>{l s='Notre service client' d='Modules.Socialfollow.Shop'}</h4>
    <p>
		{l s='Une question ?' d='Modules.Socialfollow.Shop'}<br>
        <span class="phone_number">05 57 10 41 41</span>
    </p>
    <p>
        <i class="fa fa-envelope-o"></i> <a href="mailto:noemie@la-vinotheque.com">noemie@la-vinotheque.com</a>
    </p>
</div>

{block name='ps_social_follow'}
  <div class="ps-social-follow col-md-4">
    <h4>{l s='Follow us' d='Modules.Socialfollow.Shop'}</h4>
    <ul>
      {foreach from=$social_links item='social_link'}
        <li class="{$social_link.class}">
          <a href="{$social_link.url}"><i class="fa fa-{if $social_link.class == 'googleplus'}google-plus{else}{$social_link.class}{/if}"></i></a>
        </li>
      {/foreach}
    </ul>
  </div>
{/block}
