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

require_once _PS_MODULE_DIR_.'totloyaltyadvanced/LoyaltyStateModuleAdvanced.php';
require_once _PS_MODULE_DIR_.'totloyaltyadvanced/models/LoyaltyAdvanced.php';
require_once _PS_MODULE_DIR_.'totloyaltyadvanced//classes/TotLoyalty.php';

class TotLoyaltyAdvancedAdminController extends ModuleAdminController
{

    public function __construct()
    {
        $this->bootstrap = true;
        // add my css for cient
        $this->context = Context::getContext();

        $this->className = 'TotLoyalty';

        parent::__construct();
        $this->identifier = 'id_loyalty';
        $this->context->controller->addCSS(_PS_MODULE_DIR_.'totloyaltyadvanced/views/css/totloyaltyrewarded_client_back.css');
        $this->context->controller->addCSS(_PS_MODULE_DIR_.'totloyaltyadvanced/views/css/banner.css');

        $this->addRowAction('view');
        $this->show_toolbar_options = true;
        $this->table = 'totloyalty';
        $this->_select = '
            c.`firstname`,
            c.`lastname`,
            SUM(IF(a.`id_loyalty_state` = '.(int)LoyaltyStateModuleAdvanced::getValidationId().', a.points, 0)) AS conf,
            SUM(IF(a.`id_loyalty_state` = '.(int)LoyaltyStateModuleAdvanced::getDefaultId().', a.points, 0)) AS wait,
            gl.`name` AS civilite,
            c.`email`
        ';
        $this->_join = '
            INNER JOIN `'._DB_PREFIX_.'customer` AS c
                    ON c.`id_customer` = a.`id_customer`
            LEFT OUTER JOIN `'._DB_PREFIX_.'orders` AS o
                    ON o.`id_order` = a.`id_order` '.Shop::addSqlRestriction(false, 'o').'
            LEFT OUTER JOIN `'._DB_PREFIX_.'gender_lang` AS gl
                    ON gl.`id_gender` = c.`id_gender` AND gl.`id_lang` = "'.$this->context->cookie->id_lang.'" ';
        $this->_group = 'GROUP BY c.`id_customer`';
        $this->_defaultOrderBy = 'conf';
        $this->_defaultOrderWay = 'DESC';
        $this->fields_list['civilite'] = array(
            'title' => $this->module->l('Gender', 'TotLoyaltyAdvancedAdmin'),
            'align' => 'center',
        );
        $this->fields_list['firstname'] = array(
            'title' => $this->module->l('Firstname', 'TotLoyaltyAdvancedAdmin'),
            'align' => 'center',
        );
        $this->fields_list['lastname'] = array(
            'title' => $this->module->l('Lastname', 'TotLoyaltyAdvancedAdmin'),
            'align' => 'center',
        );
        $this->fields_list['email'] = array(
            'title' => $this->module->l('Email', 'TotLoyaltyAdvancedAdmin'),
            'align' => 'center',
        );
        $this->fields_list['conf'] = array(
            'title' => $this->module->l('confirmed', 'TotLoyaltyAdvancedAdmin'),
            'align' => 'center',
        );
        $this->fields_list['wait'] = array(
            'title' => $this->module->l('pending', 'TotLoyaltyAdvancedAdmin'),
            'align' => 'center',
        );
    }

    public function renderView()
    {
        $this->context->smarty->assign('loyalties', $this->getLoyaltiesById(Tools::getValue('id_loyalty')));
        $this->toolbar_btn['new'] = array(
            'href' => $this->context->link->getAdminLink('TotLoyaltyAdvancedAdmin', true).'&addloyalty&id_customer='.$this->object->id_customer,
            'desc' => $this->module->l('Add new', 'TotLoyaltyAdvancedAdmin')
        );
        $html = parent::renderView();
        $l = new Link();
        $this->context->smarty->assign('parent', $html);
        $this->context->smarty->assign('linkOrder', $l->getAdminLink('AdminOrders'));
        $this->context->smarty->assign('linkBack', $l->getAdminLink('TotLoyaltyAdvancedAdmin'));
        $this->context->smarty->assign('lvl_valid', LoyaltyStateModuleAdvanced::getValidationId());

        $this->template = 'Admin.tpl';
    }

    public function renderForm()
    {
        if (empty($this->toolbar_title)) {
            $this->initToolbarTitle();
        }

        $helper = new HelperForm();
        $this->setHelperDisplay($helper);

        $valid = LoyaltyStateModuleAdvanced::getValidationId();
        $this->fields_form = array(
            'legend' => array(
                'title' => $this->module->l('Add loyalty', 'TotLoyaltyAdvancedAdmin'),
                'image' => '/modules/totloyaltyadvanced/logo.gif'
            ),
            'submit' => array(
                'name' => 'subtmitAddLoyalty',
                'title' => $this->module->l('Save', 'TotLoyaltyAdvancedAdmin'),
                'class' => (version_compare(_PS_VERSION_, '1.6', '<') ? 'button' : 'btn btn-default pull-right')
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Customer:', 'TotLoyaltyAdvancedAdmin'),
                    'name' => 'id_customer',
                    'required' => true,
                    'options' => array (
                            'query' => $this->getCustomers(),
                            'id' => 'id_customer',
                            'name' => 'names'
                    )
                ),
                array(
                    'type' => 'text',
                    'label' => $this->module->l('Points:', 'TotLoyaltyAdvancedAdmin'),
                    'name' => 'points',
                    'required' => true,
                    'desc' => $this->module->l('You can only use numeric chars, exemple : -17 or 23', 'TotLoyaltyAdvancedAdmin')
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'id_loyalty_state',
                    'required' => true,
                    'default_value' => $valid
                )
            )
        );
        return parent::renderForm();
    }

    private function getLoyaltiesById($id)
    {
        $sql = '
            SELECT *, gl.name AS gender, l.`date_upd`, lsl.`name` AS lang_state
            FROM `'._DB_PREFIX_.'totloyalty` AS l
            INNER JOIN `'._DB_PREFIX_.'customer` AS c
                    ON c.`id_customer` = l.`id_customer`
            INNER JOIN`'._DB_PREFIX_.'totloyalty_state_lang` AS lsl
                    ON lsl.`id_loyalty_state` = l.`id_loyalty_state` AND lsl.`id_lang` = \''.$this->context->cookie->id_lang.'\'
            LEFT OUTER JOIN `'._DB_PREFIX_.'orders` AS o
                    ON o.`id_order` = l.`id_order` '.Shop::addSqlRestriction(false, 'o').'
            LEFT OUTER JOIN `'._DB_PREFIX_.'gender_lang` AS gl
                    ON gl.`id_gender` = c.`id_gender` AND gl.`id_lang` = \''.$this->context->cookie->id_lang.'\'
            WHERE l.`id_customer` = (SELECT `id_customer` FROM `'._DB_PREFIX_.'totloyalty` WHERE `id_loyalty` = '.(int)$id.') ORDER BY l.`date_upd` DESC ';
        $results = DB::getInstance()->executeS($sql);
        foreach ($results as $custom) {

            $this->id_customer = $custom['id_customer'];
            $this->context->smarty->assign('customer', $custom['firstname'].' '.$custom['lastname']);
            $this->context->smarty->assign('gender', isset($custom['gender']) ? '' : '');
        }
        return $results;
    }

    private function getCustomers()
    {
        $temp = array();
        $customers = Customer::getCustomers();
        foreach ($customers as $key => $value) {

            $temp[$key]['names'] = $value['firstname'].' '.$value['lastname'];
            $temp[$key]['id_customer'] = $value['id_customer'];
        }
        return $temp;
    }
}
