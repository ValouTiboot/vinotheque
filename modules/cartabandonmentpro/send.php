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

include_once(dirname(__FILE__).'/../../config/config.inc.php');
include_once('cartabandonmentpro.php');
$dir = str_replace('\\', '/', dirname(__FILE__));

$id_shop = Tools::getValue('id_shop');
if (!$id_shop) {
    $id_shop = Tools::getValue('amp;id_shop');
    if (!$id_shop) {
        $id_shop = $argv[1];
    }
    if (!$id_shop) {
        echo 'No shop ...';
        die;
    }
}

$token = Tools::getValue('token');
$token_bdd = Configuration::get('CARTABAND_TOKEN', null, null, $id_shop);

if (!$token) {
    $token = Tools::getValue('amp;token');
}

if (!$token) {
    $token = $argv[2];
}

if (Tools::strlen($token_bdd) > 0 && isset($token_bdd) && isset($token) && $token_bdd == $token) {
    require_once dirname(__FILE__).'/controllers/ReminderController.class.php';
    require_once dirname(__FILE__).'/controllers/TemplateController.class.php';
    require_once dirname(__FILE__).'/classes/Template.class.php';
    include_once('controllers/DiscountsController.class.php');
    $wich_remind = Tools::getValue('wich_remind');

    if (!$wich_remind) {
        $wich_remind = Tools::getValue('amp;wich_remind');
        if (!$wich_remind) {
            $wich_remind = $argv[3];
        }
        if (!$wich_remind) {
            echo 'No remind number ...';
            die;
        }
    }

    $query = "SELECT active FROM `"._DB_PREFIX_."cartabandonment_remind_config`
    WHERE wich_remind = " . (int)$wich_remind;
    if (Db::getInstance()->getValue($query) == 0) {
        die;
    }

    $carts = ReminderController::getAbandonedCart($wich_remind, $id_shop);
    $templates = TemplateController::getActiveTemplate($id_shop);
    if (!$templates) {
        die('No active template ...');
    }
    $x = 0;
    $sent = array();
    $first = true;
    $mails = '';

    if (!isset(Context::getContext()->link)) {
        Context::getContext()->link = new Link();
    }

    foreach ($carts as $arr_cart) {
        Context::getContext()->cart = new Cart($arr_cart['id_cart']);

        $iso = Language::getIsoById($arr_cart['id_lang']);
        $id_lang = $arr_cart['id_lang'];

        if (!isset($templates[$arr_cart['id_shop']][$arr_cart['id_lang']][$wich_remind])) {
            $id_lang = Configuration::get('PS_LANG_DEFAULT');
            $iso = Language::getIsoById($id_lang);
        }

        $content = Tools::file_get_contents('mails/' . $iso . '/' . $templates[$arr_cart['id_shop']][$id_lang][$wich_remind]['id'] . '.html');
        $content = CartAbandonmentProTemplate::editTemplate($content, $wich_remind, $arr_cart['id_cart'], $id_lang, $id_shop);

        if (!$content) {
            continue;
        }

        $discounts = DiscountsController::getDiscounts($wich_remind);

        $cart2 = new Cart($arr_cart['id_cart']);
        if (!isset($context->currency->id)) {
            $context->currency = new Currency($cart2->id_currency, null, $id_shop);
        }

        $id_address = Address::getFirstCustomerAddressId($arr_cart['id_customer']);
        if ($cart2->id_address_delivery != $id_address) {
            $cart2->id_address_delivery = $id_address;
            $cart2->id_address_invoice = $id_address;
            $cart2->save();
        }

        $total_cart = $cart2->getOrderTotal();
        $i = 0;
        $disc = false;
        $disc_valid = false;
        $type = false;
        $min = false;
        $max = false;
        $value = false;

        if (is_array($discounts) && count($discounts) > 0) {
            foreach ($discounts as $discount) {
                if ($total_cart >= $discount['min_amount']) {
                    $disc = $i;
                    $disc_valid = $discount['valid_value'];
                    $type = $discount['type'];
                    $min = $discount['min_amount'];
                    $max = $discount['max_amount'];
                    $value = $discount['value'];
                }
                $i++;
            }

            if ($value > 0 || $type == 'shipping') {
                $voucher = DiscountsController::createDiscount($arr_cart['id_customer'], $value, $disc_valid, $type, $min);
                $content = CartAbandonmentProTemplate::editDiscount($voucher, $content, $id_lang);
            } else {
                $content = str_replace('%DISCOUNT_TXT%', "", $content);
            }
        } else {
            $content = str_replace('%DISCOUNT_TXT%', "", $content);
        }

        $title     = CartAbandonmentProTemplate::editTitleBeforeSending($templates[$arr_cart['id_shop']][$id_lang][$wich_remind]['name'], $arr_cart['id_cart'], $id_lang);

        $fp = fopen('mails/' . $iso . '/send.html', 'w+');
        fwrite($fp, $content);
        fclose($fp);
        $fp = fopen('mails/' . $iso . '/send.txt', 'w+');
        $content = preg_replace("/(\s){2,}/", "\r\n\r\n", trim(strip_tags($content)));
        fwrite($fp, $content);
        fclose($fp);

        $mail = Mail::Send($id_lang, 'send', $title, array(), $arr_cart['email'], null, null, null, null, null, dirname(__FILE__) . '/mails/');

        unlink('mails/' . $iso . '/send.html');
        unlink('mails/' . $iso . '/send.txt');

        if ($mail) {
            if (!$first) {
                $mails .= ';';
            }
            $mails .= $arr_cart['email'];
            $first = false;
            $justSent = array('id_customer'=> $arr_cart['id_customer'], 'id_cart'=> $arr_cart['id_cart'], 'firstname' => $arr_cart['firstname'], 'lastname' => $arr_cart['lastname'], 'email' => $arr_cart['email']);
            $sent[] = $justSent;
            Db::getInstance()->Execute("INSERT INTO "._DB_PREFIX_."cartabandonment_remind VALUES (NULL, " . (int) $wich_remind . ", " . (int) $arr_cart['id_cart'] . ", NOW(), 0, 0, 0)");
            $x++;
        }
    }
    unset($justSent, $carts, $content, $title, $templates);
    $str = '<LINK rel=stylesheet type="text/css" href="views/css/bootstrap.min.css">';
    $str .= '<div class="container"><h3>'.$x.' mails have been sent.</h3><br><br>';
    $str .= '<table class="table table-striped"><tr><th>ID CUSTOMER</th><th>ID CART</th><th>FIRSTNAME</th><th>LASTNAME</th><th>EMAIL</th></tr>';
    foreach ($sent as $s) {
        $str .= '<tr><td>'.$s['id_customer'].'</td><td>'.$s['id_cart'].'</td><td>'.$s['firstname'].'</td><td>'.$s['lastname'].'</td><td><a href="mailto:'.$s['email'].'">'.$s['email'].'</a></td></tr>';
    }
    $str .= '</table>
            </div>';
    echo $str;
} else {
    echo 'hack ...';
    die;
}
