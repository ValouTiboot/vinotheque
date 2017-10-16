<?php

class orderfeesAjaxModuleFrontController extends ModuleFrontController
{
	public function initContent()
	{
		parent::initContent();

		if (Tools::getIsset('ajax'))
		{
			$this->ajaxDie(Tools::jsonEncode(array('resp' => $this->updateFees())));
		}
	}

	public function updateFees()
    {
    	$this->context->cart->current_type = $this->module::DISABLE_CHECKING;
        
        CartRule::autoRemoveFromCart();
        CartRule::autoAddToCart();
        
        $fees = array();
        $discounts = $this->context->cart->getCartRules();
        $cookie = Context::getContext()->cookie;
        $products = $this->context->cart->getProducts();

        foreach ($discounts as $index => &$discount) 
        {
            if (!$discount['is_fee']) {
                continue;
            }

            $add_price = 0;
            if (preg_match('@primeur@i', $discount['name']))
            {
	            foreach ($products as $product)
	            {
	            	if ($product['wine'])
	            	{
	            		$add_price += ($product['total_wt']*1.5)/100;
	            	}
	            }
                $discounts[$index]['obj']->reduction_amount += $add_price;
                // $discount['obj']->unit_value_real += $add_price;
                // $discount['obj']->unit_value_real_exc += $add_price;
                $discounts[$index]['reduction_amount'] += $add_price;
                $discounts[$index]['value_real'] -= $add_price;
                $discounts[$index]['value_tax_exc'] -= $add_price;
            }
            

            if ($discount['is_fee']) {
                $option_selected = isset($cookie->{'enable_option_' . $discount['id_cart_rule']});

                if (($discount['display_selectable']) || (($discount['display_visible']) && $option_selected)) 
                {
                    $discount['is_checked'] = isset($cookie->{'enable_option_' . $discount['id_cart_rule']});
                    $fees[] = $discount;
                }
            } else {
                if ($discount['display_visible']) {
                    $fees[] = $discount;
                }
            }

            if ($discount['is_fee']) {
                unset($discounts[$index]);
            }
        }
        // echo '<pre>';
        // print_r($this->context->cart);
        // die();
        
        $this->context->smarty->assign(array(
            'fees' => $fees,
            'module' => $this->module
        ));
        
        if (Tools::version_compare('1.7', _PS_VERSION_)) {
            $price_formatter = new PrestaShop\PrestaShop\Adapter\Product\PriceFormatter();
        
            $this->context->smarty->assign(array(
                'tax' => new TaxConfiguration(),
                'price' => $price_formatter
            ));

            return $this->context->smarty->fetch(_PS_THEME_DIR_ . 'modules/orderfees/views/templates/hook/1.7/cart-voucher.tpl');
        }
        
        return $this->display(__FILE__, 'cart-voucher.tpl');
    }
}