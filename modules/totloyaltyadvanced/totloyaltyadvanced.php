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

require_once dirname(__FILE__).'/models/LoyaltyAdvanced.php';

class TotLoyaltyAdvanced extends Module
{
    public $html;
    /**
     * Loyalty status
     * @var boolean
     */

    /**
     * Constructor of module
     * @version 1.0.0
     */
    public function __construct()
    {
        $this->name = 'totloyaltyadvanced'; // Name module
        $this->default_name = 'totloyalty'; // Name module default
        $this->tab = 'pricing_promotion'; // Tab module
        $this->version = '2.0.0'; // Version of module 2.0.0 for Prestashop 1.7.0
        $this->author = '202-ecommerce'; // Author module

        parent::__construct(); // Parent constructor
        $this->controllers = array(
            'default'
        );
        $this->bootstrap = true;

        $this->displayName = $this->l('Customer loyalty and rewards Advanced'); // Translation display name
        $this->description = $this->l('Advanced features for loyalty program'); // Translation description
        $this->module_key = '3c8cb070cc93980b3c103167d3cd20e5';

        $this->disableLoyalty();

        // Check upgrade if enabled and installed
        if (self::isInstalled($this->name) && self::isEnabled($this->name)) {
            $this->installSQL();
            $this->upgrade();
        }

        Shop::addTableAssociation(Tools::strtolower($this->name), array('type' => 'shop'));

    }

    /**
     * Installing the module
     * @version 1.0.0
     * @return boolean
     */
    public function install()
    {
        include_once(dirname(__FILE__).'/LoyaltyStateModuleAdvanced.php');
        $tab = new Tab();
        $tab->id_parent = Tab::getIdFromClassName('AdminParentCustomer');
        $tab->module = $this->name;
        $tab->class_name = 'TotLoyaltyAdvancedAdmin';
        $languages = Language::getLanguages();
        foreach ($languages as $lang) {

            switch ($lang['iso_code'])
            {
                case 'fr':
                    $name = 'Liste des points de fidélité';
                    break;

                default:
                    $name = 'Display loyalty by customer';
                    break;
            }
            $tab->name[$lang['id_lang']] = $name;
        }

            $ver= _PS_VERSION_;
            $finalver = explode(".", $ver);

        if (($finalver[1]==6) || ($finalver[1]==5)) {
            if ($this->installSQL() === false
                || parent::install() === false
                || $this->registerHook('displayAdminProductsExtra') === false
                || $this->registerHook('actionProductSave') === false
                || $this->registerHook('actionProductDelete') === false
                || $this->registerHook('displayRightColumnProduct') === false
                || $this->registerHook('actionOrderReturn') === false
                || $this->registerHook('actionProductCancel') === false
                || $this->registerHook('displayAdminCustomers') === false
                || $this->registerHook('actionOrderStatusUpdate') === false
                || $this->registerHook('actionValidateOrder') === false
                || $this->registerHook('displayCustomerAccount') === false
                || $this->registerHook('displayShoppingCart') === false
                || $this->registerHook('displayShoppingCartFooter') === false
                || $this->registerHook('customerAccount') === false
                || !$this->registerHook('header')
                || !Configuration::updateValue('PS_LOYALTY_POINT_VALUE', '0.20')
                || !Configuration::updateValue('PS_LOYALTY_MINIMAL', 0)
                || !Configuration::updateValue('PS_LOYALTY_POINT_RATE', '10')
                || !Configuration::updateValue('PS_LOYALTY_NONE_AWARD', '1')
                || !Configuration::updateValue('PS_LOYALTY_TAX', '0')
                || !Configuration::updateValue('PS_LOYALTY_VALIDITY_PERIOD', '100')
                || $tab->save() === false) {
                return false;
            }
        }
        if (($finalver[1]==7)) {
            if ($this->installSQL() === false
                || parent::install() === false
                || $this->registerHook('displayAdminProductsExtra') === false
                || $this->registerHook('actionProductSave') === false
                || $this->registerHook('actionProductDelete') === false
                || $this->registerHook('displayReassurance') === false
                || $this->registerHook('actionOrderReturn') === false
                || $this->registerHook('actionProductCancel') === false
                || $this->registerHook('displayAdminCustomers') === false
                || $this->registerHook('actionOrderStatusUpdate') === false
                || $this->registerHook('actionValidateOrder') === false
                || $this->registerHook('displayCustomerAccount') === false
                || $this->registerHook('displayShoppingCart') === false
                || $this->registerHook('displayShoppingCartFooter') === false
                || $this->registerHook('customerAccount') === false
                || !$this->registerHook('header')
                || !Configuration::updateValue('PS_LOYALTY_POINT_VALUE', '0.20')
                || !Configuration::updateValue('PS_LOYALTY_MINIMAL', 0)
                || !Configuration::updateValue('PS_LOYALTY_POINT_RATE', '10')
                || !Configuration::updateValue('PS_LOYALTY_NONE_AWARD', '1')
                || !Configuration::updateValue('PS_LOYALTY_TAX', '0')
                || !Configuration::updateValue('PS_LOYALTY_VALIDITY_PERIOD', '100')
                || $tab->save() === false) {
                return false;
            }
        }
        
        $defaultTranslations = array('en' => 'Loyalty reward', 'fr' => 'Récompense fidélité');
        $conf = array((int)Configuration::get('PS_LANG_DEFAULT') => $this->l('Loyalty reward'));
        foreach (Language::getLanguages() as $language) {
            if (isset($defaultTranslations[$language['iso_code']])) {
                $conf[(int)$language['id_lang']] = $defaultTranslations[$language['iso_code']];
            }
        }
        Configuration::updateValue('PS_LOYALTY_VOUCHER_DETAILS', $conf);

        $category_config = '';
        $categories = Category::getSimpleCategories((int)Configuration::get('PS_LANG_DEFAULT'));
        foreach ($categories as $category) {
            $category_config .= (int)$category['id_category'].',';
        }
        $category_config = rtrim($category_config, ',');
        Configuration::updateValue('PS_LOYALTY_VOUCHER_CATEGORY', $category_config);

        /* This hook is optional */
        $this->registerHook('displayMyAccountBlock');
        if (!LoyaltyStateModuleAdvanced::insertDefaultData()) {
            return false;
        }

        return true;
    }

    /**
     * Install SQL
     * @return boolean
     */
    private function installSQL()
    {
        $show_sql = "SHOW TABLES LIKE '"._DB_PREFIX_.Tools::strtolower($this->default_name)."'";
        $show_result = DB::getInstance()->ExecuteS($show_sql);
        
        $show_sql1 = "SHOW TABLES LIKE '"._DB_PREFIX_.Tools::strtolower($this->default_name)."_history'";
        $show_result1 = DB::getInstance()->ExecuteS($show_sql1);
        
        $show_sql2 = "SHOW TABLES LIKE '"._DB_PREFIX_.Tools::strtolower($this->default_name)."_state'";
        $show_result2 = DB::getInstance()->ExecuteS($show_sql2);
        
        $show_sql3 = "SHOW TABLES LIKE '"._DB_PREFIX_.Tools::strtolower($this->default_name)."_state_lang'";
        $show_result3 = DB::getInstance()->ExecuteS($show_sql3);

        if (empty($show_result) && empty($show_result1) && empty($show_result2) && empty($show_result3)) {
                $sql = '
              CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.Tools::strtolower($this->default_name).'` (
               `id_loyalty` INT UNSIGNED NOT NULL AUTO_INCREMENT,
               `id_loyalty_state` INT UNSIGNED NOT NULL DEFAULT 1,
               `id_customer` INT UNSIGNED NOT NULL,
               `id_order` INT UNSIGNED DEFAULT NULL,
               `id_cart_rule` INT UNSIGNED DEFAULT NULL,
               `points` INT NOT NULL DEFAULT 0,
               `date_add` DATETIME NOT NULL,
               `date_upd` DATETIME NOT NULL,
               PRIMARY KEY (`id_loyalty`),
               INDEX index_loyalty_loyalty_state (`id_loyalty_state`),
               INDEX index_loyalty_order (`id_order`),
               INDEX index_loyalty_discount (`id_cart_rule`),
               INDEX index_loyalty_customer (`id_customer`)
              ) DEFAULT CHARSET=utf8 ;';
              
            $return = DB::getInstance()->Execute($sql);

            $sql = '
              CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.Tools::strtolower($this->default_name).'_history` (
               `id_loyalty_history` INT UNSIGNED NOT NULL AUTO_INCREMENT,
               `id_loyalty` INT UNSIGNED DEFAULT NULL,
               `id_loyalty_state` INT UNSIGNED NOT NULL DEFAULT 1,
               `points` INT NOT NULL DEFAULT 0,
               `date_add` DATETIME NOT NULL,
               PRIMARY KEY (`id_loyalty_history`),
               INDEX `index_loyalty_history_loyalty` (`id_loyalty`),
               INDEX `index_loyalty_history_loyalty_state` (`id_loyalty_state`)
              ) DEFAULT CHARSET=utf8 ;';
            $return &= DB::getInstance()->Execute($sql);

            $sql = '
              CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.Tools::strtolower($this->default_name).'_state` (
               `id_loyalty_state` INT UNSIGNED NOT NULL AUTO_INCREMENT,
               `id_order_state` INT UNSIGNED DEFAULT NULL,
               PRIMARY KEY (`id_loyalty_state`),
               INDEX index_loyalty_state_order_state (`id_order_state`)
              ) DEFAULT CHARSET=utf8 ;';
            $return &= DB::getInstance()->Execute($sql);

            $sql = '
              CREATE TABLE IF NOT EXISTS`'._DB_PREFIX_.Tools::strtolower($this->default_name).'_state_lang` (
               `id_loyalty_state` INT UNSIGNED NOT NULL AUTO_INCREMENT,
               `id_lang` INT UNSIGNED NOT NULL,
               `name` varchar(64) NOT NULL,
               UNIQUE KEY `index_unique_loyalty_state_lang` (`id_loyalty_state`,`id_lang`)
              ) DEFAULT CHARSET=utf8 ;';
            $return &= DB::getInstance()->Execute($sql);
      
            $sql = '
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.Tools::strtolower($this->name).'` (
                `id_totloyaltyadvanced` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `id_product` INT NOT NULL,
                `loyalty` VARCHAR(20) NOT NULL,
                `date_begin` DATE NOT NULL,
                `date_finish` DATE NOT NULL
                ) ENGINE='._MYSQL_ENGINE_.' ';

            $return &= DB::getInstance()->Execute($sql);

            // Add table shop
            $sql = '
                CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.Tools::strtolower($this->name).'_shop` (
                    `id_totloyaltyadvanced` INT NOT NULL,
                    `id_shop` INT NOT NULL
                ) ENGINE='._MYSQL_ENGINE_.' ';

            $return &= DB::getInstance()->execute($sql);

            return $return;
        }
    }

    public function getL($key)
    {
        $translations = array(
            'Awaiting validation' => $this->l('Awaiting validation'),
            'Available' => $this->l('Available'),
            'Cancelled' => $this->l('Cancelled'),
            'Already converted' => $this->l('Already converted'),
            'Unavailable on discounts' => $this->l('Unavailable on discounts'),
            'Not available on discounts.' => $this->l('Not available on discounts.'));

        return (array_key_exists($key, $translations)) ? $translations[$key] : $key;
    }

    public function upgrade()
    {
        $ver= _PS_VERSION_;
        $finalver = explode(".", $ver);

        if (($finalver[1]==6) || ($finalver[1]==5)) {
            $this->registerHook('displayShoppingCart');
            $show_sql = "SHOW TABLES LIKE '"._DB_PREFIX_.Tools::strtolower($this->default_name)."'";
            $show_result = DB::getInstance()->ExecuteS($show_sql);
 
            $show_sql1 = "SHOW TABLES LIKE '"._DB_PREFIX_.Tools::strtolower($this->default_name)."_history'";
            $show_result1 = DB::getInstance()->ExecuteS($show_sql1);
 
            $show_sql2 = "SHOW TABLES LIKE '"._DB_PREFIX_.Tools::strtolower($this->default_name)."_state'";
            $show_result2 = DB::getInstance()->ExecuteS($show_sql2);
 
            $show_sql3 = "SHOW TABLES LIKE '"._DB_PREFIX_.Tools::strtolower($this->default_name)."_state_lang'";
            $show_result3 = DB::getInstance()->ExecuteS($show_sql3);

            if (!empty($show_result) && !empty($show_result1) && !empty($show_result2) && !empty($show_result3)) {

                $this->loyalty_table = 'loyalty';
                // Configuration name
                $cfg_name = Tools::strtoupper($this->name.'_version');

                // Get latest version upgraded
                $version = Configuration::get($cfg_name);

                $show_sql = "SHOW TABLES LIKE '"._DB_PREFIX_.Tools::strtolower($this->loyalty_table)."'";
                $show_result = DB::getInstance()->ExecuteS($show_sql);
 
                if ($show_result) {
                    $sql1 = 'INSERT INTO `'._DB_PREFIX_.Tools::strtolower($this->default_name).'`(`id_loyalty`,`id_loyalty_state`,`id_customer`,`id_order`,`id_cart_rule`,`points`,`date_add`,`date_upd`)
					SELECT `id_loyalty`,`id_loyalty_state`,`id_customer`,`id_order`,`id_cart_rule`,`points`,`date_add`,`date_upd`
					FROM '._DB_PREFIX_.Tools::strtolower($this->loyalty_table);
                    Db::getInstance()->ExecuteS($sql1);
  
                    Db::getInstance()->ExecuteS('DROP TABLE IF EXISTS `'._DB_PREFIX_.Tools::strtolower($this->loyalty_table).'`');
                }
 
                $show_sql1 = "SHOW TABLES LIKE '"._DB_PREFIX_.Tools::strtolower($this->loyalty_table)."_history'";
                $show_result1 = DB::getInstance()->ExecuteS($show_sql1);
 
                if ($show_result1) {
                    $sql2 = 'INSERT INTO `'._DB_PREFIX_.Tools::strtolower($this->default_name).'_history'.'`(`id_loyalty_history`,`id_loyalty`,`id_loyalty_state`,`points`,`date_add`)
					SELECT `id_loyalty_history`,`id_loyalty`,`id_loyalty_state`,`points`,`date_add`
					FROM '._DB_PREFIX_.Tools::strtolower($this->loyalty_table)."_history";
                    Db::getInstance()->ExecuteS($sql2);

                    Db::getInstance()->ExecuteS('DROP TABLE IF EXISTS `'._DB_PREFIX_.Tools::strtolower($this->loyalty_table).'_history`;');
                }
 
                $show_sql2 = "SHOW TABLES LIKE '"._DB_PREFIX_.Tools::strtolower($this->loyalty_table)."_state'";
                $show_result2 = DB::getInstance()->ExecuteS($show_sql2);
 
                if ($show_result2) {
                    $sql3 = 'INSERT INTO `'._DB_PREFIX_.Tools::strtolower($this->default_name).'_state'.'`(`id_loyalty_state`,`id_order_state`)
					SELECT `id_loyalty_state`,`id_order_state`
					FROM '._DB_PREFIX_.Tools::strtolower($this->loyalty_table)."_state";
                    Db::getInstance()->ExecuteS($sql3);

                    Db::getInstance()->ExecuteS('DROP TABLE IF EXISTS `'._DB_PREFIX_.Tools::strtolower($this->loyalty_table).'_state`;');
                }
 
                $show_sql3 = "SHOW TABLES LIKE '"._DB_PREFIX_.Tools::strtolower($this->loyalty_table)."_state_lang'";
                $show_result3 = DB::getInstance()->ExecuteS($show_sql3);

                if ($show_result3) {
                    $sql3 = 'INSERT INTO `'._DB_PREFIX_.Tools::strtolower($this->default_name).'_state_lang'.'`(`id_loyalty_state`,`id_lang`,`name`)
					SELECT `id_loyalty_state`,`id_lang`,`name`
					FROM '._DB_PREFIX_.Tools::strtolower($this->loyalty_table)."_state_lang";
                    Db::getInstance()->ExecuteS($sql3);
 
                    Db::getInstance()->ExecuteS('DROP TABLE IF EXISTS `'._DB_PREFIX_.Tools::strtolower($this->loyalty_table).'_state_lang`;');
                }
  
                // If the first time OR the latest version upgrade is older than this one
                if ($version === false || version_compare($version, $this->version, '<')) {

                    if ($version === false || version_compare($version, '1.0.5', '<')) {

 
                        // Get Instance of Loyalty module
                        $loyalty = Module::getInstanceByName('totloyaltyadvanced');
                        // Unhook
                        $loyalty->unregisterHook('customerAccount');
 
                        // New hook
                        $this->registerHook('customerAccount');
                    } else if ($version === false || version_compare($version, '1.1', '<')) {

 
                       // Add campaign
                        $sql = 'ALTER TABLE  `'._DB_PREFIX_.Tools::strtolower($this->name).'`
							ADD  `date_begin` DATE NOT NULL AFTER  `loyalty` ,
							ADD  `date_finish` DATE NOT NULL AFTER  `date_begin`,
							DROP `id_shop`,
							DROP `id_shop_group`; ';

                        DB::getInstance()->execute($sql);

                        // Edit primary
                        $sql = 'ALTER TABLE  `'._DB_PREFIX_.Tools::strtolower($this->name).'`  DROP PRIMARY KEY ,
							ADD UNIQUE (
								`id_product`
							) ';

                        DB::getInstance()->execute($sql);

                        // Add primary
                        $sql = 'ALTER TABLE  `'._DB_PREFIX_.Tools::strtolower($this->name).'`
							ADD  `id_totloyaltyadvanced` INT NOT NULL AUTO_INCREMENT FIRST ,
							ADD PRIMARY KEY ( `id_totloyaltyadvanced` )';

                        DB::getInstance()->execute($sql);

                        // Add table shop
                        $sql = '
							CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.Tools::strtolower($this->name).'_shop` (
								`id_totloyaltyadvanced` INT NOT NULL,
								`id_shop` INT NOT NULL
							) ENGINE='._MYSQL_ENGINE_.' ';

                        DB::getInstance()->execute($sql);
                    }

                    // Upgrade in DataBase the new version
                    Configuration::updateValue($cfg_name, $this->version);
                }
            }
        }
    }

     /**
     * Removing the module
     * @version 1.0.0
     * @return boolean
     */
    public function uninstall()
    {
        if (!parent::uninstall() || !Configuration::deleteByName('PS_LOYALTY_POINT_VALUE') || !Configuration::deleteByName('PS_LOYALTY_POINT_RATE')
            || !Configuration::deleteByName('PS_LOYALTY_NONE_AWARD') || !Configuration::deleteByName('PS_LOYALTY_MINIMAL') || !Configuration::deleteByName('PS_LOYALTY_VOUCHER_CATEGORY')
            || !Configuration::deleteByName('PS_LOYALTY_VOUCHER_DETAILS') || !Configuration::deleteByName('PS_LOYALTY_TAX') || !Configuration::deleteByName('PS_LOYALTY_VALIDITY_PERIOD')) {
            return false;
        }
        // ID tab
        $id_tab = Tab::getIdFromClassName('TotLoyaltyAdvancedAdmin');
        // Create object
        $tab = new Tab($id_tab);
        // Delete tab
        if (!$tab->delete()) {
            return false;
        }
 
        $this->loyalty_table = 'loyalty'; // prefix name on loyalty table
  
        $show_sql = "SHOW TABLES LIKE '"._DB_PREFIX_.Tools::strtolower($this->loyalty_table)."'";
        $show_result = DB::getInstance()->ExecuteS($show_sql);

        $show_sql1 = "SHOW TABLES LIKE '"._DB_PREFIX_.Tools::strtolower($this->loyalty_table)."_history'";
        $show_result1 = DB::getInstance()->ExecuteS($show_sql1);

        $show_sql2 = "SHOW TABLES LIKE '"._DB_PREFIX_.Tools::strtolower($this->loyalty_table)."_state'";
        $show_result2 = DB::getInstance()->ExecuteS($show_sql2);
 
        $show_sql3 = "SHOW TABLES LIKE '"._DB_PREFIX_.Tools::strtolower($this->loyalty_table)."_state_lang'";
        $show_result3 = DB::getInstance()->ExecuteS($show_sql3);
 
        if ($show_result && $show_result1 && $show_result2 && $show_result3) {
            Db::getInstance()->execute('DROP TABLE IF EXISTS  `'._DB_PREFIX_.Tools::strtolower($this->loyalty_table).'`');
            Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.Tools::strtolower($this->loyalty_table).'_state`;');
            Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.Tools::strtolower($this->loyalty_table).'_state_lang`;');
            Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.Tools::strtolower($this->loyalty_table).'_history`;');
        }
  
        Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.Tools::strtolower($this->default_name).'`');
        Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.Tools::strtolower($this->default_name).'_state`;');
        Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.Tools::strtolower($this->default_name).'_state_lang`;');
        Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.Tools::strtolower($this->default_name).'_history`;');
 
        $sql = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.Tools::strtolower($this->name).'`';
        if (parent::uninstall() === false || DB::getInstance()->Execute($sql) === false) {
            return false;
        }
        return true;
    }

    ################################################################################################
    ## Hook
    ################################################################################################

    /**
     * Display Tab in BO
     * @return string
     */
    public function hookdisplayAdminProductsExtra($params)
    {
        $ver= _PS_VERSION_;
        $finalver = explode(".", $ver);
 
        if (($finalver[1]==6)||($finalver[1]==5)) {
            $loy = LoyaltyAdvanced::getLoyaltyByIDProduct((int)Tools::getValue('id_product'));
        }

        if (($finalver[1]==7)) {
            $loy = LoyaltyAdvanced::getLoyaltyByIDProduct((int)$params['id_product']);
        }
 
        $datas = array(
            'name'    => $this->displayName,
            'loyalty' => $loy
        );

        $this->context->smarty->assign($datas);

        $ver= _PS_VERSION_;
        $finalver = explode(".", $ver);

        if (($finalver[1]==7)) {
            return $this->display(__FILE__, 'AdminProduct-latest.tpl');
        } else {
            return $this->display(__FILE__, 'AdminProduct.tpl');
        }
    }

    public function hookdisplayShoppingCart($params)
    {
        $discounts = array();
        if ($ids_discount = LoyaltyModuleAdvanced::getDiscountByIdCustomer((int)$this->context->customer->id)) {
            foreach ($ids_discount as $key => $discount) {
                $discounts[$key] = new CartRule((int)$discount['id_cart_rule'], (int)$this->context->cookie->id_lang);
                $discounts[$key]->orders = LoyaltyModuleAdvanced::getVoucher((int)$discount['id_cart_rule']);
            }
        }
        $this->context->smarty->assign(array(
         'discountsCustom' => $discounts
        ));
        $ver= _PS_VERSION_;
        $finalver = explode(".", $ver);
        if (($finalver[1]==6) || ($finalver[1]==5)) {
            $this->context->controller->addCSS(_MODULE_DIR_.'totloyaltyadvanced/views/css/totloyaltyrewarded_custom6.css');
            return $this->display(__FILE__, 'voucher.tpl');
        } else {
            return $this->display(__FILE__, 'voucher-latest.tpl');
        }
    }
    /**
     * Save Product
     * @param array
     * @return boolean
     */
    public function hookactionProductSave($product)
    {
        return $this->saveAndUpdate($product);
    }

    /**
     * Delete Product
     * @param array
     * @return boolean
     */
    public function hookactionProductDelete($product)
    {
        return $this->deleteLoyalty($product);
    }

    ################################################################################################
    ## Admin
    ################################################################################################

    /**
     * BO
     * @return string
     */

    public function renderForm()
    {
        $order_states = OrderState::getOrderStates($this->context->language->id);
        $currency = new Currency((int)(Configuration::get('PS_CURRENCY_DEFAULT')));

        $root_category = Category::getRootCategory();
        $root_category = array('id_category' => $root_category->id, 'name' => $root_category->name);

        if (Tools::getValue('categoryBox')) {
            $selected_categories = Tools::getValue('categoryBox');
        } else {
            $selected_categories = explode(',', Configuration::get('PS_LOYALTY_VOUCHER_CATEGORY'));
        }


        $fields_form_1 = array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs'
                ),
                'input' => array(
                array(
                    'type' => 'text',
                        'label' => $this->l('Ratio'),
                        'name' => 'point_rate',
                        'prefix' => $currency->sign,
                        'suffix' => $this->l('= 1 reward point.'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('1 point ='),
                        'name' => 'point_value',
                        'prefix' => $currency->sign,
                        'suffix' => $this->l('for the discount.'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Validity period of a point'),
                        'name' => 'validity_period',
                        'suffix' => $this->l('days'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Voucher details'),
                        'name' => 'voucher_details',
                        'lang' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Minimum amount in which the voucher can be used'),
                        'name' => 'minimal',
                        'prefix' => $currency->sign,
                        'class' => 'fixed-width-sm',
                    ),
                    array(
                        'type' => 'switch',
                        'is_bool' => true, //retro-compat
                        'label' => $this->l('Apply taxes on the voucher'),
                        'name' => 'PS_LOYALTY_TAX',
                        'values' => array(
                        array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Points are awarded when the order is'),
                        'name' => 'id_order_state_validation',
                        'options' => array(
                            'query' => $order_states,
                            'id' => 'id_order_state',
                            'name' => 'name',
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Points are cancelled when the order is'),
                        'name' => 'id_order_state_cancel',
                        'options' => array(
                            'query' => $order_states,
                            'id' => 'id_order_state',
                            'name' => 'name',
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'is_bool' => true, //retro-compat
                        'label' => $this->l('Give points on discounted products'),
                        'name' => 'PS_LOYALTY_NONE_AWARD',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        )
                    ),
                    array(
                        'type' => 'categories',
                        'label' => $this->l('Vouchers created by the loyalty system can be used in the following categories:'),
                        'name' => 'categoryBox',
                        'desc' => $this->l('Mark the boxes of categories in which loyalty vouchers can be used.'),
                        'tree' => array(
                            'use_search' => false,
                            'id' => 'categoryBox',
                            'use_checkbox' => true,
                            'selected_categories' => $selected_categories,
                        ),
                        //retro compat 1.5 for category tree
                        'values' => array(
                            'trads' => array(
                                'Root' => $root_category,
                                'selected' => $this->l('Selected'),
                                'Collapse All' => $this->l('Collapse All'),
                                'Expand All' => $this->l('Expand All'),
                                'Check All' => $this->l('Check All'),
                                'Uncheck All' => $this->l('Uncheck All')
                            ),
                            'selected_cat' => $selected_categories,
                            'input_name' => 'categoryBox[]',
                            'use_radio' => false,
                            'use_search' => false,
                            'disabled_categories' => array(),
                            'top_category' => Category::getTopCategory(),
                            'use_context' => true,
                        )
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );




        $fields_form_2 = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Loyalty points progression'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Initial'),
                        'name' => 'default_loyalty_state',
                        'lang' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Unavailable'),
                        'name' => 'none_award_loyalty_state',
                        'lang' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Converted'),
                        'name' => 'convert_loyalty_state',
                        'lang' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Validation'),
                        'name' => 'validation_loyalty_state',
                        'lang' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Cancelled'),
                        'name' => 'cancel_loyalty_state',
                        'lang' => true,
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->show_toolbar = false;
        $helper->table =  $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitLoyalty';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&click_form=yes';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
        return $helper->generateForm(array($fields_form_1, $fields_form_2));
    }

    private function _postProcess()
    {
        if (Tools::isSubmit('submitLoyalty')) {
            $id_lang_default = (int)Configuration::get('PS_LANG_DEFAULT');
            $languages = Language::getLanguages();

            $this->_errors = array();
            if (!is_array(Tools::getValue('categoryBox')) || !count(Tools::getValue('categoryBox'))) {
                $this->_errors[] = $this->l('You must choose at least one category for voucher\'s action');
            }
            if (!count($this->_errors)) {
                Configuration::updateValue('PS_LOYALTY_VOUCHER_CATEGORY', $this->voucherCategories(Tools::getValue('categoryBox')));
                Configuration::updateValue('PS_LOYALTY_POINT_VALUE', (float)(Tools::getValue('point_value')));
                Configuration::updateValue('PS_LOYALTY_POINT_RATE', (float)(Tools::getValue('point_rate')));
                Configuration::updateValue('PS_LOYALTY_NONE_AWARD', (int)(Tools::getValue('PS_LOYALTY_NONE_AWARD')));
                Configuration::updateValue('PS_LOYALTY_MINIMAL', (float)(Tools::getValue('minimal')));
                Configuration::updateValue('PS_LOYALTY_TAX', (int)(Tools::getValue('PS_LOYALTY_TAX')));
                Configuration::updateValue('PS_LOYALTY_VALIDITY_PERIOD', (int)(Tools::getValue('validity_period')));

                $this->loyaltyStateValidation->id_order_state = (int)(Tools::getValue('id_order_state_validation'));
                $this->loyaltyStateCancel->id_order_state = (int)(Tools::getValue('id_order_state_cancel'));

                $arrayVoucherDetails = array();
                foreach ($languages as $language) {
                    $arrayVoucherDetails[(int)($language['id_lang'])] = Tools::getValue('voucher_details_'.(int)($language['id_lang']));
                    $this->loyaltyStateDefault->name[(int)($language['id_lang'])] = Tools::getValue('default_loyalty_state_'.(int)($language['id_lang']));
                    $this->loyaltyStateValidation->name[(int)($language['id_lang'])] = Tools::getValue('validation_loyalty_state_'.(int)($language['id_lang']));
                    $this->loyaltyStateCancel->name[(int)($language['id_lang'])] = Tools::getValue('cancel_loyalty_state_'.(int)($language['id_lang']));
                    $this->loyaltyStateConvert->name[(int)($language['id_lang'])] = Tools::getValue('convert_loyalty_state_'.(int)($language['id_lang']));
                    $this->loyaltyStateNoneAward->name[(int)($language['id_lang'])] = Tools::getValue('none_award_loyalty_state_'.(int)($language['id_lang']));
                }
                if (empty($arrayVoucherDetails[$id_lang_default])) {
                    $arrayVoucherDetails[$id_lang_default] = ' ';
                }
                Configuration::updateValue('PS_LOYALTY_VOUCHER_DETAILS', $arrayVoucherDetails);

                if (empty($this->loyaltyStateDefault->name[$id_lang_default])) {
                    $this->loyaltyStateDefault->name[$id_lang_default] = ' ';
                }
                $this->loyaltyStateDefault->save();

                if (empty($this->loyaltyStateValidation->name[$id_lang_default])) {
                    $this->loyaltyStateValidation->name[$id_lang_default] = ' ';
                }
                $this->loyaltyStateValidation->save();

                if (empty($this->loyaltyStateCancel->name[$id_lang_default])) {
                    $this->loyaltyStateCancel->name[$id_lang_default] = ' ';
                }
                $this->loyaltyStateCancel->save();

                if (empty($this->loyaltyStateConvert->name[$id_lang_default])) {
                    $this->loyaltyStateConvert->name[$id_lang_default] = ' ';
                }
                $this->loyaltyStateConvert->save();

                if (empty($this->loyaltyStateNoneAward->name[$id_lang_default])) {
                    $this->loyaltyStateNoneAward->name[$id_lang_default] = ' ';
                }
                $this->loyaltyStateNoneAward->save();

                $this->html .= $this->displayConfirmation($this->l('Settings updated.'));
            } else {
                $errors = '';
                foreach ($this->_errors as $error) {
                    $errors .= $error.'<br />';
                }
                $this->html .= $this->displayError($errors);
            }
        }
    }

    public function getConfigFieldsValues()
    {
        $fields_values = array(
            'point_rate' => Tools::getValue('PS_LOYALTY_POINT_RATE', Configuration::get('PS_LOYALTY_POINT_RATE')),
            'point_value' => Tools::getValue('PS_LOYALTY_POINT_VALUE', Configuration::get('PS_LOYALTY_POINT_VALUE')),
            'PS_LOYALTY_NONE_AWARD' => Tools::getValue('PS_LOYALTY_NONE_AWARD', Configuration::get('PS_LOYALTY_NONE_AWARD')),
            'minimal' => Tools::getValue('PS_LOYALTY_MINIMAL', Configuration::get('PS_LOYALTY_MINIMAL')),
            'validity_period' => Tools::getValue('PS_LOYALTY_VALIDITY_PERIOD', Configuration::get('PS_LOYALTY_VALIDITY_PERIOD')),
            'id_order_state_validation' => Tools::getValue('id_order_state_validation', $this->loyaltyStateValidation->id_order_state),
            'id_order_state_cancel' => Tools::getValue('id_order_state_cancel', $this->loyaltyStateCancel->id_order_state),
            'PS_LOYALTY_TAX' => Tools::getValue('PS_LOYALTY_TAX', Configuration::get('PS_LOYALTY_TAX')),
        );
 
        $languages = Language::getLanguages(false);

        foreach ($languages as $lang) {
            $fields_values['voucher_details'][$lang['id_lang']] = Tools::getValue('voucher_details_'.(int)$lang['id_lang'], Configuration::get('PS_LOYALTY_VOUCHER_DETAILS', (int)$lang['id_lang']));
            $fields_values['default_loyalty_state'][$lang['id_lang']] = Tools::getValue('default_loyalty_state_'.(int)$lang['id_lang'], $this->loyaltyStateDefault->name[(int)($lang['id_lang'])]);
            $fields_values['validation_loyalty_state'][$lang['id_lang']] = Tools::getValue('validation_loyalty_state_'.(int)$lang['id_lang'], $this->loyaltyStateValidation->name[(int)($lang['id_lang'])]);
            $fields_values['cancel_loyalty_state'][$lang['id_lang']] = Tools::getValue('cancel_loyalty_state_'.(int)$lang['id_lang'], $this->loyaltyStateCancel->name[(int)($lang['id_lang'])]);
            $fields_values['convert_loyalty_state'][$lang['id_lang']] = Tools::getValue('convert_loyalty_state_'.(int)$lang['id_lang'], $this->loyaltyStateConvert->name[(int)($lang['id_lang'])]);
            $fields_values['none_award_loyalty_state'][$lang['id_lang']] = Tools::getValue('none_award_loyalty_state_'.(int)$lang['id_lang'], $this->loyaltyStateNoneAward->name[(int)($lang['id_lang'])]);
        }
        return $fields_values;
    }

    private function voucherCategories($categories)
    {
        $cat = '';
        if ($categories && is_array($categories)) {
            foreach ($categories as $category) {
                $cat .= $category.',';
            }
        }
        return rtrim($cat, ',');
    }

    public function getContent()
    {
        if (Tools::getValue('click_form') || Tools::isSubmit('submitLoyalty')) {
            include_once(dirname(__FILE__).'/classes/TotLoyaltyState.php');

            /* Recover default loyalty status save at module installation */
            $this->loyaltyStateDefault = new TotLoyaltyState(TotLoyaltyState::getDefaultId());
            $this->loyaltyStateValidation = new TotLoyaltyState(TotLoyaltyState::getValidationId());
            $this->loyaltyStateCancel = new TotLoyaltyState(TotLoyaltyState::getCancelId());
            $this->loyaltyStateConvert = new TotLoyaltyState(TotLoyaltyState::getConvertId());
            $this->loyaltyStateNoneAward = new TotLoyaltyState(TotLoyaltyState::getNoneAwardId());
            $this->_postProcess();

            $this->html = $this->renderForm();
            return $this->html;
        } else {
            $link = new Link();
            $this->context->controller->addCSS($this->_path.'/views/css/totloyaltyrewarded_back.css');
            $this->context->controller->addCSS($this->_path.'/views/css/totloyalty16_back.css');


            $module = Module::getInstanceByName('totloyaltyadvanced');
            $this->smarty->assign(array(
                'url' => $link->getAdminLink('AdminModules').'&configure='.$module->name.'&tab_module='.$module->tab.'&module_name='.$module->name.'&click_form=yes'
                ));

            $this->smarty->assign(array(
                'DisplayName' => $this->displayName,
                '_path'       => $this->_path,
                ));


            $ver= _PS_VERSION_;
            $finalver = explode(".", $ver);

            $html = '';
            if (($finalver[1]==7)) {

                $html .= '
                <style>
                    .nobootstrap {
                        min-width: 1px;
                        width: auto;
                    }
                </style>
                ';
            }
            //fix display admin banner
            return $html.$this->displayBann().$this->display(__FILE__, 'views/templates/admin/adminGetContent.tpl');
        }
    }

    private function displayBann()
    {
        $this->context->controller->addCSS($this->_path. '/views/css/banner.css');
        return $this->display(dirname(__FILE__), '/views/templates/admin/banner.tpl');
    }


    ################################################################################################
    ## Method
    ################################################################################################

    private function disableLoyalty()
    {
        $loyalty_id = Module::getModuleIdByName('loyalty');

        if ($loyalty_id != 0) {

            $sql = 'DELETE FROM `'._DB_PREFIX_."hook_module` WHERE `id_module` = '".(int)$loyalty_id."' ";
            DB::getInstance()->execute($sql);

            $sql12 = 'DELETE FROM `'._DB_PREFIX_."module_access` WHERE `id_module` = '".(int)$loyalty_id."' ";
            DB::getInstance()->execute($sql12);

            $sql13 = 'DELETE FROM `'._DB_PREFIX_."module_group` WHERE `id_module` = '".(int)$loyalty_id."' ";
            DB::getInstance()->execute($sql13);

            $sql14 = 'DELETE FROM `'._DB_PREFIX_."module_shop` WHERE `id_module` = '".(int)$loyalty_id."' ";
            DB::getInstance()->execute($sql14);

            $sql15 = 'DELETE FROM `'._DB_PREFIX_."module` WHERE `id_module` = '".(int)$loyalty_id."' ";
            DB::getInstance()->execute($sql15);
        }
    }

    /**
     * Add or Update product
     * @param array
     * @return boolean
     */
    private function saveAndUpdate($product)
    {
        if (Tools::getValue('loyalty_filled') != false) {

            $loyalty = LoyaltyAdvanced::getLoyaltyByIDProduct($product['id_product']);
            if ($this->isLoyalty(Tools::getValue('loyalty')) === true) {

                $loyalty->id_product = (int)$product['id_product'];
                $loyalty->loyalty = Tools::getValue('loyalty');
                $loyalty->date_begin = Tools::getValue('date_begin');
                $loyalty->date_finish = Tools::getValue('date_finish');

                return $loyalty->save();

            } else {

                // If loyalty exists
                if ($loyalty->id) {
                    $loyalty->delete();
                }
            }
        }
    }

    /**
     * Delete product
     * @param array product
     * @return boolean
     */
    private function deleteLoyalty($product)
    {
        $loyalty = LoyaltyAdvanced::getLoyaltyByIDProduct($product['product']->id);
        return $loyalty->delete();
    }

    /**
     * Check if the valid loyalty code
     * @param string
     * @return boolean
     */
    private function isLoyalty($loyalty)
    {
        if (preg_match('#^[0-9]+$#is', $loyalty)) { // Type "31"
            return true;
        } elseif (preg_match('#^[x]{1}[0-9]+$#is', $loyalty)) { // Type "x32"
            return true;
        }
        return false;
    }

    ############################################################################################################
    # Hook Header
    ############################################################################################################

    public function hookHeader()
    {
     
        $ver= _PS_VERSION_;
        $finalver = explode(".", $ver);


        if (($finalver[1]==6) || ($finalver[1]==5)) {
            $CSS = $this->_path . 'views/css/totloyaltyreward15.css';
        } else {
            $CSS = $this->_path . 'views/css/totloyaltyreward16.css';
        }
        // $this->context->controller->addCSS($CSS);

    }

    ################################################################################################
    ## Override Loyalty
    ################################################################################################

    /* Hook display on customer account page */

    public function hookCustomerAccount()
    {

        $ver= _PS_VERSION_;
        $finalver = explode(".", $ver);
        if (($finalver[1]==6) || ($finalver[1]==5)) {
            return $this->display(__FILE__, 'my-account.tpl');
        } else {
            return $this->display(__FILE__, 'my-account-latest.tpl');
        }
    }

    /**
     * displayRightColumnProduct
     * @param array params
     * @return boolean
     */
    public function hookdisplayRightColumnProduct($params)
    {
        require_once(dirname(__FILE__).'/LoyaltyModuleAdvanced.php');

        $product = new Product((int)Tools::getValue('id_product'));

        $loyalty = LoyaltyAdvanced::getLoyaltyByIDProduct((int)$product->id, true);

        $points = $loyalty->loyalty;
        $regex = '#^x{1}([\d]+)$#is';

        if (Validate::isLoadedObject($product)) {

            if (Validate::isLoadedObject($params['cart'])) {

                $points_before = (int)LoyaltyModuleAdvanced::getCartNbPoints($params['cart']);
                $points_after = (int)LoyaltyModuleAdvanced::getCartNbPoints($params['cart'], $product);
                $calcul = $points_after - $points_before;
                $points = (int)$calcul;

            } else {

                if (!(int)Configuration::get('PS_LOYALTY_NONE_AWARD') && Product::isDiscounted((int)$product->id)) {

                    $points = 0;
                    $this->smarty->assign('no_pts_discounted', 1);

                } else {

                    if (!$points || preg_match($regex, $points, $multiplier)) {

                        $points = (int)LoyaltyModuleAdvanced::getNbPointsByPrice(
                            $product->getPrice(
                                Product::getTaxCalculationMethod() == PS_TAX_EXC ? false : true,
                                (int)$product->getDefaultIdProductAttribute()
                            )
                        );
                        if (isset($multiplier[1])) {
                            $points *= $multiplier[1];
                        }
                    }
                }

                $points_after = $points;
                $points_before = 0;
            }

            if ($loyalty->loyalty === 0) {
                $points = 0;
            }

            $this->smarty->assign(
                array(
                    'points'         => (int)$points,
                    'multiplier'     => isset($multiplier[1]) ? (int)$multiplier[1] : 1,
                    'total_points'   => (int)$points_after,
                    'point_rate'     => Configuration::get('PS_LOYALTY_POINT_RATE'),
                    'point_value'    => Configuration::get('PS_LOYALTY_POINT_VALUE'),
                    'points_in_cart' => (int)$points_before,
                    'voucher'        => LoyaltyModuleAdvanced::getVoucherValue((int)$points_after),
                    'none_award'     => Configuration::get('PS_LOYALTY_NONE_AWARD')
                    )
            );

            return $this->display(__FILE__, 'product.tpl');
        }

        return false;
    }

    /**
     * displayReassurance
     * @param array params
     * @return boolean
     */
    public function hookdisplayReassurance($params)
    {
        require_once(dirname(__FILE__).'/LoyaltyModuleAdvanced.php');

        $product = new Product((int)Tools::getValue('id_product'));

        $loyalty = LoyaltyAdvanced::getLoyaltyByIDProduct((int)$product->id, true);

        $points = $loyalty->loyalty;
        $regex = '#^x{1}([\d]+)$#is';

        if (Validate::isLoadedObject($product)) {

            if (Validate::isLoadedObject($params['cart'])) {

                $points_before = (int)LoyaltyModuleAdvanced::getCartNbPoints($params['cart']);
                $points_after = (int)LoyaltyModuleAdvanced::getCartNbPoints($params['cart'], $product);
                $calcul = $points_after - $points_before;
                $points = (int)$calcul;

            } else {

                if (!(int)Configuration::get('PS_LOYALTY_NONE_AWARD') && Product::isDiscounted((int)$product->id)) {

                    $points = 0;
                    $this->smarty->assign('no_pts_discounted', 1);

                } else {

                    if (!$points || preg_match($regex, $points, $multiplier)) {

                        $points = (int)LoyaltyModuleAdvanced::getNbPointsByPrice(
                            $product->getPrice(
                                Product::getTaxCalculationMethod() == PS_TAX_EXC ? false : true,
                                (int)$product->getDefaultIdProductAttribute()
                            )
                        );
                        if (isset($multiplier[1])) {
                            $points *= $multiplier[1];
                        }
                    }
                }

                $points_after = $points;
                $points_before = 0;
            }

            if ($loyalty->loyalty === 0) {
                $points = 0;
            }
            $currency = Currency::getCurrency((int)$this->context->cart->id_currency);

            $this->smarty->assign(
                array(
                    'points'         => (int)$points,
                    'multiplier'     => isset($multiplier[1]) ? (int)$multiplier[1] : 1,
                    'total_points'   => (int)$points_after,
                    'point_rate'     => Configuration::get('PS_LOYALTY_POINT_RATE'),
                    'point_value'    => Configuration::get('PS_LOYALTY_POINT_VALUE'),
                    'points_in_cart' => (int)$points_before,
                    'voucher'        => Tools::displayPrice(LoyaltyModuleAdvanced::getVoucherValue((int)$points_after), $currency),
                    'none_award'     => Configuration::get('PS_LOYALTY_NONE_AWARD')
                    )
            );

            return $this->display(__FILE__, 'product-latest.tpl');
        }

        return false;
    }

    public function hookactionOrderReturn($params)
    {
        include_once(dirname(__FILE__).'/LoyaltyStateModuleAdvanced.php');
        include_once(dirname(__FILE__).'/LoyaltyModuleAdvanced.php');

        $total_price = 0;
        $details = OrderReturn::getOrdersReturnDetail((int)$params['orderReturn']->id);
        foreach ($details as $detail) {

            $total_price += Db::getInstance()->getValue('
                SELECT ROUND(total_price_tax_incl, 2)
                FROM '._DB_PREFIX_.'order_detail od
                WHERE id_order_detail = '.(int)$detail['id_order_detail']);
        }

        $loyalty_new = new LoyaltyModuleAdvanced();
        $points = (-1) * LoyaltyModuleAdvanced::getNbPointsByPrice($total_price);
        $loyalty_new->points = (int)$points;
        $loyalty_new->id_loyalty_state = (int)LoyaltyStateModuleAdvanced::getCancelId();
        $loyalty_new->id_order = (int)$params['orderReturn']->id_order;
        $loyalty_new->id_customer = (int)$params['orderReturn']->id_customer;
        $loyalty_new->save();
    }

    /* Hook display on shopping cart summary */

    public function hookdisplayShoppingCartFooter($params)
    {
        include_once(dirname(__FILE__).'/LoyaltyModuleAdvanced.php');
        include_once(dirname(__FILE__).'/LoyaltyStateModuleAdvanced.php');

        if (Tools::getValue('btnTransform')) {
            LoyaltyModuleAdvanced::transformPoints($this->context->link->getPageLink('order'));
        }

        if (Validate::isLoadedObject($params['cart'])) {
            $currency = Currency::getCurrency((int)$this->context->cart->id_currency);
            $points = LoyaltyModuleAdvanced::getCartNbPoints($params['cart']);
            $ver= _PS_VERSION_;
            $finalver = explode(".", $ver);
            if (($finalver[1]==7)) {
                $vouch = Tools::displayPrice(LoyaltyModuleAdvanced::getVoucherValue(LoyaltyModuleAdvanced::getPointsByCustomer((int)$this->context->cart->id_customer), $currency['id_currency']));
            }
            if (($finalver[1]==5) || ($finalver[1]==6)) {
                $vouch = LoyaltyModuleAdvanced::getVoucherValue(LoyaltyModuleAdvanced::getPointsByCustomer((int)$params['delivery']->id_customer));
            }
            $vouchpoints = LoyaltyModuleAdvanced::getPointsByCustomer((int)$this->context->cart->id_customer);
            $this->smarty->assign(
                array(
                    'points'         => (int)$points,
                    'voucher'        => Tools::displayPrice(LoyaltyModuleAdvanced::getVoucherValue((int)$points), $currency),
                    'guest_checkout' => (int)Configuration::get('PS_GUEST_CHECKOUT_ENABLED'),
                    'voucherOld'     => $vouch,
                    'vouchpoints'    => $vouchpoints,
                    )
            );

        } else {
            $this->smarty->assign(array('points' => 0));
        }


        $ver= _PS_VERSION_;
        $finalver = explode(".", $ver);


        if (($finalver[1]==7)) {
            return $this->display(__FILE__, 'shopping-cart-latest.tpl');
        } else {
            return $this->display(__FILE__, 'shopping-cart.tpl');
        }
    }

    /* Hook called when a new order is created */

    public function hookactionValidateOrder($params)
    {
        include_once(dirname(__FILE__).'/LoyaltyStateModuleAdvanced.php');
        include_once(dirname(__FILE__).'/LoyaltyModuleAdvanced.php');

        if (!Validate::isLoadedObject($params['customer']) || !Validate::isLoadedObject($params['order'])) {
            die($this->l('Missing parameters'));
        }
        $loyalty = new LoyaltyModuleAdvanced();
        $loyalty->id_customer = (int)$params['customer']->id;
        $loyalty->id_order = (int)$params['order']->id;
        $loyalty->points = LoyaltyModuleAdvanced::getOrderNbPoints($params['order']);
        if (!Configuration::get('PS_LOYALTY_NONE_AWARD') && (int)$loyalty->points == 0) {
            $loyalty->id_loyalty_state = LoyaltyStateModuleAdvanced::getNoneAwardId();

        } else {
            $loyalty->id_loyalty_state = LoyaltyStateModuleAdvanced::getDefaultId();
        }
        return $loyalty->save();
    }

    /* Hook called when an order change its status */

    public function hookactionOrderStatusUpdate($params)
    {
        include_once(dirname(__FILE__).'/LoyaltyStateModuleAdvanced.php');
        include_once(dirname(__FILE__).'/LoyaltyModuleAdvanced.php');

        if (!Validate::isLoadedObject($params['newOrderStatus'])) {
            die($this->l('Missing parameters'));
        }
        $new_order = $params['newOrderStatus'];
        $order = new Order((int)$params['id_order']);
        if ($order && !Validate::isLoadedObject($order)) {
            die($this->l('Incorrect Order object.'));
        }
        $this->instanceDefaultStates();

        if ($new_order->id == $this->loyalty_state_validation->id_order_state || $new_order->id == $this->loyalty_state_cancel->id_order_state) {

            if (!Validate::isLoadedObject($loyalty = new LoyaltyModuleAdvanced(LoyaltyModuleAdvanced::getByOrderId($order->id)))) {
                return false;
            }
            if ((int)Configuration::get('PS_LOYALTY_NONE_AWARD') && $loyalty->id_loyalty_state == LoyaltyStateModuleAdvanced::getNoneAwardId()) {
                return true;
            }

            if ($new_order->id == $this->loyalty_state_validation->id_order_state) {

                $loyalty->id_loyalty_state = LoyaltyStateModuleAdvanced::getValidationId();
                if ((int)$loyalty->points < 0) {
                    $loyalty->points = abs((int)$loyalty->points);
                }
            } elseif ($new_order->id == $this->loyalty_state_cancel->id_order_state) {

                $loyalty->id_loyalty_state = LoyaltyStateModuleAdvanced::getCancelId();
                $loyalty->points = 0;
            }
            return $loyalty->save();
        }
        return true;
    }

    /* Hook display in tab AdminCustomers on BO */

    public function hookdisplayAdminCustomers($params)
    {
        include_once(dirname(__FILE__).'/LoyaltyModuleAdvanced.php');
        include_once(dirname(__FILE__).'/LoyaltyStateModuleAdvanced.php');

        $customer = new Customer((int)$params['id_customer']);
        if ($customer && !Validate::isLoadedObject($customer)) {
            die($this->l('Incorrect Customer object.'));
        }

        $details = LoyaltyModuleAdvanced::getAllByIdCustomer((int)$params['id_customer'], (int)$params['cookie']->id_lang);
        $points = (int)LoyaltyModuleAdvanced::getPointsByCustomer((int)$params['id_customer']);

        $html = '
        <br /><h2>'.sprintf($this->l('Loyalty points (%d points)'), $points).'</h2>';

        if (!$points) {
            return $html.' '.$this->l('This customer has no points');
        }

        $html .= ' <a
            href="'.$this->context->link->getAdminLink('TotLoyaltyAdvancedAdmin', true).'&addloyalty&id_customer='.Tools::getValue('id_customer').'"
            class="button"><img src="../img/admin/add.gif" alt="" /> '.$this->l('Add loyalty').'</a><br /><br />
        <table cellspacing="0" cellpadding="0" class="table">
            <tr style="background-color:#F5E9CF; padding: 0.3em 0.1em;">
                <th>'.$this->l('Order').'</th>
                <th>'.$this->l('Date').'</th>
                <th>'.$this->l('Total (without shipping)').'</th>
                <th>'.$this->l('Points').'</th>
                <th>'.$this->l('Points Status').'</th>
            </tr>';
        foreach ($details as $key => $loyalty) {

            $token = Tools::getAdminToken('AdminOrders'.(int)Tab::getIdFromClassName('AdminOrders').(int)$params['cookie']->id_employee);
            $url = 'index.php?tab=AdminOrders&id_order='.$loyalty['id'].'&vieworder&token='.$token;
            $html .= '
            <tr style="background-color: '.($key % 2 != 0 ? '#FFF6CF' : '#FFFFFF').';">
                <td>'.(
                    (int)$loyalty['id'] > 0
                        ? '<a style="color: #268CCD; font-weight: bold; text-decoration: underline;" href="'.$url.'">'.sprintf($this->l('#%d'), $loyalty['id']).'</a>'
                        : '--'
                ).'</td>
                <td>'.Tools::displayDate($loyalty['date'], (int)$params['cookie']->id_lang).'</td>
                <td>'.((int)$loyalty['id'] > 0 ? $loyalty['total_without_shipping'] : '--').'</td>
                <td>'.(int)$loyalty['points'].'</td>
                <td>'.$loyalty['state'].'</td>
            </tr>';
        }
        $html .= '
            <tr>
                <td>&nbsp;</td>
                <td colspan="2" class="bold" style="text-align: right;">'.$this->l('Total points available:').'</td>
                <td>'.$points.'</td>
                <td>'.$this->l('Voucher value:').' ';
        $html .= Tools::displayPrice(
            LoyaltyModuleAdvanced::getVoucherValue((int)$points, (int)Configuration::get('PS_CURRENCY_DEFAULT')),
            new Currency((int)Configuration::get('PS_CURRENCY_DEFAULT'))
        );
        $html .= '</td>
            </tr>
        </table>';

        return $html;
    }

    public function hookactionProductCancel($params)
    {
        include_once(dirname(__FILE__).'/LoyaltyStateModuleAdvanced.php');
        include_once(dirname(__FILE__).'/LoyaltyModuleAdvanced.php');

        if (!Validate::isLoadedObject($params['order'])
            || !Validate::isLoadedObject($order_detail = new OrderDetail((int)$params['id_order_detail']))
            || !Validate::isLoadedObject($loyalty = new LoyaltyModuleAdvanced((int)LoyaltyModuleAdvanced::getByOrderId((int)$params['order']->id)))) {
            return false;
        }

        $loyalty_new = new LoyaltyModuleAdvanced();
        $loyalty_new->points = - 1 * LoyaltyModuleAdvanced::getNbPointsByPrice(number_format($order_detail->total_price_tax_incl, 2, '.', ''));
        $loyalty_new->id_loyalty_state = (int)LoyaltyStateModuleAdvanced::getCancelId();
        $loyalty_new->id_order = (int)$params['order']->id;
        $loyalty_new->id_customer = (int)$loyalty->id_customer;
        $loyalty_new->add();

    }

    private function instanceDefaultStates()
    {
        include_once(dirname(__FILE__).'/LoyaltyStateModuleAdvanced.php');

        /* Recover default loyalty status save at module installation */
        $this->loyalty_state_default = new LoyaltyStateModuleAdvanced(LoyaltyStateModuleAdvanced::getDefaultId());
        $this->loyalty_state_validation = new LoyaltyStateModuleAdvanced(LoyaltyStateModuleAdvanced::getValidationId());
        $this->loyalty_state_cancel = new LoyaltyStateModuleAdvanced(LoyaltyStateModuleAdvanced::getCancelId());
        $this->loyalty_state_convert = new LoyaltyStateModuleAdvanced(LoyaltyStateModuleAdvanced::getConvertId());
        $this->loyalty_state_none_award = new LoyaltyStateModuleAdvanced(LoyaltyStateModuleAdvanced::getNoneAwardId());
    }
}
