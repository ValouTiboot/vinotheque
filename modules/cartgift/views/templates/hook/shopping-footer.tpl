{if $cartgift.allowed}
<div class="delivery-options-list">
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
      <label>{l s='I wish to leave a message on a card' mod='cartgift'}</label>
    </span>

    <div id="gift" class="">
      <label for="gift_message">{l s='If you\'d like, you can add a note to the gift:' d='Shop.Theme.Checkout'}</label>
      <textarea rows="2" id="gift_message" name="gift_message">{$cartgift.message}</textarea>
      <button type="submit" name="SubmitGiftMessage" id="send_message_gift">{l s='Save message' mod='cartgift'}</button>
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