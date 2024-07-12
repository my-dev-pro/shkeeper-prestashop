<?php

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
        
        return (
            parent::install() 
            && Configuration::updateValue('MYMODULE_NAME', 'my module')
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
     * Summary of isUsingNewTranslationSystem
     * @return bool
     */
    public function isUsingNewTranslationSystem()
    {
        return true;
    }

}
