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
}