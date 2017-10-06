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

$wichRemind = Tools::getValue('wichRemind');
$id_cart = Tools::getValue('id_cart');
$token = Tools::getValue('token_cart');

if ($token == md5(_COOKIE_KEY_.'recover_cart_'.$id_cart)) {
    $query = "UPDATE "._DB_PREFIX_."cartabandonment_remind SET visualize = 1 WHERE wich_remind = ".(int)$wichRemind." AND id_cart = ".(int)$id_cart;
    Db::getInstance()->Execute($query);
}

header('Content-Type: image/png');
echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZgAAAApJREFUCNdjYAAAAAIAAeIhvDMAAAAASUVORK5CYII=');
