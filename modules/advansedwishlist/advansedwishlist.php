<?php
/**
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2015 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once(dirname(__FILE__).'/Ws_WishList.php');

class Advansedwishlist extends Module
{
    protected $config_form = false;
    private $_html = '';

    public function __construct()
    {
        $this->name = 'advansedwishlist';
        $this->tab = 'front_office_features';
        $this->version = '1.0.2';
        $this->author = 'Snegurka';
        $this->need_instance = 0;
        $this->module_key = 'fcdeb86309ac51aa0e914a05c472d2b9';

        $this->bootstrap = true;

        parent::__construct();
        
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $base_dir_ssl = _PS_BASE_URL_SSL_.__PS_BASE_URI__;
        } else {
            $base_dir_ssl = _PS_BASE_URL_.__PS_BASE_URI__;
        }
                
        ## prestashop 1.7 ##
        if (version_compare(_PS_VERSION_, '1.7', '>')) {
            $smarty = Context::getContext()->smarty;
            
            $smarty->assign(
                array(
                            $this->name.'is17' => 1,
                            'base_dir' => $base_dir_ssl,
                )
            );
        } else {
            $smarty = $this->context->smarty;

            $smarty->assign(
                    array(
                            $this->name.'is17' => 0,
                            'base_dir' => $base_dir_ssl,
                    )
            );
        }
        ## prestashop 1.7 ##

        $this->displayName = $this->l('Advanced Wish List');
        $this->description = $this->l('display Advanced Wish List');
        $this->default_wishlist_name = $this->l('My wishlist');
        $this->controllers = array('mywishlist', 'view');
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('ADVANSEDWISHLIST_NAV', true);
        Configuration::updateValue('ADVANSEDWISHLIST_TOP', false);
        
        include(dirname(__FILE__).'/sql/install.php');
        
        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('rightColumn') &&
            $this->registerHook('productActions') &&
            $this->registerHook('cart') &&
            $this->registerHook('customerAccount') &&
            $this->registerHook('header') &&
            $this->registerHook('BackOfficeHeader') &&
            $this->registerHook('adminCustomers') &&
            $this->registerHook('displayProductListFunctionalButtons') &&
            $this->registerHook('top')  &&
            $this->registerHook('displayMyAccountBlock')  &&
            $this->registerHook('displayNav1')  &&
            $this->registerHook('displayNav');
    }

    public function uninstall()
    {
        Configuration::deleteByName('ADVANSEDWISHLIST_NAV');

        include(dirname(__FILE__).'/sql/uninstall.php');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $this->_html  = '';
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitAdvansedwishlistModule')) == true) {
            $this->postProcess();
        } elseif (Tools::isSubmit('viewblockwishlist') && $id = Tools::getValue('id_product')) {
            Tools::redirect($this->context->link->getProductLink($id));
        } elseif (Tools::isSubmit('submitSettings')) {
            $activated = Tools::getValue('activated');
            if ($activated != 0 && $activated != 1) {
                $this->html .= '<div class="alert error alert-danger">'.$this->l('Activate module : Invalid choice.').'</div>';
            }
            $this->html .= '<div class="conf confirm alert alert-success">'.$this->l('Settings updated').'</div>';
        }

        $this->_html .= $this->renderForm();
        
        if (Tools::getValue('id_customer') && Tools::getValue('id_wishlist')) {
            $this->_html .= $this->renderList((int)Tools::getValue('id_wishlist'));
        }
        
        $this->context->controller->addJs($this->_path.'/views/js/back.js');
        
        return $this->_html;
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitAdvansedwishlistModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm($this->getConfigForm());
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        $fields_form = array();
        $fields_form[0]= array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Display in Nav'),
                        'name' => 'ADVANSEDWISHLIST_NAV',
                        'is_bool' => true,
                        'desc' => $this->l('Display block wishlist in nav hook'),
                        'values' => array(
                            array(
                                'id' => 'nav_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'nav_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                        array(
                                'type' => 'switch',
                                'label' => $this->l('Display in Top'),
                                'name' => 'ADVANSEDWISHLIST_TOP',
                                'is_bool' => true,
                                'desc' => $this->l('Display block wishlist in top hook'),
                                'values' => array(
                                        array(
                                                'id' => 'top_on',
                                                'value' => true,
                                                'label' => $this->l('Enabled')
                                        ),
                                        array(
                                                'id' => 'top_off',
                                                'value' => false,
                                                'label' => $this->l('Disabled')
                                        )
                                ),
                        ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
        
        $t_customers = Ws_WishList::getCustomers();

        $wl_customers = array();
        foreach ($t_customers as $c) {
            $wl_customers[$c['id_customer']]['id_customer'] = $c['id_customer'];
            $wl_customers[$c['id_customer']]['name'] = $c['firstname'].' '.$c['lastname'];
        }
         
        $fields_form[1]= array(
                'form' => array(
                        'legend' => array(
                                'title' => $this->l('Listing'),
                                'icon' => 'icon-cogs'
                        ),
                        'input' => array(
                                array(
                                        'type' => 'select',
                                        'label' => $this->l('Customers :'),
                                        'name' => 'id_customer',
                                        'options' => array(
                                                'default' => array('value' => 0, 'label' => $this->l('Choose customer')),
                                                'query' => $wl_customers,
                                                'id' => 'id_customer',
                                                'name' => 'name'
                                        ),
                                )
                        ),
                ),
        );
        
        if ($id_customer = Tools::getValue('id_customer')) {
            $wishlists = Ws_WishList::getByIdCustomer($id_customer);
            $fields_form[1]['form']['input'][] = array(
                    'type' => 'select',
                    'label' => $this->l('Wishlist :'),
                    'name' => 'id_wishlist',
                    'options' => array(
                            'default' => array('value' => 0, 'label' => $this->l('Choose wishlist')),
                            'query' => $wishlists,
                            'id' => 'id_wishlist',
                            'name' => 'name'
                    ),
            );
        }
        
        return $fields_form;
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'ADVANSEDWISHLIST_NAV' => Configuration::get('ADVANSEDWISHLIST_NAV'),
            'ADVANSEDWISHLIST_TOP' => Configuration::get('ADVANSEDWISHLIST_TOP'),
                'id_customer' => Tools::getValue('id_customer'),
                'id_wishlist' => Tools::getValue('id_wishlist'),
        );
    }

    public function renderList($id_wishlist)
    {
        $wishlist = new Ws_WishList($id_wishlist);
        $products = Ws_WishList::getProductByIdCustomer($id_wishlist, $wishlist->id_customer, $this->context->language->id);
    
        foreach ($products as $key => $val) {
            $image = Image::getCover($val['id_product']);
            $products[$key]['image'] = $this->context->link->getImageLink($val['link_rewrite'], $image['id_image'], ImageType::getFormatedName('small'));
        }
    
        $fields_list = array(
                'image' => array(
                        'title' => $this->l('Image'),
                        'type' => 'image',
                ),
                'name' => array(
                        'title' => $this->l('Product'),
                        'type' => 'text',
                ),
                'attributes_small' => array(
                        'title' => $this->l('Combination'),
                        'type' => 'text',
                ),
                'quantity' => array(
                        'title' => $this->l('Quantity'),
                        'type' => 'text',
                ),
                'priority' => array(
                        'title' => $this->l('Priority'),
                        'type' => 'priority',
                        'values' => array($this->l('High'), $this->l('Medium'), $this->l('Low')),
                ),
        );
    
        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = true;
        $helper->no_link = true;
        $helper->actions = array('view');
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->identifier = 'id_product';
        $helper->title = $this->l('Product list');
        $helper->table = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->tpl_vars = array('priority' => array($this->l('High'), $this->l('Medium'), $this->l('Low')));
    
        return $helper->generateList($products, $fields_list);
    }
    
    /**
     * Save form data.
     */
    protected function postProcess()
    {
        Configuration::updateValue('ADVANSEDWISHLIST_NAV', Tools::getValue('ADVANSEDWISHLIST_NAV'));
        Configuration::updateValue('ADVANSEDWISHLIST_TOP', Tools::getValue('ADVANSEDWISHLIST_TOP'));
        
        
        $this->_html .= $this->displayConfirmation($this->l('Settings updated'));
    }
    
    public function hookDisplayProductListFunctionalButtons($params)
    {
        $cookie = $params['cookie'];

        $this->smarty->assign(array(
            'id_product' => $params['product']['id_product'],
            'cache_default_attribute' => $params['product']['cache_default_attribute'],
            'logged' => $this->context->customer->isLogged(true),
        ));

        if (isset($cookie->id_customer)) {
            $this->smarty->assign(array(
                'wishlists' => Ws_WishList::getByIdCustomer($cookie->id_customer),
                'issetProduct' => Ws_WishList::issetProduct($this->context->cookie->id_wishlist, $params['product']['id_product']),
                'static_token' => Tools::getToken(false),
            ));
        }

        return $this->display(__FILE__, 'buttonwishlist.tpl');
    }

    public function hookDisplayNav($params)
    {
        if (Configuration::get('ADVANSEDWISHLIST_NAV')) {
            $products_count = 0;
            $wishlists = false;
             
            if ($this->context->customer->isLogged()) {
                $wishlists = Ws_Wishlist::getByIdCustomer($this->context->customer->id);
                
                if (empty($this->context->cookie->id_wishlist) === true || Ws_WishList::exists($this->context->cookie->id_wishlist, $this->context->customer->id) === false) {
                    $products_count = 0;
                } else {
                    if ($t_products_count = Ws_Wishlist::getInfosByIdCustomer($this->context->customer->id)) {
                        $products_count = $t_products_count[0]["nbProducts"];
                    }
                }
            }
            
            $this->smarty->assign(
                array(
                        'logged' => $this->context->customer->isLogged(),
                        'products_count' => (int)$products_count,
                        'wishlists' => $wishlists,
                        'show_text' => true,
                        'hook_name' => 'nav'
                )
            );
                        
            return $this->display(__FILE__, 'blockwishlist_top.tpl');
        }
    }
    
    public function hookDisplayNav1($params)
    {
        if (Configuration::get('ADVANSEDWISHLIST_NAV')) {
            $products_count = 0;
            $wishlists = false;
             
            if ($this->context->customer->isLogged()) {
                $wishlists = Ws_Wishlist::getByIdCustomer($this->context->customer->id);
    
                if (empty($this->context->cookie->id_wishlist) === true || Ws_WishList::exists($this->context->cookie->id_wishlist, $this->context->customer->id) === false) {
                    $products_count = 0;
                } else {
                    if ($t_products_count = Ws_Wishlist::getInfosByIdCustomer($this->context->customer->id)) {
                        $products_count = $t_products_count[0]["nbProducts"];
                    }
                }
            }
    
            $this->smarty->assign(
                array(
                        'logged' => $this->context->customer->isLogged(),
                        'products_count' => (int)$products_count,
                        'wishlists' => $wishlists,
                        'show_text' => true,
                        'hook_name' => 'nav'
                )
            );
    
            return $this->display(__FILE__, 'blockwishlist_top.tpl');
        }
    }
        
    public function hookTop($params)
    {
        if (Configuration::get('ADVANSEDWISHLIST_TOP')) {
            $products_count = 0;
            $wishlists = false;
             
            if ($this->context->customer->isLogged()) {
                $wishlists = Ws_Wishlist::getByIdCustomer($this->context->customer->id);
                 
                if (empty($this->context->cookie->id_wishlist) === true || Ws_WishList::exists($this->context->cookie->id_wishlist, $this->context->customer->id) === false) {
                    $products_count = 0;
                } else {
                    if ($t_products_count = Ws_Wishlist::getInfosByIdCustomer($this->context->customer->id)) {
                        $products_count = $t_products_count[0]["nbProducts"];
                    }
                }
            }
            
            $this->smarty->assign(
                array(
                        'logged' => $this->context->customer->isLogged(),
                        'products_count' => (int)$products_count,
                        'wishlists' => $wishlists,
                        'show_text' => false,
                        'hook_name' => 'top',
                )
            );
            
            return $this->display(__FILE__, 'blockwishlist_top.tpl');
        }
            /*
        if ($this->context->customer->isLogged()) {
            $wishlists = Ws_Wishlist::getByIdCustomer($this->context->customer->id);
            if (empty($this->context->cookie->id_wishlist) === true ||
            Ws_WishList::exists($this->context->cookie->id_wishlist, $this->context->customer->id) === false)
            {
                if (!sizeof($wishlists))
                {
        
                    $wishlist = new Ws_WishList();
                    $wishlist->id_shop = 1;
                    $wishlist->id_shop_group = 1;
                    $wishlist->counter = 1;
                    $wishlist->name = 'my wishlist';
        
                    $wishlist->id_customer = (int)($this->context->customer->id);
                    list($us, $s) = explode(' ', microtime());
                    srand($s * $us);
                    $wishlist->token = strtoupper(substr(sha1(uniqid(rand(), true)._COOKIE_KEY_.$this->context->customer->id), 0, 16));
                    $wishlist->add();
                    $this->context->cookie->id_wishlist = (int)($wishlist->id);
        
                    $id_wishlist = (int)($wishlist->id);
                }
                else
                {
                    $id_wishlist = (int)($wishlists[0]['id_wishlist']);
                    $this->context->cookie->id_wishlist = (int)($id_wishlist);
                }
            }
            else
                $id_wishlist = $this->context->cookie->id_wishlist;
        
            $products_count = Ws_WishList::getInfosByIdCustomer($this->context->customer->id);

            
            
            $this->smarty->assign(
                    array(
                            'id_wishlist' => $id_wishlist,
                            'isLogged' => true,
                            'wishlist_products' => ($id_wishlist == false ? false : Ws_WishList::getProductByIdCustomer($id_wishlist,
                                    $this->context->customer->id, $this->context->language->id, null, true)),
                            'wishlists' => $wishlists,
                            'products_count' => $products_count[0]["nbProducts"],
                            'ptoken' => Tools::getToken(false)
                    )
            );
        }
        else
            $this->smarty->assign(array('wishlist_products' => false, 'wishlists' => false));
        
        return $this->display(__FILE__, 'blockwishlist_top.tpl');
        
        */
    }
    
    public function hookRightColumn($params)
    {
        $products_count = 0;
    }

    public function hookLeftColumn($params)
    {
        $products_count = 0;
    }
    
    public function hookProductActions($params)
    {
        $cookie = $params['cookie'];

        $this->smarty->assign(array(
            'id_product' => (int)Tools::getValue('id_product'),
            'logged' => $this->context->customer->isLogged(true),
        ));

        if (isset($cookie->id_customer)) {
            $this->smarty->assign(array(
                'wishlists' => Ws_WishList::getByIdCustomer($cookie->id_customer),
                'issetProduct' => Ws_WishList::issetProduct($this->context->cookie->id_wishlist, (int)(Tools::getValue('id_product'))),
            ));
        }

        return $this->display(__FILE__, 'blockwishlist-extra.tpl');
    }
    
    public function hookCustomerAccount($params)
    {
        return $this->display(__FILE__, 'my-account.tpl');
    }
    
    public function hookDisplayMyAccountBlock($params)
    {
        return $this->hookCustomerAccount($params);
    }
    
    public function hookAdminCustomers($params)
    {
        $products_count = 0;
    }
    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
    }
    
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            //$this->context->controller->addJs($this->_path.'/views/js/back.js');
        }
    }
}
