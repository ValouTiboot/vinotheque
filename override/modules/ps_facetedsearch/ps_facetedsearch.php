<?php



class Ps_FacetedsearchOverride extends Ps_Facetedsearch
{
    private $nbr_products;
    private $ps_layered_full_tree;

	private static function getId_featureFilterSubQuery($filter_value, $ignore_join = false)
    {
        if (empty($filter_value)) {
            return array();
        }
        $query_filters = ' AND EXISTS (SELECT * FROM '._DB_PREFIX_.'feature_product fp WHERE (fp.id_product = p.id_product AND ';
        foreach ($filter_value as $filter_val) {
            $query_filters .= 'fp.`id_feature_value` = '.(int) $filter_val.') OR (fp.id_product = p.id_product AND ';
        }
        $query_filters = rtrim($query_filters, 'OR (fp.id_product = p.id_product AND ').') ';

        return array('where' => $query_filters);
    }

	public function getProductByFilters(
        $products_per_page,
        $page,
        $order_by,
        $order_way,
        $id_lang,
        $selected_filters = array()
    ) {
        
        $products_per_page = (int)$products_per_page;

        if (!Validate::isOrderBy($order_by)) {
            $order_by = 'cp.position';
        }

        if (!Validate::isOrderWay($order_way)) {
            $order_way = 'ASC';
        }

        $order_clause = $order_by.' '.$order_way;

        $home_category = Configuration::get('PS_HOME_CATEGORY');
        /* If the current category isn't defined or if it's homepage, we have nothing to display */
        $id_parent = (int) Tools::getValue('id_category', Tools::getValue('id_category_layered', $home_category));

        $alias_where = 'p';
        if (version_compare(_PS_VERSION_, '1.5', '>')) {
            $alias_where = 'product_shop';
        }

        $query_filters_where = ' AND '.$alias_where.'.`active` = 1 AND '.$alias_where.'.`visibility` IN ("both", "catalog")';
        $query_filters_from = '';

        $parent = new Category((int) $id_parent);

        foreach ($selected_filters as $key => $filter_values) {
            if (!count($filter_values)) {
                continue;
            }

            preg_match('/^(.*[^_0-9])/', $key, $res);
            $key = $res[1];

            switch ($key) {
                case 'id_feature':
                    $sub_queries = array();
                    foreach ($filter_values as $filter_value) {
                        $filter_value_array = explode('_', $filter_value);
                        if (!isset($sub_queries[$filter_value_array[0]])) {
                            $sub_queries[$filter_value_array[0]] = array();
                        }
                        $sub_queries[$filter_value_array[0]][] = 'fp.`id_feature_value` = '.(int) $filter_value_array[1];
                    }
                    foreach ($sub_queries as $sub_query) {
                        $query_filters_where .= ' AND p.id_product IN (SELECT `id_product` FROM `'._DB_PREFIX_.'feature_product` fp WHERE ';
                        $query_filters_where .= implode(' OR ', $sub_query).') ';
                    }
                break;

                case 'id_attribute_group':
                    $sub_queries = array();

                    foreach ($filter_values as $filter_value) {
                        $filter_value_array = explode('_', $filter_value);
                        if (!isset($sub_queries[$filter_value_array[0]])) {
                            $sub_queries[$filter_value_array[0]] = array();
                        }
                        $sub_queries[$filter_value_array[0]][] = 'pac.`id_attribute` = '.(int) $filter_value_array[1];
                    }
                    foreach ($sub_queries as $sub_query) {
                        $query_filters_where .= ' AND p.id_product IN (SELECT pa.`id_product`
                        FROM `'._DB_PREFIX_.'product_attribute_combination` pac
                        LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa
                        ON (pa.`id_product_attribute` = pac.`id_product_attribute`)'.
                        Shop::addSqlAssociation('product_attribute', 'pa').'
                        WHERE '.implode(' OR ', $sub_query).') ';
                    }
                break;

                case 'category':
                    $query_filters_where .= ' AND p.id_product IN (SELECT id_product FROM '._DB_PREFIX_.'category_product cp WHERE ';
                    foreach ($selected_filters['category'] as $id_category) {
                        $query_filters_where .= 'cp.`id_category` = '.(int) $id_category.' OR ';
                    }
                    $query_filters_where = rtrim($query_filters_where, 'OR ').')';
                break;

                case 'quantity':
                    if (count($selected_filters['quantity']) == 2) {
                        break;
                    }

                    $query_filters_where .= ' AND sa.quantity '.(!$selected_filters['quantity'][0] ? '<=' : '>').' 0 ';
                    $query_filters_from .= 'LEFT JOIN `'._DB_PREFIX_.'stock_available` sa ON (sa.id_product = p.id_product '.StockAvailable::addSqlShopRestriction(null, null,  'sa').') ';
                break;

                case 'manufacturer':
                    $selected_filters['manufacturer'] = array_map('intval', $selected_filters['manufacturer']);
                    $query_filters_where .= ' AND p.id_manufacturer IN ('.implode($selected_filters['manufacturer'], ',').')';
                break;

                case 'condition':
                    if (count($selected_filters['condition']) == 3) {
                        break;
                    }
                    $query_filters_where .= ' AND '.$alias_where.'.condition IN (';
                    foreach ($selected_filters['condition'] as $cond) {
                        $query_filters_where .= '\''.pSQL($cond).'\',';
                    }
                    $query_filters_where = rtrim($query_filters_where, ',').')';
                break;

                case 'weight':
                    if ($selected_filters['weight'][0] != 0 || $selected_filters['weight'][1] != 0) {
                        $query_filters_where .= ' AND p.`weight` BETWEEN '.(float) ($selected_filters['weight'][0] - 0.001).' AND '.(float) ($selected_filters['weight'][1] + 0.001);
                    }
                break;

                case 'price':
                    if (isset($selected_filters['price'])) {
                        if ($selected_filters['price'][0] !== '' || $selected_filters['price'][1] !== '') {
                            $price_filter = array();
                            $price_filter['min'] = (float) ($selected_filters['price'][0]);
                            $price_filter['max'] = (float) ($selected_filters['price'][1]);
                        }
                    } else {
                        $price_filter = false;
                    }
                break;
            }
        }

        if (Tools::getIsset('search') && Tools::getValue('search') != '') {
            $query_filters_where .= " AND pl.`name` LIKE '%" .pSQL(Tools::getValue('search'))  . "%'";
        }

        $context = Context::getContext();
        $id_currency = (int) $context->currency->id;

        $price_filter_query_in = ''; // All products with price range between price filters limits
        $price_filter_query_out = ''; // All products with a price filters limit on it price range
        if (isset($price_filter) && $price_filter) {
            $price_filter_query_in = 'INNER JOIN `'._DB_PREFIX_.'layered_price_index` psi
            ON
            (
                psi.price_min <= '.(int) $price_filter['max'].'
                AND psi.price_max >= '.(int) $price_filter['min'].'
                AND psi.`id_product` = p.`id_product`
                AND psi.`id_shop` = '.(int) $context->shop->id.'
                AND psi.`id_currency` = '.$id_currency.'
            )';

            $price_filter_query_out = 'INNER JOIN `'._DB_PREFIX_.'layered_price_index` psi
            ON
                ((psi.price_min < '.(int) $price_filter['min'].' AND psi.price_max > '.(int) $price_filter['min'].')
                OR
                (psi.price_max > '.(int) $price_filter['max'].' AND psi.price_min < '.(int) $price_filter['max'].'))
                AND psi.`id_product` = p.`id_product`
                AND psi.`id_shop` = '.(int) $context->shop->id.'
                AND psi.`id_currency` = '.$id_currency;
        }

        $query_filters_from .= Shop::addSqlAssociation('product', 'p');

        Db::getInstance()->execute('DROP TEMPORARY TABLE IF EXISTS '._DB_PREFIX_.'cat_filter_restriction', false);
        if (empty($selected_filters['category'])) {
            /* Create the table which contains all the id_product in a cat or a tree */
            Db::getInstance()->execute('CREATE TEMPORARY TABLE '._DB_PREFIX_.'cat_filter_restriction ENGINE=MEMORY
                                                        SELECT cp.id_product, MIN(cp.position) position FROM '._DB_PREFIX_.'category c
                                                        STRAIGHT_JOIN '._DB_PREFIX_.'category_product cp ON (c.id_category = cp.id_category AND
                                                        '.($this->ps_layered_full_tree ? 'c.nleft >= '.(int) $parent->nleft.'
                                                        AND c.nright <= '.(int) $parent->nright : 'c.id_category = '.(int) $id_parent).'
                                                        AND c.active = 1)
                                                        STRAIGHT_JOIN `'._DB_PREFIX_.'product` p ON (p.id_product=cp.id_product)
                                                        '.$price_filter_query_in.'
                                                        '.$query_filters_from.'
                                                        WHERE 1 '.$query_filters_where.'
                                                        GROUP BY cp.id_product ORDER BY position, id_product', false);
        } else {
            $categories = array_map('intval', $selected_filters['category']);

            Db::getInstance()->execute('CREATE TEMPORARY TABLE '._DB_PREFIX_.'cat_filter_restriction ENGINE=MEMORY
                                                        SELECT cp.id_product, MIN(cp.position) position FROM '._DB_PREFIX_.'category_product cp
                                                        STRAIGHT_JOIN `'._DB_PREFIX_.'product` p ON (p.id_product=cp.id_product)
                                                        '.$price_filter_query_in.'
                                                        '.$query_filters_from.'
                                                        WHERE cp.`id_category` IN ('.implode(',', $categories).') '.$query_filters_where.'
                                                        GROUP BY cp.id_product ORDER BY position, id_product', false);
        }
        Db::getInstance()->execute('ALTER TABLE '._DB_PREFIX_.'cat_filter_restriction ADD PRIMARY KEY (id_product), ADD KEY (position, id_product) USING BTREE', false);

        if (isset($price_filter) && $price_filter) {
            static $ps_layered_filter_price_usetax = null;
            static $ps_layered_filter_price_rounding = null;

            if ($ps_layered_filter_price_usetax === null) {
                $ps_layered_filter_price_usetax = Configuration::get('PS_LAYERED_FILTER_PRICE_USETAX');
            }

            if ($ps_layered_filter_price_rounding === null) {
                $ps_layered_filter_price_rounding = Configuration::get('PS_LAYERED_FILTER_PRICE_ROUNDING');
            }

            if (empty($selected_filters['category'])) {
                $all_products_out = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
                    SELECT p.`id_product` id_product
                    FROM `'._DB_PREFIX_.'product` p JOIN '._DB_PREFIX_.'category_product cp USING (id_product)
                    INNER JOIN '._DB_PREFIX_.'category c ON (c.id_category = cp.id_category AND
                        '.($this->ps_layered_full_tree ? 'c.nleft >= '.(int) $parent->nleft.'
                        AND c.nright <= '.(int) $parent->nright : 'c.id_category = '.(int) $id_parent).'
                        AND c.active = 1)
                    '.$price_filter_query_out.'
                    '.$query_filters_from.'
                    WHERE 1 '.$query_filters_where.' GROUP BY cp.id_product');
            } else {
                $all_products_out = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
                    SELECT p.`id_product` id_product
                    FROM `'._DB_PREFIX_.'product` p JOIN '._DB_PREFIX_.'category_product cp USING (id_product)
                    '.$price_filter_query_out.'
                    '.$query_filters_from.'
                    WHERE cp.`id_category` IN ('.implode(',', $categories).') '.$query_filters_where.' GROUP BY cp.id_product');
            }

            /* for this case, price could be out of range, so we need to compute the real price */
            foreach ($all_products_out as $product) {
                $price = Product::getPriceStatic($product['id_product'], $ps_layered_filter_price_usetax);
                if ($ps_layered_filter_price_rounding) {
                    $price = (int) $price;
                }
                if ($price < $price_filter['min'] || $price > $price_filter['max']) {
                    // out of range price, exclude the product
                    $product_id_delete_list[] = (int) $product['id_product'];
                }
            }
            if (!empty($product_id_delete_list)) {
                Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'cat_filter_restriction WHERE id_product IN ('.implode(',', $product_id_delete_list).')', false);
            }
        }
        $this->nbr_products = Db::getInstance()->getValue('SELECT COUNT(*) FROM '._DB_PREFIX_.'cat_filter_restriction', false);

        if ($this->nbr_products == 0) {
            $products = array();
        } else {
            $nb_day_new_product = (Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20);

            if (version_compare(_PS_VERSION_, '1.6.1', '>=') === true) {
                $products = Db::getInstance()->executeS('
                    SELECT
                        p.*,
                        '.($alias_where == 'p' ? '' : 'product_shop.*,').'
                        '.$alias_where.'.id_category_default,
                        pl.*,
                        image_shop.`id_image` id_image,
                        il.legend,
                        m.name manufacturer_name,
                        '.(Combination::isFeatureActive() ? 'product_attribute_shop.id_product_attribute id_product_attribute,' : '').'
                        DATEDIFF('.$alias_where.'.`date_add`, DATE_SUB("'.date('Y-m-d').' 00:00:00", INTERVAL '.(int) $nb_day_new_product.' DAY)) > 0 AS new,
                        stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity'.(Combination::isFeatureActive() ? ', product_attribute_shop.minimal_quantity AS product_attribute_minimal_quantity' : '').'
                    FROM '._DB_PREFIX_.'cat_filter_restriction cp
                    LEFT JOIN `'._DB_PREFIX_.'product` p ON p.`id_product` = cp.`id_product`
                    '.Shop::addSqlAssociation('product', 'p').
                    (Combination::isFeatureActive() ?
                    ' LEFT JOIN `'._DB_PREFIX_.'product_attribute_shop` product_attribute_shop
                        ON (p.`id_product` = product_attribute_shop.`id_product` AND product_attribute_shop.`default_on` = 1 AND product_attribute_shop.id_shop='.(int) $context->shop->id.')' : '').'
                    LEFT JOIN '._DB_PREFIX_.'product_lang pl ON (pl.id_product = p.id_product'.Shop::addSqlRestrictionOnLang('pl').' AND pl.id_lang = '.(int) $id_lang.')
                    LEFT JOIN `'._DB_PREFIX_.'image_shop` image_shop
                        ON (image_shop.`id_product` = p.`id_product` AND image_shop.cover=1 AND image_shop.id_shop='.(int) $context->shop->id.')
                    LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (image_shop.`id_image` = il.`id_image` AND il.`id_lang` = '.(int) $id_lang.')
                    LEFT JOIN '._DB_PREFIX_.'manufacturer m ON (m.id_manufacturer = p.id_manufacturer)
                    '.Product::sqlStock('p', 0).'
                    WHERE '.$alias_where.'.`active` = 1 AND '.$alias_where.'.`visibility` IN ("both", "catalog")
                    ORDER BY '.$order_clause.' , cp.id_product'.
                    ' LIMIT '.(((int) $page - 1) * $products_per_page.','.$products_per_page), true, false);
            } else {
                $products = Db::getInstance()->executeS('
                    SELECT
                        p.*,
                        '.($alias_where == 'p' ? '' : 'product_shop.*,').'
                        '.$alias_where.'.id_category_default,
                        pl.*,
                        MAX(image_shop.`id_image`) id_image,
                        il.legend,
                        m.name manufacturer_name,
                        '.(Combination::isFeatureActive() ? 'MAX(product_attribute_shop.id_product_attribute) id_product_attribute,' : '').'
                        DATEDIFF('.$alias_where.'.`date_add`, DATE_SUB("'.date('Y-m-d').' 00:00:00", INTERVAL '.(int) $nb_day_new_product.' DAY)) > 0 AS new,
                        stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity'.(Combination::isFeatureActive() ? ', MAX(product_attribute_shop.minimal_quantity) AS product_attribute_minimal_quantity' : '').'
                    FROM '._DB_PREFIX_.'cat_filter_restriction cp
                    LEFT JOIN `'._DB_PREFIX_.'product` p ON p.`id_product` = cp.`id_product`
                    '.Shop::addSqlAssociation('product', 'p').
                    (Combination::isFeatureActive() ?
                    'LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (p.`id_product` = pa.`id_product`)
                    '.Shop::addSqlAssociation('product_attribute', 'pa', false, 'product_attribute_shop.`default_on` = 1 AND product_attribute_shop.id_shop='.(int) $context->shop->id) : '').'
                    LEFT JOIN '._DB_PREFIX_.'product_lang pl ON (pl.id_product = p.id_product'.Shop::addSqlRestrictionOnLang('pl').' AND pl.id_lang = '.(int) $id_lang.')
                    LEFT JOIN `'._DB_PREFIX_.'image` i  ON (i.`id_product` = p.`id_product`)'.
                    Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover=1').'
                    LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (image_shop.`id_image` = il.`id_image` AND il.`id_lang` = '.(int) $id_lang.')
                    LEFT JOIN '._DB_PREFIX_.'manufacturer m ON (m.id_manufacturer = p.id_manufacturer)
                    '.Product::sqlStock('p', 0).'
                    WHERE '.$alias_where.'.`active` = 1 AND '.$alias_where.'.`visibility` IN ("both", "catalog")
                    GROUP BY product_shop.id_product
                    ORDER BY '.$order_clause.' , cp.id_product'.
                    ' LIMIT '.(((int) $page - 1) * $products_per_page.','.$products_per_page), true, false);
            }
        }

        if ($order_by == 'p.price') {
            Tools::orderbyPrice($products, $order_way);
        }

        return array(
            'products' => $products,
            'count' => $this->nbr_products,
        );
    }

    public function hookDisplayHome($params)
    {
    	$filters = $this->getFilterBlock();
    	$this->context->smarty->assign('filters', $filters['filters']);
    	return $this->display(__FILE__, 'views/templates/hook/home.tpl');
    }

    public function hookDisplayTopFaceted($params)
    {
        return $this->hookDisplayHome($params);
    }
}