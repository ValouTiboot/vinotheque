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


class TotLoyaltyAdvancedDefaultModuleFrontController extends ModuleFrontController
{

    public function __construct()
    {
        $this->auth = true;
        parent::__construct();

        $this->context = Context::getContext();

        include_once($this->module->getLocalPath().'LoyaltyModuleAdvanced.php');
        include_once($this->module->getLocalPath().'LoyaltyStateModuleAdvanced.php');

        // Declare smarty function to render pagination link
        smartyRegisterFunction(
            $this->context->smarty,
            'function',
            'summarypaginationlink',
            array('TotLoyaltyAdvancedDefaultModuleFrontController', 'getSummaryPaginationLink')
        );
    }

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        if (Tools::getValue('process') == 'transformpoints') {
            $this->processTransformPoints();
        }
        if (Tools::getValue('process') == 'transformpoints1') {
            $this->processTransformPoints1();
        }
    }

    /**
     * Transform loyalty point to a voucher
     */
    public function processTransformPoints()
    {
        LoyaltyModuleAdvanced::TransformPoints();
    }

    public function processTransformPoints1()
    {
        $ver= _PS_VERSION_;
        $finalver = explode(".", $ver);
        if (($finalver[1]==5) || ($finalver[1]==6)) {
            LoyaltyModuleAdvanced::TransformPoints('index.php?controller=order');
        }
        if (($finalver[1]==7)) {
            LoyaltyModuleAdvanced::TransformPoints('cart?action=show');
        }
    }

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        $this->display_column_left = false;
        parent::initContent();
        $this->context->controller->addJqueryPlugin(array('dimensions', 'cluetip'));

        $ver= _PS_VERSION_;
        $finalver = explode(".", $ver);

        if (($finalver[1]==5)) {
            $CSS = _MODULE_DIR_.'totloyaltyadvanced/views/css/totloyaltyrewarded_front15.css';
        } elseif (($finalver[1]==6)) {
            $CSS = _MODULE_DIR_.'totloyaltyadvanced/views/css/totloyaltyrewarded_front16.css';
            $CSS = _MODULE_DIR_.'totloyaltyadvanced/views/css/totloyaltyrewarded_custom6.css';
        } elseif (($finalver[1]==7)) {
            $CSS = _MODULE_DIR_.'totloyaltyadvanced/views/css/totloyaltyrewarded_custom7.css';
        }

        $this->context->controller->addCSS($CSS);

        if (Tools::getValue('process') == 'summary') {
            $this->assignSummaryExecution();
        }
    }

    /**
     * Render pagination link for summary
     *
     * @param (array) $params Array with to parameters p (for page number) and n (for nb of items per page)
     * @return string link
     */
    public static function getSummaryPaginationLink($params)
    {
        if (!isset($params['p'])) {
            $p = 1;

        } else {
            $p = $params['p'];
        }

        if (!isset($params['n'])) {
            $n = 10;

        } else {
            $n = $params['n'];
        }

        return Context::getContext()->link->getModuleLink(
            'totloyaltyadvanced',
            'default',
            array(
                'process' => 'summary',
                'p' => $p,
                'n' => $n,
            )
        );
    }

    /**
     * Assign summary template
     */
    public function assignSummaryExecution()
    {
        $customer_points = (int)LoyaltyModuleAdvanced::getPointsByCustomer((int)$this->context->customer->id);
        $orders = LoyaltyModuleAdvanced::getAllByIdCustomer((int)$this->context->customer->id, (int)$this->context->language->id);
        $displayorders = LoyaltyModuleAdvanced::getAllByIdCustomer(
            (int)$this->context->customer->id,
            (int)$this->context->language->id,
            false,
            true,
            ((int)Tools::getValue('n') > 0 ? (int)Tools::getValue('n') : 10),
            ((int)Tools::getValue('p') > 0 ? (int)Tools::getValue('p') : 1)
        );
        $this->context->smarty->assign(array(
            'orders' => $orders,
            'displayorders' => $displayorders,
            'totalPoints' => (int)$customer_points,
            'voucher' => Tools::displayPrice(LoyaltyModuleAdvanced::getVoucherValue($customer_points), (int)$this->context->currency->id),
            'validation_id' => LoyaltyStateModuleAdvanced::getValidationId(),
            'transformation_allowed' => $customer_points > 0,
            'page' => ((int)Tools::getValue('p') > 0 ? (int)Tools::getValue('p') : 1),
            'nbpagination' => ((int)Tools::getValue('n') > 0 ? (int)Tools::getValue('n') : 10),
            'nArray' => array(10, 20, 50),
            'max_page' => floor(count($orders) / ((int)Tools::getValue('n') > 0 ? (int)Tools::getValue('n') : 10)),
            'pagination_link' => $this->getSummaryPaginationLink(array('totloyaltyadvanced'=>'default'))
        ));

        /* Discounts */
        $nb_discounts = 0;
        $discounts = array();
        if ($ids_discount = LoyaltyModuleAdvanced::getDiscountByIdCustomer((int)$this->context->customer->id)) {

            $nb_discounts = count($ids_discount);
            foreach ($ids_discount as $key => $discount) {

                $discounts[$key] = new CartRule((int)$discount['id_cart_rule'], (int)$this->context->cookie->id_lang);
                $discounts[$key]->orders = LoyaltyModuleAdvanced::getOrdersByIdDiscount((int)$discount['id_cart_rule']);
            }
        }

        $all_categories = Category::getSimpleCategories((int)$this->context->cookie->id_lang);
        $voucher_categories = Configuration::get('PS_LOYALTY_VOUCHER_CATEGORY');
        if ($voucher_categories != '' && $voucher_categories != 0) {
            $voucher_categories = explode(',', Configuration::get('PS_LOYALTY_VOUCHER_CATEGORY'));

        } else {
            die(Tools::displayError());
        }

        if (count($voucher_categories) == count($all_categories)) {
            $categories_names = null;
        } else {

            $categories_names = array();
            foreach ($all_categories as $all_category) {
                if (in_array($all_category['id_category'], $voucher_categories)) {
                    $categories_names[$all_category['id_category']] = trim($all_category['name']);
                }
            }
            if (!empty($categories_names)) {
                $categories_names = Tools::truncate(implode(', ', $categories_names), 100).'.';
            } else {
                $categories_names = null;
            }
        }
        $this->context->smarty->assign(array(
            'nbDiscounts' => (int)$nb_discounts,
            'discounts' => $discounts,
            'minimalLoyalty' => (float)Configuration::get('PS_LOYALTY_MINIMAL'),
            'categories' => $categories_names,
            'module_dir1' => _MODULE_DIR_.'totloyaltyadvanced/',
            'temp_dir'=> Tools::getHttpHost(true)._THEME_DIR_,
            'baseDir'=>Tools::getHttpHost(true).__PS_BASE_URI__));
            $ver= _PS_VERSION_;
            $finalver = explode(".", $ver);

        if (($finalver[1]==6) || ($finalver[1]==5)) {
            $this->setTemplate('loyalty.tpl');
        } else {
            $this->setTemplate('module:totloyaltyadvanced/views/templates/front/loyalty-latest.tpl');
        }
    }
}
