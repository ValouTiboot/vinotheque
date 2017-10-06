<?php
/**
* 2007-2014 PrestaShop
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2014 PrestaShop SA
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
class FrontController extends FrontControllerCore
{
    /*
    * module: lgseoredirect
    * date: 2017-06-28 13:43:22
    * version: 1.2.7
    */
    public function init()
    {
        if (Module::isInstalled('lgseoredirect')) {
            $uri_var = $_SERVER['REQUEST_URI'];
            $shop_id = Context::getContext()->shop->id;
            $redirect = Db::getInstance()->getRow(
                'SELECT * FROM '._DB_PREFIX_.'lgseoredirect '.
                'WHERE url_old = "'.pSQL($uri_var).'" '.
                'AND id_shop = "'.(int)$shop_id.'" '.
                'ORDER BY id DESC'
            );
            if ($redirect and $uri_var == $redirect['url_old'] and $shop_id == $redirect['id_shop']) {
                if ($redirect['redirect_type'] == 301) {
                    $header = 'HTTP/1.1 301 Moved Permanently';
                }
                if ($redirect['redirect_type'] == 302) {
                    $header = 'HTTP/1.1 302 Moved Temporarily';
                }
                if ($redirect['redirect_type'] == 303) {
                    $header = 'HTTP/1.1 303 See Other';
                }
                Tools::redirect($redirect['url_new'], __PS_BASE_URI__, null, $header);
            }
        }
        parent::init();
    }
}
