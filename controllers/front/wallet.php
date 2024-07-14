<?php

class ShkeeperWalletModuleFrontController extends ModuleFrontController
{
    public fucntion postProcess()
    {
        $currency = Tools::getIsset('currency');

        return Tools::json_encode([
            'currency' => $currency,
        ]);
        
        exit;
    }
}