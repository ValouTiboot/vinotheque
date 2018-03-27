<?php
/**
 *  Order Fees
 *
 *  @author    motionSeed <ecommerce@motionseed.com>
 *  @copyright 2017 motionSeed. All rights reserved.
 *  @license   https://www.motionseed.com/en/license-module.html
 */
class PaymentModule extends PaymentModuleCore
{
    /*
    * module: orderfees
    * date: 2018-03-27 17:12:42
    * version: 1.8.9
    */
    public function formatProductAndVoucherForEmail($content)
    {
        $cart_rules = $this->context->cart->getCartRules();
        foreach ($cart_rules as $cart_rule) {
            if ($cart_rule['obj']->is_fee > 0) {
                $pattern = '#' . Tools::displayError('Voucher name:') . ' '
                    . preg_quote($cart_rule['obj']->name) . '(.*)(555454">)(\s*)(?:-)?(.*?)(</font>)#s';
                $replace = $cart_rule['obj']->name . '$1$2$3$4$5';
                $content = preg_replace($pattern, $replace, $content);
            }
        }
        return $content;
    }
    /*
    * module: orderfees
    * date: 2018-03-27 17:12:42
    * version: 1.8.9
    */
    protected function getEmailTemplateContent($template_name, $mail_type, $var)
    {
        if (strpos($template_name, 'order_conf_cart_rules') === 0) {
            $cart_rules = $this->context->cart->getCartRules();
            foreach ($cart_rules as $cart_rule) {
                if ($cart_rule['obj']->is_fee > 0) {
                    foreach ($var as &$v) {
                        if ($v['voucher_name'] == $cart_rule['obj']->name) {
                            $v['voucher_reduction'] = str_replace('-', '', $v['voucher_reduction']);
                        }
                    }
                }
            }
        }
        return parent::getEmailTemplateContent($template_name, $mail_type, $var);
    }
    
    /*
    * module: orderfees
    * date: 2018-03-27 17:12:42
    * version: 1.8.9
    */
    public function display($file, $template, $cache_id = null, $compile_id = null)
    {
        $content = parent::display($file, $template, $cache_id, $compile_id);
        
        $templates = explode(',', Configuration::get('MS_ORDERFEES_PAYMENT_TPLS'));
        
        if (in_array($template, $templates)) {
            $items = Db::getInstance()->executeS(
                'SELECT cr.*
                FROM '._DB_PREFIX_.'cart_rule cr
                INNER JOIN '._DB_PREFIX_.'cart_rule_payment crp
                    ON cr.id_cart_rule = crp.id_cart_rule 
                        AND cr.is_fee > 0 
                        AND cr.payment_restriction = 1
                        AND cr.active = 1
                INNER JOIN '._DB_PREFIX_.'module m
                    ON m.id_module = crp.id_module
                        AND m.active = 1
                        AND m.name = \'' . pSQL($this->name) . '\''
            );
            if ($items) {
                $module = Module::getInstanceByName('orderfees');
                $cart_rules = array();
                $total = 0;
                $shop_is_active = Shop::isFeatureActive();
                
                foreach ($items as $item) {
                    if ($item['shop_restriction'] && $shop_is_active) {
                        $id_shop = Db::getInstance()->getValue(
                            'SELECT id_shop FROM '._DB_PREFIX_.'cart_rule_shop
                                WHERE id_cart_rule = '.(int)$item['id_cart_rule'].'
                                    AND id_shop = '.(int)Context::getContext()->shop->id
                        );
                        
                        if (!$id_shop) {
                            return $content;
                        }
                    }
                    
                    $item['obj'] = new CartRule($item['id_cart_rule']);
                    
                    $is_fee = $item['obj']->is_fee;
                    $item['obj']->is_fee &= ~$module->getConstant('IN_SHIPPING');
                    
                    $total += $item['obj']->getContextualValue(
                        true,
                        $this->context,
                        CartRule::FILTER_ACTION_ALL_NOCAP
                    );
                    
                    $item['obj']->is_fee = $is_fee;
                    
                    $cart_rules[] = $item;
                }
                
                Hook::exec('actionPaymentModuleDisplay', array(
                    'object' => &$this,
                    'total' => &$total,
                    'cart_rules' => $cart_rules,
                    'file' => $file,
                    'template' => $template
                ));
                
                $this->context->smarty->assign(array(
                    'total' => $total,
                    'cart_rules' => $cart_rules,
                    'module' => $module,
                    'display_method' => Configuration::get('MS_ORDERFEES_PAYMENT_DISPLAY_METHOD')
                ));
                
                $content = str_replace(
                    '</a>',
                    $module->display('orderfees', 'payment.tpl') . '</a>',
                    $content
                );
            }
        }
        
        return $content;
    }
}
