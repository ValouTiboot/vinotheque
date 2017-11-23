<?php

class GiftCardOverride extends GiftCard
{
	public function install()
    {
        if (!$this->existsTab($this->tab_class)) {
            if (!$this->addTab($this->tab_name, $this->tab_class, 0)) {
                return false;
            }
        }

        if (!parent::install()
            || !$this->registerHook('header')
            || !$this->registerHook('footer')
            || !$this->registerHook('backOfficeHeader')
            || !$this->registerHook('actionCartSave')
            || !$this->registerHook('actionProductDelete')
            || !$this->registerHook('actionProductUpdate')
            || !$this->registerHook('displayProductButtons')
            || !$this->registerHook('displayMyAccountBlock')
            || !$this->registerHook('displayCustomerAccount')
            || !$this->registerHook('displayOrderConfirmation')
            || !$this->registerHook('actionOrderStatusPostUpdate')
            || !$this->registerHook('newOrder')
            || !$this->registerHook('displayCheckoutForm')
            || !Configuration::updateValue('GIFT_APPROVAL_STATUS', '2')
            || !Gift::createTable()
            || !copy(_PS_MODULE_DIR_.'giftcard/views/img/GiftCard.gif', _PS_MODULE_DIR_.'giftcard/GiftCard.gif')) {
            return false;
        }
        return true;
    }

	public function hookDisplayCheckoutForm($params)
    {
        return $this->display(__FILE__, 'views/templates/hook/'.$this->tpl_version.'/gift_form.tpl');
    }
}