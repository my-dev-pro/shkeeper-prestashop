<?php

class ShkeeperCallbackModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {

        // add transactions 
        // when complete payment update order status with "PS_OS_SHKEEPER_ACCEPTED"

        try {

            $message = null;
            if (empty(Tools::getValue('paymentId'))) {
                $message = $this->module->l('Oops, you are accessing wrong order ...');
            }

            $externalId = Tools::getValue('external_id');
            
            // fetch order ID by external ID
            $orderId = Order::getIdByCartId($externalId);
            $order = new Order($orderId);

            if ($orderId) {
                $orderPayment = new OrderPayment();
                $orderPayment->order_reference = $order->reference;
                $orderPayment->id_currency = $order->id_currency;
                $orderPayment->amount = (float)$paymentAmount;
                $orderPayment->payment_method = $paymentMethod;
                $orderPayment->transaction_id = $transactionId;
                $orderPayment->date_add = date('Y-m-d H:i:s');
            }

            // Save the payment object
            if ($orderPayment->save()) {
                // Associate the payment with the order
                $order->addOrderPayment($orderPayment->amount, $orderPayment->payment_method, $orderPayment->transaction_id);
            }

        } catch(Exception $exception) {

        }
    }
}