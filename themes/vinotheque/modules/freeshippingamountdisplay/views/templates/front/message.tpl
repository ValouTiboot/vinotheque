{**
* 2012-2018 INNERCODE
*
* NOTICE OF LICENSE
*
* This source file is subject to the EULA (End User License Agreement)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* https://www.innercode.lt/ps-module-eula.txt
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to info@innercode.lt so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future.
*
* @author    Innercode
* @copyright Copyright (c) 2012 - 2018 INNERCODE, UAB. (https://www.innercode.lt)
* @license   https://www.innercode.lt/ps-module-eula.txt
* @package   freeshippingamountdisplay
* @site      https://www.innercode.lt
*}


{if $position == 'product' && isset($freeShippingText) && $freeShippingText}
<div class="row shipping-amount-display {$position|escape:'htmlall':'UTF-8'} {if $position == 'product' && isset($freeShippingText) && $freeShippingText}has-free-shipping{/if}">
	<div class="inner">
		<p class="text free-shipping-text">
			<i class="icon-truck"></i> {$freeShippingText|escape:'htmlall':'UTF-8'}
		</p>
	</div>
</div>
{else}			
	<div class="col-xs-8 shipping_text">{l s="%s left until a free shipping" sprintf=[$amountLeftDisplay]}</div>
{/if}
