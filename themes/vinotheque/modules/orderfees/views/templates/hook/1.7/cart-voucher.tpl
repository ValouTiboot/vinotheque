{*
* Order Fees
*
* @author    motionSeed <ecommerce@motionseed.com>
* @copyright 2017 motionSeed. All rights reserved.
* @license   https://www.motionseed.com/en/license-module.html
*}
<div id="cart_fees">   
    <input type="hidden" id="fees-url" name="fees-url" value="{url entity='cart' params=['ajax' => 1]}" />
    {if isset($fees) && count($fees)}
        <div class="cart-fees">
            <ul class="card-block">
                {foreach $fees as $fee}
                    <li class="cart-summary-line row">
                        {if ($fee["obj"]->is_fee & $module->getConstant('IS_OPTION')) && ($fee["obj"]->display_selectable & $module->getConstant('CONTEXT_CART'))}
                            <div class="col-md-2 col-4 text-center">
                                <i class="icon-v-plus"></i>
                            </div>
                            <div class="col-md-5 col-8">
                                <span class="custom-checkbox">
                                    <input type="checkbox" id="fees_cart_{$fee.id_cart_rule|intval}" name="fees[]" value="{$fee.id_cart_rule|intval}" {if $fee["is_checked"]}checked="checked"{/if} />
                                    <label>
                                        <span class="fees_title">{$fee.name|escape:'html':'UTF-8'}</span>
                                        <span class="fees_description">{$fee.obj->description|escape:'html':'UTF-8'}</span>
                                    </label>
                                </span>
                            </div>
                        {else}
                            <div class="pull-xs-left">
                                <span>{$fee["obj"]->quantity|intval}x {$fee.name|escape:'html':'UTF-8'}</span>
                            </div>
                        {/if}
                        <div class="col-md-5 col-12">
                            <div class="value-row row">
                                <div class="col-10 col-sm-6 col-md-10">
                                    <div class="row">
                                        <div class="col-md-5 col-6">
                                            <label id="fees_cart_button" class="btn btn-secondary" for="fees_cart_{$fee.id_cart_rule|intval}">
                                                {if ($fee.is_checked)}
                                                    {l s='Delete' mod='orderfees'}
                                                {else}
                                                    {l s='Ajouter' mod='orderfees'}
                                                {/if}
                                            </label>
                                        </div>
                                        <div class="value col-md-7 col-6 text-right">
                                            {if $tax->includeTaxes()}
                                                {$price->format($fee.value_real*-1)|escape:'html':'UTF-8'}
                                            {else}
                                                {$price->format($fee.value_real_exc*-1)|escape:'html':'UTF-8'}
                                            {/if}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-2"></div>
                            </div>
                        </div>
                    </li>
                {/foreach}
            </ul>
        </div>
    <script type="text/javascript">
        var fee_add = "{l s='Ajouter' mod='orderfees'}";
        var fee_delete = "{l s='Delete' mod='orderfees'}";
        var fes_ajax_url = "{$link->getModuleLink('orderfees', 'ajax')}";
    </script>
    {/if}
</div>