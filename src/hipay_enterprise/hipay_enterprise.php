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
if (!defined('_PS_VERSION_')) {
    exit;
}
ini_set('serialize_precision', -1);
/**
 * Hipay_enterprise.
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 *
 * @see    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class Hipay_enterprise extends PaymentModule
{

    public $dbUtils;
    public $hipayConfigTool;
    public $hipayUpdateNotif;
    public $_errors = [];
    public $_successes = [];
    public $currencies_titles = [];
    public $moduleCurrencies = [];
    public $_technicalErrors = '';
    private static $paypalVersion = null;

    public function __construct()
    {
        $this->name = 'hipay_enterprise';
        $this->tab = 'payments_gateways';
        $this->version = '2.22.3';
        $this->module_key = 'c3c030302335d08603e8669a5210c744';
        $this->ps_versions_compliancy = ['min' => '1.7.6', 'max' => _PS_VERSION_];
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

        $this->dbSchemaManager = new HipayDBSchemaManager($this);
        $this->dbUtils = new HipayDBUtils($this);

        // init token manger object
        $this->token = new HipayCCToken($this);

        // init config form data manager object
        $this->hipayConfigFormHandler = new HipayConfigFormHandler($this);

        parent::__construct();

        $this->currencies_titles = [];
        $this->countries_titles = [];

        $countries = Country::getCountries($this->context->language->id);

        foreach ($countries as $country) {
            $this->countries_titles[$country['iso_code']] = $country['name'];
        }
        $moduleCurrencies = $this->getCurrency((int) Configuration::get('PS_CURRENCY_DEFAULT'));
        foreach ($moduleCurrencies as $cur) {
            $this->moduleCurrencies[] = $cur['iso_code'];
        }
        $currencies = Currency::getCurrencies();

        foreach ($currencies as $currency) {
            $this->currencies_titles[$currency['iso_code']] = $currency['name'];
        }

        // configuration is handle by an helper class
        $this->hipayConfigTool = new HipayConfig($this);

        // Checking new versions of the module
        $this->hipayUpdateNotif = new HipayUpdateNotif($this);
    }

    /**
     * Translations in Front Controller doesn't work.
     *
     * @see http://forge.prestashop.com/browse/BOOM-3716
     */
    private function fakeTranslation()
    {
        $fake = $this->l('Registered notification from HiPay about captured amount of ');
        $fake = $this->l('Registered notification from HiPay about refunded amount of ');
        $fake = $this->l('Order total amount :');
        $fake = $this->l('Transaction ID: ');
        $fake = $this->l('HiPay status: ');
        $fake = $this->l('A payment transaction is awaiting validation for the order %s');
        $fake = $this->l('Please enter your phone number to use this payment method.');
        $fake = $this->l('Please inform your civility to use this method of payment.');
        $fake = $this->l('Please check the information entered.');
        $fake = $this->l('Please check the phone number entered.');
        $fake = $this->l('The format of the phone number is incorrect.');
        $fake = $this->l('Refused payment for order %s');
        $fake = $this->l('Hash Algorithm for %s was already set with %s');
        $fake = $this->l('Hash Algorithm for %s has been syncrhonize with %s');
        $fake = $this->l('Hash Algorithm for %s has not been updated : You must filled credentials.');
        $fake = $this->l('The HiPay transaction was not canceled because no transaction reference exists. You can see and cancel the transaction directly from HiPay\'s BackOffice');
        $fake = $this->l('The HiPay transaction was not canceled because it\'s status doesn\'t allow cancellation. You can see and cancel the transaction directly from HiPay\'s BackOffice');
        $fake = $this->l('There was an error on the cancellation of the HiPay transaction. You can see and cancel the transaction directly from HiPay\'s BackOffice');
        $fake = $this->l('Message was : ');
        $fake = $this->l('Transaction cancellation requested');
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
     * Functions installation HiPay module or uninstall.
     */
    public function install()
    {
        if (false == extension_loaded('soap')) {
            $this->_errors[] = $this->l('You have to enable the SOAP extension on your server to install this module');

            return false;
        }

        $returnVal = parent::install() && $this->installHipay();

        $this->logs->setInstallProcess(false);

        return $returnVal;
    }

    public function uninstall()
    {
        return $this->uninstallAdminTab() &&
            parent::uninstall() &&
            HipayHelper::clearAccountData() &&
            $this->deleteHipayTable();
    }

    /**
     * Installation procedure for the HiPay module.
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function installHipay()
    {
        $return = $this->installAdminTab();
        $return &= HipayOrderStatus::updateHiPayOrderStates($this);
        $return &= $this->createHipayTable();

        $this->hipayConfigTool->getConfigHipay();
        $this->hipayConfigTool->updateFromJSONFile();

        $return &= $this->registerHook('displayBackOfficeHeader');
        $return &= $this->registerHook('displayAdminOrder');
        $return &= $this->registerHook('customerAccount');
        $return &= $this->registerHook('updateCarrier');
        $return &= $this->registerHook('actionAdminDeleteBefore');
        $return &= $this->registerHook('actionAdminBulKDeleteBefore');
        $return &= $this->registerHook('dashboardZoneOne');
        $return &= $this->registerHook('actionOrderStatusUpdate');
        $return &= $this->registerHook('actionOrderSlipAdd');
        $return &= $this->registerHook('actionDispatcher');
        $return &= $this->registerHook('displayOrderDetail');
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

        Configuration::updateValue('HIPAY_NOTIFICATION_THRESHOLD', 4);

        return $return && $this->installHook();
    }

    public function installHook()
    {
        $hook = new Hook();
        $hook->name = 'actionHipayApiRequest';
        $hook->title = 'HiPay API Request';
        $hook->description = 'This hook is called right before HiPay calls its payment API';

        return $hook->add();
    }

    public function installAdminTab()
    {
        $class_names = [
            'AdminHiPayCapture',
            'AdminHiPayRefund',
            'AdminHiPayMoto',
            'AdminHiPayChallenge',
            'AdminHiPayConfig',
            'AdminHiPaySynchronizeHashing',
            'AdminHiPayCalculatePrice',
        ];

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
        $class_names = [
            'AdminHiPayCapture',
            'AdminHiPayRefund',
            'AdminHiPayMoto',
            'AdminHiPayChallenge',
            'AdminHiPayConfig',
            'AdminHiPaySynchronizeHashing',
            'AdminHiPayCalculatePrice',
        ];
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
     * Sending refund request.
     *
     * @param array $params
     *
     * @throws Exception
     */
    public function hookActionOrderSlipAdd($params)
    {
        // Triggers only if merchant wants to use the native form to handle HiPay refunds
        // Otherwise, use custom HiPay refund form
        if ($this->hipayConfigTool->getAccountGlobal()['use_prestashop_refund_form']) {
            $order = new Order($params['order']->id);

            // Check if order needs to be refunded by HiPay
            if (HipayHelper::isHipayOrder($this, $order)) {
                // Get order slip to get fees
                $orderSlip = $order
                    ->getOrderSlipsCollection()
                    ->orderBy('date_add', 'desc')
                    ->getFirst();

                try {
                    $maintenanceParams = [
                        'order' => $order->id,
                        'operation' => HiPay\Fullservice\Enum\Transaction\Operation::REFUND,
                    ];

                    $isBasket = false;

                    $maintenaceDBHelper = new HipayDBMaintenance($this);
                    $maintenanceParams['transaction_reference'] = $maintenaceDBHelper->getTransactionReference($order->id);

                    // Check if transaction was created in basket mode or not
                    $transaction = $maintenaceDBHelper->getTransactionByRef($maintenanceParams['transaction_reference']);

                    if ($transaction) {
                        if ($transaction['basket']) {
                            $isBasket = false;
                        }

                        // Check if basket is activated for this order
                        if ($isBasket) {
                            $this->getLogs()->logInfos("# Refund using basket order ID {$params['order']->id}");

                            $refundItems = [];
                            $orderDetailList = $order->getOrderDetailList();

                            foreach ($params['productList'] as $product) {
                                $productId = null;
                                foreach ($orderDetailList as $orderDetail) {
                                    if ($orderDetail['id_order_detail'] == $product['id_order_detail']) {
                                        $productId = $orderDetail['product_attribute_id'];
                                        break;
                                    }
                                }

                                $refundItems[$productId] = $product['quantity'];
                            }

                            $maintenanceParams['refundItems'] = $refundItems;
                            $maintenanceParams['capture_refund_fee'] = $orderSlip->total_shipping_tax_incl;
                            $maintenanceParams['capture_refund_wrapping'] = true;
                            $maintenanceParams['capture_refund_discount'] = true;
                        } else {
                            $this->getLogs()->logInfos("# Refund without basket order ID {$params['order']->id}");

                            $refund_amount = 0;
                            foreach ($params['productList'] as $product) {
                                $refund_amount += $product['amount'];
                            }

                            $maintenanceParams['amount'] = $refund_amount + $orderSlip->total_shipping_tax_incl;
                        }

                        $maintenanceParams['orderSlipId'] = $orderSlip->id;

                        ApiCaller::requestMaintenance($this, $maintenanceParams);
                    } else {
                        throw new Exception($this->l('Unable to retrieve refund transaction.'));
                    }
                } catch (Exception $e) {
                    // If an error occurred, cancel the prestashop part of the refund
                    if ($orderSlip) {
                        HipayHelper::deleteOrderSlip($orderSlip);
                    }

                    $this->getLogs()->logErrors("Refund exception: {$e->getMessage()}");

                    throw new Exception($this->l('HiPay error: An error occurred while handling the refund'));
                }
            }
        }
    }

    /**
     * Changing order status.
     *
     * @param array $params
     */
    public function hookActionOrderStatusUpdate($params)
    {
        $idOrder = $params['id_order'];
        $order = new OrderCore($idOrder);

        // Handle cancellation only if order was fulfilled using HiPay Gateway
        if (HipayHelper::isHipayOrder($this, $order)) {
            /**
             * @var OrderState $newOrderStatus
             */
            $newOrderStatus = $params['newOrderStatus'];

            if ($newOrderStatus->id == Configuration::get('PS_OS_CANCELED')) {
                $maintenaceDBHelper = new HipayDBMaintenance($this);
                try {
                    $transactionId = $maintenaceDBHelper->getTransactionReference($idOrder);
                } catch (PrestaShopDatabaseException $e) {
                    $transactionId = '';
                }

                $apiHandler = new Apihandler($this, $this->context);
                $cancelResult = $apiHandler->handleCancel(
                    [
                        'order' => $idOrder,
                        'transaction_reference' => $transactionId,
                        'operation' => \HiPay\Fullservice\Enum\Transaction\Operation::CANCEL,
                    ]
                );
            }
        }
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
        $idCarrierOld = (int) $params['id_carrier'];
        $idCarrierNew = (int) $params['carrier']->id;

        $this->mapper->updateCarrier($idCarrierOld, $idCarrierNew);
    }

    public function hookCustomerAccount()
    {
        if ($this->hipayConfigTool->getPaymentGlobal()['card_token']) {
            $this->smarty->assign(
                [
                    'HiPay_link' => $this->context->link->getModuleLink($this->name, 'userToken', [], true),
                ]
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

    public function hookDisplayBackOfficeHeader()
    {
        $this->context->controller->addCSS($this->_path . 'views/css/bootstrap-duallistbox.min.css', 'all');
        $this->context->controller->addCSS($this->_path . 'views/css/bootstrap-multiselect.css', 'all');
        $this->context->controller->addCSS($this->_path . 'views/css/back.css', 'all');

        $this->context->controller->addJS($this->_path . '/views/js/form-input-control.js', 'all');
        $this->context->controller->addJS($this->_path . '/views/js/md5.js', 'all');
    }

    public function hookDisplayOrderDetail($params)
    {
        $transaction = $this->dbUtils->getTransactionByOrderId($params['order']->id);

        if (isset($transaction['reference_to_pay'])) {
            $referenceToPay = $transaction['reference_to_pay'];

            $this->context->controller->registerJavascript(
                'hipay-sdk-js',
                $this->hipayConfigTool->getPaymentGlobal()['sdk_js_url'],
                ['server' => 'remote', 'position' => 'top', 'priority' => 1]
            );

            $this->smarty->assign(
                [
                    'HiPay_lang' => Tools::strtolower($this->context->language->iso_code),
                    'HiPay_referenceToPay' => $referenceToPay,
                    'HiPay_method' => $transaction['payment_product'],
                ]
            );

            return $this->display(dirname(__FILE__), 'views/templates/hook/referenceToPay.tpl');
        }
    }

    public function hookHeader()
    {
        $this->context->controller->addCSS($this->_path . '/views/css/payment-return-pending.css', 'all');
    }

    /**
     * Handling prestashop hook payment. Adding payment methods (PS16).
     *
     * @param type $params
     *
     * @return type
     */
    public function hookPayment($params)
    {
        $idAddress = $params['cart']->id_address_invoice ? $params['cart']->id_address_invoice : $params['cart']->id_address_delivery;
        $address = new Address((int) $idAddress);
        $country = new Country((int) $address->id_country);
        $currency = new Currency((int) $params['cart']->id_currency);
        $orderTotal = $params['cart']->getOrderTotal();
        $this->context->controller->addJS([_MODULE_DIR_ . 'hipay_enterprise/views/js/devicefingerprint.js']);
        $customer = new Customer((int) $params['cart']->id_customer);

        $this->smarty->assign(
            [
                'HiPay_domain' => Tools::getShopDomainSSL(true),
                'HiPay_module_dir' => $this->_path,
                'HiPay_payment_button' => $this->_path . 'views/img/cc.png',
                'HiPay_configHipay' => $this->hipayConfigTool->getConfigHipay(),
                'HiPay_sortedPaymentProducts' => HipayHelper::getSortedActivatedPaymentByCountryAndCurrency(
                    $this,
                    $this->hipayConfigTool->getConfigHipay(),
                    $country,
                    $currency,
                    $address,
                    $customer,
                    $orderTotal
                ),
                'HiPay_lang' => Tools::strtolower($this->context->language->iso_code),
                'HiPay_isOperatingModeHostedPage' => ApiMode::HOSTED_PAGE === $this->hipayConfigTool->getPaymentGlobal()['operating_mode']['APIMode'],
            ]
        );
        $this->smarty->assign('hipay_prod', !(bool) $this->hipayConfigTool->getAccountGlobal()['sandbox_mode']);

        return $this->display(dirname(__FILE__), 'views/templates/hook/ps16/payment-16.tpl');
    }

    /**
     *  Adding payment methods (PS16).
     *
     * @return array
     */
    public function hookDisplayPaymentEU($params)
    {
        $idAddress = $params['cart']->id_address_invoice ? $params['cart']->id_address_invoice :
            $params['cart']->id_address_delivery;
        $address = new Address((int) $idAddress);
        $country = new Country((int) $address->id_country);
        $currency = new Currency((int) $params['cart']->id_currency);
        $orderTotal = $params['cart']->getOrderTotal();
        $customer = new Customer((int) $params['cart']->id_customer);

        $paymentOptions = [];

        $sortedPaymentProducts = HipayHelper::getSortedActivatedPaymentByCountryAndCurrency(
            $this,
            $this->hipayConfigTool->getConfigHipay(),
            $country,
            $currency,
            $address,
            $customer,
            $orderTotal
        );

        if (!empty($sortedPaymentProducts)) {
            foreach ($sortedPaymentProducts as $name => $paymentProduct) {
                if ('credit_card' == $name) {
                    $paymentOptions[] =
                        [
                            'cta_text' => $this->l('Pay by credit card'),
                            'logo' => Media::getMediaPath($this->_path . 'views/img/amexa200.png'),
                            'action' => $this->context->link->getModuleLink($this->name, 'redirect', [], true),
                        ];
                } else {
                    $paymentOptions[] =
                        [
                            'cta_text' => $this->l('Pay by') . ' ' . $paymentProduct['displayName'],
                            'logo' => Media::getMediaPath($paymentProduct['payment_button']),
                            'action' => $paymentProduct['link'],
                        ];
                }
            }
        }

        return $paymentOptions;
    }

    /**
     * Displays notification block in main admin dashboard.
     *
     * @return mixed
     */
    public function hookDashboardZoneOne($params)
    {
        return $this->hipayUpdateNotif->displayBlock();
    }

    /**
     * Handling prestashop payment hook. Adding payment methods (PS17).
     *
     * @param type $params
     *
     * @return type
     */
    public function hookPaymentOptions($params)
    {
        $hipay17 = new HipayEnterpriseNew();
        // Fix Bug with translation and bad context ( Hook in an another file)
        $params['translation_checkout'] = $this->l('You will be redirected to an external payment page. Please do not refresh the page during the process');

        return $hipay17->hipayPaymentOptions($params);
    }

    /**
     * @param type $params
     *
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
     * @param type $params
     *
     * @return type
     */
    private function hipayPaymentReturn($params)
    {
        // Payment Return for PS1.6
        if (false == $this->active) {
            return;
        }
        $order = $params['objOrder'];
        if ($order->getCurrentOrderState()->id != Configuration::get('PS_OS_ERROR')) {
            $this->smarty->assign('HiPay_status', 'ok');
        }
        $this->smarty->assign(
            [
                'HiPay_id_order' => $order->id,
                'HiPay_reference' => $order->reference,
                'HiPay_params' => $params,
                'HiPay_total_to_pay' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
                'HiPay_shop_name' => $this->context->shop->name,
            ]
        );
    }

    /**
     * Display refund and capture blocks in order admin page.
     */
    public function hookDisplayAdminOrder()
    {
        $hipayMaintenanceBlock = new HipayMaintenanceBlock($this, (int) Tools::getValue('id_order'));

        return $hipayMaintenanceBlock->displayBlock();
    }

    /**
     * We register the plugin everytime a controller is instantiated
     */
    public function hookActionDispatcher()
    {
        $this->context->smarty->registerPlugin('modifier', 'htmlEntityDecode', 'html_entity_decode');
        $this->context->smarty->registerPlugin('modifier', 'inArray', 'in_array');
        $this->context->smarty->registerPlugin('modifier', 'arrayKeyExists', 'array_key_exists');
    }

    /**
     * Load configuration page.
     *
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

        $source = ['brand_version' => _PS_VERSION_, 'integration_version' => $this->version];

        if (!Configuration::get('PS_SSL_ENABLED')) {
            $this->_technicalErrors = $this->l('A SSL certificate is required to process credit card payments using HiPay. Please consult the FAQ.');
        }

        $this->context->smarty->assign(
            [
                'HiPay_module_dir' => $this->_path,
                'HiPay_config_hipay' => $this->hipayConfigTool->getConfigHipay(),
                'HiPay_logs' => $this->getLogs()->getLogFiles(),
                'HiPay_module_url' => AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                'HiPay_fraud_form' => $formGenerator->getFraudForm(),
                'HiPay_form_errors' => $this->_errors,
                'HiPay_form_successes' => $this->_successes,
                'HiPay_technicalErrors' => $this->_technicalErrors,
                'HiPay_limitedCurrencies' => $this->currencies_titles,
                'HiPay_default_currency' => Configuration::get('PS_SHOP_DEFAULT'),
                'HiPay_limitedCountries' => $this->countries_titles,
                'HiPay_this_callback' => $this->context->link->getModuleLink(
                    $this->name,
                    'notify',
                    [],
                    true,
                    null,
                    (int) $this->context->shop->id
                ),
                'HiPay_ipaddr' => $_SERVER['REMOTE_ADDR'],
                'HiPay_psCategories' => $psCategories,
                'HiPay_hipayCategories' => $hipayCategories,
                'HiPay_mappedCategories' => $mappedCategories,
                'HiPay_psCarriers' => $psCarriers,
                'HiPay_hipayCarriers' => $hipayCarriers,
                'HiPay_mappedCarriers' => $mappedCarriers,
                'HiPay_lang' => Tools::strtolower($this->context->language->iso_code),
                'HiPay_languages' => Language::getLanguages(false),
                'HiPay_source' => $source,
                'HiPay_ps_round_total' => Order::ROUND_TOTAL == Configuration::get('PS_ROUND_TYPE'),
                'HiPay_ajax_url' => $this->context->link->getAdminLink('AdminModules'),
                'HiPay_url_site' => Tools::getHttpHost(true) . __PS_BASE_URI__,
                'HiPay_syncLink' => $this->context->link->getAdminLink('AdminHiPaySynchronizeHashing'),
                'HiPay_syncToken' => Tools::getAdminTokenLite('AdminHiPaySynchronizeHashing'),
                'HiPay_updateNotif' => $this->hipayUpdateNotif,
                'HiPay_prestashopVersion' => _PS_VERSION_
            ]
        );

        return $this->context->smarty->fetch($configuration);
    }

    /**
     * Process HTTP request send by module confguration page.
     */
    protected function postProcess()
    {
        // ==================================//
        // ===         LOG VIEW           ===//
        // ==================================//
        if (Tools::isSubmit('logfile')) {
            $logFile = Tools::getValue('logfile');
            $this->logs->displayLogFile($logFile);
            // ==================================//
            // ===         ACCOUNT VIEW       ===//
            // ==================================//
        } elseif (Tools::isSubmit('submitAccount')) {
            $this->logs->logInfos('# submitAccount');

            $this->hipayConfigFormHandler->saveAccountInformations();

            $this->context->smarty->assign(
                'HiPay_active_tab',
                'account_form'
            );
            // ==================================//
            // ===   GLOBAL PAYMENT METHODS   ===//
            // ==================================//
        } elseif (Tools::isSubmit('submitGlobalPaymentMethods')) {
            $this->logs->logInfos('# submitGlobalPaymentMethods');
            $this->hipayConfigFormHandler->saveGlobalPaymentInformations();
            $this->context->smarty->assign(
                'HiPay_active_tab',
                'payment_form'
            );
        } elseif (Tools::isSubmit('creditCardSubmit')) {
            $this->logs->logInfos('# creditCardSubmit');
            $this->hipayConfigFormHandler->saveCreditCardInformations($this->context);
            $this->context->smarty->assign(
                'HiPay_active_tab',
                'payment_form'
            );
        } elseif (Tools::isSubmit('localPaymentSubmit')) {
            $this->logs->logInfos('# localPaymentSubmit');
            $this->hipayConfigFormHandler->saveLocalPaymentInformations($this->context);
            $this->context->smarty->assign(
                'HiPay_active_tab',
                'payment_form'
            );
        } elseif (Tools::isSubmit('fraudSubmit')) {
            $this->logs->logInfos('# fraudSubmit');
            $this->hipayConfigFormHandler->saveFraudInformations();
            $this->context->smarty->assign(
                'HiPay_active_tab',
                'fraud_form'
            );
        } elseif (Tools::isSubmit('submitCategoryMapping')) {
            $this->logs->logInfos('# submitCategoryMapping');
            $this->hipayConfigFormHandler->saveCategoryMappingInformations();
            $this->context->smarty->assign(
                'HiPay_active_tab',
                'category_form'
            );
        } elseif (Tools::isSubmit('submitCarrierMapping')) {
            $this->logs->logInfos('# submitCarrierMapping');
            $this->hipayConfigFormHandler->saveCarrierMappingInformations();
            $this->context->smarty->assign(
                'HiPay_active_tab',
                'carrier_form'
            );
        }
    }

    private function createHipayTable()
    {
        $this->mapper->createTable();
        $this->dbSchemaManager->createOrderRefundCaptureTable();
        $this->dbSchemaManager->createCCTokenTable();
        $this->dbSchemaManager->createHipayTransactionTable();
        $this->dbSchemaManager->createHipayOrderCaptureType();
        $this->dbSchemaManager->createHipayNotificationTable();

        return true;
    }

    private function deleteHipayTable()
    {
        $this->mapper->deleteTable();
        $this->dbSchemaManager->deleteCCTokenTable();
        $this->dbSchemaManager->deleteHipayNotificationTable();
        $this->dbSchemaManager->deleteHipayPaymentConfigTable();

        return true;
    }

    /**
     * Check if PayPal instance is V2
     *
     * @param $hipayConfigTool
     * @param $paymentCode
     * @return bool
     * @throws Exception
     */
    public static function isPaypalV2($paymentCode, $hipayConfigTool)
    {
        if (self::$paypalVersion === null) {
            $hipayProducts = HipayAvailablePaymentProducts::getInstance($hipayConfigTool);
            $paymentsProducts = $hipayProducts->getAvailablePaymentProducts('paypal')[0];

            self::$paypalVersion = isset($paymentsProducts['options']['provider_architecture_version'])
                && $paymentsProducts['options']['provider_architecture_version'] === 'v1'
                && !empty($paymentsProducts['options']['payer_id']);
        }

        return 'paypal' === $paymentCode && self::$paypalVersion;
    }
}

if (_PS_VERSION_ >= '1.7') {
    // version 1.7
    require_once dirname(__FILE__) . '/hipay_enterprise-17.php';
} elseif (_PS_VERSION_ < '1.6') {
    // Version < 1.6
    Tools::displayError('The module HiPay Enterprise is not compatible with your PrestaShop');
}

require_once dirname(__FILE__) . '/classes/helper/HipayLogs.php';
require_once dirname(__FILE__) . '/classes/helper/HipayConfig.php';
require_once dirname(__FILE__) . '/classes/forms/HipayForm.php';
require_once dirname(__FILE__) . '/classes/helper/HipayMapper.php';
require_once dirname(__FILE__) . '/classes/helper/HipayHelper.php';
require_once dirname(__FILE__) . '/classes/helper/dbquery/HipayDBSchemaManager.php';
require_once dirname(__FILE__) . '/classes/helper/HipayCCToken.php';
require_once dirname(__FILE__) . '/classes/helper/HipayOrderStatus.php';
require_once dirname(__FILE__) . '/classes/helper/HipayFormControl.php';
require_once dirname(__FILE__) . '/classes/helper/HipayConfigFormHandler.php';
require_once dirname(__FILE__) . '/classes/helper/HipayMaintenanceBlock.php';
require_once dirname(__FILE__) . '/classes/helper/HipayUpdateNotif.php';
require_once dirname(__FILE__) . '/classes/helper/HipayAvailablePaymentProducts.php';