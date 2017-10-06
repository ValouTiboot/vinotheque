<?php

class Combination extends CombinationCore
{
	public $shop_quantity = 0;

	public $packaging_price;

	public $id_product_attribute_dubos;

	public $id_packaging;

	public function __construct($id = null, $id_lang = null, $id_shop = null)
	{
		self::$definition['fields']['shop_quantity'] = array('type' => self::TYPE_INT, 'validate' => 'isInt', 'size' => 11);
		self::$definition['fields']['packaging_price'] = array('type' => self::TYPE_FLOAT, 'shop' => true, 'validate' => 'isPrice', 'size' => 20);
		self::$definition['fields']['id_product_attribute_dubos'] = array('type' => self::TYPE_INT, 'validate' => 'isInt', 'size' => 11);
		self::$definition['fields']['id_packaging'] = array('type' => self::TYPE_INT, 'validate' => 'isInt', 'size' => 11);

		parent::__construct($id, $id_lang, $id_shop);
	}
}