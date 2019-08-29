<?php

use PrestaShop\PrestaShop\Core\Product\ProductExtraContentFinder;

class ProductController extends ProductControllerCore
{
	protected function assignPriceAndTax()
    {
        $id_customer = (isset($this->context->customer) ? (int) $this->context->customer->id : 0);
        $id_group = (int) Group::getCurrent()->id;
        $id_country = $id_customer ? (int) Customer::getCurrentCountry($id_customer) : (int) Tools::getCountry();

        // Tax
        $tax = (float) $this->product->getTaxesRate(new Address((int) $this->context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')}));
        $this->context->smarty->assign('tax_rate', $tax);

        $product_price_with_tax = Product::getPriceStatic($this->product->id, true, null, 6);
        if (Product::$_taxCalculationMethod == PS_TAX_INC) {
            $product_price_with_tax = Tools::ps_round($product_price_with_tax, 2);
        }

        $id_currency = (int) $this->context->cookie->id_currency;
        $id_product = (int) $this->product->id;
        $id_product_attribute = Tools::getValue('id_product_attribute', null);
        $id_shop = $this->context->shop->id;

        $quantity_discounts = SpecificPrice::getQuantityDiscounts($id_product, $id_shop, $id_currency, $id_country, $id_group, $id_product_attribute, false, (int) $this->context->customer->id);
        foreach ($quantity_discounts as &$quantity_discount) {
            if ($quantity_discount['id_product_attribute']) {
                $combination = new Combination((int) $quantity_discount['id_product_attribute']);
                $attributes = $combination->getAttributesName((int) $this->context->language->id);
                if (count($attributes))
                {
	                foreach ($attributes as $attribute) {
	                    $quantity_discount['attributes'] = $attribute['name'].' - ';
	                }
	                $quantity_discount['attributes'] = rtrim($quantity_discount['attributes'], ' - ');
                }
            }
            if ((int) $quantity_discount['id_currency'] == 0 && $quantity_discount['reduction_type'] == 'amount') {
                $quantity_discount['reduction'] = Tools::convertPriceFull($quantity_discount['reduction'], null, Context::getContext()->currency);
            }
        }

        $product_price = $this->product->getPrice(Product::$_taxCalculationMethod == PS_TAX_INC, false);
        $this->quantity_discounts = $this->formatQuantityDiscounts($quantity_discounts, $product_price, (float) $tax, $this->product->ecotax);

        $this->context->smarty->assign(array(
            'no_tax' => Tax::excludeTaxeOption() || !$tax,
            'tax_enabled' => Configuration::get('PS_TAX') && !Configuration::get('AEUC_LABEL_TAX_INC_EXC'),
            'customer_group_without_tax' => Group::getPriceDisplayMethod($this->context->customer->id_default_group),
        ));
    }

    public function getTemplateVarProduct()
    {
        $productSettings = $this->getProductPresentationSettings();
        // Hook displayProductExtraContent
        $extraContentFinder = new ProductExtraContentFinder();

        $product = $this->objectPresenter->present($this->product);

        if ($product['wine'])
            $productSettings->include_taxes = false;

        $product['id_product'] = (int) $this->product->id;
        $product['out_of_stock'] = (int) $this->product->out_of_stock;
        $product['new'] = (int) $this->product->new;
        $product['id_product_attribute'] = $this->getIdProductAttribute();
        $product['minimal_quantity'] = $this->getProductMinimalQuantity($product);
        $product['quantity_wanted'] = $this->getRequiredQuantity($product);
        $product['extraContent'] = $extraContentFinder->addParams(array('product' => $this->product))->present();
        $product['is_private_sale_product'] = \Product::isPrivateSaleProduct((int) $this->product->id);

        $product_full = Product::getProductProperties($this->context->language->id, $product, $this->context);

        $product_full = $this->addProductCustomizationData($product_full);

        $product_full['show_quantities'] = (bool) (
            Configuration::get('PS_DISPLAY_QTIES')
            && Configuration::get('PS_STOCK_MANAGEMENT')
            && $this->product->quantity > 0
            && $this->product->available_for_order
            && !Configuration::isCatalogMode()
        );
        $product_full['quantity_label'] = ($this->product->quantity > 1) ? $this->trans('Items', array(), 'Shop.Theme.Catalog') : $this->trans('Item', array(), 'Shop.Theme.Catalog');
        $product_full['quantity_discounts'] = $this->quantity_discounts;

        if ($product_full['unit_price_ratio'] > 0) {
            $unitPrice = ($productSettings->include_taxes) ? $product_full['price'] : $product_full['price_tax_exc'];
            $product_full['unit_price'] = $unitPrice / $product_full['unit_price_ratio'];
        }

        $group_reduction = GroupReduction::getValueForProduct($this->product->id, (int) Group::getCurrent()->id);
        if ($group_reduction === false) {
            $group_reduction = Group::getReduction((int) $this->context->cookie->id_customer) / 100;
        }
        $product_full['customer_group_discount'] = $group_reduction;

        $presenter = $this->getProductPresenter();

        foreach ($product_full['features'] as $k => $v)
        if ($k == 14)
            $product_full['features'][$k]['value'] = $product['grape'];

        $product_full['packaging_price'] = Db::getInstance()->getValue("SELECT `packaging_price` FROM `" . _DB_PREFIX_ . "product_attribute` WHERE `id_product_attribute`='" . pSQL($product_full['id_product_attribute']) . "'");
        $product_full['second_wine'] = $this->getSecondWine();
        $product_full['accessories'] = $this->getAccessories();

        $categories = Product::getProductCategories($product['id_product']);
        $product_full['property'] = '';
        $product_full['property_link'] = '';

        if (count($categories) > 0)
        foreach ($categories as $cat)
        {
            $category = new Category($cat, $this->context->language->id);
            $parent = new Category($category->id_parent, $this->context->language->id);

            if (preg_match('@propriete@', $parent->link_rewrite))
            {
                $product_full['property'] = $category->description;
                $cat_products = $category->getProducts($this->context->language->id, 1, 20);
                
                if (count($cat_products) > 1)
                    $product_full['property_link'] = $this->context->link->getCategoryLink($category);
            }
        }

        return $presenter->present(
            $productSettings,
            $product_full,
            $this->context->language
        );
    }

    public function getSecondWine()
    {
        if (!$this->product->id_second_wine || empty($this->product->id_second_wine))
            return false;

        $id_product = Product::getIdByRef($this->product->id_second_wine);
        if (!$id_product)
            return false;

        $productSettings = $this->getProductPresentationSettings();
        $presenter = $this->getProductPresenter();
        $assembler = new ProductAssembler(Context::getContext());

        return $presenter->present(
            $productSettings,
            $assembler->assembleProduct(array('id_product' => $id_product)),
            $this->context->language
        );
    }

    public function getAccessories()
    {
        $categorie = new Category($this->product->id_category_default);

        $sql = "SELECT cp.`id_product` 
            FROM `" . _DB_PREFIX_ . "category_product` cp
            RIGHT JOIN `"._DB_PREFIX_."product` p ON p.`id_product`=cp.`id_product`
            WHERE cp.`id_category`='" . pSQL($categorie->id_parent == 2 || $categorie->id == 2 ? $categorie->id : $categorie->id_parent) . "'
            AND cp.`id_product` != '" . pSQL($this->product->id) . "'
            ORDER BY RAND()
            LIMIT 4";

        $res = Db::getInstance()->executeS($sql);

        if (!count($res))
            return false;

        $productSettings = $this->getProductPresentationSettings();
        $presenter = $this->getProductPresenter();
        $assembler = new ProductAssembler(Context::getContext());

        $accessories = [];
        foreach ($res as $product)
            $accessories[] = $presenter->present(
            $productSettings,
            $assembler->assembleProduct($product),
            $this->context->language
        );

        return $accessories;
    }

    private function getIdProductAttribute()
    {
        $requestedIdProductAttribute = (int)Tools::getValue('id_product_attribute');

        if (!Configuration::get('PS_DISP_UNAVAILABLE_ATTR')) {
            $productAttributes = array_filter(
                $this->product->getAttributeCombinations(),
                function ($elem) {
                    return $elem['quantity'] > 0;
                });
            $productAttribute = array_filter(
                $productAttributes,
                function ($elem) use ($requestedIdProductAttribute) {
                    return $elem['id_product_attribute'] == $requestedIdProductAttribute;
                });
            if (empty($productAttribute) && !empty($productAttributes)) {
                return (int)array_shift($productAttributes)['id_product_attribute'];
            }
        }
        return $requestedIdProductAttribute;
    }
}