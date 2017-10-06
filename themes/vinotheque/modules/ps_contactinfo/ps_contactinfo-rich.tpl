{*
* 2007-2017 PrestaShop
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

<div class="contact-rich col-xs-12 col-sm-4">
  <h4>{l s='Notre service client' d='Shop.Theme'}</h4>
  {if $contact_infos.phone}
    <div class="phone">
      {l s='Une question ?' d='Shop.Theme'}<br>
      <a class="phone_number" href="tel:{$contact_infos.phone}">{$contact_infos.phone}</a>
      <br>
      <a href="">{l s='Ou voir notre faq' d='Shop.Theme'} ></a>
    </div>
  {/if}
  <img src="{$urls.img_url|escape:'html':'UTF-8'}magasin.png" alt="{l s='Notre magasin' d='Shop.Theme'}">
  <h4>{l s='Notre magasin' d='Shop.Theme'}</h4>
  <div>
    <i class="icon-v-map"></i>
	  {$contact_infos.address.address1} {$contact_infos.address.postcode} {$contact_infos.address.city}<br>
	  {$contact_infos.phone}
  </div>
  <p>
	{l s='Ouvert du Lundi au Samedi de 10h00 à 19h30 et le Dimanche de 11h00 à 19h30 sans interruption' d='Modules.Contactinfo.Shop'}
  </p>
  <a href="">{l s='En savoir +' d='Shop.Theme'}</a>
</div>
