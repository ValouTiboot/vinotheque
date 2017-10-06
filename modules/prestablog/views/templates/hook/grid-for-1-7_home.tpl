<!-- Pour les url et image proceder comme ci dessous -->
<div class="col-xs-12">
	<pre>{$RecipeNews|@print_r}</pre>
</div>

<!-- Module Presta Blog -->
<div class="prestablog_slide">
   <div class="sliders_prestablog">
   {foreach from=$ListeBlogNews item=slide name=slides}
      <a href="{PrestaBlogUrl id=$slide.id_prestablog_news seo=$slide.link_rewrite titre=$slide.title}">
         <img src="{$prestablog_theme_upimg|escape:'html':'UTF-8'}slide_{$slide.id_prestablog_news|intval}.jpg?{$md5pic|escape:'htmlall':'UTF-8'}" class="visu" alt="{$slide.title|escape:'htmlall':'UTF-8'}" title="{$slide.title|escape:'htmlall':'UTF-8'}" />
      </a>
   	{foreachelse}
   		{l s='No result found' mod='prestablog'}
   	{/foreach}
    </div>
</div>
<div class="clearfix"></div>
<!-- /Module Presta Blog -->