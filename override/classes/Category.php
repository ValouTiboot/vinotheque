<?php


class Category extends CategoryCore
{
	public $highlight_type;

	public $id_image_highlight = null;

    public $id_category_dubos;

	public function __construct($idCategory = null, $idLang = null, $idShop = null)
    {
    	Category::$definition['fields']['highlight_type'] = array('type' => self::TYPE_STRING, 'validate' => 'isString', 'values' => array('best_seller', 'promo', 'wine', 'none'), 'default' => 'none');

    	Category::$definition['fields']['id_category_dubos'] = array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false);
        
        parent::__construct($idCategory, $idLang, $idShop);

        if (file_exists($this->image_dir.'highlight/'.(int) $this->id.'.jpg'))
        	$this->id_image_highlight = $this->image_dir.'highlight/'.(int) $this->id.'.jpg';
    }
}
