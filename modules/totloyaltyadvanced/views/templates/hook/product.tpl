{*
* @version 1.0
* @author 202-ecommerce
* @copyright 2014-2015 202-ecommerce
* @license ?
*}
<script type="text/javascript">
	var point_rate = {$point_rate|escape:'html':'UTF-8'};
	var point_value = {$point_value|escape:'html':'UTF-8'};
	var points_in_cart = {$points_in_cart|escape:'html':'UTF-8'};
	var none_award = {$none_award|escape:'html':'UTF-8'};
	var multiplier = {$multiplier|escape:'html':'UTF-8'};
	var old_listen_combination;
	var default_points = {$points|escape:'html':'UTF-8'};

	$(document).ready(function() {
		// Force color "button" to fire event change
		$('#color_to_pick_list').click(function() {
			$('#color_pick_hidden').triggerHandler('change');
		});

		listenCombination();
		// Catch all attribute changeent of the product
	});

	function listenCombination()
	{
		var id_combination = $('#idCombination').val();
		var priceWithDiscountsDisplay;
		if (old_listen_combination != id_combination) {
			if (typeof combinations !== 'undefined') {
				for (k in combinations) {
					if (combinations[k]['idCombination'] == id_combination) {
						var combination = combinations[k];
						if (typeof combination != 'undefined') {
							var basePriceWithoutTax;
	                        var group_reduction;
	                        var productBasePriceTaxExcl;
	                        var productBasePriceTaxExcluded;
							var gr = group_reduction;
							// Set product (not the combination) base price
							if (typeof(productBasePriceTaxExcl) != "undefined") {
								basePriceWithoutTax = productBasePriceTaxExcl;
							} else 	{
								basePriceWithoutTax = productBasePriceTaxExcluded;
								gr = parseInt(gr) - 1;
							}

							var priceWithGroupReductionWithoutTax = 0;

							// Apply combination price impact
							// 0 by default, +x if price is inscreased, -x if price is decreased
							basePriceWithoutTax = basePriceWithoutTax + combination.price;

							// If a specific price redefine the combination base price
							if (combination.specific_price && combination.specific_price.price > 0) {
								basePriceWithoutTax = combination.specific_price.price;
							}

							// Apply group reduction
							priceWithGroupReductionWithoutTax = basePriceWithoutTax * (1 - gr);
							var priceWithDiscountsWithoutTax = priceWithGroupReductionWithoutTax;

							if (typeof(customerGroupWithoutTax) == 'undefined') {
								var customerGroupWithoutTax = false;
							}

							// Apply Tax if necessary
							if (noTaxForThisProduct || customerGroupWithoutTax) {
								priceWithDiscountsDisplay = priceWithDiscountsWithoutTax;
							} else {
								priceWithDiscountsDisplay = priceWithDiscountsWithoutTax * (taxRate/100 + 1);
							}

							if (default_eco_tax) {
								priceWithDiscountsDisplay = priceWithDiscountsDisplay + default_eco_tax * (1 + ecotaxTax_rate / 100);
							}

							if (combination.specific_price) {
								if (combination.specific_price.reduction_percent) {
									combination.specific_price.reduction = combination.specific_price.reduction_percent / 100;
								}  else if (combination.specific_price.reduction_amount) {
									combination.specific_price.reduction = combination.specific_price.reduction_amount;
								}
							}

							// Apply specific price (discount)
							// Note: Reduction amounts are given after tax
							if (combination.specific_price && combination.specific_price.reduction > 0) {
								if (combination.specific_price.reduction_type == 'amount') {
									priceWithDiscountsDisplay = priceWithDiscountsDisplay - combination.specific_price.reduction;
								} else if (combination.specific_price.reduction_type == 'percentage') {
									priceWithDiscountsDisplay = priceWithDiscountsDisplay * (1 - combination.specific_price.reduction);
								}
							}
						}
						updateLoyaltyPoints(priceWithDiscountsDisplay);
						old_listen_combination = id_combination;
					}
				}
			}
		}

		setTimeout('listenCombination()', 0100);
	}

	function updateLoyaltyPoints(price)
	{
		if (typeof(productPrice) == 'undefined' || typeof(productPriceWithoutReduction) == 'undefined') {
			return;
		}

		var points = Math.round(price / point_rate) * multiplier;
		if (points != default_points)
			points = default_points;
		var total_points = points_in_cart + points;
		var voucher = total_points * point_value;

		if (!none_award && productPriceWithoutReduction != productPrice) {
			$('#loyalty').html("{l s='No reward points for this product because there\'s already a discount.' mod='totloyaltyadvanced'}");
		} else if (!points) {
			$('#loyalty').html("{l s='No reward points for this product.' mod='totloyaltyadvanced'}");
		} else {
			var content = "{l s='By buying this product you can collect up to' mod='totloyaltyadvanced'} <b><span id=\"loyalty_points\">"+points+'</span> ';
			if (points > 1) {
				content += "{l s='loyalty points' mod='totloyaltyadvanced'}</b>. ";
			} else {
				content += "{l s='loyalty point' mod='totloyaltyadvanced'}</b>. ";
			}
			
			content += "{l s='Your cart will total' mod='totloyaltyadvanced'} <b><span id=\"total_loyalty_points\">"+total_points+'</span> ';
			if (total_points > 1) {
				content += "{l s='points' mod='totloyaltyadvanced'}";
			} else {
				content += "{l s='point' mod='totloyaltyadvanced'}";
			}
			
			content += "</b> {l s='that can be converted into a voucher of' mod='totloyaltyadvanced'} ";
			content += '<span id="loyalty_price">'+formatCurrency(voucher, currencyFormat, currencySign, currencyBlank)+'</span>.';
			$('#loyalty').html(content);
		}
	}
</script>
<p id="loyalty" class="align_justify">
	{if $points}
		{l s='By buying this product you can collect up to' mod='totloyaltyadvanced'} <b><span id="totLoyaltyAdvanced_points">{$points|escape:'html':'UTF-8'}</span> 
		{if $points > 1}{l s=' points' mod='totloyaltyadvanced'}{else}{l s=' point' mod='totloyaltyadvanced'}{/if}</b>.
		{l s='Your cart will total' mod='totloyaltyadvanced'} <b><span id="total_totLoyaltyAdvanced_points">{$total_points|escape:'html':'UTF-8'}</span> 
		{if $total_points > 1}{l s='points' mod='totloyaltyadvanced'}{else}{l s='point' mod='totloyaltyadvanced'}{/if}</b> {l s='that can be converted into a voucher of' mod='totloyaltyadvanced'} 
		<span id="loyalty_price">{convertPrice price=$voucher}</span>.
	{else}
		{if isset($no_pts_discounted) && $no_pts_discounted == 1}
			{l s='No reward points for this product because there\'s already a discount.' mod='totloyaltyadvanced'}
		{else}
			{l s='No reward points for this product.' mod='totloyaltyadvanced'}
		{/if}
	{/if}
</p>
<br class="clear" />
