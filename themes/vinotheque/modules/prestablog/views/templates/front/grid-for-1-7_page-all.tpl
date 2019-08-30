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

<!-- Module Presta Blog -->
{if isset($prestablog_title_h1)}<h1>{$prestablog_title_h1|escape:'htmlall':'UTF-8'}</h1>{/if}
{*<h2><span>{$NbNews|intval} {if $NbNews <> 1}{l s='articles' mod='prestablog'}{else}{l s='article' mod='prestablog'}{/if}</span></h2>*}

{if sizeof($news)}
	<div id="prestablog_slides" class="container">
		<div class="row">
			{foreach from=$news item=slide name=slides}
				<div class="col-lg-6">
					<a href="{PrestaBlogUrl id=$slide.id_prestablog_news seo=$slide.link_rewrite titre=$slide.title}">
						<img src="{$prestablog_theme_upimg|escape:'html':'UTF-8'}slide_{$slide.id_prestablog_news|intval}.jpg?{$md5pic|escape:'htmlall':'UTF-8'}" class="visu" alt="{$slide.title|escape:'htmlall':'UTF-8'}" title="{$slide.title|escape:'htmlall':'UTF-8'}" />
						<span class="prestablog_title">{$slide.title}</span>
					</a>
					<div class="prestablog_paragraph">
						{$slide.paragraph|strip_tags}
					</div>
				</div>
			{foreachelse}
				{l s='No result found' mod='prestablog'}
			{/foreach}
		</div>
	</div>
	{include file="$prestablog_pagination"}
{else}
	<p class="warning">{l s='Empty' mod='prestablog'}</p>
{/if}
<!-- /Module Presta Blog -->
