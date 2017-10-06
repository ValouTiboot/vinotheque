<?php
/**
* NOTICE OF LICENSE
*
* This source file is subject to a commercial license from 202 ecommerce
* Use, copy, modification or distribution of this source file without written
* license agreement from 202 ecommerce is strictly forbidden.
*
* @author    202 ecommerce <contact@202-ecommerce.com>
* @copyright Copyright (c) 202 ecommerce 2014
* @license   Commercial license
*
* Support <support@202-ecommerce.com>
*/

if (!defined('_PS_VERSION_')) {
    die(header('HTTP/1.0 404 Not Found'));
}

class LoyaltyAdvanced extends ObjectModel
{

    public $id_product;
    public $loyalty;
    public $date_begin;
    public $date_finish;

    public static $definition = array(
        'table'     => 'totloyaltyadvanced',
        'primary'   => 'id_totloyaltyadvanced',
        'multilang' => false,
        'fields' => array(
            'id_product' => array(
                'type' => parent::TYPE_INT
            ),
            'loyalty' => array(
                'type' => parent::TYPE_STRING,
                'required' => true
            ),
            'date_begin' => array(
                'type' => parent::TYPE_DATE,
            ),
            'date_finish' => array(
                'type' => parent::TYPE_DATE,
            )
        )
    );

    public static function getLoyalties($id_product = null, $period = false)
    {
        $sql = '
            SELECT `'.self::$definition['primary'].'`
            FROM `'._DB_PREFIX_.self::$definition['table'].'` AS a
            '.Shop::addSqlAssociation(self::$definition['primary'], 'a').'
            WHERE 1 ';

        if (is_null($id_product) === false) {
            $sql .= " AND a.id_product = '".(int)$id_product."' ";
        }

        if (is_bool($period) === true) {
            $sql .= " AND (a.date_finish >= '".strftime('%Y-%m-%d')."' OR a.date_finish = '0000-00-00') ";
        }

        $objs_ids = Db::getInstance()->ExecuteS($sql);

        $objs = array();

        if ($objs_ids && count($objs_ids)) {

            foreach ($objs_ids as $obj_id) {
                $objs[] = new LoyaltyAdvanced($obj_id[self::$definition['primary']]);
            }
        }

        return $objs;
    }

    /**
     * Get Loyalty by id_product
     * @param  int             $id_product ID Product
     * @return LoyaltyAdvanced             Instanciation of this class
     */
    public static function getLoyaltyByIDProduct($id_product, $period = false)
    {
        $object = self::getLoyalties($id_product, $period);

        return ($object && count($object) ? current($object) : new LoyaltyAdvanced());
    }
}
