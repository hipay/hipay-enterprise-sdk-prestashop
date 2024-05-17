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
require_once dirname(__FILE__).'/classes/apiCaller/ApiCaller.php';
require_once dirname(__FILE__).'/classes/helper/HipayCCToken.php';
require_once dirname(__FILE__).'/classes/helper/HipayHelper.php';
require_once dirname(__FILE__).'/classes/apiHandler/ApiHandler.php';
require_once dirname(__FILE__).'/classes/helper/enums/UXMode.php';

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

/**
 * HipayEnterpriseNew.
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 *
 * @see    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class HipayEnterpriseNew extends Hipay_enterprise
{
    private $customer;

    /**
     * Display new payment options.
     *
     * @param type $params
     *
     * @return type
     */
    public function hipayPaymentOptions($params)
    {
        if (!$this->active) {
            return;
        }

        if (!$this->checkCurrency($params['cart'])) {
            return;
        }

        $payment_options = $this->hipayExternalPaymentOption($params);

        return $payment_options;
    }

    /**
     * Display payment forms (PS17).
     *
     * @param type $params
     *
     * @return PaymentOption
     */
    public function hipayExternalPaymentOption($params)
    {
        try {
            $idAddress = $params['cart']->id_address_invoice ? $params['cart']->id_address_invoice : $params['cart']->id_address_delivery;
            $address = new Address((int) $idAddress);
            $country = new Country((int) $address->id_country);
            $currency = new Currency((int) $params['cart']->id_currency);
            $this->customer = new Customer((int) $params['cart']->id_customer);

            $paymentOptions = [];
            $sortedPaymentProducts = HipayHelper::getSortedActivatedPaymentByCountryAndCurrency(
                $this,
                $this->hipayConfigTool->getConfigHipay(),
                $country,
                $currency,
                $params['cart']->getOrderTotal(),
                $address,
                $this->customer
            );

            if (!empty($sortedPaymentProducts)) {
                $this->context->smarty->assign(
                    [
                        'HiPay_module_dir' => $this->_path,
                        'HiPay_confHipay' => $this->hipayConfigTool->getConfigHipay(),
                        'hipay_enterprise_tpl_dir' => _PS_MODULE_DIR_.$this->name.'/views/templates',
                    ]
                );
                foreach ($sortedPaymentProducts as $key => $paymentProduct) {
                    if ('credit_card' == $key) {
                        $this->setCCPaymentOptions(
                            $paymentOptions,
                            $paymentProduct,
                            $params
                        );
                    } else {
                        $this->setLocalPaymentOptions(
                            $paymentOptions,
                            $key,
                            $paymentProduct
                        );
                    }
                }
            }

            return $paymentOptions;
        } catch (Exception $exc) {
            $this->logs->logException($exc);
        }
    }

    /**
     * set local payment options.
     *
     * @param array $paymentOptions
     * @param type  $name
     * @param type  $paymentProduct
     */
    private function setLocalPaymentOptions(&$paymentOptions, $name, $paymentProduct)
    {
        $newOption = new PaymentOption();
        if ('applepay' === $name || HipayHelper::isPaypalV2($paymentProduct)) {
            $this->context->smarty->assign(
                [
                    'HiPay_action' => $this->context->link->getModuleLink(
                        $this->name,
                        'redirect',
                        [],
                        true
                    ),
                    'HiPay_localPaymentName' => $name,
                    'HiPay_merchantId' => $paymentProduct['merchantId'] ?? null,
                    'HiPay_errorMsg' => isset($paymentProduct['errorMsg']) ? $paymentProduct['errorMsg'] : null,
                ]
            );
        } else {
            $this->context->smarty->assign(
                [
                    'HiPay_action' => $this->context->link->getModuleLink(
                        $this->name,
                        'redirectlocal',
                        ['method' => $name],
                        true
                    ),
                    'HiPay_localPaymentName' => $name,
                    'HiPay_errorMsg' => isset($paymentProduct['errorMsg']) ? $paymentProduct['errorMsg'] : null,
                ]
            );
        }

        if (
            // If this payment product force the use of hosted pages
            // or if it handle hosted pages and the module is set to use them
            // then use hosted page
            (
                isset($paymentProduct['forceHpayment'])
                && $paymentProduct['forceHpayment']
            )
            || (
                isset($paymentProduct['handleHpayment'])
                && $paymentProduct['handleHpayment']
                && ApiMode::HOSTED_PAGE === $this->hipayConfigTool->getPaymentGlobal()['operating_mode']['APIMode']
            )
        ) {
            $iframe = false;

            if (isset($this->hipayConfigTool->getLocalPayment()[$name]['iframe'])
                && $this->hipayConfigTool->getLocalPayment()[$name]['iframe']
            ) {
                $iframe = true;
            }

            $this->context->smarty->assign([
                'HiPay_language' => $this->context->language->language_code,
                'HiPay_forceHpayment' => true,
                'HiPay_iframe' => $iframe,
            ]);
        } else {

            $formFields = [];
            // Check if any additional fields dans be filed using values already filled by the client
            if (isset($this->hipayConfigTool->getLocalPayment()[$name]['additionalFields'])
                && isset($this->hipayConfigTool->getLocalPayment()[$name]['additionalFields']['formFields'])
            ) {
                $formFields = $this->hipayConfigTool->getLocalPayment()[$name]['additionalFields']['formFields'];
                foreach ($formFields as $fieldName => $field) {
                    switch ($fieldName) {
                        case 'phone':
                            $cart = $this->context->cart;
                            $idAddress = $cart->id_address_invoice ? $cart->id_address_invoice : $cart->id_address_delivery;
                            $address = new Address((int) $idAddress);

                            $phone = $address->phone_mobile ? $address->phone_mobile : $address->phone;
                            $formFields[$fieldName]['defaultValue'] = $phone;

                            break;
                    }
                }
            }

            if ('applepay' === $name) {
                $formFields = [
                    'buttonType' => $paymentProduct['buttonType'],
                    'buttonStyle' => $paymentProduct['buttonStyle'],
                    'merchantId' => $paymentProduct['merchantId'],
                ];

                $configAccountGlobal = $this->hipayConfigTool->getAccountGlobal();

                if ($configAccountGlobal['sandbox_mode']) {
                    $config = $this->hipayConfigTool->getAccountSandbox();

                    $credentials = [
                        'api_apple_pay_username' => (
                            !empty($config['api_tokenjs_apple_pay_username_sandbox'])
                                ? $config['api_tokenjs_apple_pay_username_sandbox']
                                : $config['api_username_sandbox']
                        ),
                        'api_apple_pay_password' => (
                            !empty($config['api_tokenjs_apple_pay_password_sandbox'])
                                ? $config['api_tokenjs_apple_pay_password_sandbox']
                                : $config['api_password_sandbox']
                        ),
                    ];
                } else {
                    $config = $this->hipayConfigTool->getAccountProduction();

                    $credentials = [
                        'api_apple_pay_username' => (
                            !empty($config['api_tokenjs_apple_pay_username_production'])
                                ? $config['api_tokenjs_apple_pay_username_production']
                                : $config['api_username_production']
                        ),
                        'api_apple_pay_password' => (
                            !empty($config['api_tokenjs_apple_pay_password_production'])
                                ? $config['api_tokenjs_apple_pay_password_production']
                                : $config['api_password_production']
                        ),
                    ];
                }

                $currency = $this->getCurrency($this->context->cart->id_currency);

                $idAddress = $this->context->cart->id_address_invoice
                    ? $this->context->cart->id_address_invoice
                    : $this->context->cart->id_address_delivery;

                $address = new Address((int) $idAddress);
                $country = new Country((int) $address->id_country);

                $templateCart = [
                    'totalAmount' => $this->context->cart->getCartTotalPrice(),
                    'currencyCode' => $currency[0]['iso_code'],
                    'countryCode' => $country->iso_code,
                ];

                $this->context->smarty->assign(
                    [
                        'HiPay_cart' => $templateCart,
                        'HiPay_this_path_ssl' => Tools::getShopDomainSsl(true, true).
                        __PS_BASE_URI__.
                        'modules/'.
                        $this->name.
                        '/',
                        'HiPay_configAccountGlobal' => $configAccountGlobal,
                        'HiPay_language_iso_code' => $this->context->language->language_code,
                        'HiPay_environment' => $configAccountGlobal['sandbox_mode'] ? 'stage' : 'production',
                        'HiPay_credentials' => $credentials,
                        'HiPay_appleFields' => $formFields,
                        'HiPay_language' => $this->context->language->language_code,
                        'HiPay_forceHpayment' => false,
                        'HiPay_iframe' => false,
                    ]
                );
            } elseif (HipayHelper::isPaypalV2($paymentProduct)) {

                $formFields = [
                    'buttonShape' => $paymentProduct['buttonShape'],
                    'buttonLabel' => $paymentProduct['buttonLabel'],
                    'buttonColor' => $paymentProduct['buttonColor'],
                    'buttonHeight' => $paymentProduct['buttonHeight'],
                    'bnpl' => $paymentProduct['bnpl'],
                    'merchantId' => $paymentProduct['merchantId'],
                ];

                $currency = $this->getCurrency($this->context->cart->id_currency);

                $idAddress = $this->context->cart->id_address_invoice
                    ? $this->context->cart->id_address_invoice
                    : $this->context->cart->id_address_delivery;

                $address = new Address((int) $idAddress);
                $country = new Country((int) $address->id_country);

                $templateCart = [
                    'totalAmount' => $this->context->cart->getCartTotalPrice(),
                    'currencyCode' => $currency[0]['iso_code'],
                    'countryCode' => $country->iso_code,
                ];
                $this->context->smarty->assign(
                    [
                        'HiPay_cart' => $templateCart,
                        'HiPay_this_path_ssl' => Tools::getShopDomainSsl(true, true).
                            __PS_BASE_URI__.
                            'modules/'.
                            $this->name.
                            '/',
                        'HiPay_language_iso_code' => $this->context->language->language_code,
                        'HiPay_paypalFields' => $formFields,
                        'HiPay_language' => $this->context->language->language_code,
                        'HiPay_forceHpayment' => false,
                        'HiPay_iframe' => false,
                    ]
                );
            } else {

                $this->context->smarty->assign(
                    [
                        'HiPay_language' => $this->context->language->language_code,
                        'HiPay_forceHpayment' => false,
                        'HiPay_iframe' => false,
                        'HiPay_this_path_ssl' => Tools::getShopDomainSsl(true, true).
                        __PS_BASE_URI__.
                        'modules/'.
                        $this->name.
                        '/',
                        'HiPay_confHipay' => $this->hipayConfigTool->getConfigHipay(),
                        'HiPay_languageIsoCode' => $this->context->language->iso_code,
                    ]
                );
            }
        }

        $paymentForm = $this->fetch(
            'module:'.$this->name.'/views/templates/front/payment/ps17/paymentLocalForm-17.tpl'
        );
        if (isset($paymentProduct['displayName'][$this->context->language->iso_code])) {
            $displayName = $paymentProduct['displayName'][$this->context->language->iso_code];
        } else {
            if (isset($paymentProduct['displayName']) && !is_array($paymentProduct['displayName'])) {
                $displayName = $paymentProduct['displayName'];
            } else {
                $displayName = $paymentProduct['displayName']['en'];
            }
        }

        $newOption->setCallToActionText($this->l('Pay by').' '.$displayName)
            ->setModuleName('local_payment_hipay')
            ->setForm($paymentForm);

        if ('applepay' === $name || ('paypal' === $name && !empty($paymentProduct['merchantId']))) {
            $newOption->setAction(
                $this->context->link->getModuleLink($this->name, 'redirect', [], true)
            );
        } else {
            $newOption->setAction(
                $this->context->link->getModuleLink($this->name, 'redirectlocal', ['method' => $name], true)
            );
        }

        // if no credit card, we force ioBB input to be displayed
        if (0 == count($paymentOptions)) {
            $ioBB = '<input id="ioBB" type="hidden" name="ioBB">';
            $newOption->setAdditionalInformation($ioBB);
        }
        $paymentOptions[] = $newOption;
    }

    /**
     * set credit card payment option.
     *
     * @param type $paymentOptions
     * @param type $paymentProduct
     * @param type $params
     */
    private function setCCPaymentOptions(&$paymentOptions, $paymentProduct, $params)
    {
        if (!empty($paymentProduct['products'])) {
            if (isset(
                $this->hipayConfigTool->getPaymentGlobal()['ccDisplayName'][$this->context->language->iso_code]
            )
            ) {
                $displayName = $this->hipayConfigTool->getPaymentGlobal()['ccDisplayName'][$this->context->language->iso_code];
            } else {
                if (isset($this->hipayConfigTool->getPaymentGlobal()['ccDisplayName'])
                    && !is_array($this->hipayConfigTool->getPaymentGlobal()['ccDisplayName'])
                ) {
                    $displayName = $this->hipayConfigTool->getPaymentGlobal()['ccDisplayName'];
                } else {
                    $displayName = $this->hipayConfigTool->getPaymentGlobal()['ccDisplayName']['en'];
                }
            }

            // displaying different forms depending of the operating mode chosen in the BO configuration
            $uxMode = $this->hipayConfigTool->getPaymentGlobal()['operating_mode']['UXMode'];
            $newOption = new PaymentOption();

            $this->context->smarty->assign(
                ['HiPay_action' => $this->context->link->getModuleLink($this->name, 'redirect', [], true)]
            );

            switch ($uxMode) {
                case UXMode::DIRECT_POST:
                case UXMode::HOSTED_FIELDS:
                case UXMode::HOSTED_PAGE:
                    // set credit card for one click
                    $this->ccToken = new HipayCCToken($this);
                    $savedCC = $this->ccToken->getSavedCC($params['cart']->id_customer);

                    $this->context->smarty->assign(
                        [
                            'HiPay_module_dir' => $this->_path,
                            'HiPay_this_path_ssl' => Tools::getShopDomainSsl(true, true).
                            __PS_BASE_URI__.
                            'modules/'.
                            $this->name.
                            '/',
                            'HiPay_savedCC' => $savedCC,
                            'HiPay_activatedCreditCard' => array_keys($paymentProduct['products']),
                            'HiPay_confHipay' => $this->hipayConfigTool->getConfigHipay(),
                            'HiPay_is_guest' => $this->customer->is_guest,
                            'HiPay_customerFirstName' => $this->customer->firstname,
                            'HiPay_customerLastName' => $this->customer->lastname,
                            'HiPay_languageIsoCode' => $this->context->language->iso_code,
                        ]
                    );

                    $newOption->setModuleName('credit_card');
                    break;
            }

            $paymentForm = $this->fetch(
                'module:'.$this->name."/views/templates/front/payment/ps17/paymentForm-$uxMode-17.tpl"
            );

            $newOption->setCallToActionText($this->l('Pay by').' '.$displayName)
                ->setAction($this->context->link->getModuleLink($this->name, 'redirect', [], true))
                ->setForm($paymentForm);

            $paymentOptions[] = $newOption;
        }
    }

    /**
     * @return bool
     */
    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);
        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * add JS to the bottom of the page.
     */
    public function hipayActionFrontControllerSetMedia()
    {
        $uxMode = $this->hipayConfigTool->getPaymentGlobal()['operating_mode']['UXMode'];

        // Only on order page
        if ('order' === $this->context->controller->php_self) {
            $this->context->controller->addCSS(_MODULE_DIR_.$this->name.'/views/css/card-js.min.css', 'all');
            $this->context->controller->addCSS(_MODULE_DIR_.$this->name.'/views/css/hipay-enterprise.css', 'all');

            switch ($uxMode) {
                case UXMode::DIRECT_POST:
                    $this->context->controller->registerJavascript(
                        'card-js',
                        'modules/'.$this->name.'/views/js/card-js.min.js'
                    );
                    $this->context->controller->registerJavascript(
                        'card-tokenize',
                        'modules/'.$this->name.'/views/js/card-tokenize.js'
                    );
                    break;
                case UXMode::HOSTED_FIELDS:
                    $this->context->controller->registerJavascript(
                        'hosted-fields',
                        'modules/'.$this->name.'/views/js/hosted-fields.js'
                    );
                    break;
            }

            $this->context->controller->registerJavascript(
                'strings',
                'modules/'.$this->name.'/views/js/strings.js'
            );

            $this->context->controller->registerJavascript(
                'input-form-control',
                'modules/'.$this->name.'/views/js/form-input-control.js',
                ['position' => 'head']
            );
            $this->context->controller->registerJavascript(
                'device-fingerprint',
                'modules/'.$this->name.'/views/js/devicefingerprint.js'
            );
            $this->context->controller->registerJavascript(
                'cc-functions',
                'modules/'.$this->name.'/views/js/cc.functions.js'
            );
            $this->context->controller->registerJavascript(
                'hipay-sdk-js',
                $this->hipayConfigTool->getPaymentGlobal()['sdk_js_url'],
                ['server' => 'remote', 'position' => 'bottom', 'priority' => 20]
            );
        } elseif ("module-hipay_enterprise-pending" === $this->context->controller->page_name) {
            $this->context->controller->addCSS(_MODULE_DIR_.$this->name.'/views/css/hipay-enterprise.css', 'all');
            $this->context->controller->registerJavascript(
                'hipay-sdk-js',
                $this->hipayConfigTool->getPaymentGlobal()['sdk_js_url'],
                ['server' => 'remote', 'position' => 'top', 'priority' => 1]
            );
        }
    }
}
