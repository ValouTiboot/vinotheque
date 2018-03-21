{if $logged}
    <script>
        var baseDir = '{$base_dir|addslashes}';
        var static_token = '{$static_token|addslashes}';
        var isLogged = '{$logged}';
    </script>
    <div id="wishlist_button_block" class="buttons_bottom_block {if $issetProduct}wrap_allert{/if}">
        {if $issetProduct}
            <a class="wishlist btn btn-primary" href="#" onclick="return false;" title="{l s='Ajouter à ma liste d\'envie' mod='advansedwishlist'}"><i class="icon-v-heart"></i></a>
            <div class="allert_note">{l s='This product has been added to wishlist' mod='advansedwishlist'}</div>
        {else}
            <a class="wishlist btn btn-primary" href="#" onclick="WishlistCart('wishlist_block_list', 'add', '{$id_product|intval}', '{$cache_default_attribute|intval}', 1); return false;" rel="nofollow" title="{l s='Ajouter à ma liste d\'envie' mod='advansedwishlist'}"><i class="icon-v-heart"></i></a>
        {/if}
    </div>
{else}
    <div class="wrap_allert">
        <a class="wishlist" href="#" onclick="return false;" title="{l s='Ajouter à ma liste d\'envie' mod='advansedwishlist'}"><i class="icon-v-heart"></i></a>
        {*<div class="allert_note">*}
            {*{l s='You must be logged' mod='advansedwishlist'}*}
            {*<p class="login_links">*}
                {*<a class="inline" href="{$link->getPageLink('my-account', true)|escape:'htmlall':'UTF-8'}">{l s='Sign in' mod='advansedwishlist'}</a> | <a class="inline" href="{$link->getPageLink('my-account', true)|escape:'htmlall':'UTF-8'}">{l s='Register' mod='advansedwishlist'}</a>*}
            {*</p>*}
        {*</div>*}
</div>
{/if}
