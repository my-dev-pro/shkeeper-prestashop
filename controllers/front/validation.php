<?php
class ShkeeperValidationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        // Generate QR Code and return URL
        $amount = (float) $this->context->cart->getOrderTotal(true, Cart::BOTH);
        $qrCodeUrl = $this->generateQrCode($amount);

        $this->context->smarty->assign([
            'qr_code_url' => $qrCodeUrl,
        ]);

        $this->setTemplate('module:shkeeper/views/templates/front/payment_return.html.twig');
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