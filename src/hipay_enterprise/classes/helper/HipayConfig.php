<?php
/**
 * HiPay Enterprise SDK Prestashop.
 *
 * 2017 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright 2017 HiPay
 * @license   https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 */
require_once dirname(__FILE__).'/enums/OperatingMode.php';
require_once dirname(__FILE__).'/enums/UXMode.php';
require_once dirname(__FILE__).'/enums/ThreeDS.php';
require_once dirname(__FILE__).'/../exceptions/PaymentProductNotFoundException.php';

use HiPay\Fullservice\Enum\Helper\HashAlgorithm;

/**
 * handle module configuration.
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 *
 * @see    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class HipayConfig
{
    private $jsonFilesPath;
    private $context;
    private $configHipay = [];

    private static $_deprecatedMethods = [
        'webmoney-transfer',
    ];

    /**
     * HipayConfig constructor.
     */
    public function __construct($module_instance)
    {
        $this->context = Context::getContext();
        $this->module = $module_instance;
        $this->jsonFilesPath = dirname(__FILE__).'/../../paymentConfigFiles/';
    }

    /**
     * return config array.
     *
     * @return array
     */
    public function getConfigHipay()
    {
        // Reload context if updated in multi shop context
        $this->context = Context::getContext();
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
        return $this->getConfigHipay()['payment']['global'];
    }

    /**
     * @return mixed
     */
    public function getPaymentCreditCard()
    {
        return $this->getConfigHipay()['payment']['credit_card'];
    }

    /**
     * @return mixed
     */
    public function getLocalPayment()
    {
        return $this->getConfigHipay()['payment']['local_payment'];
    }

    /**
     * @return mixed
     */
    public function getAccountGlobal()
    {
        return $this->getConfigHipay()['account']['global'];
    }

    /**
     * @return mixed
     */
    public function getAccountSandbox()
    {
        return $this->getConfigHipay()['account']['sandbox'];
    }

    /**
     * @return mixed
     */
    public function getAccountProduction()
    {
        return $this->getConfigHipay()['account']['production'];
    }

    /**
     * @return mixed
     */
    public function getHashAlgorithm()
    {
        return $this->getConfigHipay()['account']['hash_algorithm'];
    }

    /**
     * @return bool
     *
     * @throws Exception
     */
    public function setHashAlgorithm($value)
    {
        return $this->setConfigHiPay('account', $value, 'hash_algorithm');
    }

    /**
     * @return mixed
     */
    public function getFraud()
    {
        return $this->getConfigHipay()['fraud'];
    }

    /**
     * Get payment product config from payment product name.
     *
     * @return array
     *
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
     *  Save a specific key of the module config.
     *
     * @return bool
     *
     * @throws Exception
     */
    public function setConfigHiPay($key, $value, $child = null)
    {
        // Use this function only if you have just one variable to update
        // init multistore
        $id_shop = (int) Shop::getContextShopID();
        $id_shop_group = (int) Shop::getContextShopGroupID();

        // the config is stacked in JSON
        $confHipay = $this->getConfigurationFromDB($id_shop_group, $id_shop);

        if (isset($child)) {
            $confHipay[$key][$child] = $value;
        } else {
            $confHipay[$key] = $value;
        }

        return $this->setAllConfigHiPay($confHipay, $id_shop_group, $id_shop);
    }

    /**
     * Functions to init the configuration HiPay.
     */
    private function initConfigHiPay()
    {
        // init multistore
        $id_shop = (int) Shop::getContextShopID();
        $id_shop_group = (int) Shop::getContextShopGroupID();
        $this->configHipay = $this->getConfigurationFromDB($id_shop_group, $id_shop);

        // if config exist but empty, init new object for configHipay
        if (!$this->configHipay || empty($this->configHipay)) {
            $this->insertConfigHiPay();
        }
    }

    /**
     * Update hipay config from JSON file
     * Add new payment method or new parameters on module update.
     *
     * @param array $keepParameters
     *
     * @throws Exception
     */
    public function updateFromJSONFile($keepParameters = [])
    {
        $this->module->getLogs()->logInfos('updateFromJSONFile ');

        $shops = Shop::getShops(false);
        foreach ($shops as $id => $shop) {
            $this->module->getLogs()->logInfos(
                'get HIPAY_CONFIG for shop '.$id.' and id shop group '.$shop['id_shop_group']
            );

            $configHipay = $this->getConfigurationFromDB($shop['id_shop_group'], $id);

            $this->module->getLogs()->logInfos($configHipay['account']);

            $this->module->getLogs()->logInfos('Retrieve JSON files ');

            $paymentMethod = $this->insertPaymentsConfig('local/');

            $this->diffJsonAndConfig($configHipay, $paymentMethod, $keepParameters, 'local_payment');

            $paymentMethod = $this->insertPaymentsConfig('creditCard/');

            $this->diffJsonAndConfig($configHipay, $paymentMethod, $keepParameters, 'credit_card');

            $this->updatePaymentGlobal($configHipay);

            $this->updateAccountConfig($configHipay);

            $this->setAllConfigHiPay($configHipay, $shop['id_shop_group'], $id);
        }
    }

    /**
     * Update Account config with new default values.
     */
    private function updateAccountConfig(&$configHipay)
    {
        $defaultConfig = $this->getDefaultConfig();

        // add new fields
        $configHipay['account'] = array_merge(
            $configHipay['account'],
            array_diff_key($defaultConfig['account'], $configHipay['account'])
        );
    }

    /**
     * Update Global Payment config with new default values.
     */
    private function updatePaymentGlobal(&$configHipay)
    {
        $defaultConfig = $this->getDefaultConfig();

        // add new fields
        $configHipay['payment']['global'] = array_merge(
            $configHipay['payment']['global'],
            array_diff_key($defaultConfig['payment']['global'], $configHipay['payment']['global'])
        );

        // update operating_mode with new format
        switch ($configHipay['payment']['global']['operating_mode']) {
            case 'api':
                $configHipay['payment']['global']['operating_mode'] = OperatingMode::getOperatingMode(
                    UXMode::DIRECT_POST
                );
                break;
            case 'hosted_page':
                $configHipay['payment']['global']['operating_mode'] = OperatingMode::getOperatingMode(
                    UXMode::HOSTED_PAGE
                );
                break;
        }
    }

    /**
     * Get base config value.
     *
     * @return array
     */
    private function getDefaultConfig()
    {
        return [
            'account' => [
                'global' => [
                    'sandbox_mode' => 1,
                    'host_proxy' => '',
                    'port_proxy' => '',
                    'user_proxy' => '',
                    'password_proxy' => '',
                    'order_message_on_notification' => 1,
                    'notification_cron' => 0,
                    'notification_cron_token' => md5(time()),
                    'notification_max_retry' => 50,
                    'use_prestashop_refund_form' => 0,
                ],
                'sandbox' => [
                    'api_username_sandbox' => '',
                    'api_password_sandbox' => '',
                    'api_tokenjs_username_sandbox' => '',
                    'api_tokenjs_password_publickey_sandbox' => '',
                    'api_secret_passphrase_sandbox' => '',
                    'api_moto_username_sandbox' => '',
                    'api_moto_password_sandbox' => '',
                    'api_moto_secret_passphrase_sandbox' => '',
                    'api_apple_pay_username_sandbox' => '',
                    'api_apple_pay_password_sandbox' => '',
                    'api_apple_pay_passphrase_sandbox' => '',
                    'api_tokenjs_apple_pay_username_sandbox' => '',
                    'api_tokenjs_apple_pay_password_sandbox' => '',
                ],
                'production' => [
                    'api_username_production' => '',
                    'api_password_production' => '',
                    'api_tokenjs_username_production' => '',
                    'api_tokenjs_password_publickey_production' => '',
                    'api_secret_passphrase_production' => '',
                    'api_moto_username_production' => '',
                    'api_moto_password_production' => '',
                    'api_moto_secret_passphrase_production' => '',
                    'api_apple_pay_username_production' => '',
                    'api_apple_pay_password_production' => '',
                    'api_apple_pay_passphrase_production' => '',
                    'api_tokenjs_apple_pay_username_production' => '',
                    'api_tokenjs_apple_pay_password_production' => '',
                ],
                'hash_algorithm' => [
                    'production' => HashAlgorithm::SHA1,
                    'test' => HashAlgorithm::SHA1,
                    'production_moto' => HashAlgorithm::SHA1,
                    'test_moto' => HashAlgorithm::SHA1,
                    'production_apple_pay' => HashAlgorithm::SHA1,
                    'test_apple_pay' => HashAlgorithm::SHA1,
                ],
            ],
            'payment' => [
                'global' => [
                    'operating_mode' => OperatingMode::getOperatingMode(UXMode::HOSTED_FIELDS),
                    'enable_api_v2' => 1,
                    'iframe_hosted_page_template' => 'basic-js',
                    'display_card_selector' => 0,
                    'display_hosted_page' => 'redirect',
                    'css_url' => '',
                    'activate_3d_secure' => ThreeDS::THREE_D_S_DISABLED,
                    'sdk_js_url' => 'https://libs.hipay.com/js/sdkjs.js',
                    '3d_secure_rules' => [
                        [
                            'field' => 'total_price',
                            'operator' => '>',
                            'value' => 100,
                        ],
                    ],
                    'hosted_fields_style' => [
                        'base' => [
                            'color' => '#000000',
                            'fontFamily' => 'Roboto',
                            'fontSize' => '15px',
                            'fontWeight' => '400',
                            'placeholderColor' => '',
                            'caretColor' => '#00ADE9',
                            'iconColor' => '#00ADE9',
                        ],
                    ],
                    'capture_mode' => 'automatic',
                    'card_token' => 0,
                    'activate_basket' => 1,
                    'log_infos' => 1,
                    'regenerate_cart_on_decline' => 1,
                    'ccDisplayName' => [
                        'fr' => 'Carte de crédit',
                        'en' => 'Credit card',
                        'it' => 'Carta di credito',
                    ],
                    'ccFrontPosition' => 1,
                    'send_url_notification' => 0,
                ],
                'credit_card' => [],
                'local_payment' => [],
            ],
            'fraud' => [
                'payment_fraud_email_sender' => (string) Configuration::get('PS_SHOP_EMAIL'),
                'send_payment_fraud_email_copy_to' => '',
                'send_payment_fraud_email_copy_method' => 'bcc',
            ],
        ];
    }

    /**
     * Override saved payment method config saved in DB with config from JSON
     * Parameters in $keepParameters will not be override.
     */
    private function diffJsonAndConfig(&$configHipay, $paymentMethod, $keepParameters, $paymentMethodType)
    {
        $this->module->getLogs()->logInfos('diffJsonAndConfig ');

        // Add new payment Method
        $configHipay['payment'][$paymentMethodType] = array_merge(
            $configHipay['payment'][$paymentMethodType],
            array_diff_key($paymentMethod, $configHipay['payment'][$paymentMethodType])
        );

        // remove deprecated payment method
        foreach (array_diff_key($configHipay['payment'][$paymentMethodType], $paymentMethod) as $removeKey => $item) {
            unset($configHipay['payment'][$paymentMethodType][$removeKey]);
        }

        foreach ($paymentMethod as $key => $value) {
            // Add new parameters to payment method
            $configHipay['payment'][$paymentMethodType][$key] = array_merge(
                $configHipay['payment'][$paymentMethodType][$key],
                array_diff_key($paymentMethod[$key], $configHipay['payment'][$paymentMethodType][$key])
            );

            // remove old parameters
            $configHipay['payment'][$paymentMethodType][$key] = array_diff_key(
                $configHipay['payment'][$paymentMethodType][$key],
                array_diff_key($configHipay['payment'][$paymentMethodType][$key], $paymentMethod[$key])
            );

            // preserve saved parameters in Database, only parameters not in $keepParameters[$key] will be override
            $keep = (isset($keepParameters[$key])) ? $keepParameters[$key] : [];
            $replace = array_diff_key($paymentMethod[$key], $keep);

            // override parameters
            $configHipay['payment'][$paymentMethodType][$key] = array_replace(
                $configHipay['payment'][$paymentMethodType][$key],
                $replace
            );
        }
    }

    /**
     * init module configuration.
     *
     * @return bool
     *
     * @throws Exception
     */
    private function insertConfigHiPay()
    {
        $configFields = $this->getDefaultConfig();

        $configFields['payment']['credit_card'] = $this->insertPaymentsConfig('creditCard/');
        $configFields['payment']['local_payment'] = $this->insertPaymentsConfig('local/');

        return $this->setAllConfigHiPay($configFields);
    }

    /**
     * Save initial module config.
     *
     * @param null $arrayHipay
     * @param null $id_shop_group
     * @param null $id_shop
     *
     * @return bool
     *
     * @throws Exception
     */
    public function setAllConfigHiPay($arrayHipay = null, $id_shop_group = null, $id_shop = null)
    {
        // use this function if you have a few variables to update
        if (null != $arrayHipay) {
            $for_json_hipay = $arrayHipay;
        } else {
            $for_json_hipay = $this->configHipay;
        }

        // init multistore
        $id_shop = (is_null($id_shop)) ? (int) Shop::getContextShopID() : (int) $id_shop;
        $id_shop_group = (is_null($id_shop_group)) ? (int) Shop::getContextShopGroupID() : $id_shop_group;

        $paymentConfig = ['credit_card' => $for_json_hipay['payment']['credit_card'], 'local_payment' => $for_json_hipay['payment']['local_payment']];

        unset($for_json_hipay['payment']['credit_card']);
        unset($for_json_hipay['payment']['local_payment']);

        $paymentMeans = ['credit_card' => [], 'local_payment' => []];

        foreach ($paymentConfig as $methodGroup => $methods) {
            foreach ($methods as $methodId => $methodConfig) {
                $paymentMeans[$methodGroup][] = $methodId;

                Configuration::updateValue(
                    'HIPAY_PAYMENT_'.strtoupper($methodId),
                    json_encode($methodConfig),
                    false,
                    $id_shop_group,
                    $id_shop
                );
            }
        }

        Configuration::updateValue(
            'HIPAY_PAYMENT_MEANS',
            json_encode($paymentMeans),
            false,
            $id_shop_group,
            $id_shop
        );

        if (Configuration::updateValue(
            'HIPAY_CONFIG',
            json_encode($for_json_hipay),
            false,
            $id_shop_group,
            $id_shop
        )
        ) {
            $this->configHipay = $this->getConfigurationFromDB($id_shop_group, $id_shop);
            $this->module->getLogs()->logInfos($this->configHipay);

            return true;
        } else {
            throw new Exception($this->l('Update failed, try again.'));
        }
    }

    /**
     * init local config.
     *
     * @return array
     */
    private function insertPaymentsConfig($folderName)
    {
        $paymentMethod = [];

        $files = scandir($this->jsonFilesPath.$folderName);

        foreach ($files as $file) {
            $paymentMethod = array_merge($paymentMethod, $this->addPaymentConfig($file, $folderName));
        }

        return $paymentMethod;
    }

    /**
     * add specific payment config from JSON file.
     *
     * @return array
     */
    private function addPaymentConfig($file, $folderName)
    {
        $paymentMethod = [];

        if (1 == preg_match('/(.*)\.json/', $file)) {
            $json = json_decode(Tools::file_get_contents($this->jsonFilesPath.$folderName.$file), true);
            if (!in_array($json['name'], static::$_deprecatedMethods)) {
                $paymentMethod[$json['name']] = $json['config'];

                $sdkConfig = HiPay\Fullservice\Data\PaymentProduct\Collection::getItem($json['name']);

                if (null !== $sdkConfig) {
                    $paymentMethod[$json['name']] = array_merge($sdkConfig->toArray(), $paymentMethod[$json['name']]);
                }

                if (isset($paymentMethod[$json['name']]['currencies']) &&
                    empty($paymentMethod[$json['name']]['currencies'])
                ) {
                    $paymentMethod[$json['name']]['currencies'] = $this->getActiveCurrencies();
                }

                if (isset($paymentMethod[$json['name']]['countries']) &&
                    empty($paymentMethod[$json['name']]['countries'])
                ) {
                    $paymentMethod[$json['name']]['countries'] = $this->getActiveCountries();
                }
            }
        }

        return $paymentMethod;
    }

    private function getActiveCurrencies()
    {
        $activeCurrenciesIso = [];
        $activeCurrencies = Currency::getCurrencies(false, true);

        foreach ($activeCurrencies as $currency) {
            $activeCurrenciesIso[] = $currency['iso_code'];
        }

        return $activeCurrenciesIso;
    }

    private function getActiveCountries()
    {
        $activeCountriesIso = [];
        $activeCountries = Country::getCountries($this->context->language->id, true);

        foreach ($activeCountries as $country) {
            $activeCountriesIso[] = $country['iso_code'];
        }

        return $activeCountriesIso;
    }

    private function getConfigurationFromDB($id_shop_group = null, $id_shop = null)
    {
        // init multistore
        $id_shop = (is_null($id_shop)) ? (int) Shop::getContextShopID() : (int) $id_shop;
        $id_shop_group = (is_null($id_shop_group)) ? (int) Shop::getContextShopGroupID() : $id_shop_group;

        $configHipay = json_decode(
            Configuration::get('HIPAY_CONFIG', null, $id_shop_group, $id_shop),
            true
        );

        if (!empty($configHipay)) {
            $paymentMeansList = json_decode(
                Configuration::get('HIPAY_PAYMENT_MEANS', null, $id_shop_group, $id_shop),
                true
            );

            if ($paymentMeansList) {
                foreach ($paymentMeansList as $methodGroup => $methods) {
                    foreach ($methods as $key => $methodId) {
                        $methodConfig = json_decode(
                            Configuration::get(
                                'HIPAY_PAYMENT_'.strtoupper($methodId),
                                false,
                                $id_shop_group,
                                $id_shop
                            ),
                            true
                        );

                        $configHipay['payment'][$methodGroup][$methodId] = $methodConfig;
                    }
                }
            }
        }

        return $configHipay;
    }
}
