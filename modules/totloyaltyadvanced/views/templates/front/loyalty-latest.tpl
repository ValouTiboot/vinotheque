{*
* @version 1.0
* @author 202-ecommerce
* @copyright 2014-2015 202-ecommerce
* @license ?
*}

<!-- Header part menu -->
<head>
    {block name='head'}
        {include file='_partials/head.tpl'}
    {/block}
</head>

<body>
{hook h='displayAfterBodyOpeningTag'}
<main>
    <!-- Menu part-->
    <header id="header">
        {block name='header'}
            {include file='_partials/header.tpl'}
        {/block}
    </header>

    <!-- Header part ends -->

    <section id="wrapper">
        <div class="container">

            <section id="main">
                <section id="content" class="page-content card card-block">
                    {include file='_partials/breadcrumb.tpl'}
                    <h2>{l s='My loyalty points' mod='totloyaltyadvanced'}</h2>
                    {if $orders}
                        <div class="block-center" id="block-history">
                            {if $orders && count($orders)}
                                <div class="table-responsive-row clearfix">
                                    <table id="" class="table loyalty custom-table">
                                        <thead>
                                        <tr class="odd">
                                            <th class="first_item">{l s='Order' mod='totloyaltyadvanced'}</th>
                                            <th class="item">{l s='Date' mod='totloyaltyadvanced'}</th>
                                            <th class="item">{l s='Points' mod='totloyaltyadvanced'}</th>
                                            <th class="last_item">{l s='Points Status' mod='totloyaltyadvanced'}</th>
                                        </tr>
                                        </thead>
                                        <tfoot>
                                        <tr class="alternate_item">
                                            <td colspan="2" class="history_method bold"
                                                style="text-align:center;">{l s='Total points available:' mod='totloyaltyadvanced'}</td>
                                            <td class="history_method"
                                                style="text-align:left;">{$totalPoints|intval}</td>
                                            <td class="history_method">&nbsp;</td>
                                        </tr>
                                        </tfoot>
                                        <tbody>
                                        {foreach from=$displayorders item='order'}
                                            <tr class="alternate_item">
                                                <td data-label="{l s='Order' mod='totloyaltyadvanced'}"
                                                    class="history_link bold">{l s='#' mod='totloyaltyadvanced'}{$order.id|string_format:"%06d"|escape:'htmlall':'UTF-8'}</td>
                                                <td data-label="{l s='Date' mod='totloyaltyadvanced'}"
                                                    class="history_date">{dateFormat date=$order.date full=1}</td>
                                                <td data-label="{l s='Points' mod='totloyaltyadvanced'}"
                                                    class="history_method">{$order.points|intval}</td>
                                                <td data-label="{l s='Points Status' mod='totloyaltyadvanced'}"
                                                    class="history_method">{$order.state|escape:'htmlall':'UTF-8'}</td>
                                            </tr>
                                        {/foreach}
                                        </tbody>
                                    </table>
                                </div>
                                <div id="block-order-detail" class="hidden">&nbsp;</div>
                            {else}
                                <p class="warning">{l s='You have not placed any orders.' mod='totloyaltyadvanced'}</p>
                            {/if}
                        </div>
                        <!-- ------------------------- -->
                        <div id="pagination" class="pagination">
                            {if $nbpagination < $orders|@count}
                                <ul class="pagination total-custom">
                                    {if $page != 1}
                                        {assign var='p_previous' value=$page-1}
                                        <li id="pagination_previous"><a
                                                    href="{summarypaginationlink p=$p_previous n=$nbpagination}">
                                                &laquo;&nbsp;{l s='Previous' mod='totloyaltyadvanced'}</a></li>
                                    {else}
                                        <li id="pagination_previous" class="disabled">
                                            <span>&laquo;&nbsp;{l s='Previous' mod='totloyaltyadvanced'}</span></li>
                                    {/if}
                                    {if $page > 2}
                                        <li><a href="{summarypaginationlink p='1' n=$nbpagination}">1</a></li>
                                        {if $page > 3}
                                            <li class="truncate">...</li>
                                        {/if}
                                    {/if}
                                    {section name=pagination start=$page-1 loop=$page+2 step=1}
                                        {if $page == $smarty.section.pagination.index}
                                            <li class="current"><span>{$page|escape:'htmlall':'UTF-8'}</span></li>
                                        {elseif $smarty.section.pagination.index > 0 && $orders|@count+$nbpagination > ($smarty.section.pagination.index)*($nbpagination)}
                                            <li>
                                                <a href="{summarypaginationlink p=$smarty.section.pagination.index n=$nbpagination}">{$smarty.section.pagination.index|escape:'htmlall':'UTF-8'}</a>
                                            </li>
                                        {/if}
                                    {/section}
                                    {if $max_page-$page > 1}
                                        {if $max_page-$page > 2}
                                            <li class="truncate">...</li>
                                        {/if}
                                        <li>
                                            <a href="{summarypaginationlink p=$max_page n=$nbpagination}">{$max_page}</a>
                                        </li>
                                    {/if}
                                    {if $orders|@count > $page * $nbpagination}
                                        {assign var='p_next' value=$page+1}
                                        <li id="pagination_next"><a
                                                    href="{summarypaginationlink p=$p_next n=$nbpagination}">{l s='Next' mod='totloyaltyadvanced'}
                                                &nbsp;&raquo;</a></li>
                                    {else}
                                        <li id="pagination_next" class="disabled">
                                            <span>{l s='Next' mod='totloyaltyadvanced'}&nbsp;&raquo;</span></li>
                                    {/if}
                                </ul>
                            {/if}

                        </div>
                        <!-- -----  -->
                        <div class="points_container">
                            <p>
                                {l s='Vouchers generated here are usable in the following categories : ' mod='totloyaltyadvanced'}
                                {if $categories}
                                    {$categories|escape:'html':'UTF-8'}
                                {else}
                                    {l s='All' mod='totloyaltyadvanced'}
                                {/if}

                                {if $transformation_allowed}
                            </p>

                            <a href="{$link->getModuleLink('totloyaltyadvanced', 'default', ['process' => 'transformpoints'])|escape:'htmlall':'UTF-8'}"
                               onclick="return confirm('{l s='Are you sure you want to transform your points into vouchers?' mod='totloyaltyadvanced' js=1}');"
                               class="cta_btn">
                                {l s='Transform my points into a voucher of' mod='totloyaltyadvanced'}
                                <span class="price">{$voucher|escape:'htmlall':'UTF-8'}</span>.
                            </a>

                            </p>
                            {/if}

                        </div>
                        <h2>{l s='My vouchers from loyalty points' mod='totloyaltyadvanced'}</h2>
                    {if $nbDiscounts}
                        <div class="block-center" id="block-history">
                            <div class="table-responsive-row table-responsive custom-table clearfix">
                                <table id="order-list" class="table loyalty">
                                    <thead>
                                    <tr>
                                        <th class="first_item">{l s='Created' mod='totloyaltyadvanced'}</th>
                                        <th class="item">{l s='Value' mod='totloyaltyadvanced'}</th>
                                        <th class="item">{l s='Code' mod='totloyaltyadvanced'}</th>
                                        <th class="item">{l s='Valid from' mod='totloyaltyadvanced'}</th>
                                        <th class="item">{l s='Valid until' mod='totloyaltyadvanced'}</th>
                                        <th class="item">{l s='Status' mod='totloyaltyadvanced'}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {foreach from=$discounts item=discount name=myLoop}
                                        <tr class="alternate_item">
                                            <td data-label="{l s='Created' mod='totloyaltyadvanced'}"
                                                class="history_date">{dateFormat date=$discount->date_add}</td>
                                            <td data-label="{l s='Value' mod='totloyaltyadvanced'}"
                                                class="history_price">
                                <span class="price">
                                    {if $discount->reduction_percent > 0}
                                        {$discount->reduction_percent|escape:'html':'UTF-8'}%
                                    {elseif $discount->reduction_amount}
                                        {$discount->reduction_amount|escape:'htmlall':'UTF-8'}
                                    {else}
                                        {l s='Free shipping' mod='totloyaltyadvanced'}
                                    {/if}</span>
                                            </td>
                                            <td data-label="{l s='Code' mod='totloyaltyadvanced'}"
                                                class="history_method bold">{$discount->code|escape:'html':'UTF-8'}</td>
                                            <td data-label="{l s='Valid from' mod='totloyaltyadvanced'}"
                                                class="history_date">{dateFormat date=$discount->date_from}</td>

                                            <td data-label="{l s='Valid until' mod='totloyaltyadvanced'}"
                                                class="history_date">{dateFormat date=$discount->date_to}</td>

                                            <td data-label="{l s='Status' mod='totloyaltyadvanced'}"
                                                class="history_method bold">{if $discount->quantity > 0}{l s='To use' mod='totloyaltyadvanced'}{else}{l s='Used' mod='totloyaltyadvanced'}{/if}</td>

                                        </tr>
                                    {/foreach}
                                    </tbody>
                                </table>
                            </div>
                            <div id="block-order-detail" class="hidden">&nbsp;</div>
                        </div>
                    {if $minimalLoyalty > 0}
                        <p>{l s='The minimum order amount in order to use these vouchers is:' mod='totloyaltyadvanced'} {$minimalLoyalty|escape:'htmlall':'UTF-8'}</p>
                    {/if}
                        <script type="text/javascript">
                            {literal}
                            $(document).ready(function () {
                                $('a.tips').cluetip({
                                    showTitle : false,
                                    splitTitle: '|',
                                    arrows    : false,
                                    fx        : {
                                        open     : 'fadeIn',
                                        openSpeed: 'fast'
                                    }
                                });
                            });
                            {/literal}
                        </script>
                    {else}
                        <p class="warning">
                            {l s='No vouchers yet.' mod='totloyaltyadvanced'}
                        </p>
                    {/if}
                    {else}
                        <p class="warning">
                            {l s='No reward points yet.' mod='totloyaltyadvanced'}
                        </p>
                    {/if}

                </section>
            </section>
        </div>
    </section>
    <!-- Footer starts -->

    <footer id="footer">
        {block name="footer"}
            {include file="_partials/footer.tpl"}
        {/block}
    </footer>
    <!-- Footer Ends -->
    {block name='javascript_bottom'}
        {include file="_partials/javascript.tpl" javascript=$javascript.bottom}
    {/block}
    {hook h='displayBeforeBodyClosingTag'}
</main>

</body>

