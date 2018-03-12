<?php
/**
 * HiPay Enterprise SDK Prestashop
 *
 * 2017 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright 2017 HiPay
 * @license   https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 */

require_once(dirname(__FILE__) . '/../apiHandler/ApiHandler.php');

use HiPay\Fullservice\Enum\Helper\HashAlgorithm;

/**
 * handle module configuration
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class HipayConfig
{
    const THREE_D_S_DISABLED = 0;
    const THREE_D_S_TRY_ENABLE_ALL = 1;
    const THREE_D_S_TRY_ENABLE_RULES = 2;
    const THREE_D_S_FORCE_ENABLE_ALL = 3;
    const THREE_D_S_FORCE_ENABLE_RULES = 4;

    private $jsonFilesPath;
    private $context;
    private $configHipay = array();

    /**
     * HipayConfig constructor.
     * @param $module_instance
     */
    public function __construct($module_instance)
    {
        $this->context = Context::getContext();
        $this->module = $module_instance;
        $this->jsonFilesPath = dirname(__FILE__) . "/../../paymentConfigFiles/";
    }

    /**
     * return config array
     *
     * @return array
     */
    public function getConfigHipay()
    {
        if (empty($this->configHipay)) {
            $this->initConfigHiPay();
        }

        return $this->configHipay;
    }

    /**
     * @return mixed
     */
    public function getPaymentGlobal()
    {

        return $this->getConfigHipay()["payment"]["global"];
    }

    /**
     * @return mixed
     */
    public function getPaymentCreditCard()
    {

        return $this->getConfigHipay()["payment"]["credit_card"];
    }

    /**
     * @return mixed
     */
    public function getLocalPayment()
    {

        return $this->getConfigHipay()["payment"]["local_payment"];
    }

    /**
     * @return mixed
     */
    public function getAccountGlobal()
    {

        return $this->getConfigHipay()["account"]["global"];
    }

    /**
     * @return mixed
     */
    public function getAccountSandbox()
    {

        return $this->getConfigHipay()["account"]["sandbox"];
    }

    /**
     * @return mixed
     */
    public function getAccountProduction()
    {

        return $this->getConfigHipay()["account"]["production"];
    }

    /**
     * @param string $platform
     * @return mixed
     */
    public function getHashAlgorithm()
    {
        return $this->getConfigHipay()["account"]["hash_algorithm"];
    }

    /**
     * @param string $platform
     * @return mixed
     */
    public function setHashAlgorithm($value)
    {
        return $this->setConfigHiPay("account",$value,"hash_algorithm");
    }

    /**
     * @return mixed
     */
    public function getFraud()
    {

        return $this->getConfigHipay()["fraud"];
    }


    /**
     *  Save a specific key of the module config
     *
     * @param $key
     * @param $value
     * @param $child
     * @return bool
     * @throws Exception
     */
    public function setConfigHiPay($key, $value, $child = null)
    {
        // Use this function only if you have just one variable to update
        // init multistore
        $id_shop = (int)$this->context->shop->id;
        $id_shop_group = (int)Shop::getContextShopGroupID();

        // the config is stacked in JSON
        $confHipay = Tools::jsonDecode(Configuration::get('HIPAY_CONFIG', null, $id_shop_group, $id_shop), true);

        if (isset($child)) {
            $confHipay[$key][$child] = $value;
        } else {
            $confHipay[$key] = $value;
        }

        if (Configuration::updateValue(
            'HIPAY_CONFIG',
            Tools::jsonEncode($confHipay),
            false,
            $id_shop_group,
            $id_shop
        )
        ) {
            $this->configHipay = Tools::jsonDecode(
                Configuration::get('HIPAY_CONFIG', null, $id_shop_group, $id_shop),
                true
            );
            return true;
        } else {
            throw new Exception($this->l('Update failed, try again.'));
        }
    }

    /**
     * Functions to init the configuration HiPay
     */
    private function initConfigHiPay()
    {
        // init multistore
        $id_shop = (int)$this->context->shop->id;
        $id_shop_group = (int)Shop::getContextShopGroupID();
        $this->configHipay = Tools::jsonDecode(
            Configuration::get('HIPAY_CONFIG', null, $id_shop_group, $id_shop),
            true
        );

        // if config exist but empty, init new object for configHipay
        if (!$this->configHipay || empty($this->configHipay)) {
            $this->insertConfigHiPay();
        }
    }

    /**
     * update config if there's a new json uploaded
     */
    public function updateConfig()
    {

        $configFields = array();

        $configFields["payment"]["credit_card"] = $this->insertPaymentsConfig("creditCard/");
        $configFields["payment"]["local_payment"] = $this->insertPaymentsConfig("local/");

        // we update only new payment method
        $localkeys = array_diff(
            array_keys($configFields["payment"]["local_payment"]),
            array_keys($this->configHipay["payment"]["local_payment"])
        );
        $cckeys = array_diff(
            array_keys($configFields["payment"]["credit_card"]),
            array_keys($this->configHipay["payment"]["credit_card"])
        );

        $this->module->getLogs()->logInfos("# Update Config");
        $this->module->getLogs()->logInfos(print_r($localkeys, true) . print_r($cckeys, true));

        foreach ($cckeys as $key) {
            $this->configHipay["payment"]["credit_card"][$key] = $configFields["payment"]["credit_card"][$key];
        }

        foreach ($localkeys as $key) {
            $this->configHipay["payment"]["local_payment"][$key] = $configFields["payment"]["local_payment"][$key];
        }
    }

    /**
     * Update hipay config from JSON file
     * Add new payment method or new parameters on module update
     *
     * @param array $keepParameters
     * @throws Exception
     */
    public function updateFromJSONFile($keepParameters = array())
    {
        $this->module->getLogs()->logInfos("updateFromJSONFile ");

        $shops = Shop::getShops(false);
        foreach ($shops as $id => $shop) {

            $this->module->getLogs()->logInfos(
                "get HIPAY_CONFIG for shop " . $id . " and id shop group " . $shop['id_shop_group']
            );

            $configHipay = Tools::jsonDecode(
                Configuration::get('HIPAY_CONFIG', null, $shop['id_shop_group'], $id),
                true
            );

            $this->module->getLogs()->logInfos($configHipay['account']);

            $this->module->getLogs()->logInfos("Retrieve JSON files ");

            $paymentMethod = $this->insertPaymentsConfig("local/");

            $this->diffJsonAndConfig($configHipay, $paymentMethod, $keepParameters, 'local_payment');

            $paymentMethod = $this->insertPaymentsConfig("creditCard/");

            $this->diffJsonAndConfig($configHipay, $paymentMethod, $keepParameters, 'credit_card');

            $this->setAllConfigHiPay($configHipay, $shop['id_shop_group'], $id);
        }
    }

    /**
     * Override saved payment method config saved in DB with config from JSON
     * Parameters in $keepParameters will not be override
     *
     * @param $configHipay
     * @param $paymentMethod
     * @param $keepParameters
     * @param $paymentMethodType
     */
    private function diffJsonAndConfig(&$configHipay, $paymentMethod, $keepParameters, $paymentMethodType)
    {
        $this->module->getLogs()->logInfos("diffJsonAndConfig ");

        // Add new payment Method
        $configHipay["payment"][$paymentMethodType] = array_merge(
            $configHipay["payment"][$paymentMethodType],
            array_diff_key($paymentMethod, $configHipay["payment"][$paymentMethodType])
        );

        foreach ($paymentMethod as $key => $value) {
            // Add new parameters to payment method
            $configHipay["payment"][$paymentMethodType][$key] = array_merge(
                $configHipay["payment"][$paymentMethodType][$key],
                array_diff_key($paymentMethod[$key], $configHipay["payment"][$paymentMethodType][$key])
            );

            // remove old parameters
            $configHipay["payment"][$paymentMethodType][$key] = array_diff_key(
                $configHipay["payment"][$paymentMethodType][$key],
                array_diff_key($configHipay["payment"][$paymentMethodType][$key], $paymentMethod[$key])
            );

            // preserve saved parameters in Database, only parameters not in $keepParameters[$key] will be override
            $keep = (isset($keepParameters[$key])) ? $keepParameters[$key] : array();
            $replace = array_diff_key($paymentMethod[$key], $keep);

            // override parameters
            $configHipay["payment"][$paymentMethodType][$key] = array_replace(
                $configHipay["payment"][$paymentMethodType][$key],
                $replace
            );

        }
    }

    /**
     * init module configuration
     *
     * @return bool
     * @throws Exception
     */
    private function insertConfigHiPay()
    {
        $configFields = array(
            "account" => array(
                "global" => array(
                    "sandbox_mode" => 1,
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
                ),
                "hash_algorithm" => array(
                    "production" => HashAlgorithm::SHA1,
                    "test" => HashAlgorithm::SHA1,
                    "production_moto" => HashAlgorithm::SHA1,
                    "test_moto" => HashAlgorithm::SHA1
                )
            ),
            "payment" => array(
                "global" => array(
                    "operating_mode" => Apihandler::DIRECTPOST,
                    "iframe_hosted_page_template" => "basic-js",
                    "display_card_selector" => 0,
                    "display_hosted_page" => "redirect",
                    "css_url" => "",
                    "activate_3d_secure" => HipayConfig::THREE_D_S_DISABLED,
                    "3d_secure_rules" => array(
                        array(
                            "field" => "total_price",
                            "operator" => ">",
                            "value" => 100,
                        )
                    ),
                    "capture_mode" => "automatic",
                    "card_token" => 0,
                    "activate_basket" => 1,
                    "log_infos" => 1,
                    "regenerate_cart_on_decline" => 1,
                    "ccDisplayName" => array("fr" => "Carte de crédit", "en" => "Credit card"),
                    "ccFrontPosition" => 1,
                    "send_url_notification" => 0
                ),
                "credit_card" => array(),
                "local_payment" => array()
            ),
            "fraud" => array(
                "payment_fraud_email_sender" => (string)Configuration::get('PS_SHOP_EMAIL'),
                "send_payment_fraud_email_copy_to" => "",
                "send_payment_fraud_email_copy_method" => "bcc"
            )
        );
        $configFields["payment"]["credit_card"] = $this->insertPaymentsConfig("creditCard/");
        $configFields["payment"]["local_payment"] = $this->insertPaymentsConfig("local/");

        return $this->setAllConfigHiPay($configFields);
    }

    /**
     * Save initial module config
     *
     * @param null $arrayHipay
     * @param null $id_shop_group
     * @param null $id_shop
     * @return bool
     * @throws Exception
     */
    private function setAllConfigHiPay($arrayHipay = null, $id_shop_group = null, $id_shop = null)
    {
        // use this function if you have a few variables to update
        if ($arrayHipay != null) {
            $for_json_hipay = $arrayHipay;
        } else {
            $for_json_hipay = $this->configHipay;
        }

        // init multistore
        $id_shop = (is_null($id_shop)) ? (int)$this->context->shop->id : $id_shop;
        $id_shop_group = (is_null($id_shop_group)) ? (int)Shop::getContextShopGroupID() : $id_shop_group;
        if (Configuration::updateValue(
            'HIPAY_CONFIG',
            Tools::jsonEncode($for_json_hipay),
            false,
            $id_shop_group,
            $id_shop
        )
        ) {
            $this->configHipay = Tools::jsonDecode(
                Configuration::get('HIPAY_CONFIG', null, $id_shop_group, $id_shop),
                true
            );

            $this->module->getLogs()->logInfos($this->configHipay);

            return true;
        } else {
            throw new Exception($this->l('Update failed, try again.'));
        }
    }

    /**
     * init local config
     * @return array
     */
    private function insertPaymentsConfig($folderName)
    {
        $creditCard = array();

        $files = scandir($this->jsonFilesPath . $folderName);

        foreach ($files as $file) {
            $creditCard = array_merge($creditCard, $this->addPaymentConfig($file, $folderName));
        }

        return $creditCard;
    }

    /**
     * add specific payment config from JSON file
     *
     * @param $file
     * @param $folderName
     * @return array
     */
    private function addPaymentConfig($file, $folderName)
    {
        $creditCard = array();

        if (preg_match('/(.*)\.json/', $file) == 1) {
            $json = Tools::jsonDecode(Tools::file_get_contents($this->jsonFilesPath . $folderName . $file), true);
            $creditCard[$json["name"]] = $json["config"];
        }

        return $creditCard;
    }
}
