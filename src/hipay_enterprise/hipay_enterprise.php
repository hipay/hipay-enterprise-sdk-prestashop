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

        // init mapper object
        $this->mapper = new HipayMapper($this);

        // init query object
        $this->db = new HipayDBQuery($this);

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
            'RUB' => $this->l('Russian ruble'),
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
        return /* $this->uninstallAdminTab() && */ parent::uninstall() && $this->clearAccountData() && $this->deleteHipayTable();
    }

    public function installHipay() {

        $return = $this->installAdminTab();
        $return = $this->updateHiPayOrderStates();
        $return = $this->createHipayTable();
        $return = $this->registerHook('backOfficeHeader');
        $return = $this->registerHook('displayAdminOrder');
        if (_PS_VERSION_ >= '1.7') {
            $return17 = $this->registerHook('paymentOptions') && $this->registerHook('header') && $this->registerHook('actionFrontControllerSetMedia');
            $return = $return && $return17;
        } else if (_PS_VERSION_ < '1.7' && _PS_VERSION_ >= '1.6') {
            $return16 = $this->registerHook('payment') && $this->registerHook('paymentReturn');
            $return = $return && $return16;
        }
        return $return;
    }

    public function hookActionFrontControllerSetMedia($params) {
        $hipay17 = new HipayEnterpriseNew();
        return $hipay17->hipayActionFrontControllerSetMedia($params);
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
        $orderTotal = $params['cart']->getOrderTotal();

        $this->context->controller->addJS(array(_MODULE_DIR_ . 'hipay_enterprise/views/js/devicefingerprint.js'));

        $this->smarty->assign(array(
            'domain' => Tools::getShopDomainSSL(true),
            'module_dir' => $this->_path,
            'payment_button' => $this->_path . 'views/img/amexa200.png',
            'min_amount' => $this->min_amount,
            'configHipay' => $this->hipayConfigTool->getConfigHipay(),
            'activated_credit_card' => $this->getActivatedPaymentByCountryAndCurrency("credit_card", $country, $currency),
            'activated_local_payment' => $this->getActivatedPaymentByCountryAndCurrency("local_payment", $country, $currency, $orderTotal),
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

    /**
     * 
     * @param type $params
     * @return type
     */
    public function hookPaymentReturn($params) {

        if (_PS_VERSION_ >= '1.7') {
            $hipay17 = new HipayProfessionalNew();
            $hipay17->hipayPaymentReturnNew($params);
        } elseif (_PS_VERSION_ < '1.7' && _PS_VERSION_ >= '1.6') {
            $this->hipayPaymentReturn($params);
            return $this->display(dirname(__FILE__), 'views/templates/hook/paymentReturn.tpl');
        }
    }

    /**
     * 
     */
    public function HookDisplayAdminOrder() {

        $order = new Order((int) Tools::getValue('id_order'));
        $refundableAmount = $order->getTotalPaid();
        $error = Tools::getValue('hipay_refund_err');
        $stillToCapture = $order->total_paid_tax_incl - $refundableAmount;
        $alreadyCaptured = $this->db->alreadyCaptured($order->id);
        $manualCapture = false;
        $showCapture = false;
        $showRefund = true;
        $partiallyCaptured = false;
        $partiallyRefunded = false;
        
        if($order->getCurrentState() == Configuration::get('HIPAY_OS_PARTIALLY_CAPTURED', null, null, 1)){
            $partiallyCaptured = true;
        }
        
        if($order->getCurrentState() == Configuration::get('HIPAY_OS_PARTIALLY_REFUNDED', null, null, 1)){
            $partiallyRefunded = true;
        }

        if (isset($this->hipayConfigTool->getConfigHipay()["payment"]["global"]["capture_mode"]) && $this->hipayConfigTool->getConfigHipay()["payment"]["global"]["capture_mode"] == "manual") {
            $manualCapture = true;
        }

        if ((boolean) $order->getHistory($this->context->language->id, Configuration::get('HIPAY_OS_PENDING', null, null, 1)) || (boolean) $order->getHistory($this->context->language->id, Configuration::get('HIPAY_OS_CHALLENGED', null, null, 1))) {
            // Order was previously pending or challenged
            // Then check if its currently in authorized state
            if ($order->current_state == Configuration::get('HIPAY_OS_AUTHORIZED', null, null, 1)) {
                $manualCapture = true;
            }
        }


        if ($order->getCurrentState() == Configuration::get('HIPAY_OS_AUTHORIZED', null, null, 1) || $order->getCurrentState() == _PS_OS_PAYMENT_ || $order->getCurrentState() == Configuration::get('HIPAY_OS_PARTIALLY_CAPTURED', null, null, 1)) {
            $showCapture = true;
        }

        $paymentProduct = $this->db->getPaymentProductFromMessage($order->id);
        if ($paymentProduct) {
            if (isset($this->hipayConfigTool->getConfigHipay()["payment"]["local_payment"][$paymentProduct])) {
                if (!(bool) $this->hipayConfigTool->getConfigHipay()["payment"]["local_payment"][$paymentProduct]["canRefund"]) {
                    $showRefund = false;
                }
                if (!(bool) $this->hipayConfigTool->getConfigHipay()["payment"]["local_payment"][$paymentProduct]["canManualCapture"]) {
                    $showCapture = false;
                }
            } else if (isset($this->hipayConfigTool->getConfigHipay()["payment"]["credit_card"][$paymentProduct])) {
                if (!(bool) $this->hipayConfigTool->getConfigHipay()["payment"]["credit_card"][$paymentProduct]["canRefund"]) {
                    $showRefund = false;
                }
                if (!(bool) $this->hipayConfigTool->getConfigHipay()["payment"]["credit_card"][$paymentProduct]["canManualCapture"]) {
                    $showCapture = false;
                }
            }
        }

        if ($order->getCurrentState() == Configuration::get('HIPAY_OS_REFUND_REQUESTED')) {
            $showRefund = false;
        }

        if ($order->getCurrentState() == _PS_OS_ERROR_ || $order->getCurrentState() == _PS_OS_CANCELED_ || $order->getCurrentState() == Configuration::get('HIPAY_OS_EXPIRED', null, null, 1) || $order->getCurrentState() == Configuration::get('HIPAY_OS_REFUND_REQUESTED', null, null, 1) || $order->getCurrentState() == Configuration::get('HIPAY_OS_REFUNDED', null, null, 1)) {
            $showCapture = false;
        }

        $this->context->smarty->assign(array(
            'refundableAmountDisplay' => Tools::displayPrice($refundableAmount),
            'refundableAmount' => $refundableAmount,
            'error' => $error,
            'stillToCaptureDisplay' => Tools::displayPrice($stillToCapture),
            'stillToCapture' => $stillToCapture,
            'alreadyCaptured' => $alreadyCaptured,
            'partiallyCaptured' => $partiallyCaptured,
            'partiallyRefunded' => $partiallyRefunded,
            'showCapture' => $showCapture,
            'showRefund' => $showRefund,
            'manualCapture' => $manualCapture
        ));

        return $this->display(dirname(__FILE__), 'views/templates/hook/maintenance.tpl');
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

        $this->postProcess();

        $formGenerator = new HipayForm($this);

        $configuration = $this->local_path . 'views/templates/admin/configuration.tpl';

        $psCategories = $this->mapper->getPrestashopCategories();
        $hipayCategories = $this->mapper->getHipayCategories();

        $psCarriers = $this->mapper->getPrestashopCarriers();
        $hipayCarriers = $this->mapper->getHipayCarriers();

        $mappedCategories = $this->mapper->getMappedCategories($this->context->shop->id);
        $mappedCarriers = $this->mapper->getMappedCarriers($this->context->shop->id);

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
            'ipaddr' => $_SERVER ['REMOTE_ADDR'],
            'psCategories' => $psCategories,
            'hipayCategories' => $hipayCategories,
            'mappedCategories' => $mappedCategories,
            'psCarriers' => $psCarriers,
            'hipayCarriers' => $hipayCarriers,
            'mappedCarriers' => $mappedCarriers,
            'lang' => Tools::strtolower($this->context->language->iso_code),
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
        } else if (Tools::isSubmit('submitCategoryMapping')) {
            $this->logs->logsHipay('---- >> submitCategoryMapping');
            $this->saveCategoryMappingInformations();
            $this->context->smarty->assign('active_tab', 'category_form');
        } else if (Tools::isSubmit('submitCarrierMapping')) {
            $this->logs->logsHipay('---- >> submitCarrierMapping');
            $this->saveCarrierMappingInformations();
            $this->context->smarty->assign('active_tab', 'carrier_form');
        }
    }

    /**
     * Save Carrier Mapping informations send by config page form
     *
     * @return : bool
     * */
    protected function saveCarrierMappingInformations() {
        $this->logs->logsHipay('---- >> function saveCarrierMappingInformations');

        try {

            $psCarriers = $this->mapper->getPrestashopCarriers();

            $mapping = array();
            $this->_errors = array();
            foreach ($psCarriers as $car) {

                $psMapCar = Tools::getValue('ps_map_' . $car["id_carrier"]);
                $hipayMapCarMode = Tools::getValue('hipay_map_mode_' . $car["id_carrier"]);
                $hipayMapCarShipping = Tools::getValue('hipay_map_shipping_' . $car["id_carrier"]);
                $hipayMapCarOETA = Tools::getValue('ps_map_prep_eta_' . $car["id_carrier"]);
                $hipayMapCarDETA = Tools::getValue('ps_map__delivery_eta_' . $car["id_carrier"]);

                if (empty($psMapCar) || empty($hipayMapCarMode) || empty($hipayMapCarShipping) || empty($hipayMapCarOETA) || empty($hipayMapCarDETA)) {
                    $this->_errors[] = $this->l("all carrier mapping fields are required");
                }

                //   if ($this->mapper->hipayCarrierExist($hipayMapCar)) {
                $mapping[] = array("pscar" => $psMapCar, "hipaycarmode" => $hipayMapCarMode, "hipaycarshipping" => $hipayMapCarShipping, "prepeta" => $hipayMapCarOETA, "deliveryeta" => $hipayMapCarDETA);
                //   }
            }

            if (!empty($this->_errors)) {
                $this->_errors = array(end($this->_errors));
                return false;
            }

            $response = $this->mapper->setMapping(HipayMapper::HIPAY_CARRIER_MAPPING, $mapping);
            $this->_successes[] = $this->l('Settings configuration saved successfully.');
            return true;
        } catch (Exception $e) {
            // LOGS
            $this->logs->errorLogsHipay($e->getMessage());
            $this->_errors[] = $this->l($e->getMessage());
        }

        return false;
    }

    /**
     * Save Category Mapping informations send by config page form
     *
     * @return : bool
     * */
    protected function saveCategoryMappingInformations() {
        $this->logs->logsHipay('---- >> function saveCategoryMappingInformations');

        try {

            $psCategories = $this->mapper->getPrestashopCategories();

            $mapping = array();

            foreach ($psCategories as $cat) {

                $psMapCat = Tools::getValue('ps_map_' . $cat["id_category"]);
                $hipayMapCat = Tools::getValue('hipay_map_' . $cat["id_category"]);

                if (empty($psMapCat) || empty($hipayMapCat)) {
                    $this->_errors[] = $this->l("all category mapping fields are required");
                }

                if ($this->mapper->hipayCategoryExist($hipayMapCat)) {
                    $mapping[] = array("pscat" => $psMapCat, "hipaycat" => $hipayMapCat);
                }
            }

            if (!empty($this->_errors)) {
                $this->_errors = array(end($this->_errors));
                return false;
            }

            $response = $this->mapper->setMapping(HipayMapper::HIPAY_CAT_MAPPING, $mapping);
            $this->_successes[] = $this->l('Settings configuration saved successfully.');
            return true;
        } catch (Exception $e) {
            // LOGS
            $this->logs->errorLogsHipay($e->getMessage());
            $this->_errors[] = $this->l($e->getMessage());
        }

        return false;
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
                    //prevent specific fields from being updated
                    if ($key == "currencySelectorReadOnly" || $key == "countrySelectorReadOnly" || $key == "logo" || $key == "displayName") {
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
    public function getActivatedPaymentByCountryAndCurrency($paymentMethodType, $country, $currency, $orderTotal = 1) {
        $activatedPayment = array();
        foreach ($this->hipayConfigTool->getConfigHipay()["payment"][$paymentMethodType] as $name => $settings) {
            if ($settings["activated"] &&
                    (empty($settings["countries"]) || in_array($country->iso_code, $settings["countries"]) ) &&
                    (empty($settings["currencies"]) || in_array($currency->iso_code, $settings["currencies"]) ) &&
                    $orderTotal >= $settings["minAmount"]
            ) {

                if ($paymentMethodType == "local_payment") {
                    if (Configuration::get('PS_ROUND_TYPE') == Order::ROUND_LINE || Configuration::get('PS_ROUND_TYPE') == Order::ROUND_ITEM || !$settings["forceBasket"]) {
                        $activatedPayment[$name] = $settings;
                        $activatedPayment[$name]["link"] = $this->context->link->getModuleLink($this->name, 'redirectlocal', array("method" => $name), true);
                        $activatedPayment[$name]['payment_button'] = $this->_path . 'views/img/' . $settings["logo"];
                    }
                } else {
                    $activatedPayment[$name] = $settings;
                }
            }
        }
        return $activatedPayment;
    }

    /**
     * Add order status
     * @return boolean
     */
    public function updateHiPayOrderStates() {

        $hipayStates = array(
            "HIPAY_OS_PENDING" => array(
                "waiting_state_color" => "#4169E1",
                "setup" => array(
                    'delivery' => false,
                    'hidden' => false,
                    'invoice' => false,
                    'logable' => false,
                    'module_name' => $this->name,
                    'send_email' => false,
                ),
                "name_FR" => "En attente d'autorisation (Hipay)",
                "name_EN" => "Waiting for authorization (Hipay)",
            ),
            "HIPAY_OS_EXPIRED" => array(
                "waiting_state_color" => "#8f0621",
                "setup" => array(
                    'delivery' => false,
                    'hidden' => false,
                    'invoice' => false,
                    'logable' => false,
                    'module_name' => $this->name,
                    'send_email' => false,
                ),
                "name_FR" => "Contesté (Hipay)",
                "name_EN" => "Challenged (Hipay)",
            ),
            "HIPAY_OS_CHALLENGED" => array(
                "waiting_state_color" => "#4169E1",
                "setup" => array(
                    'delivery' => false,
                    'hidden' => false,
                    'invoice' => false,
                    'logable' => false,
                    'module_name' => $this->name,
                    'send_email' => false,
                ),
                "name_FR" => "Expiré (Hipay)",
                "name_EN" => "Expired (Hipay)",
            ),
            "HIPAY_OS_AUTHORIZED" => array(
                "waiting_state_color" => "LimeGreen",
                "setup" => array(
                    'delivery' => false,
                    'hidden' => false,
                    'invoice' => false,
                    'logable' => false,
                    'module_name' => $this->name,
                    'send_email' => false,
                ),
                "name_FR" => "Paiement autorisé (Hipay)",
                "name_EN" => "Payment authorized (Hipay)",
            ),
            "HIPAY_OS_CAPTURE_REQUESTED" => array(
                "waiting_state_color" => "LimeGreen",
                "setup" => array(
                    'delivery' => false,
                    'hidden' => false,
                    'invoice' => false,
                    'logable' => false,
                    'module_name' => $this->name,
                    'send_email' => false,
                ),
                "name_FR" => "Capture demmandé (Hipay)",
                "name_EN" => "Capture requested (Hipay)",
            ),
            "HIPAY_OS_CAPTURED" => array(
                "waiting_state_color" => "LimeGreen",
                "setup" => array(
                    'delivery' => false,
                    'hidden' => false,
                    'invoice' => false,
                    'logable' => false,
                    'module_name' => $this->name,
                    'send_email' => false,
                ),
                "name_FR" => "Capturé (Hipay)",
                "name_EN" => "Captured (Hipay)",
            ),
            "HIPAY_OS_PARTIALLY_CAPTURED" => array(
                "waiting_state_color" => "LimeGreen",
                "setup" => array(
                    'delivery' => false,
                    'hidden' => false,
                    'invoice' => true,
                    'logable' => false,
                    'module_name' => $this->name,
                    'send_email' => false,
                    'paid' => false
                ),
                "name_FR" => "Capture partielle (Hipay)",
                "name_EN" => "partially captured (Hipay)",
            ),
            "HIPAY_OS_REFUND_REQUESTED" => array(
                "waiting_state_color" => "#ec2e15",
                "setup" => array(
                    'delivery' => false,
                    'hidden' => false,
                    'invoice' => true,
                    'logable' => false,
                    'module_name' => $this->name,
                    'send_email' => false,
                    'paid' => false
                ),
                "name_FR" => "Remboursement demandé (Hipay)",
                "name_EN" => "Refund requested (Hipay)",
            ),
            "HIPAY_OS_REFUNDED_PARTIALLY" => array(
                "waiting_state_color" => "HotPink",
                "setup" => array(
                    'delivery' => false,
                    'hidden' => false,
                    'invoice' => true,
                    'logable' => false,
                    'module_name' => $this->name,
                    'send_email' => false,
                    'paid' => false
                ),
                "name_FR" => "Remboursé Partiellement (Hipay)",
                "name_EN" => "Refunded Partially (Hipay)",
            ),
            "HIPAY_OS_REFUNDED" => array(
                "waiting_state_color" => "HotPink",
                "setup" => array(
                    'delivery' => false,
                    'hidden' => false,
                    'invoice' => true,
                    'logable' => false,
                    'module_name' => $this->name,
                    'send_email' => false,
                    'paid' => false
                ),
                "name_FR" => "Remboursé (Hipay)",
                "name_EN" => "Refunded (Hipay)",
            ),
            "HIPAY_OS_DENIED" => array(
                "waiting_state_color" => "#8f0621",
                "setup" => array(
                    'delivery' => false,
                    'hidden' => false,
                    'invoice' => false,
                    'logable' => false,
                    'module_name' => $this->name,
                    'send_email' => false,
                    'paid' => false
                ),
                "name_FR" => "Refusé (Hipay)",
                "name_EN" => "Denied (Hipay)",
            ),
            "HIPAY_OS_CHARGEDBACK" => array(
                "waiting_state_color" => "#f89406",
                "setup" => array(
                    'delivery' => false,
                    'hidden' => false,
                    'invoice' => true,
                    'logable' => false,
                    'module_name' => $this->name,
                    'send_email' => false,
                    'paid' => false
                ),
                "name_FR" => "Charged back (Hipay)",
                "name_EN" => "Charged back (Hipay)",
            )
        );



        foreach ($hipayStates as $name => $state) {
            $waiting_state_config = $name;
            $waiting_state_color = $state["waiting_state_color"];
            $waiting_state_names = array();

            $setup = $state["setup"];

            foreach (Language::getLanguages(false) as $language) {
                if (Tools::strtolower($language['iso_code']) == 'fr') {
                    $waiting_state_names[(int) $language['id_lang']] = $state["name_FR"];
                } else {
                    $waiting_state_names[(int) $language['id_lang']] = $state["name_EN"];
                }
            }

            $this->saveOrderState($waiting_state_config, $waiting_state_color, $waiting_state_names, $setup);
        }

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

    /**
     * 
     * @param type $params
     * @return type
     */
    private function hipayPaymentReturn($params) {
        // Payment Return for PS1.6
        if ($this->active == false) {
            return;
        }
        $order = $params['objOrder'];
        if ($order->getCurrentOrderState()->id != Configuration::get('PS_OS_ERROR')) {
            $this->smarty->assign('status', 'ok');
        }
        $this->smarty->assign(
                array(
                    'id_order' => $order->id,
                    'reference' => $order->reference,
                    'params' => $params,
                    'total_to_pay' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
                    'shop_name' => $this->context->shop->name,
                )
        );
    }

    /**
     * 
     */
    private function createHipayTable() {
        $this->mapper->createTable();

        return true;
    }

    /**
     * 
     */
    private function deleteHipayTable() {
        $this->mapper->deleteTable();
        return true;
    }

    public function HookDisplayAdminOrderez() {
        $orderLoaded = new OrderCore(Tools::getValue('id_order'));
        // Verify the payment method name
        $payment_method_sql = "SELECT payment_method FROM `" . _DB_PREFIX_ . "order_payment` WHERE order_reference='" . $orderLoaded->reference . "'";
        $payment_method = Db::getInstance()->executeS($payment_method_sql);
        $hide_refund = false;
        $hide_capture = false;
        if (isset($payment_method [0] ['payment_method'])) {
            $explode_payment_local_card = explode($this->displayName . ' via', $payment_method [0] ['payment_method']);
            if (isset($explode_payment_local_card [1])) {
                $payment_local_card = $explode_payment_local_card [1];
                $local_cards = $this->checkLocalCards();
                if (isset($local_cards)) {
                    if (count($local_cards)) {
                        foreach ($local_cards as $value) {
                            if ((string) $value->name == trim($payment_local_card)) {
                                if ((string) $value->refund == '0') {
                                    $hide_refund = true;
                                }
                                if ((string) $value->manualcapture == '0') {
                                    $hide_capture = true;
                                }
                            }
                        }
                    }
                }
                if (Tools::strtolower(trim($payment_local_card)) == 'bcmc')
                    $hide_refund = true;
            }
            // Verify if already CAPTURED
            $payment_message_sql = "SELECT * FROM `" . _DB_PREFIX_ . "message` WHERE id_order='" . $orderLoaded->id . "' AND message LIKE 'HiPay%Status : 118%'";
            $paymentmessage = Db::getInstance()->executeS($payment_message_sql);
            if (empty($paymentmessage))
                $hide_refund = true;
        }
        $currentState = $orderLoaded->current_state;
        $stateLoaded = new OrderState($currentState);
        // Check if current state = Configuration::get( 'HIPAY_REFUND_REQUESTED' )
        // If renfund requested, then prevent any further refund until current refund has been completed
        if ($currentState == Configuration::get('HIPAY_REFUND_REQUESTED')) {
            $hide_refund = true;
        }
        $form = '';
        if ($orderLoaded->module == $this->name) {
            if ($stateLoaded->paid) {

                /**
                 * variables de vérification
                 */
                $orderTotal = $orderLoaded->total_products_wt + $orderLoaded->total_shipping_tax_incl + $orderLoaded->total_wrapping_tax_incl;
                $totalEncaissement = $this->getOrderTotalAmountCaptured($orderLoaded->reference);
                $adminDir = _PS_ADMIN_DIR_;
                $adminDir = Tools::substr($adminDir, strrpos($adminDir, '/'));
                $adminDir = Tools::substr($adminDir, strrpos($adminDir, '\\'));
                $adminDir = str_replace('\\', '', $adminDir);
                $adminDir = str_replace('/', '', $adminDir);
                $context = Context::getContext();
                $form_action = '../index.php?fc=module&module=' . $this->name . '&controller=refund';
                if (version_compare(_PS_VERSION_, '1.6', '>')) {
                    $form .= '<div id="htmlcontent" class="panel">
	                 <div class="panel-heading"><img src="../img/admin/money.gif">&nbsp;&nbsp;' . $this->l('Hipay Refund') . '</div>
	                 <fieldset>';
                } else {
                    $form .= '
		        		<div style="height:10px"></div>
		        		<div>
		        		<fieldset>';
                    $form .= '<legend><img src="../img/admin/money.gif">&nbsp;&nbsp;' . $this->l('Hipay Refund') . '</legend>';
                }
                if (Tools::getValue('hipay_refund_err')) {
                    if (Tools::getValue('hipay_refund_err') == 'ok') {
                        $form .= '<p style="" class="conf">
									<a style="position: relative; top: -100px;" id="hipay"></a>
			        				' . $this->l('Request successfully sent') . '
						        	</p>';
                    } else {
                        if (_PS_VERSION_ >= '1.6') {
                            $form .= '<style media="screen" type="text/css">
							p.error{
								color:red;
							}
							</style>';
                        }
                        $form .= '<p style="" class="error">
									<a style="position: relative; top: -100px;" id="hipay"></a>
						        	' . Tools::getValue('hipay_refund_err') . '
						        	</p>';
                    }
                }
                /**
                 * FORMULAIRE DE REMBOURSEMENT
                 */
                $form .= '
		        		<fieldset>
		        			<legend>' . $this->l('Refund this order') . '</legend>';
                // summary of amount captured, amount that can be refunded
                $form .= '
	        			<table class="table" width="auto" cellspacing="0" cellpadding="0">
	        				<tr>
	        					<th>' . $this->l('Amount that can be refunded') . '</th>
	        				</tr>
	        				<tr>
	        					<td class="value"><span class="badge badge-success">' . Tools::displayPrice($totalEncaissement) . '</span></td>
	        				</tr>
	        			</table>';
                $form .= '<div style="font-size: 12px;">
					<sup>*</sup> ' . $this->l('Amount will be updated once the refund will be confirmed by HiPay Fullservice') . '</div>';
                if ($totalEncaissement != 0 && $hide_refund == false) {
                    $form .= '
					<script>
                       $( document ).ready(function() {
                            $("#hipay_refund_form").submit( function(){
                                var type=$("[name=hipay_refund_type]:checked").val();
                                var proceed = "true";
                                /*if(type=="partial")
                                {
                                    var amount=$("#hidden2").val();
                                    if(amount == "" || !$.isNumeric(amount))
                                    {
                                        alert("' . $this->l('Please enter an amount') . '");
                                        proceed = "false";
                                    }
                                    if(amount<=0)
                                    {
                                        alert("' . $this->l('Please enter an amount greater than zero') . '");
                                        proceed = "false";
                                    }
                                    if(amount>' . $totalEncaissement . ')
                                    {
                                        alert("' . $this->l('Amount exceeding authorized amount') . '");
                                        proceed = "false";
                                    }
                                }*/
                                if(proceed == "false")
                                {
                                    return false;
                                }else{
                                    return true;
                                }
                                return false;
                            });
					    });
                    </script>
					<form action="' . $form_action . '" method="post" id="hipay_refund_form">';
                    $form .= '<input type="hidden" name="id_order" value="' . Tools::getValue('id_order') . '" />';
                    $form .= '<input type="hidden" name="id_emp" value="' . $context->employee->id . '" />';
                    $form .= '<input type="hidden" name="token" value="' . Tools::getValue('token') . '" />';
                    $form .= '<input type="hidden" name="adminDir" value="' . $adminDir . '" />';
                    $form .= '<p><table>';
                    $form .= '<tr><td><label for="hipay_refund_type">' . $this->l('Refund type') . '</label></td><td>&nbsp;</td>';
                    if ((boolean) $orderLoaded->getHistory($context->language->id, Configuration::get('HIPAY_REFUNDED'))) {
                        $form .= '<td>';
                        $form .= '<input type="radio" onclick="javascript:document.getElementById(\'hidden1\').style.display=\'inline\';javascript:document.getElementById(\'hidden2\').style.display=\'inline\';" name="hipay_refund_type" id="hipay_refund_type" value="partial" checked />' . $this->l('Partial') . '</td></tr>';
                    } else {
                        $form .= '<td><input type="radio" onclick="javascript:document.getElementById(\'hidden1\').style.display=\'none\';javascript:document.getElementById(\'hidden2\').style.display=\'none\';" name="hipay_refund_type" value="complete" checked />' . $this->l('Complete') . '<br/>';
                        $form .= '<input type="radio" onclick="javascript:document.getElementById(\'hidden1\').style.display=\'inline\';javascript:document.getElementById(\'hidden2\').style.display=\'inline\';" name="hipay_refund_type" id="hipay_refund_type" value="partial" />' . $this->l('Partial') . '</td></tr>';
                    }
                    $form .= '</table></p>';
                    $form .= '<p>';
                    if ((boolean) $orderLoaded->getHistory($context->language->id, Configuration::get('HIPAY_REFUNDED'))) {
                        $form .= '<label style="display:block;" id="hidden1" for="">' . $this->l('Refund amount') . '</label>';
                        $form .= '<input style="display:block;" id="hidden2" type="text" name="hipay_refund_amount" value="" />';
                    } else {
                        $form .= '<label style="display:none;" id="hidden1" for="">' . $this->l('Refund amount') . '</label>';
                        $form .= '<input style="display:none;" id="hidden2" type="text" name="hipay_refund_amount" value="" />';
                    }
                    $form .= '</p>';
                    $form .= '<label>&nbsp;</label><input type="submit" name="hipay_refund_submit" class="btn btn-primary" value="' . $this->l('Refund') . '" />';
                    $form .= '</form>';
                } else {
                    $form .= $this->l('This order has already been fully refunded or refund is not allowed');
                }
                $form .= '</fieldset>';
                $form .= '</fieldset></div>';
            }
            $showCapture = false;
            if ($orderLoaded->current_state == Configuration::get('HIPAY_AUTHORIZED') || $orderLoaded->current_state == _PS_OS_PAYMENT_ || $orderLoaded->current_state == Configuration::get('HIPAY_PARTIALLY_CAPTURED')) {
                $showCapture = true;
            }
            if ($showCapture) {
                // Modification to allow a full capture if the previous state was HIPAY_PENDING or HIPAY_CHALLENGED
                $get_HIPAY_MANUALCAPTURE = Configuration::get('HIPAY_MANUALCAPTURE');
                $context = Context::getContext();
                if ((boolean) $orderLoaded->getHistory($context->language->id, Configuration::get('HIPAY_PENDING')) || (boolean) $orderLoaded->getHistory($context->language->id, Configuration::get('HIPAY_CHALLENGED'))
                ) {
                    // Order was previously pending or challenged
                    // Then check if its currently in authorized state
                    if ($orderLoaded->current_state == Configuration::get('HIPAY_AUTHORIZED')) {
                        $get_HIPAY_MANUALCAPTURE = 1;
                    }
                } else {
                    // Nothing to do, classical system behaviour will take over
                }
                // FORCING ORDER CAPTURED AMOUNT UPDATE
                $sql = "UPDATE `" . _DB_PREFIX_ . "order_payment`
                        SET `amount` = '" . $this->getOrderTotalAmountCaptured($orderLoaded->reference) . "'
                        WHERE `order_reference`='" . $orderLoaded->reference . "'";
                //Db::getInstance()->execute($sql);
                /**
                 * FORMULAIRE DE CAPTURE
                 */
                if (version_compare(_PS_VERSION_, '1.6', '>')) {
                    $form .= '<div id="htmlcontent" class="panel">
	                 <div class="panel-heading"><img src="../img/admin/money.gif">&nbsp;&nbsp;' . $this->l('Hipay Capture') . '</div>
	                 <fieldset>';
                } else {
                    $form .= '
		        		<div style="height:10px"></div>
		        		<div>
		        		<fieldset>';
                    $form .= '<legend><img src="../img/admin/money.gif">&nbsp;&nbsp;' . $this->l('Hipay Capture') . '</legend>';
                }
                if ($get_HIPAY_MANUALCAPTURE) {
                    if (Tools::getValue('hipay_err')) {
                        if (Tools::getValue('hipay_err') == 'ok') {
                            $form .= '<p style="" class="conf">
									<a style="position: relative; top: -100px;" id="hipay"></a>
			        				' . $this->l('Request successfully sent') . '
						        	</p>';
                        } else {
                            if (_PS_VERSION_ >= '1.6') {
                                $form .= '<style media="screen" type="text/css">
								p.error{
									color:red;
								}
								</style>';
                            }
                            $form .= '<p style="" class="error">
									<a style="position: relative; top: -100px;" id="hipay"></a>
						        	' . Tools::getValue('hipay_err') . '
						        	</p>';
                        }
                    }
                    $form_action = '../index.php?fc=module&module=' . $this->name . '&controller=capture';
                    $form .= '
			        		<div style="height:10px"></div>
			        		<fieldset>
			        			<legend>' . $this->l('Capture this order') . '</legend>';
                    $orderTotal = $orderLoaded->total_products_wt + $orderLoaded->total_shipping_tax_incl + $orderLoaded->total_wrapping_tax_incl;
                    $totalEncaissement = $this->getOrderTotalAmountCaptured($orderLoaded->reference);
                    $stillToCapture = $orderTotal - $totalEncaissement;

                    // Modif ajout texte warning si montant pas completement capture
                    if ($stillToCapture) {
                        // Retrieve _PS_OS_PAYMENT_ real name
                        $form .= '
						   <table class="table" width="100%" cellspacing="0" cellpadding="0">
						   <tr>
								<th>' . $this->l('The order has not been fully captured.') . '</th>
							</tr><tr>
								<th>' . $this->l('To generate the invoice, you must capture the remaining amount due which will generate an invoice once the order full amount has been captured.') . '</th>
						   </tr>
							</table>
							<p>&nbsp;</p>
							';
                    }
                    // summary of amount captured, still to capture
                    $form .= '
                                       <table class="table" width="100%" cellspacing="0" cellpadding="0">
                                       <tr>
                                       	<th>' . $this->l('Amount already captured') . '</th>
                                       	<th>' . $this->l('Amount still to be captured') . '</th>
                                       </tr>
                                       <tr>
                                       	<td class="value"><span class="badge badge-success">' . Tools::displayPrice($totalEncaissement) . '</span></td>
                                       	<td class="value"><span class="badge badge-info">' . Tools::displayPrice($stillToCapture) . '</span></td>
                                       </tr>
                                       </table>';
                    $form .= '<div style="font-size: 12px;">
					<sup>*</sup> ' . $this->l('Amounts will be updated once the capture will be confirmed by HiPay Fullservice') . '</div>';
                    $adminDir = _PS_ADMIN_DIR_;
                    $adminDir = Tools::substr($adminDir, strrpos($adminDir, '/'));
                    $adminDir = Tools::substr($adminDir, strrpos($adminDir, '\\'));
                    $adminDir = str_replace('\\', '', $adminDir);
                    $adminDir = str_replace('/', '', $adminDir);
                    $context = Context::getContext();
                    // Last check
                    // If state should not allow user to manually capture then disable display
                    if ($currentState == _PS_OS_ERROR_ || $currentState == _PS_OS_CANCELED_
                            /* || $currentState == _PS_OS_PAYMENT_ issue with partical capture returning state _PS_OS_PAYMENT_ */ || $currentState == Configuration::get('HIPAY_EXPIRED') || $currentState == Configuration::get('HIPAY_REFUND_REQUESTED') || $currentState == Configuration::get('HIPAY_REFUNDED')) {
                        $stillToCapture = false;
                    }
                    if (($stillToCapture) && $hide_capture == false) {
                        $form .= "<script>
                           $( document ).ready(function() {
                            $('#hipay_capture_form').submit( function(){
                                var type=$('[name=hipay_capture_type]:checked').val();
                                var proceed = 'true';
                                /*if(type=='partial')
                                {
                                    var amount=$('#hidden4').val();
                                    if(amount == '')
                                    {
                                        alert('" . $this->l('Please enter an amount') . "');
                                        proceed = 'false';
                                    }
                                    if(amount<=0)
                                    {
                                        alert('" . $this->l('Please enter an amount greater than zero') . "');
                                        proceed = 'false';
                                    }
                                    if(amount>" . $stillToCapture . ")
                                    {
                                        alert('" . $this->l('Amount exceeding authorized amount') . "');
                                        proceed = 'false';
                                    }
                                }*/
    
                                if(proceed == 'false')
                                {
                                    return false;
                                }else{
                                    return true;
                                }
    
                                return false;
                            });
					    });
                        </script>";
                        $form .= '<form action="' . $form_action . '" method="post" id="hipay_capture_form">';
                        $form .= '<input type="hidden" name="id_order" value="' . Tools::getValue('id_order') . '" />';
                        $form .= '<input type="hidden" name="id_emp" value="' . $context->employee->id . '" />';
                        $form .= '<input type="hidden" name="token" value="' . Tools::getValue('token') . '" />';
                        $form .= '<input type="hidden" name="adminDir" value="' . $adminDir . '" />';
                        $form .= '<p><table>';
                        $form .= '<tr><td><label for="hipay_capture_type">' . $this->l('Capture type') . '</label></td><td>&nbsp;</td>';
                        if ((boolean) $orderLoaded->getHistory($context->language->id, Configuration::get('HIPAY_PARTIALLY_CAPTURED'))) {
                            $form .= '<td>';
                            $form .= '<input type="radio" onclick="javascript:document.getElementById(\'hidden3\').style.display=\'inline\';javascript:document.getElementById(\'hidden4\').style.display=\'inline\';" name="hipay_capture_type" id="hipay_capture_type" value="partial" checked />' . $this->l('Partial') . '</td></tr>';
                        } else {
                            $form .= '<td><input type="radio" onclick="javascript:document.getElementById(\'hidden3\').style.display=\'none\';javascript:document.getElementById(\'hidden4\').style.display=\'none\';" name="hipay_capture_type" value="complete" checked />' . $this->l('Complete') . '<br>';
                            $form .= '<input type="radio" onclick="javascript:document.getElementById(\'hidden3\').style.display=\'inline\';javascript:document.getElementById(\'hidden4\').style.display=\'inline\';" name="hipay_capture_type" id="hipay_capture_type" value="partial" />' . $this->l('Partial') . '</td></tr>';
                        }
                        $form .= '</table></p>';
                        $form .= '<p>';
                        if ((boolean) $orderLoaded->getHistory($context->language->id, Configuration::get('HIPAY_PARTIALLY_CAPTURED'))) {
                            $form .= '<label style="display:block;" id="hidden3" >' . $this->l('Capture amount') . '</label>';
                            $form .= '<input style="display:block;" id="hidden4" type="text" name="hipay_capture_amount" value="' . round($stillToCapture, 2) . '" />';
                        } else {
                            $form .= '<label style="display:none;" id="hidden3" >' . $this->l('Capture amount') . '</label>';
                            $form .= '<input style="display:none;" id="hidden4" type="text" name="hipay_capture_amount" value="' . round($stillToCapture, 2) . '" />';
                        }
                        $form .= '</p>';
                        $form .= '<label>&nbsp;</label><input type="submit" name="hipay_capture_submit" class="btn btn-primary" value="' . $this->l('Capture') . '" />';
                        $form .= '</form>';
                    } else {
                        $form .= '<p>' . $this->l('This order has already been fully captured, cannot be captured or waiting authorization for capture') . '</p>';
                    }
                    $form .= '</fieldset>';
                }
                $form .= '</fieldset></div>';
            }
            return $form;
        }
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
require_once(dirname(__FILE__) . '/classes/helper/tools/hipayMapper.php');
require_once(dirname(__FILE__) . '/classes/helper/tools/hipayDBQuery.php');
