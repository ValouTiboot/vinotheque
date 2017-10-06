<?php
/**
* 2007-2017 PrestaShop
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2017 PrestaShop SA
* @license   http://addons.prestashop.com/en/content/12-terms-and-conditions-of-use
* International Registered Trademark & Property of PrestaShop SA
*/

include_once('../../config/config.inc.php');
$token = Tools::getValue('token_cartabandonment');
$id_shop = Context::getContext()->shop->id;
$token_bdd = Configuration::get('CARTABAND_TOKEN', null, null, $id_shop);
if (Tools::strlen($token_bdd) > 0 && isset($token_bdd) && isset($token) && $token_bdd == $token) {
    $id_template = Tools::getValue('template_id');
    echo TemplateController::deleteTemplate($id_template, $iso);
} else {
    echo 'hack ...';
    die;
}
