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
include_once('classes/Template.class.php');
include_once('controllers/TemplateController.class.php');
include_once('classes/Model.class.php');

$token = Tools::getValue('token_cartabandonment');
$id_shop = Context::getContext()->shop->id;
$token_bdd = Configuration::get('CARTABAND_TOKEN', null, null, $id_shop);
$template_id = Tools::getValue('template_id');
$wich_remind = Tools::getValue('wich_remind');
$iso = Language::getIsoById(Tools::getValue('language'));
$id_lang = Tools::getValue('language');

if (Tools::strlen($token_bdd) > 0 && isset($token_bdd) && isset($token) && $token_bdd == $token) {
    $templates = TemplateController::getActiveTemplate($id_shop);
    $content = Tools::file_get_contents('mails/' . $iso . '/' . $templates[$id_shop][$id_lang][$wich_remind]['id'] . '.html');

    // Replace %DISCOUNT_TXT%
    // id_template = reminder and not the real id_template
    $discount = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'cartabandonmentpro_cartrule WHERE id_template = '.(int)$wich_remind);
    if ($discount) {
        $fake_voucher = new CartRule();
        if ($discount['type'] == 'percent') {
            $fake_voucher->reduction_percent = $discount['value'];
        } elseif ($discount['type'] == 'currency') {
            $fake_voucher->reduction_amount = $discount['value'];
        }

        $fake_voucher->date_to = 'XX-XX-XX XX:XX:XX';
        $fake_voucher->code = 'XXXXXXX';

        $content = CartAbandonmentProTemplate::editDiscount($fake_voucher, $content, $id_lang);
    }

    die(CartAbandonmentProTemplate::editTemplate($content, $wich_remind));
} else {
    echo 'hack ...';
    die;
}
