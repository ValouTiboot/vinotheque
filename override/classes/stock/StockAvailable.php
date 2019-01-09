<?php

class StockAvailable extends StockAvailableCore
{
    /** @var int the quantity available for sale */
    public $shop_quantity = 0;

    public  function __construct($id_product = null, $id_lang = null, $id_shop = null)
    {
        $definition['fields']['shop_quantity'] = array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true);
        parent::__construct($id_product, $id_lang, $id_shop);
    }

	/**
     * For a given id_product and id_product_attribute, gets its stock available
     *
     * @param int $id_product
     * @param int $id_product_attribute Optional
     * @param int $id_shop Optional : gets context by default
     * @return int Quantity
     */
    public static function getShopQuantityAvailableByProduct($id_product = null, $id_product_attribute = null, $id_shop = null)
    {
        // if null, it's a product without attributes
        if ($id_product_attribute === null) {
            $id_product_attribute = 0;
        }

        $key = 'StockAvailable::getShopQuantityAvailableByProduct_'.(int)$id_product.'-'.(int)$id_product_attribute.'-'.(int)$id_shop;
        if (!Cache::isStored($key)) {
            $query = new DbQuery();
            $query->select('SUM(shop_quantity)');
            $query->from('stock_available');

            // if null, it's a product without attributes
            if ($id_product !== null) {
                $query->where('id_product = '.(int)$id_product);
            }

            $query->where('id_product_attribute = '.(int)$id_product_attribute);
            $query = StockAvailable::addSqlShopRestriction($query, $id_shop);
            $result = (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
            Cache::store($key, $result);
            return $result;
        }

        return Cache::retrieve($key);
    }

    /**
     * For a given id_product and id_product_attribute updates the quantity available
     * If $avoid_parent_pack_update is true, then packs containing the given product won't be updated
     *
     * @param int $id_product
     * @param int $id_product_attribute Optional
     * @param int $delta_quantity The delta quantity to update
     * @param int $id_shop Optional
     */
    public static function updateShopQuantity($id_product, $id_product_attribute, $delta_quantity, $id_shop = null)
    {
        if (!Validate::isUnsignedId($id_product)) {
            return false;
        }
        $product = new Product((int)$id_product);
        if (!Validate::isLoadedObject($product)) {
            return false;
        }

        $stockManager = \PrestaShop\PrestaShop\Adapter\ServiceLocator::get('\\PrestaShop\\PrestaShop\\Core\\Stock\\StockManager');
        $stockManager->updateShopQuantity($product, $id_product_attribute, $delta_quantity, $id_shop = null);
        return true;
    }

    /**
     * For a given id_product and id_product_attribute sets the quantity available
     *
     * @param int $id_product
     * @param int $id_product_attribute Optional
     * @param int $delta_quantity The delta quantity to update
     * @param int $id_shop Optional
     */
    public static function setShopQuantity($id_product, $id_product_attribute, $shop_quantity, $id_shop = null)
    {
        if (!Validate::isUnsignedId($id_product)) {
            return false;
        }

        $context = Context::getContext();

        // if there is no $id_shop, gets the context one
        if ($id_shop === null && Shop::getContext() != Shop::CONTEXT_GROUP) {
            $id_shop = (int)$context->shop->id;
        }

        $depends_on_stock = StockAvailable::dependsOnStock($id_product);

        //Try to set available quantity if product does not depend on physical stock
        if (!$depends_on_stock) {
            $id_stock_available = (int)StockAvailable::getStockAvailableIdByProductId($id_product, $id_product_attribute, $id_shop);
            if ($id_stock_available) {
                $stock_available = new StockAvailable($id_stock_available);
                $stock_available->shop_quantity = (int)$shop_quantity;
                $stock_available->update();
            } else {
                $out_of_stock = StockAvailable::outOfStock($id_product, $id_shop);
                $stock_available = new StockAvailable();
                $stock_available->out_of_stock = (int)$out_of_stock;
                $stock_available->id_product = (int)$id_product;
                $stock_available->id_product_attribute = (int)$id_product_attribute;
                $stock_available->shop_quantity = (int)$shop_quantity;

                if ($id_shop === null) {
                    $shop_group = Shop::getContextShopGroup();
                } else {
                    $shop_group = new ShopGroup((int)Shop::getGroupFromShop((int)$id_shop));
                }

                // if quantities are shared between shops of the group
                if ($shop_group->share_stock) {
                    $stock_available->id_shop = 0;
                    $stock_available->id_shop_group = (int)$shop_group->id;
                } else {
                    $stock_available->id_shop = (int)$id_shop;
                    $stock_available->id_shop_group = 0;
                }
                $stock_available->add();
            }

            // Hook::exec('actionUpdateQuantity',
            //     array(
            //         'id_product' => $id_product,
            //         'id_product_attribute' => $id_product_attribute,
            //         'shop_quantity' => $stock_available->shop_quantity
            //     )
            // );
        }

        Cache::clean('StockAvailable::getQuantityAvailableByProduct_'.(int)$id_product.'*');
    }
}