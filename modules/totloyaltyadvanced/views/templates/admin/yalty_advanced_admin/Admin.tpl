{*
* @version 1.0
* @author 202-ecommerce
* @copyright 2014-2015 202-ecommerce
* @license ?
*}

<div id="container-customer">
     {if !empty($customer)}
          <h2>{$gender|escape:'html':'UTF-8'} {$customer|escape:'html':'UTF-8'}</h2>
          <div class="table-responsive-row clearfix">
               <table class="table loyalty">
                    <thead>
                         <tr>
                              <th class="center"><span class="title_box">{l s='Order' mod='totloyaltyadvanced'}</span></th>
                              <th class="center"><span class="title_box">{l s='Date' mod='totloyaltyadvanced'}</span></th>
                              <th class="center"><span class="title_box">{l s='Points' mod='totloyaltyadvanced'}</span></th>
                              <th class="center"><span class="title_box">{l s='State' mod='totloyaltyadvanced'}</span></th>
                         </tr>
                    </thead>
                    <tbody>
                         {if isset($loyalties) && sizeof($loyalties)}
                              {assign var="total" value="0"}
                              {foreach $loyalties as $loyalty}
                                   <tr class="#">
                                        <td data-title="{l s='Order' mod='totloyaltyadvanced'}" class="center"><a href="{$linkOrder|escape:'html':'UTF-8'}&vieworder&id_order={$loyalty.id_order|escape:'html':'UTF-8'}">#{$loyalty.id_order|escape:'html':'UTF-8'}</a></td>
                                        <td data-title="{l s='Date' mod='totloyaltyadvanced'}"  class="center">{$loyalty.date_upd|escape:'html':'UTF-8'}</td>
                                        <td data-title="{l s='Points' mod='totloyaltyadvanced'}" class="center">{$loyalty.points|escape:'html':'UTF-8'}</td>
                                        <td data-title="{l s='State' mod='totloyaltyadvanced'}"  class="center">{$loyalty.lang_state|escape:'html':'UTF-8'}</td>
                                   </tr>
                                   {if $loyalty.id_loyalty_state == $lvl_valid}
                                        {math equation="total + points" total=$total points=$loyalty.points assign="total"}
                                   {/if}
                              {/foreach}
                         {else}
                              <tr>
                                   <td colspan="4">
                                        <div class="warn">
                                             {l s='No loyalties for this customer' mod='totloyaltyadvanced'}
                                        </div>
                                   </td>
                              </tr>
                         {/if}
                    </tbody>
                    <tfoot>
                         <tr>
                              <td colspan="3" class="right">
                                   {l s='Total points valid : ' mod='totloyaltyadvanced'}
                              </td>
                              <td data-title="{l s='Total points valid : ' mod='totloyaltyadvanced'}" class="center">
                                   {$total|escape:'html':'UTF-8'}
                              </td>
                         </tr>
                    </tfoot>
               </table>
          </div>
     {else}
          <div class="error">
               {l s='No points in this shop or group for this customer' mod='totloyaltyadvanced'}
          </div>
     {/if}
     <p>
          <a href="{$linkBack|escape:'html':'UTF-8'}">
               <input type="button" class="btn btn-default" value="{l s='Back to the list' mod='totloyaltyadvanced'}"/>
          </a>
     </p>
</div>
