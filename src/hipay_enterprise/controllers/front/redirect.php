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
require_once(dirname(__FILE__).'/../../classes/apiHandler/ApiHandler.php');
require_once(dirname(__FILE__).'/../../classes/helper/HipayHelper.php');
require_once(dirname(__FILE__).'/../../classes/helper/HipayCCToken.php');
require_once(dirname(__FILE__).'/../../classes/helper/HipayConfig.php');

/**
 * Class Hipay_enterprisePendingModuleFrontController
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link 	https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class Hipay_enterpriseRedirectModuleFrontController extends ModuleFrontController
{

    /**
     * Display payment form API/Iframe/HostedPage(PS16)
     *
     * @return type
     */
    public function initContent()
    {
        $this->display_column_left  = false;
        $this->display_column_right = false;
        parent::initContent();

        $context          = Context::getContext();
        $cart             = $context->cart;
        $customer         = new Customer((int) $cart->id_customer);
        $this->apiHandler = new ApiHandler(
            $this->module, $this->context
        );

        if ($cart->id == null) {
            $this->module->getLogs()->logErrors("# Cart ID is null in initContent");
            Tools::redirect('index.php?controller=order');
        }
        $this->module->getLogs()->logInfos("# Redirect init CART ID".$context->cart->id);

        $this->ccToken = new HipayCCToken($this->module);
        $context->smarty->assign(
            array(
                'nbProducts' => $cart->nbProducts(),
                'cust_currency' => $cart->id_currency,
                'currencies' => $this->module->getCurrency((int) $cart->id_currency),
                'total' => $cart->getOrderTotal(true, Cart::BOTH),
                'this_path' => $this->module->getPathUri(),
                'this_path_bw' => $this->module->getPathUri(),
                'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/',
                'hipay_enterprise_tpl_dir' => _PS_MODULE_DIR_.$this->module->name.'/views/templates/hook'
            )
        );

        $savedCC = $this->ccToken->getSavedCC($cart->id_customer);

        //Displaying different forms depending of the operating mode chosen in the BO configuration
        switch ($this->module->hipayConfigTool->getConfigHipay()["payment"]["global"]["operating_mode"]) {
            case Apihandler::HOSTEDPAGE:
                if ($this->module->hipayConfigTool->getConfigHipay()["payment"]["global"]["display_hosted_page"] == "redirect") {
                    $this->apiHandler->handleCreditCard(Apihandler::HOSTEDPAGE,
                                                        array(
                        "method" => "credit_card",
                        "authentication_indicator" => $this->setAuthenticationIndicator($cart)
                        )
                    );
                } else {
                    $context->smarty->assign(
                        array(
                            'url' => $this->apiHandler->handleCreditCard(Apihandler::IFRAME,
                                                                         array(
                                "method" => "credit_card",
                                "authentication_indicator" => $this->setAuthenticationIndicator($cart)
                                )
                            )
                        )
                    );
                    $path = (_PS_VERSION_ >= '1.7' ? 'module:'.$this->module->name.'/views/templates/front/17' : '16').'paymentFormIframe.tpl';
                }
                break;
            case Apihandler::DIRECTPOST:
                // if form is sent
                if (Tools::getValue('card-token') && Tools::getValue('card-brand') && Tools::getValue('card-pan')
                ) {
                    $path = $this->apiNewCC(
                        $cart, $context, $savedCC
                    );
                } elseif (Tools::getValue('ccTokenHipay')) {
                    $path = $this->apiSavedCC(
                        Tools::getValue('ccTokenHipay'), $cart, $savedCC, $context
                    );
                } else {
                    $context->smarty->assign(
                        array(
                            'status_error' => '200', // Force to ok for first call
                            'status_error_oc' => '200',
                            'cart_id' => $cart->id,
                            'savedCC' => $savedCC,
                            'is_guest' => $customer->is_guest,
                            'amount' => $cart->getOrderTotal(
                                true, Cart::BOTH
                            ),
                            'confHipay' => $this->module->hipayConfigTool->getConfigHipay()
                        )
                    );
                    $path = 'paymentFormApi16.tpl';
                }
                break;
            default:
                break;
        }

        return $this->setTemplate($path);
    }

    /**
     * handle One click payment
     * @param type $token
     * @param type $cart
     * @param type $savedCC
     * @param type $context
     * @return string
     */
    private function apiSavedCC($token, $cart, $savedCC, $context)
    {
        if ($tokenDetails = $this->ccToken->getTokenDetails(
            $cart->id_customer, $token
            )
        ) {
            $params = array(
                "deviceFingerprint" => Tools::getValue('ioBB'),
                "productlist" => $tokenDetails['brand'],
                "cardtoken" => $tokenDetails['token'],
                "oneClick" => true,
                "method" => $tokenDetails['brand'],
                "authentication_indicator" => $this->setAuthenticationIndicator($cart)
            );
            $this->apiHandler->handleCreditCard(
                Apihandler::DIRECTPOST, $params
            );
        } else {
            if (_PS_VERSION_ >= '1.7') {
                $redirectUrl = $context->link->getModuleLink(
                    $this->module->name, 'exception', array('status_error' => 405), true
                );
                Tools::redirect($redirectUrl);
            }
            $context->smarty->assign(
                array(
                    'status_error' => '200',
                    'status_error_oc' => '400',
                    'cart_id' => $cart->id,
                    'savedCC' => $savedCC,
                    'amount' => $cart->getOrderTotal(
                        true, Cart::BOTH
                    ),
                    'confHipay' => $this->module->hipayConfigTool->getConfigHipay()
                )
            );
            return 'paymentFormApi16.tpl';
        }
    }

    /**
     * Handle Credit card payment (not one click)
     * @param type $cart
     * @param type $context
     * @param type $savedCC
     * @return string
     */
    private function apiNewCC($cart, $context, $savedCC)
    {
        $delivery        = new Address((int) $cart->id_address_delivery);
        $deliveryCountry = new Country((int) $delivery->id_country);
        $currency        = new Currency((int) $cart->id_currency);
        $customer        = new Customer((int) $cart->id_customer);

        $creditCard = HipayHelper::getActivatedPaymentByCountryAndCurrency(
                $this->module, $this->module->hipayConfigTool->getConfigHipay(), "credit_card", $deliveryCountry,
                $currency
        );

        $selectedCC = Tools::strtolower(
                str_replace(
                    " ", "-", Tools::getValue('card-brand')
                )
        );


        if (in_array($selectedCC, array_keys($creditCard))) {
            try {
                $card = array(
                    "token" => Tools::getValue('card-token'),
                    "brand" => $selectedCC,
                    "pan" => Tools::getValue('card-pan'),
                    "card_holder" => Tools::getValue('card-holder'),
                    "card_expiry_month" => Tools::getValue('card-expiry-month'),
                    "card_expiry_year" => Tools::getValue('card-expiry-year'),
                    "issuer" => Tools::getValue('card-issuer'),
                    "country" => Tools::getValue('card-country'),
                );

                $params = array(
                    "deviceFingerprint" => Tools::getValue('ioBB'),
                    "productlist" => $selectedCC,
                    "cardtoken" => Tools::getValue('card-token'),
                    "method" => $selectedCC,
                    "authentication_indicator" => $this->setAuthenticationIndicator($cart)
                );

                if (!$customer->is_guest && Tools::isSubmit('saveTokenHipay')) {
                    $configCC = $this->module->hipayConfigTool->getConfigHipay()["payment"]["credit_card"][$selectedCC];

                    if (isset($configCC['recurring']) && $configCC['recurring']) {
                        $this->ccToken->saveCCToken(
                            $cart->id_customer, $card
                        );
                    }
                }

                $this->apiHandler->handleCreditCard(
                    Apihandler::DIRECTPOST, $params
                );
            } catch (Exception $e) {
                $this->module->getLogs()->logException($e);
                return HipayHelper::redirectToErrorPage($context, $this->module, $cart, $savedCC);
            }
        } else {
            return HipayHelper::redirectToErrorPage($context, $this->module, $cart, $savedCC);
        }
    }

    /**
     * Add JS and CSS in checkout page
     */
    public function setMedia()
    {
        parent::setMedia();
        $this->addJS(array(_MODULE_DIR_.'hipay_enterprise/views/js/card-js.min.js'));
        $this->addJS(array(_MODULE_DIR_.'hipay_enterprise/views/js/devicefingerprint.js'));
        $this->addCSS(array(_MODULE_DIR_.'hipay_enterprise/views/css/card-js.min.css'));
        $this->addCSS(array(_MODULE_DIR_.'hipay_enterprise/views/css/hipay-enterprise.css'));
        $this->context->controller->addJS(
            array(_MODULE_DIR_.'hipay_enterprise/lib/bower_components/hipay-fullservice-sdk-js/dist/hipay-fullservice-sdk.min.js')
        );
        $this->addJS(array(_MODULE_DIR_.'hipay_enterprise/views/js/form-input-control.js'));
    }

    /**
     * set 3D-secure or not from configuration
     * @return int
     */
    private function setAuthenticationIndicator($cart)
    {
        switch ($this->module->hipayConfigTool->getConfigHipay()["payment"]["global"]["activate_3d_secure"]) {
            case HipayConfig::THREE_D_S_DISABLED:
                return 0;
            case HipayConfig::THREE_D_S_TRY_ENABLE_ALL:
                return 1;
            case HipayConfig::THREE_D_S_TRY_ENABLE_RULES:
                $cartSummary = $cart->getSummaryDetails();
                foreach ($this->module->hipayConfigTool->getConfigHipay()["payment"]["global"]["3d_secure_rules"] as $rule) {
                    if (isset($cartSummary[$rule["field"]]) && !$this->criteriaMet(
                            (int) $cartSummary[$rule["field"]], html_entity_decode($rule["operator"]),
                                                                                   (int) $rule["value"]
                        )
                    ) {
                        return 0;
                    }
                }
                return 1;
            case HipayConfig::THREE_D_S_FORCE_ENABLE_ALL:
                return 2;
            case HipayConfig::THREE_D_S_FORCE_ENABLE_RULES:
                $cartSummary = $cart->getSummaryDetails();

                foreach ($this->module->hipayConfigTool->getConfigHipay()["payment"]["global"]["3d_secure_rules"] as $rule) {
                    if (isset($cartSummary[$rule["field"]]) && !$this->criteriaMet(
                            (int) $cartSummary[$rule["field"]], html_entity_decode($rule["operator"]),
                                                                                   (int) $rule["value"]
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
     * Test 2 value with $operator
     * @param type $value1
     * @param type $operator
     * @param type $value2
     * @return boolean
     */
    private function criteriaMet(
    $value1, $operator, $value2
    )
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