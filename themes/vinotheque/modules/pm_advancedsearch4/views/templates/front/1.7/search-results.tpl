{extends file='catalog/listing/product-list.tpl'}

{block name='left_column'}{/block}
{block name='right_column'}{/block}
{block name='breadcrumb'}
    {include file='_partials/breadcrumb.tpl'}
{/block}
{block name='content_wrapper'}
    <div id="content-wrapper">
        {hook h="displayContentWrapperTop"}
        {block name='content'}
            <section id="main">
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
