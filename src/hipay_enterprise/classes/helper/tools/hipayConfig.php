<?php

/**
 * 2017 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.wallet@hipay.com>
 * @copyright 2017 HiPay
 * @license   https://github.com/hipay/hipay-wallet-sdk-prestashop/blob/master/LICENSE.md
 */

require_once(dirname(__FILE__) . '/../apiHandler/ApiHandler.php');


class HipayConfig {

    private $jsonFilesPath;
    private $context;
    private $configHipay = array();

    public function __construct($module_instance) {
        $this->context = Context::getContext();
        $this->module = $module_instance;
        $this->jsonFilesPath = _PS_ROOT_DIR_ . _MODULE_DIR_ . $this->module->name . "/classes/helper/paymentConfigFiles/";
    }

    /**
     * return config array
     * @return type
     */
    public function getConfigHipay() {

        if (empty($this->configHipay)) {
            $this->initConfigHiPay();
        }

        $this->updateConfig();
        return $this->configHipay;
    }

    private function updateConfig() {

        $this->module->getLogs()->logsHipay('---- >> function updateConfig << --------');

        $configFields["payment"]["credit_card"] = $this->insertPaymentsConfig("creditCard/");
        $configFields["payment"]["local_payment"] = $this->insertPaymentsConfig("local/");

        $this->module->getLogs()->logsHipay(print_r($configFields, true));

        $localkeys = array_diff(array_keys($configFields["payment"]["local_payment"]), array_keys($this->configHipay["payment"]["local_payment"]));
        $cckeys = array_diff(array_keys($configFields["payment"]["credit_card"]), array_keys($this->configHipay["payment"]["credit_card"]));

        foreach ($cckeys as $key) {
            $this->configHipay["payment"]["credit_card"][$key] = $configFields["payment"]["credit_card"][$key];
        }

        foreach ($localkeys as $key) {
            $this->configHipay["payment"]["local_payment"][$key] = $configFields["payment"]["local_payment"][$key];
        }
    }

    /**
     *  save a specific key of the module config
     * @param: string $key
     * @param: mixed $value
     * @return : bool
     * */
    public function setConfigHiPay($key, $value) {
        $this->module->getLogs()->logsHipay('---- >> function setConfigHiPay');
        // Use this function only if you have just one variable to update
        // init multistore
        $id_shop = (int) $this->context->shop->id;
        $id_shop_group = (int) Shop::getContextShopGroupID();
        // the config is stacked in JSON
        $confHipay = Tools::jsonDecode(Configuration::get('HIPAY_CONFIG', null, $id_shop_group, $id_shop), true);

        $this->module->getLogs()->logsHipay(print_r($confHipay, true));

        $confHipay[$key] = $value;
        //$confHipay = array_replace_recursive($confHipay,$value);
        //TODO: if value is an array and not associative replace not recursivly

        if (Configuration::updateValue('HIPAY_CONFIG', Tools::jsonEncode($confHipay), false, $id_shop_group, $id_shop)) {
            $this->configHipay = Tools::jsonDecode(Configuration::get('HIPAY_CONFIG', null, $id_shop_group, $id_shop), true);
            return true;
        } else {
            throw new Exception($this->l('Update failed, try again.'));
        }
    }

    /**
     * Functions to init the configuration HiPay
     */
    private function initConfigHiPay() {
        // init multistore
        $id_shop = (int) $this->context->shop->id;
        $id_shop_group = (int) Shop::getContextShopGroupID();
        $this->configHipay = Tools::jsonDecode(Configuration::get('HIPAY_CONFIG', null, $id_shop_group, $id_shop), true);

        // if config exist but empty, init new object for configHipay
        if (!$this->configHipay || empty($this->configHipay)) {
            $this->insertConfigHiPay();
        }
    }

    /**
     * init module configuration
     * @return : boolrequire_once(dirname(__FILE__) . '/../apiHandler/ApiHandler.php');

     */
    private function insertConfigHiPay() {
        $this->module->getLogs()->logsHipay('---- >> function insertConfigHiPay');

        //TODO mock config for front test. credit_card and payment indexes must be injected through json

        $configFields = array(
            "account" => array(
                "global" => array(
                    "sandbox_mode" => 0,
                    "host_proxy" => "",
                    "host_proxy" => "",
                    "port_proxy" => "",
                    "user_proxy" => "",
                    "password_proxy" => ""
                ),
                "sandbox" => array(
                    "api_username_sandbox" => "",
                    "api_password_sandbox" => "",
                    "api_tokenjs_username_sandbox" => "",
                    "api_tokenjs_password_publickey_sandbox" => "",
                    "api_secret_passphrase_sandbox" => "",
                    "api_moto_username_sandbox" => "",
                    "api_moto_password_sandbox" => "",
                    "api_moto_secret_passphrase_sandbox" => ""
                ),
                "production" => array(
                    "api_username_production" => "",
                    "api_password_production" => "",
                    "api_tokenjs_username_production" => "",
                    "api_tokenjs_password_publickey_production" => "",
                    "api_secret_passphrase_production" => "",
                    "api_moto_username_production" => "",
                    "api_moto_password_production" => "",
                    "api_moto_secret_passphrase_production" => ""
                )
            ),
            "payment" => array(
                "global" => array(
                    "operating_mode" => Apihandler::DIRECTPOST,
                    "iframe_hosted_page_template" => "basic-js",
                    "display_card_selector" => 0,
                    "css_url" => "",
                    "activate_3d_secure" => 1,
                    "capture_mode" => "manual",
                    "card_token" => 1,
                    "electronic_signature" => 1
                ),
                "credit_card" => array(),
                "local_payment" => array()
            ),
            "fraud" => array(
                "payment_fraud_email_sender" => strval(Configuration::get('PS_SHOP_EMAIL')),
                "send_payment_fraud_email_copy_to" => "",
                "send_payment_fraud_email_copy_method" => "bcc",
                "payment_fraud_accept_email_sender" => strval(Configuration::get('PS_SHOP_EMAIL')),
                "send_payment_accept_email_copy_to" => "",
                "send_payment_accept_email_copy_method" => "bcc",
                "payment_fraud_deny_email_sender" => strval(Configuration::get('PS_SHOP_EMAIL')),
                "send_payment_deny_email_copy_to" => "",
                "send_payment_deny_email_copy_method" => "bcc"
            )
        );
        $configFields["payment"]["credit_card"] = $this->insertPaymentsConfig("creditCard/");
        $configFields["payment"]["local_payment"] = $this->insertPaymentsConfig("local/");


        return $this->setAllConfigHiPay($configFields);
    }

    /**
     * Save initial module config
     * @param : array $arrayHipay
     *
     * @return : bool
     * */
    private function setAllConfigHiPay($arrayHipay = null) {
        $this->module->getLogs()->logsHipay('---- >> function setAllConfigHiPay');
        // use this function if you have a few variables to update
        if ($arrayHipay != null) {
            $for_json_hipay = $arrayHipay;
        } else {
            $for_json_hipay = $this->configHipay;
        }

        // init multistore
        $id_shop = (int) $this->context->shop->id;
        $id_shop_group = (int) Shop::getContextShopGroupID();
        // the config is stacked in JSON
        $this->module->getLogs()->logsHipay(print_r(Tools::jsonEncode($for_json_hipay), true));
        if (Configuration::updateValue('HIPAY_CONFIG', Tools::jsonEncode($for_json_hipay), false, $id_shop_group, $id_shop)) {
            $this->configHipay = Tools::jsonDecode(Configuration::get('HIPAY_CONFIG', null, $id_shop_group, $id_shop), true);
            return true;
        } else {
            throw new Exception($this->l('Update failed, try again.'));
        }
    }

    /**
     * init local config
     * @return array
     */
    private function insertPaymentsConfig($folderName) {
        $creditCard = array();

        $files = scandir($this->jsonFilesPath . $folderName);

        foreach ($files as $file) {
            $creditCard = array_merge($creditCard, $this->addPaymentConfig($file, $folderName));
        }

        return $creditCard;
    }

    /**
     * add specific payment config from JSON file
     * @param type $file
     * @return type
     */
    private function addPaymentConfig($file, $folderName) {

        $creditCard = array();

        if (preg_match('/(.*)\.json/', $file) == 1) {
            $json = Tools::jsonDecode(file_get_contents($this->jsonFilesPath . $folderName . $file), true);
            $creditCard[$json["name"]] = $json["config"];
        }

        return $creditCard;
    }

}
