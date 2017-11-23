{*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    FMM Modules
*  @copyright 2016 FMM Modules
*  @version   1.3.0
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<div class="panel">
    <form class="form-horizontal" method="POST" action="{$action_url|escape:'htmlall':'UTF-8'}" enctype="multipart/form-data">
    <fieldset>
        <legend class="panel-heading"><i class="icon-cogs"></i> {l s='General Settings' mod='giftcard'}</legend>
        <div class="form-group">
            <label class="control-label col-lg-4">
                <span title="" data-html="true" data-toggle="tooltip" class="label-tooltip" data-original-title="{l s='Selected groups will be able to enroll into affiliate program' mod='giftcard'}">{l s='Approval status for gift cards' mod='giftcard'}</span>
            </label>
            <div class="col-lg-8">
                <div class="{if $ps_version >= 1.6}row{/if}">
                    <div class="col-lg-8">
                        <table class="table table-bordered well">
                            <thead>
                                <tr>
                                    <th class="fixed-width-xs">
                                        <span class="title_box">
                                            <input type="checkbox" onclick="checkDelBoxes(this.form, 'approval_states[]', this.checked)" id="checkme" name="checkme">
                                        </span>
                                    </th>
                                    <th>
                                        <span class="title_box">{l s='Order Status' mod='giftcard'}</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                            {if isset($states) AND $states}
                                {foreach from=$states item=state}
                                <tr>
                                    <td>
                                        <input type="checkbox" value="{$state.id_order_state|escape:'htmlall':'UTF-8'}" id="affiliate_groups_{$state.id_order_state|escape:'htmlall':'UTF-8'}" class="approval_states" name="approval_states[]" {if isset($approval_states) AND $approval_states AND $approval_states AND in_array($state.id_order_state, $approval_states)}checked="checked"{/if}>
                                    </td>
                                    <td>
                                        <label for="affiliate_groups_{$state.id_order_state|escape:'htmlall':'UTF-8'}">{$state.name|escape:'htmlall':'UTF-8'}</label>
                                    </td>
                                </tr>
                                {/foreach}
                            {/if}
                            </tbody>
                        </table>
                        <p class="help-block hint-block margin-form">{l s='Gift cards will be accessible to customer(s) after validating specified selected order states.' mod='giftcard'}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="clearfix"></div><br/>

        <div class="panel-footer">
            <button class="btn btn-default button pull-right" type="submit" name="updateConfiguration"><span><i class="process-icon-save"></i></span> {l s='Save' mod='giftcard'}</button>
        </div>
    </fieldset>
    </form>
</div>