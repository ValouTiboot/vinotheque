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
{extends file=$layout}

{block name='head_seo' prepend}
  <link rel="canonical" href="{$product.canonical_url}">
{/block}

{block name='head' append}
  <meta property="og:type" content="product">
  <meta property="og:url" content="{$urls.current_url}">
  <meta property="og:title" content="{$page.meta.title}">
  <meta property="og:site_name" content="{$shop.name}">
  <meta property="og:description" content="{$page.meta.description}">
  <meta property="og:image" content="{$product.cover.large.url}">
  <meta property="product:pretax_price:amount" content="{$product.price_tax_exc}">
  <meta property="product:pretax_price:currency" content="{$currency.iso_code}">
  <meta property="product:price:amount" content="{$product.price_amount}">
  <meta property="product:price:currency" content="{$currency.iso_code}">
  {if isset($product.weight) && ($product.weight != 0)}
    <meta property="product:weight:value" content="{$product.weight}">
    <meta property="product:weight:units" content="{$product.weight_unit}">
  {/if}
{/block}

{block name='content'}

  <section id="main" itemscope itemtype="https://schema.org/Product">
    <meta itemprop="url" content="{$product.url}">

    {block name='product_activation'}
      {include file='catalog/_partials/product-activation.tpl'}
    {/block}

    {block name='page_header_container'}{/block}

    {block name='page_content_container'}
      <section id="content" class="page-content">
        {block name='page_content'}
          {*{block name='product_flags'}*}
            {*<ul class="product-flags">*}
              {*{foreach from=$product.flags item=flag}*}
                {*<li>{$flag.label}</li>*}
              {*{/foreach}*}
            {*</ul>*}
          {*{/block}*}

          <div class="container">
              <div class="row">
                  <div class="col-md-4 col-12">
                    {block name='product_cover_thumbnails'}
                      {include file='catalog/_partials/product-cover-thumbnails.tpl'}
                    {/block}
                  </div>
                  <div class="col-md-8 col-12">
                      <div class="row">
                          <div class="col-12">
                              <h1 class="product-name" itemprop="name">{$product.name}</h1>
                          </div>
                      </div>
                      <div class="row">
                          <div class="col-xl-7 col-lg-6 col-12 product-information">
                            {*{block name='product_reference'}*}
                              {*{if $product.reference}*}
                                {*<p id="product-reference">*}
                                  {*<label>{l s='Reference' d='Shop.Theme.Catalog'}</label>*}
                                  {*<span itemprop="sku">{$product.reference}</span>*}
                                {*</p>*}
                              {*{/if}*}
                            {*{/block}*}

                            {*{block name='product_condition'}*}
                              {*{if $product.condition}*}
                                {*<p id="product-condition">*}
                                  {*<label>{l s='Condition' d='Shop.Theme.Catalog'}</label>*}
                                  {*<link itemprop="itemCondition" href="{$product.condition.schema_url}"/>*}
                                  {*<span>{$product.condition.label}</span>*}
                                {*</p>*}
                              {*{/if}*}
                            {*{/block}*}

                            {block name='product_description_short'}
                              <div id="product-description-short" itemprop="description">
                                {$product.description_short nofilter}
                                <br><a class="learn-more" href="#product-description">{l s='En savoir plus +' d='Shop.Theme.Actions'}</a>
                              </div>
                            {/block}

                            {block name='product_features_pictos'}
                            <div id="product-features-pictos">
                              {if $product.features}
                              <ul class="row">
                                {*Année*}
								  {if isset($product.features[6])}
                                      <li class="col-xl-4 col-sm-6">
                                          <div>
                                              <i class="icon-v-agenda"></i>
                                              <span>{$product.features[6].value}</span>
                                          </div>
                                      </li>
								  {/if}
                                {*Région*}
								  {if isset($product.features[11])}
                                      <li class="col-xl-4 col-sm-6">
                                          <div>
                                              <i class="icon-v-france"></i>
                                              <span>{$product.features[11].value}</span>
                                          </div>
                                      </li>
								  {/if}
                                {*Couleur*}
								  {if isset($product.features[5])}
                                      <li class="col-xl-4 col-sm-6">
                                          <div>
                                              <i class="icon-v-bottles"></i>
                                              <span>{$product.features[5].value}</span>
                                          </div>
                                      </li>
								  {/if}
                                {*Appellation*}
								  {if isset($product.features[2])}
                                      <li class="col-xl-4 col-sm-6">
                                          <div>
                                              <i class="icon-v-tag"></i>
                                              <span>{$product.features[2].value}</span>
                                          </div>
                                      </li>
								  {/if}
                                {*Degré*}
								  {if isset($product.features[13])}
                                      <li class="col-xl-4 col-sm-6">
                                          <div>
                                              <i class="icon-v-temperature"></i>
                                              <span>{$product.features[13].value}</span>
                                          </div>
                                      </li>
								  {/if}
                                {*Classification*}
								  {if isset($product.features[4])}
                                      <li class="col-xl-4 col-sm-6">
                                          <div>
                                              <i class="icon-v-reward"></i>
                                              <span>{$product.features[4].value}</span>
                                          </div>
                                      </li>
								  {/if}

                                {*{foreach from=$product.features item=feature}*}
                                    {*{if in_array($feature.name, array('Année','Région','Couleur','Appellation','Degré','Classification'))}*}
                                        {*<li class="col-xl-4 col-sm-6">*}
                                            {*<div>*}
                                                {*{if $feature.name == 'Année'}*}
                                                    {*<i class="icon-v-agenda"></i>*}
                                                {*{elseif $feature.name == 'Région'}*}
                                                    {*<i class="icon-v-france"></i>*}
                                                {*{elseif $feature.name == 'Couleur'}*}
                                                    {*<i class="icon-v-bottles"></i>*}
                                                {*{elseif $feature.name == 'Appellation'}*}
                                                    {*<i class="icon-v-tag"></i>*}
                                                {*{elseif $feature.name == 'Degré'}*}
                                                    {*<i class="icon-v-temperature"></i>*}
                                                {*{elseif $feature.name == 'Classification'}*}
                                                    {*<i class="icon-v-reward"></i>*}
                                                {*{/if}*}
                                                {*<span>{$feature.value}</span>*}
                                            {*</div>*}
                                        {*</li>*}
                                    {*{/if}*}
                                {*{/foreach}*}
                              </ul>
                              {/if}
                            </div>
                            {/block}

                            {*{block name='product_quantities'}*}
                              {*{if $product.show_quantities}*}
                                {*<p id="product-quantities">{$product.quantity} {$product.quantity_label}</p>*}
                              {*{/if}*}
                            {*{/block}*}

                            {block name='second_wine'}
                              {if $product.second_wine}
                                <div id="second_wine">
                                    <img
                                            src="{$product.second_wine.cover.bySize.large_default.url}"
                                            alt="{$product.second_wine.cover.legend}"
                                            title="{$product.second_wine.cover.legend}"
                                            width="65"
                                    >
                                    <div>
                                        {l s='Le second vin' d='Shop.Theme.Catalog'}<br>
                                        <a class="learn-more" href="{$product.second_wine.url}">{l s='En savoir plus +' d='Shop.Theme.Actions'}</a>
                                    </div>
                                </div>
                              {/if}
                            {/block}

                            {block name='product_availability'}
                              {*{if $product.show_availability}*}
                                {*<p id="product-availability">{$product.availability_message}</p>*}
                              {*{/if}*}
                            {/block}

                            {block name='product_availability_date'}
                              {if $product.availability_date}
                                <p id="product-availability-date">
                                  <label>{l s='Availability date:' d='Shop.Theme.Catalog'} </label>
                                  <span>{$product.availability_date}</span>
                                </p>
                              {/if}
                            {/block}

                            {block name='product_out_of_stock'}
                              <div class="product-out-of-stock">
                                {hook h='actionProductOutOfStock' product=$product}
                              </div>
                            {/block}
                          </div>
                          <div class="col-xl-5 col-lg-6 col-12 product-actions">
                            {if $product.category == 'primeurs'}
                                <div class="primeur">{$product.category_name}</div>
                            {/if}
                            {block name='product_buy'}
                              <form action="{$urls.pages.cart}" method="post" id="add-to-cart-or-refresh">
                                <input type="hidden" name="token" value="{$static_token}">
                                <input type="hidden" name="id_product" value="{$product.id}" id="product_page_product_id">
                                <input type="hidden" name="id_customization" value="{$product.id_customization}" id="product_customization_id">

                                {block name='product_prices'}
                                  {include file='catalog/_partials/product-prices.tpl'}
                                {/block}

                                {block name='product_add_to_cart'}
                                  {include file='catalog/_partials/product-add-to-cart.tpl'}
                                {/block}

                                {block name='hook_product_buttons'}
                                  {hook h='displayProductButtons' product=$product}
                                {/block}

                                <div id="stock_pictos">
                                  <div id="stock_pictos_quantity" {if $product.quantity <= 0}style="display:none;"{/if}>
                                    <i class="icon-v-check-bubble"></i>{l s='En stock' d='Shop.Theme.Catalog'}
                                  </div>
                                    {if $product.quantity <= 0}
                                      {*}
                                      <div>
									                     <i class="icon-v-truck"></i>{l s='Livraison estimée' d='Shop.Theme.Catalog'}: {$product.available_date}
                                      </div>
                                      {*}
                                    {/if}

                                    <div id="stock_pictos_shop_quantity" {if $product.shop_quantity <= 0}style="display:none;"{/if}>
								                      <i class="icon-v-check-bubble"></i>{l s='Disponible en magasin' d='Shop.Theme.Catalog'}
                                    </div>
                                </div>

                                {block name='hook_display_reassurance'}
                                  {hook h='displayReassurance'}
                                {/block}

                                {block name='product_refresh'}
                                  <input class="product-refresh ps-hidden-by-js" name="refresh" type="submit" value="{l s='Refresh' d='Shop.Theme.Actions'}">
                                {/block}
                              </form>
                            {/block}

                            {block name='product_additional_info'}
                              {include file='catalog/_partials/product-additional-info.tpl'}
                            {/block}
                          </div>
                      </div>
                  </div>
              </div>

              {*{block name='product_discounts'}*}
                {*{include file='catalog/_partials/product-discounts.tpl'}*}
              {*{/block}*}

              {if $product.is_customizable && count($product.customizations.fields)}
                {block name='product_customization'}
                  {include file='catalog/_partials/product-customization.tpl' customizations=$product.customizations}
                {/block}
              {/if}

              {block name='product_features'}
                {if $product.features}
                  <section class="product-features">
                    <ul class="row">
                      {foreach from=$product.features item=feature}
                        <li class="col-md-6 col-12">
                            <div class="row">
                                <div class="product-features-name col-xl-3 col-lg-4 col-6">{$feature.name}</div>
                                <div class="product-features-value col-xl-9 col-lg-8 col-6">{$feature.value}</div>
                            </div>
                        </li>
                      {/foreach}
                    </ul>
                  </section>
                {/if}
              {/block}
          </div>

          {if $product.description}
              <div class="container-fluid product-mets-vins">
                    <div class="container">
                        <h3>{l s='Notes de dégustation' d='Shop.Theme.Catalog'}</h3>
                        <div id="product-description" class="block-degustation">
                            {$product.description nofilter}
                        </div>
                        <div class="block-mets-vins">
                            <h3>{l s='Accords mets vins' d='Shop.Theme.Catalog'}</h3>
                            <div class="row justify-content-center">
                                {hook h='displayProductFoodPictos' mod='foodandwine' product=$product}
                            </div>
                        </div>
                    </div>
              </div>
          {/if}

          <div class="container">
              {block name='product_property'}
                {if !empty($product.property)}
                  <div id="product-property">
                    <h3>{l s='La propriété' d='Shop.Theme.Catalog'}</h3>
                    <div class="row">
                      <div class="col-4">
                          <img src="{$product.property_picture}">
                      </div>
                      <div class="col-8">
                        <div>{$product.property nofilter}</div>
                          <a class="btn btn-secondary" href="#">{l s='Voir tous les vins de cette propriété' d='Shop.Theme.Catalog'}</a>
                      </div>
                    </div>
                  </div>
                {/if}
              {/block}

              {block name='product_calling'}
              {if !empty($product.calling)}
                <div id="product-calling">
                  <h3>{l s='L\'appellation' d='Shop.Theme.Catalog'}</h3>
                  <div class="row">
                    <div class="col-4">
                        <img class="appellation-image" src="{$product.calling_picture_small}">
                        <div class="layer hidden-sm-down" data-toggle="modal" data-target="#appellation-modal">
                            <i class="material-icons zoom-in">&#xE8FF;</i>
                        </div>
                        <div class="modal fade js-product-images-modal" id="appellation-modal">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-body">
										{assign var=imagesCount value=$product.images|count}
                                        <figure>
                                            <img width="100%" src="{$product.calling_picture_big}">
                                        </figure>
                                    </div>
                                </div><!-- /.modal-content -->
                            </div><!-- /.modal-dialog -->
                        </div><!-- /.modal -->
                    </div>
                    <div class="col-8">{$product.calling nofilter}</div>
                  </div>
                </div>
              {/if}
              {/block}

              {block name='product_pack'}
                {if $packItems}
                  <section class="product-pack">
                    <h3>{l s='Pack content' d='Shop.Theme.Catalog'}</h3>
                    {foreach from=$packItems item="product_pack"}
                      {block name='product_pack_miniature'}
                        {include file='catalog/_partials/pack-product.tpl' product=$product_pack}
                      {/block}
                    {/foreach}
                  </section>
                {/if}
              {/block}

              {block name='product_accessories'}
                {if $product.accessories}
                  <section class="product-accessories row">
                    <h3 class="col-12 page_title">{l s='Vous aimerez aussi' d='Shop.Theme.Catalog'}</h3>
                    {foreach from=$product.accessories item="product_accessory"}
                      {block name='product_miniature'}
                        {include file='catalog/_partials/miniatures/product.tpl' product=$product_accessory}
                      {/block}
                    {/foreach}
                  </section>
                {/if}
              {/block}
          </div>

          {block name='product_footer'}
            {hook h='displayFooterProduct' product=$product category=$category}
          {/block}

          {block name='product_images_modal'}
            {include file='catalog/_partials/product-images-modal.tpl'}
          {/block}

          {block name='product_attachments'}
            {if $product.attachments}
              <section class="product-attachments">
                <h3>{l s='Download' d='Shop.Theme.Actions'}</h3>
                {foreach from=$product.attachments item=attachment}
                  <div class="attachment">
                    <h4>
                      <a href="{url entity='attachment' params=['id_attachment' => $attachment.id_attachment]}">
                        {$attachment.name}
                      </a>
                    </h4>
                    <p>{$attachment.description}</p>
                    <a href="{url entity='attachment' params=['id_attachment' => $attachment.id_attachment]}">
                      {l s='Download' d='Shop.Theme.Actions'} ({$attachment.file_size_formatted})
                    </a>
                  </div>
                {/foreach}
              </section>
            {/if}
          {/block}
          {foreach from=$product.extraContent item=extra key=extraKey}
            <div class="{$extra.attr.class}" id="extra-{$extraKey}">
              {$extra.content nofilter}
            </div>
          {/foreach}
        {/block}
      </section>
    {/block}

    {block name='page_footer_container'}
      <footer class="page-footer container-fluid">
        {block name='page_footer'}
          <div id="footer_links" class="row">
              <div class="container">
                  <div class="row justify-content-center">
                      <div class="col-xl-2 col-lg-3 col-6 partage"><a href="#"> <i class="icon-v-users"></i><br /> Parrainage </a></div>
                      <div class="col-xl-2 col-lg-3 col-6 partage"><a href="#"> <i class="icon-v-gift"></i><br /> Chèques cadeaux </a></div>
                      <div class="col-xl-2 col-lg-3 col-6 partage"><a href="#"> <i class="icon-v-bottle"></i><br /> Fidélité </a></div>
                      <div class="col-xl-2 col-lg-3 col-6 partage"><a href="#"> <i class="icon-v-gifts"></i><br /> Cadeaux d'entreprises </a></div>
                  </div>
              </div>
          </div>
        {/block}
      </footer>
    {/block}

  </section>

{/block}
