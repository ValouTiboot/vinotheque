<?php

class OrderDetail extends OrderDetailCore
{
	/**
     * Check the order status
     * @param array $product
     * @param int $id_order_state
     */
    protected function checkProductStock($product, $id_order_state)
    {
        if ($id_order_state != Configuration::get('PS_OS_CANCELED') && $id_order_state != Configuration::get('PS_OS_ERROR')) {
            $update_quantity = true;
            if (!StockAvailable::dependsOnStock($product['id_product'])) {
            	if ($this->context->cart->id_carrier == '1')
            	{
            		$current_qty = StockAvailable::getShopQuantityAvailableByProduct($product['id_product'], $product['id_product_attribute']);

                	$update_quantity = StockAvailable::updateShopQuantity($product['id_product'], $product['id_product_attribute'], $current_qty-(int)$product['cart_quantity']);
            	}
                else
                	$update_quantity = StockAvailable::updateQuantity($product['id_product'], $product['id_product_attribute'], -(int)$product['cart_quantity']);
            }

            if ($update_quantity) {
                $product['stock_quantity'] -= $product['cart_quantity'];
            }

            if ($product['stock_quantity'] < 0 && Configuration::get('PS_STOCK_MANAGEMENT')) {
                $this->outOfStock = true;
            }
            Product::updateDefaultAttribute($product['id_product']);
        }
    }
}