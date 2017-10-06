{*
* Order Fees
*
* @author    motionSeed <ecommerce@motionseed.com>
* @copyright 2017 motionSeed. All rights reserved.
* @license   https://www.motionseed.com/en/license-module.html
*}
{if $display_method == 'percent'}
    {assign var='percent' value='0'}
    {foreach $cart_rules as $cart_rule}
        {if $cart_rule.obj->is_fee & $module->getConstant('IS_REDUCTION')}
            {assign var='type' value=-1}
        {else}
            {assign var='type' value=1}
        {/if}
        
        {assign var='percent' value=$percent + ($cart_rule.obj->reduction_percent * $type)}
    {/foreach}
    
    {if $percent > 0}
        <span class="pull-right">{l s='+ additional fees : %s' mod='orderfees' sprintf=[$percent|number_format:2:",":" "|cat:' %']}</span>
    {else}
        <span class="pull-right">{l s='%s' mod='orderfees' sprintf=[$percent|number_format:2:",":" "|cat:' %']}</span>
    {/if}
{else}    
    {if $total > 0}
        <span class="pull-right">{l s='+ additional fees : %s' mod='orderfees' sprintf=[$price->format($total)]}</span>
    {else}
        <span class="pull-right">{l s='%s' mod='orderfees' sprintf=[$price->format($total)]}</span>
    {/if}
{/if}

