<?php

if (!defined('_PS_VERSION_'))
	exit;

class Foodandwine extends Module
{
	public function __construct()
	{
		$this->name = 'foodandwine';
		$this->tab = 'front_office_features';
		$this->version = '1.0.0';
		$this->author = 'Yateo - Valentin THIBAULT';
		$this->need_instance = 0;
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
		$this->module_table = 'foodandwine';

		parent::__construct();

		$this->displayName = $this->l('Food and wine pairing Product');
		$this->description = $this->l('Add food and wine pairing fields for product.');

		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
	}

	public function install()
   {
    	if (Shop::isFeatureActive())
    		Shop::setContext(Shop::CONTEXT_ALL);

      if (!parent::install() || !$this->registerHook('displayAdminProductsExtra') || !$this->registerHook('actionProductUpdate') || !$this->registerHook('actionProductAdd') || !$this->installDB())
			return false;
		return true;
   }

	public function uninstall()
   {
      if (!parent::uninstall())
			return false;
		return true;
   }

   public function installDB()
   {
    	if (!Db::getInstance()->execute("CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . $this->module_table . "` (
			  `id_foodandwine` int(11) NOT NULL AUTO_INCREMENT,
			  `slug` varchar(255) NOT NULL,
			  PRIMARY KEY (`id_foodandwine`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;")
    	|| !Db::getInstance()->execute("INSERT INTO `" . _DB_PREFIX_ . $this->module_table . "` (`id_foodandwine`,`slug`) VALUES
        (1,'Viandes blanches (porc, volaille, lapin, veau, mouton, agneau)'),
        (2,'Viande blanche grillée'),
        (3,'Viande blanche en sauce'),
        (4,'Viandes rouges (buf, canard)'),
        (5,'Viande rouge grillée'),
        (6,'Viande rouge en sauce'),
        (7,'Viande rouge crue'),
        (8,'Viande fumée'),
        (9,'Jambon'),
        (10,'Charcuterie'),
        (11,'ufs'),
        (12,'Gibier'),
        (13,'Poisson grillé'),
        (14,'Poisson en sauce'),
        (15,'Poisson fumé'),
        (16,'Poisson mariné'),
        (17,'Poisson cru'),
        (18,'Fruits de mer'),
        (19,'Pâtes et risottos'),
        (20,'Pizzas'),
        (21,'Cakes, quiches et feuilletés'),
        (22,'Gratins'),
        (23,'Fromage à pâte molle'),
        (24,'Fromage à pâte dure'),
        (25,'Fromage bleu'),
        (26,'Fromage italien'),
        (27,'Fromage de chèvre'),
        (28,'Fruits cuits'),
        (29,'Fruits rouges'),
        (30,'Fruits frais'),
        (31,'Café et chocolat'),
        (32,'Tartes, cakes et gâteaux'),
        (33,'Glaces et sorbets');")
    	|| !Db::getInstance()->execute("CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . $this->module_table . "_product` (
			  `id_foodandwine` int(11) NOT NULL,
			  `id_product` int(11) NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;")
    	)
    		return false;
    	
      return true;
   }

    public function save($type = 'add')
    {
    	if (Tools::getIsset('foodandwine'))
    	{
    		if ($type == 'update')
    			Db::getInstance()->delete($this->module_table . '_product', "`id_product`='" . pSQL(Tools::getValue('id_product')) . "'");
    		
    		foreach (Tools::getValue('foodandwine') as $id_foodandwine)
				Db::getInstance()->insert($this->module_table . '_product', array(
					'id_product' => (int) Tools::getValue('id_product'),
					'id_foodandwine' => (int) $id_foodandwine
					)
				);
    	}
    }

   public function hookActionProductAdd($params)
   {
   	return $this->save();
   }

   public function hookActionProductUpdate($params)
   {
   	return $this->save('update');
   }

   public function hookDisplayAdminProductsExtra($params)
   {
   	$fields_form = array(
         'form' => array(
            'input' => array(
               array(
                  'type' => 'checkbox',
                  'name' => 'foodandwine[]',
                  'label' => $this->l('FoodAndWine'),
                  'values' => array(
                  	'query' => $this->getFiedlsLang(),
                  	'id' => 'id_foodandwine',
                  	'name' => 'slug',
                  ),
               ),
            ),
         ),
      );

      $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));

      $helper = new HelperForm();
      $helper->base_folder = realpath($this->context->smarty->getTemplateDir(0) . '../../default/template') . '/';
      $helper->base_tpl = 'helpers/form/form.tpl';
      $helper->show_toolbar = false;
      $helper->table = $this->module_table;
      $helper->module = $this;
      $helper->identifier = $this->identifier;
      $helper->tpl_vars = array('fields_value' => $this->getConfigFieldsValues($params['id_product']));

      $html = $helper->generateForm(array($fields_form));
      return str_replace(array('<form ', '</form>', 'panel'), array('<div ', '</div>', 'paneldef'), $html);
   }

  public function hookDisplayProductFoodPictos($params)
  {
      if (!isset($params['product']))
          return false;

      $pictos = Db::getInstance()->executeS("SELECT `slug` FROM `" . _DB_PREFIX_ . "foodandwine` WHERE `id_foodandwine` IN (SELECT `id_foodandwine` FROM `" . _DB_PREFIX_ . "foodandwine_product` WHERE `id_product`='" . pSQL($params['product']['id_product']) . "');");
      
      $pictos_link = [];
      if (count($pictos) > 0)
      foreach ($pictos as $picto)
          $pictos_link[] = array('name' => $picto['slug'], 'url' => $this->toNurl($picto['slug']));
          // $pictos_link[] = array('name' => $picto['slug'], 'url' => $this->context->link->getMediaLink(__PS_BASE_URI__.'modules/'.$this->name.'/img/'. $this->toNurl($picto['slug']) . '.png'));

      $this->smarty->assign('pictos_link', $pictos_link);
      return $this->display(__FILE__, 'picto.tpl');
  }

   public function getFiedlsLang()
   {
   	$fields = Db::getInstance()->executeS("SELECT * FROM `" . _DB_PREFIX_ . $this->module_table . "`");

   	foreach ($fields as &$field)
   	{
   		$field['slug'] = $this->l($field['slug']);
   		$field['val'] = $field['id_foodandwine'];
   	}

   	return $fields;
   }

   public function getConfigFieldsValues($id_product)
   {
   	$fields = array();
      $checkeds = Db::getInstance()->executeS("SELECT `id_foodandwine` FROM `" . _DB_PREFIX_ . $this->module_table . "_product` WHERE `id_product`='" . pSQL($id_product) . "'");

   	foreach ($checkeds as $checked)
   		$fields['foodandwine[]_' . $checked['id_foodandwine']] = true;

   	return $fields;
   }

   public function toNurl($str)
    {
        $str = htmlentities(trim($str), ENT_NOQUOTES, 'utf-8');

        $str = preg_replace('#\&([A-za-z])(?:acute|cedil|circ|grave|ring|tilde|uml)\;#', '\1', $str);
        $str = preg_replace('#\&([A-za-z]{2})(?:lig)\;#', '\1', $str); // pour les ligatures e.g. '&oelig;'
        $str = preg_replace('#\&[^;]+\;#', '', $str); // supprime les autres caractères (&[X]acute;)
        $str = preg_replace('([^a-zA-Z0-9-_])', '-', $str); // remplace tous ce qui n'est pas alphanumérique (et - et _)
        while (strlen($str) != strlen(($str = str_replace('--', '-', $str))));

        return strtolower($str);
    }

}