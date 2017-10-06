<?php

class CartgiftOverride extends Cartgift
{
    public function hookDisplayCartRuleCartVoucher(&$params)
    {
        return $this->hookDisplayShoppingCartFooter($params);
    }
}
