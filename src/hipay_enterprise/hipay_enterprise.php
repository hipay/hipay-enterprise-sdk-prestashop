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
if (!defined('_PS_VERSION_')) {
    exit;
}

class Hipay_enterprise extends PaymentModule {

    public $limited_countries = array();
    public $hipayConfigTool;
    public $_errors = array();
    public $min_amount = 1;
    public $limited_currencies = array();
    public $currencies_titles = array();

    public function __construct() {
        $this->name = 'hipay_enterprise';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        $this->author = 'HiPay';
        $this->is_eu_compatible = 1;

        $this->bootstrap = true;
        $this->display = 'view';

        $this->displayName = $this->l('HiPay Enterprise');
        $this->description = $this->l('Accept payments by credit card and other local methods with HiPay Enterprise. Very competitive rates, no configuration required!');

        // init log object
        $this->logs = new HipayLogs($this);

        // Compliancy
        $this->limited_countries = array(
            'AT', 'BE', 'CH', 'CY', 'CZ', 'DE', 'DK',
            'EE', 'ES', 'FI', 'FR', 'GB', 'GR', 'HK',
            'HR', 'HU', 'IE', 'IT', 'LI', 'LT', 'LU',
            'LV', 'MC', 'MT', 'NL', 'NO', 'PL', 'PT',
            'RO', 'RU', 'SE', 'SI', 'SK', 'TR'
        );

        $this->countries_titles = array(
            'AT' => $this->l('Austria'),
            'BE' => $this->l('Belgium'),
            'CH' => $this->l('Switzerland'),
            'CY' => $this->l('Cyprus'),
            'CZ' => $this->l('Czech Republic'),
            'DE' => $this->l('Germany'),
            'DK' => $this->l('Denmark'),
            'EE' => $this->l('Estonia'),
            'ES' => $this->l('Spain'),
            'FI' => $this->l('Finland'),
            'FR' => $this->l('France'),
            'GB' => $this->l('United Kingdom'),
            'GR' => $this->l('Greece'),
            'HK' => $this->l('Hong Kong'),
            'HR' => $this->l('Croatia'),
            'HU' => $this->l('Hungary'),
            'IE' => $this->l('Ireland'),
            'IT' => $this->l('Italy'),
            'LI' => $this->l('Liechtenstein'),
            'LT' => $this->l('Lithuania'),
            'LU' => $this->l('Luxembourg'),
            'LV' => $this->l('Latvia'),
            'MC' => $this->l('Monaco'),
            'MT' => $this->l('Malta'),
            'NL' => $this->l('Netherlands'),
            'NO' => $this->l('Norway'),
            'PL' => $this->l('Poland'),
            'PT' => $this->l('Portugal'),
            'RO' => $this->l('Romania'),
            'RU' => $this->l('Russia'),
            'SE' => $this->l('Sweden'),
            'SI' => $this->l('Slovenia'),
            'SK' => $this->l('Slovakia'),
            'TR' => $this->l('Turkey')
        );

        $this->currencies_titles = array(
            'AUD' => $this->l('Australian dollar'),
            'CAD' => $this->l('Canadian dollar'),
            'CHF' => $this->l('Swiss franc'),
            'EUR' => $this->l('Euro'),
            'GBP' => $this->l('Pound sterling'),
            'PLN' => $this->l('Polish złoty'),
            'SEK' => $this->l('Swedish krona'),
            'USD' => $this->l('United States dollar'),
        );

        $this->limited_currencies = array_keys($this->currencies_titles);

        parent::__construct();

        if (!Configuration::get('HIPAY_CONFIG')) {
            $this->warning = $this->l('Please, do not forget to configure your module');
        }

        //configuration is handle by an helper class
        $this->hipayConfigTool = new HipayConfig($this);
    }

    public function getLogs() {
        return $this->logs;
    }

    /**
     * Functions installation HiPay module or uninstall
     */
    public function install() {
        if (extension_loaded('soap') == false) {
            $this->_errors[] = $this->l('You have to enable the SOAP extension on your server to install this module');
            return false;
        }
        $iso_code = Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT'));
        if (in_array($iso_code, $this->limited_countries) == false) {
            $this->_errors[] = $this->l('This module cannot work in your country');
            return false;
        }
        return parent::install() && $this->installHipay();
    }

    public function uninstall() {
        return /* $this->uninstallAdminTab() && */ parent::uninstall() && $this->clearAccountData();
    }

    public function installHipay() {

        $return = $this->installAdminTab();
        $return = $this->updateHiPayOrderStates();
        $return = $this->registerHook('backOfficeHeader');
        if (_PS_VERSION_ >= '1.7') {
            $return17 = $this->registerHook('paymentOptions') && $this->registerHook("header");
            $return = $return && $return17;
        } else if (_PS_VERSION_ < '1.7' && _PS_VERSION_ >= '1.6') {
            $return16 = $this->registerHook('payment');
            $return = $return && $return16;
        }
        return $return;
    }

    public function hookBackOfficeHeader($params) {
        $this->logs->logsHipay('---- START function hookDisplayBackOfficeHeader');
        $this->context->controller->addCSS(($this->_path) . 'views/css/bootstrap-duallistbox.min.css', 'all');
    }

    /**
     * Handling prestashop hook payment. Adding payment methods (PS16)
     * @param type $params
     * @return type
     */
    public function hookPayment($params) {
        $address = new Address(intval($params['cart']->id_address_delivery));
        $country = new Country(intval($address->id_country));
        $currency = new Currency(intval($params['cart']->id_currency));

        $this->smarty->assign(array(
            'domain' => Tools::getShopDomainSSL(true),
            'module_dir' => $this->_path,
            'payment_button' => $this->_path . 'views/img/amexa200.png',
            'min_amount' => $this->min_amount,
            'configHipay' => $this->hipayConfigTool->getConfigHipay(),
            'activated_credit_card' => $this->getActivatedPaymentByCountryAndCurrency("credit_card", $country, $currency),
            'activated_local_payment' => $this->getActivatedPaymentByCountryAndCurrency("local_payment", $country, $currency),
            'lang' => Tools::strtolower($this->context->language->iso_code),
        ));
        $this->smarty->assign('hipay_prod', !(bool) $this->hipayConfigTool->getConfigHipay()["account"]["global"]["sandbox_mode"]);

        return $this->display(dirname(__FILE__), 'views/templates/hook/payment.tpl');
    }

    /**
     * Handling prestashop payment hook. Adding payment methods (PS17)
     * @param type $params
     * @return type
     */
    public function hookPaymentOptions($params) {
        $hipay17 = new HipayEnterpriseNew();
        return $hipay17->hipayPaymentOptions($params);
    }

    /**
     * * Handling prestashop header hook. Adding JS file (PS17)
     * @param type $params
     * @return type
     */
    public function hookHeader($params) {
        $hipay17 = new HipayEnterpriseNew();
        return $hipay17->hookDisplayHeader($params);
    }

    public function installAdminTab() {
        $class_names = [
            'AdminHiPayCapture',
            'AdminHiPayRefund',
            'AdminHiPayConfig',
        ];
        return $this->createTabAdmin($class_names);
    }

    protected function createTabAdmin($class_names) {
        foreach ($class_names as $class_name) {
            $tab = new Tab();
            $tab->active = 1;
            $tab->module = $this->name;
            $tab->class_name = $class_name;
            $tab->id_parent = -1;
            foreach (Language::getLanguages(true) as $lang) {
                $tab->name[$lang['id_lang']] = $this->name;
            }
            if (!$tab->add()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Load configuration page
     * @return string
     */
    public function getContent() {

        $this->logs->logsHipay('##########################');
        $this->logs->logsHipay('---- START function getContent');
        $formGenerator = new HipayForm($this);

        $this->postProcess();

        $configuration = $this->local_path . 'views/templates/admin/configuration.tpl';

        $this->context->smarty->assign(array(
            'module_dir' => $this->_path,
            'config_hipay' => $this->hipayConfigTool->getConfigHipay(),
            'logs' => $this->getLogFiles(),
            'module_url' => AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'),
            'global_payment_methods_form' => $formGenerator->getGlobalPaymentMethodsForm(),
            'fraud_form' => $formGenerator->getFraudForm(),
            'form_errors' => $this->_errors,
            'limitedCurrencies' => $this->currencies_titles,
            'limitedCountries' => $this->countries_titles,
            'this_callback' => $this->context->link->getModuleLink($this->name, 'validation', array(), true),
            'ipaddr' => $_SERVER ['REMOTE_ADDR']
        ));

        $this->logs->logsHipay('---- END function getContent');
        $this->logs->logsHipay('##########################');

        return $this->context->smarty->fetch($configuration);
    }

    /**
     * Process HTTP request send by module conifguration page
     */
    protected function postProcess() {
        //$ur_redirection = AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules');
        $this->logs->logsHipay('---- >> function postProcess');

        if (Tools::isSubmit('logfile')) {
            $logFile = Tools::getValue('logfile');
            $path = _PS_MODULE_DIR_ . $this->logs->getBasePath() . $logFile;
            if (!file_exists($path)) {
                http_response_code(404);
                die('<h1>File not found</h1>');
            } else {
                header('Content-Type: text/plain');
                $content = file_get_contents($path);
                echo $content;
                die();
            }
        } else if (Tools::isSubmit('submitAccount')) {
            $this->logs->logsHipay('---- >> submitAccount');

            $this->saveAccountInformations();

            $this->context->smarty->assign('active_tab', 'account_form');
        } else if (Tools::isSubmit('submitGlobalPaymentMethods')) {
            $this->logs->logsHipay('---- >> submitGlobalPaymentMethods');
            $this->saveGlobalPaymentInformations();
            $this->context->smarty->assign('active_tab', 'payment_form');
        } else if (Tools::isSubmit('creditCardSubmit')) {
            $this->logs->logsHipay('---- >> creditCardSubmit');
            $this->saveCreditCardInformations();
            $this->context->smarty->assign('active_tab', 'payment_form');
        } else if (Tools::isSubmit('localPaymentSubmit')) {
            $this->logs->logsHipay('---- >> localPaymentSubmit');
            $this->saveLocalPaymentInformations();
            $this->context->smarty->assign('active_tab', 'payment_form');
        } else if (Tools::isSubmit('fraudSubmit')) {
            $this->logs->logsHipay('---- >> fraudSubmit');
            $this->saveFraudInformations();
            $this->context->smarty->assign('active_tab', 'fraud_form');
        }
    }

    /**
     * Save Account informations send by config page form
     *
     * @return : bool
     * */
    protected function saveAccountInformations() {
        $this->logs->logsHipay('---- >> function saveAccountInformations');

        try {
            // saving all array "account" in $configHipay
            $accountConfig = array("global" => array(), "sandbox" => array(), "production" => array());

            //requirement : input name in tpl must be the same that name of indexes in $this->configHipay

            foreach ($this->hipayConfigTool->getConfigHipay()["account"]["global"] as $key => $value) {
                $fieldValue = Tools::getValue($key);
                $accountConfig["global"][$key] = $fieldValue;
            }

            foreach ($this->hipayConfigTool->getConfigHipay()["account"]["sandbox"] as $key => $value) {
                $fieldValue = Tools::getValue($key);
                $accountConfig["sandbox"][$key] = $fieldValue;
            }

            foreach ($this->hipayConfigTool->getConfigHipay()["account"]["production"] as $key => $value) {
                $fieldValue = Tools::getValue($key);
                $accountConfig["production"][$key] = $fieldValue;
            }

            //save configuration
            $this->hipayConfigTool->setConfigHiPay("account", $accountConfig);

            $this->_successes[] = $this->l('Settings configuration saved successfully.');
            $this->logs->logsHipay(print_r($this->hipayConfigTool->getConfigHipay(), true));
            return true;
        } catch (Exception $e) {
            // LOGS
            $this->logs->errorLogsHipay($e->getMessage());
            $this->_errors[] = $this->l($e->getMessage());
        }

        return false;
    }

    /**
     * Save Global payment informations send by config page form
     *
     * @return : bool
     * */
    protected function saveGlobalPaymentInformations() {
        $this->logs->logsHipay('---- >> function saveGlobalPaymentInformations');

        try {
            // saving all array "payemnt" "global" in $configHipay
            $accountConfig = array(
                "global" => array(),
                // Not cool but works
                "credit_card" => $this->hipayConfigTool->getConfigHipay()["payment"]["credit_card"],
                "local_payment" => $this->hipayConfigTool->getConfigHipay()["payment"]["local_payment"]
            );

            //requirement : input name in tpl must be the same that name of indexes in $this->configHipay

            foreach ($this->hipayConfigTool->getConfigHipay()["payment"]["global"] as $key => $value) {
                $fieldValue = Tools::getValue($key);
                $this->logs->logsHipay($key . " => " . $fieldValue);
                $accountConfig["global"][$key] = $fieldValue;
            }

            //save configuration
            $this->hipayConfigTool->setConfigHiPay("payment", $accountConfig);

            $this->_successes[] = $this->l('Settings configuration saved successfully.');
            $this->logs->logsHipay(print_r($this->hipayConfigTool->getConfigHipay(), true));
            return true;
        } catch (Exception $e) {
            // LOGS
            $this->logs->errorLogsHipay($e->getMessage());
            $this->_errors[] = $this->l($e->getMessage());
        }

        return false;
    }

    /**
     * save credit cards settings form
     * @return boolean
     */
    public function saveCreditCardInformations() {
        $this->logs->logsHipay('---- >> function saveCreditCardInformations');

        try {
            // saving all array "payemnt" "credit_card" in $configHipay
            $accountConfig = array(
                "global" => $this->hipayConfigTool->getConfigHipay()["payment"]["global"],
                "credit_card" => array(),
                "local_payment" => $this->hipayConfigTool->getConfigHipay()["payment"]["local_payment"]
            );

            //requirement : input name in tpl must be the same that name of indexes in $this->configHipay

            foreach ($this->hipayConfigTool->getConfigHipay()["payment"]["credit_card"] as $card => $conf) {
                foreach ($conf as $key => $value) {
                    if (is_bool(Tools::getValue($card . "_" . $key)) && !Tools::getValue($card . "_" . $key)) {
                        $fieldValue = array();
                    } else {
                        $fieldValue = Tools::getValue($card . "_" . $key);
                    }
                    //         $this->logs->logsHipay($key . " => " . $fieldValue);
                    $accountConfig["credit_card"][$card][$key] = $fieldValue;
                }
            }
            //save configuration
            $this->hipayConfigTool->setConfigHiPay("payment", $accountConfig);

            $this->_successes[] = $this->l('Settings configuration saved successfully.');
            $this->logs->logsHipay(print_r($this->hipayConfigTool->getConfigHipay(), true));
            return true;
        } catch (Exception $e) {
            // LOGS
            $this->logs->errorLogsHipay($e->getMessage());
            $this->_errors[] = $this->l($e->getMessage());
        }

        return false;
    }

    /**
     * save local payment form
     * @return boolean
     */
    public function saveLocalPaymentInformations() {
        $this->logs->logsHipay('---- >> function saveLocalPaymentInformations');

        try {
            // saving all array "payemnt" "local_payment" in $configHipay
            $accountConfig = array(
                "global" => $this->hipayConfigTool->getConfigHipay()["payment"]["global"],
                "credit_card" => $this->hipayConfigTool->getConfigHipay()["payment"]["credit_card"],
                "local_payment" => array()
            );

            //requirement : input name in tpl must be the same that name of indexes in $this->configHipay

            foreach ($this->hipayConfigTool->getConfigHipay()["payment"]["local_payment"] as $card => $conf) {
                foreach ($conf as $key => $value) {
                    if ($key == "currencies" || $key == "logo") {
                        $fieldValue = $this->hipayConfigTool->getConfigHipay()["payment"]["local_payment"][$card][$key];
                    } else if (is_bool(Tools::getValue($card . "_" . $key)) && !Tools::getValue($card . "_" . $key)) {
                        $fieldValue = array();
                    } else {
                        $fieldValue = Tools::getValue($card . "_" . $key);
                    }
                    $accountConfig["local_payment"][$card][$key] = $fieldValue;
                }
            }
            //save configuration
            $this->hipayConfigTool->setConfigHiPay("payment", $accountConfig);

            $this->_successes[] = $this->l('Settings configuration saved successfully.');
            $this->logs->logsHipay(print_r($this->hipayConfigTool->getConfigHipay(), true));
            return true;
        } catch (Exception $e) {
            // LOGS
            $this->logs->errorLogsHipay($e->getMessage());
            $this->_errors[] = $this->l($e->getMessage());
        }

        return false;
    }

    /**
     * Save fraud settings
     * @return boolean
     */
    public function saveFraudInformations() {
        $this->logs->logsHipay('---- >> function saveFraudInformations');

        try {
            // saving all array "fraud" in $configHipay
            $accountConfig = array();

            //requirement : input name in tpl must be the same that name of indexes in $this->configHipay
            foreach ($this->hipayConfigTool->getConfigHipay()["fraud"] as $key => $value) {
                $fieldValue = Tools::getValue($key);
                $accountConfig[$key] = $fieldValue;
            }

            //save configuration
            $this->hipayConfigTool->setConfigHiPay("fraud", $accountConfig);

            $this->_successes[] = $this->l('Settings configuration saved successfully.');
            $this->logs->logsHipay(print_r($this->hipayConfigTool->getConfigHipay(), true));
            return true;
        } catch (Exception $e) {
            // LOGS
            $this->logs->errorLogsHipay($e->getMessage());
            $this->_errors[] = $this->l($e->getMessage());
        }

        return false;
    }

    /**
     * Get the appropriate logs
     * @return string
     */
    protected function getLogFiles() {
        // scan log dir
        $dir = _PS_MODULE_DIR_ . $this->logs->getBasePath();
        $files = scandir($dir, 1);
        // init array files
        $error_files = [];
        $info_files = [];
        $callback_files = [];
        $request_files = [];
        $refund_files = [];
        // dispatch files
        foreach ($files as $file) {
            if (preg_match("/error/i", $file) && count($error_files) < 10) {
                $error_files[] = $file;
            }
            if (preg_match("/callback/i", $file) && count($callback_files) < 10) {
                $callback_files[] = $file;
            }
            if (preg_match("/infos/i", $file) && count($info_files) < 10) {
                $info_files[] = $file;
            }
            if (preg_match("/request/i", $file) && count($request_files) < 10) {
                $request_files[] = $file;
            }
            if (preg_match("/refund/i", $file) && count($refund_files) < 10) {
                $refund_files[] = $file;
            }
        }
        return [
            'error' => $error_files,
            'infos' => $info_files,
            'callback' => $callback_files,
            'request' => $request_files,
            'refund' => $refund_files
        ];
    }

    /**
     * Clear every single merchant account data
     * @return boolean
     */
    protected function clearAccountData() {
        $this->logs->logsHipay('---- >> function clearAccountData');
        Configuration::deleteByName('HIPAY_CONFIG');
        return true;
    }

    /**
     * return an array of payment methods (set in BO configuration) for the customer country and currency
     * @param Country $country
     * @param Currency $currency
     * @return array
     */
    public function getActivatedPaymentByCountryAndCurrency($paymentMethodType, $country, $currency) {
        $activatedPayment = array();
        foreach ($this->hipayConfigTool->getConfigHipay()["payment"][$paymentMethodType] as $name => $settings) {
            if ($settings["activated"] && (empty($settings["countries"]) || in_array($country->iso_code, $settings["countries"]) ) && (empty($settings["currencies"]) || in_array($currency->iso_code, $settings["currencies"]) )) {
                $activatedPayment[$name] = $settings;
            }
        }
        return $activatedPayment;
    }

    /**
     * Add order status
     * @return boolean
     */
    public function updateHiPayOrderStates() {
        $waiting_state_config = 'HIPAY_OS_PENDING';
        $waiting_state_color = '#4169E1';
        $waiting_state_names = [];

        $setup = [
            'delivery' => false,
            'hidden' => false,
            'invoice' => false,
            'logable' => false,
            'module_name' => $this->name,
            'send_email' => false,
        ];

        foreach (Language::getLanguages(false) as $language) {
            if (Tools::strtolower($language['iso_code']) == 'fr') {
                $waiting_state_names[(int) $language['id_lang']] = 'En attente d\'autorisation';
            } else {
                $waiting_state_names[(int) $language['id_lang']] = 'Waiting for authorization';
            }
        }

        $this->saveOrderState($waiting_state_config, $waiting_state_color, $waiting_state_names, $setup);

        return true;
    }

    /**
     * save new order status
     * @param type $config
     * @param type $color
     * @param type $names
     * @param type $setup
     * @return boolean
     */
    protected function saveOrderState($config, $color, $names, $setup) {
        $state_id = Configuration::get($config);

        if ((bool) $state_id == true) {
            $order_state = new OrderState($state_id);
        } else {
            $order_state = new OrderState();
        }

        $order_state->name = $names;
        $order_state->color = $color;

        foreach ($setup as $param => $value) {
            $order_state->{$param} = $value;
        }

        if ((bool) $state_id == true) {
            return $order_state->save();
        } elseif ($order_state->add() == true) {
            Configuration::updateValue($config, $order_state->id);
            @copy($this->local_path . 'logo.gif', _PS_ORDER_STATE_IMG_DIR_ . (int) $order_state->id . '.gif');

            return true;
        }
        return false;
    }

}

if (_PS_VERSION_ >= '1.7') {
    // version 1.7
    require_once(dirname(__FILE__) . '/hipay_enterprise-17.php');
} elseif (_PS_VERSION_ < '1.6') {
    // Version < 1.6
    Tools::displayError('The module HiPay Enterprise is not compatible with your PrestaShop');
}

require_once(dirname(__FILE__) . '/classes/helper/tools/hipayLogs.php');
require_once(dirname(__FILE__) . '/classes/helper/tools/hipayConfig.php');
require_once(dirname(__FILE__) . '/classes/helper/forms/hipayForm.php');
