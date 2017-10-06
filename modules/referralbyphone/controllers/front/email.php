<?php
/**
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @copyright 2007-2015 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

/**
 * @since 1.5.0
 */
class ReferralByPhoneEmailModuleFrontController extends ModuleFrontController
{
    public $content_only = true;
    
    public $display_header = false;
    
    public $display_footer = false;
    
    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();
        $shop_name = htmlentities(Configuration::get('PS_SHOP_NAME'), null, 'utf-8');
        $shop_url = Tools::getHttpHost(true, true);
        $customer = Context::getContext()->customer;
        
        if (!preg_match("#.*\.html$#Ui", Tools::getValue('mail')) OR !preg_match("#.*\.html$#Ui", Tools::getValue('mail')))
            die(Tools::redirect());
            
        $file = Tools::file_get_contents(dirname(__FILE__).'/../../mails/'.(string)preg_replace('#\.{2,}#', '.', Tools::getValue('mail')));
        
        $file = str_replace('{shop_name}', $shop_name, $file);
        $file = str_replace('{shop_url}', $shop_url.__PS_BASE_URI__, $file);
        $file = str_replace('{shop_logo}', $shop_url._PS_IMG_.'logo.jpg', $file);
        $file = str_replace('{firstname}', $customer->firstname, $file);
        $file = str_replace('{lastname}', $customer->lastname, $file);
        $file = str_replace('{email}', $customer->email, $file);
        $file = str_replace('{firstname_friend}', 'XXXXX', $file);
        $file = str_replace('{lastname_friend}', 'xxxxxx', $file);
        $file = str_replace('{link}', 'authentication.php?create_account=1', $file);
        
        $account_voucher = unserialize(Configuration::get('REFERRALPH_ACC_VOUCHER'));
        $order_voucher = unserialize(Configuration::get('REFERRALPH_ORDER_VOUCHER'));
        $f_order_voucher = unserialize(Configuration::get('REFERRALPH_F_ORDER_VOUCHER'));
        
        $c_discount_acc = false;
        $c_discount_ord = false;
        $c_discount_f_ord = false;
        /*
        if ($account_voucher[1] == 1) {
            $discount_type_acc = (int)(Configuration::get('REFERRALPH_DISCOUNT_TYPE_ACC'));
            if ($discount_type_acc == 1) {
                $file = str_replace('{c_discount_acc}', Discount::display((float)(Configuration::get('REFERRALPH_PERCENTAGE_ACC')), $discount_type_acc, new Currency($this->context->currency->id)), $file);
            } else {
                $file = str_replace('{c_discount_acc}', Discount::display((float)(Configuration::get('REFERRALPH_DISCOUNT_VALUE_ACC'.(int)($this->context->currency->id))), $discount_type_acc, new Currency($this->context->currency->id)), $file);
            }
        }
        
        if ($order_voucher[1] == 1) {
            $discount_type_ord = (int)(Configuration::get('REFERRALPH_DISCOUNT_TYPE_ORD'));
            if ($discount_type_ord == 1) {
                $file = str_replace('{c_discount_ord}', Discount::display((float)(Configuration::get('REFERRALPH_PERCENTAGE_ORD')), $discount_type_ord, new Currency($this->context->currency->id)), $file);
            } else {
                $file = str_replace('{c_discount_ord}', Discount::display((float)(Configuration::get('REFERRALPH_DISCOUNT_VALUE_ORD'.(int)($this->context->currency->id))), $discount_type_ord, new Currency($this->context->currency->id)), $file);
            }
        }
        
        if ($account_voucher[1] == 1) {
            $discount_type_acc = (int)(Configuration::get('REFERRALPH_DISCOUNT_TYPE_ACC'));
            if ($discount_type_acc == 1) {
                    $c_discount_acc = Discount::display((float)(Configuration::get('REFERRALPH_PERCENTAGE_ACC')), $discount_type_acc, new Currency($this->context->currency->id));
            } else {
                    $c_discount_acc = Discount::display((float)(Configuration::get('REFERRALPH_DISCOUNT_VALUE_ACC'.(int)($this->context->currency->id))), $discount_type_acc, new Currency($this->context->currency->id));
            }
        }
        
        if ($order_voucher[1] == 1) {
            $discount_type_ord = (int)(Configuration::get('REFERRALPH_DISCOUNT_TYPE_ORD'));
            if ($discount_type_ord == 1) {
                   $c_discount_ord = Discount::display((float)(Configuration::get('REFERRALPH_PERCENTAGE_ORD')), $discount_type_ord, new Currency($this->context->currency->id));
            } else {
                   $c_discount_ord = Discount::display((float)(Configuration::get('REFERRALPH_DISCOUNT_VALUE_ORD'.(int)($this->context->currency->id))), $discount_type_ord, new Currency($this->context->currency->id));
            }
        }
        
        if ($f_order_voucher[1] == 1) {
            $discount_type_f_ord = (int)(Configuration::get('REFERRALPH_DISCOUNT_TYPE_F_ORD'));
            if ($discount_type_f_ord == 1) {
                    $c_discount_f_ord = Discount::display((float)(Configuration::get('REFERRALPH_PERCENTAGE_ORD')), $discount_type_f_ord, new Currency($this->context->currency->id));
            } else {
                    $c_discount_f_ord = Discount::display((float)(Configuration::get('REFERRALPH_DISCOUNT_VALUE_ORD'.(int)($this->context->currency->id))), $discount_type_f_ord, new Currency($this->context->currency->id));
            }
        }
        */
        if ($c_discount_acc) {
            $discount = $c_discount_acc;
        } elseif ($c_discount_ord) {
            $discount = $c_discount_ord;
        } else {
            $discount = $c_discount_f_ord;
        }
        
        $file = str_replace('{discount}', $discount, $file);
        
        $this->context->smarty->assign(array('content' => $file));
        
        $this->setTemplate('email.tpl');
    }
}
