<?php

class OrderController extends OrderControllerCore
{
	public function initContent()
    {
        if (Configuration::isCatalogMode()) {
            Tools::redirect('index.php');
        }

        $this->restorePersistedData($this->checkoutProcess);
        $this->checkoutProcess->handleRequest(
            Tools::getAllValues()
        );

        $presentedCart = $this->cart_presenter->present($this->context->cart);

        if (count($presentedCart['products']) <= 0 || $presentedCart['minimalPurchaseRequired']) {
            $this->redirectWithNotifications('index.php?controller=cart?action=show');
        }

        $this->checkoutProcess
            ->setNextStepReachable()
            ->markCurrentStep()
            ->invalidateAllStepsAfterCurrent();

        $this->saveDataToPersist($this->checkoutProcess);

        if (!$this->checkoutProcess->hasErrors()) {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET' && !$this->ajax) {
                return $this->redirectWithNotifications(
                    $this->checkoutProcess->getCheckoutSession()->getCheckoutURL()
                );
            }
        }

        $this->context->smarty->assign([
            'checkout_process' => new RenderableProxy($this->checkoutProcess),
            'cart' => $presentedCart,
        ]);

        parent::initContent();
        $this->setTemplate('checkout/checkout');
    }

    private function saveDataToPersist(CheckoutProcess $process)
    {
        $data = $process->getDataToPersist();
        $data['checksum'] = $this->cartChecksum->generateChecksum($this->context->cart);

        Db::getInstance()->execute(
            'UPDATE '._DB_PREFIX_.'cart SET checkout_session_data = "'.pSQL(json_encode($data)).'"
                WHERE id_cart = '.(int) $this->context->cart->id
        );
    }

    private function restorePersistedData(CheckoutProcess $process)
    {
        $rawData = Db::getInstance()->getValue(
            'SELECT checkout_session_data FROM '._DB_PREFIX_.'cart WHERE id_cart = '.(int) $this->context->cart->id
        );
        $data = json_decode($rawData, true);
        if (!is_array($data)) {
            $data = [];
        }

        $checksum = $this->cartChecksum->generateChecksum($this->context->cart);
        if (isset($data['checksum']) && $data['checksum'] === $checksum) {
            $process->restorePersistedData($data);
        }
    }
}