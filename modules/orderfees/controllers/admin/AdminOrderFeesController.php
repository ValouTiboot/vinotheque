<?php
/**
 *  Order Fees
 *
 *  @author    motionSeed <ecommerce@motionseed.com>
 *  @copyright 2017 motionSeed. All rights reserved.
 *  @license   https://www.motionseed.com/en/license-module.html
 */

class AdminOrderFeesController extends AdminCartRulesController
{
    
    public function __construct()
    {
        parent::__construct();

        $tab = new Tab($this->id);
        $this->override_folder = $tab->module . '/' . $this->override_folder;
        
        if (!$tab->module) {
            throw new PrestaShopException('Admin tab '.get_class($this).' is not a module tab');
        }

        $this->module = Module::getInstanceByName($tab->module);
        
        // Remove unused fields
        unset($this->fields_list['code'], $this->fields_list['quantity'], $this->fields_list['date_to']);
        
        // Add Type Column
        $this->fields_list = $this->insertColumnAt($this->fields_list, 2, array(
            'type' => array(
                'title' => $this->l('Type'),
                'filter_key' => 'type',
                'type' => 'select',
                'havingFilter' => true,
                'list' => array(
                    $this->l('Option') => $this->l('Option'),
                    $this->l('Fee') => $this->l('Fee'),
                    $this->l('Discount') => $this->l('Discount')
                ),
                'filter_type' => 'string'
            )
        ));

        $this->_select = "CASE 
                            WHEN (is_fee & " . (int) $this->module->getConstant('IS_OPTION')  . ")
                                THEN '" . pSQL($this->l('Option')) . "'
                            WHEN (is_fee & " . (int) $this->module->getConstant('IS_REDUCTION') . ")
                                THEN '" . pSQL($this->l('Discount')) . "'
                            ELSE '" . pSQL($this->l('Fee')) . "'
                        END AS type";
        
        $this->_where = ' AND (is_fee & ' . (int) $this->module->getConstant('IS_FEE') . ')';
        
        array_splice($this->actions, 1, 0, 'duplicate');
        
        $this->_post = &$_POST;
    }

    public function renderForm()
    {
        $current_object = $this->loadObject(true);
        $dimension_rule_groups = $this->getDimensionRuleGroupsDisplay($current_object);
        $zipcode_rule_groups = $this->getZipcodeRuleGroupsDisplay($current_object);
        $package_rule_groups = $this->getPackageRuleGroupsDisplay($current_object);
        $id_lang = $this->context->language->id;
        $is_option = Tools::getIsset('is_option');
        
        if (Validate::isLoadedObject($current_object)) {
            $is_option = ($current_object->is_fee & $this->module->getConstant('IS_OPTION'));
        } else {
            // Set CONTEXT_ALL without (CONTEXT_ADDRESS | CONTEXT_CARRIER) to display_visible for new fee
            if (!$is_option) {
                $mask = $this->module->getConstant('CONTEXT_ADDRESS') | $this->module->getConstant('CONTEXT_CARRIER');
                
                $current_object->display_visible = $this->module->getConstant('CONTEXT_ALL');
                $current_object->display_visible &= ~$mask;
            }
        }
        
        $this->context->smarty->assign(
            array(
                'adminCartRulesToken' => Tools::getAdminTokenLite('AdminCartRules'),
                'payments' => $current_object->getAssociatedRestrictions('payment', true, false),
                'dimension_rule_groups' => $dimension_rule_groups,
                'dimension_rule_groups_counter' => count($dimension_rule_groups),
                'zipcode_rule_groups' => $zipcode_rule_groups,
                'zipcode_rule_groups_counter' => count($zipcode_rule_groups),
                'zipcode_countries_nb' => count(Country::getCountries($id_lang, true, false, false)),
                'package_rule_groups' => $package_rule_groups,
                'package_rule_groups_counter' => count($package_rule_groups),
                'module' => $this->module,
                'tax_rules_groups' => TaxRulesGroup::getTaxRulesGroups(true),
                'tax_exclude_taxe_option' => Tax::excludeTaxeOption(),
                'is_option' => $is_option
            )
        );
        
        $this->addJS($this->module->getPathUri().'views/js/admin.js');
        
        Hook::exec('actionAdminOrderFeesRenderForm', array(
            'controller' => &$this,
            'object' => &$current_object
        ));

        parent::renderForm();

        // Provide fees only on compatibility field
        $cart_rules = $this->context->smarty->getVariable('cart_rules');

        foreach ($cart_rules->value as $type => $data) {
            foreach ($data as $k => $cr) {
                if (!$cr['is_fee']) {
                    unset($cart_rules->value[$type][$k]);
                }
            }
        }

        $this->context->smarty->assign(
            array(
                'title' => array($this->l('Order Fees'), $this->l('Fee')),
                'defaultDateFrom' => $this->module->getConstant('DATE_FROM_MASK'),
                'defaultDateTo' => $this->module->getConstant('DATE_TO_MASK'),
                'cart_rules' => $this->getCartRuleCombinations($current_object, 0, 40)
            )
        );

        // For translation
        $this->loadTranslationContext();

        $this->content = $this->createTemplate('form.tpl')->fetch();
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();

        if (empty($this->display)) {
            $this->page_header_toolbar_btn['new_cart_rule'] = array(
                'href' => self::$currentIndex . '&addcart_rule&token=' . $this->token,
                'desc' => $this->l('Add new fee'),
                'icon' => 'process-icon-new'
            );
            
            $this->page_header_toolbar_btn['new_option'] = array(
                'href' => self::$currentIndex . '&addcart_rule&is_option=1&token=' . $this->token,
                'desc' => $this->l('Add new option'),
                'icon' => 'process-icon-new'
            );
        }
        
        Hook::exec('action'.$this->controller_name.'InitPageHeaderToolbar', array(
            'controller' => &$this,
            'toolbar' => &$this->page_header_toolbar_btn
        ));
    }

    public function getProductRuleGroupDisplay(
        $product_rule_group_id,
        $product_rule_group_quantity = 1,
        $product_rules = null
    ) {
        // For translation
        $this->loadTranslationContext();

        Context::getContext()->smarty->assign('product_rule_group_id', $product_rule_group_id);
        Context::getContext()->smarty->assign('product_rule_group_quantity', $product_rule_group_quantity);
        Context::getContext()->smarty->assign('product_rules', $product_rules);
        
        return $this->createTemplate('controllers/cart_rules/product_rule_group.tpl')->fetch();
    }

    public function createTemplate($tpl_name)
    {
        // Use override tpl if it exists
        // If view access is denied, we want to use the default template that will be used to display an error
        if ($this->viewAccess() && $this->override_folder) {
            if (file_exists(
                $this->module->getLocalPath() . 'views/templates/admin/orderfees/' . $tpl_name
            )) {
                return $this->context->smarty->createTemplate(
                    $this->module->getLocalPath() . 'views/templates/admin/orderfees/' . $tpl_name,
                    $this->context->smarty
                );
            } elseif (file_exists(
                $this->context->smarty->getTemplateDir(1) . DIRECTORY_SEPARATOR . $this->override_folder . $tpl_name
            )) {
                return $this->context->smarty->createTemplate(
                    $this->override_folder . $tpl_name,
                    $this->context->smarty
                );
            } elseif (file_exists(
                $this->context->smarty->getTemplateDir(0) . 'controllers' . DIRECTORY_SEPARATOR
                . $this->override_folder . $tpl_name
            )) {
                return $this->context->smarty->createTemplate(
                    'controllers' . DIRECTORY_SEPARATOR . $this->override_folder . $tpl_name,
                    $this->context->smarty
                );
            } elseif (file_exists(
                $this->context->smarty->getTemplateDir(0) . 'controllers' . DIRECTORY_SEPARATOR
                . 'cart_rules/' . $tpl_name
            )) {
                return $this->context->smarty->createTemplate(
                    'controllers' . DIRECTORY_SEPARATOR . 'cart_rules/' . $tpl_name,
                    $this->context->smarty
                );
            }
        }

        return $this->context->smarty->createTemplate(
            $this->context->smarty->getTemplateDir(0) . $tpl_name,
            $this->context->smarty
        );
    }
    
    public function processFilter()
    {
        parent::processFilter();
        
        Cache::clean('objectmodel_def_CartRule');
    }
    
    public function postProcess()
    {
        if (Tools::isSubmit('submitAddcart_rule') || Tools::isSubmit('submitAddcart_ruleAndStay')) {
            $current_object = $this->loadObject(true);
            
            Hook::exec('actionAdminOrderFeesValidateBefore', array(
                'controller' => &$this,
                'object' => &$current_object,
                'errors' => &$this->errors
            ));
            
            if (Tools::getValue('is_fee')) {
                $is_fee = (int) Tools::getValue('is_fee');
                $is_option = $is_fee & $this->module->getConstant('IS_OPTION');
                $display_visible = 0;
                $display_selectable = 0;
                
                foreach ($_POST as $key => $value) {
                    if ($is_option && strpos($key, 'option_') === 0 && (int)Tools::getValue($key) != 0) {
                        $is_fee |= $this->module->getConstant(str_replace('option_', '', $key));
                    }
                    
                    if (strpos($key, 'display_visible') === 0 && (int)Tools::getValue($key) != 0) {
                        $context_name = str_replace('display_visible_', '', $key);
                        $context_value = $this->module->getConstant($context_name);
                        
                        $display_visible |= $context_value;
                        
                        if ($is_option && (int)Tools::getValue('display_selectable_' . $context_name) != 0) {
                            $display_selectable |= $context_value;
                        }
                    }
                }
                
                // Reduction
                if (!$is_option && Tools::getValue('is_reduction')) {
                    $is_fee |= $this->module->getConstant('IS_REDUCTION');
                }
                
                // In Product Price
                if (!$is_option && Tools::getValue('in_product_price')) {
                    $is_fee |= $this->module->getConstant('IN_PRODUCT_PRICE');
                    
                    $display_visible = 0;
                    $display_selectable = 0;
                } else {
                    // Quantity per product
                    if (Tools::getValue('quantity_per_product')) {
                        $is_fee |= $this->module->getConstant('QUANTITY_PER_PRODUCT');
                    }

                    // In Shipping
                    if (!$is_option && Tools::getValue('in_shipping')) {
                        $is_fee |= $this->module->getConstant('IN_SHIPPING');

                        $display_visible = 0;
                        $display_selectable = 0;
                    }
                }
                
                // In Product Price
                if (!$is_option && Tools::getValue('in_product_price')) {
                    $is_fee |= $this->module->getConstant('IN_PRODUCT_PRICE');
                    
                    $display_visible = 0;
                    $display_selectable = 0;
                }
                
                // Free shipping
                if (!$is_option && Tools::getValue('free_shipping')) {
                    $is_fee = (int) Tools::getValue('is_fee');
                    $is_fee |= $this->module->getConstant('FREE_SHIPPING');
                    
                    $display_visible = 0;
                    $display_selectable = 0;
                    
                    $_POST['reduction_percent'] = 0;
                    $_POST['reduction_amount'] = 0;
                }
                
                $_POST['is_fee'] = $is_fee;
                $_POST['display_visible'] = (int) $display_visible;
                $_POST['display_selectable'] = (int) $display_selectable;
            }
            
            if (!Tools::getValue('payment_restriction')) {
                $_POST['payment_restriction'] = 0;
            }
            
            if (!Tools::getValue('dimension_restriction')) {
                $_POST['dimension_restriction'] = 0;
            }
            
            if (!Tools::getValue('zipcode_restriction')) {
                $_POST['zipcode_restriction'] = 0;
            }
            
            if ((int)Tools::getValue('maximum_amount') < 0) {
                $this->errors[] = $this->l('The maximum amount cannot be lower than zero.');
            }
            
            if (!Tools::getValue('package_restriction')) {
                $_POST['package_restriction'] = 0;
            } else {
                $_POST['apply_discount'] = 'shipping';

                if (is_array($rule_group_array = Tools::getValue('package_rule_group')) && count($rule_group_array)) {
                    foreach ($rule_group_array as $rule_group_id) {
                        if (!(int)Tools::getValue('package_rule_group_ratio_' . $rule_group_id)) {
                            $ratio = (float)$this->_post['package_rule_group_unit_predefined_' . $rule_group_id];

                            $this->_post['package_rule_group_ratio_' . $rule_group_id] = $ratio;
                        }
                    }
                }
            }
            
            if ((int)Tools::getValue('reduction_tax') != $this->module->getConstant('TAX_RULE')) {
                $_POST['tax_rules_group'] = 0;
            }
            
            Hook::exec('actionAdminOrderFeesValidateAfter', array(
                'controller' => &$this,
                'object' => &$current_object,
                'errors' => &$this->errors
            ));
        } elseif (Tools::getIsset('duplicate'.$this->table)) {
            if ($this->tabAccess['add'] === '1') {
                $this->action = 'duplicate';
            } else {
                $this->errors[] = Tools::displayError('You do not have permission to add this.');
            }
        }
        
        return parent::postProcess();
    }
    
    protected function afterAdd($current_object)
    {
        // Add restrictions for payment
        if (Tools::getValue('payment_restriction')
            && is_array($array = Tools::getValue('payment_select'))
            && count($array)
        ) {
            $values = array();
            
            foreach ($array as $id) {
                $values[] = '(' . (int) $current_object->id . ',' . (int) $id . ')';
            }
            
            Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'cart_rule_payment` 
                (`id_cart_rule`, `id_module`) VALUES '.implode(',', $values));
        }
        
        // Add dimension rule restrictions
        if (Tools::getValue('dimension_restriction')
            && is_array($ruleGroupArray = Tools::getValue('dimension_rule_group'))
            && count($ruleGroupArray)
        ) {
            foreach ($ruleGroupArray as $ruleGroupId) {
                Db::getInstance()->execute(
                    'INSERT INTO `'._DB_PREFIX_.'cart_rule_dimension_rule_group` (`id_cart_rule`, `base`)
                        VALUES ('.(int)$current_object->id.',
                            "'.pSQL(Tools::getValue('dimension_rule_group_base_'.$ruleGroupId)).'")'
                );
                
                $id_dimension_rule_group = Db::getInstance()->Insert_ID();

                if (is_array($ruleArray = Tools::getValue('dimension_rule_'.$ruleGroupId)) && count($ruleArray)) {
                    foreach ($ruleArray as $ruleId) {
                        Db::getInstance()->execute(
                            'INSERT INTO `'._DB_PREFIX_.'cart_rule_dimension_rule`
                            (`id_dimension_rule_group`, `type`, `operator`, `value`)
                            VALUES ('.(int)$id_dimension_rule_group.',
                            "'.pSQL(Tools::getValue('dimension_rule_'.$ruleGroupId.'_'.$ruleId.'_type'), true).'",
                            "'.pSQL(Tools::getValue('dimension_rule_'.$ruleGroupId.'_'.$ruleId.'_operator'), true).'",
                            "'.pSQL(Tools::getValue('dimension_rule_'.$ruleGroupId.'_'.$ruleId.'_value'), true).'")'
                        );
                    }
                }
            }
        }
        
        // Add zipcode rule restrictions
        if (Tools::getValue('zipcode_restriction')
            && is_array($ruleGroupArray = Tools::getValue('zipcode_rule_group'))
            && count($ruleGroupArray)
        ) {
            foreach ($ruleGroupArray as $ruleGroupId) {
                Db::getInstance()->execute(
                    'INSERT INTO `'._DB_PREFIX_.'cart_rule_zipcode_rule_group` (`id_cart_rule`)
                        VALUES ('.(int)$current_object->id.')'
                );
                
                $id_zipcode_rule_group = Db::getInstance()->Insert_ID();

                if (is_array($ruleArray = Tools::getValue('zipcode_rule_'.$ruleGroupId)) && count($ruleArray)) {
                    foreach ($ruleArray as $ruleId) {
                        Db::getInstance()->execute(
                            'INSERT INTO `'._DB_PREFIX_.'cart_rule_zipcode_rule`
                            (`id_zipcode_rule_group`, `type`, `operator`, `value`)
                            VALUES ('.(int)$id_zipcode_rule_group.',
                            "'.pSQL(Tools::getValue('zipcode_rule_'.$ruleGroupId.'_'.$ruleId.'_type'), true).'",
                            "'.pSQL(Tools::getValue('zipcode_rule_'.$ruleGroupId.'_'.$ruleId.'_operator'), true).'",
                            "'.pSQL(Tools::getValue('zipcode_rule_'.$ruleGroupId.'_'.$ruleId.'_value'), true).'")'
                        );
                    }
                }
            }
        }
        
        // Add package rule restrictions
        if (Tools::getValue('package_restriction')
            && is_array($ruleGroupArray = Tools::getValue('package_rule_group'))
            && count($ruleGroupArray)
        ) {
            foreach ($ruleGroupArray as $ruleGroupId) {
                Db::getInstance()->execute(
                    'INSERT INTO `'._DB_PREFIX_.'cart_rule_package_rule_group`
                        (`id_cart_rule`, `unit`, `unit_weight`, `ratio`)
                        VALUES ('.(int)$current_object->id.',
                            "'.pSQL(Tools::getValue('package_rule_group_unit_'.$ruleGroupId)).'",
                            "'.pSQL(Tools::getValue('package_rule_group_unit_weight_'.$ruleGroupId)).'",
                            "'.pSQL(Tools::getValue('package_rule_group_ratio_'.$ruleGroupId)).'")'
                );
                
                $id_package_rule_group = Db::getInstance()->Insert_ID();

                if (is_array($ruleArray = Tools::getValue('package_rule_'.$ruleGroupId)) && count($ruleArray)) {
                    foreach ($ruleArray as $ruleId) {
                        Db::getInstance()->execute(
                            'INSERT INTO `'._DB_PREFIX_.'cart_rule_package_rule`
                            (`id_package_rule_group`, `range_start`, `range_end`, `round`, `divider`,
                            `currency`, `tax`, `value`)
                            VALUES ('.(int)$id_package_rule_group.',
                            "'.pSQL(Tools::getValue('package_rule_'.$ruleGroupId.'_'.$ruleId.'_range_start')).'",
                            "'.pSQL(Tools::getValue('package_rule_'.$ruleGroupId.'_'.$ruleId.'_range_end')).'",
                            "'.pSQL(Tools::getValue('package_rule_'.$ruleGroupId.'_'.$ruleId.'_round')).'",
                            "'.pSQL(Tools::getValue('package_rule_'.$ruleGroupId.'_'.$ruleId.'_divider')).'",
                            "'.pSQL(Tools::getValue('package_rule_'.$ruleGroupId.'_'.$ruleId.'_currency')).'",
                            "'.pSQL(Tools::getValue('package_rule_'.$ruleGroupId.'_'.$ruleId.'_tax')).'",
                            "'.pSQL(Tools::getValue('package_rule_'.$ruleGroupId.'_'.$ruleId.'_value')).'")'
                        );
                    }
                }
            }
            
            if (Tools::getValue('is_fee')) {
                $this->_post['is_fee'] &= ~$this->module->getConstant('IN_SHIPPING');
                $this->_post['is_fee'] &= ~$this->module->getConstant('CONTEXT_ALL');
            }
        }
        
        Hook::exec('actionAdminOrderFeesAfterAdd', array(
            'controller' => &$this,
            'object' => &$current_object
        ));
        
        parent::afterAdd($current_object);
    }
    
    public function processDuplicate()
    {
        if (Validate::isLoadedObject($cart_rule = new CartRule((int)Tools::getValue('id_cart_rule')))) {
            $id_cart_rule = $cart_rule->id;
            
            unset($cart_rule->id);
            
            if (Tools::getValue('name')) {
                $name = Tools::htmlentitiesUTF8(Tools::getValue('name'));
                
                foreach (array_keys($cart_rule->name) as $id_lang) {
                    $cart_rule->name[$id_lang] = $name;
                }
            }

            if ($cart_rule->add()) {
                $tables = array(
                    'cart_rule_carrier' => array(
                        'column' => 'id_cart_rule'
                    ),
                    'cart_rule_combination' => array(
                        'column' => 'id_cart_rule_1'
                    ),
                    'cart_rule_country' => array(
                        'column' => 'id_cart_rule'
                    ),
                    'cart_rule_group' => array(
                        'column' => 'id_cart_rule'
                    ),
                    'cart_rule_shop' => array(
                        'column' => 'id_cart_rule'
                    ),
                    'cart_rule_payment' => array(
                        'column' => 'id_cart_rule'
                    ),
                    'cart_rule_product_rule_group' => array(
                        'column' => 'id_cart_rule',
                        'assoc_column' => 'id_product_rule_group',
                        'assoc_column_id' => 'id_product_rule',
                        'assoc_table' => 'cart_rule_product_rule',
                    ),
                    'cart_rule_zipcode_rule_group' => array(
                        'column' => 'id_cart_rule',
                        'assoc_column' => 'id_zipcode_rule_group',
                        'assoc_column_id' => 'id_zipcode_rule',
                        'assoc_table' => 'cart_rule_zipcode_rule',
                    ),
                    'cart_rule_dimension_rule_group' => array(
                        'column' => 'id_cart_rule',
                        'assoc_column' => 'id_dimension_rule_group',
                        'assoc_column_id' => 'id_dimension_rule',
                        'assoc_table' => 'cart_rule_dimension_rule'
                    ),
                    'cart_rule_package_rule_group' => array(
                        'column' => 'id_cart_rule',
                        'assoc_column' => 'id_package_rule_group',
                        'assoc_column_id' => 'id_package_rule',
                        'assoc_table' => 'cart_rule_package_rule'
                    )
                );
                
                foreach ($tables as $table => $params) {
                    $this->module->duplicateDBRecord(
                        $table,
                        $params['column'],
                        $id_cart_rule,
                        $cart_rule->id,
                        $params
                    );
                }
                
                Hook::exec(
                    'actionCartRuleDuplicate',
                    array('id_cart_rule_duplicated' => (int)$id_cart_rule, 'cart_rule' => &$cart_rule)
                );
                    
                $this->redirect_after = self::$currentIndex.'&conf=19&token='.$this->token;
            } else {
                $this->errors[] = Tools::displayError('An error occurred while creating an object.');
            }
        }
    }
    
    public function processDelete()
    {
        $r = parent::processDelete();
        
        if (Validate::isLoadedObject($object = $this->loadObject())) {
            $id_cart_rule = $object->id;
            
            // Payment restriction
            $r &= Db::getInstance()->delete('cart_rule_payment', '`id_cart_rule` = '.(int)$id_cart_rule);

            // Dimension restriction
            $r &= Db::getInstance()->delete('cart_rule_dimension_rule_group', '`id_cart_rule` = '.(int)$id_cart_rule);
            $r &= Db::getInstance()->delete(
                'cart_rule_dimension_rule',
                'NOT EXISTS (SELECT 1 FROM `'._DB_PREFIX_.'cart_rule_dimension_rule_group`
                    WHERE `'._DB_PREFIX_.'cart_rule_dimension_rule`.`id_dimension_rule_group`
                        = `'._DB_PREFIX_.'cart_rule_dimension_rule_group`.`id_dimension_rule_group`)'
            );

            // Zipcode restriction
            $r &= Db::getInstance()->delete('cart_rule_zipcode_rule_group', '`id_cart_rule` = '.(int)$id_cart_rule);
            $r &= Db::getInstance()->delete(
                'cart_rule_zipcode_rule',
                'NOT EXISTS (SELECT 1 FROM `'._DB_PREFIX_.'cart_rule_zipcode_rule_group`
                    WHERE `'._DB_PREFIX_.'cart_rule_zipcode_rule`.`id_zipcode_rule_group`
                        = `'._DB_PREFIX_.'cart_rule_zipcode_rule_group`.`id_zipcode_rule_group`)'
            );
            
            // Package restriction
            Db::getInstance()->delete('cart_rule_package_rule_group', '`id_cart_rule` = '.(int)$id_cart_rule);
            Db::getInstance()->delete(
                'cart_rule_package_rule',
                'NOT EXISTS (SELECT 1 FROM `'._DB_PREFIX_.'cart_rule_package_rule_group`
                    WHERE `'._DB_PREFIX_.'cart_rule_package_rule`.`id_package_rule_group`
                        = `'._DB_PREFIX_.'cart_rule_package_rule_group`.`id_package_rule_group`)'
            );
            
            Hook::exec('actionAdminOrderFeesAfterDelete', array(
                'controller' => &$this,
                'object' => &$object
            ));
        }
        
        return $r;
    }
    
    protected function afterUpdate($current_object)
    {
        $id_cart_rule = Tools::getValue('id_cart_rule');
        
        // Payment restriction
        Db::getInstance()->delete('cart_rule_payment', '`id_cart_rule` = '.(int)$id_cart_rule);
        
        // Dimension restriction
        Db::getInstance()->delete('cart_rule_dimension_rule_group', '`id_cart_rule` = '.(int)$id_cart_rule);
        Db::getInstance()->delete(
            'cart_rule_dimension_rule',
            'NOT EXISTS (SELECT 1 FROM `'._DB_PREFIX_.'cart_rule_dimension_rule_group`
                WHERE `'._DB_PREFIX_.'cart_rule_dimension_rule`.`id_dimension_rule_group`
                    = `'._DB_PREFIX_.'cart_rule_dimension_rule_group`.`id_dimension_rule_group`)'
        );
        
        // Zipcode restriction
        Db::getInstance()->delete('cart_rule_zipcode_rule_group', '`id_cart_rule` = '.(int)$id_cart_rule);
        Db::getInstance()->delete(
            'cart_rule_zipcode_rule',
            'NOT EXISTS (SELECT 1 FROM `'._DB_PREFIX_.'cart_rule_zipcode_rule_group`
                WHERE `'._DB_PREFIX_.'cart_rule_zipcode_rule`.`id_zipcode_rule_group`
                    = `'._DB_PREFIX_.'cart_rule_zipcode_rule_group`.`id_zipcode_rule_group`)'
        );
        
        // Package restriction
        Db::getInstance()->delete('cart_rule_package_rule_group', '`id_cart_rule` = '.(int)$id_cart_rule);
        Db::getInstance()->delete(
            'cart_rule_package_rule',
            'NOT EXISTS (SELECT 1 FROM `'._DB_PREFIX_.'cart_rule_package_rule_group`
                WHERE `'._DB_PREFIX_.'cart_rule_package_rule`.`id_package_rule_group`
                    = `'._DB_PREFIX_.'cart_rule_package_rule_group`.`id_package_rule_group`)'
        );
        
        Hook::exec('actionAdminOrderFeesAfterUpdate', array(
            'controller' => &$this,
            'object' => &$current_object
        ));
        
        parent::afterUpdate($current_object);
    }
    
    public function ajaxProcess()
    {
        if (Tools::isSubmit('newDimensionRule')) {
            die($this->getDimensionRuleDisplay(
                Tools::getValue('dimension_rule_group_id'),
                Tools::getValue('dimension_rule_id'),
                Tools::getValue('dimension_rule_type')
            ));
        }
        
        if (Tools::isSubmit('newDimensionRuleGroup')
            && $dimension_rule_group_id = Tools::getValue('dimension_rule_group_id')
        ) {
            die($this->getDimensionRuleGroupDisplay(
                $dimension_rule_group_id,
                Tools::getValue('dimension_rule_group_base_'.$dimension_rule_group_id, 'product')
            ));
        }
        
        if (Tools::isSubmit('newZipcodeRule')) {
            die($this->getZipcodeRuleDisplay(
                Tools::getValue('zipcode_rule_group_id'),
                Tools::getValue('zipcode_rule_id'),
                Tools::getValue('zipcode_rule_type')
            ));
        }
        
        if (Tools::isSubmit('newZipcodeRuleGroup')
            && $zipcode_rule_group_id = Tools::getValue('zipcode_rule_group_id')
        ) {
            die($this->getZipcodeRuleGroupDisplay($zipcode_rule_group_id));
        }
        
        if (Tools::isSubmit('newPackageRule')) {
            die($this->getPackageRuleDisplay(
                Tools::getValue('package_rule_group_id'),
                Tools::getValue('package_rule_id'),
                Tools::getValue('package_rule_unit_weight'),
                Tools::getValue('package_rule_range_start'),
                Tools::getValue('package_rule_range_end'),
                Tools::getValue('package_rule_round', 1),
                Tools::getValue('package_rule_divider', 1),
                Tools::getValue('package_rule_currency', ''),
                Tools::getValue('package_rule_tax', true),
                Tools::getValue('package_rule_value', '')
            ));
        }
        
        if (Tools::isSubmit('newPackageRuleGroup')
            && $package_rule_group_id = Tools::getValue('package_rule_group_id')
        ) {
            die($this->getPackageRuleGroupDisplay(
                $package_rule_group_id
            ));
        }
        
        Hook::exec('actionAdminOrderFeesAjaxProcess', array(
            'controller' => &$this
        ));

        parent::ajaxProcess();
    }
    
    public function getDimensionRuleGroupDisplay(
        $dimension_rule_group_id,
        $dimension_rule_group_base = 'product',
        $dimension_rules = null
    ) {
        // For translation
        $this->loadTranslationContext();

        Context::getContext()->smarty->assign('dimension_rule_group_id', $dimension_rule_group_id);
        Context::getContext()->smarty->assign('dimension_rule_group_base', $dimension_rule_group_base);
        Context::getContext()->smarty->assign('dimension_rules', $dimension_rules);
        
        return $this->createTemplate('dimension_rule_group.tpl')->fetch();
    }
    
    public function getDimensionRuleGroupsDisplay($cart_rule)
    {
        $dimensionRuleGroupsArray = array();
        
        if (Tools::getValue('dimension_restriction')
            && is_array($array = Tools::getValue('dimension_rule_group'))
            && count($array)
        ) {
            $i = 1;
            
            foreach ($array as $ruleGroupId) {
                $dimensionRulesArray = array();
                if (is_array($array = Tools::getValue('dimension_rule_'.$ruleGroupId)) && count($array)) {
                    foreach ($array as $ruleId) {
                        $dimensionRulesArray[] = $this->getDimensionRuleDisplay(
                            $ruleGroupId,
                            $ruleId,
                            Tools::getValue('dimension_rule_'.$ruleGroupId.'_'.$ruleId.'_type'),
                            Tools::getValue('dimension_rule_'.$ruleGroupId.'_'.$ruleId)
                        );
                    }
                }

                $dimensionRuleGroupsArray[] = $this->getDimensionRuleGroupDisplay(
                    $i++,
                    Tools::getValue('dimension_rule_group_base_'.$ruleGroupId),
                    $dimensionRulesArray
                );
            }
        } else {
            $i = 1;
            foreach ($this->module->getDimensionRuleGroups($cart_rule) as $dimensionRuleGroup) {
                $j = 1;
                $dimensionRulesDisplay = array();
                
                foreach ($dimensionRuleGroup['dimension_rules'] as $dimensionRule) {
                    $dimensionRulesDisplay[] = $this->getDimensionRuleDisplay(
                        $i,
                        $j++,
                        $dimensionRule['type'],
                        $dimensionRule['operator'],
                        $dimensionRule['value']
                    );
                }
                
                $dimensionRuleGroupsArray[] = $this->getDimensionRuleGroupDisplay(
                    $i++,
                    $dimensionRuleGroup['base'],
                    $dimensionRulesDisplay
                );
            }
        }
        return $dimensionRuleGroupsArray;
    }
    
    public function getDimensionRuleDisplay(
        $dimension_rule_group_id,
        $dimension_rule_id,
        $dimension_rule_type,
        $dimension_rule_operator = '=',
        $dimension_rule_value = ''
    ) {
        // For translation
        $this->loadTranslationContext();


        $this->context->smarty->assign(
            array(
                'dimension_rule_group_id' => (int)$dimension_rule_group_id,
                'dimension_rule_id' => (int)$dimension_rule_id,
                'dimension_rule_type' => $dimension_rule_type,
                'id_lang' => (int)$this->context->language->id,
                'operator' => $dimension_rule_operator,
                'value' => $dimension_rule_value,
                'ps_dimension_unit' => Configuration::get('PS_DIMENSION_UNIT'),
                'ps_weight_unit' => Configuration::get('PS_WEIGHT_UNIT')
            )
        );

        if (Tools::getValue('dimension_restriction')) {
            $this->context->smarty->assign(
                array(
                    'operator' => Tools::getValue(
                        'dimension_rule_'.$dimension_rule_group_id.'_'.$dimension_rule_id.'_operator'
                    ),
                    'value' => Tools::getValue(
                        'dimension_rule_'.$dimension_rule_group_id.'_'.$dimension_rule_id.'_value'
                    )
                )
            );
        }

        return $this->createTemplate('dimension_rule.tpl')->fetch();
    }
    
    public function getZipcodeRuleGroupDisplay(
        $zipcode_rule_group_id,
        $zipcode_rules = null
    ) {
        // For translation
        $this->loadTranslationContext();

        Context::getContext()->smarty->assign('zipcode_rule_group_id', $zipcode_rule_group_id);
        Context::getContext()->smarty->assign(
            'zipcode_countries',
            Country::getCountries($this->context->language->id, true, false, false)
        );
        Context::getContext()->smarty->assign('zipcode_rules', $zipcode_rules);
        
        return $this->createTemplate('zipcode_rule_group.tpl')->fetch();
    }
    
    public function getZipcodeRuleGroupsDisplay($cart_rule)
    {
        $zipcodeRuleGroupsArray = array();
        
        if (Tools::getValue('zipcode_restriction')
            && is_array($array = Tools::getValue('zipcode_rule_group'))
            && count($array)
        ) {
            $i = 1;
            
            foreach ($array as $ruleGroupId) {
                $zipcodeRulesArray = array();
                if (is_array($array = Tools::getValue('zipcode_rule_'.$ruleGroupId)) && count($array)) {
                    foreach ($array as $ruleId) {
                        $zipcodeRulesArray[] = $this->getZipcodeRuleDisplay(
                            $ruleGroupId,
                            $ruleId,
                            Tools::getValue('zipcode_rule_'.$ruleGroupId.'_'.$ruleId.'_type'),
                            Tools::getValue('zipcode_rule_'.$ruleGroupId.'_'.$ruleId)
                        );
                    }
                }

                $zipcodeRuleGroupsArray[] = $this->getZipcodeRuleGroupDisplay(
                    $i++,
                    $zipcodeRulesArray
                );
            }
        } else {
            $i = 1;
            foreach ($this->module->getZipcodeRuleGroups($cart_rule) as $zipcodeRuleGroup) {
                $j = 1;
                $zipcodeRulesDisplay = array();
                
                foreach ($zipcodeRuleGroup['zipcode_rules'] as $zipcodeRule) {
                    $zipcodeRulesDisplay[] = $this->getZipcodeRuleDisplay(
                        $i,
                        $j++,
                        $zipcodeRule['type'],
                        $zipcodeRule['operator'],
                        $zipcodeRule['value']
                    );
                }
                
                $zipcodeRuleGroupsArray[] = $this->getZipcodeRuleGroupDisplay(
                    $i++,
                    $zipcodeRulesDisplay
                );
            }
        }
        return $zipcodeRuleGroupsArray;
    }
    
    public function getZipcodeRuleDisplay(
        $zipcode_rule_group_id,
        $zipcode_rule_id,
        $zipcode_rule_type,
        $zipcode_rule_operator = '=',
        $zipcode_rule_value = ''
    ) {
        // For translation
        $this->loadTranslationContext();


        $this->context->smarty->assign(
            array(
                'zipcode_rule_group_id' => (int)$zipcode_rule_group_id,
                'zipcode_rule_id' => (int)$zipcode_rule_id,
                'zipcode_rule_type' => $zipcode_rule_type,
                'id_lang' => (int)$this->context->language->id,
                'operator' => $zipcode_rule_operator,
                'value' => $zipcode_rule_value
            )
        );

        if (Tools::getValue('zipcode_restriction')) {
            $this->context->smarty->assign(
                array(
                    'operator' => Tools::getValue(
                        'zipcode_rule_'.$zipcode_rule_group_id.'_'.$zipcode_rule_id.'_operator'
                    ),
                    'value' => Tools::getValue(
                        'zipcode_rule_'.$zipcode_rule_group_id.'_'.$zipcode_rule_id.'_value'
                    )
                )
            );
        }

        return $this->createTemplate('zipcode_rule.tpl')->fetch();
    }
    
    public function getPackageRuleGroupDisplay(
        $package_rule_group_id,
        $package_rule_group_unit = 'kg/m3',
        $package_rule_group_unit_weight = 'kg',
        $package_rule_group_ratio = '',
        $package_rules = null
    ) {
        $this->context->smarty->assign('package_rule_group_id', $package_rule_group_id);
        $this->context->smarty->assign('package_rule_group_unit', $package_rule_group_unit);
        $this->context->smarty->assign('package_rule_group_unit_weight', $package_rule_group_unit_weight);
        $this->context->smarty->assign('package_rule_group_ratio', $package_rule_group_ratio);
        $this->context->smarty->assign('package_rules', $package_rules);
        
        return $this->createTemplate('package_rule_group.tpl')->fetch();
    }
    
    public function getPackageRuleGroupsDisplay($cart_rule)
    {
        $packageRuleGroupsArray = array();
        
        if (Tools::getValue('package_restriction')
            && is_array($array = Tools::getValue('package_rule_group'))
            && count($array)
        ) {
            $i = 1;
            
            foreach ($array as $ruleGroupId) {
                $packageRulesArray = array();
                if (is_array($array = Tools::getValue('package_rule_'.$ruleGroupId)) && count($array)) {
                    foreach ($array as $ruleId) {
                        $packageRulesArray[] = $this->getPackageRuleDisplay(
                            $ruleGroupId,
                            $ruleId,
                            Tools::getValue('package_rule_'.$ruleGroupId.'_'.$ruleId.'_unit_weight'),
                            Tools::getValue('package_rule_'.$ruleGroupId.'_'.$ruleId.'_range_start'),
                            Tools::getValue('package_rule_'.$ruleGroupId.'_'.$ruleId.'_range_end'),
                            Tools::getValue('package_rule_'.$ruleGroupId.'_'.$ruleId.'_round'),
                            Tools::getValue('package_rule_'.$ruleGroupId.'_'.$ruleId.'_divider'),
                            Tools::getValue('package_rule_'.$ruleGroupId.'_'.$ruleId.'_currency'),
                            Tools::getValue('package_rule_'.$ruleGroupId.'_'.$ruleId.'_tax'),
                            Tools::getValue('package_rule_'.$ruleGroupId.'_'.$ruleId.'_value')
                        );
                    }
                }

                $packageRuleGroupsArray[] = $this->getPackageRuleGroupDisplay(
                    $i++,
                    Tools::getValue('package_rule_group_unit_'.$ruleGroupId),
                    Tools::getValue('package_rule_group_unit_weight_'.$ruleGroupId),
                    Tools::getValue('package_rule_group_ratio_'.$ruleGroupId),
                    $packageRulesArray
                );
            }
        } else {
            $i = 1;
            foreach ($this->module->getPackageRuleGroups($cart_rule) as $packageRuleGroup) {
                $j = 1;
                $packageRulesDisplay = array();
                
                foreach ($packageRuleGroup['package_rules'] as $packageRule) {
                    $packageRulesDisplay[] = $this->getPackageRuleDisplay(
                        $i,
                        $j++,
                        $packageRuleGroup['unit_weight'],
                        $packageRule['range_start'],
                        $packageRule['range_end'],
                        $packageRule['round'],
                        $packageRule['divider'],
                        $packageRule['currency'],
                        $packageRule['tax'],
                        $packageRule['value']
                    );
                }
                
                $packageRuleGroupsArray[] = $this->getPackageRuleGroupDisplay(
                    $i++,
                    $packageRuleGroup['unit'],
                    $packageRuleGroup['unit_weight'],
                    $packageRuleGroup['ratio'],
                    $packageRulesDisplay
                );
            }
        }
        return $packageRuleGroupsArray;
    }
    
    public function getPackageRuleDisplay(
        $package_rule_group_id,
        $package_rule_id,
        $package_rule_unit_weight,
        $package_rule_range_start,
        $package_rule_range_end,
        $package_rule_round = 1,
        $package_rule_divider = 1,
        $package_rule_currency = '',
        $package_rule_tax = true,
        $package_rule_value = ''
    ) {
        (bool)$package_rule_currency;
        
        $this->context->smarty->assign(
            array(
                'package_rule_group_id' => (int)$package_rule_group_id,
                'package_rule_id' => (int)$package_rule_id,
                'unit_weight' => $package_rule_unit_weight,
                'range_start' => $package_rule_range_start,
                'range_end' => $package_rule_range_end,
                'round' => $package_rule_round,
                'divider' => $package_rule_divider,
                'id_lang' => (int)$this->context->language->id,
                'selected_currency' => $this->module->getActualCurrency(),
                'tax' => $package_rule_tax,
                'value' => $package_rule_value
            )
        );

        if (Tools::getValue('package_restriction')) {
            $this->context->smarty->assign(
                array(
                    'round' => Tools::getValue(
                        'package_rule_'.$package_rule_group_id.'_'.$package_rule_id.'_round'
                    ),
                    'divided' => Tools::getValue(
                        'package_rule_'.$package_rule_group_id.'_'.$package_rule_id.'_divider'
                    ),
                    'currency' => Tools::getValue(
                        'package_rule_'.$package_rule_group_id.'_'.$package_rule_id.'_currency'
                    ),
                    'tax' => Tools::getValue(
                        'package_rule_'.$package_rule_group_id.'_'.$package_rule_id.'_tax'
                    ),
                    'value' => Tools::getValue(
                        'package_rule_'.$package_rule_group_id.'_'.$package_rule_id.'_value'
                    )
                )
            );
        }
        
        return $this->createTemplate('package_rule.tpl')->fetch();
    }
    
    public function getProductRuleDisplay(
        $product_rule_group_id,
        $product_rule_id,
        $product_rule_type,
        $selected = array()
    ) {
        $this->loadTranslationContext();
        
        Context::getContext()->smarty->assign(
            array(
                'product_rule_group_id' => (int)$product_rule_group_id,
                'product_rule_id' => (int)$product_rule_id,
                'product_rule_type' => $product_rule_type,
            )
        );

        switch ($product_rule_type) {
            case 'attributes':
                $attributes = array('selected' => array(), 'unselected' => array());
                $results = Db::getInstance()->executeS('
				SELECT CONCAT(agl.name, " - ", al.name) as name, a.id_attribute as id
				FROM '._DB_PREFIX_.'attribute_group_lang agl
				LEFT JOIN '._DB_PREFIX_.'attribute a ON a.id_attribute_group = agl.id_attribute_group
				LEFT JOIN '._DB_PREFIX_.'attribute_lang al
                    ON (a.id_attribute = al.id_attribute AND al.id_lang = '.(int)Context::getContext()->language->id.')
				WHERE agl.id_lang = '.(int)Context::getContext()->language->id.'
				ORDER BY agl.name, al.name');
                
                foreach ($results as $row) {
                    $attributes[in_array($row['id'], $selected) ? 'selected' : 'unselected'][] = $row;
                }
                
                Context::getContext()->smarty->assign('product_rule_itemlist', $attributes);
                $choose_content = $this->createTemplate('controllers/cart_rules/product_rule_itemlist.tpl')->fetch();
                Context::getContext()->smarty->assign('product_rule_choose_content', $choose_content);
                
                break;
            case 'products':
                $display_sku = (bool)Configuration::get('MS_ORDERFEES_CONDITIONS_DISPLAY_SKU');
                $products = array('selected' => array(), 'unselected' => array());
                $results = Db::getInstance()->executeS('
				SELECT DISTINCT ' . ($display_sku ? 'CONCAT(p.reference, " - ", name) AS name' : 'name') . ',
                    p.id_product as id
				FROM '._DB_PREFIX_.'product p
				LEFT JOIN `'._DB_PREFIX_.'product_lang` pl
					ON (p.`id_product` = pl.`id_product`
					AND pl.`id_lang` = '.(int)Context::getContext()->language->id.Shop::addSqlRestrictionOnLang('pl').')
				'.Shop::addSqlAssociation('product', 'p').'
				WHERE id_lang = '.(int)Context::getContext()->language->id.'
				ORDER BY name');
                
                foreach ($results as $row) {
                    $products[in_array($row['id'], $selected) ? 'selected' : 'unselected'][] = $row;
                }
                
                Context::getContext()->smarty->assign('product_rule_itemlist', $products);
                $choose_content = $this->createTemplate('product_rule_itemlist.tpl')->fetch();
                Context::getContext()->smarty->assign('product_rule_choose_content', $choose_content);
                
                break;
            case 'manufacturers':
                $products = array('selected' => array(), 'unselected' => array());
                $results = Db::getInstance()->executeS('
				SELECT name, id_manufacturer as id
				FROM '._DB_PREFIX_.'manufacturer
				ORDER BY name');
                
                foreach ($results as $row) {
                    $products[in_array($row['id'], $selected) ? 'selected' : 'unselected'][] = $row;
                }
                
                Context::getContext()->smarty->assign('product_rule_itemlist', $products);
                $choose_content = $this->createTemplate('product_rule_itemlist.tpl')->fetch();
                Context::getContext()->smarty->assign('product_rule_choose_content', $choose_content);
                
                break;
            case 'suppliers':
                $products = array('selected' => array(), 'unselected' => array());
                $results = Db::getInstance()->executeS('
				SELECT name, id_supplier as id
				FROM '._DB_PREFIX_.'supplier
				ORDER BY name');
                
                foreach ($results as $row) {
                    $products[in_array($row['id'], $selected) ? 'selected' : 'unselected'][] = $row;
                }
                
                Context::getContext()->smarty->assign('product_rule_itemlist', $products);
                $choose_content = $this->createTemplate('product_rule_itemlist.tpl')->fetch();
                Context::getContext()->smarty->assign('product_rule_choose_content', $choose_content);
                
                break;
            case 'categories':
                $categories = array('selected' => array(), 'unselected' => array());
                $results = Db::getInstance()->executeS('
				SELECT DISTINCT name, c.id_category as id
				FROM '._DB_PREFIX_.'category c
				LEFT JOIN `'._DB_PREFIX_.'category_lang` cl
					ON (c.`id_category` = cl.`id_category`
					AND cl.`id_lang` = '.(int)Context::getContext()->language->id.Shop::addSqlRestrictionOnLang('cl').')
				'.Shop::addSqlAssociation('category', 'c').'
				WHERE id_lang = '.(int)Context::getContext()->language->id.'
				ORDER BY name');
                
                foreach ($results as $row) {
                    $categories[in_array($row['id'], $selected) ? 'selected' : 'unselected'][] = $row;
                }
                
                Context::getContext()->smarty->assign('product_rule_itemlist', $categories);
                $choose_content = $this->createTemplate('product_rule_itemlist.tpl')->fetch();
                Context::getContext()->smarty->assign('product_rule_choose_content', $choose_content);
                
                break;
            default:
                Context::getContext()->smarty->assign(
                    'product_rule_itemlist',
                    array('selected' => array(), 'unselected' => array())
                );
                
                Context::getContext()->smarty->assign('product_rule_choose_content', '');
        }

        return $this->createTemplate('product_rule.tpl')->fetch();
    }
    
    public function ajaxProcessLoadCartRules()
    {
        $type = $token = $search = '';
        $limit = $count = $id_cart_rule = 0;
        if (Tools::getIsset('limit')) {
            $limit = Tools::getValue('limit');
        }

        if (Tools::getIsset('type')) {
            $type = Tools::getValue('type');
        }

        if (Tools::getIsset('count')) {
            $count = Tools::getValue('count');
        }

        if (Tools::getIsset('id_cart_rule')) {
            $id_cart_rule = Tools::getValue('id_cart_rule');
        }

        if (Tools::getIsset('search')) {
            $search = Tools::getValue('search');
        }

        $page = floor($count / $limit);

        $html = '';
        $next_link = '';

        if (($page * $limit) + 1 == $count || $count == 0) {
            if ($count == 0) {
                $count = 1;
            }

            $current_object = $this->loadObject(true);
            $cart_rules = $this->getCartRuleCombinations($current_object, ($page)*$limit, $limit, $search);

            if ($type == 'selected') {
                $i = 1;
                foreach ($cart_rules['selected'] as $cart_rule) {
                    $html .= '<option value="'.(int)$cart_rule['id_cart_rule'].'">&nbsp;'.Tools::safeOutput($cart_rule['name']).'</option>';
                    if ($i == $limit) {
                        break;
                    }
                    $i++;
                }
                if ($i == $limit) {
                    $next_link = Context::getContext()->link->getAdminLink('AdminCartRules').'&ajaxMode=1&ajax=1&id_cart_rule='.(int)$id_cart_rule.'&action=loadCartRules&limit='.(int)$limit.'&type=selected&count='.($count - 1 + count($cart_rules['selected']).'&search='.urlencode($search));
                }
            } else {
                $i = 1;
                foreach ($cart_rules['unselected'] as $cart_rule) {
                    $html .= '<option value="'.(int)$cart_rule['id_cart_rule'].'">&nbsp;'.Tools::safeOutput($cart_rule['name']).'</option>';
                    if ($i == $limit) {
                        break;
                    }
                    $i++;
                }
                if ($i == $limit) {
                    $next_link = Context::getContext()->link->getAdminLink('AdminCartRules').'&ajaxMode=1&ajax=1&id_cart_rule='.(int)$id_cart_rule.'&action=loadCartRules&limit='.(int)$limit.'&type=unselected&count='.($count - 1 + count($cart_rules['unselected']).'&search='.urlencode($search));
                }
            }
        }
        
        echo Tools::jsonEncode(array('html' => $html, 'next_link' => $next_link));
    }
    
    protected function getCartRuleCombinations($object, $offset = null, $limit = null, $search = '')
    {
        $array = array();
        if ($offset !== null && $limit !== null) {
            $sql_limit = ' LIMIT '.(int)$offset.', '.(int)($limit+1);
        } else {
            $sql_limit = '';
        }

        $array['selected'] = Db::getInstance()->executeS('
		SELECT cr.*, crl.*, 1 as selected
		FROM '._DB_PREFIX_.'cart_rule cr
		LEFT JOIN '._DB_PREFIX_.'cart_rule_lang crl ON (cr.id_cart_rule = crl.id_cart_rule AND crl.id_lang = '.(int)Context::getContext()->language->id.')
		WHERE cr.is_fee > 0 AND cr.id_cart_rule != '.(int)$object->id.($search ? ' AND crl.name LIKE "%'.pSQL($search).'%"' : '').'
		AND (
			cr.cart_rule_restriction = 0
			OR EXISTS (
				SELECT 1
				FROM '._DB_PREFIX_.'cart_rule_combination
				WHERE cr.id_cart_rule = '._DB_PREFIX_.'cart_rule_combination.id_cart_rule_1 AND '.(int)$object->id.' = id_cart_rule_2
			)
			OR EXISTS (
				SELECT 1
				FROM '._DB_PREFIX_.'cart_rule_combination
				WHERE cr.id_cart_rule = '._DB_PREFIX_.'cart_rule_combination.id_cart_rule_2 AND '.(int)$object->id.' = id_cart_rule_1
			)
		) ORDER BY cr.id_cart_rule'.$sql_limit);

        $array['unselected'] = Db::getInstance()->executeS('
		SELECT cr.*, crl.*, 1 as selected
		FROM '._DB_PREFIX_.'cart_rule cr
		INNER JOIN '._DB_PREFIX_.'cart_rule_lang crl ON (cr.id_cart_rule = crl.id_cart_rule AND crl.id_lang = '.(int)Context::getContext()->language->id.')
		LEFT JOIN '._DB_PREFIX_.'cart_rule_combination crc1 ON (cr.id_cart_rule = crc1.id_cart_rule_1 AND crc1.id_cart_rule_2 = '.(int)$object->id.')
		LEFT JOIN '._DB_PREFIX_.'cart_rule_combination crc2 ON (cr.id_cart_rule = crc2.id_cart_rule_2 AND crc2.id_cart_rule_1 = '.(int)$object->id.')
		WHERE cr.is_fee > 0 AND cr.cart_rule_restriction = 1
		AND cr.id_cart_rule != '.(int)$object->id.($search ? ' AND crl.name LIKE "%'.pSQL($search).'%"' : '').'
		AND crc1.id_cart_rule_1 IS NULL
		AND crc2.id_cart_rule_1 IS NULL  ORDER BY cr.id_cart_rule'.$sql_limit);
        return $array;
    }
    
    public function displayDuplicateLink($token = null, $id = null, $name = null)
    {
        (bool)$name;
        
        $tpl = $this->createTemplate('list_action_duplicate.tpl');
        
        if (!array_key_exists('Bad SQL query', self::$cache_lang)) {
            self::$cache_lang['Duplicate'] = $this->l('Duplicate', 'Helper');
        }

        $duplicate = self::$currentIndex.'&'.$this->identifier.'='.$id.'&duplicate'.$this->table;

        $tpl->assign(array(
            'href' => self::$currentIndex.'&'.$this->identifier.'='.$id
                    .'&view'.$this->table.'&token='.($token != null ? $token : $this->token),
            'action' => self::$cache_lang['Duplicate'],
            'location_ok' => $duplicate.'&token='.($token != null ? $token : $this->token)
        ));

        return $tpl->fetch();
    }
    
    public function getCurrentDisplay()
    {
        return $this->display;
    }
    
    protected function l($string, $class = 'AdminCartRules', $addslashes = false, $htmlentities = true)
    {
        return parent::l($string, $class, $addslashes, $htmlentities);
    }

    protected function loadTranslationContext()
    {
        $ctx = Context::getContext();
        $ctx->controller = MotionSeedModule::cast('AdminCartRulesController', $ctx->controller);
    }
    
    protected function insertColumnAt($fields, $position, $column)
    {
        return array_slice($fields, 0, $position, true) + $column + array_slice($fields, $position, null, true);
    }
}
