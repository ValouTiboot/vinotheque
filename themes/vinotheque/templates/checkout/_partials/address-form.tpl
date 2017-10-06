{extends file='customer/_partials/address-form.tpl'}

{block name='form_field'}
  {if $field.name eq "alias"}
    {* we don't ask for alias here *}
  {else}
    {$smarty.block.parent}
  {/if}
{/block}

{block name="address_form_url"}
  <form
    method="POST"
    action="{url entity='order' params=['id_address' => $id_address]}"
    data-id-address="{$id_address}"
    data-refresh-url="{url entity='order' params=['ajax' => 1, 'action' => 'addressForm']}"
  >
{/block}

{block name='form_fields' append}
  <input type="hidden" name="saveAddress" value="{$type}">
  {if $type === "delivery"}
    <div class="form-group row ">
      <label class="col-md-3 form-control-label"></label>
      <div class="col-md-9">
        <span class="custom-checkbox">
          <input  name    = "use_same_address"
                  type    = "checkbox"
                  value   = "1"
                  {if $use_same_address} checked {/if}
          >
          <span><i class="material-icons checkbox-checked">&#xE5CA;</i></span>
          <label>{l s='Use this address for invoice too' d='Shop.Theme.Checkout'}</label>
        </span>
      </div>
    </div>
  {/if}
{/block}

{block name='form_buttons'}
  {if !$form_has_continue_button}
    <a href="{url entity='order' params=['cancelAddress' => {$type}]}">{l s='Cancel' d='Shop.Theme.Actions'}</a>
    <button type="submit">{l s='Save Address' d='Shop.Theme.Actions'}</button>
  {else}
    {if $customer.addresses|count > 0}
      <a class="btn btn-secondary" href="{url entity='order' params=['cancelAddress' => {$type}]}">{l s='Cancel' d='Shop.Theme.Actions'}</a>
    {/if}
    <button class="btn btn-primary" type="submit" class="continue" name="confirm-addresses" value="1">
      {l s='Continue' d='Shop.Theme.Actions'}
    </button>
  {/if}
{/block}
