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

class AdminGiftController extends ModuleAdminController
{
    public $msg = 0;
    public function __construct()
    {
        parent::__construct();
        $this->table = 'gift_card';
        $this->className = 'Gift';
        $this->identifier = 'id_gift_card';
        $this->deleted = false;
        $this->bootstrap = true;
        $this->shopLinkType = 'shop';
        $this->addRowAction('delete');
        $this->bulk_actions = array('delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?')));
    }

    public function renderList()
    {
        $stock = new StockAvailable();
        $this->addRowAction('view');
        $this->addRowAction('delete');
        $id_lang = $this->context->employee->id_lang;
        $gift = Gift::getAllCards($id_lang);

        $this->context->smarty->assign(array(
            'Gift_Card'     => $gift,
            'currentIndex'  => self::$currentIndex,
            'currentToken'  => $this->token,
            'msg'           => $this->msg,
            'stock_avail'   => $stock,
            'version'       => _PS_VERSION_,
            )
        );
        parent::renderList();
        return $this->context->smarty->fetch(dirname(__FILE__).'/../../views/templates/admin/_config/helpers/list/list.tpl');
    }

    public function initProcess()
    {
        $card = new Gift();
        if (Tools::isSubmit('deleteGift')) {
            $id_gift_card = (int)Tools::getValue('id_gift_card');
            $id_product = (int)Tools::getValue('id_product');
            $card->deleteCard($id_gift_card, $id_product);
            $this->msg = 2;
        }
        if (Tools::isSubmit('updateGift')) {
            $id_gift_card = (int)Tools::getValue('id_gift_card');
            $id_product = (int)Tools::getValue('id_product');
            AdminController::$currentIndex = 'index.php?controller=AdminCreateGift&token='.Tools::getAdminTokenLite('AdminCreateGift');
            Tools::redirectAdmin(AdminController::$currentIndex.'&Edit&id_gift_card='.$id_gift_card.'&id_product='.$id_product);
        }
        parent::initProcess();
    }
}
