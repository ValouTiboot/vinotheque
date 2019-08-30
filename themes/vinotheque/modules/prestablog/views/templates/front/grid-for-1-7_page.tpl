{*
 * 2008 - 2017 (c) HDClic
 *
 * MODULE PrestaBlog
 *
 * @author    HDClic <prestashop@hdclic.com>
 * @copyright Copyright (c) permanent, HDClic
 * @license   Addons PrestaShop license limitation
 * @version    4.0.1
 * @link    http://www.hdclic.com
 *
 * NOTICE OF LICENSE
 *
 * Don't use this module on several shops. The license provided by PrestaShop Addons
 * for all its modules is valid only once for a single shop.
 *}

<!-- Module Presta Blog START PAGE -->
{extends file=$layout_blog}

{block name='head_seo'}
  <title>{$meta_title|escape:'htmlall':'UTF-8'}</title>
  <meta name="description" content="{$meta_description|escape:'htmlall':'UTF-8'}">
  <meta name="keywords" content="{block name='head_seo_keywords'}{$page.meta.keywords|escape:'htmlall':'UTF-8'}{/block}">
  {if $page.meta.robots !== 'index'}
    <meta name="robots" content="{$page.meta.robots|escape:'htmlall':'UTF-8'}">
  {/if}
  {if $page.canonical}
    <link rel="canonical" href="{$page.canonical|escape:'url':'UTF-8'}">
  {/if}
{/block}

{block name='content_wrapper'}
	<div id="content-wrapper"{if isset($tpl_menu_cat) && $tpl_menu_cat} class="prestablog_index_background"{/if}>
		{hook h="displayContentWrapperTop"}
			  {block name='content'}
				  <div class="container">
					  <div class="row justify-content-center">
						<div class="col-12 page_title">{l s='Actualités' mod='prestablog'}</div>

						{if isset($tpl_filtre_cat) && $tpl_filtre_cat}{PrestaBlogContent return=$tpl_filtre_cat}{/if}
						{if isset($tpl_menu_cat) && $tpl_menu_cat}
							<div id="prestablog_description" class="col-8">
								<p class="caveat text-center">Amatrices, amateurs de vin,</p>
								<p>Que pourrez-vous trouver sur notre page d’actualités ? … et bien tout ce qui concerne le vin et son univers : nos évènements, nos dégustations, les actualités des propriétés avec lesquelles nous travaillons, des recettes, etc. bref un peu de lecture et de découverte autour de ce qui nous rassemble : le Vin ! Le but n’étant pas d’inciter à quelconque abus mais bien de promouvoir la richesse de nos terroirs, l’investissement des femmes et des hommes qui font le vin, leur savoir-faire, etc. Et comme le vin s’accompagne toujours d’un bon repas, une touche de gastronomie avec nos amis restaurateurs : découvrez chaque mois une de leur recette et un accord met et vin digne des plus grandes tables.</p>
								<p class="caveat text-center">Bonne vinolecture !</p>
							</div>
							{PrestaBlogContent return=$tpl_menu_cat}
						{/if}

						{if isset($tpl_unique) && $tpl_unique}{PrestaBlogContent return=$tpl_unique}{/if}
						{if isset($tpl_comment) && $tpl_comment}{PrestaBlogContent return=$tpl_comment}{/if}
						{if isset($tpl_comment_fb) && $tpl_comment_fb}{PrestaBlogContent return=$tpl_comment_fb}{/if}

						{if isset($tpl_slide) && $tpl_slide}{PrestaBlogContent return=$tpl_slide}{/if}
						{if isset($tpl_cat) && $tpl_cat}{PrestaBlogContent return=$tpl_cat}{/if}
						{if isset($tpl_all) && $tpl_all}{PrestaBlogContent return=$tpl_all}{/if}
					  </div>
				  </div>

			{/block}
		{hook h="displayContentWrapperBottom"}
	</div>
{/block}



<!-- /Module Presta Blog END PAGE -->
