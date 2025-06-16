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
require_once dirname(__FILE__) . '/../../lib/vendor/autoload.php';
require_once dirname(__FILE__) . '/../../classes/apiHandler/ApiHandler.php';
require_once dirname(__FILE__) . '/../../classes/helper/HipayHelper.php';
require_once dirname(__FILE__) . '/../../classes/helper/HipayCCToken.php';
require_once dirname(__FILE__) . '/../../classes/helper/enums/ThreeDS.php';
require_once dirname(__FILE__) . '/../../classes/helper/enums/ApiMode.php';
require_once dirname(__FILE__) . '/../../classes/helper/enums/UXMode.php';
require_once dirname(__FILE__) . '/../../classes/helper/enums/CardPaymentProduct.php';

/**
 * Class Hipay_enterprisePendingModuleFrontController.
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 *
 * @see    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class Hipay_enterpriseRedirectModuleFrontController extends ModuleFrontController
{
    /** @var Hipay_entreprise */
    public $module;

    /** @var ApiHandler */
    private $apiHandler;
    /** @var HipayCCToken */
    private $ccToken;
    /** @var array<string,mixed> */
    private $creditCard;
    /** @var Context */
    protected $context;
    /** @var Customer */
    private $customer;
    /** @var array<string,mixed> */
    private $savedCC;
    /** @var Country */
    private $deliveryCountry;
    /** @var Cart */
    private $currentCart;

    /**
     * Init data.
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function init()
    {
        parent::init();

        $this->context = Context::getContext();
        $this->apiHandler = new ApiHandler($this->module, $this->context);
        $this->currentCart = $this->context->cart;
        $this->customer = new Customer((int) $this->currentCart->id_customer);
        $this->ccToken = new HipayCCToken($this->module);
        $this->savedCC = $this->ccToken->getSavedCC($this->currentCart->id_customer);
        $delivery = new Address((int) $this->currentCart->id_address_delivery);
        $this->deliveryCountry = new Country((int) $delivery->id_country);
        $currency = new Currency((int) $this->currentCart->id_currency);

        $this->creditCard = HipayHelper::getActivatedPaymentByCountryAndCurrency(
            $this->module,
            $this->module->hipayConfigTool->getConfigHipay(),
            'credit_card',
            $this->deliveryCountry,
            $currency
        );
    }

    /**
     * Process post from payment form.
     *
     * @throws PrestaShopException
     */
    public function postProcess()
    {
        $apiMode = $this->module->hipayConfigTool->getPaymentGlobal()['operating_mode']['APIMode'];
        $isApplePay = false;
        $isPayPalV2 = false;
        $isOneClick = false;
        // If it's an apple pay payment, force the api mode to direct post
        if ('true' === Tools::getValue('is-apple-pay')) {
            $apiMode = ApiMode::DIRECT_POST;
            $isApplePay = true;
        } elseif (!(empty(Tools::getValue('paypalOrderId')))) {
            $apiMode = ApiMode::DIRECT_POST;
            $isPayPalV2 = true;
        }

        if (Tools::getValue('one-click')) {
            $isOneClick = true;
        }

        switch ($apiMode) {
            case ApiMode::HOSTED_PAGE:
                if ('redirect' == $this->module->hipayConfigTool->getPaymentGlobal()['display_hosted_page']) {
                    $ccToken = Tools::getValue('ccTokenHipay', '');
                    if (
                        $this->module->hipayConfigTool->getPaymentGlobal()['card_token']
                        && ((_PS_VERSION_ > '1.7' && !empty($ccToken) && 'noToken' != $ccToken)
                            ||
                            (_PS_VERSION_ < '1.7' &&
                                (empty($ccToken) || (!empty($ccToken) && 'noToken' != $ccToken))))
                    ) {
                        $path = $this->apiSavedCC(
                            Tools::getValue('ccTokenHipay'),
                            $this->currentCart,
                            $this->savedCC,
                            $this->context
                        );

                        return $this->setTemplate($path);
                    } else {
                        $this->apiHandler->handleCreditCard(
                            ApiMode::HOSTED_PAGE,
                            [
                                'method' => CardPaymentProduct::HOSTED,
                                'authentication_indicator' => $this->setAuthenticationIndicator($this->currentCart),
                                'isApplePay' => $isApplePay,
                            ]
                        );
                    }
                }
                break;
            case ApiMode::DIRECT_POST:
                if ($isPayPalV2) {
                    $this->apiPayPalOrderId($this->currentCart, $this->context);
                } elseif (Tools::getValue('card-token')) {
                    $this->handleCC($this->currentCart, $this->context, $this->customer, $this->savedCC, $isApplePay, $isOneClick);
                }
        }
    }

    /**
     * Display payment form API/Iframe/HostedPage(PS16).
     *
     * @throws PrestaShopException
     */
    public function initContent()
    {
        parent::initContent();

        if (null == $this->currentCart->id) {
            $this->module->getLogs()->logErrors('# Cart ID is null in initContent');
            Tools::redirect('index.php?controller=order');
        }
        $this->module->getLogs()->logInfos(
            '# Redirect init context cart ID ' . $this->context->cart->id . ' - current cart ID ' . $this->currentCart->id
        );

        $this->context->smarty->assign(
            [
                'HiPay_nbProducts' => $this->currentCart->nbProducts(),
                'HiPay_cust_currency' => $this->currentCart->id_currency,
                'HiPay_activatedCreditCard' => json_encode(array_keys($this->creditCard)),
                'HiPay_currencies' => $this->module->getCurrency((int) $this->currentCart->id_currency),
                'HiPay_total' => $this->currentCart->getOrderTotal(true, Cart::BOTH),
                'HiPay_this_path' => $this->module->getPathUri(),
                'HiPay_this_path_bw' => $this->module->getPathUri(),
                'HiPay_this_path_ssl' => Tools::getShopDomainSsl(true, true) .
                    __PS_BASE_URI__ .
                    'modules/' .
                    $this->module->name .
                    '/',
                'hipay_enterprise_tpl_dir' => _PS_MODULE_DIR_ . $this->module->name . '/views/templates',
            ]
        );

        $uxMode = $this->module->hipayConfigTool->getPaymentGlobal()['operating_mode']['UXMode'];

        $path = (_PS_VERSION_ >= '1.7' ?
            'module:' . $this->module->name .
            '/views/templates/front/payment/ps17/paymentForm-' . $uxMode . '-17'
            : 'payment/ps16/paymentForm-' . $uxMode . '-16') . '.tpl';

        // Displaying different forms depending of the operating mode chosen in the BO configuration
        switch ($uxMode) {
            case UXMode::HOSTED_PAGE:
                if (
                    'redirect' !== $this->module->hipayConfigTool->getPaymentGlobal()['display_hosted_page']
                    && Tools::getValue('iframeCall')
                ) {
                    $this->module->getLogs()->logInfos(
                        '# UXMode: ' . $uxMode . ' (Iframe case) - Redirect to path ' . $path
                    );
                    $this->context->smarty->assign(
                        [
                            'HiPay_url' => $this->apiHandler->handleCreditCard(
                                ApiMode::HOSTED_PAGE_IFRAME,
                                [
                                    'method' => CardPaymentProduct::HOSTED,
                                    'authentication_indicator' => $this->setAuthenticationIndicator($this->currentCart),
                                ]
                            ),
                        ]
                    );
                    $path = (_PS_VERSION_ >= '1.7' ?
                        'module:' . $this->module->name .
                        '/views/templates/front/payment/ps17/paymentFormIframe-17'
                        : 'payment/ps16/paymentFormIframe-16') . '.tpl';
                } elseif ($this->module->hipayConfigTool->getPaymentGlobal()['card_token'] && _PS_VERSION_ < '1.7') {
                    $this->module->getLogs()->logInfos(
                        '# UXMode: ' . $uxMode . ' (PS_VERSION < 1.7) - Redirect to path ' . $path
                    );
                    $this->assignTemplate();
                    $path = 'payment/ps16/paymentForm-' . $uxMode . '-16.tpl';
                } else {
                    // Impossible case but necessary
                    $this->module->getLogs()->logInfos('# UXMode: ' . $uxMode . ' (Else case) - Redirect to path ' . $path);
                    $this->module->getLogs()->logInfos($this->currentCart);
                    $this->assignTemplate();
                    $this->context->smarty->assign(
                        [
                            'HiPay_action' => $this->context->link->getModuleLink(
                                $this->module->name,
                                'redirect',
                                [],
                                true
                            ),
                            'HiPay_languageIsoCode' => $this->context->language->iso_code,
                        ]
                    );
                }
                break;
            case UXMode::DIRECT_POST:
            case UXMode::HOSTED_FIELDS:
                $this->module->getLogs()->logInfos('# UXMode: ' . $uxMode . ' - Redirect to path ' . $path);
                $this->module->getLogs()->logInfos($this->currentCart);
                $this->assignTemplate();
                break;
            default:
                break;
        }

        return $this->setTemplate($path);
    }

    /**
     *  Assign Order template.
     */
    private function assignTemplate()
    {
        $this->context->smarty->assign(
            [
                'HiPay_status_error' => '200', // Force to ok for first call
                'HiPay_status_error_oc' => '200',
                'HiPay_cart_id' => $this->currentCart->id,
                'HiPay_savedCC' => $this->savedCC,
                'HiPay_is_guest' => $this->customer->is_guest,
                'HiPay_customerFirstName' => $this->customer->firstname,
                'HiPay_customerLastName' => $this->customer->lastname,
                'HiPay_amount' => $this->currentCart->getOrderTotal(true, Cart::BOTH),
                'HiPay_confHipay' => $this->module->hipayConfigTool->getConfigHipay(),
            ]
        );
    }

    /**
     * handle One click payment.
     *
     * @return string
     */
    private function apiSavedCC($token, $cart, $savedCC, $context)
    {
        if ($tokenDetails = $this->ccToken->getCCDetails($cart->id_customer, $token)) {
            $params = [
                'deviceFingerprint' => Tools::getValue('ioBB'),
                'productlist' => $tokenDetails['brand'],
                'cardtoken' => $tokenDetails['token'],
                'card_holder' => $tokenDetails['card_holder'],
                'card_pan' => $tokenDetails['pan'],
                'card_expiration_date' => '0' .
                    $tokenDetails['card_expiry_month'] .
                    '/' .
                    $tokenDetails['card_expiry_year'],
                'oneClick' => true,
                'method' => $tokenDetails['brand'],
                'authentication_indicator' => $this->setAuthenticationIndicator($cart),
                'browser_info' => json_decode(Tools::getValue('browserInfo')),
            ];
            $this->apiHandler->handleCreditCard(ApiMode::DIRECT_POST, $params);
        } else {
            if (_PS_VERSION_ >= '1.7') {
                $redirectUrl = $context->link->getModuleLink(
                    $this->module->name,
                    'exception',
                    ['status_error' => 405],
                    true
                );
                Tools::redirect($redirectUrl);
            }
            $context->smarty->assign(
                [
                    'HiPay_status_error' => '200',
                    'HiPay_status_error_oc' => '400',
                    'HiPay_cart_id' => $cart->id,
                    'HiPay_savedCC' => $savedCC,
                    'HiPay_amount' => $cart->getOrderTotal(true, Cart::BOTH),
                    'HiPay_confHipay' => $this->module->hipayConfigTool->getConfigHipay(),
                ]
            );

            return 'payment/ps16/paymentForm-' . UXMode::DIRECT_POST . '-16.tpl';
        }
    }

    /**
     * Handle Credit card payment
     *
     * @return string
     */
    private function handleCC($cart, $context, $customer, $savedCC, $isApplePay, $isOneClick)
    {
        $selectedCC = Tools::getValue('card-brand');
        $isCCSaveNeeded = (bool) Tools::getValue('multi-use');

        if (in_array($selectedCC, array_keys($this->creditCard))) {
            try {
                $params = [
                    'deviceFingerprint' => Tools::getValue('ioBB'),
                    'productlist' => $selectedCC,
                    'cardtoken' => Tools::getValue('card-token'),
                    'card_holder' => Tools::getValue('card-holder'),
                    'card_pan' => Tools::getValue('card-pan'),
                    'card_expiration_date' => Tools::getValue('card-expiry-month') .
                        '/' .
                        Tools::getValue('card-expiry-year'),
                    'method' => $selectedCC,
                    'authentication_indicator' => $this->setAuthenticationIndicator($cart),
                    'browser_info' => json_decode(Tools::getValue('browserInfo')),
                    'isApplePay' => $isApplePay,
                    'isOneClick' => $isOneClick,
                ];

                $ccToSave = [
                    'token' => Tools::getValue('card-token'),
                    'brand' => Tools::getValue('card-brand'),
                    'pan' => Tools::getValue('card-pan'),
                    'card_holder' => Tools::getValue('card-holder'),
                    'card_expiry_month' => Tools::getValue('card-expiry-month'),
                    'card_expiry_year' => Tools::getValue('card-expiry-year'),
                ];
                if ($isCCSaveNeeded) {
                    $this->ccToken->saveCC($customer->id, $ccToSave);
                }

                $this->apiHandler->handleCreditCard(ApiMode::DIRECT_POST, $params);
            } catch (Exception $e) {
                $this->module->getLogs()->logException($e);

                return HipayHelper::redirectToErrorPage($context, $this->module, $cart, $savedCC);
            }
        } else {
            return HipayHelper::redirectToErrorPage($context, $this->module, $cart, $savedCC);
        }
    }

    /**
     * Handle Paypal V2
     *
     * @param $cart
     * @param $context
     * @return string
     */
    private function apiPayPalOrderId($cart, $context)
    {
        $selectedCC = Tools::getValue('productlist');

        if (isset($selectedCC)) {
            try {
                $providerData = ['paypal_id' => Tools::getValue('paypalOrderId')];
                $params = [
                    'deviceFingerprint' => Tools::getValue('ioBB'),
                    'productlist' => $selectedCC,
                    'method' => $selectedCC,
                    // [PRES-1] Ensure amount is present and includes discountsAdd commentMore actions
                    'amount' => $cart->getOrderTotal(true, Cart::BOTH),
                    'browser_info' => json_decode(Tools::getValue('browserInfo')),
                    'provider_data' => (string) json_encode($providerData)
                ];
                $this->apiHandler->handleLocalPayment(ApiMode::DIRECT_POST, $params);
            } catch (Exception $e) {
                $this->module->getLogs()->logException($e);

                return HipayHelper::redirectToErrorPage($context, $this->module, $cart);
            }
        }

        return HipayHelper::redirectToErrorPage($context, $this->module, $cart);
    }

    /**
     * Add JS and CSS in checkout page.
     */
    public function setMedia()
    {
        parent::setMedia();

        $this->addJS([_MODULE_DIR_ . 'hipay_enterprise/views/js/cc.functions.js']);
        $this->addJS([_MODULE_DIR_ . 'hipay_enterprise/views/js/devicefingerprint.js']);
        $this->addCSS([_MODULE_DIR_ . 'hipay_enterprise/views/css/hipay-enterprise.css']);
        $this->context->controller->addJS(
            [
                $this->module->hipayConfigTool->getPaymentGlobal()['sdk_js_url'],
            ]
        );

        $uxMode = $this->module->hipayConfigTool->getPaymentGlobal()['operating_mode']['UXMode'];
        // Displaying different forms depending of the operating mode chosen in the BO configuration
        switch ($uxMode) {
            case UXMode::DIRECT_POST:
                $this->addJS([_MODULE_DIR_ . 'hipay_enterprise/views/js/strings.js']);
                $this->addJS([_MODULE_DIR_ . 'hipay_enterprise/views/js/card-js.min.js']);
                $this->addCSS([_MODULE_DIR_ . 'hipay_enterprise/views/css/card-js.min.css']);
                $this->addJS([_MODULE_DIR_ . 'hipay_enterprise/views/js/form-input-control.js']);
                break;
            case UXMode::HOSTED_FIELDS:
                $this->addJS([_MODULE_DIR_ . 'hipay_enterprise/views/js/hosted-fields.js']);
                break;
        }
    }

    /**
     * set 3D-secure or not from configuration.
     *
     * @return int
     */
    private function setAuthenticationIndicator($cart)
    {
        switch ($this->module->hipayConfigTool->getPaymentGlobal()['activate_3d_secure']) {
            case ThreeDS::THREE_D_S_DISABLED:
                return 0;
            case ThreeDS::THREE_D_S_TRY_ENABLE_ALL:
                return 1;
            case ThreeDS::THREE_D_S_TRY_ENABLE_RULES:
                $cartSummary = $cart->getSummaryDetails();
                foreach ($this->module->hipayConfigTool->getPaymentGlobal()['3d_secure_rules'] as $rule) {
                    if (
                        isset($cartSummary[$rule['field']]) &&
                        !$this->criteriaMet(
                            (int) $cartSummary[$rule['field']],
                            html_entity_decode($rule['operator']),
                            (int) $rule['value']
                        )
                    ) {
                        return 0;
                    }
                }

                return 1;
            case ThreeDS::THREE_D_S_FORCE_ENABLE_ALL:
                return 2;
            case ThreeDS::THREE_D_S_FORCE_ENABLE_RULES:
                $cartSummary = $cart->getSummaryDetails();

                foreach ($this->module->hipayConfigTool->getPaymentGlobal()['3d_secure_rules'] as $rule) {
                    if (
                        isset($cartSummary[$rule['field']]) &&
                        !$this->criteriaMet(
                            (int) $cartSummary[$rule['field']],
                            html_entity_decode($rule['operator']),
                            (int) $rule['value']
                        )
                    ) {
                        return 0;
                    }
                }

                return 2;
            default:
                return 0;
        }
    }

    /**
     * Test 2 value with $operator.
     *
     * @param type $value1
     * @param type $operator
     * @param type $value2
     *
     * @return bool
     */
    private function criteriaMet($value1, $operator, $value2)
    {
        switch ($operator) {
            case '<':
                return $value1 < $value2;
            case '<=':
                return $value1 <= $value2;
            case '>':
                return $value1 > $value2;
            case '>=':
                return $value1 >= $value2;
            case '==':
                return $value1 == $value2;
            case '!=':
                return $value1 != $value2;
            default:
                return false;
        }

        return false;
    }
}
