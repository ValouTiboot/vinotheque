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
$dir = str_replace('\\', '/', dirname(__FILE__));

if (is_dir($dir.'/../../themes/'._THEME_NAME_.'/modules/cartabandonmentpro')) {
    // rmdir($dir.'/../../themes/'._THEME_NAME_.'/modules/cartabandonmentpro');
    deleteDirectory($dir.'/../../themes/'._THEME_NAME_.'/modules/cartabandonmentpro');
}
$token = Tools::getValue('token');
$id_shop = Context::getContext()->shop->id;
$token_bdd = Configuration::get('CARTABAND_TOKEN', null, null, $id_shop);

if (Tools::strlen($token_bdd) > 0 && isset($token_bdd) && isset($token) && $token_bdd == $token) {
    require_once dirname(__FILE__).'/controllers/ReminderController.class.php';
    require_once dirname(__FILE__).'/controllers/TemplateController.class.php';
    require_once dirname(__FILE__).'/classes/Template.class.php';

    include_once('classes/Template.class.php');
    include_once('controllers/TemplateController.class.php');
    include_once('controllers/DiscountsController.class.php');
    include_once('classes/Model.class.php');

    $id_lang = Tools::getValue('id_lang');
    $iso = Language::getIsoById($id_lang);
    $mail = Tools::getValue('mail');

    if (!Validate::isEmail($mail)) {
        die('Invalid email');
    }

    $templates = TemplateController::getActiveTemplate($id_shop);
    $x = 0;
    if (!isset(Context::getContext()->link)) {
        Context::getContext()->link = new Link();
    }


    foreach ($templates[$id_shop][$id_lang] as $which_remind => $template) {
        $total_cart = (float)Tools::getValue('amount', 0);
        $content = Tools::file_get_contents('mails/' . $iso . '/' . $template['id'] . '.html');
        $content = CartAbandonmentProTemplate::editTemplate($content, $template['id'], null, $id_lang, $id_shop);

        if (!$content) {
            continue;
        }

        // Replace %DISCOUNT_TXT%
        // id_template = reminder and not the real id_template
        $discount = Db::getInstance()->getRow('
            SELECT * FROM '._DB_PREFIX_.'cartabandonmentpro_cartrule
            WHERE id_template = '.(int)$which_remind.'
            AND min_amount <= '.(int)$total_cart.'
            ORDER BY min_amount DESC');
        if ($discount) {
            $fake_voucher = new CartRule();
            if ($discount['type'] == 'percent') {
                $fake_voucher->reduction_percent = $discount['value'];
            } elseif ($discount['type'] == 'currency') {
                $fake_voucher->reduction_amount = $discount['value'];
            }

            $fake_voucher->date_to = 'XX-XX-XX XX:XX:XX';
            $fake_voucher->code = 'XXXXXXX';

            $content = CartAbandonmentProTemplate::editDiscount($fake_voucher, $content, Context::getContext()->cookie->id_lang);
        } else {
            $content = str_replace('%DISCOUNT_TXT%', '', $content);
        }

        $fp = fopen('mails/' . $iso . '/send.html', 'w+');
        fwrite($fp, $content);
        fclose($fp);
        $fp = fopen('mails/' . $iso . '/send.txt', 'w+');
        $content = preg_replace("/(\s){2,}/", "\r\n\r\n", trim(strip_tags($content)));
        fwrite($fp, $content);
        fclose($fp);

        $title = CartAbandonmentProTemplate::editTitleBeforeSending($template['name'], null, $id_lang);

        $sent = Mail::Send($id_lang, 'send', $title, array(), trim($mail), null, null, null, null, null, dirname(__FILE__) . '/mails/');

        if ($sent) {
            $x++;
        }
    }
    echo $x . ' mails have been sent.';
} else {
    echo 'hack ...';
    die;
}

function deleteDirectory($dir)
{
    if (!file_exists($dir)) {
        return true;
    }

    if (!is_dir($dir)) {
        return unlink($dir);
    }

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }

    return rmdir($dir);
}
