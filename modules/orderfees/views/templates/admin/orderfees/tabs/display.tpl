{*
* Order Fees
*
* @author    motionSeed <ecommerce@motionseed.com>
* @copyright 2017 motionSeed. All rights reserved.
* @license   https://www.motionseed.com/en/license-module.html
*}

{hook h="displayOrderFeesFormDisplayBefore" module=$module controller=$currentTab object=$currentObject}

{if !$is_option}
<style type="text/css">
    #display_table tr > *:nth-child(3) {
        display: none;
    }
</style>
{/if}

<div class="form-group">
    <div class="col-lg-7">
        <table id="display_table" class="table">
            <thead>
                <tr>
                    <th>{l s='Page' mod='orderfees'}</th>
                    <th class="text-center">{l s='Visible' mod='orderfees'}</th>
                    <th class="text-center">{l s='Selectable' mod='orderfees'}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="col-lg-1">
                        {l s='Cart' mod='orderfees'}
                    </td>
                    <td class="col-lg-1 text-center">
                        <input type="checkbox" id="display_visible_context_cart" name="display_visible_context_cart" value="{$module->getConstant('CONTEXT_CART')|escape:'html':'UTF-8'}" {if $currentTab->getFieldValue($currentObject, 'display_visible') & $module->getConstant('CONTEXT_CART')}checked="checked"{/if} />
                    </td>
                    <td class="col-lg-1 text-center">
                        <input type="checkbox" id="display_selectable_context_cart" name="display_selectable_context_cart" value="{$module->getConstant('CONTEXT_CART')|escape:'html':'UTF-8'}" {if $currentTab->getFieldValue($currentObject, 'display_selectable') & $module->getConstant('CONTEXT_CART')}checked="checked"{/if} />
                    </td>
                </tr>
                
                {if !$is_option}
                <tr>
                    <td class="col-lg-1">
                        {l s='Product page' mod='orderfees'}
                    </td>
                    <td class="col-lg-1 text-center">
                        <input type="checkbox" id="display_visible_context_product" name="display_visible_context_product" value="{$module->getConstant('CONTEXT_PRODUCT')|escape:'html':'UTF-8'}" {if $currentTab->getFieldValue($currentObject, 'display_visible') & $module->getConstant('CONTEXT_PRODUCT')}checked="checked"{/if} />
                    </td>
                    <td class="col-lg-1 text-center">
                        <input type="checkbox" id="display_selectable_context_product" name="display_selectable_context_product" value="{$module->getConstant('CONTEXT_PRODUCT')|escape:'html':'UTF-8'}" {if $currentTab->getFieldValue($currentObject, 'display_selectable') & $module->getConstant('CONTEXT_PRODUCT')}checked="checked"{/if} />
                    </td>
                </tr>
                {/if}
                
                <tr>
                    <td class="col-lg-1">
                        {l s='Payment page' mod='orderfees'}
                    </td>
                    <td class="col-lg-1 text-center">
                        <input type="checkbox" id="display_visible_context_payment" name="display_visible_context_payment" value="{$module->getConstant('CONTEXT_PAYMENT')|escape:'html':'UTF-8'}" {if $currentTab->getFieldValue($currentObject, 'display_visible') & $module->getConstant('CONTEXT_PAYMENT')}checked="checked"{/if} />
                    </td>
                    <td class="col-lg-1 text-center">
                        <input type="checkbox" id="display_selectable_context_payment" name="display_selectable_context_payment" value="{$module->getConstant('CONTEXT_PAYMENT')|escape:'html':'UTF-8'}" {if $currentTab->getFieldValue($currentObject, 'display_selectable') & $module->getConstant('CONTEXT_PAYMENT')}checked="checked"{/if} />
                    </td>
                </tr>
                <tr>
                    <td class="col-lg-1">
                        {l s='Carrier page' mod='orderfees'}
                    </td>
                    <td class="col-lg-1 text-center">
                        <input type="checkbox" id="display_visible_context_carrier" name="display_visible_context_carrier" value="{$module->getConstant('CONTEXT_CARRIER')|escape:'html':'UTF-8'}" {if $currentTab->getFieldValue($currentObject, 'display_visible') & $module->getConstant('CONTEXT_CARRIER')}checked="checked"{/if} />
                    </td>
                    <td class="col-lg-1 text-center">
                        <input type="checkbox" id="display_selectable_context_carrier" name="display_selectable_context_carrier" value="{$module->getConstant('CONTEXT_CARRIER')|escape:'html':'UTF-8'}" {if $currentTab->getFieldValue($currentObject, 'display_selectable') & $module->getConstant('CONTEXT_CARRIER')}checked="checked"{/if} />
                    </td>
                </tr>
                <tr>
                    <td class="col-lg-1">
                        {l s='Address page' mod='orderfees'}
                    </td>
                    <td class="col-lg-1 text-center">
                        <input type="checkbox" id="display_visible_context_address" name="display_visible_context_address" value="{$module->getConstant('CONTEXT_ADDRESS')|escape:'html':'UTF-8'}" {if $currentTab->getFieldValue($currentObject, 'display_visible') & $module->getConstant('CONTEXT_ADDRESS')}checked="checked"{/if} />
                    </td>
                    <td class="col-lg-1 text-center">
                        <input type="checkbox" id="display_selectable_context_address" name="display_selectable_context_address" value="{$module->getConstant('CONTEXT_ADDRESS')|escape:'html':'UTF-8'}" {if $currentTab->getFieldValue($currentObject, 'display_selectable') & $module->getConstant('CONTEXT_ADDRESS')}checked="checked"{/if} />
                    </td>
                </tr>
                
                {if Tools::version_compare('1.7', $smarty.const._PS_VERSION_)}
                    <tr>
                        <td class="col-lg-1">
                            {l s='Order confirmation page' mod='orderfees'}
                        </td>
                        <td class="col-lg-1 text-center">
                            <input type="checkbox" id="display_visible_context_confirmation" name="display_visible_context_confirmation" value="{$module->getConstant('CONTEXT_CONFIRMATION')|escape:'html':'UTF-8'}" {if $currentTab->getFieldValue($currentObject, 'display_visible') & $module->getConstant('CONTEXT_CONFIRMATION')}checked="checked"{/if} />
                        </td>
                        <td class="col-lg-1 text-center">
                            <input type="checkbox" id="display_selectable_context_confirmation" name="display_selectable_context_confirmation" value="{$module->getConstant('CONTEXT_CONFIRMATION')|escape:'html':'UTF-8'}" {if $currentTab->getFieldValue($currentObject, 'display_selectable') & $module->getConstant('CONTEXT_CONFIRMATION')}checked="checked"{/if} />
                        </td>
                    </tr>
                {/if}
                
                <tr>
                    <td class="col-lg-1">
                        {l s='Mails' mod='orderfees'}
                    </td>
                    <td class="col-lg-1 text-center">
                        <input type="checkbox" id="display_visible_context_mail" name="display_visible_context_mail" value="{$module->getConstant('CONTEXT_MAIL')|escape:'html':'UTF-8'}" {if $currentTab->getFieldValue($currentObject, 'display_visible') & $module->getConstant('CONTEXT_MAIL')}checked="checked"{/if} />
                    </td>
                    <td class="col-lg-1 text-center">
                        -
                    </td>
                </tr>
                <tr>
                    <td class="col-lg-1">
                        {l s='PDF documents' mod='orderfees'}
                    </td>
                    <td class="col-lg-1 text-center">
                        <input type="checkbox" id="display_visible_context_pdf" name="display_visible_context_pdf" value="{$module->getConstant('CONTEXT_PDF')|escape:'html':'UTF-8'}" {if $currentTab->getFieldValue($currentObject, 'display_visible') & $module->getConstant('CONTEXT_PDF')}checked="checked"{/if} />
                    </td>
                    <td class="col-lg-1 text-center">
                        -
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
                        
{hook h="displayOrderFeesFormDisplayAfter" module=$module controller=$currentTab object=$currentObject}