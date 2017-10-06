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

if (!defined('_PS_VERSION_')) {
    exit;
}
    

class ReferralByPhoneModule extends ObjectModel
{
    public $id_sponsor;
    public $email;
    public $lastname;
    public $firstname;
    public $id_customer;
    public $id_cart_rule;
    public $id_cart_rule_sponsor;
    public $date_add;
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'referralbyphone',
        'primary' => 'id_referralbyphone',
        'fields' => array(
            'id_sponsor' =>          array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'email' =>               array('type' => self::TYPE_STRING, 'validate' => 'isEmail', 'required' => true, 'size' => 255),
            'lastname' =>            array('type' => self::TYPE_STRING, 'validate' => 'isName', 'required' => true, 'size' => 128),
            'firstname' =>           array('type' => self::TYPE_STRING, 'validate' => 'isName', 'required' => true, 'size' => 128),
            'id_customer' =>         array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_cart_rule' =>        array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_cart_rule_sponsor' =>array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'date_add' =>            array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' =>            array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );
    
    public static function isNotEmpty()
    {
        Db::getInstance()->ExecuteS('SELECT 1 FROM `'._DB_PREFIX_.'referralbyphone`');
        return (bool)Db::getInstance()->NumRows();
    }
    
    public static function importFromReferralProgram()
    {
        @Db::getInstance()->Execute('
            INSERT INTO `'._DB_PREFIX_.'referralbyphone` (id_sponsor, email, lastname, firstname, id_customer, id_cart_rule, id_cart_rule_sponsor, date_add, date_upd)
            SELECT id_sponsor, email, lastname, firstname, id_customer, id_cart_rule, id_cart_rule_sponsor, date_add, date_upd FROM `'._DB_PREFIX_.'referralprogram`');
    }

    public static function getDiscountPrefix()
    {
        return 'SP';
    }

    public function registerDiscountForSponsor($id_currency, $type_voucher)
    {
        if ((int)$this->id_cart_rule_sponsor > 0) {
            return false;
        }
        return $this->registerDiscount((int)$this->id_sponsor, 'sponsor', (int)$id_currency, $type_voucher);
    }

    public function registerDiscountForSponsored($id_currency, $type_voucher)
    {
        if (!(int)$this->id_customer || (int)$this->id_cart_rule > 0) {
            return false;
        }
        return $this->registerDiscount((int)$this->id_customer, 'sponsored', (int)$id_currency, $type_voucher);
    }

    public function registerDiscount($id_customer, $register = false, $id_currency = 0, $type_voucher = 'ord', $total_to_pay = 0)
    {
        $configurations = Configuration::getMultiple(array('REFERRALPH_TAX_ACC', 'REFERRALPH_TAX_ORD', 'REFERRALPH_CUMULAT_ACC', 'REFERRALPH_CUMULAT_ORD', 'REFERRALPH_DISCOUNT_TYPE_ACC', 'REFERRALPH_PERCENTAGE_ACC', 'REFERRALPH_VOUCHER_DURATION_ACC', 'REFERRALPH_DISCOUNT_VALUE_ACC'.(int)$id_currency, 'REFERRALPH_HIGHLIGHT_ACC', 'REFERRALPH_HIGHLIGHT_ORD', 'REFERRALPH_DISCOUNT_TYPE_ORD', 'REFERRALPH_VOUCHER_DURATION_ORD', 'REFERRALPH_PERCENTAGE_ORD', 'REFERRALPH_DISCOUNT_VALUE_ORD'.(int)$id_currency, 'REFERRALPH_DISCOUNT_TYPE_FO', 'REFERRALPH_VOUCHER_DURATION_FO', 'REFERRALPH_PERCENTAGE_FO', 'REFERRALPH_DISCOUNT_VALUE_FO'.(int)$id_currency));
        
        $cartRule = new CartRule();
        $currency = new Currency((int)$id_currency);
        
        switch ($type_voucher) {
            case 'acc':
                $cartRule->name = Configuration::getInt('REFERRALPH_VOUCHER_DESCR_ACC');
                if ($configurations['REFERRALPH_DISCOUNT_TYPE_ACC'] == '1') {
                    $cartRule->reduction_percent = (float)$configurations['REFERRALPH_PERCENTAGE_ACC'];
                } elseif ($configurations['REFERRALPH_DISCOUNT_TYPE_ACC'] == '2' && isset($configurations['REFERRALPH_DISCOUNT_VALUE_ACC'.(int)$id_currency])) {
                    $cartRule->reduction_amount = (float)$configurations['REFERRALPH_DISCOUNT_VALUE_ACC'.(int)$id_currency];
                    $cartRule->reduction_tax = (int)$configurations['REFERRALPH_TAX_ACC'];
                } elseif ($configurations['REFERRALPH_DISCOUNT_TYPE_ACC'] == '3') {
                    $gdOrder = $total_to_pay*$configurations['REFERRALPH_PERCENTAGE_ACC']/100;
                    $cartRule->reduction_amount = (int)$gdOrder;
                    $cartRule->reduction_tax = (int)$configurations['REFERRALPH_TAX_ACC'];
                }
                $cartRule->date_to = date('Y-m-d H:i:s', time() + (int)$configurations['REFERRALPH_VOUCHER_DURATION_ACC']*24*60*60);
                $cartRule->highlight = $configurations['REFERRALPH_HIGHLIGHT_ACC'];
                $cartRule->cart_rule_restriction = !(int)$configurations['REFERRALPH_CUMULAT_ACC'];
 
                break;
            case 'fo':
                $cartRule->name = Configuration::getInt('REFERRALPH_VOUCHER_DESCR_FO');
                if ($configurations['REFERRALPH_DISCOUNT_TYPE_FO'] == '1') {
                    $cartRule->reduction_percent = (float)$configurations['REFERRALPH_PERCENTAGE_FO'];
                } elseif ($configurations['REFERRALPH_DISCOUNT_TYPE_FO'] == '2' && isset($configurations['REFERRALPH_DISCOUNT_VALUE_FO'.(int)$id_currency])) {
                    $cartRule->reduction_amount = (float)$configurations['REFERRALPH_DISCOUNT_VALUE_FO'.(int)$id_currency];
                    $cartRule->reduction_tax = (int)$configurations['REFERRALPH_TAX_ORD'];
                } elseif ($configurations['REFERRALPH_DISCOUNT_TYPE_FO'] == '3') {
                    $gdOrder = $total_to_pay*$configurations['REFERRALPH_PERCENTAGE_FO']/100;
                    if ($currency->decimals) {
                        $cartRule->reduction_amount = round($gdOrder, 2);
                    } else {
                        $cartRule->reduction_amount = (int)$gdOrder;
                    }
                    $cartRule->reduction_tax = (int)$configurations['REFERRALPH_TAX_ORD'];
                }
                $cartRule->date_to = date('Y-m-d H:i:s', time() + (int)$configurations['REFERRALPH_VOUCHER_DURATION_ORD']*24*60*60);
                $cartRule->highlight = $configurations['REFERRALPH_HIGHLIGHT_ORD'];
                $cartRule->cart_rule_restriction = !(int)$configurations['REFERRALPH_CUMULAT_ORD'];
                                
                break;
            case 'ord':
                $cartRule->name = Configuration::getInt('REFERRALPH_VOUCHER_DESCR_ORD');
                if ($configurations['REFERRALPH_DISCOUNT_TYPE_ORD'] == '1') {
                    $cartRule->reduction_percent = (float)$configurations['REFERRALPH_PERCENTAGE_ORD'];
                } elseif ($configurations['REFERRALPH_DISCOUNT_TYPE_ORD'] == '2' && isset($configurations['REFERRALPH_DISCOUNT_VALUE_ORD'.(int)$id_currency])) {
                    $cartRule->reduction_amount = (float)$configurations['REFERRALPH_DISCOUNT_VALUE_ORD'.(int)$id_currency];
                    $cartRule->reduction_tax = (int)$configurations['REFERRALPH_TAX_ORD'];
                } elseif ($configurations['REFERRALPH_DISCOUNT_TYPE_ORD'] == '3') {
                    $gdOrder = $total_to_pay*$configurations['REFERRALPH_PERCENTAGE_ORD']/100;
                    if ($currency->decimals) {
                        $cartRule->reduction_amount = round($gdOrder, 2);
                    } else {
                        $cartRule->reduction_amount = (int)$gdOrder;
                    }
                    $cartRule->reduction_tax = (int)$configurations['REFERRALPH_TAX_ORD'];
                }
                $cartRule->date_to = date('Y-m-d H:i:s', time() + (int)$configurations['REFERRALPH_VOUCHER_DURATION_ORD']*24*60*60);
                $cartRule->highlight = $configurations['REFERRALPH_HIGHLIGHT_ORD'];
                $cartRule->cart_rule_restriction = !(int)$configurations['REFERRALPH_CUMULAT_ORD'];
              
                break;
        }
        
        $cartRule->quantity = 1;
        $cartRule->quantity_per_user = 1;
        $cartRule->date_from = date('Y-m-d H:i:s', time());
        //$cartRule->date_to = date('Y-m-d H:i:s', time() + 31536000); // + 1 year
        $cartRule->code = $this->getDiscountPrefix().Tools::passwdGen(6);
        if (empty($cartRule->name)) {
            $cartRule->name[Configuration::get('PS_LANG_DEFAULT')] = 'test name';
        }
        $cartRule->id_customer = (int)$id_customer;
        $cartRule->reduction_currency = (int)$id_currency;

        if ($cartRule->add()) {
            if ($register != false) {
                if ($register == 'sponsor') {
                    $this->id_cart_rule_sponsor = (int)$cartRule->id;
                } elseif ($register == 'sponsored') {
                    $this->id_cart_rule = (int)$cartRule->id;
                }
                $this->save();
                return $cartRule->id;
            }
            return true;
        }
        return false;
    }

    /**
      * Return sponsored friends
      *
      * @return array Sponsor
      */
    public static function getSponsorFriend($id_customer, $restriction = false)
    {
        if (!(int)($id_customer)) {
            return array();
        }

        $query = '
        SELECT s.*
        FROM `'._DB_PREFIX_.'referralbyphone` s
        WHERE s.`id_sponsor` = '.(int)$id_customer;
        if ($restriction) {
            if ($restriction == 'pending') {
                $query.= ' AND s.`id_customer` = 0';
            } elseif ($restriction == 'subscribed') {
                $query.= ' AND s.`id_customer` != 0';
            }
        }

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
    }

    /**
      * Return if a customer is sponsorised
      *
      * @return boolean
      */
    public static function isSponsorised($id_customer, $getId = false)
    {
        $result = Db::getInstance()->getRow('
        SELECT s.`id_referralbyphone`
        FROM `'._DB_PREFIX_.'referralbyphone` s
        WHERE s.`id_customer` = '.(int)$id_customer);
        
        if (isset($result['id_referralbyphone']) && $getId === true) {
            return (int)$result['id_referralbyphone'];
        }

        return isset($result['id_referralbyphone']);
    }

    public static function isSponsorFriend($id_sponsor, $id_friend)
    {
        if (!(int)($id_sponsor) || !(int)($id_friend)) {
            return false;
        }
    
        $result = Db::getInstance()->getRow('
        SELECT s.`id_referralbyphone`
        FROM `'._DB_PREFIX_.'referralbyphone` s
        WHERE s.`id_sponsor` = '.(int)($id_sponsor).' AND s.`id_referralbyphone` = '.(int)($id_friend));

        return isset($result['id_referralbyphone']);
    }
    
    public static function displayDiscount($discountValue, $discountType, $currency = false)
    {
        if ((float)$discountValue and (int)$discountType) {
            if ($discountType == 1) {
                return $discountValue.chr(37); // asCII #37 --> % (percent)
            } elseif ($discountType == 2) {
                return Tools::displayPrice($discountValue, $currency);
            } elseif ($discountType == 3) {
                return $discountValue.chr(37);
            }
        }
        return ''; // return a string because it's a display method
    }
    
    /**
      * Return if an email is already register
      *
      * @return boolean OR int idreferralbyphone
      */
    public static function isEmailExists($email, $getId = false, $checkCustomer = true)
    {
        if (empty($email) || !Validate::isEmail($email)) {
            die(Tools::displayError('The email address is invalid.'));
        }

        if ($checkCustomer === true && Customer::customerExists($email)) {
            return false;
        }
        $result = Db::getInstance()->getRow('
        SELECT s.`id_referralbyphone`
        FROM `'._DB_PREFIX_.'referralbyphone` s
        WHERE s.`email` = \''.pSQL($email).'\'');
        if ($getId) {
            return (int)$result['id_referralbyphone'];
        }
        return isset($result['id_referralbyphone']);
    }
    
    public static function isPhoneExists($phone, $getId = false)
    {
        if (empty($phone) || !Validate::isPhoneNumber($phone)) {
            die(Tools::displayError('The phone address is invalid.'));
        }
    
        $sql = 'SELECT c.*
                FROM `'._DB_PREFIX_.'customer` c
                LEFT JOIN `'._DB_PREFIX_.'address` ad ON (c.`id_customer` = ad.`id_customer`)        
                WHERE ad.`phone` = \''.pSQL($phone).'\'
                    '.Shop::addSqlRestriction(Shop::SHARE_CUSTOMER).'
                    AND c.`is_guest` = 0';
    
        $result = Db::getInstance()->getRow($sql);
        if ($getId) {
            return (int)$result['id_customer'];
        }
        return isset($result['id_customer']);
    }
    
    public static function getAvailableSponsors($id_customer, $filter)
    {
        $allowed_groups = Configuration::get('REFERRALPH_SPONSOR_GROUP');
        $result = array();
        
        $query = '
            SELECT DISTINCT c.`id_customer`, c.`firstname`, c.`lastname`, c.`email`
            FROM `'._DB_PREFIX_.'customer` AS c
            JOIN `'._DB_PREFIX_.'customer_group` AS cg USING (id_customer)
            WHERE c.deleted = 0
            AND id_customer != '.(int)$id_customer.'
            AND (
                c.`id_customer` = '.(int)$filter.'
                OR c.`firstname` LIKE "%'.pSQL($filter).'%"
                OR c.`lastname` LIKE "%'.pSQL($filter).'%"
                OR c.`email` LIKE "%'.pSQL($filter).'%"
            )
            AND ('.
                    (!empty($allowed_groups) ? '
                (
                    id_group IN ('.$allowed_groups.')
                ) OR ' : '').'
            )';
        $result = Db::getInstance()->ExecuteS($query);
        
        return $result;
    }
    
    public static function getSponsorsList()
    {
        $allowed_groups = Configuration::get('REFERRALPH_SPONSOR_GROUP');
        $result = array();
    
        $query = '
            SELECT DISTINCT `id_sponsor`, CONCAT(c.`firstname`, " ", c.`lastname`) as sponsor_name, c.`email`, SUM(nb_registered) AS nb_registered
            FROM (
                SELECT id_sponsor, COUNT(distinct rf.id_referralbyphone) AS nb_registered
                FROM `'._DB_PREFIX_.'referralbyphone` AS rf
                GROUP BY id_sponsor
            ) AS tab
            JOIN `'._DB_PREFIX_.'customer` AS c ON (c.id_customer=id_sponsor)
            GROUP BY id_sponsor
            ORDER BY id_sponsor';
        
        $result = Db::getInstance()->ExecuteS($query);
        
        foreach ($result as &$sppnsor) {
            $customer = new Customer((int)($sppnsor['id_sponsor']));
            $sppnsor['ref_code'] = ReferralByPhoneModule::getReferralCode($customer);
        }
        
        return $result;
    }
    
   
    public static function countFriends($id_sponsor)
    {
        $query = '
            SELECT COUNT(distinct rf.id_referralbyphone) AS nb_registered
                FROM `'._DB_PREFIX_.'referralbyphone` AS rf
                WHERE id_sponsor ='.(int)$id_sponsor.'
            ';
    
        $result = Db::getInstance()->getValue($query);
    
        return $result;
    }
    
    public static function isCustomerAllowed($customer)
    {
        if (Validate::isLoadedObject($customer)) {
            if (Configuration::get('REFERRALPH_SPONSOR_GROUP')) {
                $allowed_groups = explode(',', Configuration::get('REFERRALPH_SPONSOR_GROUP'));
                $customer_groups = $customer->getGroups();
                return sizeof(array_intersect($allowed_groups, $customer_groups)) > 0;
            }
        }
        return false;
    }
    
    /*
     * Work with Sposor ID
     */
    
    public static function getReferralCode($customer)
    {
        return date('m', strtotime($customer->date_add)).$customer->id.date('d', strtotime($customer->date_add));
    }
    
    public static function getReferralLink($customer)
    {
        $context = Context::getContext();
        $ref_code = self::getReferralCode($customer);
        return $context->link->getPageLink('index', true, defined('_PS_ADMIN_DIR_') ? $customer->id_lang : $context->language->id, 'ref='.$ref_code);
    }
    
    public static function getReferralProductLink($id_product)
    {
        $context = Context::getContext();
        $link = $context->link->getProductLink($id_product);
        return $link.(strpos($link, '?') !== false ? '&' : '?').'ref='.self::getReferralCode($context->customer);
    }
    
    public function getReferralMailLink()
    {
        $context = Context::getContext();
        $code = 'm'.date('d', strtotime($this->date_add)).$this->id.date('m', strtotime($this->date_add));
        return $context->link->getPageLink('index', true, $context->language->id, 'ref='.$code);
    }
    
    public static function decodeReferralLink($value)
    {
        $id_customer = Tools::substr($value, 2, -2);
        $date_add =  Tools::substr($value, 0, 2).'-'. Tools::substr($value, -2);
        $query = '
            SELECT id_customer
            FROM `'._DB_PREFIX_.'customer`
            WHERE `id_customer` = '.(int)$id_customer.'
            AND `date_add` LIKE \'%'.pSQL($date_add) . '%\'';
        $result = Db::getInstance()->getRow($query);
        return (int)$result['id_customer'];
    }
    
    public static function decodeReferralMailLink($value)
    {
        $id_ref_progr = Tools::substr($value, 3, -2);
        $date_add = Tools::substr($value, -2) . '-' . Tools::substr($value, 1, 2);
        $query = '
            SELECT id_referralbyphone
            FROM `'._DB_PREFIX_.'referralbyphone`
            WHERE `id_referralbyphone` = '.(int)$id_ref_progr.'
            AND `date_add` LIKE \'%'.pSQL($date_add) . '%\'';
        $result = Db::getInstance()->getRow($query);
        return (int)$result['id_referralbyphone'];
    }
}
