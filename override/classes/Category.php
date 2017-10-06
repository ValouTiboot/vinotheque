<?php


class Category extends CategoryCore
{
	public $highlight_type;

	public $id_image_highlight = null;

	public function __construct($idCategory = null, $idLang = null, $idShop = null)
    {
    	Category::$definition['fields']['highlight_type'] = array('type' => self::TYPE_STRING, 'validate' => 'isString', 'values' => array('best_seller', 'promo', 'none'), 'default' => 'best_seller');
        
        parent::__construct($idCategory, $idLang, $idShop);

        if (file_exists($this->image_dir.'highlight/'.(int) $this->id.'.jpg'))
        	$this->id_image_highlight = $this->image_dir.'highlight/'.(int) $this->id.'.jpg';
    }
}
