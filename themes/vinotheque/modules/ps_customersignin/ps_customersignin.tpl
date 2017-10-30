<!-- Block Customer+Cart module TOP -->
    <div id="_desktop_user_info">
      <div class="user-info">
        {*{if $logged}*}
          {*<a*}
            {*class="logout"*}
            {*href="{$logout_url}"*}
            {*rel="nofollow"*}
          {*>*}
            {*<i class="icon-v-user"></i>*}
            {*{l s='Sign out' d='Shop.Theme.Actions'}*}
          {*</a>*}
          {*<a*}
            {*class="account"*}
            {*href="{$my_account_url}"*}
            {*title="{l s='View my customer account' d='Shop.Theme.Customeraccount'}"*}
            {*rel="nofollow"*}
          {*>*}
            {*<i class="material-icons hidden-md-up logged">&#xE7FF;</i>*}
            {*<span class="hidden-sm-down">{$customerName}</span>*}
          {*</a>*}
        {*{else}*}
          <a
            href="{$my_account_url}"
            title="{if $logged}{l s='View my customer account' d='Shop.Theme.Customeraccount'}{else}{l s='Log in to your customer account' d='Shop.Theme.Customeraccount'}{/if}"
            rel="nofollow"
          >
            <i class="icon-v-user"></i>
            <span>
              {if $logged}
                {l s='My account' d='Shop.Theme.Actions'}
              {else}
                {l s='Connexion' d='Shop.Theme.Actions'}
              {/if}
            </span>
          </a>
        {*{/if}*}
      </div>
    </div>
</div>

{if !$logged}
    <script type="text/javascript">
        $(document).ready(function() {
            $('.is_logged').remove();
        });
    </script>
{/if}
