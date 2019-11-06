{**
 * 2007-2017 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2017 PrestaShop SA
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}
{block name='header_banner'}
  <div class="header-banner">
    {hook h='displayBanner'}
  </div>
{/block}

{block name='header_nav'}
  <div class="header-nav">
	  <div class="container">
		  <div class="row justify-content-center justify-content-md-end">
			{hook h='displayNav'}
		  </div>
	  </div>
  </div>
{/block}

{block name='header_top'}
  <div class="header-top" data-toggle="sticky-onscroll">
	  <div class="container">
		  <div class="row">
			{block name='header_logo'}
			  <div id="logo" class="col-lg-3 col-md-7 col-sm-12 col-12">
				  <a class="logo" href="{$urls.base_url}" title="{$shop.name}">
					  <img class="not-sticky" src="{$shop.logo}" alt="{$shop.name}">
					  <img class="yes-sticky" src="{$urls.img_url|escape:'html':'UTF-8'}logo_sticky.png" alt="{$shop.name}">
				  </a>
			  </div>
			{/block}
			{hook h='displayTop'}
  			{hook h='displayNavFullWidth'}
		  </div>
	  </div>
  </div>

{/block}
