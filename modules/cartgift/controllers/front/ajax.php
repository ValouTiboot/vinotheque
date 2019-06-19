<?php



class CartgiftAjaxModuleFrontController extends ModuleFrontController
{
	public function postProcess()
	{
		parent::postProcess();

		if (Tools::isSubmit('action') && Tools::getValue('action') == 'giftMessage')
			return $this->ajaxProcessGiftMessage();
	}

	public function ajaxProcessGiftMessage()
	{
		if (Tools::isSubmit('action'))
		{
			$ret = Db::getInstance()->update('cart', array(
				'gift' => Tools::getIsset('gift') ? 1 : 0,
				'gift_message' => pSQL(Tools::getValue('gift_message')),
				),
				"id_cart='" . pSQL($this->context->cart->id) . "'"
			);

			if ($ret)
				$msg = $this->l('Message saved.','ajax');
			else
				$msg = $this->l('An error occured.','ajax');

			die(Tools::jsonEncode(array('msg' => $msg)));
		}
	}
}