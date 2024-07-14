<?php
/**
 * @author MY-Dev <mydev@my-dev.pro>
 */

// disable loading outside prestashop
if (!defined("_PS_VERSION_")) {
    exit();
}

class Shkeeper extends PaymentModule
{
    public function __construct()
    {
        $this->name = "shkeeper";
        $this->tab = "payments_gateways";
        $this->version = "1.0.0";
        $this->author = "MY-Dev";
        $this->author_uri = "https://my-dev.pro";
        $this->need_instance = 0;
        $this->is_configurable = 1;
        $this->ps_version_compliancy = [
            "min" => "1.7",
            "max" => _PS_VERSION_,
        ];

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans("SHkeeper", [], "Modules.Shkeeper.Admin");
        $this->description = $this->trans("SHKeeper Cryptocurrencies Payment Gateway", [], "Modules.Shkeeper.Admin");

        $this->confirmUninstall = $this->trans("Are you sure you want to uninstall?", [], "Modules.Shkeeper.Admin");
    }

    public function install()
    {
        // Enable module in case multiple stores enabled
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        // register payment hooks        
        return (
            parent::install()
            && $this->registerHook('PaymentOptions')
            && $this->registerHook('displayHeader')
            // && $this->registerHook('PaymentReturn')
            && Configuration::updateValue('SHKEEPER', 'shkeeper')
        ); 
    }

    /**
     * Handles module configuration page
     * @return string
     */
    public function getContent()
    {
        $output = "";

        //TODO save form values
        if (Tools::isSubmit("submit" . $this->name)) {
            $configInstruction = (string) Tools::getValue("SHKEEPER_INSTRUCTION");
            $configKey = Tools::getValue("SHKEEPER_APIKEY");
            $configURL = Tools::getValue("SHKEEPER_APIURL");

            // auto validate the URL
            $configURL = $this->addURLSchema($configURL);
            $configURL = $this->addURLSeparator($configURL);

            // check that the value is valid
            if (empty($configKey) || empty($configURL)) {
                // invalid value, show an error
                $output = $this->displayError( $this->trans("Invalid Configuration value", [], "Modules.Shkeeper.Admin" ) );
            } else {
                // value is ok, update it and display a confirmation message
                Configuration::updateValue("SHKEEPER_INSTRUCTION", $configInstruction);
                Configuration::updateValue("SHKEEPER_APIKEY", $configKey);
                Configuration::updateValue("SHKEEPER_APIURL", $configURL);
                $output = $this->displayConfirmation( $this->trans("Settings updated", [], "Modules.Shkeeper.Admin" ) );
            }
        }

        return $output . $this->renderForm();
    }

    public function renderForm()
    {
        $form = [
            "form" => [
                "legend" => [
                    "title" => $this->trans("Settings"),
                    "icon" => "icon-cogs",
                ],
                "input" => [
                    [
                        "type" => "textarea",
                        "label" => $this->trans("Instruction", [], "Modules.Shkeeper.Admin"),
                        "name" => "SHKEEPER_INSTRUCTION",
                        "desc" => "Instruction for Customer",
                        "required" => false,
                    ],
                    [
                        "type" => "text",
                        "label" => $this->trans("API Key", [], "Modules.Shkeeper.Admin"),
                        "name" => "SHKEEPER_APIKEY",
                        "desc" => "API Key",
                        "required" => true,
                    ],
                    [
                        "type" => "text",
                        "label" => $this->trans("API URL", [], "Modules.Shkeeper.Admin"),
                        "name" => "SHKEEPER_APIURL",
                        "desc" => "API URL",
                        "required" => true,
                    ],
                ],
                "submit" => [
                    "title" => $this->trans("Save", [], "Modules.Shkeeper.Admin"),
                    "class" => "btn btn-default pull-right",
                ],
            ],
        ];

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->table = $this->table;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite("AdminModules");
        $helper->currentIndex = AdminController::$currentIndex . "&" . http_build_query(["configure" => $this->name]);
        $helper->submit_action = "submit" . $this->name;

        // Default language
        $helper->default_form_language = (int) Configuration::get( "PS_LANG_DEFAULT" );

        // Load current value into the form
        $helper->fields_value["SHKEEPER_INSTRUCTION"] = Tools::getValue( "SHKEEPER_INSTRUCTION", Configuration::get("SHKEEPER_INSTRUCTION") );
        $helper->fields_value["SHKEEPER_APIKEY"] = Tools::getValue( "SHKEEPER_APIKEY", Configuration::get("SHKEEPER_APIKEY"));
        $helper->fields_value["SHKEEPER_APIURL"] = Tools::getValue( "SHKEEPER_APIURL", Configuration::get("SHKEEPER_APIURL"));

        return $helper->generateForm([$form]);
    }

    /**
     * Enable translator at backoffice
     * @return bool
     */
    public function isUsingNewTranslationSystem()
    {
        return true;
    }

    public function hookDisplayHeader()
    {
        $this->context->controller->registerJavascript('shkeeper-js', 'modules/' . $this->name . '/views/js/shkeeper.js', [
            'position' => 'bottom',
        ]);
    }

    public function hookPaymentOptions()
    {
        if (! $this->active ) {
            return;
        }

        // get currencies
        $this->smarty->assign($this->getAvailableCurrencies());

        $paymetnOptions = [
            $this->getShkeeperOptions(),
        ];

        return $paymetnOptions;
    }

    public function getAvailableCurrencies()
    {
        $instructions = Configuration::get('SHKEEPER_INSTRUCTION');
        $currencies = $this->getData('api/v1/crypto');

        return [
            'instructions' => $instructions,
            'status' => $currencies['status'],
            'currencies' => $currencies['crypto_list'],
            'get_address' => $this->trans('Get address', [], 'Module.Shkeeper.Shop'),
        ];
    }

    public function getShkeeperOptions() 
    {
        $shkeeper = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $shkeeper->setModuleName($this->name);
        $shkeeper->setCallToActionText($this->trans('Pay with Cryptocurrencies', [], 'Module.Shkeeper.Shop'));
        
        $shkeeper->setAction($this->context->link->getModuleLink($this->name, 'validation', [], true));
        $shkeeper->setAdditionalInformation($this->fetch('module:shkeeper/views/templates/front/payment_info.tpl'));

        return $shkeeper;
    }

    /**
     * Validate adding separetor directory at the end of the link
     * @param string $url
     * @return string
     */
    public function addURLSeparator(string $url): string
    {
        if (!str_ends_with($url, '/')) {
            return $url .= DIRECTORY_SEPARATOR;
        }

        return $url;
    }

    /**
     * Validate adding schema at the start of the link
     * @param string $url
     * @return string
     */
    public function addURLSchema(string $url): string
    {
        if (!str_contains($url, 'http'))
        {
            return 'https://' . $url;
        }

        return $url;
    }

    public function getData(string $url)
    {
        $headers = [
            "X-Shkeeper-Api-Key: " . Configuration::get('SHKEEPER_APIKEY'),
        ];

        $base_url = Configuration::get('SHKEEPER_APIURL');

        $options = [
            CURLOPT_URL => $base_url . $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
        ];

        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);
    }

}
