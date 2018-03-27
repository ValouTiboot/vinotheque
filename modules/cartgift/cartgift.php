<?php


class Cartgift extends Module
{
	public function __construct()
	{
		$this->name = 'cartgift';
		$this->version = '1.0.0';
		$this->author = 'yateo';
		$this->need_instance = 0;

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->trans('Cart Gift', array(), 'Modules.Cartgift.Admin');
        $this->description = $this->trans('Displays the gift option in cart process instead of checkout.', array(), 'Modules.Cartgift.Admin');

        $this->ps_versions_compliancy = array('min' => '1.7.1.0', 'max' => _PS_VERSION_);
    }

    public function install()
    {
    	return parent::install() && $this->registerHook('displayShoppingCartDetailFooter') && $this->registerHook('header') && Configuration::updateValue('CARTGIFT_ALLOWED', 0);
    }

    public function uninstall()
    {
    	return parent::uninstall() && $this->unregisterHook('displayShoppingCartDetailFooter') && $this->unregisterHook('header');
    }

    public function getContent()
    {
        return $this->postProcess().$this->renderForm();
    }

    public function postProcess()
    {
    	if (Tools::isSubmit('submitCartgiftConf')) 
    	{
    		Configuration::updateValue('CARTGIFT_ALLOWED', Tools::getValue('CARTGIFT_ALLOWED'));

            return $this->displayConfirmation($this->trans('The settings have been updated.', array(), 'Admin.Notifications.Success'));
    	}
    }

    public function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->trans('Settings', array(), 'Admin.Global'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->trans('Activate', array(), 'Modules.Cartgift.Admin'),
                        'name' => 'CARTGIFT_ALLOWED',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->getTranslator()->trans('Enabled', array(), 'Admin.Global')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->getTranslator()->trans('Disabled', array(), 'Admin.Global')
                            )
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->trans('Save', array(), 'Admin.Actions')
                )
            ),
        );

        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->default_form_language = $lang->id;
        $helper->module = $this;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitCartgiftConf';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            // 'uri' => $this->getPathUri(),
            'fields_value' => array('CARTGIFT_ALLOWED' => Configuration::get('CARTGIFT_ALLOWED')),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    public function hookHeader()
    {
        if (Configuration::get('CARTGIFT_ALLOWED')) {
            $this->context->controller->registerJavascript('modules-cartgift', 'modules/'.$this->name.'/cartgift.js', ['position' => 'bottom', 'priority' => 150]);
        }
    }

    public function hookDisplayShoppingCartDetailFooter($params)
    {
    	$cart_gift = array(
    		'allowed' => Configuration::get('CARTGIFT_ALLOWED'),
    		'isGift' => $params['cart']->gift,
    		'message' => $params['cart']->gift_message,
            'url' => $this->context->link->getModuleLink($this->name, 'ajax', array('ajax' => 1, 'action' => 'giftMessage')),
    	);

    	$this->context->smarty->assign('cartgift', $cart_gift);
    	return $this->display(__FILE__, './views/templates/hook/shopping-footer.tpl');
    }

    public function hookDisplayCartRuleCartVoucher(&$params)
    {
        return $this->hookDisplayShoppingCartFooter($params);
    }
}
