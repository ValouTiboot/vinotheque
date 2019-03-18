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
{extends file='checkout/_partials/steps/checkout-step.tpl'}

{block name='step_content'}
  <div id="hook-display-before-carrier">
    {$hookDisplayBeforeCarrier nofilter}
  </div>

  <div class="delivery-options-list">
    {if $delivery_options|count}
      <form
        id="js-delivery"
        data-url-update="{url entity='order' params=['ajax' => 1, 'action' => 'selectDeliveryOption']}"
        method="post"
      >
      <div class="form-fields">
        {block name='delivery_options'}
          <div class="delivery-options">
            {foreach from=$delivery_options item=carrier key=carrier_id}
              <div>
                <div class="delivery-option">
                  <span class="custom-radio">
                    <input type="radio" name="delivery_option[{$id_address}]" id="delivery_option_{$carrier.id}" value="{$carrier_id}"{if $delivery_option == $carrier_id} checked{/if}>
                    <span></span>
                  </span>
                  <label class="row" for="delivery_option_{$carrier.id}">
                    <div class="col-12 col-md-9">
                      <span class="carrier-name">{$carrier.name}</span>
                      <span class="carrier-delay">{$carrier.delay}</span>
                    </div>
                    <span class="col-12 col-md-3 carrier-prices">{$carrier.price}</span>
                  </label>
                  {$carrier.extraContent nofilter}
                </div>
              </div>
            {/foreach}
          </div>
        {/block}
        <div class="order-options">
          {*{if $recyclablePackAllowed}*}
            {*<label>*}
              {*<input type="checkbox" name="recyclable" value="1" {if $recyclable} checked {/if}>*}
              {*<span>{l s='I would like to receive my order in recycled packaging.' d='Shop.Theme.Checkout'}</span>*}
            {*</label>*}
          {*{/if}*}
          {if $gift.allowed}
            <div class="hidden">            
              <label>
                <input class="js-gift-checkbox" type="checkbox" name="gift" value="1" {if $gift.isGift} checked {/if}>
                <span>{$gift.label}</span>
              </label>
              <label for="gift_message">{l s='If you\'d like, you can add a note to the gift:' d='Shop.Theme.Checkout'}</label>
              <textarea rows="2" cols="120" id="gift_message" name="gift_message">{$gift.message}</textarea>
            </div>
          {/if}
        </div>
      </div>
      <div class="text-right">
          <button class="btn btn-primary" type="submit" class="continue" name="confirmDeliveryOption" value="1">
            {l s='Continue' d='Shop.Theme.Actions'}
          </button>
      </div>
      </form>
    {else}
      <p class="warning">
        {if isset($carrier_error) && $carrier_error == 'mixed_product'}
          {l s="Vous ne pouvez pas mélanger des produits traditionnels et des produits de type primeurs dans la même commande. Merci de modifier votre panier et de réessayer." d='Shop.Theme.Checkout'}
        {else if isset($carrier_error) && $carrier_error == 'quantity_error'}
          {l s="Dans votre panier, vous avez au moins un produit qui n'est disponible qu'en magasin, et au moins un produit qui n'est disponible qu'en vente en ligne, il est donc impossible d'avoir un transporteur commun. Merci de modifier votre panier et de réessayer." d='Shop.Theme.Checkout'}
        {else}
          {l s='Unfortunately, there are no carriers available for your delivery address.' d='Shop.Theme.Checkout'}
        {/if}
      </p>
    {/if}
  </div>

  <div id="hook-display-after-carrier">
    {$hookDisplayAfterCarrier nofilter}
  </div>

  <div id="extra_carrier"></div>
{/block}
