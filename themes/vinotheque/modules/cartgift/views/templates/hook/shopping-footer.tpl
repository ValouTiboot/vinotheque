{if $cartgift.allowed}
<div id="cart_gift" class="delivery-options-list">
  <label class="gift_message">{l s='Cette commande est un cadeau ?' d='Shop.Theme.Checkout'}</label>
  <form
    class="clearfix"
    id="js-delivery-cart"
    data-url="{$cartgift.url}"
    method="post"
  >
    <span class="custom-checkbox">
      <input
        id="gift_checkbox"
        class="js-gift-checkbox"
        name="gift"
        type="checkbox"
        value="1"
        {if $cartgift.isGift}checked="checked"{/if}
      >
      <span><i class="material-icons checkbox-checked">&#xE5CA;</i></span>
      <label class="gift_checkbox" for="gift_checkbox">{l s='I wish to leave a message on a card' mod='cartgift'}</label>
    </span>

    <div id="gift">
      <textarea rows="4" id="gift_message" name="gift_message" placeholder="{l s='Votre message d\'accompagnement' mod='cartgift'}">{$cartgift.message}</textarea>
      <button class="btn btn-secondary pull-right" type="submit" name="SubmitGiftMessage" id="send_message_gift">{l s='Save message' mod='cartgift'}</button>
    </div>
  </form>
</div>

<div id="modal_cartgift" class="modal fade" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-body">
        <p>One fine body&hellip;</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

{/if}