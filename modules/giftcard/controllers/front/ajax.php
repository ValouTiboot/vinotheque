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

header('Content-type: text/javascript');
class GiftCardAjaxModuleFrontController extends ModuleFrontController
{
    public function __construct()
    {
        parent::__construct();
        $this->context = Context::getContext();
    }

    public function initContent()
    {
        parent::initContent();
        $action = Tools::getValue('action');
        if ($action == 'ProductExists') {
            $id_product = Tools::getValue('id_product');
            $id_product = Tools::jsonDecode($id_product);
            if (Gift::isExists($id_product)) {
                $html = $id_product;
            } else {
                $html = 0;
            }
            echo $html;
        }

        if (true == Tools::version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            if ($action == 'get_gift_price') {
                header('Content-Type: application/json');
                $this->ajaxDie(Tools::jsonEncode(array(
                    'gift_prices' => $this->module->hookdisplayProductButtons(),
                )));
            } elseif ($action == 'get_gift_type') {
                $gift_type = '';
                $id_product = (int)Tools::getValue('id_product');
                if ($id_product) {
                    $gift_type = Gift::getCardValue($id_product)? Gift::getCardValue($id_product)['value_type'] : '';
                }
                $this->ajaxDie(Tools::jsonEncode(array('gift_type' => $gift_type)));
            }
        }
        die();
    }
}
