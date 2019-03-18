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
<div class="product-add-to-cart">
  {if !$configuration.is_catalog}
    {if $customer.is_logged || isset($product.is_private_sale_product) && !$product.is_private_sale_product}
      {block name='product_quantity'}
        <div class="product-quantity">
          <label for="quantity_wanted">{l s='Quantity' d='Shop.Theme.Catalog'}</label>
          <input
            type="text"
            name="qty"
            id="quantity_wanted"
            value="{$product.quantity_wanted}"
            class="input-group"
            min="{$product.minimal_quantity}"
          >
        </div>
      {/block}

      {block name='quantity_left'}
        <div class="quantity-left">
          {if $product.quantity < 12}
            <div class="alert alert-warning">        
              {l s='Only %quantity% quantity left online and %shop_quantity% quantity left on shop' d='Shop.Theme.Checkout' sprintf=['%quantity%' => $product.quantity, '%shop_quantity%' => $product.shop_quantity]}
            </div>
            <div class="border-white"></div>
          {/if}
        </div>
      {/block}

      {*{block name='product_minimal_quantity'}*}
        {*<p class="product-minimal-quantity">*}
            {*{if $product.minimal_quantity > 1}*}
                {*{l*}
                {*s='The minimum purchase order quantity for the product is %quantity%.'*}
                {*d='Shop.Theme.Checkout'*}
                {*sprintf=['%quantity%' => $product.minimal_quantity]*}
                {*}*}
            {*{/if}*}
        {*</p>*}
      {*{/block}*}

      {block name='product_variants'}
          {include file='catalog/_partials/product-variants.tpl'}
      {/block}
      <div class="add add-to-cart-block">
        <button class="add-to-cart btn btn-primary" type="submit" name="add" data-button-action="add-to-cart" {if !$product.add_to_cart_url || ($product.quantity < $product.quantity_wanted && $product.shop_quantity < $product.quantity_wanted)}disabled{/if}>
          {l s='Add to cart' d='Shop.Theme.Actions'}
        </button>
      </div>
    {else}
      <div class="add add-to-cart-block">
        <a class="add-to-cart btn btn-primary" href="{$link->getCMSLink(7)}">
          {l s='Private sales'}
        </a>
      </div>
    {/if}

  {/if}
</div>

{block name='hook_product_buttons'}
  {hook h='displayProductButtons' product=$product}
{/block}

<div id="stock_pictos">
  <div id="stock_pictos_qty"> {*if $product.quantity <= 0}style="display:none;"{/if*}
    <i class="icon-v-check-bubble"></i>{if $product.quantity <= 0}{l s='épuisé' d='Shop.Theme.Catalog'}{else}{l s='En stock' d='Shop.Theme.Catalog'}{/if}
  </div>
    {if $product.wine && $product.wine_delivery}
      <div>
         <i class="icon-v-truck"></i>{l s='Livraison estimée' d='Shop.Theme.Catalog'}: {dateFormat date=$product.wine_delivery}
      </div>
    {/if}

    <div id="stock_pictos_shop_qty" {if $product.shop_quantity <= 0}style="display:none;"{/if}>
      <i class="icon-v-check-bubble"></i>{l s='Disponible en magasin' d='Shop.Theme.Catalog'}
    </div>
</div>