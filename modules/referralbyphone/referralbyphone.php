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
*  @copyright 2007-2017 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once(dirname(__FILE__).'/ReferralByPhoneModule.php');
include_once(dirname(__FILE__).'/classes/ReferralByPhoneSponsorModel.php');
    
class ReferralByPhone extends Module
{
    public function __construct()
    {
        $this->name = 'referralbyphone';
        $this->tab = 'advertising_marketing';
        $this->version = '2.2.1';
        $this->author = 'Snegurka';

        $this->bootstrap = true;
        $this->module_key = '9326b22ea29e3242fcf41fc76b3f3188';
        parent::__construct();

        $this->confirmUninstall = $this->l('All sponsors and friends will be deleted. Are you sure you want to uninstall this module?');
        
        ## prestashop 1.7 ##
        if (version_compare(_PS_VERSION_, '1.7', '>')) {
            require_once(_PS_MODULE_DIR_.$this->name.'/classes/ps17helpreferralbyphone.class.php');
            $ps17help = new Ps17helpReferralbyphone();
            $ps17help->setMissedVariables();
        } else {
            $smarty = $this->context->smarty;
            $smarty->assign($this->name.'is17', 0);
        }
        ## prestashop 1.7 ##
        
        //$this->displayName = $this->getTranslator()->trans('Referral Program Plus', array(), 'Modules.referralbyphone');
        //$this->description = $this->getTranslator()->trans('Integrate a referral program system into your shop ', array(), 'Modules.referralbyphone');
        $this->displayName = $this->l('Referral Program Plus');
        $this->description = $this->l('Integrate a referral program system into your shop.');
        $this->dir_mails = _PS_MODULE_DIR_.'referralbyphone/mails/';
        
        
        //$this->ps_versions_compliancy = array('min' => '1.5.0', 'max' => _PS_VERSION_);
        if (Configuration::get('REFERRALPH_DISCOUNT_TYPE') == 1 and !Configuration::get('REFERRALPH_PERCENTAGE')) {
            $this->warning = $this->l('Please specify an amount for referral program vouchers.');
        }

        if ($this->id) {
            //$this->_configuration = Configuration::getMultiple(array('REFERRALPH_NB_FRIENDS', 'REFERRALPH_ORDER_QUANTITY', 'REFERRALPH_DISCOUNT_TYPE', 'REFERRALPH_DISCOUNT_VALUE'));
            $this->_configuration['REFERRALPH_DISCOUNT_DESCRIPTION'] = Configuration::getInt('REFERRALPH_DISCOUNT_DESCRIPTION');
            $this->_xmlFile = dirname(__FILE__).'/referralbyphone.xml';
        }
    }

    public function install()
    {
        include(dirname(__FILE__).'/sql/install.php');
        
        $defaultTranslations = array('en' => 'Referral reward', 'fr' => 'Récompense parrainage');
        $desc_acc = array((int)Configuration::get('PS_LANG_DEFAULT') => $this->l('Referral reward'));
        $desc_fo = array((int)Configuration::get('PS_LANG_DEFAULT') => $this->l('Referral reward2'));
        $desc_ord = array((int)Configuration::get('PS_LANG_DEFAULT') => $this->l('Referral reward3'));
        foreach (Language::getLanguages() as $language) {
            if (isset($defaultTranslations[$language['iso_code']])) {
                $desc_acc[(int)$language['id_lang']] = $defaultTranslations[$language['iso_code']];
                $desc_fo[(int)$language['id_lang']] = $defaultTranslations[$language['iso_code']];
                $desc_ord[(int)$language['id_lang']] = $defaultTranslations[$language['iso_code']];
            }
        }
        
        if (version_compare(_PS_VERSION_, '1.7', '>')) {
            $this->registerHook('additionalCustomerFormFields');
            $this->registerHook('productActions');
        }

        if (!parent::install()
            or !$this->registerHook('orderConfirmation') or !$this->registerHook('updateOrderStatus')
            or !$this->registerHook('adminCustomers') or !$this->registerHook('createAccount')
            or !$this->registerHook('displayCustomerAccountForm')
            or !$this->registerHook('actionSubmitAccountBefore')
            or !$this->registerHook('displayLeftColumnProduct')
            or !$this->registerHook('customerAccount')
            or !$this->registerHook('backOfficeHeader') or !$this->registerHook('shoppingCart')
            or !$this->registerHook('Header')) {
            return false;
        }

        /* Define a default value for fixed amount vouchers, for each currency */
        foreach (Currency::getCurrencies() as $currency) {
            Configuration::updateValue('REFERRALPH_DISCOUNT_VALUE_FO_'.(int)($currency['id_currency']), 5);
            Configuration::updateValue('REFERRALPH_DISCOUNT_VALUE_ACC_'.(int)($currency['id_currency']), 5);
            Configuration::updateValue('REFERRALPH_DISCOUNT_VALUE_ORD_'.(int)($currency['id_currency']), 5);
        }
        
        $groups_config = '';
        $groups = Group::getGroups((int)Configuration::get('PS_LANG_DEFAULT'));
        foreach ($groups as $group) {
            $groups_config .= (int)$group['id_group'].',';
        }
        $groups_config = rtrim($groups_config, ',');
        
        Configuration::updateValue('REFERRALPH_FIRST_START', false);
        Configuration::updateValue('REFERRALPH_SPONSOR_DATA', 'sponsorby_email');
        Configuration::updateValue('REFERRALPH_SPONSOR_GROUPS', $groups_config);
        Configuration::updateValue('REFERRALPH_GODCHILD_LIST', true);
        Configuration::updateValue('REFERRALPH_ORDER_QUANTITY', 0);
        Configuration::updateValue('REFERRALPH_NB_FRIENDS', 5);
        Configuration::updateValue('REFERRALPH_MIN_ORDER', 0);
        
        /* Define a default value for the percentage vouchers */
        
        Configuration::updateValue('REFERRALPH_ACC_VOUCHER', serialize(array(1, 0)));
        Configuration::updateValue('REFERRALPH_ORDER_VOUCHER', serialize(array(1, 0)));
        Configuration::updateValue('REFERRALPH_F_ORDER_VOUCHER', serialize(array(1, 0)));
        
        Configuration::updateValue('REFERRALPH_DISCOUNT_TYPE_ACC', 2);
        Configuration::updateValue('REFERRALPH_PERCENTAGE_ACC', 5);
        Configuration::updateValue('REFERRALPH_DISCOUNT_TYPE_FO', 2);
        Configuration::updateValue('REFERRALPH_PERCENTAGE_FO', 5);
        Configuration::updateValue('REFERRALPH_DISCOUNT_TYPE_ORD', 2);
        Configuration::updateValue('REFERRALPH_PERCENTAGE_ORD', 5);
                
        Configuration::updateValue('REFERRALPH_VOUCHER_DESCR_ACC', $desc_acc);
        Configuration::updateValue('REFERRALPH_VOUCHER_DESCR_FO', $desc_fo);
        Configuration::updateValue('REFERRALPH_VOUCHER_DESCR_ORD', $desc_ord);
        
        Configuration::updateValue('REFERRALPH_ORD_STATE_GENERATION', serialize(array(2)));
        Configuration::updateValue('REFERRALPH_VOUCHER_DURATION_ACC', 365);
        Configuration::updateValue('REFERRALPH_VOUCHER_DURATION_ORD', 365);
        Configuration::updateValue('REFERRALPH_HIGHLIGHT_ACC', true);
        Configuration::updateValue('REFERRALPH_HIGHLIGHT_ORD', true);
        Configuration::updateValue('REFERRALPH_CUMULAT_ACC', true);
        Configuration::updateValue('REFERRALPH_CUMULAT_ORD', true);
        
        /* Define a default value for the amount tax */
        Configuration::updateValue('REFERRALPH_TAX_ACC', 1);
        Configuration::updateValue('REFERRALPH_TAX_ORD', 1);

        Configuration::updateValue('REFERRALPH_EMAIL_ACC', true);
        
        $this->registerHook('displayMyAccountBlock');

        return true;
    }



    public function uninstall()
    {
        $result = true;
        foreach (Currency::getCurrencies() as $currency) {
            $result = $result and Configuration::deleteByName('REFERRALPH_DISCOUNT_VALUE_'.(int)($currency['id_currency']));
        }
        if (!parent::uninstall() or !$this->uninstallDB() or !$this->removeMail() or !$result
        or !Configuration::deleteByName('REFERRALPH_PERCENTAGE') or !Configuration::deleteByName('REFERRALPH_ORDER_QUANTITY')
        or !Configuration::deleteByName('REFERRALPH_DISCOUNT_TYPE') or !Configuration::deleteByName('REFERRALPH_NB_FRIENDS')
        or !Configuration::deleteByName('REFERRALPH_DISCOUNT_DESCRIPTION')) {
            return false;
        }
        return true;
    }

    public function uninstallDB()
    {
        return Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'referralbyphone`;');
    }

    public function removeMail()
    {
        $langs = Language::getLanguages(false);
        foreach ($langs as $lang) {
            foreach (array('referralbyphone-congratulations', 'referralbyphone-invitation', 'referralbyphone-voucher') as $name) {
                foreach (array('txt', 'html') as $ext) {
                    $file = _PS_MAIL_DIR_.$lang['iso_code'].'/'.$name.'.'.$ext;
                    if (file_exists($file) and !@unlink($file)) {
                        $this->_errors[] = $this->l('Cannot delete this file:').' '.$file;
                    }
                }
            }
        }
        return true;
    }

    public static function displayDiscount($discountValue, $discountType, $currency = false)
    {
        if ((float)$discountValue and (int)$discountType) {
            if ($discountType == 1) {
                return $discountValue.chr(37); // asCII #37 --> % (percent)
            } elseif ($discountType == 2) {
                return Tools::displayPrice($discountValue, $currency);
            }
        }
        return ''; // return a string because it's a display method
    }
    
    private function _postProcess()
    {
        if (Tools::getValue('referralprogram_import')) {
            ReferralByPhoneModule::importFromReferralProgram();
            Configuration::updateValue('REFERRALPH_FIRST_START', 1);
        }
        
        if (Tools::isSubmit('submitGeneral')) {
            Configuration::updateValue('REFERRALPH_SPONSOR_DATA', Tools::getValue('sponsor_data'));
            Configuration::updateValue('REFERRALPH_SPONSOR_GROUP', implode(",", Tools::getValue('sponsor_group')));
            Configuration::updateValue('REFERRALPH_ORDER_QUANTITY', (int)(Tools::getValue('order_quantity', 0)));
            Configuration::updateValue('REFERRALPH_NB_FORM_FRIENDS', (int)(Tools::getValue('nb_form_friends')));
            Configuration::updateValue('REFERRALPH_NB_FRIENDS', (int)(Tools::getValue('nb_friends')));
            Configuration::updateValue('REFERRALPH_GODCHILD_LIST', Tools::getValue('godchild_list'));
            Configuration::updateValue('REFERRALPH_MIN_ORDER', Tools::getValue('min_order'));
            $this->_html .= $this->displayConfirmation($this->l('General configuration updated.'));
        } elseif (Tools::isSubmit('submitCoupons')) {
            $account_voucher = array();
            $account_voucher[] = Tools::getValue('account_voucher_1');
            $account_voucher[] = Tools::getValue('account_voucher_2');
            Configuration::updateValue('REFERRALPH_ACC_VOUCHER', serialize($account_voucher));
            
            $order_voucher = array();
            $order_voucher[] = Tools::getValue('order_voucher_1');
            $order_voucher[] = Tools::getValue('order_voucher_2');
            Configuration::updateValue('REFERRALPH_ORDER_VOUCHER', serialize($order_voucher));
            
            $f_order_voucher = array();
            $f_order_voucher[] = Tools::getValue('f_order_voucher_1');
            $f_order_voucher[] = Tools::getValue('f_order_voucher_2');
            Configuration::updateValue('REFERRALPH_F_ORDER_VOUCHER', serialize($f_order_voucher));
            
            Configuration::updateValue('REFERRALPH_DISCOUNT_TYPE_ACC', (int)(Tools::getValue('discount_type_acc')));
            Configuration::updateValue('REFERRALPH_PERCENTAGE_ACC', (int)(Tools::getValue('discount_value_percentage_acc')));
            Configuration::updateValue('REFERRALPH_DISCOUNT_TYPE_FO', (int)(Tools::getValue('discount_type_fo')));
            Configuration::updateValue('REFERRALPH_PERCENTAGE_FO', (int)(Tools::getValue('discount_value_percentage_fo')));
            Configuration::updateValue('REFERRALPH_DISCOUNT_TYPE_ORD', (int)(Tools::getValue('discount_type_ord')));
            Configuration::updateValue('REFERRALPH_PERCENTAGE_ORD', (int)(Tools::getValue('discount_value_percentage_ord')));
            
            Configuration::updateValue('REFERRALPH_ORD_STATE_GENERATION', serialize(Tools::getValue('id_order_state_generation')));
            Configuration::updateValue('REFERRALPH_VOUCHER_DURATION_ACC', (int)(Tools::getValue('voucher_duration_acc')));
            Configuration::updateValue('REFERRALPH_VOUCHER_DURATION_ORD', (int)(Tools::getValue('voucher_duration_ord')));
            Configuration::updateValue('REFERRALPH_HIGHLIGHT_ACC', Tools::getValue('highlight_acc'));
            Configuration::updateValue('REFERRALPH_HIGHLIGHT_ORD', Tools::getValue('highlight_ord'));
            Configuration::updateValue('REFERRALPH_CUMULAT_ACC', Tools::getValue('cumulat_acc'));
            Configuration::updateValue('REFERRALPH_CUMULAT_ORD', Tools::getValue('cumulat_ord'));
            
            foreach (Tools::getValue('discount_value_acc') as $id_currency => $discount_value) {
                Configuration::updateValue('REFERRALPH_DISCOUNT_VALUE_ACC'.(int)($id_currency), (float)($discount_value));
            }
            foreach (Tools::getValue('discount_value_ord') as $id_currency => $discount_value) {
                Configuration::updateValue('REFERRALPH_DISCOUNT_VALUE_ORD'.(int)($id_currency), (float)($discount_value));
            }
            foreach (Tools::getValue('discount_value_fo') as $id_currency => $discount_value) {
                Configuration::updateValue('REFERRALPH_DISCOUNT_VALUE_FO'.(int)($id_currency), (float)($discount_value));
            }
            
            foreach (Language::getLanguages(false) as $lang) {
                Configuration::updateValue('REFERRALPH_VOUCHER_DESCR_ACC', array($lang['id_lang'] => Tools::getValue('voucher_descr_acc_'.(int)$lang['id_lang'])));
                Configuration::updateValue('REFERRALPH_VOUCHER_DESCR_FO', array($lang['id_lang'] => Tools::getValue('voucher_descr_fo_'.(int)$lang['id_lang'])));
                Configuration::updateValue('REFERRALPH_VOUCHER_DESCR_ORD', array($lang['id_lang'] => Tools::getValue('voucher_descr_ord_'.(int)$lang['id_lang'])));
            }
            
            Configuration::updateValue('REFERRALPH_TAX_ACC', (int)(Tools::getValue('discount_tax_acc')));
            Configuration::updateValue('REFERRALPH_TAX_ORD', (int)(Tools::getValue('discount_tax_ord')));
            
            
            $this->_html .= $this->displayConfirmation($this->l('Vouchers configuration updated.'));
        } elseif (Tools::isSubmit('submitEmail')) {
            Configuration::updateValue('REFERRALPH_EMAIL_ACC', Tools::getValue('REFERRALPH_EMAIL_ACC'));
            $this->_html .= $this->displayConfirmation($this->l('Emails configuration updated.'));
        } elseif (Tools::isSubmit('viewreferralbyphonereferralbyphone')) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminCustomers').'&id_customer='.Tools::getValue('id_sponsor').'&viewcustomer');
        }
    }

    private function _postValidation()
    {
        $this->_errors = array();
        
        if (Tools::isSubmit('submitGeneral')) {
            if (Tools::getValue('order_quantity') < 0) {
                $this->_errors[] = $this->displayError($this->l('Order quantity is required/invalid.'));
            }
            if (Tools::getValue('nb_friends') < 0) {
                $this->_errors[] = $this->displayError($this->l('Number of friends is required/invalid.'));
            }
            if (!(int)(Tools::getValue('nb_form_friends')) or Tools::getValue('nb_form_friends') < 0) {
                $this->_errors[] = $this->displayError($this->l('Number of friends is required/invalid.'));
            }
        } elseif (Tools::isSubmit('submitCoupons')) {
            if ((int)(Tools::getValue('discount_type_acc')) === 1) {
                if (!(int)(Tools::getValue('discount_value_percentage_acc')) or (int)(Tools::getValue('discount_value_percentage_acc')) < 0 or (int)(Tools::getValue('discount_value_percentage_acc')) > 100) {
                    $this->_errors[] = $this->displayError($this->l('Discount percentage is required/invalid.'));
                }
            }
             
            if ((int)(Tools::getValue('discount_type_acc')) === 2) {
                if (!is_array(Tools::getValue('discount_value_acc'))) {
                    $this->_errors[] = $this->displayError($this->l('Discount value is invalid.'));
                }
                foreach (Tools::getValue('discount_value_acc') as $id_currency => $discount_value_acc) {
                    if ($discount_value_acc == '') {
                        $this->_errors[] = $this->displayError(sprintf($this->l('Discount value for the currency #%d is empty.'), $id_currency));
                    } elseif (!Validate::isUnsignedFloat($discount_value_acc)) {
                        $this->_errors[] = $this->displayError(sprintf($this->l('Discount value for the currency #%d is invalid.'), $id_currency));
                    }
                }
            }
             
            if (!(int)(Tools::getValue('discount_type_acc')) or Tools::getValue('discount_type_acc') < 1 or Tools::getValue('discount_type_acc') > 2) {
                $this->_errors[] = $this->displayError($this->l('Discount type is required/invalid.'));
            }

            $states_valid = Tools::getValue('id_order_state_generation');
            if (!is_array($states_valid) || !sizeof($states_valid)) {
                $this->_errors[] = $this->l('You must choose the states when voucher is awarded');
            }
        }
    }
    
    private function _writeXml()
    {
        $forbiddenKey = array('submitUpdate'); // Forbidden key

        // Generate new XML data
        $newXml = '<'.'?xml version=\'1.0\' encoding=\'utf-8\' ?>'."\n";
        $newXml .= '<referralbyphone>'."\n";
        $newXml .= "\t".'<body>';
        // Making body data
        foreach (Language::getLanguages(false) as $lang) {
            if ($line = $this->putContent($newXml, 'body_paragraph_'.(int)$lang['id_lang'], Tools::getValue('body_paragraph_'.(int)$lang['id_lang']), $forbiddenKey, 'body')) {
                $newXml .= $line;
            }
        }
        
        $newXml .= "\n\t".'</body>'."\n";
        $newXml .= '</referralbyphone>'."\n";

        /* write it into the editorial xml file */
        if ($fd = @fopen($this->_xmlFile, 'w')) {
            if (!@fwrite($fd, $newXml)) {
                $this->_html .= $this->displayError($this->l('Unable to write to the xml file.'));
            }
            if (!@fclose($fd)) {
                $this->_html .= $this->displayError($this->l('Cannot close the xml file.'));
            }
        } else {
            $this->_html .= $this->displayError($this->l('Unable to update the xml file. Please check the xml file\'s writing permissions.'));
        }
    }

    public function putContent($xml_data, $key, $field, $forbidden, $section)
    {
        foreach ($forbidden as $line) {
            if ($key == $line) {
                return 0;
            }
        }
        if (!preg_match('/^'.$section.'_/i', $key)) {
            return 0;
        }
        $key = preg_replace('/^'.$section.'_/i', '', $key);
        $field = Tools::htmlentitiesDecodeUTF8(htmlspecialchars($field));
        if (!$field) {
            return 0;
        }
        return ("\n\t\t".'<'.$key.'><![CDATA['.$field.']]></'.$key.'>');
    }

    public function getContent()
    {
        $this->_html = '';
        
        if (!Configuration::get('REFERRALPH_FIRST_START') and $this->_firstStart() !== true) {
                 $this->_html .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/firstStart.tpl');
        }
        
        if (((bool)Tools::isSubmit('submitText')) == true) {
            $this->_writeXml();
        } else {
            $this->_postValidation();
            if (!sizeof($this->_errors)) {
                $this->_postProcess();
            } else {
                foreach ($this->_errors as $err) {
                    $this->_html .= $err;
                }
            }
        }
        $this->_html .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
        $this->_html .= '<div class="row">';
        $this->_html .= '<div class="tab-content col-lg-12 col-md-9">';
        $this->_html .= '<div class="tab-pane active" id="configForm">';
        $this->_html .= $this->renderForm();
        $this->_html .= '</div>';
        $this->_html .= '<div class="tab-pane" id="comfigCoupons">';
        $this->_html .= $this->renderCouponsForm();
        $this->_html .= '</div>';
        $this->_html .= '<div class="tab-pane" id="comfigEmails">';
        $this->_html .= $this->renderEmailConfig();
        $this->_html .= '</div>';
        $this->_html .= '<div class="tab-pane" id="statSponsor">';
        $this->_html .= $this->renderSponsorList();
        $this->_html .= '</div>';
        $this->_html .= '</div>';
        $this->_html .= '</div>';
        return $this->_html;
    }
    
    private function _firstStart()
    {
        if (Tools::isSubmit('submitFirstStart')) {
            $this->_postProcess();
            return true;
        }
        
        if (ReferralByPhoneModule::isNotEmpty()) {
            return true;
        }
        
        if (Module::isInstalled('referralprogram')) {
            $referralprogram = Module::getInstanceByName('referralprogram');
            $bReferralProgram = (bool)$referralprogram->active;
            $nbReferralProgram = Db::getInstance()->getValue('SELECT count(*) AS nb FROM `'._DB_PREFIX_.'referralprogram`');
        } else {
            return true;
        }
        return false;
    }

    /**
     * Return customer instance from its phone
     *
     * @param string $phone phone
     * @return Customer instance
     */
    public function getByPhone($phone)
    {
        if (!Validate::isPhoneNumber($phone)) {
            die(Tools::displayError());
        }
    
        $sql = 'SELECT c.*
                FROM `'._DB_PREFIX_.'customer` c
                LEFT JOIN `'._DB_PREFIX_.'address` ad ON (c.`id_customer` = ad.`id_customer`)        
                WHERE ad.`phone` = \''.pSQL($phone).'\'
                    '.Shop::addSqlRestriction(Shop::SHARE_CUSTOMER).'
                    and c.`is_guest` = 0';
    
        $result = Db::getInstance()->getRow($sql);

        if (!$result) {
            return false;
        }

        return $result;
    }
    
    /**
     * Hook call when cart created and updated
     * Display the discount name if the sponsor friend have one
     */
    public function hookShoppingCart($params)
    {
        if (!isset($params['cart']->id_customer)) {
            return false;
        }
        if (!($id_referralprogram = ReferralByPhoneModule::isSponsorised((int)($params['cart']->id_customer), true))) {
            return false;
        }
        $referralprogram = new ReferralByPhoneModule($id_referralprogram);
        if (!Validate::isLoadedObject($referralprogram)) {
            return false;
        }

        $cartRule = new CartRule($referralprogram->id_cart_rule);
        if (!Validate::isLoadedObject($cartRule)) {
            return false;
        }
    
        if ($cartRule->checkValidity($this->context, false, false) === true) {
            $this->smarty->assign(array('discount_display' => ReferralByPhone::displayDiscount($cartRule->reduction_percent ? $cartRule->reduction_percent : $cartRule->reduction_amount, $cartRule->reduction_percent ? 1 : 2, new Currency($params['cookie']->id_currency)), 'discount' => $cartRule));
            return $this->display(__FILE__, 'shopping-cart.tpl');
        }
        return false;
    }
    
    /**
     * Hook display on customer account page
     * Display an additional link on my-account and block my-account
     */
    public function hookCustomerAccount($params)
    {
        if (ReferralByPhoneModule::isCustomerAllowed($this->context->customer) && Configuration::get('REFERRALPH_GODCHILD_LIST')) {
               return $this->display(__FILE__, 'my-account.tpl');
        }
    }
    
    public function hookDisplayMyAccountBlock($params)
    {
        return $this->hookCustomerAccount($params);
    }

    /**
    * Hook display on form create account
    * Add an additional input on bottom for fill the sponsor's e-mail or phone address
    */
    
    public function hookDisplayCustomerAccountForm($params)
    {
        
        $this->context->controller->addJS($this->_path.'js/referralbyphone.js');
        
        if (!empty($this->context->cookie->ws_refprogram_id)) {
            $referralprogram = new ReferralByPhoneModule((int)$this->context->cookie->ws_refprogram_id);
            
            if (Validate::isLoadedObject($referralprogram)) {
                $_POST['customer_firstname'] = $referralprogram->firstname;
                $_POST['firstname'] = $referralprogram->firstname;
                $_POST['customer_lastname'] = $referralprogram->lastname;
                $_POST['lastname'] = $referralprogram->lastname;
                $_POST['email'] = $referralprogram->email;
                $_POST['email_create'] = $referralprogram->email;
                $sponsor = new Customer((int)$referralprogram->id_sponsor);
                $_POST['referralbyphone'] = $sponsor->email;
            }
        } elseif (!empty($this->context->cookie->ws_sponsor_id)) {
                $sponsor = new Customer((int)$this->context->cookie->ws_sponsor_id);
                $_POST['referralbyphone'] = ReferralByPhoneModule::getReferralCode($sponsor);
        }

        $this->context->smarty->assign(array(
            'referralbyphone_controller_url' => $this->context->link->getModuleLink('referralbyphone'),
            'sponsor_data' => Configuration::get('REFERRALPH_SPONSOR_DATA')
        ));
        
        return $this->display(__FILE__, 'authentication.tpl');
    }
    

    public function hookAdditionalCustomerFormFields($params)
    {
        $label = $this->trans(
                'Email your sponsor',
                array(),
                'Modules.Referralbyphone.Shop'
        );
    
        $formField = (new FormField())
        ->setName('referralbyphone')
        ->setType('text')
        ->setLabel($label)
        ->setRequired(false);
    
        return array($formField);
    }
    
    /**
    * Hook called on creation customer account
    * Create a discount for the customer if sponsorised
    */
    public function hookCreateAccount($params)
    {
        $sponsor = array();
        
        $sponsor_data = Configuration::get('REFERRALPH_SPONSOR_DATA');
        $account_voucher = unserialize(Configuration::get('REFERRALPH_ACC_VOUCHER'));

        $newCustomer = $params['newCustomer'];
        if (!Validate::isLoadedObject($newCustomer)) {
            return false;
        }

        if (!empty($this->context->cookie->ws_sponsor_id)) {
            $sponsor = new Customer($this->context->cookie->ws_sponsor_id);
        } else {
            $sponsorField = trim(Tools::getValue('referralbyphone'));
            
            if ($sponsor_data == 'sponsorby_email') {
                $sponsor = new Customer();
                
                if (Validate::isEmail($sponsorField)) {
                    $sponsor=$sponsor->getByEmail($sponsorField);
                } else {
                    $sponsor = new Customer(ReferralByPhoneModule::decodeReferralLink($sponsorField));
                }


                if (!Validate::isLoadedObject($sponsor) and $sponsor->email == $newCustomer->email and !ReferralByPhoneModule::isCustomerAllowed($sponsor)) {
                    return false;
                }
            
                if (Configuration::get('REFERRALPH_NB_FRIENDS') > 0) {
                    $count_friends = ReferralByPhoneModule::countFriends($sponsor->id);
                    if ($count_friends >= Configuration::get('REFERRALPH_NB_FRIENDS')) {
                        return false;
                    }
                }
            } else {
                $sponsor = $this->getByPhone($sponsorField);
            }
        }
        
        if ((int)$sponsor->id) {
            /* If the customer was not invited by the sponsor, we create the invitation dynamically */
            if (!$id_referralprogram = ReferralByPhoneModule::isEmailExists($newCustomer->email, true, false)) {
                $referralbyphone = new ReferralByPhoneModule();
                $referralbyphone->id_sponsor = (int)$sponsor->id;
                $referralbyphone->firstname = $newCustomer->firstname;
                $referralbyphone->lastname = $newCustomer->lastname;
                $referralbyphone->email = $newCustomer->email;
                
                if (!$referralbyphone->validateFields(false)) {
                    return false;
                } else {
                    $referralbyphone->save();
                }
            } else {
                $referralbyphone = new ReferralByPhoneModule((int)$id_referralprogram);
            }
            
            if ($referralbyphone->id_sponsor == $sponsor->id) {
                $referralbyphone->id_customer = (int)$newCustomer->id;
                $referralbyphone->save();
                
                $cookie = $this->context->cookie;
                
                // send notifications
                if (Configuration::get('REFERRALPH_EMAIL_ACC')) {
                    $data = array(
                            '{firstname}' => $sponsor->firstname,
                            '{lastname}' => $sponsor->lastname,
                            '{sponsored_firstname}' => $newCustomer->firstname,
                            '{sponsored_lastname}' => $newCustomer->lastname,
                            '{sponsored_email}' => $newCustomer->email,
                    );
                    Mail::Send(
                    (int)$sponsor->id_lang,
                    'referralbyphone-registration',
                    Mail::l('New registration in your referral program', (int)$sponsor->id_lang),
                    $data,
                    $sponsor->email,
                    $sponsor->firstname.' '.$sponsor->lastname,
                    (string)Configuration::get('PS_SHOP_EMAIL'),
                    (string)Configuration::get('PS_SHOP_NAME'),
                    null,
                    null,
                    dirname(__FILE__).'/mails/'
                            );
                }
                
                // create vouchers
                if ($account_voucher[0] == 1) {
                    $referralbyphone->registerDiscountForSponsor((int)$params['cookie']->id_currency, 'acc');
                    
                    $cartRule = new CartRule((int)$referralbyphone->id_cart_rule_sponsor);
                    
                    if (Validate::isLoadedObject($cartRule)) {
                        $data = array(
                                '{firstname}' => $sponsor->firstname,
                                '{lastname}' => $sponsor->lastname,
                                '{voucher_num}' => $cartRule->code,
                                '{voucher_amount}' => (Configuration::get('REFERRALPH_DISCOUNT_TYPE_ACC') == 2 ? Tools::displayPrice((float)Configuration::get('REFERRALPH_DISCOUNT_VALUE_ACC'.(int)$this->context->currency->id), (int)Configuration::get('PS_CURRENCY_DEFAULT')) : (float)Configuration::get('REFERRALPH_PERCENTAGE_ACC').'%'));
                    
                        Mail::Send(
                            (int)$sponsor->id_lang,
                            'referralbyphone-voucher',
                            Mail::l('Congratulations! New sponsor voucher!', (int)$sponsor->id_lang),
                            $data,
                            $sponsor->email,
                            $sponsor->firstname.' '.$sponsor->lastname,
                            (string)Configuration::get('PS_SHOP_EMAIL'),
                            (string)Configuration::get('PS_SHOP_NAME'),
                            null,
                            null,
                            dirname(__FILE__).'/mails/'
                        );
                    }
                }

                if ($account_voucher[1] == 1) {
                    $referralbyphone->registerDiscountForSponsored((int)$params['cookie']->id_currency, 'acc');
                    $cartRule = new CartRule((int)$referralbyphone->id_cart_rule);
                
                    if (Validate::isLoadedObject($cartRule)) {
                        $data = array(
                            '{firstname}' => $newCustomer->firstname,
                            '{lastname}' => $newCustomer->lastname,
                            '{voucher_num}' => $cartRule->code,
                            '{voucher_amount}' => (Configuration::get('REFERRALPH_DISCOUNT_TYPE_ACC') == 2 ? Tools::displayPrice((float)Configuration::get('REFERRALPH_DISCOUNT_VALUE_ACC'.(int)$this->context->currency->id), (int)Configuration::get('PS_CURRENCY_DEFAULT')) : (float)Configuration::get('REFERRALPH_PERCENTAGE_ACC').'%'));

                        Mail::Send(
                            (int)$cookie->id_lang,
                            'referralbyphone-voucher',
                            Mail::l('Congratulations! New customer voucher!', (int)$cookie->id_lang),
                            $data,
                            $newCustomer->email,
                            $newCustomer->firstname.' '.$newCustomer->lastname,
                            (string)Configuration::get('PS_SHOP_EMAIL'),
                            (string)Configuration::get('PS_SHOP_NAME'),
                            null,
                            null,
                            dirname(__FILE__).'/mails/'
                        );
                    }
                }
                return true;
            }
        }
        return false;
    }
    
    /**
     * Hook called when a order is confimed
     * display a message to customer about sponsor discount
     */
    public function hookOrderConfirmation($params)
    {

        if (version_compare(_PS_VERSION_, '1.7', '>')) {
            $params_obj_order = $params['order'];
        } else {
            $params_obj_order = $params['objOrder'];
        }
        
        if ($params_obj_order and !Validate::isLoadedObject($params_obj_order)) {
            return die($this->l('Incorrect Order object.'));
        }

        $order_voucher = unserialize(Configuration::get('REFERRALPH_ORDER_VOUCHER'));
        
        //include_once(dirname(__FILE__).'/ReferralByPhoneModule.php');
        
        $customer = new Customer((int)$params_obj_order->id_customer);
        
        //TODO firs order
        $stats = $customer->getStats();
        $nbOrdersCustomer = (int)$stats['nb_orders'] + 1; // hack to count current order
        
        $referralbyphone = new ReferralByPhoneModule(ReferralByPhoneModule::isSponsorised((int)$customer->id, true));
        
        if (!Validate::isLoadedObject($referralbyphone)) {
            return false;
        }

        $total_paid = $params_obj_order->total_paid_tax_excl;
        // TODO better
        if (Configuration::get('REFERRALPH_MIN_ORDER')) {
            if ($total_paid < Configuration::get('REFERRALPH_MIN_ORDER')) {
                return ;
            }
        }
        
        if ($order_voucher[0] == 1) {
            if (!empty($referralbyphone->id_sponsor)) {
                //$referralbyphone->registerDiscount((int)$referralbyphone->id_sponsor, 'sponsored', (int)$params['cookie']->id_currency, 'ord', $params["objOrder"]->total_paid_tax_excl);
                $this->smarty->assign(array('is_sponsor' => true));
                return $this->display(__FILE__, 'order-confirmation.tpl');
            }
        }
        if ($order_voucher[1] == 1) {
            if (!empty($referralbyphone->id_sponsor)) {
                //$referralbyphone->registerDiscount((int)$customer->id, 'sponsor', (int)$params['cookie']->id_currency, 'ord', $params["objOrder"]->total_paid_tax_excl);
                
                //TODO
                /*
                $cartRule = new CartRule((int)$referralbyphone->id_cart_rule_sponsor);
                if (!Validate::isLoadedObject($cartRule))
                    return false;
                $this->smarty->assign(array('discount' => ReferralProgram::displayDiscount($cartRule->reduction_percent ? $cartRule->reduction_percent : $cartRule->reduction_amount, $cartRule->reduction_percent ? 1 : 2, new Currency((int)$params['objOrder']->id_currency)), 'sponsor_firstname' => $sponsor->firstname, 'sponsor_lastname' => $sponsor->lastname));
                */
                $this->smarty->assign(array('is_sponsor' => false));
                return $this->display(__FILE__, 'order-confirmation.tpl');
            }
        }
    }
    /**
    * Hook display in tab AdminCustomers on BO
    * Data table with all sponsors informations for a customer
    */
    public function hookAdminCustomers($params)
    {
        //include_once(dirname(__FILE__).'/ReferralByPhoneModule.php');

        $customer = new Customer((int)$params['id_customer']);
        $sponsor = null;

        if (!Validate::isLoadedObject($customer)) {
            die($this->l('Incorrect Customer object.'));
        }

        $sponsor_ref_code = '';
        
        $friends = ReferralByPhoneModule::getSponsorFriend((int)$customer->id);
        if ($id_referralbyphone = ReferralByPhoneModule::isSponsorised((int)$customer->id, true)) {
            $referralbyphone = new ReferralByPhoneModule((int)$id_referralbyphone);
            $sponsor = new Customer((int)$referralbyphone->id_sponsor);
            $sponsor_ref_code = '['.ReferralByPhoneModule::getReferralCode($sponsor).']';
        }
        
        $customer_ref_code = ReferralByPhoneModule::getReferralCode($customer);
        $sponsor_url = ReferralByPhoneModule::getReferralLink($customer);
        
        foreach ($friends as $key => &$friend) {
            $friend['orders_count'] = sizeof(Order::getCustomerOrders($friend['id_customer']));
            $friend['date_add'] = Tools::displayDate($friend['date_add'], null, true);
            $friend['sponsored_friend_count'] = sizeof(ReferralByPhoneModule::getSponsorFriend($friend['id_customer']));
        }

        $this->smarty->assign(array(
            'friends' => $friends,
            'sponsor' => $sponsor,
            'sponsor_ref_code' => $sponsor_ref_code,
            'customer_ref_code' => $customer_ref_code,
            'sponsor_url' => $sponsor_url,
            'customer' => $customer,
            'admin_image_dir' => _PS_ADMIN_IMG_,
            'token' => Tools::getAdminToken('AdminCustomers'.(int)(Tab::getIdFromClassName('AdminCustomers')).(int)$this->context->employee->id)
        ));

        if (version_compare(_PS_VERSION_, '1.6.0', '>=') === true) {
            return $this->display(__FILE__, 'hook_customers_16.tpl');
        } else {
            return $this->display(__FILE__, 'hook_customers.tpl');
        }
    }


    /**
    * Hook called when order status changed
    * register a discount for sponsor and send him an e-mail
    */
    public function hookUpdateOrderStatus($params)
    {

        if (!Validate::isLoadedObject($params['newOrderStatus'])) {
            die($this->l('Missing parameters'));
        }
        $orderState = $params['newOrderStatus'];
        $order = new Order((int)($params['id_order']));
        if ($order and !Validate::isLoadedObject($order)) {
            die($this->l('Incorrect Order object.'));
        }
        
        $order_voucher = unserialize(Configuration::get('REFERRALPH_ORDER_VOUCHER'));
        $order_status = unserialize(Configuration::get('REFERRALPH_ORD_STATE_GENERATION'));
        
        if (!in_array((int)$orderState->id, $order_status)) {
            return false;
        }
        
        $customer = new Customer((int)$order->id_customer);
        $key_v = 'ord';
        
        $stats = $customer->getStats();
        $nb_orders = $stats['nb_orders'] + 1;
        
        if ($nb_orders == 1) {
            $key_v = 'fo';
            $order_voucher = unserialize(Configuration::get('REFERRALPH_F_ORDER_VOUCHER'));
        }

        
        $referralbyphone = new ReferralByPhoneModule(ReferralByPhoneModule::isSponsorised((int)$customer->id, true));
        if (!Validate::isLoadedObject($referralbyphone)) {
            return false;
        }

        if (Configuration::get('COUPON_TAX_ORD')) {
            $total_paid = $order->total_products_wt;
        } else {
            $total_paid = $order->total_products;
        }
                
        if (Configuration::get('REFERRALPH_MIN_ORDER')) {
            if ($total_paid < Configuration::get('REFERRALPH_MIN_ORDER')) {
                return false;
            }
        }
        
        if (!empty($referralbyphone->id_sponsor)) {
            if ($order_voucher[0] == 1) {
                $id_cart_rule =  $referralbyphone->registerDiscount((int)$referralbyphone->id_sponsor, 'sponsored', (int)$order->id_currency, $key_v, $total_paid);
                
                $cartRule = new CartRule((int)$id_cart_rule);
                $currency = new Currency((int)$order->id_currency);
                $discount_display = referralbyphone::displayDiscount((float) $cartRule->reduction_percent ? (float) $cartRule->reduction_percent : (int) $cartRule->reduction_amount, (float) $cartRule->reduction_percent ? 1 : 2, $currency);
                
                $sponsor = new Customer((int)$referralbyphone->id_sponsor);
                $data = array('{sponsored_firstname}' => $customer->firstname, '{sponsored_lastname}' => $customer->lastname, '{discount_display}' => $discount_display, '{discount_name}' => $cartRule->code);
                Mail::Send((int)$order->id_lang, 'referralbyphone-congratulations-sponsor', Mail::l('New voucher for you!', (int)$order->id_lang), $data, $sponsor->email, $sponsor->firstname.' '.$sponsor->lastname, (string)Configuration::get('PS_SHOP_EMAIL'), (string)Configuration::get('PS_SHOP_NAME'), null, null, dirname(__FILE__).'/mails/');
            }
            if ($order_voucher[1] == 1) {
                $id_cart_rule =  $referralbyphone->registerDiscount((int)$customer->id, 'sponsor', (int)$order->id_currency, $key_v, $total_paid);
                
                $cartRule = new CartRule((int)$id_cart_rule);
                $currency = new Currency((int)$order->id_currency);
                $discount_display = referralbyphone::displayDiscount((float) $cartRule->reduction_percent ? (float) $cartRule->reduction_percent : (int) $cartRule->reduction_amount, (float) $cartRule->reduction_percent ? 1 : 2, $currency);
                
                $data = array('{sponsored_firstname}' => $customer->firstname, '{sponsored_lastname}' => $customer->lastname, '{discount_display}' => $discount_display, '{discount_name}' => $cartRule->code);
                Mail::Send((int)$order->id_lang, 'referralbyphone-congratulations-friend', Mail::l('Congratulations a new friend!', (int)$order->id_lang), $data, $customer->email, $customer->firstname.' '.$customer->lastname, (string)Configuration::get('PS_SHOP_EMAIL'), (string)Configuration::get('PS_SHOP_NAME'), null, null, dirname(__FILE__).'/mails/');
            }
        }
        return false;
    }
    
    public function hookDisplayLeftColumnProduct($params)
    {
        if (ReferralByPhoneModule::isCustomerAllowed($this->context->customer)) {
            $ref_link = ReferralByPhoneModule::getReferralProductLink(Tools::getValue('id_product'));
            $this->context->smarty->assign(array('ref_link' => $ref_link));
            return $this->display(__FILE__, 'product-referral.tpl');
        }
    }
    
    public function hookProductActions($params)
    {
        if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true) {
            if (ReferralByPhoneModule::isCustomerAllowed($this->context->customer)) {
                $ref_link = ReferralByPhoneModule::getReferralProductLink(Tools::getValue('id_product'));
                $this->context->smarty->assign(array('ref_link' => $ref_link));
                return $this->display(__FILE__, 'product-referral.tpl');
            }
        }
    }
    
    public function hookActionObjectCustomerDeleteAfter($params)
    {
        //Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'referralbyphone` WHERE `id_customer`');
    }
    
    public function renderForm()
    {
        $groups = Group::getGroups($this->context->language->id);
        
        $fields_form_1 = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => $this->l("Sponsor's data:"),
                        'name' => 'sponsor_data',
                        'desc' => "Select which data required: sponsor's email or sponsor's phone",
                        'required' => true,
                        'options' => array(
                            'query' => array(
                            array(
                                'id' => 'sponsorby_email',
                                'name' => $this->l('Email')),
                            array(
                                'id' => 'sponsorby_phone',
                                'name' => $this->l('Phone')),
                            ),
                            'id' => 'id',
                            'name' => 'name'
                        )),
                        array(
                                'type' => 'select',
                                'label' => $this->l("Customers groups allowed to sponsor their friends:"),
                                'name' => 'sponsor_group[]',
                                'multiple' => true,
                                'required' => true,
                                'options' => array(
                                        'query' => $groups,
                                        'id' => 'id_group',
                                        'name' => 'name'
                                )),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Minimum number of orders a customer must place to become a sponsor'),
                        'desc' => "Use 0 to disable",
                        'name' => 'order_quantity',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Number of friends in the referral program invitation form (customer account, referral program section):'),
                        'name' => 'nb_form_friends',
                    ),
                        array(
                                'type' => 'text',
                                'label' => $this->l('Number of friends in the referral program:'),
                                'desc' => "Use 0 to disable",
                                'name' => 'nb_friends',
                        ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Minimum amount for Godchild order for generate voucher for sponsor:'),
                        'desc' => "Use 0 to disable",
                           'name' => 'min_order',
                    ),
                    array(
                            'type' => (version_compare(_PS_VERSION_, '1.6')<0) ?'radio' :'switch',
                            'label' => $this->l('Show Friends List for sposor'),
                            'name' => 'godchild_list',
                            'class' => 'presta_compab',
                            'is_bool' => true,
                            'values' => array(
                                        array(
                                                'id' => 'list_on',
                                                'value' => true,
                                                'label' => $this->l('Enabled')
                                        ),
                                        array(
                                                'id' => 'list_off',
                                                'value' => false,
                                                'label' => $this->l('Disabled')
                                        )
                                ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                    'name' => 'submitGeneral',
                    )
            ),
        );
        
        $fields_form_2 = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Conditions of the referral program'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'textarea',
                        'autoload_rte' => true,
                        'label' => $this->l('Text'),
                        'name' => 'body_paragraph',
                        'lang' => true,
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                    'name' => 'submitText',
                )
            ),
        );
        
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table =  $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitModule';
        $helper->module = $this;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'currencies' => Currency::getCurrencies(),
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
        
        $helper->override_folder = '/';
        
        return $helper->generateForm(array($fields_form_1, $fields_form_2));
    }

    public function renderCouponsForm()
    {
        $fields_form_1 = array(
                'form' => array(
                        'legend' => array(
                                'title' => $this->l('Welcome voucher'),
                                'icon' => 'icon-money'
                        ),
                        'input' => array(
                                array(
                                        'type' => 'checkbox',
                                        'label' => $this->l('Generate voucher with create godchild account for:'),
                                        'name' => 'account_voucher',
                                        'values' => array(
                                                'query' => array(
                                                        array('id' => '1', 'name' => $this->l('Sponsor'), 'val' => '1'),
                                                        array('id' => '2', 'name' => $this->l('Godchild'), 'val' => '1'),
                                                ),
                                                'id' => 'id',
                                                'name' => 'name'
                                        )
                                ),
                                array(
                                        'type' => 'text',
                                        'label' => $this->l('Voucher description'),
                                        'name' => 'voucher_descr_acc',
                                        'lang' => true,
                                ),
                                array(
                                        'type' => 'text',
                                        'label' => $this->l('Validity of the voucher (in days)'),
                                        'name' => 'voucher_duration_acc',
                                        'class' => 'fixed-width-xs',
                                        'suffix' => 'days'
                                ),
                                array(
                                        'type' => 'radio',
                                        'label' => $this->l('Voucher type :'),
                                        'name' => 'discount_type_acc',
                                        'class' => 't',
                                        'values' => array(
                                                array(
                                                        'id' => 'discount_type1',
                                                        'value' => 1,
                                                        'label' => $this->l('Voucher offering a percentage')),
                                                array(
                                                        'id' => 'discount_type2',
                                                        'value' => 2,
                                                        'label' => $this->l('Voucher offering a fixed amount (by currency)')),
                                        ),
                                ),
                                array(
                                        'type' => 'text',
                                        'label' => $this->l('Percentage'),
                                        'name' => 'discount_value_percentage_acc',
                                        'class' => 'fixed-width-xs',
                                        'suffix' => '%'
                                ),
                                array(
                                        'type' => 'discount_value',
                                        'label' =>     $this->l('Voucher amount'),
                                        'name' => 'discount_value_acc',
                                        'class' => 'discount_value_acc',
                                        'id' => 'discount_value_acc',
                                ),
                                array(
                                        'type' => 'select',
                                        'label' =>     $this->l('Voucher tax'),
                                        'name' => 'discount_tax_acc',
                                        'options' => array(
                                                'query' => array(
                                                        array('id' => 0, 'name' => $this->l('Tax excluded')),
                                                        array('id' => 1, 'name' => $this->l('Tax included'))
                                                ),
                                                'id' => 'id',
                                                'name' => 'name',
                                        ),
                                ),
                                array(
                                        'type' => (version_compare(_PS_VERSION_, '1.6')<0) ?'radio' :'switch',
                                        'label' => $this->l('Show the welcome voucher in the shopping cart'),
                                        'name' => 'highlight_acc',
                                        'class' => 'presta_compab',
                                        'is_bool' => true,
                                        'values' => array(
                                                array(
                                                        'id' => 'highlight_acc_on',
                                                        'value' => true,
                                                        'label' => $this->l('Enabled')
                                                ),
                                                array(
                                                        'id' => 'highlight_acc_off',
                                                        'value' => false,
                                                        'label' => $this->l('Disabled')
                                                )
                                        ),
                                ),
                                array(
                                        'type' => (version_compare(_PS_VERSION_, '1.6')<0) ?'radio' :'switch',
                                        'label' => $this->l('Cumulative with other vouchers'),
                                        'name' => 'cumulat_acc',
                                        'desc' => 'If enabled, a customer can use several vouchers for a same order.',
                                        'class' => 'presta_compab',
                                        'is_bool' => true,
                                        'values' => array(
                                                array(
                                                        'id' => 'cumulat_acc_on',
                                                        'value' => true,
                                                        'label' => $this->l('Enabled')
                                                ),
                                                array(
                                                        'id' => 'cumulat_acc_off',
                                                        'value' => false,
                                                        'label' => $this->l('Disabled')
                                                )
                                        ),
                                ),
                                ),
                                ),
                                );
        $order_states = OrderState::getOrderStates((int)$this->context->language->id);
        $fields_form_2 = array(
                'form' => array(
                        'legend' => array(
                                'title' => $this->l('Vouchers for referrals orders'),
                                'icon' => 'icon-money'
                        ),
                        'input' => array(
                                array(
                                        'type' => 'checkbox',
                                        'label' => $this->l('Generate voucher with first new order by referral'),
                                        'name' => 'f_order_voucher',
                                        'values' => array(
                                                'query' => array(
                                                        array('id' => '1', 'name' => $this->l('Sponsor'), 'val' => '1'),
                                                        array('id' => '2', 'name' => $this->l('Godchild'), 'val' => '1'),
                                                ),
                                                'id' => 'id',
                                                'name' => 'name'
                                        )
                                ),
                                array(
                                        'type' => 'text',
                                        'label' => $this->l('Voucher description'),
                                        'name' => 'voucher_descr_fo',
                                        'lang' => true,
                                ),
                                array(
                                        'type' => 'radio',
                                        'label' => $this->l('Voucher type :'),
                                        'name' => 'discount_type_fo',
                                        'class' => 't',
                                        'values' => array(
                                                array(
                                                        'id' => 'discount_type1',
                                                        'value' => 1,
                                                        'label' => $this->l('Voucher offering a percentage')),
                                                array(
                                                        'id' => 'discount_type2',
                                                        'value' => 2,
                                                        'label' => $this->l('Voucher offering a fixed amount (by currency)')),
                                                array(
                                                        'id' => 'discount_type3',
                                                        'value' => 3,
                                                        'label' => $this->l('% from the current order')),
                                        ),
                                ),
                                array(
                                        'type' => 'text',
                                        'label' => $this->l('Percentage'),
                                        'name' => 'discount_value_percentage_fo',
                                        'class' => 'fixed-width-xs',
                                        'suffix' => '%'
                                ),
                                array(
                                        'type' => 'discount_value',
                                        'label' => $this->l('Voucher amount'),
                                        'name' => 'discount_value_fo',
                                        'class' => 'discount_value_fo',
                                        'id' => 'discount_value_fo',
                                ),
                                array(
                                        'type' => 'checkbox',
                                        'label' => $this->l('Generate voucher with each new order by referral'),
                                        'name' => 'order_voucher',
                                        'values' => array(
                                                'query' => array(
                                                        array('id' => '1', 'name' => $this->l('Sponsor'), 'val' => '1'),
                                                        array('id' => '2', 'name' => $this->l('Godchild'), 'val' => '1'),
                                                ),
                                                'id' => 'id',
                                                'name' => 'name'
                                        )
                                ),
                                array(
                                        'type' => 'text',
                                        'label' => $this->l('Voucher description'),
                                        'name' => 'voucher_descr_ord',
                                        'lang' => true,
                                ),
                                array(
                                        'type' => 'radio',
                                        'label' => $this->l('Voucher type :'),
                                        'name' => 'discount_type_ord',
                                        'class' => 't',
                                        'values' => array(
                                                array(
                                                        'id' => 'discount_type1',
                                                        'value' => 1,
                                                        'label' => $this->l('Voucher offering a percentage')),
                                                array(
                                                        'id' => 'discount_type2',
                                                        'value' => 2,
                                                        'label' => $this->l('Voucher offering a fixed amount (by currency)')),
                                                array(
                                                        'id' => 'discount_type3',
                                                        'value' => 3,
                                                        'label' => $this->l('% from the current order')),
                                        ),
                                ),
                                array(
                                        'type' => 'text',
                                        'label' => $this->l('Percentage'),
                                        'name' => 'discount_value_percentage_ord',
                                        'class' => 'fixed-width-xs',
                                        'suffix' => '%'
                                ),
                                array(
                                        'type' => 'discount_value',
                                        'label' =>     $this->l('Voucher amount'),
                                        'name' => 'discount_value_ord',
                                        'class' => 'discount_value_ord',
                                        'id' => 'discount_value_ord',
                                ),
                                array(
                                        'type' => 'select',
                                        'label' => $this->l('Voucher is awarded when the order is'),
                                        'name' => 'id_order_state_generation[]',
                                        'multiple' => true,
                                        'options' => array(
                                                'query' => $order_states,
                                                'id' => 'id_order_state',
                                                'name' => 'name'
                                        )),
                                        array(
                                                'type' => 'text',
                                                'label' => $this->l('Validity of the voucher (in days)'),
                                                'name' => 'voucher_duration_ord',
                                                'class' => 'fixed-width-xs',
                                                'suffix' => 'day'
                                        ),
                                array(
                                        'type' => 'select',
                                        'label' =>     $this->l('Voucher tax'),
                                        'name' => 'discount_tax_ord',
                                        'options' => array(
                                                'query' => array(
                                                        array('id' => 0, 'name' => $this->l('Tax excluded')),
                                                        array('id' => 1, 'name' => $this->l('Tax included'))
                                                ),
                                                'id' => 'id',
                                                'name' => 'name',
                                        ),
                                ),
                                array(
                                        'type' => (version_compare(_PS_VERSION_, '1.6')<0) ?'radio' :'switch',
                                        'label' => $this->l('Show the voucher in the shopping cart'),
                                        'name' => 'highlight_ord',
                                        'class' => 'presta_compab',
                                        'is_bool' => true,
                                        'values' => array(
                                                array(
                                                        'id' => 'highlight_ord_on',
                                                        'value' => true,
                                                        'label' => $this->l('Enabled')
                                                ),
                                                array(
                                                        'id' => 'highlight_ord_off',
                                                        'value' => false,
                                                        'label' => $this->l('Disabled')
                                                )
                                        ),
                                ),
                                array(
                                        'type' => (version_compare(_PS_VERSION_, '1.6')<0) ?'radio' :'switch',
                                        'label' => $this->l('Cumulative with other vouchers'),
                                        'hint' => $this->l('A customer can have several active vouchers. Do you allow these vouchers to be combined on a single purchase?'),
                                        'name' => 'cumulat_ord',
                                        'class' => 'presta_compab',
                                        'desc' => 'If enabled, a customer can use several vouchers for a same order.',
                                        'is_bool' => true,
                                        'values' => array(
                                                array(
                                                        'id' => 'cumulat_ord_on',
                                                        'value' => true,
                                                        'label' => $this->l('Enabled')
                                                ),
                                                array(
                                                        'id' => 'cumulat_ord_off',
                                                        'value' => false,
                                                        'label' => $this->l('Disabled')
                                                )
                                        ),
                                ),
                                ),
                                'submit' => array(
                                        'title' => $this->l('Save'),
                                        'class' => 'btn btn-default pull-right',
                                        'name' => 'submitCoupons',
                                )
                                ),
                                );
                                    
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table =  $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitModule';
        $helper->module = $this;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
                'currencies' => Currency::getCurrencies(),
                'fields_value' => $this->getCouponFieldsValues(),
                'languages' => $this->context->controller->getLanguages(),
                'id_language' => $this->context->language->id
        );
        
        $helper->override_folder = '/';
        
        return $helper->generateForm(array($fields_form_1, $fields_form_2));
    }

    public function renderEmailConfig()
    {
        $fields_form_1 = array(
                'form' => array(
                        'legend' => array(
                                'title' => $this->l('Welcome voucher'),
                                'icon' => 'icon-money'
                        ),
                        'input' => array(
                                array(
                                        'type' => 'switch',
                                        'label' => $this->l('Send notification email to the sponsor when registering a referral'),
                                        'name' => 'REFERRALPH_EMAIL_ACC',
                                        'is_bool' => true,
                                        'values' => array(
                                                array(
                                                        'id' => 'active_on',
                                                        'value' => true,
                                                        'label' => $this->l('Enabled')
                                                ),
                                                array(
                                                        'id' => 'active_off',
                                                        'value' => false,
                                                        'label' => $this->l('Disabled')
                                                )
                                        ),
                                ),
                                ),
                                'submit' => array(
                                        'title' => $this->l('Save'),
                                        'class' => 'btn btn-default pull-right',
                                        'name' => 'submitEmail',
                        )
                ),
        );
        
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table =  $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitEmail';
        $helper->module = $this;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
                'fields_value' => $this->getConfigEmailsValues(),
        );
        
        return $helper->generateForm(array($fields_form_1));
    }
    
    public function renderSponsorList()
    {
        $sponsors = ReferralByPhoneModule::getSponsorsList();
        $fields_list = array(
                'id_sponsor' => array(
                        'title' => $this->l('ID'),
                        'type' => 'text',
                ),
                'ref_code' => array(
                        'title' => $this->l('Referral Code'),
                        'type' => 'text',
                ),
                'sponsor_name' => array(
                        'title' => $this->l('Sponsor name'),
                        'type' => 'text',
                ),
                'email' => array(
                        'title' => $this->l('Email'),
                        'type' => 'text',
                ),
                'nb_registered' => array(
                        'title' => $this->l('Registered Friends'),
                        'type' => 'text',
                ),
                /*
                'date_add'=> array(
                        'title' => $this->l('Order date'),
                        'width' => 140,
                        'type' => 'datetime',
                        'remove_onclick' => true,
                ),
                'status' => array(
                        'title' => $this->l('Status'),
                        'active' => 'status',
                        'type' => 'bool',
                        'remove_onclick' => true,
                ),
                */
        );
        
        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->actions = array('view');
        $helper->show_toolbar = true;
        $helper->module = $this;
        $helper->identifier = 'id_sponsor';
        $helper->title = $this->l('Sponsor statistics');
        $helper->table = $this->name.'referralbyphone';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        
        return $helper->generateList($sponsors, $fields_list);
    }
    
    public function getConfigFieldsValues()
    {
        
        $fields_values = array(
            'sponsor_data' => Tools::getValue('sponsor_data', Configuration::get('REFERRALPH_SPONSOR_DATA')),
               'sponsor_group[]' => Tools::getValue('sponsor_group[]', explode(',', Configuration::get('REFERRALPH_SPONSOR_GROUP'))),
               'order_quantity' => Tools::getValue('order_quantity', Configuration::get('REFERRALPH_ORDER_QUANTITY')),
                'nb_form_friends' => Tools::getValue('nb_form_friends', Configuration::get('REFERRALPH_NB_FORM_FRIENDS')),
                'nb_friends' => Tools::getValue('nb_friends', Configuration::get('REFERRALPH_NB_FRIENDS')),
            'godchild_list' => Tools::getValue('godchild_list', Configuration::get('REFERRALPH_GODCHILD_LIST')),
            'min_order' => Tools::getValue('min_order', Configuration::get('REFERRALPH_MIN_ORDER')),
        );
        
        $languages = Language::getLanguages(false);
        foreach ($languages as $lang) {
            $fields_values['body_paragraph'][$lang['id_lang']] = '';
        }
        
        // xml loading
        $xml = false;
        if (file_exists($this->_xmlFile)) {
            if ($xml = @simplexml_load_file($this->_xmlFile)) {
                foreach ($languages as $lang) {
                    $key = 'paragraph_'.$lang['id_lang'];
                    $fields_values['body_paragraph'][$lang['id_lang']] = Tools::getValue('body_paragraph_'.(int)$lang['id_lang'], (string)$xml->body->$key);
                }
            }
        }
    
        return $fields_values;
    }
    
    public function getConfigEmailsValues()
    {
        $fields_values = array(
                'REFERRALPH_EMAIL_ACC' => Tools::getValue('REFERRALPH_EMAIL_ACC', Configuration::get('REFERRALPH_EMAIL_ACC')),
        );
        
        return $fields_values;
    }
    
    public function getCouponFieldsValues()
    {
        $account_voucher = unserialize(Tools::getValue('account_voucher', Configuration::get('REFERRALPH_ACC_VOUCHER')));
        $order_voucher = unserialize(Tools::getValue('order_voucher', Configuration::get('REFERRALPH_ORDER_VOUCHER')));
        $f_order_voucher = unserialize(Tools::getValue('f_order_voucher', Configuration::get('REFERRALPH_F_ORDER_VOUCHER')));
        $id_order_state_generation = unserialize(Configuration::get('REFERRALPH_ORD_STATE_GENERATION'));
        
        $fields_values = array(
                'account_voucher_1' => $account_voucher[0],
                'account_voucher_2' => $account_voucher[1],
                
                'order_voucher_1' => $order_voucher[0],
                'order_voucher_2' => $order_voucher[1],
                
                'f_order_voucher_1' => $f_order_voucher[0],
                'f_order_voucher_2' => $f_order_voucher[1],
                
                'discount_type_acc' => Tools::getValue('discount_type_acc', Configuration::get('REFERRALPH_DISCOUNT_TYPE_ACC')),
                'discount_value_percentage_acc' => Tools::getValue('discount_value_percentage_acc', Configuration::get('REFERRALPH_PERCENTAGE_ACC')),
                'voucher_duration_acc' => Tools::getValue('voucher_duration_acc', Configuration::get('REFERRALPH_VOUCHER_DURATION_ACC')),
                'discount_type_fo' => Tools::getValue('discount_type_fo', Configuration::get('REFERRALPH_DISCOUNT_TYPE_FO')),
                'discount_value_percentage_fo' => Tools::getValue('discount_value_percentage_fo', Configuration::get('REFERRALPH_PERCENTAGE_FO')),
                'discount_type_ord' => Tools::getValue('discount_type_ord', Configuration::get('REFERRALPH_DISCOUNT_TYPE_ORD')),
                'discount_value_percentage_ord' => Tools::getValue('discount_value_percentage_ord', Configuration::get('REFERRALPH_PERCENTAGE_ORD')),
                'voucher_duration_ord' => Tools::getValue('voucher_duration_ord', Configuration::get('REFERRALPH_VOUCHER_DURATION_ORD')),
                'id_order_state_generation[]' => Tools::getValue('id_order_state_generation', $id_order_state_generation),
                'highlight_acc' => Tools::getValue('highlight_acc', Configuration::get('REFERRALPH_HIGHLIGHT_ACC')),
                'highlight_ord' => Tools::getValue('highlight_ord', Configuration::get('REFERRALPH_HIGHLIGHT_ORD')),
                'cumulat_acc' => Tools::getValue('cumulat_acc', Configuration::get('REFERRALPH_CUMULAT_ACC')),
                'cumulat_ord' => Tools::getValue('cumulat_ord', Configuration::get('REFERRALPH_CUMULAT_ORD')),
                'discount_tax_acc' => Tools::getValue('discount_tax_acc', Configuration::get('REFERRALPH_TAX_ACC')),
                'discount_tax_ord' => Tools::getValue('discount_tax_ord', Configuration::get('REFERRALPH_TAX_ORD')),
        );
        
        $languages = Language::getLanguages(false);
        foreach ($languages as $lang) {
            $fields_values['voucher_descr_acc'][$lang['id_lang']] = Tools::getValue('voucher_descr_acc_'.(int)$lang['id_lang'], Configuration::get('REFERRALPH_VOUCHER_DESCR_ACC', (int)$lang['id_lang']));
            $fields_values['voucher_descr_fo'][$lang['id_lang']] = Tools::getValue('voucher_descr_fo_'.(int)$lang['id_lang'], Configuration::get('REFERRALPH_VOUCHER_DESCR_FO', (int)$lang['id_lang']));
            $fields_values['voucher_descr_ord'][$lang['id_lang']] = Tools::getValue('voucher_descr_ord_'.(int)$lang['id_lang'], Configuration::get('REFERRALPH_VOUCHER_DESCR_ORD', (int)$lang['id_lang']));
        }
        
        $currencies = Currency::getCurrencies();
        foreach ($currencies as $currency) {
            $fields_values['discount_value_acc'][$currency['id_currency']] = Tools::getValue('discount_value_acc['.(int)$currency['id_currency'].']', Configuration::get('REFERRALPH_DISCOUNT_VALUE_ACC'.(int)$currency['id_currency']));
            $fields_values['discount_value_fo'][$currency['id_currency']] = Tools::getValue('discount_value_fo['.(int)$currency['id_currency'].']', Configuration::get('REFERRALPH_DISCOUNT_VALUE_FO'.(int)$currency['id_currency']));
            $fields_values['discount_value_ord'][$currency['id_currency']] = Tools::getValue('discount_value_ord['.(int)$currency['id_currency'].']', Configuration::get('REFERRALPH_DISCOUNT_VALUE_ORD'.(int)$currency['id_currency']));
        }
        
        
        return $fields_values;
    }
    
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJs($this->_path.'/views/js/back.js');
        }
        
        // Add bootstrap for 1.5
        if (version_compare(_PS_VERSION_, '1.6.0', '<=') === true) {
            $this->context->controller->addCSS($this->_path.'/views/css/presta15.css');
            $this->context->controller->addJs($this->_path.'/views/js/presta15.js');
        }
    }
    
    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
        $this->context->controller->addjqueryPlugin('fancybox');
        
        if (version_compare(_PS_VERSION_, '1.7', '>')) {
            $this->context->controller->registerJavascript('modules-referralbyphone', 'modules/'.$this->name.'/views/js/referralbyphone.js', ['position' => 'bottom', 'priority' => 150]);
        } else {
            $this->context->controller->addJS($this->_path.'/views/js/referralbyphone.js', 'all');
        }
        
        if (!empty($this->context->cookie->ws_sponsor_id)) {
            $sponsor = new Customer($this->context->cookie->ws_sponsor_id);
            if (!ReferralByPhoneModule::isCustomerAllowed($sponsor)) {
                unset($this->context->cookie->ws_sponsor_id);
                unset($this->context->cookie->ws_refprogram_id);
            }
        }
        
        if (Tools::getValue('ref')) {
            $ws_referral = new ReferralByPhoneModule(ReferralByPhoneModule::decodeReferralMailLink(Tools::getValue('ref')));
            
            if (Validate::isLoadedObject($ws_referral)) {
                $sponsor = new Customer($ws_referral->id_sponsor);
            } else {
                $sponsor = new Customer(ReferralByPhoneModule::decodeReferralLink(Tools::getValue('ref')));
            }
            
            if (Validate::isLoadedObject($sponsor) && ReferralByPhoneModule::isCustomerAllowed($sponsor)) {
                $this->context->cookie->ws_sponsor_id = $sponsor->id;
                $this->context->cookie->ws_refprogram_id = Validate::isLoadedObject($ws_referral) ? $ws_referral->id : '';
            }
        }
    }
    
    // new17
    public function renderWidget($hookName, array $params)
    {
        $this->smarty->assign($this->getWidgetVariables($hookName, $params));
        //echo $hookName;
        /*
        if ('displayCustomerAccountFormTop' === $hookName) {
            return $this->fetch(
                    'module:referralbyphone/views/templates/hook/authentication.tpl',
                    $this->getCacheId('referralbyphone')
            );
        }
       
        if ('displayCustomerAccountForm' === $hookName) {
            echo 'fff';
            return $this->fetch(
                    'module:referralbyphone/views/templates/hook/authentication.tpl',
                    $this->getCacheId('referralbyphone')
            );
        }
        */
    }
    
    public function getWidgetVariables($hookName, array $params)
    {
        $id_referralprogram = '';
        $email = '';
        
        $referralprogram = new ReferralByPhoneModule($id_referralprogram);
            
        if (Validate::isLoadedObject($referralprogram)) {
                $_POST['customer_firstname'] = $referralprogram->firstname;
                $_POST['firstname'] = $referralprogram->firstname;
                $_POST['customer_lastname'] = $referralprogram->lastname;
                $_POST['lastname'] = $referralprogram->lastname;
                $_POST['email'] = $referralprogram->email;
                $_POST['email_create'] = $referralprogram->email;
                $sponsor = new Customer((int)$referralprogram->id_sponsor);
                $_POST['referralbyphone'] = $sponsor->email;
        }

        return array(
            'referralbyphone_controller_url' => $this->context->link->getModuleLink('referralbyphone'),
            'sponsor_data' => Configuration::get('REFERRALPH_SPONSOR_DATA')
        );
    }
}
