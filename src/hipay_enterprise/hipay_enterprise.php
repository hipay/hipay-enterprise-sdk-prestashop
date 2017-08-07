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
ini_set('display_errors',
    1);
error_reporting(E_ALL);

class Hipay_enterprise extends PaymentModule
{
    public $hipayConfigTool;
    public $_errors           = array();
    public $_successes        = array();
    public $min_amount        = 1;
    public $currencies_titles = array();

    public function __construct()
    {
        $this->name                   = 'hipay_enterprise';
        $this->tab                    = 'payments_gateways';
        $this->version                = '2.0.0';
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->currencies             = true;
        $this->currencies_mode        = 'checkbox';
        $this->author                 = 'HiPay';
        $this->is_eu_compatible       = 1;

        $this->bootstrap = true;
        $this->display   = 'view';

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

        $this->token = new HipayCCToken($this);

        parent::__construct();

        $this->currencies_titles = array();
        $this->countries_titles  = array();

        $countries = Country::getCountries($this->context->language->id);

        foreach ($countries as $country) {
            $this->countries_titles[$country["iso_code"]] = $country["name"];
        }
        $currencies = $this->getCurrency((int) Configuration::get('PS_CURRENCY_DEFAULT'));

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

    /**
     * Functions installation HiPay module or uninstall
     */
    public function install()
    {
        if (extension_loaded('soap') == false) {
            $this->_errors[] = $this->l('You have to enable the SOAP extension on your server to install this module');
            return false;
        }
        $iso_code = Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT'));

        return parent::install() && $this->installHipay();
    }

    public function uninstall()
    {
        return $this->uninstallAdminTab() && parent::uninstall() && $this->clearAccountData() && $this->deleteHipayTable();
    }

    public function installHipay()
    {
        $return = $this->installAdminTab();
        $return &= $this->updateHiPayOrderStates();
        $return &= $this->createHipayTable();
        $return &= $this->registerHook('backOfficeHeader');
        $return &= $this->registerHook('displayAdminOrder');
        $return &= $this->registerHook('customerAccount');
        $return &= $this->registerHook('updateCarrier');
        $return &= $this->registerHook('actionAdminDeleteBefore');
        $return &= $this->registerHook('actionAdminBulKDeleteBefore');
        if (_PS_VERSION_ >= '1.7') {
            $return17 = $this->registerHook('paymentOptions') && $this->registerHook('header') && $this->registerHook('actionFrontControllerSetMedia');
            $return   = $return && $return17;
        } elseif (_PS_VERSION_ < '1.7' && _PS_VERSION_ >= '1.6') {
            $return16 = $this->registerHook('payment') && $this->registerHook('paymentReturn') && $this->registerHook('displayPaymentEU');
            $return   = $return && $return16;
        }
        return $return;
    }

    /**
     * Delete customer.
     *
     * @param $value
     */
    public function hookActionAdminDeleteBefore($value)
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
    public function hookActionAdminBulKDeleteBefore($value)
    {
        if (Tools::getValue('customerBox')) {
            foreach (Tools::getValue('customerBox') as $customerId) {
                $this->token->deleteAllToken($customerId);
            }
        }
    }

    public function hookUpdateCarrier($params)
    {
        $this->logs->logInfos('# HookUpdateCarrier'.$params['id_carrier']);
        $idCarrierOld = (int) ($params['id_carrier']);
        $idCarrierNew = (int) ($params['carrier']->id);

        $this->mapper->updateCarrier($idCarrierOld,
            $idCarrierNew);
    }

    public function hookCustomerAccount()
    {
        if ($this->hipayConfigTool->getConfigHipay()["payment"]["global"]["card_token"]) {
            $this->smarty->assign(
                array(
                    "link" => $this->context->link->getModuleLink(
                        $this->name,
                        'userToken',
                        array(),
                        true
                    )
                )
            );
            if (_PS_VERSION_ >= '1.7') {
                $path = 'views/templates/hook/my-account-17.tpl';
            } else {
                $path = 'views/templates/hook/my-account-16.tpl';
            }

            return $this->display(
                    dirname(__FILE__),
                    $path
            );
        }
    }

    public function hookActionFrontControllerSetMedia($params)
    {
        $hipay17 = new HipayEnterpriseNew();
        return $hipay17->hipayActionFrontControllerSetMedia($params);
    }

    public function hookBackOfficeHeader($params)
    {
        $this->context->controller->addCSS(
            ($this->_path).'views/css/bootstrap-duallistbox.min.css',
            'all'
        );
        $this->context->controller->addCSS(
            ($this->_path).'views/css/bootstrap-multiselect.css',
            'all'
        );
        $this->context->controller->addCSS($this->_path.'views/css/back.css',
            'all');
    }

    /**
     * Handling prestashop hook payment. Adding payment methods (PS16)
     * @param type $params
     * @return type
     */
    public function hookPayment($params)
    {
        $address    = new Address((int) $params['cart']->id_address_delivery);
        $country    = new Country((int) $address->id_country);
        $currency   = new Currency((int) $params['cart']->id_currency);
        $orderTotal = $params['cart']->getOrderTotal();
        $this->context->controller->addJS(array(_MODULE_DIR_.'hipay_enterprise/views/js/devicefingerprint.js'));

        $this->smarty->assign(
            array(
                'domain' => Tools::getShopDomainSSL(true),
                'module_dir' => $this->_path,
                'payment_button' => $this->_path.'views/img/amexa200.png',
                'min_amount' => $this->min_amount,
                'configHipay' => $this->hipayConfigTool->getConfigHipay(),
                'activated_credit_card' => $this->getActivatedPaymentByCountryAndCurrency(
                    "credit_card",
                    $country,
                    $currency
                ),
                'activated_local_payment' => $this->getActivatedPaymentByCountryAndCurrency(
                    "local_payment",
                    $country,
                    $currency,
                    $orderTotal
                ),
                'lang' => Tools::strtolower($this->context->language->iso_code),
            )
        );
        $this->smarty->assign(
            'hipay_prod',
            !(bool) $this->hipayConfigTool->getConfigHipay()["account"]["global"]["sandbox_mode"]
        );

        return $this->display(
                dirname(__FILE__),
                'views/templates/hook/payment.tpl'
        );
    }

    public function hookDisplayPaymentEU($params)
    {
        $this->logs->logInfos('##########################');
        $this->logs->logInfos('---- START function hookDisplayPaymentEU');
        $this->logs->logInfos('##########################');

        $address    = new Address((int) $params['cart']->id_address_delivery);
        $country    = new Country((int) $address->id_country);
        $currency   = new Currency((int) $params['cart']->id_currency);
        $orderTotal = $params['cart']->getOrderTotal();

        $activatedCreditCard = $this->getActivatedPaymentByCountryAndCurrency(
            "credit_card",
            $country,
            $currency
        );

        $activatedLocalPayment = $this->getActivatedPaymentByCountryAndCurrency(
            "local_payment",
            $country,
            $currency,
            $orderTotal
        );
        $paymentOptions        = array();
        $paymentOptionsCC      = array();
        $paymentOptionsLP      = array();

        if (!empty($activatedCreditCard)) {
            $paymentOptionsCC[] = array(
                'cta_text' => $this->l('Pay by credit or debit card'),
                'logo' => Media::getMediaPath($this->_path.'views/img/amexa200.png'),
                'action' => $this->context->link->getModuleLink(
                    $this->name,
                    'redirect',
                    array(),
                    true
                )
            );
        }

        if (!empty($activatedLocalPayment)) {
            foreach ($activatedLocalPayment as $localPayment) {
                $paymentOptionsLP[] = array(
                    'cta_text' => $this->l('Pay by').' '.$localPayment['displayName'],
                    'logo' => Media::getMediaPath($localPayment['payment_button']),
                    'action' => $localPayment['link']
                );
            }
        }
        $paymentOptions = array_merge(
            $paymentOptionsCC,
            $paymentOptionsLP
        );
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
        if (_PS_VERSION_ >= '1.7') {
            $hipay17 = new HipayProfessionalNew();
            $hipay17->hipayPaymentReturnNew($params);
        } elseif (_PS_VERSION_ < '1.7' && _PS_VERSION_ >= '1.6') {
            $this->hipayPaymentReturn($params);
            return $this->display(
                    dirname(__FILE__),
                    'views/templates/hook/paymentReturn.tpl'
            );
        }
    }

    /**
     * Display refund and capture blocks in order admin page
     */
    public function hookDisplayAdminOrder()
    {
        $order                 = new Order((int) Tools::getValue('id_order'));
        $cart                  = new Cart($order->id_cart);
        $shippingCost          = $order->total_shipping;
        $refundableAmount      = $order->getTotalPaid();
        $errorHipay            = $this->context->cookie->__get('hipay_errors');
        $messagesHipay         = $this->context->cookie->__get('hipay_success');
        $stillToCapture        = $order->total_paid_tax_incl - $refundableAmount;
        $alreadyCaptured       = $this->db->alreadyCaptured($order->id);
        $manualCapture         = false;
        $showCapture           = false;
        $showRefund            = false;
        $showChallenge         = false;
        $showMoto              = false;
        $partiallyCaptured     = false;
        $partiallyRefunded     = false;
        $orderId               = $order->id;
        $employeeId            = $this->context->employee->id;
        $basket                = $this->db->getOrderBasket($order->id);
        $products              = $order->getProducts();
        $capturedFees          = $this->db->feesAreCaptured($order->id);
        $refundedFees          = $this->db->feesAreRefunded($order->id);
        $capturedDiscounts     = $this->db->discountsAreCaptured($order->id);
        $refundedDiscounts     = $this->db->discountsAreRefunded($order->id);
        $amountFees            = $order->getShipping() ? $order->getShipping()[0]['shipping_cost_tax_incl'] : 0;
        $capturedItems         = $this->db->getCapturedItems($order->id);
        $refundedItems         = $this->db->getRefundedItems($order->id);
        $totallyRefunded       = true;
        $id_currency           = $order->id_currency;
        $discount              = array();
        $catpureOrRefundFromBo = $this->db->captureOrRefundFromBO($order->id);
        $discounts             = $order->getCartRules();
        if (!empty($discounts)) {
            foreach ($discounts as $disc) {
                $discount["name"][] = $disc["name"];
                $discount["value"]  = (!isset($discount["value"])) ? $disc["value"] : $discount["value"] + $disc["value"];
            }
            $discount["name"] = join("/",
                $discount["name"]);
        }


        foreach ($order->getProducts() as $product) {
            $totallyRefunded &= (isset($refundedItems[$product["product_id"]]) && $refundedItems[$product["product_id"]]["quantity"]
                >= $product["product_quantity"]);
        }

        if (!$refundedFees || !$refundedDiscounts) {
            $totallyRefunded = false;
        }

        if ($order->getCurrentState() == Configuration::get(
                'HIPAY_OS_MOTO_PENDING',
                null,
                null,
                1
            ) && !$this->db->getTransactionReference($order->id)
        ) {
            $showMoto    = true;
            $showCapture = false;
            $showRefund  = false;
        }

        if ($order->getCurrentState() == Configuration::get(
                'HIPAY_OS_PARTIALLY_CAPTURED',
                null,
                null,
                1
            ) || !empty($capturedItems) || $capturedFees
        ) {
            $partiallyCaptured = true;
        }

        if ($order->getCurrentState() == Configuration::get(
                'HIPAY_OS_PARTIALLY_REFUNDED',
                null,
                null,
                1
            ) || !empty($refundedItems) || $refundedFees || $partiallyCaptured || $refundedDiscounts
        ) {
            $partiallyRefunded = true;
        }

        if (isset($this->hipayConfigTool->getConfigHipay()["payment"]["global"]["capture_mode"]) && $this->hipayConfigTool->getConfigHipay()["payment"]["global"]["capture_mode"]
            == "manual"
        ) {
            $manualCapture = true;
        }

        if ((boolean) $order->getHistory(
                $this->context->language->id,
                Configuration::get(
                    'HIPAY_OS_PENDING',
                    null,
                    null,
                    1
                )
            ) || (boolean) $order->getHistory(
                $this->context->language->id,
                Configuration::get(
                    'HIPAY_OS_CHALLENGED',
                    null,
                    null,
                    1
                )
            )
        ) {
            // Order was previously pending or challenged
            // Then check if its currently in authorized state
            if ($order->current_state == Configuration::get(
                    'HIPAY_OS_AUTHORIZED',
                    null,
                    null,
                    1
                )
            ) {
                $manualCapture = true;
            }
        }

        if ($order->getCurrentState() == Configuration::get(
                'HIPAY_OS_AUTHORIZED',
                null,
                null,
                1
            ) || $order->getCurrentState() == _PS_OS_PAYMENT_ || $order->getCurrentState() == Configuration::get(
                'HIPAY_OS_PARTIALLY_CAPTURED',
                null,
                null,
                1
            )
        ) {
            $showCapture = true;
            $showRefund  = true;
        }

        if ($order->current_state == Configuration::get(
                'HIPAY_OS_CHALLENGED',
                null,
                null,
                1
            )
        ) {
            $showChallenge = true;
            $showCapture   = false;
            $showRefund    = false;
        }

        $paymentProduct = $this->db->getPaymentProductFromMessage($order->id);
        if ($paymentProduct) {
            if (isset($this->hipayConfigTool->getConfigHipay()["payment"]["local_payment"][$paymentProduct])) {
                if (!(bool) $this->hipayConfigTool->getConfigHipay(
                    )["payment"]["local_payment"][$paymentProduct]["canRefund"]
                ) {
                    $showRefund = false;
                }
                if (!(bool) $this->hipayConfigTool->getConfigHipay(
                    )["payment"]["local_payment"][$paymentProduct]["canManualCapture"]
                ) {
                    $showCapture = false;
                }
            } elseif (isset($this->hipayConfigTool->getConfigHipay()["payment"]["credit_card"][$paymentProduct])) {
                if (!(bool) $this->hipayConfigTool->getConfigHipay(
                    )["payment"]["credit_card"][$paymentProduct]["canRefund"]
                ) {
                    $showRefund = false;
                }
                if (!(bool) $this->hipayConfigTool->getConfigHipay(
                    )["payment"]["credit_card"][$paymentProduct]["canManualCapture"]
                ) {
                    $showCapture = false;
                }
            }
        }

        if ($order->getCurrentState() == Configuration::get('HIPAY_OS_REFUND_REQUESTED')) {
            $showRefund = false;
        }

        if ($order->getCurrentState() == _PS_OS_ERROR_ || $order->getCurrentState() == _PS_OS_CANCELED_ || $order->getCurrentState()
            == Configuration::get(
                'HIPAY_OS_EXPIRED',
                null,
                null,
                1
            ) || $order->getCurrentState() == Configuration::get(
                'HIPAY_OS_REFUND_REQUESTED',
                null,
                null,
                1
            ) || $order->getCurrentState() == Configuration::get(
                'HIPAY_OS_REFUNDED',
                null,
                null,
                1
            )
        ) {
            $showCapture = false;
        }
        
        if($catpureOrRefundFromBo){
            $showRefund = false;
            $showCapture = false;
        }

        $this->context->smarty->assign(
            array(
                'config_hipay' => $this->hipayConfigTool->getConfigHipay(),
                'refundableAmountDisplay' => Tools::displayPrice($refundableAmount),
                'refundableAmount' => $refundableAmount,
                'shippingCost' => $shippingCost,
                'errorHipay' => $errorHipay,
                'messagesHipay' => $messagesHipay,
                'stillToCaptureDisplay' => Tools::displayPrice($stillToCapture),
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
                'amountFees' => $amountFees
            )
        );

        $this->resetMessagesHipay();

        return $this->display(
                dirname(__FILE__),
                'views/templates/hook/maintenance.tpl'
        );
    }

    /**
     *  Restore error messages in Session or cookie
     */
    private function resetMessagesHipay()
    {
        $this->context->cookie->__set('hipay_errors',
            '');
        $this->context->cookie->__set('hipay_success',
            '');
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
            $tab             = new Tab();
            $tab->active     = 1;
            $tab->module     = $this->name;
            $tab->class_name = $class_name;
            $tab->id_parent  = -1;
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
            $id_tab = (int) Tab::getIdFromClassName($class_name);
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
     * Load configuration page
     * @return string
     */
    public function getContent()
    {
        $this->postProcess();

        $formGenerator = new HipayForm($this);

        $configuration = $this->local_path.'views/templates/admin/configuration.tpl';

        $psCategories     = $this->mapper->getPrestashopCategories();
        $hipayCategories  = $this->mapper->getHipayCategories();
        $psCarriers       = $this->mapper->getPrestashopCarriers();
        $hipayCarriers    = $this->mapper->getHipayCarriers();
        $mappedCategories = $this->mapper->getMappedCategories($this->context->shop->id);
        $mappedCarriers   = $this->mapper->getMappedCarriers($this->context->shop->id);

        $source = array(
            "brand_version" => _PS_VERSION_,
            "integration_version" => $this->version,
        );

        if (!Configuration::get('PS_SSL_ENABLED')) {
            $this->_technicalErrors = $this->l('A SSL certificate is required to process credit card payments using HiPay. Please consult the FAQ.');
        }

        $this->context->smarty->assign(
            array(
                'module_dir' => $this->_path,
                'config_hipay' => $this->hipayConfigTool->getConfigHipay(),
                'logs' => $this->getLogFiles(),
                'module_url' => AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite(
                    'AdminModules'
                ),
                'global_payment_methods_form' => $formGenerator->getGlobalPaymentMethodsForm(),
                'fraud_form' => $formGenerator->getFraudForm(),
                'form_errors' => $this->_errors,
                'form_successes' => $this->_successes,
                'technicalErrors' => $this->_technicalErrors,
                'limitedCurrencies' => $this->currencies_titles,
                'limitedCountries' => $this->countries_titles,
                'this_callback' => $this->context->link->getModuleLink(
                    $this->name,
                    'notify',
                    array(),
                    true,
                    null,
                    (int) Configuration::get('PS_SHOP_DEFAULT')
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
            $path    = $this->logs->getBasePath().$logFile;

            if (!file_exists($path)) {
                http_response_code(404);
                die('<h1>File not found</h1>');
                $this->logs->logErrors("Log File not found $path");
            } else {
                header('Content-Type: text/plain');
                $content = Tools::file_get_contents($path);
                echo $content;
                die();
            }
            //==================================//
            //===         ACCOUNT VIEW       ===//
            //==================================//
        } elseif (Tools::isSubmit('submitAccount')) {
            $this->logs->logInfos('# submitAccount');

            $this->saveAccountInformations();

            $this->context->smarty->assign(
                'active_tab',
                'account_form'
            );
            //==================================//
            //===   GLOBAL PAYMENT METHODS   ===//
            //==================================//
        } elseif (Tools::isSubmit('submitGlobalPaymentMethods')) {
            $this->logs->logInfos('# submitGlobalPaymentMethods');
            $this->saveGlobalPaymentInformations();
            $this->context->smarty->assign(
                'active_tab',
                'payment_form'
            );
        } elseif (Tools::isSubmit('submit3DSecure')) {
            $this->logs->logInfos('# submit3DSecure');
            $this->save3DSecureInformations();
            $this->context->smarty->assign(
                'active_tab',
                'payment_form'
            );
        } elseif (Tools::isSubmit('creditCardSubmit')) {
            $this->logs->logInfos('# creditCardSubmit');
            $this->saveCreditCardInformations();
            $this->context->smarty->assign(
                'active_tab',
                'payment_form'
            );
        } elseif (Tools::isSubmit('localPaymentSubmit')) {
            $this->logs->logInfos('# localPaymentSubmit');
            $this->saveLocalPaymentInformations();
            $this->context->smarty->assign(
                'active_tab',
                'payment_form'
            );
        } elseif (Tools::isSubmit('fraudSubmit')) {
            $this->logs->logInfos('# fraudSubmit');
            $this->saveFraudInformations();
            $this->context->smarty->assign(
                'active_tab',
                'fraud_form'
            );
        } elseif (Tools::isSubmit('submitCategoryMapping')) {
            $this->logs->logInfos('# submitCategoryMapping');
            $this->saveCategoryMappingInformations();
            $this->context->smarty->assign(
                'active_tab',
                'category_form'
            );
        } elseif (Tools::isSubmit('submitCarrierMapping')) {
            $this->logs->logInfos('# submitCarrierMapping');
            $this->saveCarrierMappingInformations();
            $this->context->smarty->assign(
                'active_tab',
                'carrier_form'
            );
        }
    }

    protected function save3DSecureInformations()
    {
        $this->logs->logInfos('# save3DSecureInformations');

        try {
            $accountConfig                                 = array(
                "global" => array()
            );
            $accountConfig["global"]["activate_3d_secure"] = Tools::getValue("activate_3d_secure");
            $accountConfig["global"]["3d_secure_rules"]    = array();

            foreach (Tools::getValue("3d_secure_rules") as $rule) {
                $newRules = array(
                    "field" => $rule["field"],
                    "operator" => htmlentities($rule["operator"]),
                    "value" => $rule["value"],
                );

                $accountConfig["global"]["3d_secure_rules"][] = $newRules;
            }

            return $accountConfig;
        } catch (Exception $e) {
            // LOGS
            $this->logs->logErrors($e->getMessage());
            $this->_errors[] = $this->l($e->getMessage());
        }
    }

    /**
     * Save Carrier Mapping informations send by config page form
     *
     * @return : bool
     * */
    protected function saveCarrierMappingInformations()
    {
        $this->logs->logInfos('# SaveCarrierMappingInformations');

        try {
            $psCarriers = $this->mapper->getPrestashopCarriers();

            $mapping       = array();
            $this->_errors = array();
            foreach ($psCarriers as $car) {
                $psMapCar            = Tools::getValue('ps_map_'.$car["id_carrier"]);
                $hipayMapCarMode     = Tools::getValue('hipay_map_mode_'.$car["id_carrier"]);
                $hipayMapCarShipping = Tools::getValue('hipay_map_shipping_'.$car["id_carrier"]);
                $hipayMapCarOETA     = Tools::getValue('ps_map_prep_eta_'.$car["id_carrier"]);
                $hipayMapCarDETA     = Tools::getValue('ps_map__delivery_eta_'.$car["id_carrier"]);

                if (empty($psMapCar) || empty($hipayMapCarMode) || empty($hipayMapCarShipping) || empty($hipayMapCarOETA)
                    || empty($hipayMapCarDETA)
                ) {
                    $this->_errors[] = $this->l("all carrier mapping fields are required");
                }

                //   if ($this->mapper->hipayCarrierExist($hipayMapCar)) {
                $mapping[] = array("pscar" => $psMapCar, "hipaycarmode" => $hipayMapCarMode,
                    "hipaycarshipping" => $hipayMapCarShipping, "prepeta" => $hipayMapCarOETA,
                    "deliveryeta" => $hipayMapCarDETA);
                //   }
            }

            if (!empty($this->_errors)) {
                $this->_errors = array(end($this->_errors));
                return false;
            }

            $response           = $this->mapper->setMapping(
                HipayMapper::HIPAY_CARRIER_MAPPING,
                $mapping
            );
            $this->_successes[] = $this->l('Carrier mapping configuration saved successfully.');
            return true;
        } catch (Exception $e) {
            // LOGS
            $this->logs->logErrors($e->getMessage());
            $this->_errors[] = $this->l($e->getMessage());
        }

        return false;
    }

    /**
     * Save Category Mapping informations send by config page form
     *
     * @return : bool
     * */
    protected function saveCategoryMappingInformations()
    {
        $this->logs->logInfos('# saveCategoryMappingInformations');
        try {
            $psCategories = $this->mapper->getPrestashopCategories();
            $mapping      = array();
            foreach ($psCategories as $cat) {
                $psMapCat    = Tools::getValue('ps_map_'.$cat["id_category"]);
                $hipayMapCat = Tools::getValue('hipay_map_'.$cat["id_category"]);
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
            $this->mapper->setMapping(
                HipayMapper::HIPAY_CAT_MAPPING,
                $mapping
            );

            $this->_successes[] = $this->l('Category mapping configuration saved successfully.');
            return true;
        } catch (Exception $e) {
            // LOGS
            $this->logs->logErrors($e->getMessage());
            $this->_errors[] = $this->l($e->getMessage());
        }
        return false;
    }

    /**
     * Save Account informations send by config page form
     *
     * @return : bool
     * */
    protected function saveAccountInformations()
    {
        $this->logs->logInfos('# SaveAccountInformations');

        try {
            // saving all array "account" in $configHipay
            $accountConfig = array("global" => array(), "sandbox" => array(), "production" => array());

            //requirement : input name in tpl must be the same that name of indexes in $this->configHipay

            foreach ($this->hipayConfigTool->getConfigHipay()["account"]["global"] as $key => $value) {
                $fieldValue                    = Tools::getValue($key);
                $accountConfig["global"][$key] = $fieldValue;
            }

            foreach ($this->hipayConfigTool->getConfigHipay()["account"]["sandbox"] as $key => $value) {
                if (($key == "api_username_sandbox" && Tools::getValue("api_username_sandbox") && !Tools::getValue("api_password_sandbox"))
                    || ($key == "api_password_sandbox" && Tools::getValue("api_password_sandbox") && !Tools::getValue("api_username_sandbox"))
                ) {
                    $this->_errors[] = $this->l("If sandbox api username is filled sandbox api password is mandatory");
                    return false;
                } elseif (($key == "api_tokenjs_username_sandbox" && Tools::getValue("api_tokenjs_username_sandbox") && !Tools::getValue("api_tokenjs_password_publickey_sandbox"))
                    || ($key == "api_tokenjs_password_publickey_sandbox" && Tools::getValue(
                        "api_tokenjs_password_publickey_sandbox"
                    ) && !Tools::getValue("api_tokenjs_username_sandbox"))
                ) {
                    $this->_errors[] = $this->l(
                        "If sandbox api TokenJS username is filled sandbox api TokenJS password is mandatory"
                    );
                    return false;
                } elseif (($key == "api_moto_username_sandbox" && Tools::getValue("api_moto_username_sandbox") && !Tools::getValue("api_moto_password_sandbox"))
                    || ($key == "api_moto_password_sandbox" && Tools::getValue("api_moto_password_sandbox") && !Tools::getValue(
                        "api_moto_username_sandbox"
                    ))
                ) {
                    $this->_errors[] = $this->l(
                        "If sandbox api MO/TO username is filled sandbox api MO/TO password is mandatory"
                    );
                    return false;
                } else {
                    $fieldValue                     = Tools::getValue($key);
                    $accountConfig["sandbox"][$key] = $fieldValue;
                }
            }

            foreach ($this->hipayConfigTool->getConfigHipay()["account"]["production"] as $key => $value) {
                if (($key == "api_username_production" && Tools::getValue("api_username_production") && !Tools::getValue("api_password_production"))
                    || ($key == "api_password_production" && Tools::getValue("api_password_production") && !Tools::getValue("api_username_production"))
                ) {
                    $this->_errors[] = $this->l(
                        "If production api username is filled production api password is mandatory"
                    );
                    return false;
                } elseif (($key == "api_tokenjs_username_production" && Tools::getValue(
                        "api_tokenjs_username_production"
                    ) && !Tools::getValue("api_tokenjs_password_publickey_production")) || ($key == "api_tokenjs_password_publickey_production"
                    && Tools::getValue(
                        "api_tokenjs_password_publickey_production"
                    ) && !Tools::getValue("api_tokenjs_username_production"))
                ) {
                    $this->_errors[] = $this->l(
                        "If production api TokenJS username is filled production api TokenJS password is mandatory"
                    );
                    return false;
                } elseif (($key == "api_moto_username_production" && Tools::getValue("api_moto_username_production") && !Tools::getValue("api_moto_password_production"))
                    || ($key == "api_moto_password_production" && Tools::getValue("api_moto_password_production") && !Tools::getValue("api_moto_username_production"))
                ) {
                    $this->_errors[] = $this->l(
                        "If production api MO/TO username is filled production api MO/TO password is mandatory"
                    );
                    return false;
                } else {
                    $fieldValue                        = Tools::getValue($key);
                    $accountConfig["production"][$key] = $fieldValue;
                }
            }

            //save configuration
            $this->hipayConfigTool->setConfigHiPay(
                "account",
                $accountConfig
            );

            $this->_successes[] = $this->l('Module settings saved successfully.');
            $this->logs->logInfos(
                print_r(
                    $this->hipayConfigTool->getConfigHipay(),
                    true
                )
            );
            return true;
        } catch (Exception $e) {
            // LOGS
            $this->logs->logErrors($e->getMessage());
            $this->_errors[] = $this->l($e->getMessage());
        }

        return false;
    }

    /**
     * Save Global payment informations send by config page form
     *
     * @return : bool
     * */
    protected function saveGlobalPaymentInformations()
    {
        $this->logs->logInfos("# saveGlobalPaymentInformations");

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
                if (is_bool(Tools::getValue($key)) && !Tools::getValue($key)) {
                    $fieldValue = $value;
                } elseif ($key == "css_url" && Tools::getValue("css_url") && !HipayFormControl::checkHttpsUrl(Tools::getValue("css_url"))) {
                    $this->_errors[] = $this->l(
                        "CSS url needs to be a valid https url."
                    );
                    return false;
                } else {
                    $fieldValue = Tools::getValue($key);
                }

                $this->logs->logInfos(
                    $key." => ".print_r(
                        $fieldValue,
                        true
                    )
                );
                $accountConfig["global"][$key] = $fieldValue;
            }
            $conf3d                                        = $this->save3DSecureInformations();
            $accountConfig["global"]["activate_3d_secure"] = $conf3d["global"]["activate_3d_secure"];
            $accountConfig["global"]["3d_secure_rules"]    = $conf3d["global"]["3d_secure_rules"];

            //save configuration
            $this->hipayConfigTool->setConfigHiPay(
                "payment",
                $accountConfig
            );

            $this->_successes[] = $this->l('Global payment method settings saved successfully.');
            $this->logs->logInfos(
                print_r(
                    $this->hipayConfigTool->getConfigHipay(),
                    true
                )
            );
            return true;
        } catch (Exception $e) {
            // LOGS
            $this->logs->logErrors($e->getMessage());
            $this->_errors[] = $this->l($e->getMessage());
        }

        return false;
    }

    /**
     * save credit cards settings form
     * @return boolean
     */
    public function saveCreditCardInformations()
    {
        $this->logs->logInfos("# SaveCreditCardInformations");

        try {
            // saving all array "payemnt" "credit_card" in $configHipay
            $accountConfig = array(
                "global" => $this->hipayConfigTool->getConfigHipay()["payment"]["global"],
                "credit_card" => array(),
                "local_payment" => $this->hipayConfigTool->getConfigHipay()["payment"]["local_payment"]
            );

            $keySaved = array(
                "activated",
                "currencies",
                "countries",
                "minAmount",
                "maxAmount"
            );

            if (Tools::getValue("ccDisplayName")) {
                $accountConfig["global"]["ccDisplayName"] = Tools::getValue("ccDisplayName");
            }

            //requirement : input name in tpl must be the same that name of indexes in $this->configHipay

            foreach ($this->hipayConfigTool->getConfigHipay()["payment"]["credit_card"] as $card => $conf) {
                foreach ($conf as $key => $value) {
                    if (in_array(
                            $key,
                            $keySaved
                        )) {
                        $fieldValue = Tools::getValue($card."_".$key);
                    } else {
                        $fieldValue = $this->hipayConfigTool->getConfigHipay()["payment"]["credit_card"][$card][$key];
                    }

                    $accountConfig["credit_card"][$card][$key] = $fieldValue;
                }
            }
            //save configuration
            $this->hipayConfigTool->setConfigHiPay(
                "payment",
                $accountConfig
            );

            $this->_successes[] = $this->l('Credit card settings saved successfully.');
            $this->logs->logInfos(
                print_r(
                    $this->hipayConfigTool->getConfigHipay(),
                    true
                )
            );
            return true;
        } catch (Exception $e) {
            // LOGS
            $this->logs->logErrors($e->getMessage());
            $this->_errors[] = $this->l($e->getMessage());
        }

        return false;
    }

    /**
     * Save local payment form
     *
     * @return boolean
     */
    public function saveLocalPaymentInformations()
    {
        $this->logs->logInfos("# SaveLocalPaymentInformations");
        try {
            // saving all array "payemnt" "local_payment" in $configHipay
            $accountConfig = array(
                "global" => $this->hipayConfigTool->getConfigHipay()["payment"]["global"],
                "credit_card" => $this->hipayConfigTool->getConfigHipay()["payment"]["credit_card"],
                "local_payment" => array()
            );

            //requirement : input name in tpl must be the same that name of indexes in $this->configHipay

            $keySaved = array(
                "activated",
                "currencies",
                "countries",
                "minAmount",
                "maxAmount",
                "displayName"
            );

            foreach ($this->hipayConfigTool->getConfigHipay()["payment"]["local_payment"] as $card => $conf) {
                foreach ($conf as $key => $value) {
                    //prevent specific fields from being updated
                    if (in_array(
                            $key,
                            $keySaved
                        )) {
                        $fieldValue = Tools::getValue($card."_".$key);
                    } else {
                        $fieldValue = $this->hipayConfigTool->getConfigHipay()["payment"]["local_payment"][$card][$key];
                    }
                    $accountConfig["local_payment"][$card][$key] = $fieldValue;
                }
            }
            //save configuration
            $this->hipayConfigTool->setConfigHiPay(
                "payment",
                $accountConfig
            );

            $this->_successes[] = $this->l('Local payment settings saved successfully.');
            $this->logs->logInfos(
                print_r(
                    $this->hipayConfigTool->getConfigHipay(),
                    true
                )
            );
            return true;
        } catch (Exception $e) {
            // LOGS
            $this->logs->logErrors($e->getMessage());
            $this->_errors[] = $this->l($e->getMessage());
        }

        return false;
    }

    /**
     * Save fraud settings
     * @return boolean
     */
    public function saveFraudInformations()
    {
        $this->logs->logInfos("# SaveFraudInformations");

        try {
            // saving all array "fraud" in $configHipay
            $accountConfig = array();

            //requirement : input name in tpl must be the same that name of indexes in $this->configHipay
            foreach ($this->hipayConfigTool->getConfigHipay()["fraud"] as $key => $value) {
                $fieldValue          = Tools::getValue($key);
                $accountConfig[$key] = $fieldValue;
            }

            //save configuration
            $this->hipayConfigTool->setConfigHiPay(
                "fraud",
                $accountConfig
            );

            $this->_successes[] = $this->l('Fraud settings saved successfully.');
            $this->logs->logInfos(
                print_r(
                    $this->hipayConfigTool->getConfigHipay(),
                    true
                )
            );
            return true;
        } catch (Exception $e) {
            $this->logs->logErrors($e->getMessage());
            $this->_errors[] = $this->l($e->getMessage());
        }

        return false;
    }

    /**
     * List log files
     *
     * @return string
     */
    protected function getLogFiles()
    {
        // Scan log dir
        $directory = $this->logs->getBasePath();
        $files     = scandir($directory,
            1);

        // Init array files
        $error_files    = array();
        $info_files     = array();
        $callback_files = array();
        $request_files  = array();
        $refund_files   = array();

        // List files
        foreach ($files as $file) {
            if (preg_match("/error/i",
                    $file) && count($error_files) < 10) {
                $error_files[] = $file;
            }
            if (preg_match("/callback/i",
                    $file) && count($callback_files) < 10) {
                $callback_files[] = $file;
            }
            if (preg_match("/infos/i",
                    $file) && count($info_files) < 10) {
                $info_files[] = $file;
            }
            if (preg_match("/request/i",
                    $file) && count($request_files) < 10
            ) {
                $request_files[] = $file;
            }
        }

        return array(
            'error' => $error_files,
            'infos' => $info_files,
            'callback' => $callback_files,
            'request' => $request_files,
            'refund' => $refund_files
        );
    }

    /**
     * Clear every single merchant account data
     * @return boolean
     */
    protected function clearAccountData()
    {
        Configuration::deleteByName('HIPAY_CONFIG');
        return true;
    }

    /**
     * return an array of payment methods (set in BO configuration) for the customer country and currency
     * @param Country $country
     * @param Currency $currency
     * @return array
     */
    public function getActivatedPaymentByCountryAndCurrency(
    $paymentMethodType, $country, $currency, $orderTotal = 1
    )
    {
        $activatedPayment = array();
        foreach ($this->hipayConfigTool->getConfigHipay()["payment"][$paymentMethodType] as $name => $settings) {
            if ($settings["activated"] &&
                (empty($settings["countries"]) || in_array(
                    $country->iso_code,
                    $settings["countries"]
                )) &&
                (empty($settings["currencies"]) || in_array(
                    $currency->iso_code,
                    $settings["currencies"]
                )) &&
                $orderTotal >= $settings["minAmount"]["EUR"] && ($orderTotal <= $settings["maxAmount"]["EUR"] || !$settings["maxAmount"]["EUR"])
            ) {
                if ($paymentMethodType == "local_payment") {
                    if (Configuration::get('PS_ROUND_TYPE') == Order::ROUND_LINE || Configuration::get('PS_ROUND_TYPE') == Order::ROUND_ITEM
                        || !$settings["forceBasket"]
                    ) {
                        $activatedPayment[$name]                   = $settings;
                        $activatedPayment[$name]["link"]           = $this->context->link->getModuleLink(
                            $this->name,
                            'redirectlocal',
                            array("method" => $name),
                            true
                        );
                        $activatedPayment[$name]['payment_button'] = $this->_path.'views/img/'.$settings["logo"];
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
    public function updateHiPayOrderStates()
    {
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
            "HIPAY_OS_MOTO_PENDING" => array(
                "waiting_state_color" => "#4169E1",
                "setup" => array(
                    'delivery' => false,
                    'hidden' => false,
                    'invoice' => false,
                    'logable' => false,
                    'module_name' => $this->name,
                    'send_email' => false,
                ),
                "name_FR" => "En attente de paiement MO/TO (Hipay)",
                "name_EN" => "Waiting for MO/TO payment (Hipay)",
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
                "name_FR" => "Expiré (Hipay)",
                "name_EN" => "Expired (Hipay)",
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
                "name_FR" => "Contesté (Hipay) ",
                "name_EN" => "Challenged (Hipay)",
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
                    'send_email' => true,
                    'paid' => false,
                    'template' => 'refund'
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
            $waiting_state_color  = $state["waiting_state_color"];
            $waiting_state_names  = array();

            $setup = $state["setup"];

            foreach (Language::getLanguages(false) as $language) {
                if (Tools::strtolower($language['iso_code']) == 'fr') {
                    $waiting_state_names[(int) $language['id_lang']] = $state["name_FR"];
                } else {
                    $waiting_state_names[(int) $language['id_lang']] = $state["name_EN"];
                }
            }

            $this->saveOrderState(
                $waiting_state_config,
                $waiting_state_color,
                $waiting_state_names,
                $setup
            );
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
    protected function saveOrderState(
    $config, $color, $names, $setup
    )
    {
        $state_id = Configuration::get($config);

        if ((bool) $state_id == true) {
            $order_state = new OrderState($state_id);
        } else {
            $order_state = new OrderState();
        }

        $order_state->name  = $names;
        $order_state->color = $color;

        foreach ($setup as $param => $value) {
            $order_state->{$param} = $value;
        }

        if ((bool) $state_id == true) {
            return $order_state->save();
        } elseif ($order_state->add() == true) {
            Configuration::updateValue(
                $config,
                $order_state->id
            );
            @copy(
                    $this->local_path.'views/img/logo-16.png',
                    _PS_ORDER_STATE_IMG_DIR_.(int) $order_state->id.'.gif'
            );

            return true;
        }
        return false;
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
            $this->smarty->assign(
                'status',
                'ok'
            );
        }
        $this->smarty->assign(
            array(
                'id_order' => $order->id,
                'reference' => $order->reference,
                'params' => $params,
                'total_to_pay' => Tools::displayPrice(
                    $params['total_to_pay'],
                    $params['currencyObj'],
                    false
                ),
                'shop_name' => $this->context->shop->name,
            )
        );
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
    require_once(dirname(__FILE__).'/hipay_enterprise-17.php');
} elseif (_PS_VERSION_ < '1.6') {
    // Version < 1.6
    Tools::displayError('The module HiPay Enterprise is not compatible with your PrestaShop');
}

require_once(dirname(__FILE__).'/classes/helper/tools/HipayLogs.php');
require_once(dirname(__FILE__).'/classes/helper/tools/hipayConfig.php');
require_once(dirname(__FILE__).'/classes/helper/forms/hipayForm.php');
require_once(dirname(__FILE__).'/classes/helper/tools/hipayMapper.php');
require_once(dirname(__FILE__).'/classes/helper/tools/hipayDBQuery.php');
require_once(dirname(__FILE__).'/classes/helper/tools/hipayCCToken.php');
require_once(dirname(__FILE__).'/classes/helper/tools/hipayFormControl.php');
