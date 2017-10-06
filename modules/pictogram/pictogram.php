<?php

if (!defined('_PS_VERSION_'))
	exit;

class Pictogram extends Module
{
	public function __construct()
	{
		$this->name = 'pictogram';
		$this->tab = 'front_office_features';
		$this->version = '1.0.0';
		$this->author = 'Yateo - Valentin THIBAULT';
		$this->need_instance = 0;
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
		$this->module_table = 'pictogram';

		parent::__construct();

		$this->displayName = $this->l('Basic Pictogram Product');
		$this->description = $this->l('Add basic pictogram product.');

		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
	}

	public function install()
    {
    	if (Shop::isFeatureActive())
    		Shop::setContext(Shop::CONTEXT_ALL);

        if (!parent::install() || !$this->registerHook('displayAdminProductsExtra') || !$this->registerHook('displayProductPictos') || !$this->registerHook('actionProductUpdate') || !$this->registerHook('actionProductAdd') || !$this->installDB())
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
			  `id_pictogram` int(11) NOT NULL AUTO_INCREMENT,
			  `slug` varchar(255) NOT NULL,
			  PRIMARY KEY (`id_pictogram`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;")
    	|| !Db::getInstance()->execute("INSERT INTO `" . _DB_PREFIX_ . $this->module_table . "` (`slug`) VALUES
    		('Récompenses concours'), 
    		('Notation'),
    		('Bio'),
    		('Coup de coeur Vinothèque'),
    		('Coup de coeur client');")
    	|| !Db::getInstance()->execute("CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . $this->module_table . "_product` (
			  `id_pictogram` int(11) NOT NULL,
			  `id_product` int(11) NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;")
    	)
    		return false;
    	
    	return true;
    }

    public function save($type = 'add')
    {
    	if (Tools::getIsset('pictogram'))
    	{
    		if ($type == 'update')
    			Db::getInstance()->delete($this->module_table . '_product', "`id_product`='" . pSQL(Tools::getValue('id_product')) . "'");
    		
    		foreach (Tools::getValue('pictogram') as $id_pictogram)
				Db::getInstance()->insert($this->module_table . '_product', array(
					'id_product' => (int) Tools::getValue('id_product'),
					'id_pictogram' => (int) $id_pictogram
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
                        'name' => 'pictogram[]',
                        'label' => $this->l('Pictogram'),
                        'values' => array(
                        	'query' => $this->getFiedlsLang(),
                        	'id' => 'id_pictogram',
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

    public function hookDisplayProductPictos($params)
    {
        if (!isset($params['product']))
            return false;

        $pictos = Db::getInstance()->executeS("SELECT `slug` FROM `" . _DB_PREFIX_ . "pictogram` WHERE `id_pictogram` IN (SELECT `id_pictogram` FROM `" . _DB_PREFIX_ . "pictogram_product` WHERE `id_product`='" . pSQL($params['product']['id_product']) . "');");
        
        $pictos_link = [];
        if (count($pictos) > 0)
        foreach ($pictos as $picto)
            $pictos_link[] = $this->context->link->getMediaLink(__PS_BASE_URI__.'modules/'.$this->name.'/img/'. $this->toNurl($picto['slug']) . '.png');

        $this->smarty->assign('pictos_link', $pictos_link);
        return $this->display(__FILE__, 'picto.tpl');
    }    

    public function getFiedlsLang()
    {
       	$fields = Db::getInstance()->executeS("SELECT * FROM `" . _DB_PREFIX_ . $this->module_table . "`");

    	foreach ($fields as &$field)
    	{
    		$field['slug'] = $this->l($field['slug']);
    		$field['val'] = $field['id_pictogram'];
    	}

    	return $fields;
    }

    public function getConfigFieldsValues($id_product)
    {
    	$fields = array();
		$checkeds = Db::getInstance()->executeS("SELECT `id_pictogram` FROM `" . _DB_PREFIX_ . $this->module_table . "_product` WHERE `id_product`='" . pSQL($id_product) . "'");

    	foreach ($checkeds as $checked)
    		$fields['pictogram[]_' . $checked['id_pictogram']] = true;

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