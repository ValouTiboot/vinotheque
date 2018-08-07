<?php

/**
* 
*/
class FreeShippingAmountDisplayOverride extends FreeShippingAmountDisplay
{
	public function install()
    {
        return parent::install() && $this->registerHook('displayExpressCheckoutCustom');
    }

    public function hookDisplayExpressCheckoutCustom()
    {
        return $this->getMessageHTML();
    }
}
