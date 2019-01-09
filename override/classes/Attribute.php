<?php

class Attribute extends AttributeCore
{
	public static function checkAttributeShopQty($idProductAttribute, $qty, Shop $shop = null)
    {
        if (!$shop) {
            $shop = Context::getContext()->shop;
        }

        $result = StockAvailable::getQuantityAvailableByProduct(null, (int) $idProductAttribute, $shop->id);

        return ($result && $qty <= $result);
    }
}