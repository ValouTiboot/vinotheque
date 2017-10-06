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

include_once(dirname(__FILE__).'../../../ReferralByPhoneModule.php');
include_once(dirname(__FILE__).'../../../referralbyphone.php');

class ReferralByPhoneProgramModuleFrontController extends ModuleFrontController
{
    public function __construct()
    {
        parent::__construct();
    
        $this->dir_mails = _PS_MODULE_DIR_.'referralbyphone/mails/';
    }
    
    public function init()
    {
        if (!$this->context->customer->isLogged() or !ReferralByPhoneModule::isCustomerAllowed($this->context->customer)) {
            Tools::redirect('index.php?controller=authentication');
        }
        parent::init();
    }

    public function setMedia()
    {
        parent::setMedia();
        $this->context->controller->addJS(_MODULE_DIR_.$this->module->name.'/views/js/'.$this->module->name.'.js');
        $this->context->controller->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/front.css');
        $this->addJqueryPlugin(array('fancybox', 'idTabs'));
    }


    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();

        // get discount value (ready to display)
        $account_voucher = unserialize(Configuration::get('REFERRALPH_ACC_VOUCHER'));
        $order_voucher = unserialize(Configuration::get('REFERRALPH_ORDER_VOUCHER'));
        $f_order_voucher = unserialize(Configuration::get('REFERRALPH_F_ORDER_VOUCHER'));
                
        $s_discount_acc = false;
        $s_discount_ord = false;
        $s_discount_f_ord = false;
        $c_discount_acc = false;
        $c_discount_ord = false;
        $c_discount_f_ord = false;
        
        $ref_code = ReferralByPhoneModule::getReferralCode($this->context->customer);
        $sponsor_url = ReferralByPhoneModule::getReferralLink($this->context->customer);
       
        
        if (isset($account_voucher)) {
            $discount_type_acc = (int)(Configuration::get('REFERRALPH_DISCOUNT_TYPE_ACC'));
            if ($discount_type_acc == 1) {
                if ($account_voucher[0] == 1) {
                    $s_discount_acc = ReferralByPhoneModule::displayDiscount((float)(Configuration::get('REFERRALPH_PERCENTAGE_ACC')), $discount_type_acc, new Currency($this->context->currency->id));
                } else {
                    $c_discount_acc = ReferralByPhoneModule::displayDiscount((float)(Configuration::get('REFERRALPH_PERCENTAGE_ACC')), $discount_type_acc, new Currency($this->context->currency->id));
                }
            } else {
                if ($account_voucher[0] == 1) {
                    $s_discount_acc = ReferralByPhoneModule::displayDiscount((float)(Configuration::get('REFERRALPH_DISCOUNT_VALUE_ACC'.(int)($this->context->currency->id))), $discount_type_acc, new Currency($this->context->currency->id));
                } else {
                    $c_discount_acc = ReferralByPhoneModule::displayDiscount((float)(Configuration::get('REFERRALPH_DISCOUNT_VALUE_ACC'.(int)($this->context->currency->id))), $discount_type_acc, new Currency($this->context->currency->id));
                }
            }
        }

        if (isset($order_voucher)) {
            $discount_type_ord = (int)(Configuration::get('REFERRALPH_DISCOUNT_TYPE_ORD'));
            if ($discount_type_ord == 2) {
                if ($order_voucher[0] == 1) {
                    $s_discount_ord = ReferralByPhoneModule::displayDiscount((float)(Configuration::get('REFERRALPH_DISCOUNT_VALUE_ORD'.(int)($this->context->currency->id))), $discount_type_ord, new Currency($this->context->currency->id));
                } else {
                    $c_discount_ord = ReferralByPhoneModule::displayDiscount((float)(Configuration::get('REFERRALPH_DISCOUNT_VALUE_ORD'.(int)($this->context->currency->id))), $discount_type_ord, new Currency($this->context->currency->id));
                }
            } else {
                if ($order_voucher[0] == 1) {
                    $s_discount_ord = ReferralByPhoneModule::displayDiscount((float)(Configuration::get('REFERRALPH_PERCENTAGE_ORD')), $discount_type_ord, new Currency($this->context->currency->id));
                } else {
                    $c_discount_ord = ReferralByPhoneModule::displayDiscount((float)(Configuration::get('REFERRALPH_PERCENTAGE_ORD')), $discount_type_ord, new Currency($this->context->currency->id));
                }
            }
        }
        
        if (isset($f_order_voucher)) {
            $discount_type_f_ord = (int)(Configuration::get('REFERRALPH_DISCOUNT_TYPE_FO'));
            if ($discount_type_f_ord == 2) {
                if ($f_order_voucher[0] == 1) {
                    $s_discount_f_ord = ReferralByPhoneModule::displayDiscount((float)(Configuration::get('REFERRALPH_DISCOUNT_VALUE_FO'.(int)($this->context->currency->id))), $discount_type_f_ord, new Currency($this->context->currency->id));
                } else {
                    $c_discount_f_ord = ReferralByPhoneModule::displayDiscount((float)(Configuration::get('REFERRALPH_DISCOUNT_VALUE_FO'.(int)($this->context->currency->id))), $discount_type_f_ord, new Currency($this->context->currency->id));
                }
            } else {
                if ($f_order_voucher[0] == 1) {
                    $s_discount_f_ord = ReferralByPhoneModule::displayDiscount((float)(Configuration::get('REFERRALPH_PERCENTAGE_FO')), $discount_type_f_ord, new Currency($this->context->currency->id));
                } else {
                    $c_discount_f_ord = ReferralByPhoneModule::displayDiscount((float)(Configuration::get('REFERRALPH_PERCENTAGE_FO')), $discount_type_f_ord, new Currency($this->context->currency->id));
                }
            }
        }
       
        $activeTab = 'sponsor';
        $error = false;

        // Mailing invitation to friend sponsor
        $invitation_sent = false;
        $nbInvitation = 0;
        
        $iso_lng = Language::getIsoById((int)($this->context->language->id));
         
        if (is_dir($this->dir_mails . $iso_lng . '/')) {
            $id_lang_current = $this->context->language->id;
        } else {
            $id_lang_current = Language::getIdByIso('en');
        }
        
        if (Tools::isSubmit('submitSponsorFriends') and Tools::getValue('friendsEmail') and sizeof($friendsEmail = Tools::getValue('friendsEmail')) >= 1) {
            $activeTab = 'sponsor';
            if (!Tools::getValue('conditionsValided')) {
                $error = 'conditions not valided';
            } else {
                $friendsLastName = Tools::getValue('friendsLastName');
                $friendsFirstName = Tools::getValue('friendsFirstName');
                $mails_exists = array();
                foreach ($friendsEmail as $key => $friendEmail) {
                    $friendEmail = (string)$friendEmail;
                    $friendLastName = (string)$friendsLastName[$key];
                    $friendFirstName = (string)$friendsFirstName[$key];

                    if (empty($friendEmail) and empty($friendLastName) and empty($friendFirstName)) {
                        continue;
                    } elseif (empty($friendEmail) or !Validate::isEmail($friendEmail)) {
                        $error = 'email invalid';
                    } elseif (empty($friendFirstName) or empty($friendLastName) or !Validate::isName($friendLastName) or !Validate::isName($friendFirstName)) {
                        $error = 'name invalid';
                    } elseif (ReferralByPhoneModule::isEmailExists($friendEmail) or Customer::customerExists($friendEmail)) {
                        $mails_exists[] = $friendEmail;
                    } else {
                        $referralbyphone = new ReferralByPhoneModule();
                        $referralbyphone->id_sponsor = (int)($this->context->customer->id);
                        $referralbyphone->firstname = $friendFirstName;
                        $referralbyphone->lastname = $friendLastName;
                        $referralbyphone->email = $friendEmail;
                        if (!$referralbyphone->validateFields(false)) {
                            $error = 'name invalid';
                        } else {
                            if ($referralbyphone->save()) {
                                $vars = array(
                                    '{email}' => (string)$this->context->customer->email,
                                    '{lastname}' => (string)$this->context->customer->lastname,
                                    '{firstname}' => (string)$this->context->customer->firstname,
                                    '{email_friend}' => $friendEmail,
                                    '{lastname_friend}' => $friendLastName,
                                    '{firstname_friend}' => $friendFirstName,
                                    '{link}' => $referralbyphone->getReferralMailLink(),
                                    '{c_discount_acc}' => $c_discount_acc,
                                    '{c_discount_ord}' => $c_discount_ord,
                                    '{c_discount_f_ord}' => $c_discount_f_ord,
                                );
                                Mail::Send((int)$id_lang_current, 'referralbyphone-invitation', Mail::l('I invite you to join', (int)$this->context->language->id), $vars, $friendEmail, $friendFirstName.' '.$friendLastName, (string)Configuration::get('PS_SHOP_EMAIL'), (string)Configuration::get('PS_SHOP_NAME'), null, null, dirname(__FILE__).'/../../mails/');
                                $invitation_sent = true;
                                $nbInvitation++;
                                $activeTab = 'pending';
                            } else {
                                $error = 'cannot add friends';
                            }
                        }
                    }
                    if ($error) {
                        break;
                    }
                }
                if ($nbInvitation > 0) {
                    unset($_POST);
                }
                //Not to stop the sending of e-mails in case of doubloon
                if (sizeof($mails_exists)) {
                    $error = 'email exists';
                }
            }
        }

        // Mailing revive
        $revive_sent = false;
        $nbRevive = 0;
        if (Tools::isSubmit('revive')) {
            $activeTab = 'pending';
            if (Tools::getValue('friendChecked') and sizeof($friendsChecked = Tools::getValue('friendChecked')) >= 1) {
                foreach ($friendsChecked as $key => $friendChecked) {
                    if (ReferralByPhoneModule::isSponsorFriend((int)($this->context->customer->id), (int)($key))) {
                        $referralbyphone = new ReferralByPhoneModule((int)($key));
                        $vars = array(
                            '{email}' => $this->context->customer->email,
                            '{lastname}' => $this->context->customer->lastname,
                            '{firstname}' => $this->context->customer->firstname,
                            '{email_friend}' => $referralbyphone->email,
                            '{lastname_friend}' => $referralbyphone->lastname,
                            '{firstname_friend}' => $referralbyphone->firstname,
                            '{link}' => $referralbyphone->getReferralMailLink(),
                            '{c_discount_acc}' => $c_discount_acc,
                            '{c_discount_ord}' => $c_discount_ord,
                            '{c_discount_f_ord}' => $c_discount_f_ord,
                        );
                        $referralbyphone->save();
                        Mail::Send((int)$id_lang_current, 'referralbyphone-invitation', Mail::l('Re-invitation to the program', (int)$this->context->language->id), $vars, $referralbyphone->email, $referralbyphone->firstname.' '.$referralbyphone->lastname, (string)Configuration::get('PS_SHOP_EMAIL'), (string)Configuration::get('PS_SHOP_NAME'), null, null, dirname(__FILE__).'/../../mails/');
                        $revive_sent = true;
                        $nbRevive++;
                    }
                }
            } else {
                $error = 'no revive checked';
            }
        }

        $customer = new Customer((int)($this->context->customer->id));
        $stats = $customer->getStats();

        $orderQuantity = (int)(Configuration::get('REFERRALPH_ORDER_QUANTITY'));
        $canSendInvitations = false;

        if ((int)($stats['nb_orders']) >= $orderQuantity) {
            $canSendInvitations = true;
        }
        
        $discountInPercent = Tools::getValue('discount_type', Configuration::get('REFERRALPH_DISCOUNT_TYPE')) == 1;

        $xmlFile = _PS_MODULE_DIR_.'referralbyphone/referralbyphone.xml';
        if (file_exists($xmlFile)) {
            if ($xml = @simplexml_load_file($xmlFile)) {
                $this->context->smarty->assign(array(
                        'xml' => $xml,
                        'paragraph' => 'paragraph_'.$this->context->language->id
                ));
            }
        }
        //Statistics
        $friends = ReferralByPhoneModule::getSponsorFriend((int)($this->context->customer->id), 'subscribed');
        $friends_total_orders = 0;
        foreach ($friends as $key => &$friend) {
            $friend['orders_count'] = sizeof(Order::getCustomerOrders($friend['id_customer']));
            $friend['sponsored_friend_count'] = sizeof(ReferralByPhoneModule::getSponsorFriend($friend['id_customer']));
            $friends_total_orders += $friend['orders_count'];
        }

        $this->context->smarty->assign(array(
                'friends_total_count' => ReferralByPhoneModule::countFriends((int)($this->context->customer->id)),
                'friends_total_orders' => $friends_total_orders,
        ));
        
        // Smarty display
        $this->context->smarty->assign(array(
            'activeTab' => $activeTab,
            's_discount_acc' => $s_discount_acc,
            's_discount_ord' => $s_discount_ord,
            's_discount_f_ord' => $s_discount_f_ord,
            'orderQuantity' => $orderQuantity,
            'canSendInvitations' => $canSendInvitations,
            'nbFriends' => (int)(Configuration::get('REFERRALPH_NB_FORM_FRIENDS')),
            'error' => $error,
            'invitation_sent' => $invitation_sent,
            'ref_code' => $ref_code,
            'sponsor_url' => $sponsor_url,
            'nbInvitation' => $nbInvitation,
            'pendingFriends' => ReferralByPhoneModule::getSponsorFriend((int)($this->context->customer->id), 'pending'),
            'revive_sent' => $revive_sent,
            'nbRevive' => $nbRevive,
            'subscribeFriends' => $friends,
            'mails_exists' => (isset($mails_exists) ? $mails_exists : array()),
            'currencySign' => ($discountInPercent ? '%' : $this->context->currency->sign)
        ));
        if (version_compare(_PS_VERSION_, '1.7', '>')) {
            $this->setTemplate('module:referralbyphone/views/templates/front/program17.tpl');
        } else {
            $this->setTemplate('program.tpl');
        }
    }
}
