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
{block name='hook_footer_before'}
    <div id="footer_before_responsive">
        <div class="container">
            <div id="footer_before" class="row">
              {hook h='displayFooterBefore'}
                <div class="col-md-6 col-lg-2 reassurance reassurance_footer">
                    <img src="{$urls.img_url|escape:'html':'UTF-8'}selection_ft.png" alt="{l s='La sélection' d='Shop.Theme'}">
                    <p class="reassurance_title">{l s='La sélection' d='Shop.Theme'}</p>
                    <p>{l s='Les vins sont dégustés et sélectionnés avec beaucoup de rigueur.' d='Shop.Theme'}</p>
                </div>
                <div class="col-md-6 col-lg-2 reassurance reassurance_footer">
                    <img src="{$urls.img_url|escape:'html':'UTF-8'}conseil_ft.png" alt="{l s='Le conseil' d='Shop.Theme'}">
                    <p class="reassurance_title">{l s='Le conseil' d='Shop.Theme'}</p>
                    <p>{l s='Nous sommes à votre écoute au' d='Shop.Theme'} <b>05 57 10 41 41</b>.</p>
                </div>
                <div class="col-md-6 col-lg-2 reassurance reassurance_footer">
                    <img src="{$urls.img_url|escape:'html':'UTF-8'}livraison_ft.png" alt="{l s='Une livraison sur-mesure' d='Shop.Theme'}">
                    <p class="reassurance_title">{l s='Une livraison sur-mesure' d='Shop.Theme'}</p>
                    <p>{l s='Plusieurs modes de livraison selon vos besoins.' d='Shop.Theme'}</p>
                </div>
                <div class="col-md-6 col-lg-2 reassurance reassurance_footer">
                    <img src="{$urls.img_url|escape:'html':'UTF-8'}paiement_ft.png" alt="{l s='Paiement 100 sécurisé' d='Shop.Theme'}">
                    <p class="reassurance_title">{l s='Paiement 100 sécurisé' d='Shop.Theme'}</p>
                    <p>{l s='Réglez vos achats en toute sérénité par carte bancaire.' d='Shop.Theme'}</p>
                </div>
                <div class="col-md-6 col-lg-2 reassurance reassurance_footer">
                    <img src="{$urls.img_url|escape:'html':'UTF-8'}satisfaction_ft.png" alt="{l s='Satisfait ?' d='Shop.Theme'}">
                    <p class="reassurance_title">{l s='Satisfait ?' d='Shop.Theme'}</p>
                    <p>{l s='Si votre commande ne vous convient pas, vous pouvez nous la retourner' d='Shop.Theme'}</p>
                </div>
                <div class="col-md-6 col-lg-2 reassurance reassurance_footer">
                    <img src="{$urls.img_url|escape:'html':'UTF-8'}fidelite_ft.png" alt="{l s='Programme fidélité' d='Shop.Theme'}">
                    <p class="reassurance_title">{l s='Programme fidélité' d='Shop.Theme'}</p>
                    <p>{l s='Convertissez vos points fidélité en bon d\'achat.' d='Shop.Theme'}</p>
                </div>
            </div>
        </div>
    </div>
{/block}

{block name='hook_footer'}
    <div id="footer_content_responsive">
        <div id="footer_content_bg">
            <div class="container">
                <div id="footer_content" class="row">
                    {hook h='displayFooter'}
                </div>
            </div>
        </div>
    </div>
{/block}

{block name='hook_footer_after'}
    <div id="footer_after_responsive">
        <div class="container">
            <div id="footer_after" class="row">
                {hook h='displayFooterAfter'}
            </div>
        </div>
    </div>
{/block}

<div id="footer_copyright" class="container">
  <div class="row">
    <div class="col-lg-6 text-center text-md-left mb-1 pr-0">
        {l s='Moyens de paiement' d='Shop.Theme'} <img src="{$urls.img_url|escape:'html':'UTF-8'}moyens-de-paiement-1.png" alt="{l s='Moyens de paiement' d='Shop.Theme'}" title="{l s='Moyens de paiement' d='Shop.Theme'}"><img src="{$urls.img_url|escape:'html':'UTF-8'}moyens-de-paiement-2.png" alt="{l s='Moyens de paiement' d='Shop.Theme'}" title="{l s='Moyens de paiement' d='Shop.Theme'}">
    </div>
    <div class="col-lg-6 text-center text-md-right pl-0">
      {block name='copyright_link'}
        {l s='%copyright% La Vinothèque de Bordeaux - %year%' sprintf=['%year%' => 'Y'|date, '%copyright%' => '©'] d='Shop.Theme'} |
        <a class="_blank" href="{$link->getCMSLink('2')}" target="_blank">
            {l s='Mentions légales' d='Shop.Theme'}
        </a> |
        <a target="_blank" rel="nofollow" href="https://www.yateo.com/" class="_blank">
            {l s='site fait par YATEO' d='Shop.Theme'}
        </a>
      {/block}
    </div>
  </div>
</div>

<a id="back-to-top" href="#" class="btn btn-primary back-to-top">
    <i class="material-icons">expand_less</i>
</a>
