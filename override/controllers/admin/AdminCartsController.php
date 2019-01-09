<?php
/**
 *  Order Fees
 *
 *  @author    motionSeed <ecommerce@motionseed.com>
 *  @copyright 2017 motionSeed. All rights reserved.
 *  @license   https://www.motionseed.com/en/license-module.html
 */
class AdminCartsController extends AdminCartsControllerCore
{
    public function ajaxProcessUpdateQty()
    {
        if ($this->access('edit')) {
            $errors = array();
            if (!$this->context->cart->id) {
                return;
            }
            if ($this->context->cart->OrderExists()) {
                $errors[] = $this->trans('An order has already been placed with this cart.', array(), 'Admin.Catalog.Notification');
            } elseif (!($id_product = (int)Tools::getValue('id_product')) || !($product = new Product((int)$id_product, true, $this->context->language->id))) {
                $errors[] = $this->trans('Invalid product', array(), 'Admin.Catalog.Notification');
            } elseif (!($qty = Tools::getValue('qty')) || $qty == 0) {
                $errors[] = $this->trans('Invalid quantity', array(), 'Admin.Catalog.Notification');
            }

            // Don't try to use a product if not instanciated before due to errors
            if (isset($product) && $product->id) {
                if (($id_product_attribute = Tools::getValue('id_product_attribute')) != 0) {
                    if (!Product::isAvailableWhenOutOfStock($product->out_of_stock) && !Attribute::checkAttributeQty((int)$id_product_attribute, (int)$qty) && !Attribute::checkAttributeShopQty((int)$id_product_attribute, (int)$qty)) {
                        $errors[] = $this->trans('There are not enough products in stock.', array(), 'Admin.Catalog.Notification');
                    }
                } elseif (!$product->checkQty((int)$qty)) {
                    $errors[] = $this->trans('There are not enough products in stock.', array(), 'Admin.Catalog.Notification');
                }
                if (!($id_customization = (int)Tools::getValue('id_customization', 0)) && !$product->hasAllRequiredCustomizableFields()) {
                    $errors[] = $this->trans('Please fill in all the required fields.', array(), 'Admin.Notifications.Error');
                }
                $this->context->cart->save();
            } else {
                $errors[] = $this->trans('This product cannot be added to the cart.', array(), 'Admin.Catalog.Notification');
            }

            if (!count($errors)) {
                if ((int)$qty < 0) {
                    $qty = str_replace('-', '', $qty);
                    $operator = 'down';
                } else {
                    $operator = 'up';
                }

                if (!($qty_upd = $this->context->cart->updateQty($qty, $id_product, (int)$id_product_attribute, (int)$id_customization, $operator))) {
                    $errors[] = $this->trans('You already have the maximum quantity available for this product.', array(), 'Admin.Catalog.Notification');
                } elseif ($qty_upd < 0) {
                    $minimal_qty = $id_product_attribute ? Attribute::getAttributeMinimalQty((int)$id_product_attribute) : $product->minimal_quantity;
                    $errors[] = $this->trans('You must add a minimum quantity of %d', array($minimal_qty), 'Admin.Orderscustomers.Notification');
                }
            }

            echo json_encode(array_merge($this->ajaxReturnVars(), array('errors' => $errors)));
        }
    }

    /*
    * module: orderfees
    * date: 2018-11-19 10:31:03
    * version: 1.8.9
    */
    public function setHelperDisplay(Helper $helper)
    {
        if (isset($this->tpl_view_vars['cart'])) {
            Hook::exec('actionAdminCartsControllerHelperDisplay', array(
                'controller' => &$this,
                'helper' => &$helper
            ));
        }
        
        parent::setHelperDisplay($helper);
    }
}
