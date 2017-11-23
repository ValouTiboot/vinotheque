<?php
/**
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    FMM Modules
*  @copyright 2017 FMM Modules
*  @license   FMM Modules
*  @version   1.4.1
*/

class CartController extends CartControllerCore
{
	public $php_self = 'cart';

	protected $id_product;
	protected $id_product_attribute;
	protected $id_address_delivery;
	protected $customization_id;
	protected $qty;
	public $ssl = true;
	protected $new_price;

	protected $ajax_refresh = false;

	/**
	 * Initialize cart controller
	 * @see FrontController::init()
	 */
	public function init()
	{
		parent::init();

		// Send noindex to avoid ghost carts by bots
		header('X-Robots-Tag: noindex, nofollow', true);

		// Get page main parameters
		$this->id_product = (int)Tools::getValue('id_product', null);
		$this->id_product_attribute = (int)Tools::getValue('id_product_attribute', Tools::getValue('ipa'));
		$this->customization_id = (int)Tools::getValue('id_customization');
		$this->qty = abs(Tools::getValue('qty', 1));
		$this->id_address_delivery = (int)Tools::getValue('id_address_delivery');
		$this->new_price = (float)Tools::getValue('giftcard_price');
	}

	/**
	 * This process add or update a product in the cart
	 */
	protected function processChangeProductInCart()
	{
		//$mode = (Tools::getValue('update') && $this->id_product) ? 'update' : 'add';

		if ($this->qty == 0) {
			$this->errors[] = Tools::displayError('Null quantity.', !Tools::getValue('ajax'));
		} else if (!$this->id_product) {
			$this->errors[] = Tools::displayError('Product not found', !Tools::getValue('ajax'));
		}

		$product = new Product($this->id_product, true, $this->context->language->id);
		if (!$product->id || !$product->active || !$product->checkAccess($this->context->cart->id_customer)) {
			$this->errors[] = Tools::displayError('This product is no longer available.', !Tools::getValue('ajax'));
			return;
		}

		$qty_to_check = $this->qty;
		$cart_products = $this->context->cart->getProducts();

		if (is_array($cart_products)) {
			foreach ($cart_products as $cart_product) {
				if ((empty($this->id_product_attribute) || $cart_product['id_product_attribute'] == $this->id_product_attribute) &&
					(!empty($this->id_product) && $cart_product['id_product'] == $this->id_product)) {
					$qty_to_check = $cart_product['cart_quantity'];

					if (Tools::getValue('op', 'up') == 'down') {
						$qty_to_check -= $this->qty;
					} else {
						$qty_to_check += $this->qty;
					}

					break;
				}
			}
		}

		// Check product quantity availability
		if ($this->id_product_attribute) {
			if (!Product::isAvailableWhenOutOfStock($product->out_of_stock) && !Attribute::checkAttributeQty($this->id_product_attribute, $qty_to_check)) {
				$this->errors[] = Tools::displayError('There isn\'t enough product in stock.', !Tools::getValue('ajax'));
			}
		} elseif ($product->hasAttributes()) {
			$minimumQuantity = ($product->out_of_stock == 2) ? !Configuration::get('PS_ORDER_OUT_OF_STOCK') : !$product->out_of_stock;
			$this->id_product_attribute = Product::getDefaultAttribute($product->id, $minimumQuantity);
			// @todo do something better than a redirect admin !!
			if (!$this->id_product_attribute) {
				Tools::redirectAdmin($this->context->link->getProductLink($product));
			} elseif (!Product::isAvailableWhenOutOfStock($product->out_of_stock) && !Attribute::checkAttributeQty($this->id_product_attribute, $qty_to_check)) {
				$this->errors[] = Tools::displayError('There isn\'t enough product in stock.', !Tools::getValue('ajax'));
			}
		} elseif (!$product->checkQty($qty_to_check))
			$this->errors[] = Tools::displayError('There isn\'t enough product in stock.', !Tools::getValue('ajax'));

		// If no errors, process product addition
		if (!$this->errors) {
			$this->setPrice();
			// Add cart if no cart found
			if (!$this->context->cart->id) {
				if (Context::getContext()->cookie->id_guest && _PS_VERSION_ >= 1.6) {
					$guest = new Guest(Context::getContext()->cookie->id_guest);
					$this->context->cart->mobile_theme = $guest->mobile_theme;
				}
				$this->context->cart->add();
				if ($this->context->cart->id) {
					$this->context->cookie->id_cart = (int)$this->context->cart->id;
				}
			}

			// Check customizable fields
			if (!$product->hasAllRequiredCustomizableFields() && !$this->customization_id) {
				$this->errors[] = Tools::displayError('Please fill in all of the required fields, and then save your customizations.', !Tools::getValue('ajax'));
			}

			if (!$this->errors) {
				$cart_rules = $this->context->cart->getCartRules();
				$update_quantity = $this->context->cart->updateQty($this->qty, $this->id_product, $this->id_product_attribute, $this->customization_id, Tools::getValue('op', 'up'), $this->id_address_delivery);
				if ($update_quantity < 0) {
					// If product has attribute, minimal quantity is set with minimal quantity of attribute
					$minimal_quantity = ($this->id_product_attribute) ? Attribute::getAttributeMinimalQty($this->id_product_attribute) : $product->minimal_quantity;
					$this->errors[] = sprintf(Tools::displayError('You must add %d minimum quantity', !Tools::getValue('ajax')), $minimal_quantity);
				} elseif (!$update_quantity) {
					$this->errors[] = Tools::displayError('You already have the maximum quantity available for this product.', !Tools::getValue('ajax'));
				} elseif ((int)Tools::getValue('allow_refresh')) {
					// If the cart rules has changed, we need to refresh the whole cart
					$cart_rules2 = $this->context->cart->getCartRules();
					if (count($cart_rules2) != count($cart_rules)) {
						$this->ajax_refresh = true;
					} else {
						$rule_list = array();
						foreach ($cart_rules2 as $rule) {
							$rule_list[] = $rule['id_cart_rule'];
						}
						foreach ($cart_rules as $rule) {
							if (!in_array($rule['id_cart_rule'], $rule_list)) {
								$this->ajax_refresh = true;
								break;
							}
						}
					}
				}
			}
		}

		$removed = CartRule::autoRemoveFromCart();
		CartRule::autoAddToCart();
		if (count($removed) && (int)Tools::getValue('allow_refresh')) {
			$this->ajax_refresh = true;
		}
	}

	public function setPrice()
	{
		$this->id_product = (int)Tools::getValue('id_product');
		$this->new_price = (float)Tools::getValue('giftcard_price');
		if (!empty($this->id_product) && !empty($this->new_price) && $this->new_price != 0) {
			$product = new Product($this->id_product);
			$product->price = $this->new_price;
			$product->update();
		}

	}
}
