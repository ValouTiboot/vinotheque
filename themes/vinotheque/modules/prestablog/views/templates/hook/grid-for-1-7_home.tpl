<!-- Pour les url et image proceder comme ci dessous -->
<div class="col-xs-12">
    {*<pre>{$RecipeNews|@print_r}</pre>*}
</div>

<!-- Accords mets vins -->
<div class="container-fluid">
    <div id="prestablog_accord" class="row justify-content-center">
        <div class="col-lg-12 page_title">
			{l s='Accords mets vins' mod='prestablog'}
        </div>
        <div class="col-lg-6">
            <div id="prestablog_met_top"></div>
            <div id="prestablog_met" class="container">
                <div class="row">
                    <div class="col-lg-7">
                        <img src="{$prestablog_theme_upimg|escape:'html':'UTF-8'}slide_{$RecipeNews.id_prestablog_news|intval}.jpg?{$md5pic|escape:'htmlall':'UTF-8'}" class="visu" alt="{$RecipeNews.title|escape:'htmlall':'UTF-8'}" title="{$RecipeNews.title|escape:'htmlall':'UTF-8'}" />
                        <div class="triangle"></div>
                    </div>
                    <div id="recette" class="col-lg-5">
                        <p id="recette_title">{$RecipeNews.title}</p>
                        <div id="recette_content">
                            {PrestaBlogContent return=$RecipeNews.content}<br>
                        </div>
                        <a id="read_more" href="{PrestaBlogUrl id=$RecipeNews.id_prestablog_news seo=$RecipeNews.link_rewrite titre=$RecipeNews.title}">
                            {l s='Lire la suite' mod='prestablog'}
                        </a>
                    </div>
                </div>
            </div>
        </div>
        {if isset($RecipeNews.product)}
            <div id="prestablog_plus" class="col-lg-1">+</div>
            <div class="col-lg-4">
                <div id="prestablog_vin_top"></div>
                <div id="prestablog_vin">
                    {foreach from=$RecipeNews.product item="product"}
                        <article class="product-miniature" data-id-product="{$product.id_product}" data-id-product-attribute="{$product.cache_default_attribute}" itemscope itemtype="http://schema.org/Product">
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
                                            </div>
                                        {/block}
                                    </div>
                                {/block}

                                {block name='category_name'}
                                {$product.last_cat|var_dump}
                                  <div class="category-name">{if !empty($product.last_cat)}{$product.last_cat.name}{else}{$product.category_name}{/if}</div>
                                {/block}

                                {block name='product_name'}
                                    <div class="name">
                                        <h1 itemprop="name"><a href="{$product.url}">{$product.name}</a></h1>
                                        <span class="feature-name">
                                            {if isset($product.features[3].value) && isset($product.features[1].value)}
                                                {$product.features[3].value} - {$product.features[1].value}
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

                                {block name='product_availability'}
                                    {if $product.show_availability}
                                    <span class='product-availability {$product.availability}'>{$product.availability_message}</span>
                                    {/if}
                                {/block}
                                {hook h='displayProductListReviews' product=$product}
                            </div>
                        </article>
                    {/foreach}
                </div>
            </div>
		{/if}
    </div>
</div>
<div class="clearfix"></div>
<!-- /Accords mets vins -->

<!-- La vinothèque de Bordeaux -->
<div id="vinotheque">
    <div class="row">
        <div id="vinotheque_infos" class="col-lg-6">
            <p>
                <span class="page_title">{l s='La vinothèque de Bordeaux' mod='prestablog'}</span>
            </p>
            <p>
                <span class="page_subtitle">{l s='un caviste emblématique au cœur de la ville' mod='prestablog'}</span>
            </p>
            <p id="vinotheque_content">
                La Vinothèque de Bordeaux ouvre ses portes en 1973 sur une idée originale d’une maison bordelaise de haute renommée. Située aux abords du Grand Théâtre, du Conseil Interprofessionnel du Vin de Bordeaux, du Grand Hôtel et de l’Office de Tourisme, elle devient le caviste emblématique du centre ville. En 2007, la maison Dubos Frères & Cie, l’un des piliers du négoce bordelais, acquiert la Vinothèque de Bordeaux et lance l’activité de vente en ligne.
            </p>
        </div>
        <div id="vinotheque_photo_1" class="col-lg-6">
            <img src="{$urls.img_url|escape:'html':'UTF-8'}vinotheque1.jpg" alt="">
        </div>
    </div>
    <div class="row">
        <div id="vinotheque_photo_2" class="col-lg-6">
            <div class="row">
                <div class="col-lg-9">
                    <img src="{$urls.img_url|escape:'html':'UTF-8'}vinotheque2.jpg" alt="">
                </div>
                <div class="col-lg-3">
                    <div class="vinotheque_partage">
                        <a href="{$link->getCMSLink(13)|escape:'htmlall':'UTF-8'}">
                            <i class="icon-v-users"></i><br>
                            {l s='Parrainage' mod='prestablog'}
                        </a>
                    </div>
                    <div class="vinotheque_partage">
                        <a href="{$link->getCMSLink(12)|escape:'htmlall':'UTF-8'}">
                            <i class="icon-v-bottle"></i><br>
                            {l s='Fidélité' mod='prestablog'}
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div id="vinotheque_photo_3" class="col-lg-6">
            <div class="row">
                <div class="col-lg-3">
                    <div class="vinotheque_partage">
                        <a href="{$link->getCMSLink(14)|escape:'htmlall':'UTF-8'}">
                            <i class="icon-v-gift"></i><br>
                            {l s='Cartes' mod='prestablog'}<br>
                            {l s='cadeaux' mod='prestablog'}
                        </a>
                    </div>
                    <div class="vinotheque_partage">
                        <a href="{$link->getCMSLink(15)|escape:'htmlall':'UTF-8'}">
                            <i class="icon-v-gifts"></i><br>
                            {l s='Cadeaux d\'entreprises' mod='prestablog'}
                        </a>
                    </div>
                </div>
                <div id="vinotheque_volonte" class="col-lg-9">
                    <img src="{$urls.img_url|escape:'html':'UTF-8'}vinotheque3.jpg" alt="">
                    <div>
                        <span>Notre volonté : </span>offrir à tous les amateurs de vins la qualité et la richesse de notre sélection ainsi que notre savoir-faire de caviste réputé...
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="clearfix"></div>
<!-- /La vinothèque de Bordeaux -->

<!-- Module Presta Blog -->
<div class="container-fluid">
    <div class="prestablog_slide row">
        <div class="col-lg-12 page_title">
            {l s='Actualités' mod='prestablog'}
        </div>
        <div id="prestablog_slides" class="container">
            <div class="row">
                {foreach from=$ListeBlogNews item=slide name=slides}
					{if $smarty.foreach.slides.index == 2}
						{break}
					{/if}
                    <div class="col-lg-6">
                        <a href="{PrestaBlogUrl id=$slide.id_prestablog_news seo=$slide.link_rewrite titre=$slide.title}">
                            <img src="{$prestablog_theme_upimg|escape:'html':'UTF-8'}slide_{$slide.id_prestablog_news|intval}.jpg?{$md5pic|escape:'htmlall':'UTF-8'}" class="visu" alt="{$slide.title|escape:'htmlall':'UTF-8'}" title="{$slide.title|escape:'htmlall':'UTF-8'}" />
                            <span class="prestablog_title">{$slide.title}</span>
                        </a>
                        <div class="prestablog_paragraph">
							{$slide.paragraph}
                        </div>
                    </div>
                {foreachelse}
                    {l s='No result found' mod='prestablog'}
                {/foreach}
            </div>
        </div>
        <div id="prestablog_seeall" class="col-lg-12">
            <a href="/blog">
			    {l s='Voir toutes les actualités' mod='prestablog'}
            </a>
        </div>
    </div>
</div>
<div class="clearfix"></div>
<!-- /Module Presta Blog -->