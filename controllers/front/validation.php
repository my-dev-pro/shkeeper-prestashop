<?php
class ShkeeperValidationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $cart = $this->context->cart;

        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');

            return;
        }

        // Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'shkeeper') {
                $authorized = true;
                break;
            }
        }

        if (!$authorized) {
            exit($this->trans('This payment method is not available.', [], 'Modules.Shkeeper.Shop'));
        }

        $customer = new Customer($cart->id_customer);

        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');

            return;
        }

        $apiurl = Tools::getValue('SHKEEPER_APIURL');


        // Generate QR Code and return URL
        // $amount = (float) $cart->getOrderTotal(true, Cart::BOTH);
        // $qrCodeUrl = $this->generateQrCode($amount);

        $this->context->smarty->assign([
            // 'amount' => 15,
            // 'qr_code_url' => $qrCodeUrl,
        ]);

        // $this->setTemplate('module:shkeeper/views/templates/front/payment_return.html.twig');
    }

    private function generateQrCode($amount)
    {
        // Generate the QR code URL using CURL
        $data = ['amount' => $amount];
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode(json_encode($data)));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $qrCodeUrl = curl_exec($ch);

        curl_close($ch);

        return $qrCodeUrl;
    }
}