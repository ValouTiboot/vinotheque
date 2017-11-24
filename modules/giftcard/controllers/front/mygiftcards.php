<?php
/**
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    FMM Modules
*  @copyright 2017 FMM Modules
*  @license   FMM Modules
*  @version   1.4.1
*/

/**
 * @since 1.5.0
 */

class GiftCardMyGiftCardsModuleFrontController extends ModuleFrontController
{
    public $errors = '';
    public function __construct()
    {
        parent::__construct();
        $this->display_column_left = false;
        $this->display_column_right = false;
        $this->context = Context::getContext();
    }

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();
        $this->giftcardProcess();
    }

    public function giftcardProcess()
    {
        $html = 0;
        $model = new Gift();
        $id_lang = $this->context->cookie->id_lang;
        if ($this->context->customer->isLogged()) {
            if (Tools::isSubmit('send_giftcard')) {
                $id_cart_rule   = (int)Tools::getValue('id_coupen');
                $friend_name    = (string)Tools::getValue('friend_name');
                $friend_email   = (string)Tools::getValue('friend_email');
                $now            = (string)date('Y-m-d H:i:s');

                $mycoupon = $model->getCartsRuleById($id_cart_rule, $id_lang);
                $mycoupon = array_shift($mycoupon);
                $old_voucher = new CartRule((int)$id_cart_rule);
                $gift_coupon = new CartRule();

                $gift_coupon->name[$id_lang]            = $mycoupon['name'];
                $gift_coupon->date_from                 = $mycoupon['date_from'];
                $gift_coupon->date_to                   = $mycoupon['date_to'];
                $gift_coupon->quantity                  = 1;
                $gift_coupon->quantity_per_user         = 1;
                $gift_coupon->free_shipping             = $mycoupon['free_shipping'];
                $gift_coupon->reduction_currency        = $mycoupon['reduction_currency'];
                $gift_coupon->active                    = $mycoupon['active'];
                $gift_coupon->date_add                  = $mycoupon['date_add'];
                $gift_coupon->reduction_product         = $mycoupon['reduction_product'];
                $gift_coupon->code                      = Tools::passwdGen(mt_rand(4, 14));
                $gift_coupon->minimum_amount_currency   = $mycoupon['minimum_amount_currency'];
                $gift_coupon->reduction_amount          = $mycoupon['reduction_amount'];
                $gift_coupon->reduction_tax             = $mycoupon['reduction_tax'];
                $gift_coupon->reduction_percent         = $mycoupon['reduction_percent'];
                $gift_coupon->shop_restriction          = $old_voucher->shop_restriction;

                if ($gift_coupon->add()) {
                    CartRule::copyConditions($id_cart_rule, $gift_coupon->id);
                    $email = trim($friend_email);
                    if (!validate::isName($friend_name)) {
                        $this->context->controller->errors[] = Tools::displayError('Inavlid recipient name');
                    }
                    if (empty($email)) {
                        $this->context->controller->errors[] = Tools::displayError('E-mail address required');
                    } elseif (!Validate::isEmail($email)) {
                        $this->context->controller->errors[] = Tools::displayError('Invalid e-mail address');
                    } elseif (!$this->context->controller->errors) {
                        $currency = new Currency((int)$gift_coupon->reduction_currency);
                        $sender_name = $this->context->cookie->customer_firstname.' '.$this->context->cookie->customer_lastname;
                        $value = (isset($mycoupon['reduction_percent']) && $mycoupon['reduction_percent'] != '0.00') ? $mycoupon['reduction_percent'].' %' : Tools::displayPrice($mycoupon['reduction_amount'], $currency);

                        if (!$this->SendGiftCard($sender_name, $friend_name, $friend_email, $gift_coupon->name[$id_lang], $gift_coupon->code, $gift_coupon->date_to, $now, $value)) {
                            $this->context->controller->errors[] = Tools::displayError('Gift card sending failed');
                        } else {
                            $html = 1;
                            $qty = $mycoupon['quantity'];
                            if (!empty($mycoupon['quantity'])) {
                                $qty = $qty - 1;
                            }

                            Db::getInstance()->update('cart_rule', array('quantity' => (int)$qty, 'quantity_per_user' => (int)$qty), 'id_cart_rule ='.(int)$mycoupon['id_cart_rule']);
                            Tools::redirect('index.php?fc=module&module=giftcard&controller=mygiftcards&msg='.(int)$html);
                        }
                    }
                } else {
                    $this->context->controller->errors[] = Tools::displayError('Cannot sent, something went wrong');
                }
            }
        } else {
            Tools::redirect('index.php?controller=authentication&back='.urlencode($this->context->link->getModuleLink('giftcard', 'mygiftcards')));
        }

        $id_customer = (int)$this->context->cookie->id_customer;
        $id_lang = (int)$this->context->cookie->id_lang;
        $coupen = $model->getVoucherByCustomerId($id_customer, $id_lang);
        $pending_gc = $model->getOrderedGiftCards($id_customer);
        foreach ($pending_gc as $gc) {
            GiftCard::generateVoucher($gc['id_cart'], 'front');
        }

        $this->context->smarty->assign(array(
            'id_customer'   => $id_customer,
            'coupens'       => $coupen,
            'errors'        => $this->context->controller->errors,
            'msg'           => $html,
            'ps_version'    => _PS_VERSION_));

        if (true == Tools::version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $this->setTemplate('module:'.$this->module->name.'/views/templates/front/v1_7/mygiftcards.tpl');
        } else {
            $this->setTemplate('v1_6/mygiftcards.tpl');
        }
    }

    public function SendGiftCard($sender, $rec_name, $rec_email, $giftcard_name, $vcode, $expire_date, $date, $value)
    {
        $id_lang = (int)$this->context->cookie->id_lang;
        $vars = array(
        '{sender}'          => $sender,
        '{rec_name}'        => $rec_name,
        '{giftcard_name}'   => $giftcard_name,
        '{vcode}'           => $vcode,
        '{expire_date}'     => $expire_date,
        '{date}'            => $date,
        '{value}'           => $value,
        '{shop_link}'       => _PS_BASE_URL_.__PS_BASE_URI__
        );

        if (!empty($rec_email)) {
            Mail::Send((int)$id_lang, 'gift', Mail::l('You received a Gift Card', (int)$id_lang), $vars, $rec_email, null, null, null, null, null, _PS_MODULE_DIR_.'giftcard/mails/', false);
            return true;
        }
        return false;
    }
}
