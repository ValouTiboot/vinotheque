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
{extends file='catalog/listing/product-list.tpl'}


{block name='left_column'}{/block}
{block name='right_column'}{/block}

{block name='content_wrapper'}
    <div id="content-wrapper">
		{hook h="displayContentWrapperTop"}
		{block name='content'}
            <section id="main">

				{block name='product_list_header'}
                    <div class="category-cover" style="background-image: url('{$category.image.large.url}')">
                        <div class="container">
                            <div class="row">
                                <div id="category-description" class="col-sm-6 col-sm-8">
                                    <h1 class="page_title">{$category.name}</h1>
									{$category.description nofilter}
                                </div>
                            </div>
                        </div>
                    </div>

					{*{block name='category_subcategories'}{/block}*}

				{/block}

                <div class="container">
                    <div class="row">
                        <div id="left-column" class="col-md-3">
                            <div class="container">
                                <div class="row">
									{hook h="displayLeftColumn"}
                                </div>
                            </div>
                        </div>
                        <section id="products" class="col-12 col-md-9">
                            <div id="category-highlight">
								{if $category.highlight_type == 'promo' && isset($category.image_highlight.category_default)}
                                    <!-- YATEO
									Format disponible category_default|medium_default|small_default
									Possibilité d'ajouter des formats en BO dans la gestion des images
									-->
                                    <img src="{$category.image_highlight.category_default}" />
								{elseif $category.highlight_type == 'best_seller' && isset($category.best_seller.cover) && $category.link_rewrite == 'primeurs'}
                                    <div id="best_seller" class="container">
                                        <div class="row">
                                            <div class="best_seller_image col-6 col-sm-3 col-lg-2">
                                                <img
                                                        src="{$category.best_seller.cover.bySize.large_default.url}"
                                                        alt="{$category.best_seller.cover.legend}"
                                                        title="{$category.best_seller.cover.legend}"
                                                >
                                            </div>
                                            <div class="best_seller_picto col-6 col-sm-3 col-lg-2">
                                                <img src="{$urls.img_url|escape:'html':'UTF-8'}derniere_sortie.png" alt="{l s='Dernière sortie' d='Shop.Theme.Actions'}">
                                            </div>
                                            <div class="best_seller_name col-sm-6 col-lg-4">
                                                <div class="category-name">{$category.best_seller.features[4].value}</div>
                                                <h1>
                                                    <a href="{$category.best_seller.url}">{$category.best_seller.name}</a>
                                                </h1>
                                                <div class="feature-name">
                                                    {if isset($category.best_seller.features[3].value) && isset($category.best_seller.features[1].value)}
                                                        {$category.best_seller.features[3].value} - {$category.best_seller.features[1].value}
                                                    {/if}
                                                </div>
                                                <div class="primeur">{l s='Primeurs' d='Shop.Theme.Actions'}</div>
                                                <div class="wine_delivery">
													{l s='Livraison prévue' d='Shop.Theme.Actions'} : {dateFormat date=$category.best_seller.wine_delivery}
                                                </div>
                                                <div class="product-price-and-shipping">
													{l s='A partir de' d='Shop.Theme.Actions'}{if $category.best_seller.has_discount} <span class="regular-price">{$category.best_seller.regular_price}</span>{/if} <span class="price">{$category.best_seller.price}</span>
                                                </div>
                                            </div>
                                            <div class="best_seller_buttons col-md-12 col-lg-4">
                                                <a class="btn btn-primary" href="{$category.best_seller.url}">{l s='En savoir plus' d='Shop.Theme.Actions'}</a>
                                                <div>
													{if $category.best_seller.add_to_cart_url}
                                                        <a
                                                                class = "add-to-cart btn btn-primary"
                                                                href  = "{$category.best_seller.add_to_cart_url}"
                                                                rel   = "nofollow"
                                                                data-id-product="{$category.best_seller.id_product}"
                                                                data-id-product-attribute="{$category.best_seller.id_product_attribute}"
                                                                data-link-action="add-to-cart"
                                                        ><i class="icon-v-cart"></i></a>
													{/if}
													{hook h='displayProductListFunctionalButtons' product=$category.best_seller}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
								{elseif $category.highlight_type == 'best_seller' && isset($category.best_seller.cover)}
                                    <div id="best_seller" class="container">
                                        <div class="row">
                                            <div class="best_seller_image col-6 col-sm-3 col-lg-2">
                                                <img
                                                        src="{$category.best_seller.cover.bySize.large_default.url}"
                                                        alt="{$category.best_seller.cover.legend}"
                                                        title="{$category.best_seller.cover.legend}"
                                                >
                                            </div>
                                            <div class="best_seller_picto col-6 col-sm-3 col-lg-2">
                                                <img src="{$urls.img_url|escape:'html':'UTF-8'}best_seller.png" alt="{l s='Meilleure vente' d='Shop.Theme.Actions'}">
                                            </div>
                                            <div class="best_seller_name col-sm-6 col-lg-4">
                                                <div class="category-name">{$category.best_seller.features[4].value}</div>
                                                <h1>
                                                    <a href="{$category.best_seller.url}">{$category.best_seller.name}</a>
                                                </h1>
                                                <span class="feature-name">
                                                    {if isset($category.best_seller.features[3].value) && isset($category.best_seller.features[1].value)}
                                                        {$category.best_seller.features[3].value} - {$category.best_seller.features[1].value}
                                                    {/if}
                                                </span>
                                                <div class="product-price-and-shipping">
													{l s='A partir de' d='Shop.Theme.Actions'}{if $category.best_seller.has_discount} <span class="regular-price">{$category.best_seller.regular_price}</span>{/if} <span class="price">{$category.best_seller.price}</span>
                                                </div>
                                            </div>
                                            <div class="best_seller_buttons col-md-12 col-lg-4">
                                                <a class="btn btn-primary" href="{$category.best_seller.url}">{l s='En savoir plus' d='Shop.Theme.Actions'}</a>
                                                <div>
													{if $category.best_seller.add_to_cart_url}
                                                        <a
                                                                class = "add-to-cart btn btn-primary"
                                                                href  = "{$category.best_seller.add_to_cart_url}"
                                                                rel   = "nofollow"
                                                                data-id-product="{$category.best_seller.id_product}"
                                                                data-id-product-attribute="{$category.best_seller.id_product_attribute}"
                                                                data-link-action="add-to-cart"
                                                        ><i class="icon-v-cart"></i></a>
													{/if}
													{hook h='displayProductListFunctionalButtons' product=$category.best_seller}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
									{*{$category.best_seller|@var_dump}*}
								{/if}
                            </div>

							{if $listing.products|count}

								{block name='product_list_top'}
									{include file='catalog/_partials/products-top.tpl' listing=$listing}
								{/block}

								{block name='product_list_active_filters'}
									{$listing.rendered_active_filters nofilter}
								{/block}

								{block name='product_list'}
									{include file='catalog/_partials/products.tpl' listing=$listing lg=4}
								{/block}

                                <div id="js-product-list-bottom">
									{block name='product_list_bottom'}
										{include file='catalog/_partials/products-bottom.tpl' listing=$listing}
									{/block}
                                </div>

							{else}

								{include file='errors/not-found.tpl'}

							{/if}
                        </section>
                    </div>
                </div>

            </section>
		{/block}
		{hook h="displayContentWrapperBottom"}
    </div>
{/block}
