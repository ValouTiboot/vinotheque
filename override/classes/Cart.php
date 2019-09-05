<?php

class Cart extends CartCore
{
    public function getProducts($refresh = false, $id_product = false, $id_country = null)
    {
        if (!$this->id) {
            return array();
        }
        if ($this->_products !== null && !$refresh) {
            if (is_int($id_product)) {
                foreach ($this->_products as $product) {
                    if ($product['id_product'] == $id_product) {
                        return array($product);
                    }
                }
                return array();
            }
            return $this->_products;
        }
        $sql = new DbQuery();
        $sql->select('cp.`id_product_attribute`, cp.`id_product`, cp.`quantity` AS cart_quantity, cp.id_shop, cp.`id_customization`, pl.`name`, p.`is_virtual`,
                        pl.`description_short`, pl.`available_now`, pl.`available_later`, product_shop.`id_category_default`, p.`id_supplier`,
                        p.`id_manufacturer`, m.`name` AS manufacturer_name, product_shop.`on_sale`, product_shop.`ecotax`, product_shop.`additional_shipping_cost`,
                        product_shop.`available_for_order`, product_shop.`price`, product_shop.`active`, product_shop.`unity`, product_shop.`unit_price_ratio`,
                        stock.`quantity` AS quantity_available, p.`width`, p.`height`, p.`depth`, stock.`out_of_stock`, p.`weight`, p.`wine`, p.`wine_delivery`, p.`wine_date`,
                        p.`date_add`, p.`date_upd`, IFNULL(stock.quantity, 0) as quantity, pl.`link_rewrite`, cl.`name` as category_name, cl.`link_rewrite` AS category,
                        CONCAT(LPAD(cp.`id_product`, 10, 0), LPAD(IFNULL(cp.`id_product_attribute`, 0), 10, 0), IFNULL(cp.`id_address_delivery`, 0), IFNULL(cp.`id_customization`, 0)) AS unique_id, cp.id_address_delivery,
                        product_shop.advanced_stock_management, ps.product_supplier_reference supplier_reference');
        $sql->from('cart_product', 'cp');
        $sql->leftJoin('product', 'p', 'p.`id_product` = cp.`id_product`');
        $sql->innerJoin('product_shop', 'product_shop', '(product_shop.`id_shop` = cp.`id_shop` AND product_shop.`id_product` = p.`id_product`)');
        $sql->leftJoin(
            'product_lang',
            'pl',
            'p.`id_product` = pl.`id_product`
            AND pl.`id_lang` = '.(int)$this->id_lang.Shop::addSqlRestrictionOnLang('pl', 'cp.id_shop')
        );
        $sql->leftJoin(
            'category_lang',
            'cl',
            'product_shop.`id_category_default` = cl.`id_category`
            AND cl.`id_lang` = '.(int)$this->id_lang.Shop::addSqlRestrictionOnLang('cl', 'cp.id_shop')
        );
        $sql->leftJoin('product_supplier', 'ps', 'ps.`id_product` = cp.`id_product` AND ps.`id_product_attribute` = cp.`id_product_attribute` AND ps.`id_supplier` = p.`id_supplier`');
        $sql->leftJoin('manufacturer', 'm', 'm.`id_manufacturer` = p.`id_manufacturer`');
        $sql->join(Product::sqlStock('cp', 'cp'));
        $sql->where('cp.`id_cart` = '.(int)$this->id);
        if ($id_product) {
            $sql->where('cp.`id_product` = '.(int)$id_product);
        }
        $sql->where('p.`id_product` IS NOT NULL');
        $sql->orderBy('cp.`date_add`, cp.`id_product`, cp.`id_product_attribute` ASC');
        if (Customization::isFeatureActive()) {
            $sql->select('cu.`id_customization`, cu.`quantity` AS customization_quantity');
            $sql->leftJoin(
                'customization',
                'cu',
                'p.`id_product` = cu.`id_product` AND cp.`id_product_attribute` = cu.`id_product_attribute` AND cp.`id_customization` = cu.`id_customization` AND cu.`id_cart` = '.(int)$this->id
            );
            $sql->groupBy('cp.`id_product_attribute`, cp.`id_product`, cp.`id_shop`, cp.`id_customization`');
        } else {
            $sql->select('NULL AS customization_quantity, NULL AS id_customization');
        }
        if (Combination::isFeatureActive()) {
            $sql->select('
                product_attribute_shop.`price` AS price_attribute, product_attribute_shop.`ecotax` AS ecotax_attr,
                IF (IFNULL(pa.`reference`, \'\') = \'\', p.`reference`, pa.`reference`) AS reference,
                (p.`weight`+ pa.`weight`) weight_attribute,
                IF (IFNULL(pa.`ean13`, \'\') = \'\', p.`ean13`, pa.`ean13`) AS ean13,
                IF (IFNULL(pa.`isbn`, \'\') = \'\', p.`isbn`, pa.`isbn`) AS isbn,
                IF (IFNULL(pa.`upc`, \'\') = \'\', p.`upc`, pa.`upc`) AS upc,
                IFNULL(product_attribute_shop.`minimal_quantity`, product_shop.`minimal_quantity`) as minimal_quantity,
                IF(product_attribute_shop.wholesale_price > 0,  product_attribute_shop.wholesale_price, product_shop.`wholesale_price`) wholesale_price
            ');
            $sql->leftJoin('product_attribute', 'pa', 'pa.`id_product_attribute` = cp.`id_product_attribute`');
            $sql->leftJoin('product_attribute_shop', 'product_attribute_shop', '(product_attribute_shop.`id_shop` = cp.`id_shop` AND product_attribute_shop.`id_product_attribute` = pa.`id_product_attribute`)');
        } else {
            $sql->select(
                'p.`reference` AS reference, p.`ean13`, p.`isbn`,
                p.`upc` AS upc, product_shop.`minimal_quantity` AS minimal_quantity, product_shop.`wholesale_price` wholesale_price'
            );
        }
        $sql->select('image_shop.`id_image` id_image, il.`legend`');
        $sql->leftJoin('image_shop', 'image_shop', 'image_shop.`id_product` = p.`id_product` AND image_shop.cover=1 AND image_shop.id_shop='.(int)$this->id_shop);
        $sql->leftJoin('image_lang', 'il', 'il.`id_image` = image_shop.`id_image` AND il.`id_lang` = '.(int)$this->id_lang);
        $result = Db::getInstance()->executeS($sql);
        $products_ids = array();
        $pa_ids = array();
        if ($result) {
            foreach ($result as $key => $row) {
                $products_ids[] = $row['id_product'];
                $pa_ids[] = $row['id_product_attribute'];
                $specific_price = SpecificPrice::getSpecificPrice($row['id_product'], $this->id_shop, $this->id_currency, $id_country, $this->id_shop_group, $row['cart_quantity'], $row['id_product_attribute'], $this->id_customer, $this->id);
                if ($specific_price) {
                    $reduction_type_row = array('reduction_type' => $specific_price['reduction_type']);
                } else {
                    $reduction_type_row = array('reduction_type' => 0);
                }
                $result[$key] = array_merge($row, $reduction_type_row);
            }
        }
        Product::cacheProductsFeatures($products_ids);
        Cart::cacheSomeAttributesLists($pa_ids, $this->id_lang);
        $this->_products = array();
        if (empty($result)) {
            return array();
        }
        $ecotax_rate = (float)Tax::getProductEcotaxRate($this->{Configuration::get('PS_TAX_ADDRESS_TYPE')});
        $apply_eco_tax = Product::$_taxCalculationMethod == PS_TAX_INC && (int)Configuration::get('PS_TAX');
        $cart_shop_context = Context::getContext()->cloneContext();
        $gifts = $this->getCartRules(CartRule::FILTER_ACTION_GIFT);
        $givenAwayProductsIds = array();
        if ($this->shouldSplitGiftProductsQuantity && count($gifts) > 0) {
            foreach ($gifts as $gift) {
                foreach ($result as $rowIndex => $row) {
                    if (!array_key_exists('is_gift', $result[$rowIndex])) {
                        $result[$rowIndex]['is_gift'] = false;
                    }
                    if (
                        $row['id_product'] == $gift['gift_product'] &&
                        $row['id_product_attribute'] == $gift['gift_product_attribute']
                    ) {
                        $row['is_gift'] = true;
                        $result[$rowIndex] = $row;
                    }
                }
                $index = $gift['gift_product'].'-'.$gift['gift_product_attribute'];
                if (!array_key_exists($index, $givenAwayProductsIds)) {
                    $givenAwayProductsIds[$index] = 1;
                } else {
                    $givenAwayProductsIds[$index]++;
                }
            }
        }
        foreach ($result as &$row) {
            if (!array_key_exists('is_gift', $row)) {
                $row['is_gift'] = false;
            }
            $givenAwayQuantity = 0;
            $giftIndex = $row['id_product'].'-'.$row['id_product_attribute'];
            if ($row['is_gift'] && array_key_exists($giftIndex, $givenAwayProductsIds)) {
                $givenAwayQuantity = $givenAwayProductsIds[$giftIndex];
            }
            if (!$row['is_gift'] || (int) $row['cart_quantity'] === $givenAwayQuantity) {
                $row = $this->applyProductCalculations($row, $cart_shop_context);
            } else {
                $this->_products[] = $this->applyProductCalculations($row, $cart_shop_context, $givenAwayQuantity);
                unset($row['is_gift']);
                $row = $this->applyProductCalculations(
                    $row,
                    $cart_shop_context,
                    $row['cart_quantity'] - $givenAwayQuantity
                );
            }
            $this->_products[] = $row;
        }
        return $this->_products;
    }
    protected function applyProductCalculations($row, $shopContext, $productQuantity = null)
    {
        if (is_null($productQuantity)) {
            $productQuantity = (int)$row['cart_quantity'];
        }
        if (isset($row['ecotax_attr']) && $row['ecotax_attr'] > 0) {
            $row['ecotax'] = (float)$row['ecotax_attr'];
        }
        $row['stock_quantity'] = (int)$row['quantity'];
        $row['quantity'] = $productQuantity;
        $customization_weight = Customization::getCustomizationWeight($row['id_customization']);
        if (isset($row['id_product_attribute']) && (int)$row['id_product_attribute'] && isset($row['weight_attribute'])) {
            $row['weight_attribute'] += $customization_weight;
            $row['weight'] = (float)$row['weight_attribute'];
        } else {
            $row['weight'] += $customization_weight;
        }
        if (Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_invoice') {
            $address_id = (int)$this->id_address_invoice;
        } else {
            $address_id = (int)$row['id_address_delivery'];
        }
        if (!Address::addressExists($address_id)) {
            $address_id = null;
        }
        if ($shopContext->shop->id != $row['id_shop']) {
            $shopContext->shop = new Shop((int)$row['id_shop']);
        }
        $address = Address::initialize($address_id, true);
        $id_tax_rules_group = Product::getIdTaxRulesGroupByIdProduct((int)$row['id_product'], $shopContext);
        $tax_calculator = TaxManagerFactory::getManager($address, $id_tax_rules_group)->getTaxCalculator();
        $specific_price_output = null;
        $row['price_without_reduction'] = Product::getPriceStatic(
            (int)$row['id_product'],
            true,
            isset($row['id_product_attribute']) ? (int)$row['id_product_attribute'] : null,
            6,
            null,
            false,
            false,
            $productQuantity,
            false,
            (int)$this->id_customer ? (int)$this->id_customer : null,
            (int)$this->id,
            $address_id,
            $specific_price_output,
            true,
            true,
            $shopContext,
            true,
            $row['id_customization']
        );
        $row['price_with_reduction'] = Product::getPriceStatic(
            (int)$row['id_product'],
            true,
            isset($row['id_product_attribute']) ? (int)$row['id_product_attribute'] : null,
            6,
            null,
            false,
            true,
            $productQuantity,
            false,
            (int)$this->id_customer ? (int)$this->id_customer : null,
            (int)$this->id,
            $address_id,
            $specific_price_output,
            true,
            true,
            $shopContext,
            true,
            $row['id_customization']
        );
        $row['price'] = $row['price_with_reduction_without_tax'] = Product::getPriceStatic(
            (int)$row['id_product'],
            false,
            isset($row['id_product_attribute']) ? (int)$row['id_product_attribute'] : null,
            6,
            null,
            false,
            true,
            $productQuantity,
            false,
            (int)$this->id_customer ? (int)$this->id_customer : null,
            (int)$this->id,
            $address_id,
            $specific_price_output,
            true,
            true,
            $shopContext,
            true,
            $row['id_customization']
        );
        switch (Configuration::get('PS_ROUND_TYPE')) {
            case Order::ROUND_TOTAL:
                $row['total'] = $row['price_with_reduction_without_tax'] * $productQuantity;
                $row['total_wt'] = $row['price_with_reduction'] * $productQuantity;
                break;
            case Order::ROUND_LINE:
                $row['total'] = Tools::ps_round(
                    $row['price_with_reduction_without_tax'] * $productQuantity,
                    _PS_PRICE_COMPUTE_PRECISION_
                );
                $row['total_wt'] = Tools::ps_round(
                    $row['price_with_reduction'] * $productQuantity,
                    _PS_PRICE_COMPUTE_PRECISION_
                );
                break;
            case Order::ROUND_ITEM:
            default:
                $row['total'] = Tools::ps_round(
                    $row['price_with_reduction_without_tax'],
                    _PS_PRICE_COMPUTE_PRECISION_
                ) * $productQuantity;
                $row['total_wt'] = Tools::ps_round(
                    $row['price_with_reduction'],
                    _PS_PRICE_COMPUTE_PRECISION_
                ) * $productQuantity;
                break;
        }
        $row['price_wt'] = $row['price_with_reduction'];
        $row['description_short'] = Tools::nl2br($row['description_short']);
        if ($row['id_product_attribute']) {
            $row2 = Image::getBestImageAttribute($row['id_shop'], $this->id_lang, $row['id_product'], $row['id_product_attribute']);
            if ($row2) {
                $row = array_merge($row, $row2);
            }
        }
        $row['reduction_applies'] = ($specific_price_output && (float)$specific_price_output['reduction']);
        $row['quantity_discount_applies'] = ($specific_price_output && $productQuantity >= (int)$specific_price_output['from_quantity']);
        $row['id_image'] = Product::defineProductImage($row, $this->id_lang);
        $row['allow_oosp'] = Product::isAvailableWhenOutOfStock($row['out_of_stock']);
        $row['features'] = Product::getFeaturesStatic((int)$row['id_product']);
        $name_feature = [];
        if (count($row['features']))
        foreach ($row['features'] as $feature)
        {
            if ($feature['id_feature'] == 10 || $feature['id_feature'] == 16)
                $name_feature[] = $feature['value'];
        }
        $row['features_name'] = implode(' - ', $name_feature);
        if (array_key_exists($row['id_product_attribute'] . '-' . $this->id_lang, self::$_attributesLists)) {
            $row = array_merge($row, self::$_attributesLists[$row['id_product_attribute'] . '-' . $this->id_lang]);
        }
        return Product::getTaxesInformations($row, $shopContext);
    }
    
    
    public function getDeliveryOptionList(Country $default_country = null, $flush = false)
    {
        static $cache = array();
        if (isset($cache[$this->id]) && !$flush) {
            return $cache[$this->id];
        }

        $delivery_option_list = array();
        $carriers_price = array();
        $carrier_collection = array();
        $package_list = $this->getPackageList($flush);

        // Foreach addresses
        foreach ($package_list as $id_address => $packages) {
            // Initialize vars
            $delivery_option_list[$id_address] = array();
            $carriers_price[$id_address] = array();
            $common_carriers = null;
            $best_price_carriers = array();
            $best_grade_carriers = array();
            $carriers_instance = array();

            // Get country
            if ($id_address) {
                $address = new Address($id_address);
                $country = new Country($address->id_country);
            } else {
                $country = $default_country;
            }

            // Foreach packages, get the carriers with best price, best position and best grade
            foreach ($packages as $id_package => $package) {
                // No carriers available
                if (count($packages) == 1 && count($package['carrier_list']) == 1 && current($package['carrier_list']) == 0) {
                    $cache[$this->id] = array();
                    return $cache[$this->id];
                }

                $carriers_price[$id_address][$id_package] = array();

                // Get all common carriers for each packages to the same address
                if (is_null($common_carriers)) {
                    $common_carriers = $package['carrier_list'];
                } else {
                    $common_carriers = array_intersect($common_carriers, $package['carrier_list']);
                }

                $best_price = null;
                $best_price_carrier = null;
                $best_grade = null;
                $best_grade_carrier = null;

                // Foreach carriers of the package, calculate his price, check if it the best price, position and grade
                foreach ($package['carrier_list'] as $id_carrier) {
                    if (!isset($carriers_instance[$id_carrier])) {
                        $carriers_instance[$id_carrier] = new Carrier($id_carrier);
                    }

                    $price_with_tax = $this->getPackageShippingCost((int)$id_carrier, true, $country, $package['product_list']);
                    $price_without_tax = $this->getPackageShippingCost((int)$id_carrier, false, $country, $package['product_list']);
                    if (is_null($best_price) || $price_with_tax < $best_price) {
                        $best_price = $price_with_tax;
                        $best_price_carrier = $id_carrier;
                    }
                    $carriers_price[$id_address][$id_package][$id_carrier] = array(
                        'without_tax' => $price_without_tax,
                        'with_tax' => $price_with_tax);

                    $grade = $carriers_instance[$id_carrier]->grade;
                    if (is_null($best_grade) || $grade > $best_grade) {
                        $best_grade = $grade;
                        $best_grade_carrier = $id_carrier;
                    }
                }

                $best_price_carriers[$id_package] = $best_price_carrier;
                $best_grade_carriers[$id_package] = $best_grade_carrier;
            }

            // Reset $best_price_carrier, it's now an array
            $best_price_carrier = array();
            $key = '';

            // Get the delivery option with the lower price
            foreach ($best_price_carriers as $id_package => $id_carrier) {
                $key .= $id_carrier.',';
                if (!isset($best_price_carrier[$id_carrier])) {
                    $best_price_carrier[$id_carrier] = array(
                        'price_with_tax' => 0,
                        'price_without_tax' => 0,
                        'package_list' => array(),
                        'product_list' => array(),
                    );
                }
                $best_price_carrier[$id_carrier]['price_with_tax'] += $carriers_price[$id_address][$id_package][$id_carrier]['with_tax'];
                $best_price_carrier[$id_carrier]['price_without_tax'] += $carriers_price[$id_address][$id_package][$id_carrier]['without_tax'];
                $best_price_carrier[$id_carrier]['package_list'][] = $id_package;
                $best_price_carrier[$id_carrier]['product_list'] = array_merge($best_price_carrier[$id_carrier]['product_list'], $packages[$id_package]['product_list']);
                $best_price_carrier[$id_carrier]['instance'] = $carriers_instance[$id_carrier];
                $real_best_price = !isset($real_best_price) || $real_best_price > $carriers_price[$id_address][$id_package][$id_carrier]['with_tax'] ?
                    $carriers_price[$id_address][$id_package][$id_carrier]['with_tax'] : $real_best_price;
                $real_best_price_wt = !isset($real_best_price_wt) || $real_best_price_wt > $carriers_price[$id_address][$id_package][$id_carrier]['without_tax'] ?
                    $carriers_price[$id_address][$id_package][$id_carrier]['without_tax'] : $real_best_price_wt;
            }

            // Add the delivery option with best price as best price
            $delivery_option_list[$id_address][$key] = array(
                'carrier_list' => $best_price_carrier,
                'is_best_price' => true,
                'is_best_grade' => false,
                'unique_carrier' => (count($best_price_carrier) <= 1)
            );

            // Reset $best_grade_carrier, it's now an array
            $best_grade_carrier = array();
            $key = '';

            // Get the delivery option with the best grade
            foreach ($best_grade_carriers as $id_package => $id_carrier) {
                $key .= $id_carrier.',';
                if (!isset($best_grade_carrier[$id_carrier])) {
                    $best_grade_carrier[$id_carrier] = array(
                        'price_with_tax' => 0,
                        'price_without_tax' => 0,
                        'package_list' => array(),
                        'product_list' => array(),
                    );
                }
                $best_grade_carrier[$id_carrier]['price_with_tax'] += $carriers_price[$id_address][$id_package][$id_carrier]['with_tax'];
                $best_grade_carrier[$id_carrier]['price_without_tax'] += $carriers_price[$id_address][$id_package][$id_carrier]['without_tax'];
                $best_grade_carrier[$id_carrier]['package_list'][] = $id_package;
                $best_grade_carrier[$id_carrier]['product_list'] = array_merge($best_grade_carrier[$id_carrier]['product_list'], $packages[$id_package]['product_list']);
                $best_grade_carrier[$id_carrier]['instance'] = $carriers_instance[$id_carrier];
            }

            // Add the delivery option with best grade as best grade
            if (!isset($delivery_option_list[$id_address][$key])) {
                $delivery_option_list[$id_address][$key] = array(
                    'carrier_list' => $best_grade_carrier,
                    'is_best_price' => false,
                    'unique_carrier' => (count($best_grade_carrier) <= 1)
                );
            }
            $delivery_option_list[$id_address][$key]['is_best_grade'] = true;

            // Get all delivery options with a unique carrier
            foreach ($common_carriers as $id_carrier) {
                $key = '';
                $package_list = array();
                $product_list = array();
                $price_with_tax = 0;
                $price_without_tax = 0;

                foreach ($packages as $id_package => $package) {
                    $key .= $id_carrier.',';
                    $price_with_tax += $carriers_price[$id_address][$id_package][$id_carrier]['with_tax'];
                    $price_without_tax += $carriers_price[$id_address][$id_package][$id_carrier]['without_tax'];
                    $package_list[] = $id_package;
                    $product_list = array_merge($product_list, $package['product_list']);
                }

                if (!isset($delivery_option_list[$id_address][$key])) {
                    $delivery_option_list[$id_address][$key] = array(
                        'is_best_price' => false,
                        'is_best_grade' => false,
                        'unique_carrier' => true,
                        'carrier_list' => array(
                            $id_carrier => array(
                                'price_with_tax' => $price_with_tax,
                                'price_without_tax' => $price_without_tax,
                                'instance' => $carriers_instance[$id_carrier],
                                'package_list' => $package_list,
                                'product_list' => $product_list,
                            )
                        )
                    );
                } else {
                    $delivery_option_list[$id_address][$key]['unique_carrier'] = (count($delivery_option_list[$id_address][$key]['carrier_list']) <= 1);
                }
            }
        }

        $cart_rules = CartRule::getCustomerCartRules(Context::getContext()->cookie->id_lang, Context::getContext()->cookie->id_customer, true, true, false, $this, true);

        $result = false;
        if ($this->id) {
            $result = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'cart_cart_rule WHERE id_cart = '.(int)$this->id);
        }

        $cart_rules_in_cart = array();

        if (is_array($result)) {
            foreach ($result as $row) {
                $cart_rules_in_cart[] = $row['id_cart_rule'];
            }
        }

        $total_products_wt = $this->getOrderTotal(true, Cart::ONLY_PRODUCTS);
        $total_products = $this->getOrderTotal(false, Cart::ONLY_PRODUCTS);

        $free_carriers_rules = array();

        $context = Context::getContext();
        foreach ($cart_rules as $cart_rule) {
            $total_price = $cart_rule['minimum_amount_tax'] ? $total_products_wt : $total_products;
            $total_price += $cart_rule['minimum_amount_tax'] && $cart_rule['minimum_amount_shipping'] ? $real_best_price : 0;
            $total_price += !$cart_rule['minimum_amount_tax'] && $cart_rule['minimum_amount_shipping'] ? $real_best_price_wt : 0;
            if ($cart_rule['free_shipping'] && $cart_rule['carrier_restriction']
                && in_array($cart_rule['id_cart_rule'], $cart_rules_in_cart)
                && $cart_rule['minimum_amount'] <= $total_price) {
                $cr = new CartRule((int)$cart_rule['id_cart_rule']);
                if (Validate::isLoadedObject($cr) &&
                    $cr->checkValidity($context, in_array((int)$cart_rule['id_cart_rule'], $cart_rules_in_cart), false, false)) {
                    $carriers = $cr->getAssociatedRestrictions('carrier', true, false);
                    if (is_array($carriers) && count($carriers) && isset($carriers['selected'])) {
                        foreach ($carriers['selected'] as $carrier) {
                            if (isset($carrier['id_carrier']) && $carrier['id_carrier']) {
                                $free_carriers_rules[] = (int)$carrier['id_carrier'];
                            }
                        }
                    }
                }
            }
        }

        // For each delivery options :
        //    - Set the carrier list
        //    - Calculate the price
        //    - Calculate the average position

        $is_wine = false;
        $is_not_wine = false;
        foreach ($delivery_option_list as $id_address => $delivery_option) {
            foreach ($delivery_option as $key => $value) {
                $total_price_with_tax = 0;
                $total_price_without_tax = 0;
                $position = 0;
                foreach ($value['carrier_list'] as $id_carrier => $data) {
                    $total_price_with_tax += $data['price_with_tax'];
                    $total_price_without_tax += $data['price_without_tax'];
                    $total_price_without_tax_with_rules = (in_array($id_carrier, $free_carriers_rules)) ? 0 : $total_price_without_tax;

                    if (!isset($carrier_collection[$id_carrier])) {
                        $carrier_collection[$id_carrier] = new Carrier($id_carrier);
                    }
                    $delivery_option_list[$id_address][$key]['carrier_list'][$id_carrier]['instance'] = $carrier_collection[$id_carrier];

                    if (file_exists(_PS_SHIP_IMG_DIR_.$id_carrier.'.jpg')) {
                        $delivery_option_list[$id_address][$key]['carrier_list'][$id_carrier]['logo'] = _THEME_SHIP_DIR_.$id_carrier.'.jpg';
                    } else {
                        $delivery_option_list[$id_address][$key]['carrier_list'][$id_carrier]['logo'] = false;
                    }

                    $position += $carrier_collection[$id_carrier]->position;

                    foreach ($this->getProducts() as $product)
                    {
                        // 7 = livraison primeur
                        // 8 = magasin primeur
                        // 9 = magasin retrai 2h

                        if ($product['wine'])
                            $is_wine = true;
                        else
                            $is_not_wine = true;

                        $remove_carrier = false;
                        if ($product['wine']) 
                        {
                            if ($data['instance']->grade != '8' && $data['instance']->grade != '7')
                            {
                                $remove_carrier = true;
                            } 
                        }
                        else
                        {
                            if ($data['instance']->grade == '8' || $data['instance']->grade == '7')
                            {
                                $remove_carrier = true;
                            }
                        }

                        // $quantity = Product::getQuantity($product['id_product'], $product['id_product_attribute']);
                        $shop_quantity = Product::getShopQuantity($product['id_product'], $product['id_product_attribute']);

                        if ($shop_quantity == '0' && ($data['instance']->grade == '9' || $data['instance']->grade == '8'))
                            $remove_carrier = true;
                        // else if ($quantity == '0' && $data['instance']->grade != '9' && $data['instance']->grade != '8')
                        //     $remove_carrier = true;

                        if ($remove_carrier)
                            unset($delivery_option_list[$id_address][$key]['carrier_list'][$id_carrier]);
                    }
                }

                // if (empty($delivery_option_list[$id_address][$key]['carrier_list']))
                //     Context::getContext()->smarty->assign('carrier_error', 'quantity_error');

                if ($is_wine && $is_not_wine)
                    Context::getContext()->smarty->assign('carrier_error', 'mixed_product');

                $delivery_option_list[$id_address][$key]['total_price_with_tax'] = $total_price_with_tax;
                $delivery_option_list[$id_address][$key]['total_price_without_tax'] = $total_price_without_tax;
                $delivery_option_list[$id_address][$key]['is_free'] = !$total_price_without_tax_with_rules ? true : false;
                $delivery_option_list[$id_address][$key]['position'] = $position / count($value['carrier_list']);
            }
        }

        // Sort delivery option list
        foreach ($delivery_option_list as &$array) {
            uasort($array, array('Cart', 'sortDeliveryOptionList'));
        }

        $cache[$this->id] = $delivery_option_list;
        return $cache[$this->id];
    }

    public function updateQty(
        $quantity,
        $id_product,
        $id_product_attribute = null,
        $id_customization = false,
        $operator = 'up',
        $id_address_delivery = 0,
        Shop $shop = null,
        $auto_add_cart_rule = true
    ) {
        if (!$shop) {
            $shop = Context::getContext()->shop;
        }

        if (Context::getContext()->customer->id) {
            if ($id_address_delivery == 0 && (int)$this->id_address_delivery) { // The $id_address_delivery is null, use the cart delivery address
                $id_address_delivery = $this->id_address_delivery;
            } elseif ($id_address_delivery == 0) { // The $id_address_delivery is null, get the default customer address
                $id_address_delivery = (int)Address::getFirstCustomerAddressId((int)Context::getContext()->customer->id);
            } elseif (!Customer::customerHasAddress(Context::getContext()->customer->id, $id_address_delivery)) { // The $id_address_delivery must be linked with customer
                $id_address_delivery = 0;
            }
        }

        $quantity = (int)$quantity;
        $id_product = (int)$id_product;
        $id_product_attribute = (int)$id_product_attribute;
        $product = new Product($id_product, false, Configuration::get('PS_LANG_DEFAULT'), $shop->id);

        if ($id_product_attribute) {
            $combination = new Combination((int)$id_product_attribute);
            if ($combination->id_product != $id_product) {
                return false;
            }
        }

        /* If we have a product combination, the minimal quantity is set with the one of this combination */
        if (!empty($id_product_attribute)) {
            $minimal_quantity = (int)Attribute::getAttributeMinimalQty($id_product_attribute);
        } else {
            $minimal_quantity = (int)$product->minimal_quantity;
        }

        if (!Validate::isLoadedObject($product)) {
            die(Tools::displayError());
        }

        if (isset(self::$_nbProducts[$this->id])) {
            unset(self::$_nbProducts[$this->id]);
        }

        if (isset(self::$_totalWeight[$this->id])) {
            unset(self::$_totalWeight[$this->id]);
        }

        $data = array(
            'cart' => $this,
            'product' => $product,
            'id_product_attribute' => $id_product_attribute,
            'id_customization' => $id_customization,
            'quantity' => $quantity,
            'operator' => $operator,
            'id_address_delivery' => $id_address_delivery,
            'shop' => $shop,
            'auto_add_cart_rule' => $auto_add_cart_rule,
        );

        /* @deprecated deprecated since 1.6.1.1 */
        // Hook::exec('actionBeforeCartUpdateQty', $data);
        Hook::exec('actionCartUpdateQuantityBefore', $data);

        if ((int)$quantity <= 0) {
            return $this->deleteProduct($id_product, $id_product_attribute, (int)$id_customization);
        } elseif (!$product->available_for_order || (Configuration::isCatalogMode() && !defined('_PS_ADMIN_DIR_'))) {
            return false;
        } else {
            /* Check if the product is already in the cart */
            $result = $this->containsProduct($id_product, $id_product_attribute, (int)$id_customization, (int)$id_address_delivery);

            /* Update quantity if product already exist */
            if ($result) {
                if ($operator == 'up') {
                    $sql = 'SELECT stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity
                            FROM '._DB_PREFIX_.'product p
                            '.Product::sqlStock('p', $id_product_attribute, true, $shop).'
                            WHERE p.id_product = '.$id_product;

                    $result2 = Db::getInstance()->getRow($sql);
                    $product_qty = (int)$result2['quantity'] + Product::getShopQuantity($id_product, $id_product_attribute);;
                    // Quantity for product pack
                    if (Pack::isPack($id_product)) {
                        $product_qty = Pack::getQuantity($id_product, $id_product_attribute);
                    }
                    $new_qty = (int)$result['quantity'] + (int)$quantity;
                    $qty = '+ '.(int)$quantity;

                    if (!Product::isAvailableWhenOutOfStock((int)$result2['out_of_stock'])) {
                        if ($new_qty > $product_qty) {
                            return false;
                        }
                    }
                } elseif ($operator == 'down') {
                    $qty = '- '.(int)$quantity;
                    $new_qty = (int)$result['quantity'] - (int)$quantity;
                    if ($new_qty < $minimal_quantity && $minimal_quantity > 1) {
                        return -1;
                    }
                } else {
                    return false;
                }

                /* Delete product from cart */
                if ($new_qty <= 0) {
                    return $this->deleteProduct((int)$id_product, (int)$id_product_attribute, (int)$id_customization);
                } elseif ($new_qty < $minimal_quantity) {
                    return -1;
                } else {
                    Db::getInstance()->execute(
                        'UPDATE `'._DB_PREFIX_.'cart_product`
                        SET `quantity` = `quantity` '.$qty.'
                        WHERE `id_product` = '.(int)$id_product.
                        ' AND `id_customization` = '.(int)$id_customization.
                        (!empty($id_product_attribute) ? ' AND `id_product_attribute` = '.(int)$id_product_attribute : '').'
                        AND `id_cart` = '.(int)$this->id.(Configuration::get('PS_ALLOW_MULTISHIPPING') && $this->isMultiAddressDelivery() ? ' AND `id_address_delivery` = '.(int)$id_address_delivery : '').'
                        LIMIT 1'
                    );
                }
            } elseif ($operator == 'up') {
                /* Add product to the cart */
                $sql = 'SELECT stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity
                        FROM '._DB_PREFIX_.'product p
                        '.Product::sqlStock('p', $id_product_attribute, true, $shop).'
                        WHERE p.id_product = '.$id_product;

                $result2 = Db::getInstance()->getRow($sql);

                $product_shop_quantity = Product::getShopQuantity($id_product, $id_product_attribute);
                $result2['quantity'] = $result2['quantity']+$product_shop_quantity;

                // Quantity for product pack
                if (Pack::isPack($id_product)) {
                    $result2['quantity'] = Pack::getQuantity($id_product, $id_product_attribute);
                }

                if (!Product::isAvailableWhenOutOfStock((int)$result2['out_of_stock'])) {
                    if ((int)$quantity > $result2['quantity']) {
                        return false;
                    }
                }

                if ((int)$quantity < $minimal_quantity) {
                    return -1;
                }

                $result_add = Db::getInstance()->insert('cart_product', array(
                    'id_product' =>            (int)$id_product,
                    'id_product_attribute' =>    (int)$id_product_attribute,
                    'id_cart' =>                (int)$this->id,
                    'id_address_delivery' =>    (int)$id_address_delivery,
                    'id_shop' =>                $shop->id,
                    'quantity' =>                (int)$quantity,
                    'date_add' =>                date('Y-m-d H:i:s'),
                    'id_customization' =>       (int)$id_customization,
                ));

                if (!$result_add) {
                    return false;
                }
            }
        }

        // refresh cache of self::_products
        $this->_products = $this->getProducts(true);
        $this->update();
        $context = Context::getContext()->cloneContext();
        $context->cart = $this;
        Cache::clean('getContextualValue_*');
        if ($auto_add_cart_rule) {
            CartRule::autoAddToCart($context);
        }

        if ($product->customizable) {
            return $this->_updateCustomizationQuantity((int)$quantity, (int)$id_customization, (int)$id_product, (int)$id_product_attribute, (int)$id_address_delivery, $operator);
        } else {
            return true;
        }
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    /*
    * module: orderfees
    * date: 2018-11-19 10:30:54
    * version: 1.8.9
    */
    public $current_type = null;
    
    /*
    * module: orderfees
    * date: 2018-11-19 10:30:54
    * version: 1.8.9
    */
    public function getPackageShippingCost(
        $id_carrier = null,
        $use_tax = true,
        Country $default_country = null,
        $product_list = null,
        $id_zone = null
    ) {
        $total = 0;
        $return = false;
        
        Hook::exec('actionCartGetPackageShippingCost', array(
            'object' => &$this,
            'id_carrier' => &$id_carrier,
            'use_tax' => &$use_tax,
            'default_country' => &$default_country,
            'product_list' => &$product_list,
            'id_zone' => &$id_zone,
            'total' => &$total,
            'return' => &$return
        ));
        
        if ($return) {
            return (float) Tools::ps_round((float) $total, 2);
        }
        
        return parent::getPackageShippingCost(
            $id_carrier,
            $use_tax,
            $default_country,
            $product_list,
            $id_zone
        ) + (float) Tools::ps_round((float) $total, 2);
    }
    /*
    * module: orderfees
    * date: 2018-11-19 10:30:54
    * version: 1.8.9
    */
    public function getCartRulesSort(&$a, &$b)
    {
        return strcmp($b['is_fee'], $a['is_fee']);
    }
    /*
    * module: orderfees
    * date: 2018-11-19 10:30:54
    * version: 1.8.9
    */
    public function getCartRules($filter = CartRule::FILTER_ACTION_ALL)
    {
        $result = parent::getCartRules($filter);

        if (count($result) > 0)
        {
            $products = $this->getProducts();
            foreach ($result as $index => &$discount)
            {
                $add_price = 0;
                $add_price_tax_exc = 0;
                if (preg_match('@primeur@i', $discount['name']))
                {
                    // foreach ($products as $product)
                    // {
                    //     if ($product['wine'])
                    //     {
                    //         $add_price += (($product['total']*1.5)/100);
                    //         $add_price_tax_exc += ($product['total']*1.5)/100;
                    //     }
                    // }

                    // $discount['obj']->reduction_amount += $add_price;
                    // $discount['obj']->unit_value_real -= $add_price;
                    // $discount['obj']->unit_value_tax_exc -= $add_price_tax_exc;
                    $discount['value_real'] = $discount['obj']->unit_value_real;
                    $discount['value_tax_exc'] = $discount['obj']->unit_value_tax_exc;
                    $discount['reduction_amount'] = $discount['obj']->reduction_amount;
                }
            }
        }

        usort($result, array($this, 'getCartRulesSort'));
        return $result;
    }
    /*
    * module: orderfees
    * date: 2018-11-19 10:30:54
    * version: 1.8.9
    */
    public function getOrderTotal(
        $with_taxes = true,
        $type = Cart::BOTH,
        $products = null,
        $id_carrier = null,
        $use_cache = true
    ) {
        if (in_array($type, array(self::BOTH, self::ONLY_DISCOUNTS))) {
            $this->fees_total_discounts = array();
            $this->current_type = $type;
        }
        
        $total = parent::getOrderTotal($with_taxes, $type, $products, $id_carrier, $use_cache);
        
        if ($type == self::ONLY_DISCOUNTS) {
            $this->current_type = null;
            
            if (!empty($this->fees_total_discounts)) {
                return array_sum($this->fees_total_discounts);
            }
        }
        
        return $total;
    }
    /*
    * module: orderfees
    * date: 2018-11-19 10:30:54
    * version: 1.8.9
    */
    public function addCartRule($id_cart_rule)
    {
        $results = Hook::exec('actionCartRuleAdd', array(
            'object' => &$this,
            'id_cart_rule' => &$id_cart_rule
        ), null, true);
        
        if (is_array($results)) {
            foreach ($results as $result) {
                if ($result !== null) {
                    return $result;
                }
            }
        }
        
        return parent::addCartRule($id_cart_rule);
    }
    /*
    * module: orderfees
    * date: 2018-11-19 10:30:54
    * version: 1.8.9
    */
    public function removeCartRule($id_cart_rule)
    {
        $results = Hook::exec('actionCartRuleRemove', array(
            'object' => &$this,
            'id_cart_rule' => &$id_cart_rule
        ), null, true);
        
        if (is_array($results)) {
            foreach ($results as $result) {
                if ($result !== null) {
                    return $result;
                }
            }
        }
        
        return parent::removeCartRule($id_cart_rule);
    }
    
    /*
    * module: orderfees
    * date: 2018-11-19 10:30:54
    * version: 1.8.9
    */
    public function getOrderedCartRulesIds($filter = CartRule::FILTER_ACTION_ALL)
    {
        $cache_key = 'Cart::getOrderedCartRulesIds_' . $this->id . '-' . $filter . '-ids';
        if (!Cache::isStored($cache_key)) {
            $result = Db::getInstance()->executeS(
                'SELECT cr.`id_cart_rule`
				FROM `' . _DB_PREFIX_ . 'cart_cart_rule` cd
				LEFT JOIN `' . _DB_PREFIX_ . 'cart_rule` cr ON cd.`id_cart_rule` = cr.`id_cart_rule`
				LEFT JOIN `' . _DB_PREFIX_ . 'cart_rule_lang` crl ON (
					cd.`id_cart_rule` = crl.`id_cart_rule`
					AND crl.id_lang = ' . (int) $this->id_lang . '
				)
				WHERE `id_cart` = ' . (int) $this->id . '
                    AND is_fee = 0
				' . ($filter == CartRule::FILTER_ACTION_SHIPPING ? 'AND free_shipping = 1' : '') . '
				' . ($filter == CartRule::FILTER_ACTION_GIFT ? 'AND gift_product != 0' : '') . '
				' . ($filter == CartRule::FILTER_ACTION_REDUCTION ? 'AND (reduction_percent != 0 OR reduction_amount != 0)' : '')
                . ' ORDER BY cr.priority ASC'
            );
            Cache::store($cache_key, $result);
        } else {
            $result = Cache::retrieve($cache_key);
        }
        return $result;
    }
}
