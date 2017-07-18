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

require_once(dirname(__FILE__) . '/../../classes/helper/apiHandler/ApiHandler.php');
require_once(dirname(__FILE__) . '/../../classes/helper/tools/hipayHelper.php');
require_once(dirname(__FILE__) . '/../../classes/helper/tools/hipayCCToken.php');

class Hipay_enterpriseRedirectModuleFrontController extends ModuleFrontController
{

    /**
     * display payment form API/Iframe/HostedPage(PS16)
     * @return type
     */
    public function initContent()
    {
        $this->display_column_left = false;
        $this->display_column_right = false;
        parent::initContent();

        $context = Context::getContext();
        $cart = $context->cart;
        $this->apiHandler = new ApiHandler(
            $this->module,
            $this->context
        );
        $this->ccToken = new HipayCCToken($this->module);

        if ($cart->id == null) {
            Tools::redirect('index.php?controller=order');
        }
        $context->smarty->assign(
            array(
                'nbProducts' => $cart->nbProducts(),
                'cust_currency' => $cart->id_currency,
                'currencies' => $this->module->getCurrency((int)$cart->id_currency),
                'total' => $cart->getOrderTotal(
                    true,
                    Cart::BOTH
                ),
                'this_path' => $this->module->getPathUri(),
                'this_path_bw' => $this->module->getPathUri(),
                'this_path_ssl' => Tools::getShopDomainSsl(
                        true,
                        true
                    ) . __PS_BASE_URI__ . 'modules/' . $this->module->name . '/',
                'hipay_enterprise_tpl_dir' => _PS_MODULE_DIR_ . $this->module->name . '/views/templates/hook'
            )
        );

        $savedCC = $this->ccToken->getSavedCC($cart->id_customer);

        //displaying different forms depending of the operating mode chosen in the BO configuration
        switch ($this->module->hipayConfigTool->getConfigHipay()["payment"]["global"]["operating_mode"]) {
            case "hosted_page":
                $this->apiHandler->handleCreditCard(Apihandler::HOSTEDPAGE);
                break;
            case "api":
                // if form is sent
                if (Tools::getValue('card-token') && Tools::getValue('card-brand')
                    && Tools::getValue('card-pan')
                ) {
                    $path = $this->apiNewCC(
                        $cart,
                        $context,
                        $savedCC
                    );
                } elseif (Tools::getValue('ccTokenHipay')) {
                    $path = $this->apiSavedCC(
                        Tools::getValue('ccTokenHipay'),
                        $cart,
                        $savedCC,
                        $context
                    );
                } else {
                    $context->smarty->assign(
                        array(
                            'status_error' => '200', // Force to ok for first call
                            'status_error_oc' => '200',
                            'cart_id' => $cart->id,
                            'savedCC' => $savedCC,
                            'amount' => $cart->getOrderTotal(
                                true,
                                Cart::BOTH
                            ),
                            'confHipay' => $this->module->hipayConfigTool->getConfigHipay()
                        )
                    );
                    $path = 'paymentFormApi16.tpl';
                }
                break;
            case "iframe":
                $context->smarty->assign(
                    array(
                        'url' => $this->apiHandler->handleCreditCard(Apihandler::IFRAME)
                    )
                );
                $path = (_PS_VERSION_ >= '1.7' ? 'module:' . $this->module->name . '/views/templates/front/17'
                        : '16') . 'paymentFormIframe.tpl';
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
    private function apiSavedCC(
        $token,
        $cart,
        $savedCC,
        $context
    ) {
        if ($tokenDetails = $this->ccToken->getTokenDetails(
            $cart->id_customer,
            $token
        )
        ) {
            $params = array(
                "deviceFingerprint" => Tools::getValue('ioBB'),
                "productlist" => $tokenDetails['brand'],
                "cardtoken" => $tokenDetails['token'],
                "oneClick" => true,
                "method" => $tokenDetails['brand']
            );
            $this->apiHandler->handleCreditCard(
                Apihandler::DIRECTPOST,
                $params
            );
        } else {
            if (_PS_VERSION_ >= '1.7') {
                $redirectUrl = $context->link->getModuleLink(
                            $this->module->name,
                            'exception',
                            array('status_error' => 405),
                            true
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
                        true,
                        Cart::BOTH
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
    private function apiNewCC(
        $cart,
        $context,
        $savedCC
    ) {
        $delivery = new Address((int)$cart->id_address_delivery);
        $deliveryCountry = new Country((int)$delivery->id_country);
        $currency = new Currency((int)$cart->id_currency);
        $customer = new Customer((int)$cart->id_customer);

        $creditCard = $this->module->getActivatedPaymentByCountryAndCurrency(
            "credit_card",
            $deliveryCountry,
            $currency
        );
        $selectedCC = Tools::strtolower(
            str_replace(
                " ",
                "-",
                Tools::getValue('card-brand')
            )
        );
        if (in_array(
            $selectedCC,
            array_keys($creditCard)
        )) {
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
                "method" => $selectedCC
            );
            if (!$customer->is_guest && Tools::isSubmit('saveTokenHipay')) {
                $this->ccToken->saveCCToken(
                    $cart->id_customer,
                    $card
                );
            }
            $this->apiHandler->handleCreditCard(
                Apihandler::DIRECTPOST,
                $params
            );
        } else {
            if (_PS_VERSION_ >= '1.7') {
                $redirectUrl = $context->link->getModuleLink(
                            $this->module->name,
                            'exception',
                            array('status_error' => 404),
                            true
                        );
                Tools::redirect($redirectUrl);
            }
            $context->smarty->assign(
                array(
                    'status_error' => '404',
                    'status_error_oc' => '200',
                    'cart_id' => $cart->id,
                    'savedCC' => $savedCC,
                    'amount' => $cart->getOrderTotal(
                        true,
                        Cart::BOTH
                    ),
                    'confHipay' => $this->module->hipayConfigTool->getConfigHipay()
                )
            );
            return 'paymentFormApi16.tpl';
        }
    }

    /**
     * add JS and CSS in page
     */
    public function setMedia()
    {
        parent::setMedia();
        $this->addJS(array(_MODULE_DIR_ . 'hipay_enterprise/views/js/card-js.min.js'));
        $this->addJS(array(_MODULE_DIR_ . 'hipay_enterprise/views/js/devicefingerprint.js'));
        $this->addCSS(array(_MODULE_DIR_ . 'hipay_enterprise/views/css/card-js.min.css'));
        $this->addCSS(array(_MODULE_DIR_ . 'hipay_enterprise/views/css/hipay-enterprise.css'));
        $this->context->controller->addJS(
            array(_MODULE_DIR_ . 'hipay_enterprise/lib/bower_components/hipay-fullservice-sdk-js/dist/hipay-fullservice-sdk.min.js')
        );
        $this->addJS(array(_MODULE_DIR_.'hipay_enterprise/views/js/form-input-control.js'));
    }
}
