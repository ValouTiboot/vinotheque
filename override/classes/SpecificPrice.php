<?php

class SpecificPrice extends SpecificPriceCore
{
    public $id_specific_price_dubos;

    public function __construct()
    {
        SpecificPrice::$definition['fields']['id_specific_price_dubos'] = array('type' => self::TYPE_STRING, 'validate' => 'isString');

        parent::__construct();
    }

     /**
     * Remove or add useless fields value depending on the values in the database (cache friendly)
     *
     * @param int|null $id_product
     * @param int|null $id_product_attribute
     * @param int|null $id_cart
     * @param string|null $beginning
     * @param string|null $ending
     * @return string
     */
    protected static function computeExtraConditions($id_product, $id_product_attribute, $id_customer, $id_cart, $beginning = null, $ending = null)
    {
        $first_date = date('Y-m-d 00:00:00');
        $last_date = date('Y-m-d 23:59:59');
        $now = date('Y-m-d H:i:00');
        if ($beginning === null) {
            $beginning = $now;
        }
        if ($ending === null) {
            $ending = $now;
        }
        $id_customer = (int)$id_customer;

        $query_extra = '';

        if ($id_product !== null) {
            $query_extra .= self::filterOutField('id_product', $id_product);
        }

        if ($id_customer !== null) {
            $query_extra .= self::filterOutField('id_customer', $id_customer);
        }
        if ($id_product_attribute !== null) {
            $query_extra .= self::filterOutField('id_product_attribute', $id_product_attribute, 100000);
        }

        if ($id_cart !== null) {
            $query_extra .= self::filterOutField('id_cart', $id_cart);
        }

        if ($ending == $now && $beginning == $now) {
            $key = __FUNCTION__.'-'.$first_date.'-'.$last_date;
            if (!array_key_exists($key, SpecificPrice::$_filterOutCache)) {
                $query_from_count    = 'SELECT 1 FROM `'._DB_PREFIX_.'specific_price` WHERE `from` BETWEEN \''.$first_date.'\' AND \''.$last_date.'\'';
                $from_specific_count = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query_from_count);

                $query_to_count                       = 'SELECT 1 FROM `'._DB_PREFIX_.'specific_price` WHERE `to` BETWEEN \''.$first_date.'\' AND \''.$last_date.'\'';

                $to_specific_count                    = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query_to_count);
                SpecificPrice::$_filterOutCache[$key] = array($from_specific_count, $to_specific_count);
            } else {
                list($from_specific_count, $to_specific_count) = SpecificPrice::$_filterOutCache[$key];
            }
        } else {
            $from_specific_count = $to_specific_count = 1;
        }

        // if the from and to is not reached during the current day, just change $ending & $beginning to any date of the day to improve the cache
        if (!$from_specific_count && !$to_specific_count) {
            $ending = $beginning = $first_date;
        }
        $db = Db::getInstance();
        $beginning = $db->escape($beginning);
        $ending = $db->escape($ending);

        $query_extra .= ' AND (`from` = \'0000-00-00 00:00:00\' OR \''.$beginning.'\' >= `from`)'
                       .' AND (`to` = \'0000-00-00 00:00:00\' OR \''.$ending.'\' <= `to`)';

        return $query_extra;
    }

    public static function getSpecificPrice($id_product, $id_shop, $id_currency, $id_country, $id_group, $quantity, $id_product_attribute = null, $id_customer = 0, $id_cart = 0, $real_quantity = 0)
    {
        if (!SpecificPrice::isFeatureActive()) {
            return array();
        }
        /*
        ** The date is not taken into account for the cache, but this is for the better because it keeps the consistency for the whole script.
        ** The price must not change between the top and the bottom of the page
        */

        static $psQtyDiscountOnCombination = null;
        if ($psQtyDiscountOnCombination === null) {
            $psQtyDiscountOnCombination = Configuration::get('PS_QTY_DISCOUNT_ON_COMBINATION');
        }

        $key = ((int)$id_product.'-'.(int)$id_shop.'-'.(int)$id_currency.'-'.(int)$id_country.'-'.(int)$id_group.'-'.(int)$quantity.'-'.(int)$id_product_attribute.'-'.(int)$id_cart.'-'.(int)$id_customer.'-'.(int)$real_quantity);
        if (!array_key_exists($key, SpecificPrice::$_specificPriceCache)) {
            $query_extra = self::computeExtraConditions($id_product, $id_product_attribute, $id_customer, $id_cart);
            $query = '
			SELECT *, '.SpecificPrice::_getScoreQuery($id_product, $id_shop, $id_currency, $id_country, $id_group, $id_customer).'
				FROM `'._DB_PREFIX_.'specific_price`
				WHERE
                `id_shop` '.self::formatIntInQuery(0, $id_shop).' AND
                `id_currency` '.self::formatIntInQuery(0, $id_currency).' AND
                `id_country` '.self::formatIntInQuery(0, $id_country).' AND
                `id_group` '.self::formatIntInQuery(0, $id_group).' '.$query_extra.'
				AND IF(`from_quantity` > 1, `from_quantity`, 0) <= ';

            $query .= ($psQtyDiscountOnCombination || !$id_cart || !$real_quantity) ? (int)$quantity : max(1, (int)$real_quantity);
            $query .= ' ORDER BY `id_product_attribute` DESC, `id_cart` DESC, `from_quantity` DESC, `id_specific_price_rule` ASC, `score` DESC, `to` DESC, `from` DESC';

            SpecificPrice::$_specificPriceCache[$key] = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);
        }
        return SpecificPrice::$_specificPriceCache[$key];
    }
}
