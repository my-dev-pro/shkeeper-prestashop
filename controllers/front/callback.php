<?php

class ShkeeperCallbackModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {

        // collect data stream
        $data = file_get_contents('php://input');
        $headers = getallheaders();

        // var_dump(apache_request_headers());
        // print_r(json_decode($data, true));
        // print_r($headers);

        // exit;

        // add transactions 
        // when complete payment update order status with "PS_OS_SHKEEPER_ACCEPTED"

        $message = null;

        // validate if the request singned by SHKeeper API
        if (! $this->isSignedRequest($headers)) {
            $message = 'Unauthorized Request!...';
            $this->response($message, 401);
        }

        $data_collected = json_decode($data, true);

        if (json_last_error() !== JSON_ERROR_NONE ) {
            $message = 'JSON: ' . json_last_error_msg();
            $this->response($message);
        }

        $externalId = (int) $data_collected['external_id'];

        // fetch order ID by external ID
        $orderId = Order::getIdByCartId($externalId);

        // terminate on Order Not Found
        if (! $orderId) {
            $message = 'Wrong Credentials!...';
            $this->response($message, 404);
        }
        
        $order = new Order($orderId);

        // collect new transactions and save data on order update
        foreach ($data_collected['transactions'] as $transaction) {
            if ($transaction['trigger']) {

                $orderPayment = new OrderPayment();
                $orderPayment->order_reference = $orderId;
                $orderPayment->id_currency = $order->id_currency;
                $orderPayment->amount = (float)$transaction['amount_fiat'];
                $orderPayment->payment_method = $this->module->name;
                $orderPayment->transaction_id = $transaction['txid'];
                $orderPayment->date_add = date('Y-m-d H:i:s');

                // Save the payment object
                if ($orderPayment->save()) {
                    // Associate the payment with the order
                    $order->addOrderPayment($orderPayment->amount, $orderPayment->payment_method, $orderPayment->transaction_id);
                }
            }
            
        }

        if ($data_collected['paid']) {
            $newOrderStatus = Configuration::get('PS_OS_PAYMENT');
            if (Configuration::get('PS_OS_SHKEEPER_ACCEPTED')) {
                $newOrderStatus = Configuration::get('PS_OS_SHKEEPER_ACCEPTED');
            }
            $order->setCurrentState($newOrderStatus);
            $order->save();
        }

        $this->response('Order status updated.', 202);

    }

    private function response(string $message, $responseCode = 200): void
    {
        header("Content-Type: application/json");
        http_response_code($responseCode);
        echo $message;
        exit;
    }

    private function isSignedRequest(array $header = [])
    {

        // terminate on empty headers
        if (empty($header)) {
            return false;
        }

        // terminate on SHKeeper NOT SET
        if (empty($header['X-Shkeeper-API-Key'])) {
            return false;
        }

        // fetch saved api
        $shkeeperKey = Configuration::get('SHKEEPER_APIKEY');

        // terminate on missy requests
        if ($shkeeperKey != $header['X-Shkeeper-API-Key']) {
            return false;
        }

        return true;
    }
}