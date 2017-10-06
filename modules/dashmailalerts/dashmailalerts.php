<?php
/**
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
*   @author    PrestaShop SA <contact@prestashop.com>
*   @copyright 2007-2016 PrestaShop SA
*   @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*   International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_CAN_LOAD_FILES_')) {
    exit;
}

class DashMailAlerts extends Module
{
    public function __construct()
    {
        $this->name = 'dashmailalerts';
        if (version_compare(_PS_VERSION_, '1.6.0.1', '>=')) {
            $this->tab = 'dashboard';
        } else {
            $this->tab = 'administration';
        }
        $this->version = '1.3.2';
        $this->author = 'PrestaEdit';
        $this->need_instance = 0;
        $this->ps_versions_compliancy['min'] = '1.5.0.1';

        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $this->dependencies = array('ps_emailalerts');
        } else {
            $this->dependencies = array('mailalerts');
        }
        $this->module_key = 'b6fff822dfc04d3530c71acc0168a655';

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Clients and Notification when out-of-stocks products');
        $this->description = $this->l('Get the customers who wants to receiving a notification when an out-of-stock product is available again.');
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        if (version_compare(_PS_VERSION_, '1.6.0.1', '>=')) {
            if (!$this->registerHook('dashboardZoneTwo')    || !$this->registerHook('dashboardData')) {
                return false;
            }
        } else {
            if (!$this->registerHook('displayAdminHomeStatistics')) {
                return false;
            }
        }

        return true;
    }

    public function hookDisplayAdminHomeStatistics()
    {
        $content = '<div id="table_info_large">
            <table cellpadding="0" cellspacing="0" id="table_customer" style="width:100%;">
                <thead>
                    <tr>
                        <th class="order_id"><span class="first">'.$this->l('Product').'</span></th>
                        <th class="order_status"><span>'.$this->l('Emails').'</span></th>
                        <th class="order_total"><span>'.$this->l('Total').'</span></th>
                    <tr>
                </thead>
                <tbody>';

        $i = 0;
        $products = self::getProducts();
        if (is_array($products) && count($products)) {
            foreach ($products as $product) {
                $first = true;
                $customers_names = '';
                $customers = self::getCustomers((int)$product['id_product'], (int)$product['id_product_attribute']);
                if (is_array($customers) && count($customers)) {
                    foreach ($customers as $customer) {
                        if ($first) {
                            $customers_names .= $customer['customer_email'];
                        } else {
                            $customers_names .= ', '.$customer['customer_email'];
                        }

                        $first = false;
                    }
                }

                $first = true;
                $attributes_names = '';
                $attributes = Product::getAttributesParams((int)$product['id_product'], (int)$product['id_product_attribute']);
                if (is_array($attributes) && count($attributes)) {
                    foreach ($attributes as $attribute) {
                        if ($first) {
                            $attributes_names .= $attribute['name'];
                        } else {
                            $attributes_names .= ', '.$attribute['name'];
                        }

                        $first = false;
                    }
                }

                $content .= '
                    <tr'.($i % 2 ? ' id="order_line1"' : '').'>
                        <td class="order_td_first order_id"><b>'.$product['name'].'</b> ('.$attributes_names.')</td>
                        <td class="order_status">'.$customers_names.'</td>
                        <td class="order_total">'.(int)count($customers).'</td>
                    </tr>
                ';
                $i++;
            }
        }

        $content .= '
                </tbody>
            </table>
        </div>
        ';

        return $content;
    }

    public function hookDashboardZoneTwo()
    {
        return $this->display(__FILE__, 'dashboard_zone_two.tpl');
    }

    public function hookDashboardData()
    {
        $table_customers = $this->getTableMailAlerts();

        return array(
            'data_table' => array(
                'table_customers' => $table_customers
            )
        );
    }

    public static function getProducts()
    {
        $query = new DbQuery();
        $query->select('COUNT(*) as `nb`, mco.`id_product`, mco.`id_product_attribute`, pl.`name`')
        ->from('mailalert_customer_oos', 'mco')
        ->leftJoin('product_lang', 'pl', '(mco.`id_product` = pl.`id_product` AND pl.`id_lang` = '.(int)Context::getContext()->language->id.')')
        ->groupBy('mco.`id_product`, mco.`id_product_attribute`')
        ->orderBy('`nb` DESC');

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
    }

    public static function getCustomers($id_product, $id_product_attribute)
    {
        $sql = '
            SELECT id_customer, customer_email
            FROM `'._DB_PREFIX_.'mailalert_customer_oos`
            WHERE `id_product` = '.(int)$id_product.' AND `id_product_attribute` = '.(int)$id_product_attribute;

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    }

    public function getTableMailAlerts()
    {
        $header = array(
            array(
                'id' => 'product',
                'title' => $this->l('Product'),
                'class' => 'text-left'
            ),
            array(
                'id' => 'email',
                'title' => $this->l('Emails'),
                'class' => 'text-center'
            ),
            array(
                'id' => 'total',
                'title' => $this->l('Total'),
                'class' => 'text-center'
            )
        );

        $body = array();

        $products = self::getProducts();
        if (is_array($products) && count($products)) {
            foreach ($products as $product) {
                $first = true;
                $customers_names = '';
                $customers = self::getCustomers((int)$product['id_product'], (int)$product['id_product_attribute']);
                if (is_array($customers) && count($customers)) {
                    foreach ($customers as $customer) {
                        if ($first) {
                            $customers_names .= $customer['customer_email'];
                        } else {
                            $customers_names .= ', '.$customer['customer_email'];
                        }

                        $first = false;
                    }
                }

                $first = true;
                $attributes_names = '';
                $attributes = Product::getAttributesParams((int)$product['id_product'], (int)$product['id_product_attribute']);
                if (is_array($attributes) && count($attributes)) {
                    foreach ($attributes as $attribute) {
                        if ($first) {
                            $attributes_names .= $attribute['name'];
                        } else {
                            $attributes_names .= ', '.$attribute['name'];
                        }

                        $first = false;
                    }
                }

                $tr = array();
                $tr[] = array(
                    'id' => 'product',
                    'value' => '<b>'.$product['name'].'</b> ('.$attributes_names.')',
                    'class' => 'text-left',
                );
                $tr[] = array(
                    'id' => 'email',
                    'value' => $customers_names,
                    'class' => 'text-left',
                );
                $tr[] = array(
                    'id' => 'total',
                    'value' => (int)count($customers),
                    'class' => 'text-center',
                );
                $body[] = $tr;
            }
        }

        return array('header' => $header, 'body' => $body);
    }
}
