<?php

class ReferralByPhoneOverride extends ReferralByPhone
{
	public function hookActionSubmitAccountBefore($params)
    {
        if (Tools::getIsset('referralbyphone') && Tools::getValue('referralbyphone') == '')
            return true;

        $sponsor = array();
        $sponsor_data = Configuration::get('REFERRALPH_SPONSOR_DATA');

        if (!empty($this->context->cookie->ws_sponsor_id)) {
            $sponsor = new Customer($this->context->cookie->ws_sponsor_id);
        } else {
            $sponsorField = trim(Tools::getValue('referralbyphone'));
            
            if ($sponsor_data == 'sponsorby_email') {
                $sponsor = new Customer();
                
                if (Validate::isEmail($sponsorField)) {
                    $sponsor = $sponsor->getByEmail($sponsorField);
                } else {
                    $sponsor = new Customer(ReferralByPhoneModule::decodeReferralLink($sponsorField));
                }
            }
        }

        if (!Validate::isLoadedObject($sponsor))
        {
            // $_error = $this->trans('Email or code referal is not valide.', array(), 'Shop.Theme.Error');
            $this->context->smarty->assign('referal_error', true);
            $this->errors[] = $this->l('Email or code referal is not valide.');
            return false;
        }

        return true;
    }

    public function hookShoppingCart($params)
    {
        if (!isset($params['cart']->id_customer)) {
            return false;
        }
        if (!($id_referralprogram = ReferralByPhoneModule::isSponsorised((int)($params['cart']->id_customer), true))) {
            return false;
        }
        $referralprogram = new ReferralByPhoneModule($id_referralprogram);
        if (!Validate::isLoadedObject($referralprogram)) {
            return false;
        }

        $cartRule = new CartRule($referralprogram->id_cart_rule, $this->context->language->id);
        if (!Validate::isLoadedObject($cartRule)) {
            return false;
        }
    
        if ($cartRule->checkValidity($this->context, false, false) === true) {
            $this->smarty->assign(array('discount_display' => ReferralByPhone::displayDiscount($cartRule->reduction_percent ? $cartRule->reduction_percent : $cartRule->reduction_amount, $cartRule->reduction_percent ? 1 : 2, new Currency($params['cookie']->id_currency)), 'discount' => $cartRule));
            return $this->display(__FILE__, 'shopping-cart.tpl');
        }
        return false;
    }
}