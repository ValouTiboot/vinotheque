{*
* Order Fees
*
* @author    motionSeed <ecommerce@motionseed.com>
* @copyright 2017 motionSeed. All rights reserved.
* @license   https://www.motionseed.com/en/license-module.html
*}
<input type="hidden" id="fees-url" name="fees-url" value="{url entity='order' params=['ajax' => 1]}" />
{if isset($fees) && count($fees)}
    <div class="cart-fees">
        <ul class="card-block">
			{foreach $fees as $fee}
                <li class="cart-summary-line">
					{if ($fee["obj"]->is_fee & $module->getConstant('IS_OPTION')) && ($fee["obj"]->display_selectable & $module->getConstant('CONTEXT_CART'))}
                        <div class="col-md-10 row">
                    <span class="custom-checkbox">
                        <input type="checkbox" id="fees_cart_{$fee.id_cart_rule|intval}" name="fees[]" value="{$fee.id_cart_rule|intval}" {if $fee["is_checked"]}checked="checked"{/if} />
                        <span><i class="material-icons checkbox-checked">&#xE5CA;</i></span>
                        <label for="fees_cart_{$fee.id_cart_rule|intval}">
                            {$fee.name|escape:'html':'UTF-8'}
							{$fee.description|escape:'html':'UTF-8'}
                        </label>
                    </span>
                        </div>
					{else}
                        <div class="pull-xs-left">
                            <span>{$fee["obj"]->quantity|intval}x {$fee.name|escape:'html':'UTF-8'}</span>
                        </div>
					{/if}
                    <div class="value col-md-2">
						{if $tax->includeTaxes()}
							{$price->format($fee["obj"]->unit_value_real*-1)|escape:'html':'UTF-8'}
						{else}
							{$price->format($fee["obj"]->unit_value_tax_exc*-1)|escape:'html':'UTF-8'}
						{/if}
                    </div>
                </li>
			{/foreach}
        </ul>
    </div>
{/if}