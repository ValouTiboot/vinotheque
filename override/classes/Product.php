<?php
class Product extends ProductCore
{
	public $wine = 0;
    public $wine_date = '0000-00-00';
    
    public $wine_delivery = '0000-00-00';
    public $property;
    public $property_picture;
    public $calling;
    public $calling_picture_big;
    public $calling_picture_small;
    public $id_second_wine;
    
    public $shop_quantity = 0;
    public $grape;
    public $reward;
    public $notation;
    public function __construct($id_product = null, $full = false, $id_lang = null, $id_shop = null, Context $context = null)
	{
		Product::$definition['fields']['wine'] = array('type' => self::TYPE_BOOL, 'shop' => true, 'validate' => 'isBool');
		Product::$definition['fields']['wine_date'] = array('type' => self::TYPE_DATE, 'shop' => true, 'validate' => 'isDateFormat');
		Product::$definition['fields']['wine_delivery'] = array('type' => self::TYPE_DATE, 'shop' => true, 'validate' => 'isDateFormat');
		Product::$definition['fields']['property'] = array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml');
        Product::$definition['fields']['property_picture'] = array('type' => self::TYPE_STRING, 'validate' => 'isString');
        Product::$definition['fields']['calling'] = array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml');
        Product::$definition['fields']['calling_picture_big'] = array('type' => self::TYPE_STRING, 'validate' => 'isString');
		Product::$definition['fields']['calling_picture_small'] = array('type' => self::TYPE_STRING, 'validate' => 'isString');
        Product::$definition['fields']['id_second_wine'] = array('type' => self::TYPE_STRING, 'shop' => true, 'validate' => 'isReference');
        Product::$definition['fields']['shop_quantity'] = array('type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isInt');
        Product::$definition['fields']['grape'] = array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255);
        Product::$definition['fields']['reward'] = array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255);
		Product::$definition['fields']['notation'] = array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255);
	   
		parent::__construct($id_product, $full, $id_lang, $id_shop, $context);
        foreach (array('property_picture','calling_picture_big','calling_picture_small') as $pics)
        {        
            if (file_exists(_PS_ROOT_DIR_.'/ftp/Images/'.(int) $this->{$pics}))
                $this->{$pics} = '/ftp/Images/'.(int) $this->{$pics};
            else
                $this->{$pics} = false;
        }
	}
    
    public static function getIdByRef($ref)
    {
        return Db::getInstance()->getValue("SELECT `id_product` FROM `" . _DB_PREFIX_ . "product` WHERE `reference`='" . pSQL($ref) . "'");
    }
	public function updateAttribute($id_product_attribute, $wholesale_price, $price, $weight, $unit, $ecotax,
        $id_images, $reference, $ean13, $default, $location = null, $upc = null, $minimal_quantity = null, $available_date = null, $update_all_fields = true, array $id_shop_list = array(), $isbn = '', $shop_quantity = null, $packaging_price)
    {
        $combination = new Combination($id_product_attribute);
        if (!$update_all_fields) {
            $combination->setFieldsToUpdate(array(
                'price' => !is_null($price),
                'wholesale_price' => !is_null($wholesale_price),
                'packaging_price' => !is_null($packaging_price),
                'ecotax' => !is_null($ecotax),
                'weight' => !is_null($weight),
                'unit_price_impact' => !is_null($unit),
                'default_on' => !is_null($default),
                'minimal_quantity' => !is_null($minimal_quantity),
                'available_date' => !is_null($available_date),
            ));
        }
        $price = str_replace(',', '.', $price);
        $weight = str_replace(',', '.', $weight);
        $packaging_price = str_replace(',', '.', $packaging_price);
        $combination->price = (float)$price;
        $combination->wholesale_price = (float)$wholesale_price;
        $combination->packaging_price = (float)$packaging_price;
        $combination->ecotax = (float)$ecotax;
        $combination->weight = (float)$weight;
        $combination->unit_price_impact = (float)$unit;
        $combination->reference = pSQL($reference);
        $combination->location = pSQL($location);
        $combination->ean13 = pSQL($ean13);
        $combination->isbn = pSQL($isbn);
        $combination->upc = pSQL($upc);
        $combination->default_on = (int)$default;
        $combination->minimal_quantity = (int)$minimal_quantity;
        $combination->shop_quantity = (int)$shop_quantity;
        $combination->available_date = $available_date ? pSQL($available_date) : '0000-00-00';
        if (count($id_shop_list)) {
            $combination->id_shop_list = $id_shop_list;
        }
        
        $combination->save();
        if (is_array($id_images) && count($id_images)) {
            $combination->setImages($id_images);
        }
        $id_default_attribute = (int)Product::updateDefaultAttribute($this->id);
        if ($id_default_attribute) {
            $this->cache_default_attribute = $id_default_attribute;
        }
        if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') && StockAvailable::dependsOnStock($this->id, Context::getContext()->shop->id)) {
            Db::getInstance()->update('stock', array(
                'reference' => pSQL($reference),
                'ean13'     => pSQL($ean13),
                'isbn'     => pSQL($isbn),
                'upc'        => pSQL($upc),
            ), 'id_product = '.$this->id.' AND id_product_attribute = '.(int)$id_product_attribute);
        }
        Hook::exec('actionProductAttributeUpdate', array('id_product_attribute' => (int)$id_product_attribute));
        Tools::clearColorListCache($this->id);
        StockAvailable::updateShopQuantity((int)$this->id, $combination->id, $combination->shop_quantity);
        return true;
    }
    public function addCombinationEntity($wholesale_price, $price, $weight, $unit_impact, $ecotax, $quantity,
        $id_images, $reference, $id_supplier, $ean13, $default, $location = null, $upc = null, $minimal_quantity = 1, array $id_shop_list = array(), $available_date = null, $isbn = '', $shop_quantity = null, $packaging_price)
    {
        $id_product_attribute = $this->addAttribute(
            $price, $weight, $unit_impact, $ecotax, $id_images,
            $reference, $ean13, $default, $location, $upc, $minimal_quantity, $id_shop_list, $available_date, 0, $isbn, $shop_quantity, $packaging_price);
        $this->addSupplierReference($id_supplier, $id_product_attribute);
        $result = ObjectModel::updateMultishopTable('Combination', array(
            'wholesale_price' => (float)$wholesale_price,
        ), 'a.id_product_attribute = '.(int)$id_product_attribute);
        if (!$id_product_attribute || !$result) {
            return false;
        }
        return $id_product_attribute;
    }
    public function addAttribute($price, $weight, $unit_impact, $ecotax, $id_images, $reference, $ean13, $default, $location = null, $upc = null, $minimal_quantity = 1, array $id_shop_list = array(), $available_date = null, $quantity = 0, $isbn = '', $shop_quantity = null, $packaging_price)
    {
        if (!$this->id) {
            return;
        }
        $price = str_replace(',', '.', $price);
        $weight = str_replace(',', '.', $weight);
        $packaging_price = str_replace(',', '.', $packaging_price);
        $combination = new Combination();
        $combination->id_product = (int)$this->id;
        $combination->price = (float)$price;
        $combination->packaging_price = (float)$packaging_price;
        $combination->ecotax = (float)$ecotax;
        $combination->quantity = (int)$quantity;
        $combination->shop_quantity = (int)$shop_quantity;
        $combination->weight = (float)$weight;
        $combination->unit_price_impact = (float)$unit_impact;
        $combination->reference = pSQL($reference);
        $combination->location = pSQL($location);
        $combination->ean13 = pSQL($ean13);
        $combination->isbn = pSQL($isbn);
        $combination->upc = pSQL($upc);
        $combination->default_on = (int)$default;
        $combination->minimal_quantity = (int)$minimal_quantity;
        $combination->available_date = $available_date;
        if (count($id_shop_list)) {
            $combination->id_shop_list = array_unique($id_shop_list);
        }
        
        $combination->add();
        
        if (!$combination->id) {
            return false;
        }
        
        $total_quantity = (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
            SELECT SUM(quantity) as quantity
            FROM '._DB_PREFIX_.'stock_available
            WHERE id_product = '.(int)$this->id.'
            AND id_product_attribute <> 0 '
        );
        
        if (!$total_quantity) {
            Db::getInstance()->update('stock_available', array('quantity' => 0), '`id_product` = '.$this->id);
        }
        
        $id_default_attribute = Product::updateDefaultAttribute($this->id);
        if ($id_default_attribute) {
            $this->cache_default_attribute = $id_default_attribute;
            if (!$combination->available_date) {
                $this->setAvailableDate();
            }
        }
        if (!empty($id_images)) {
            $combination->setImages($id_images);
        }
        Tools::clearColorListCache($this->id);
        
        if (Configuration::get('PS_DEFAULT_WAREHOUSE_NEW_PRODUCT') != 0 && Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
            $warehouse_location_entity = new WarehouseProductLocation();
            $warehouse_location_entity->id_product = $this->id;
            $warehouse_location_entity->id_product_attribute = (int)$combination->id;
            $warehouse_location_entity->id_warehouse = Configuration::get('PS_DEFAULT_WAREHOUSE_NEW_PRODUCT');
            $warehouse_location_entity->location = pSQL('');
            $warehouse_location_entity->save();
        }
        
        StockAvailable::setShopQuantity((int)$this->id, $combination->id, $combination->shop_quantity);
        return (int)$combination->id;
    }
    public static function getShopQuantity($id_product, $id_product_attribute, $cache_is_pack)
    {
        if ((int)$cache_is_pack || ($cache_is_pack === null && Pack::isPack((int)$id_product))) {
            if (!Pack::isInStock((int)$id_product)) {
                return 0;
            }
        }
        return (StockAvailable::getShopQuantityAvailableByProduct($id_product, $id_product_attribute));
    }
    public static function getProductProperties($id_lang, $row, Context $context = null)
    {
        Hook::exec('actionGetProductPropertiesBefore', [
            'id_lang'   => $id_lang,
            'product'   => &$row,
            'context'   => $context
        ]);
        if (!$row['id_product']) {
            return false;
        }
        if ($context == null) {
            $context = Context::getContext();
        }
        $id_product_attribute = $row['id_product_attribute'] = (!empty($row['id_product_attribute']) ? (int)$row['id_product_attribute'] : null);
        $row['allow_oosp'] = Product::isAvailableWhenOutOfStock($row['out_of_stock']);
        if (Combination::isFeatureActive() && $id_product_attribute === null
            && ((isset($row['cache_default_attribute']) && ($ipa_default = $row['cache_default_attribute']) !== null)
                || ($ipa_default = Product::getDefaultAttribute($row['id_product'], !$row['allow_oosp'])))) {
            $id_product_attribute = $row['id_product_attribute'] = $ipa_default;
        }
        if (!Combination::isFeatureActive() || !isset($row['id_product_attribute'])) {
            $id_product_attribute = $row['id_product_attribute'] = 0;
        }
        $usetax = !Tax::excludeTaxeOption();
        $cache_key = $row['id_product'].'-'.$id_product_attribute.'-'.$id_lang.'-'.(int)$usetax;
        if (isset($row['id_product_pack'])) {
            $cache_key .= '-pack'.$row['id_product_pack'];
        }
        if (isset(self::$producPropertiesCache[$cache_key])) {
            return array_merge($row, self::$producPropertiesCache[$cache_key]);
        }
        $row['category'] = Category::getLinkRewrite((int)$row['id_category_default'], (int)$id_lang);
        $row['category_name'] = Db::getInstance()->getValue('SELECT name FROM '._DB_PREFIX_.'category_lang WHERE id_shop = '.(int)$context->shop->id.' AND id_lang = '.(int)$id_lang.' AND id_category = '.(int)$row['id_category_default']);
        $row['link'] = $context->link->getProductLink((int)$row['id_product'], $row['link_rewrite'], $row['category'], $row['ean13']);
        $row['attribute_price'] = 0;
        if ($id_product_attribute) {
            $row['attribute_price'] = (float)Combination::getPrice($id_product_attribute);
        }
        if (isset($row['quantity_wanted'])) {
            $quantity = max((int)$row['minimal_quantity'], (int)$row['quantity_wanted']);
        } else {
            $quantity = (int)$row['minimal_quantity'];
        }
        $row['price_tax_exc'] = Product::getPriceStatic(
            (int)$row['id_product'],
            false,
            $id_product_attribute,
            (self::$_taxCalculationMethod == PS_TAX_EXC ? 2 : 6),
            null,
            false,
            true,
            $quantity
        );
        if (self::$_taxCalculationMethod == PS_TAX_EXC) {
            $row['price_tax_exc'] = Tools::ps_round($row['price_tax_exc'], 2);
            $row['price'] = Product::getPriceStatic(
                (int)$row['id_product'],
                true,
                $id_product_attribute,
                6,
                null,
                false,
                true,
                $quantity
            );
            $row['price_without_reduction'] = Product::getPriceStatic(
                (int)$row['id_product'],
                false,
                $id_product_attribute,
                2,
                null,
                false,
                false,
                $quantity
            );
        } else {
            $row['price'] = Tools::ps_round(
                Product::getPriceStatic(
                    (int)$row['id_product'],
                    true,
                    $id_product_attribute,
                    6,
                    null,
                    false,
                    true,
                    $quantity
                ),
                (int) Configuration::get('PS_PRICE_DISPLAY_PRECISION')
            );
            $row['price_without_reduction'] = Product::getPriceStatic(
                (int)$row['id_product'],
                true,
                $id_product_attribute,
                6,
                null,
                false,
                false,
                $quantity
            );
        }
        $row['reduction'] = Product::getPriceStatic(
            (int)$row['id_product'],
            (bool)$usetax,
            $id_product_attribute,
            6,
            null,
            true,
            true,
            $quantity,
            true,
            null,
            null,
            null,
            $specific_prices
        );
        $row['specific_prices'] = $specific_prices;
        $row['quantity'] = Product::getQuantity(
            (int)$row['id_product'],
            0,
            isset($row['cache_is_pack']) ? $row['cache_is_pack'] : null
        );
        $row['shop_quantity'] = Product::getShopQuantity(
            (int)$row['id_product'],
            0,
            isset($row['cache_is_pack']) ? $row['cache_is_pack'] : null
        );
        
        $row['quantity_all_versions'] = $row['quantity'];
        if ($row['id_product_attribute']) {
            $row['quantity'] = Product::getQuantity(
                (int)$row['id_product'],
                $id_product_attribute,
                isset($row['cache_is_pack']) ? $row['cache_is_pack'] : null
            );
            $row['shop_quantity'] = Product::getShopQuantity(
                (int)$row['id_product'],
                $id_product_attribute,
                isset($row['cache_is_pack']) ? $row['cache_is_pack'] : null
            );
            $row['available_date'] = Product::getAvailableDate(
                (int)$row['id_product'],
                $id_product_attribute
            );
        }
        $row['id_image'] = Product::defineProductImage($row, $id_lang);
        $row['features'] = Product::getFrontFeaturesStatic((int)$id_lang, $row['id_product']);
        $row['attachments'] = array();
        if (!isset($row['cache_has_attachments']) || $row['cache_has_attachments']) {
            $row['attachments'] = Product::getAttachmentsStatic((int)$id_lang, $row['id_product']);
        }
        $row['virtual'] = ((!isset($row['is_virtual']) || $row['is_virtual']) ? 1 : 0);
        $row['pack'] = (!isset($row['cache_is_pack']) ? Pack::isPack($row['id_product']) : (int)$row['cache_is_pack']);
        $row['packItems'] = $row['pack'] ? Pack::getItemTable($row['id_product'], $id_lang) : array();
        $row['nopackprice'] = $row['pack'] ? Pack::noPackPrice($row['id_product']) : 0;
        if ($row['pack'] && !Pack::isInStock($row['id_product'])) {
            $row['quantity'] = 0;
        }
        $row['customization_required'] = false;
        if (isset($row['customizable']) && $row['customizable'] && Customization::isFeatureActive()) {
            if (count(Product::getRequiredCustomizableFieldsStatic((int)$row['id_product']))) {
                $row['customization_required'] = true;
            }
        }
        $attributes = Product::getAttributesParams($row['id_product'], $row['id_product_attribute']);
        foreach ($attributes as $attribute) {
            $row['attributes'][$attribute['id_attribute_group']] = $attribute;
        }
        $row = Product::getTaxesInformations($row, $context);
        $row['ecotax_rate'] = (float)Tax::getProductEcotaxRate($context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')});
        Hook::exec('actionGetProductPropertiesAfter', [
            'id_lang'   => $id_lang,
            'product'   => &$row,
            'context'   => $context
        ]);
        $combination = new Combination($id_product_attribute);
        if (0 != $combination->unit_price_impact && 0 != $row['unit_price_ratio']) {
            $unitPrice = ($row['price_tax_exc'] / $row['unit_price_ratio']) + $combination->unit_price_impact;
            $row['unit_price_ratio'] = $row['price_tax_exc'] / $unitPrice;
        }
        $row['unit_price'] = ($row['unit_price_ratio'] != 0  ? $row['price'] / $row['unit_price_ratio'] : 0);
        $categories = self::getProductCategoriesFull($row['id_product']);
        $last_cat = array_pop($categories);
        $row['last_cat'] = $last_cat;
        $row['is_private_sale_product'] = \Product::isPrivateSaleProduct($row['id_product']);
        self::$producPropertiesCache[$cache_key] = $row;
        return self::$producPropertiesCache[$cache_key];
    }
    
    public static function cacheProductsFeatures($product_ids)
    {
        if (!Feature::isFeatureActive()) {
            return;
        }
        $product_implode = array();
        foreach ($product_ids as $id_product) {
            if ((int)$id_product && !array_key_exists($id_product, self::$_cacheFeatures)) {
                $product_implode[] = (int)$id_product;
            }
        }
        if (!count($product_implode)) {
            return;
        }
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
        SELECT fp.id_feature, fp.id_product, fp.id_feature_value, fl.`name`, fvl.`value`
        FROM `'._DB_PREFIX_.'feature_product` fp
        LEFT JOIN `' . _DB_PREFIX_ . 'feature_lang` fl ON (fp.`id_feature`=fl.`id_feature` AND fl.`id_lang`=\'' . Context::getContext()->language->id . '\')
        LEFT JOIN `' . _DB_PREFIX_ . 'feature_value_lang` fvl ON (fp.`id_feature_value`=fvl.`id_feature_value` AND fvl.`id_lang`=\'' . Context::getContext()->language->id . '\')
        WHERE fp.`id_product` IN ('.implode($product_implode, ',').')');
        foreach ($result as $row) {
            if (!array_key_exists($row['id_product'], self::$_cacheFeatures)) {
                self::$_cacheFeatures[$row['id_product']] = array();
            }
            self::$_cacheFeatures[$row['id_product']][] = $row;
        }
    }
    
    public static function getSimpleProducts($id_lang, Context $context = null)
    {
        if (!$context) {
            $context = Context::getContext();
        }
        $front = true;
        if (!in_array($context->controller->controller_type, array('front', 'modulefront'))) {
            $front = false;
        }
        $sql = 'SELECT p.`id_product`, pl.`name`, p.`reference`
                FROM `'._DB_PREFIX_.'product` p
                '.Shop::addSqlAssociation('product', 'p').'
                LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` '.Shop::addSqlRestrictionOnLang('pl').')
                WHERE pl.`id_lang` = '.(int)$id_lang.'
                '.($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '').'
                ORDER BY pl.`name`';
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    }
    public static function isPrivateSaleProduct($id_product)
    {
        $categories = Product::getProductCategoriesFull($id_product);
        if (count($categories))
        foreach ($categories as $category)
        {
            if (preg_match('@privée@i', $category['name']))
                return true;
        }
        return false;
    }
    /*
    * module: orderfees
    * date: 2018-11-19 10:31:03
    * version: 1.8.9
    */
    public static function priceCalculation(
        $id_shop,
        $id_product,
        $id_product_attribute,
        $id_country,
        $id_state,
        $zipcode,
        $id_currency,
        $id_group,
        $quantity,
        $use_tax,
        $decimals,
        $only_reduc,
        $use_reduc,
        $with_ecotax,
        &$specific_price,
        $use_group_reduction,
        $id_customer = 0,
        $use_customer_price = true,
        $id_cart = 0,
        $real_quantity = 0,
        $id_customization = 0
    ) {
        $total = 0;
        $return = false;
        
        Hook::exec('actionProductPriceCalculation', array(
            'id_shop' => &$id_shop,
            'id_product' => &$id_product,
            'id_product_attribute' => &$id_product_attribute,
            'id_country' => &$id_country,
            'id_state' => &$id_state,
            'zipcode' => &$zipcode,
            'id_currency' => &$id_currency,
            'id_group' => &$id_group,
            'quantity' => &$quantity,
            'use_tax' => &$use_tax,
            'decimals' => &$decimals,
            'zipcode' => &$zipcode,
            'total' => &$total,
            'return' => &$return
        ));
        
        if ($return) {
            return (float) Tools::ps_round((float) $total, 2);
        }
        
        return parent::priceCalculation(
            $id_shop,
            $id_product,
            $id_product_attribute,
            $id_country,
            $id_state,
            $zipcode,
            $id_currency,
            $id_group,
            $quantity,
            $use_tax,
            $decimals,
            $only_reduc,
            $use_reduc,
            $with_ecotax,
            $specific_price,
            $use_group_reduction,
            $id_customer,
            $use_customer_price,
            $id_cart,
            $real_quantity,
            $id_customization
        ) + (float) Tools::ps_round((float) $total, 2);
    }
}