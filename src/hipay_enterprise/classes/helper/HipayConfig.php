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

require_once(dirname(__FILE__) . '/enums/OperatingMode.php');
require_once(dirname(__FILE__) . '/enums/UXMode.php');
require_once(dirname(__FILE__) . '/enums/ThreeDS.php');
require_once(dirname(__FILE__) . '/../exceptions/PaymentProductNotFoundException.php');

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
    private $jsonFilesPath;
    private $context;
    private $configHipay = array();

    private static $_deprecatedMethods = array(
        "webmoney-transfer"
    );

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
     * @return mixed
     */
    public function getHashAlgorithm()
    {
        return $this->getConfigHipay()["account"]["hash_algorithm"];
    }

    /**
     * @param $value
     * @return bool
     * @throws Exception
     */
    public function setHashAlgorithm($value)
    {
        return $this->setConfigHiPay("account", $value, "hash_algorithm");
    }

    /**
     * @return mixed
     */
    public function getFraud()
    {
        return $this->getConfigHipay()["fraud"];
    }

    /**
     * Get payment product config from payment product name
     *
     * @param $paymentProduct
     * @return array
     * @throws PaymentProductNotFoundException
     */
    public function getPaymentProduct($paymentProduct)
    {
        if (isset($this->getPaymentCreditCard()[$paymentProduct])) {
            return $this->getPaymentCreditCard()[$paymentProduct];
        } elseif (isset($this->getLocalPayment()[$paymentProduct])) {
            return $this->getLocalPayment()[$paymentProduct];
        }

        throw new PaymentProductNotFoundException($paymentProduct);
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

            $this->updatePaymentGlobal($configHipay);

            $this->updateAccountConfig($configHipay);

            $this->setAllConfigHiPay($configHipay, $shop['id_shop_group'], $id);
        }
    }

    /**
     * Update Account config with new default values
     *
     * @param $configHipay
     */
    private function updateAccountConfig(&$configHipay)
    {
        $defaultConfig = $this->getDefaultConfig();

        // add new fields
        $configHipay["account"] = array_merge(
            $configHipay["account"],
            array_diff_key($defaultConfig["account"], $configHipay["account"])
        );
    }

    /**
     * Update Global Payment config with new default values
     *
     * @param $configHipay
     */
    private function updatePaymentGlobal(&$configHipay)
    {
        $defaultConfig = $this->getDefaultConfig();

        // add new fields
        $configHipay["payment"]["global"] = array_merge(
            $configHipay["payment"]["global"],
            array_diff_key($defaultConfig["payment"]["global"], $configHipay["payment"]["global"])
        );

        // update operating_mode with new format
        switch ($configHipay["payment"]["global"]["operating_mode"]) {
            case "api":
                $configHipay["payment"]["global"]["operating_mode"] = OperatingMode::getOperatingMode(
                    UXMode::DIRECT_POST
                );
                break;
            case "hosted_page":
                $configHipay["payment"]["global"]["operating_mode"] = OperatingMode::getOperatingMode(
                    UXMode::HOSTED_PAGE
                );
                break;
        }
    }

    /**
     * Get base config value
     *
     * @return array
     */
    private function getDefaultConfig()
    {
        return array(
            "account" => array(
                "global" => array(
                    "sandbox_mode" => 1,
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
                    "operating_mode" => OperatingMode::getOperatingMode(UXMode::DIRECT_POST),
                    "iframe_hosted_page_template" => "basic-js",
                    "display_card_selector" => 0,
                    "display_hosted_page" => "redirect",
                    "css_url" => "",
                    "activate_3d_secure" => ThreeDS::THREE_D_S_DISABLED,
                    "sdk_js_url" => 'https://libs.hipay.com/js/sdkjs.js',
                    "3d_secure_rules" => array(
                        array(
                            "field" => "total_price",
                            "operator" => ">",
                            "value" => 100,
                        )
                    ),
                    "hosted_fields_style" => array(
                        "base" => array(
                            "color" => "#000000",
                            "fontFamily" => "Roboto",
                            "fontSize" => "15px",
                            "fontWeight" => "400",
                            "placeholderColor" => "",
                            "caretColor" => "#00ADE9",
                            "iconColor" => "#00ADE9",

                        )
                    ),
                    "capture_mode" => "automatic",
                    "card_token" => 0,
                    "activate_basket" => 1,
                    "log_infos" => 1,
                    "regenerate_cart_on_decline" => 1,
                    "ccDisplayName" => array(
                        "fr" => "Carte de crédit",
                        "en" => "Credit card",
                        "it" => "Carta di credito"
                    ),
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

        // remove deprecated payment method
        foreach (array_diff_key($configHipay["payment"][$paymentMethodType], $paymentMethod) as $removeKey => $item) {
            unset($configHipay["payment"][$paymentMethodType][$removeKey]);
        }

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
        $configFields = $this->getDefaultConfig();
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
    public function setAllConfigHiPay($arrayHipay = null, $id_shop_group = null, $id_shop = null)
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
     *
     * @param $folderName
     * @return array
     */
    private function insertPaymentsConfig($folderName)
    {
        $paymentMethod = array();

        $files = scandir($this->jsonFilesPath . $folderName);

        foreach ($files as $file) {
            $paymentMethod = array_merge($paymentMethod, $this->addPaymentConfig($file, $folderName));
        }

        return $paymentMethod;
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
        $paymentMethod = array();

        if (preg_match('/(.*)\.json/', $file) == 1) {
            $json = Tools::jsonDecode(Tools::file_get_contents($this->jsonFilesPath . $folderName . $file), true);
            if (!in_array($json["name"], static::$_deprecatedMethods)) {

                $paymentMethod[$json["name"]] = $json["config"];

                $sdkConfig = HiPay\Fullservice\Data\PaymentProduct\Collection::getItem($json["name"]);

                if ($sdkConfig !== null) {
                    $paymentMethod[$json["name"]] = array_merge($sdkConfig->toArray(), $paymentMethod[$json["name"]]);
                }

                if (
                    isset($paymentMethod[$json["name"]]["currencies"]) &&
                    empty($paymentMethod[$json["name"]]["currencies"])
                ) {
                    $paymentMethod[$json["name"]]["currencies"] = $this->getActiveCurrencies();
                }

                if (
                    isset($paymentMethod[$json["name"]]["countries"]) &&
                    empty($paymentMethod[$json["name"]]["countries"])
                ) {
                    $paymentMethod[$json["name"]]["countries"] = $this->getActiveCountries();
                }

            }
        }

        return $paymentMethod;
    }

    private function getActiveCurrencies(){
        $activeCurrenciesIso = array();
        $activeCurrencies = Currency::getCurrencies(false, true);

        foreach ($activeCurrencies as $currency) {
            $activeCurrenciesIso[] = $currency["iso_code"];
        }

        return $activeCurrenciesIso;
    }

    private function getActiveCountries()
    {
        $activeCountriesIso = array();
        $activeCountries = Country::getCountries($this->context->language->id, true);

        foreach ($activeCountries as $country) {
            $activeCountriesIso[] = $country["iso_code"];
        }

        return $activeCountriesIso;
    }
}
