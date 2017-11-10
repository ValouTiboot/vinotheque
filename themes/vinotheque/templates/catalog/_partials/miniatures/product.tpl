{block name='product_miniature_item'}
  <article class="col-sm-6 col-lg-{if isset($lg)}{$lg}{else}3{/if} product-miniature" data-id-product="{$product.id_product}" data-id-product-attribute="{$product.id_product_attribute}" itemscope itemtype="http://schema.org/Product">
    <div>
        {block name='product_thumbnail'}
            <div class="product-thumbnail-box">
                <a href="{$product.url}" class="thumbnail product-thumbnail">
                    <div>
                        <img
                          src = "{if $product.cover}{$product.cover.medium.url}{else}{$link->getImageLink($product.link_rewrite, 'fr-default', 'medium_default')|escape:'htmlall':'UTF-8'}{/if}"
                          alt = "{$product.cover.legend}"
                          data-full-size-image-url = "{$product.cover.large.url}"
                        >
                    </div>
                </a>
                {hook h='displayProductPictos' mod='pictogram' product=$product}
				{block name='product_list_actions'}
                    <div class="product-list-actions">
                        {if $product.add_to_cart_url}
                            <a class = "know-more" href="{$product.url}">{l s='En savoir plus' d='Shop.Theme.Actions'}</a>
                            <br>
                            <a
                            class = "add-to-cart"
                            href  = "{$product.add_to_cart_url}"
                            rel   = "nofollow"
                            data-id-product="{$product.id_product}"
                            data-id-product-attribute="{$product.id_product_attribute}"
                            data-link-action="add-to-cart"
                            ><i class="icon-v-cart"></i></a>
                        {/if}
                        {hook h='displayProductListFunctionalButtons' product=$product}
                        <div id="product-attributes-list" class="row justify-content-center">
                          {if isset($product.attributes)}
                          {foreach from=$product.attributes item=attribute}
                            {if $attribute.group == 'Format'}
                              <div class="col-lg-4">
                                  <div>
								    {$attribute.name}
                                  </div>
                              </div>
                            {/if}
                          {/foreach}
                          {/if}
                        </div>
                    </div>
				{/block}
            </div>
        {/block}

        {block name='category_name'}
          <div class="category-name">{$product.category_name}</div>
        {/block}

        {block name='product_name'}
            <div class="name">
                <h1 itemprop="name"><a href="{$product.url}">{$product.name}</a></h1>
                <span class="feature-name">
                    {if isset($product.features[5].value) && isset($product.features[6].value)}
                        {$product.features[5].value} - {$product.features[6].value}
                    {/if}
                </span>
            </div>
        {/block}

        {block name='product_quick_view'}
        {/block}

        {block name='product_price_and_shipping'}
          {if $product.show_price}
            <div class="product-price-and-shipping">
              {if $product.has_discount}
                {hook h='displayProductPriceBlock' product=$product type="old_price"}
              {/if}

              {hook h='displayProductPriceBlock' product=$product type="before_price"}

              {l s='A partir de' d='Shop.Theme.Actions'}{if $product.has_discount} <span class="regular-price">{$product.regular_price}</span>{/if} <span itemprop="price" class="price">{$product.price}</span>

              {hook h='displayProductPriceBlock' product=$product type="unit_price"}

              {hook h='displayProductPriceBlock' product=$product type="weight"}
            </div>
          {/if}
        {/block}

        {block name='product_flags'}
          <ul class="product-flags">
            {if $product.has_discount}
              {if $product.discount_type === 'percentage'}
                  <li class="discount-percentage">{$product.discount_percentage}</li>
                  <br>
              {/if}
            {/if}
            {foreach from=$product.flags item=flag}
              <li class="{$flag.type}">{$flag.label}</li>
                <br>
            {/foreach}
          </ul>
        {/block}

        {*{block name='product_availability'}*}
          {*{if $product.show_availability}*}
            {* availability may take the values "available" or "unavailable" *}
            {*<span class='product-availability {$product.availability}'>{$product.availability_message}</span>*}
          {*{/if}*}
        {*{/block}*}

        {hook h='displayProductListReviews' product=$product}

    </div>
  </article>
{/block}
