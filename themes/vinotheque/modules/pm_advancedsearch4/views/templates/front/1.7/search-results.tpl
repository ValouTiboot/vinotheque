{extends file='catalog/listing/category.tpl'}

{block name='breadcrumb'}
    {include file='_partials/breadcrumb.tpl'}
{/block}
{block name='content_wrapper'}
    {block name='product_list_header'}
        {if !empty($as_search.keep_category_information) && isset($category) && isset($subcategories)}
            {$smarty.block.parent}
        {else}
            {if $as_seo_description}
                <div class="block-category card card-block hidden-sm-down">
                    <h1 class="h1">{$as_seo_title}</h1>
                    {if $as_seo_description}
                        <div id="category-description" class="text-muted">{$as_seo_description nofilter}</div>
                    {/if}
                </div>
                <div class="text-xs-center hidden-md-up">
                    <h1 class="h1">{$as_seo_title}</h1>
                </div>
            {else}
                <h2 class="h2">{$as_seo_title}</h2>
            {/if}
        {/if}
    {/block}

{/block}