<?php

class OrderFeesOverride extends OrderFees
{
    public $_path;

	public function registerHooks()
    {
        return parent::registerHooks()
            && $this->registerHook('actionAdminCartRulesListingFieldsModifier')
            && $this->registerHook('actionObjectCartRuleUpdateBefore')
            && $this->registerHook('actionAssociatedRestrictionsPayment')
            && $this->registerHook('actionCartRuleCtor')
            && $this->registerHook('actionCartRuleCheckValidity')
            && $this->registerHook('actionCartRuleGetContextualValueBefore')
            && $this->registerHook('actionCartRuleGetContextualValueAfter')
            && $this->registerHook('actionCartRuleAdd')
            && $this->registerHook('actionCartRuleRemove')
            && $this->registerHook('actionCartGetPackageShippingCost')
            && $this->registerHook('actionAdminCartsControllerHelperDisplay')
            && $this->registerHook('actionAdminOrdersControllerHelperDisplay')
            && $this->registerHook('actionValidateOrder')
            && $this->registerHook('actionGetIDZoneByAddressID')
            && $this->registerHook('actionObjectCartUpdateBefore')
            && $this->registerHook('actionObjectOrderCartRuleAddAfter')
            && $this->registerHook('actionProductPriceCalculation')
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayAdminCartsView')
            && $this->registerHook('displayAdminOrder')
            && $this->registerHook('displayCartRuleBlockCart')
            && $this->registerHook('displayCartRuleBlockCartLayer')
            && $this->registerHook('displayCartRuleShoppingCart')
            && $this->registerHook('displayCartRuleOrderDetail')
            && $this->registerHook('displayCartRuleOrderPayment')
            && $this->registerHook('displayCartRuleInvoiceProductTab')
            && $this->registerHook('displayCartRuleInvoiceB2B')
            && $this->registerHook('displayCartRuleDeliverySlipProductTab')
            && $this->registerHook('displayCartRuleOrderSlipProductTab')
            && $this->registerHook('displayCartRuleAdminOrders')
            && $this->registerHook('displayCartRuleOrderPaymentOption')
            && $this->registerHook('displayCartRuleInvoiceTaxTab')
            && $this->registerHook('displayCartRuleCartVoucher')
            && $this->registerHook('displayCartRuleOrderConfirmation')
            && $this->registerHook('displayCartRuleOrderDetailReturn')
            && $this->registerHook('displayCartRuleOrderDetailNoReturn')
            && $this->registerHook('displayPaymentTop')
            && $this->registerHook('displayCartRuleAddress')
            && $this->registerHook('displayBeforeCarrier')
            && $this->registerHook('displayCartRuleProductAttributes')
            && $this->registerHook('displayShoppingCartDetailFooter')
            && $this->registerHook('displayCartRuleProductFees');
    }

    public function hookDisplayShoppingCartDetailFooter(&$params)
    {
        $params['discounts'] = $this->context->cart->getCartRules();
        $result = $this->displayFees($params, 'cart-voucher.tpl', self::CONTEXT_CART, self::CLEAN);        
        // $cart = $params['smarty']->getTemplateVars('cart');
        // $price_formatter = new PrestaShop\PrestaShop\Adapter\Product\PriceFormatter();
        // $products = $this->context->cart->getProducts();
        
        // foreach ($params['discounts'] as $index => &$discount) 
        // {
        //     if ((!preg_match('@primeur@i', $discount['name'])) && ($discount['is_fee'] & self::IS_FEE) && ($discount['is_fee'] & self::CONTEXT_CART) || ($discount['is_fee'] & self::IS_SHIPPING)) 
        //     {
        //         unset($params['discounts'][$index]);
        //         continue;
        //     }

        //     if (preg_match('@primeur@i', $discount['name']))
        //     {
        //         // $discount['reduction_formatted'] = $price_formatter->format(abs($discount['value_real']));
        //         // if (isset($cart['vouchers']['added'][$discount['id_cart_rule']]))
        //         //     $cart['vouchers']['added'][$discount['id_cart_rule']]['reduction_formatted'] = $price_formatter->format($cart['vouchers']['added'][$discount['id_cart_rule']]['reduction_amount']);
        //         // var_dump('expression');
        //     }
        //     else if (isset($discount['reduction_percent']) && $discount['reduction_amount'] == '0.00') 
        //         $discount['reduction_formatted'] = '-'.$discount['reduction_percent'].'%';
        //     elseif (isset($discount['reduction_amount']) && $discount['reduction_amount'] > 0)
        //         $discount['reduction_formatted'] = '-'.$price_formatter->format($discount['reduction_amount']);

        //     $discount['delete_url'] = $this->context->link->getPageLink(
        //         'cart',
        //         true,
        //         null,
        //         array(
        //             'deleteDiscount' => $discount['id_cart_rule'],
        //             'token' => Tools::getToken(false),
        //         )
        //     );
        // }

        // $cart['vouchers']['added'] = $params['discounts'];
        // $params['smarty']->assign('cart', $cart);
        
        return $result;
    }

    public function displayFeesOnPDF($params, $template, $context = self::CONTEXT_PDF)
    {
        $order = $params['order'];
        
        $this->context->smarty->assign(array(
            'order' => $order,
            'fees' => $this->getFeesByOrder($order, $context)
        ));
        
        $discounts = $params['discounts'];
        
        if (!count($discounts) || empty($discounts))
            return false;

        foreach ($discounts as $index => $discount) {
            $object = new CartRule($discount['id_cart_rule']);
            
            if ($object->is_fee & self::IS_FEE) {
                unset($discounts[$index]);
            }
        }
        
        $params['smarty']->assign('cart_rules', $discounts);
        
        if (Tools::version_compare('1.7', _PS_VERSION_)) {
            $price_formatter = new PrestaShop\PrestaShop\Adapter\Product\PriceFormatter();
        
            $this->context->smarty->assign(array(
                'tax' => new TaxConfiguration(),
                'price' => $price_formatter
            ));
            
            // return $this->display(__FILE__, '1.7/' . $template);
        }
        
        return $this->display(__FILE__, $template);
    }
}