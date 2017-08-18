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

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Hipay_enterprise
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class HipayEnterprise extends PaymentModule
{
    public $hipayConfigTool;
    public $_errors = array();
    public $_successes = array();
    public $currencies_titles = array();
    public $moduleCurrencies = array();

    public function __construct()
    {
        $this->name = 'hipay_enterprise';
        $this->tab = 'payments_gateways';
        $this->version = '2.0.0-beta';
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        $this->author = 'HiPay';
        $this->is_eu_compatible = 1;

        $this->bootstrap = true;
        $this->display = 'view';

        $this->displayName = $this->l('HiPay Enterprise');
        $this->description = $this->l(
            'Accept payments by credit card and other local methods with HiPay Enterprise. Very competitive rates, no configuration required!'
        );

        // init log object
        $this->logs = new HipayLogs($this);

        // init mapper object
        $this->mapper = new HipayMapper($this);

        // init query object
        $this->db = new HipayDBQuery($this);

        // init token manger object
        $this->token = new HipayCCToken($this);

        //init config form data manager object
        $this->hipayConfigFormHandler = new HipayConfigFormHandler($this);

        parent::__construct();

        $this->currencies_titles = array();
        $this->countries_titles = array();

        $countries = Country::getCountries($this->context->language->id);

        foreach ($countries as $country) {
            $this->countries_titles[$country["iso_code"]] = $country["name"];
        }
        $moduleCurrencies = $this->getCurrency((int)Configuration::get('PS_CURRENCY_DEFAULT'));
        foreach ($moduleCurrencies as $cur) {
            $this->moduleCurrencies[] = $cur["iso_code"];
        }
        $currencies = Currency::getCurrencies();

        foreach ($currencies as $currency) {
            $this->currencies_titles[$currency["iso_code"]] = $currency["name"];
        }

        //configuration is handle by an helper class
        $this->hipayConfigTool = new HipayConfig($this);
    }

    public function getLogs()
    {
        return $this->logs;
    }

    public function getLocalPath()
    {
        return $this->local_path;
    }

    public function getPath()
    {
        return $this->_path;
    }

    /**
     * Functions installation HiPay module or uninstall
     */
    public function install()
    {
        if (extension_loaded('soap') == false) {
            $this->_errors[] = $this->l('You have to enable the SOAP extension on your server to install this module');
            return false;
        }
        return parent::install() && $this->installHipay();
    }

    public function uninstall()
    {
        return $this->uninstallAdminTab() &&
            parent::uninstall() &&
            HipayHelper::clearAccountData() &&
            $this->deleteHipayTable();
    }

    public function installHipay()
    {
        $return = $this->installAdminTab();
        $return &= HipayOrderStatus::updateHiPayOrderStates($this);
        $return &= $this->createHipayTable();
        $return &= $this->registerHook('backOfficeHeader');
        $return &= $this->registerHook('displayAdminOrder');
        $return &= $this->registerHook('customerAccount');
        $return &= $this->registerHook('updateCarrier');
        $return &= $this->registerHook('actionAdminDeleteBefore');
        $return &= $this->registerHook('actionAdminBulKDeleteBefore');
        if (_PS_VERSION_ >= '1.7') {
            $return17 = $this->registerHook('paymentOptions') &&
                $this->registerHook('header') &&
                $this->registerHook('actionFrontControllerSetMedia');
            $return = $return && $return17;
        } elseif (_PS_VERSION_ < '1.7' && _PS_VERSION_ >= '1.6') {
            $return16 = $this->registerHook('payment') &&
                $this->registerHook('paymentReturn') &&
                $this->registerHook('displayPaymentEU');
            $return = $return && $return16;
        }
        return $return;
    }

    public function installAdminTab()
    {
        $class_names = array(
            'AdminHiPayCapture',
            'AdminHiPayRefund',
            'AdminHiPayMoto',
            'AdminHiPayChallenge',
            'AdminHiPayConfig',
        );
        return $this->createTabAdmin($class_names);
    }

    protected function createTabAdmin($class_names)
    {
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
                //    return false;
            }
        }
        return true;
    }

    public function uninstallAdminTab()
    {
        $class_names = array(
            'AdminHiPayCapture',
            'AdminHiPayRefund',
            'AdminHiPayMoto',
            'AdminHiPayChallenge',
            'AdminHiPayConfig',
        );
        foreach ($class_names as $class_name) {
            $id_tab = (int)Tab::getIdFromClassName($class_name);
            if ($id_tab) {
                $tab = new Tab($id_tab);
                if (!$tab->delete()) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Delete customer.
     *
     * @param $value
     */
    public function hookActionAdminDeleteBefore()
    {
        if (Tools::getValue('id_customer')) {
            $this->token->deleteAllToken(Tools::getValue('id_customer'));
        }
    }

    /**
     * Bulk customer delete.
     *
     * @param $value
     */
    public function hookActionAdminBulKDeleteBefore()
    {
        if (Tools::getValue('customerBox')) {
            foreach (Tools::getValue('customerBox') as $customerId) {
                $this->token->deleteAllToken($customerId);
            }
        }
    }

    public function hookUpdateCarrier($params)
    {
        $this->logs->logInfos('# HookUpdateCarrier' . $params['id_carrier']);
        $idCarrierOld = (int)($params['id_carrier']);
        $idCarrierNew = (int)($params['carrier']->id);

        $this->mapper->updateCarrier($idCarrierOld, $idCarrierNew);
    }

    public function hookCustomerAccount()
    {
        if ($this->hipayConfigTool->getPaymentGlobal()["card_token"]) {
            $this->smarty->assign(
                array(
                    "link" => $this->context->link->getModuleLink($this->name, 'userToken', array(), true)
                )
            );
            if (_PS_VERSION_ >= '1.7') {
                $path = 'views/templates/hook/ps17/my-account-17.tpl';
            } else {
                $path = 'views/templates/hook/ps16/my-account-16.tpl';
            }

            return $this->display(dirname(__FILE__), $path);
        }
    }

    public function hookActionFrontControllerSetMedia($params)
    {
        $hipay17 = new HipayEnterpriseNew();
        return $hipay17->hipayActionFrontControllerSetMedia($params);
    }

    public function hookBackOfficeHeader()
    {
        $this->context->controller->addCSS(($this->_path) . 'views/css/bootstrap-duallistbox.min.css', 'all');
        $this->context->controller->addCSS(($this->_path) . 'views/css/bootstrap-multiselect.css', 'all');
        $this->context->controller->addCSS($this->_path . 'views/css/back.css', 'all');

        $this->context->controller->addJS($this->_path . '/views/js/form-input-control.js', 'all');
    }

    /**
     * Handling prestashop hook payment. Adding payment methods (PS16)
     *
     * @param type $params
     * @return type
     */
    public function hookPayment($params)
    {
        $idAddress = $params['cart']->id_address_invoice ? $params['cart']->id_address_invoice : $params['cart']->id_address_delivery;
        $address = new Address((int)$idAddress);
        $country = new Country((int)$address->id_country);
        $currency = new Currency((int)$params['cart']->id_currency);
        $orderTotal = $params['cart']->getOrderTotal();
        $this->context->controller->addJS(array(_MODULE_DIR_ . 'hipay_enterprise/views/js/devicefingerprint.js'));

        $this->smarty->assign(
            array(
                'domain' => Tools::getShopDomainSSL(true),
                'module_dir' => $this->_path,
                'payment_button' => $this->_path . 'views/img/cc.png',
                'configHipay' => $this->hipayConfigTool->getConfigHipay(),
                'sortedPaymentProducts' => HipayHelper::getSortedActivatedPaymentByCountryAndCurrency(
                    $this,
                    $this->hipayConfigTool->getConfigHipay(),
                    $country,
                    $currency,
                    $orderTotal
                ),
                'lang' => Tools::strtolower($this->context->language->iso_code),
            )
        );
        $this->smarty->assign('hipay_prod', !(bool)$this->hipayConfigTool->getAccountGlobal()["sandbox_mode"]);

        return $this->display(dirname(__FILE__), 'views/templates/hook/ps16/payment-16.tpl');
    }

    /**
     *  Adding payment methods (PS16)
     *
     * @param $params
     * @return array
     */
    public function hookDisplayPaymentEU($params)
    {
        $idAddress = $params['cart']->id_address_invoice ? $params['cart']->id_address_invoice :
            $params['cart']->id_address_delivery;
        $address = new Address((int)$idAddress);
        $country = new Country((int)$address->id_country);
        $currency = new Currency((int)$params['cart']->id_currency);
        $orderTotal = $params['cart']->getOrderTotal();

        $paymentOptions = array();

        $sortedPaymentProducts = HipayHelper::getSortedActivatedPaymentByCountryAndCurrency(
            $this,
            $this->hipayConfigTool->getConfigHipay(),
            $country,
            $currency,
            $orderTotal
        );

        if (!empty($sortedPaymentProducts)) {
            foreach ($sortedPaymentProducts as $name => $paymentProduct) {
                if ($name == "credit_card") {
                    $paymentOptions[] =
                        array(
                            'cta_text' => $this->l('Pay by credit card'),
                            'logo' => Media::getMediaPath($this->_path . 'views/img/amexa200.png'),
                            'action' => $this->context->link->getModuleLink($this->name, 'redirect', array(), true)
                        );
                } else {
                    $paymentOptions[] =
                        array(
                            'cta_text' => $this->l('Pay by') . ' ' . $paymentProduct['displayName'],
                            'logo' => Media::getMediaPath($paymentProduct['payment_button']),
                            'action' => $paymentProduct['link']
                        );
                }
            }
        }
        return $paymentOptions;
    }

    /**
     * Handling prestashop payment hook. Adding payment methods (PS17)
     * @param type $params
     * @return type
     */
    public function hookPaymentOptions($params)
    {
        $hipay17 = new HipayEnterpriseNew();
        // Fix Bug with translation and bad context ( Hook in an another file)
        $params['translation_checkout'] = $this->l(
            'You will be redirected to an external payment page. Please do not refresh the page during the process'
        );

        return $hipay17->hipayPaymentOptions($params);
    }

    /**
     * * Handling prestashop header hook. Adding JS file (PS17)
     * @param type $params
     * @return type
     */
    public function hookHeader($params)
    {
        $hipay17 = new HipayEnterpriseNew();
        return $hipay17->hookDisplayHeader($params);
    }

    /**
     *
     * @param type $params
     * @return type
     */
    public function hookPaymentReturn($params)
    {

        if (_PS_VERSION_ < '1.7' && _PS_VERSION_ >= '1.6') {
            $this->hipayPaymentReturn($params);
            return $this->display(dirname(__FILE__), 'views/templates/hook/paymentReturn.tpl');
        }
    }

    /**
     *
     * @param type $params
     * @return type
     */
    private function hipayPaymentReturn($params)
    {
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
     * Display refund and capture blocks in order admin page
     */
    public function hookDisplayAdminOrder()
    {
        $order = new Order((int)Tools::getValue('id_order'));
        $cart = new Cart($order->id_cart);
        $shippingCost = $order->total_shipping;
        $refundableAmount = $order->getTotalPaid();
        $errorHipay = $this->context->cookie->__get('hipay_errors');
        $messagesHipay = $this->context->cookie->__get('hipay_success');
        $stillToCapture = $order->total_paid_tax_incl - $refundableAmount;
        $alreadyCaptured = $this->db->alreadyCaptured($order->id);
        $manualCapture = false;
        $showCapture = false;
        $showRefund = false;
        $showChallenge = false;
        $showMoto = false;
        $partiallyCaptured = false;
        $partiallyRefunded = false;
        $refundRequestedOS = false;
        $refundStartedFromBo = false;
        $orderId = $order->id;
        $employeeId = $this->context->employee->id;
        $basket = $this->db->getOrderBasket($order->id);
        $products = $order->getProducts();
        $capturedFees = $this->db->feesAreCaptured($order->id);
        $refundedFees = $this->db->feesAreRefunded($order->id);
        $capturedDiscounts = $this->db->discountsAreCaptured($order->id);
        $refundedDiscounts = $this->db->discountsAreRefunded($order->id);
        $amountFees = $order->getShipping() ? $order->getShipping()[0]['shipping_cost_tax_incl'] : 0;
        $capturedItems = $this->db->getCapturedItems($order->id);
        $refundedItems = $this->db->getRefundedItems($order->id);
        $totallyRefunded = true;
        $totallyCaptured = true;
        $id_currency = $order->id_currency;
        $discount = array();
        $catpureOrRefundFromBo = $this->db->captureOrRefundFromBO($order->id);
        $discounts = $order->getCartRules();
        $isManualCapture = $this->db->isManualCapture($order->id);

        if (!empty($discounts)) {
            foreach ($discounts as $disc) {
                $discount["name"][] = $disc["name"];
                $discount["value"] = (!isset($discount["value"])) ? $disc["value"] : $discount["value"] +
                    $disc["value"];
            }
            $discount["name"] = join("/", $discount["name"]);
        }


        foreach ($order->getProducts() as $product) {
            $totallyRefunded &= (isset($refundedItems[$product["product_id"]]) &&
                $refundedItems[$product["product_id"]]["quantity"] >= $product["product_quantity"]);
            $totallyCaptured &= (isset($capturedItems[$product["product_id"]]) &&
                $capturedItems[$product["product_id"]]["quantity"] >= $product["product_quantity"]);
        }


        if (!$refundedFees || !$refundedDiscounts) {
            $totallyRefunded = false;
        }

        if (!$capturedFees || !$capturedDiscounts) {
            $totallyCaptured = false;
        }

        if (empty($capturedItems) && $stillToCapture == 0) {
            $totallyCaptured = true;
        }

        if ($order->getCurrentState() == Configuration::get('HIPAY_OS_MOTO_PENDING', null, null, 1) &&
            !$this->db->getTransactionReference($order->id)
        ) {
            $showMoto = true;
            $showCapture = false;
            $showRefund = false;
        }

        if ($order->getCurrentState() == Configuration::get('HIPAY_OS_PARTIALLY_CAPTURED', null, null, 1) ||
            !empty($capturedItems) ||
            $capturedFees ||
            $capturedDiscounts
        ) {
            $partiallyCaptured = true;
        }

        if ($order->getCurrentState() == Configuration::get('HIPAY_OS_REFUNDED_PARTIALLY', null, null, 1) ||
            !empty($refundedItems) ||
            $refundedFees ||
            !$totallyCaptured ||
            $refundedDiscounts
        ) {
            $partiallyRefunded = true;
        }

        if (isset($this->hipayConfigTool->getPaymentGlobal()["capture_mode"]) &&
            $this->hipayConfigTool->getPaymentGlobal()["capture_mode"] == "manual"
        ) {
            $manualCapture = true;
        }

        $isPending = $order->getHistory(
            $this->context->language->id,
            Configuration::get('HIPAY_OS_PENDING', null, null, 1)
        );

        $isChallenged = $order->getHistory(
            $this->context->language->id,
            Configuration::get('HIPAY_OS_CHALLENGED', null, null, 1)
        );

        if ((boolean)$isPending || (boolean)$isChallenged) {
            // Order was previously pending or challenged
            // Then check if its currently in authorized state
            if ($order->current_state == Configuration::get('HIPAY_OS_AUTHORIZED', null, null, 1)) {
                $manualCapture = true;
            }
        }

        if ($order->getCurrentState() == Configuration::get('HIPAY_OS_AUTHORIZED', null, null, 1) ||
            $order->getCurrentState() == _PS_OS_PAYMENT_ ||
            $order->getCurrentState() == Configuration::get('HIPAY_OS_PARTIALLY_CAPTURED', null, null, 1)
        ) {
            $showCapture = true;
            $showRefund = true;
        }

        if ($order->getCurrentState() == Configuration::get('HIPAY_OS_REFUNDED_PARTIALLY', null, null, 1)) {
            $showRefund = true;
        }
        if ($order->current_state == Configuration::get('HIPAY_OS_CHALLENGED', null, null, 1)) {
            $showChallenge = true;
            $showCapture = false;
            $showRefund = false;
        }


        if ($catpureOrRefundFromBo && $basket) {
            $showRefund = false;
            $refundStartedFromBo = true;
            $showCapture = false;
            if (!$isManualCapture && (!$partiallyRefunded || !empty($refundedItems))) {
                $showRefund = true;
                $refundStartedFromBo = false;
            }
        }

        $paymentProduct = $this->db->getPaymentProductFromMessage($order->id);
        if ($paymentProduct) {
            if (isset($this->hipayConfigTool->getLocalPayment()[$paymentProduct])) {
                if (!(bool)$this->hipayConfigTool->getLocalPayment()[$paymentProduct]["canRefund"]) {
                    $showRefund = false;
                }
                if (!(bool)$this->hipayConfigTool->getLocalPayment()[$paymentProduct]["canManualCapture"]) {
                    $showCapture = false;
                }
            } elseif (isset($this->hipayConfigTool->getPaymentCreditCard()[$paymentProduct])) {
                if (!(bool)$this->hipayConfigTool->getPaymentCreditCard()[$paymentProduct]["canRefund"]) {
                    $showRefund = false;
                }
                if (!(bool)$this->hipayConfigTool->getPaymentCreditCard()[$paymentProduct]["canManualCapture"]) {
                    $showCapture = false;
                }
            }
        }


        if ($order->getCurrentState() == Configuration::get('HIPAY_OS_REFUND_REQUESTED')) {
            $showRefund = false;
        }

        if ($order->getCurrentState() == _PS_OS_ERROR_ ||
            $order->getCurrentState() == _PS_OS_CANCELED_ ||
            $order->getCurrentState() == Configuration::get('HIPAY_OS_EXPIRED', null, null, 1) ||
            $order->getCurrentState() == Configuration::get('HIPAY_OS_REFUND_REQUESTED', null, null, 1) ||
            $order->getCurrentState() == Configuration::get('HIPAY_OS_REFUNDED', null, null, 1)
        ) {
            $showCapture = false;
        }

        $refundRequestedOS = ($order->getCurrentState() ==
            Configuration::get('HIPAY_OS_REFUND_REQUESTED', null, null, 1)) ? true : false;

        $this->context->smarty->assign(
            array(
                'config_hipay' => $this->hipayConfigTool->getConfigHipay(),
                'refundableAmountDisplay' => $refundableAmount,
                'refundableAmount' => $refundableAmount,
                'shippingCost' => $shippingCost,
                'errorHipay' => $errorHipay,
                'messagesHipay' => $messagesHipay,
                'stillToCaptureDisplay' => $stillToCapture,
                'stillToCapture' => $stillToCapture,
                'alreadyCaptured' => $alreadyCaptured,
                'partiallyCaptured' => $partiallyCaptured,
                'partiallyRefunded' => $partiallyRefunded,
                'showCapture' => $showCapture,
                'showRefund' => $showRefund,
                'manualCapture' => $manualCapture,
                'captureLink' => $this->context->link->getAdminLink('AdminHiPayCapture'),
                'refundLink' => $this->context->link->getAdminLink('AdminHiPayRefund'),
                'motoLink' => $this->context->link->getAdminLink('AdminHiPayMoto'),
                'tokenCapture' => Tools::getAdminTokenLite('AdminHiPayCapture'),
                'tokenRefund' => Tools::getAdminTokenLite('AdminHiPayRefund'),
                'challengeLink' => $this->context->link->getAdminLink('AdminHiPayChallenge'),
                'tokenChallenge' => Tools::getAdminTokenLite('AdminHiPayChallenge'),
                'showChallenge' => $showChallenge,
                'orderId' => $orderId,
                'employeeId' => $employeeId,
                'basket' => $basket,
                'capturedItems' => $capturedItems,
                'refundedItems' => $refundedItems,
                'capturedFees' => $capturedFees,
                'refundedFees' => $refundedFees,
                'capturedDiscounts' => $capturedDiscounts,
                'refundedDiscounts' => $refundedDiscounts,
                'products' => $products,
                'discount' => $discount,
                'totallyRefunded' => $totallyRefunded,
                'showMoto' => $showMoto,
                'cartId' => $cart->id,
                'id_currency' => $id_currency,
                'amountFees' => $amountFees,
                'refundRequestedOS' => $refundRequestedOS,
                'refundStartedFromBo' => $refundStartedFromBo
            )
        );

        HipayHelper::resetMessagesHipay($this->context);

        return $this->display(
            dirname(__FILE__),
            'views/templates/hook/maintenance.tpl'
        );
    }

    /**
     * Load configuration page
     * @return string
     */
    public function getContent()
    {
        $this->postProcess();

        $formGenerator = new HipayForm($this);

        $configuration = $this->local_path . 'views/templates/admin/configuration.tpl';

        $psCategories = $this->mapper->getPrestashopCategories();
        $hipayCategories = $this->mapper->getHipayCategories();
        $psCarriers = $this->mapper->getPrestashopCarriers();
        $hipayCarriers = $this->mapper->getHipayCarriers();
        $mappedCategories = $this->mapper->getMappedCategories($this->context->shop->id);
        $mappedCarriers = $this->mapper->getMappedCarriers($this->context->shop->id);

        $source = array("brand_version" => _PS_VERSION_, "integration_version" => $this->version,);

        if (!Configuration::get('PS_SSL_ENABLED')) {
            $this->_technicalErrors = $this->l(
                'A SSL certificate is required to process credit card payments using HiPay. Please consult the FAQ.'
            );
        }

        $this->context->smarty->assign(
            array(
                'module_dir' => $this->_path,
                'config_hipay' => $this->hipayConfigTool->getConfigHipay(),
                'logs' => $this->getLogs()->getLogFiles(),
                'module_url' => AdminController::$currentIndex .
                    '&configure=' .
                    $this->name .
                    '&token=' .
                    Tools::getAdminTokenLite('AdminModules'),
                'fraud_form' => $formGenerator->getFraudForm(),
                'form_errors' => $this->_errors,
                'form_successes' => $this->_successes,
                'technicalErrors' => $this->_technicalErrors,
                'limitedCurrencies' => $this->currencies_titles,
                'default_currency' => Configuration::get('PS_SHOP_DEFAULT'),
                'limitedCountries' => $this->countries_titles,
                'this_callback' => $this->context->link->getModuleLink(
                    $this->name,
                    'notify',
                    array(),
                    true,
                    null,
                    (int)Configuration::get('PS_SHOP_DEFAULT')
                ),
                'ipaddr' => $_SERVER ['REMOTE_ADDR'],
                'psCategories' => $psCategories,
                'hipayCategories' => $hipayCategories,
                'mappedCategories' => $mappedCategories,
                'psCarriers' => $psCarriers,
                'hipayCarriers' => $hipayCarriers,
                'mappedCarriers' => $mappedCarriers,
                'lang' => Tools::strtolower($this->context->language->iso_code),
                'source' => $source,
                'ps_round_total' => Configuration::get('PS_ROUND_TYPE') == Order::ROUND_TOTAL
            )
        );

        return $this->context->smarty->fetch($configuration);
    }

    /**
     * Process HTTP request send by module confguration page
     */
    protected function postProcess()
    {
        //==================================//
        //===         LOG VIEW           ===//
        //==================================//
        if (Tools::isSubmit('logfile')) {
            $logFile = Tools::getValue('logfile');
            $this->logs->displayLogFile($logFile);
            //==================================//
            //===         ACCOUNT VIEW       ===//
            //==================================//
        } elseif (Tools::isSubmit('submitAccount')) {
            $this->logs->logInfos('# submitAccount');

            $this->hipayConfigFormHandler->saveAccountInformations();

            $this->context->smarty->assign(
                'active_tab',
                'account_form'
            );
            //==================================//
            //===   GLOBAL PAYMENT METHODS   ===//
            //==================================//
        } elseif (Tools::isSubmit('submitGlobalPaymentMethods')) {
            $this->logs->logInfos('# submitGlobalPaymentMethods');
            $this->hipayConfigFormHandler->saveGlobalPaymentInformations();
            $this->context->smarty->assign(
                'active_tab',
                'payment_form'
            );
        } elseif (Tools::isSubmit('creditCardSubmit')) {
            $this->logs->logInfos('# creditCardSubmit');
            $this->hipayConfigFormHandler->saveCreditCardInformations($this->context);
            $this->context->smarty->assign(
                'active_tab',
                'payment_form'
            );
        } elseif (Tools::isSubmit('localPaymentSubmit')) {
            $this->logs->logInfos('# localPaymentSubmit');
            $this->hipayConfigFormHandler->saveLocalPaymentInformations($this->context);
            $this->context->smarty->assign(
                'active_tab',
                'payment_form'
            );
        } elseif (Tools::isSubmit('fraudSubmit')) {
            $this->logs->logInfos('# fraudSubmit');
            $this->hipayConfigFormHandler->saveFraudInformations();
            $this->context->smarty->assign(
                'active_tab',
                'fraud_form'
            );
        } elseif (Tools::isSubmit('submitCategoryMapping')) {
            $this->logs->logInfos('# submitCategoryMapping');
            $this->hipayConfigFormHandler->saveCategoryMappingInformations();
            $this->context->smarty->assign(
                'active_tab',
                'category_form'
            );
        } elseif (Tools::isSubmit('submitCarrierMapping')) {
            $this->logs->logInfos('# submitCarrierMapping');
            $this->hipayConfigFormHandler->saveCarrierMappingInformations();
            $this->context->smarty->assign(
                'active_tab',
                'carrier_form'
            );
        }
    }

    /**
     *
     */
    private function createHipayTable()
    {
        $this->mapper->createTable();
        $this->db->createOrderRefundCaptureTable();
        $this->db->createCCTokenTable();
        $this->db->createHipayTransactionTable();
        $this->db->createHipayOrderCaptureType();
        return true;
    }

    /**
     *
     */
    private function deleteHipayTable()
    {
        $this->mapper->deleteTable();
//        $this->db->deleteOrderRefundCaptureTable();
        $this->db->deleteCCTokenTable();
        return true;
    }
}

if (_PS_VERSION_ >= '1.7') {
    // version 1.7
    require_once(dirname(__FILE__) . '/hipay_enterprise-17.php');
} elseif (_PS_VERSION_ < '1.6') {
    // Version < 1.6
    Tools::displayError('The module HiPay Enterprise is not compatible with your PrestaShop');
}

require_once(dirname(__FILE__) . '/classes/helper/HipayLogs.php');
require_once(dirname(__FILE__) . '/classes/helper/HipayConfig.php');
require_once(dirname(__FILE__) . '/classes/forms/HipayForm.php');
require_once(dirname(__FILE__) . '/classes/helper/HipayMapper.php');
require_once(dirname(__FILE__) . '/classes/helper/HipayHelper.php');
require_once(dirname(__FILE__) . '/classes/helper/HipayDBQuery.php');
require_once(dirname(__FILE__) . '/classes/helper/HipayCCToken.php');
require_once(dirname(__FILE__) . '/classes/helper/HipayOrderStatus.php');
require_once(dirname(__FILE__) . '/classes/helper/HipayFormControl.php');
require_once(dirname(__FILE__) . '/classes/helper/HipayConfigFormHandler.php');
