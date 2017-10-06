<div class="col-lg-5 col-md-12 col-sm-12">
  <div id="blockcart-wrapper">
    <div class="blockcart cart-preview" data-refresh-url="{$refresh_url}">
      <div class="header">
        <a rel="nofollow" href="{$cart_url}">
          <i class="icon-v-cart"></i>
          <span class="header-label">{l s='My cart' d='Shop.Theme.Actions'}</span>
          <span>({$cart.products_count})</span>
        </a>
      </div>
      {*<div class="body">*}
        {*<ul>*}
            {*{foreach from=$cart.products item=product}*}
              {*<li>{include 'module:ps_shoppingcart/ps_shoppingcart-product-line.tpl' product=$product}</li>*}
            {*{/foreach}*}
        {*</ul>*}
        {*<div class="cart-subtotals">*}
            {*{foreach from=$cart.subtotals item="subtotal"}*}
              {*<div class="{$subtotal.type}">*}
                {*<span class="label">{$subtotal.label}</span>*}
                {*<span class="value">{$subtotal.amount}</span>*}
              {*</div>*}
            {*{/foreach}*}
        {*</div>*}
        {*<div class="cart-total">*}
          {*<span class="label">{$cart.totals.total.label}</span>*}
          {*<span class="value">{$cart.totals.total.amount}</span>*}
        {*</div>*}
      {*</div>*}
    </div>
  </div>
<!-- /Block Customer+Cart module TOP -->

