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
require_once(dirname(__FILE__).'/classes/helper/apiCaller/ApiCaller.php');
require_once(dirname(__FILE__).'/classes/helper/tools/hipayCCToken.php');
require_once(dirname(__FILE__).'/translations/HipayStrings.php');

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

class HipayEnterpriseNew extends Hipay_enterprise
{

    /**
     * Display new payment options
     * @param type $params
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
     * Add CSS and JS in header
     * @param type $params
     */
    public function hookDisplayHeader($params)
    {
        $this->context->controller->addCSS(
            _MODULE_DIR_.$this->name.'/views/css/card-js.min.css',
            'all'
        );
        $this->context->controller->addCSS(
            _MODULE_DIR_.$this->name.'/views/css/hipay-enterprise.css',
            'all'
        );
        $this->context->controller->addJS(
            _MODULE_DIR_.$this->name.'/views/js/card-js.min.js',
            'all'
        );
        $this->context->controller->addJS(array(_MODULE_DIR_.$this->name.'/views/js/devicefingerprint.js'));
        $this->context->controller->addJS(
            array(_MODULE_DIR_.$this->name.'/lib/bower_components/hipay-fullservice-sdk-js/dist/hipay-fullservice-sdk.min.js')
        );
    }

    /**
     * Display payment forms
     * @param type $params
     * @return PaymentOption
     */
    public function hipayExternalPaymentOption($params)
    {
        $address  = new Address((int) $params['cart']->id_address_delivery);
        $country  = new Country((int) $address->id_country);
        $currency = new Currency((int) $params['cart']->id_currency);
        $customer = new Customer((int) $params['cart']->id_customer);

        // get activated card for customer currency and country
        $activatedCreditCard = $this->getActivatedPaymentByCountryAndCurrency(
            "credit_card",
            $country,
            $currency
        );

        $paymentOptions = array();
        try {

            if (!empty($activatedCreditCard)) {
                //displaying different forms depending of the operating mode chosen in the BO configuration
                switch ($this->hipayConfigTool->getConfigHipay()["payment"]["global"]["operating_mode"]) {
                    case "hosted_page":
                        $newOption        = new PaymentOption();
                        $newOption->setCallToActionText($this->l("Pay by")." ".$this->hipayConfigTool->getConfigHipay()["payment"]["global"]["ccDisplayName"])
                            ->setAction(
                                $this->context->link->getModuleLink(
                                    $this->name,
                                    'redirect',
                                    array(),
                                    true
                                )
                            );
                        if ($this->hipayConfigTool->getConfigHipay()["payment"]["global"]["display_hosted_page"] == "redirect") {
                            $newOption->setAdditionalInformation("<p>".$this->l('You will be redirected to an external payment page. Please do not refresh the page during the process')."</p>");
                        }
                        $paymentOptions[] = $newOption;
                        break;
                    case "api":
                        // set credit card for one click
                        $this->ccToken    = new HipayCCToken($this);
                        $savedCC          = $this->ccToken->getSavedCC($params['cart']->id_customer);

                        $this->context->smarty->assign(
                            array(
                                'module_dir' => $this->_path,
                                'this_path_ssl' => Tools::getShopDomainSsl(
                                        true,
                                        true
                                    ).__PS_BASE_URI__.'modules/'.$this->name.'/',
                                'savedCC' => $savedCC,
                                'activatedCreditCard' => Tools::jsonEncode(array_keys($activatedCreditCard)),
                                'confHipay' => $this->hipayConfigTool->getConfigHipay(),
                                'hipay_enterprise_tpl_dir' => _PS_MODULE_DIR_.$this->name.'/views/templates/hook',
                                'is_guest' => $customer->is_guest,
                                'action' => $this->context->link->getModuleLink(
                                    $this->name,
                                    'redirect',
                                    array(),
                                    true
                                ),
                            )
                        );

                        $paymentForm = $this->fetch('module:'.$this->name.'/views/templates/hook/paymentForm17.tpl');
                        $newOption   = new PaymentOption();
                        $newOption->setCallToActionText($this->l("Pay by")." ".$this->hipayConfigTool->getConfigHipay()["payment"]["global"]["ccDisplayName"])
                            ->setAdditionalInformation("<p>".$this->l('Please prepare your credit card')."</p>")
                            ->setModuleName("credit_card")
                            ->setForm($paymentForm);

                        $paymentOptions[] = $newOption;

                        break;
                    default:
                        break;
                }
            }


            // get activated local payment for customer currency and country
            $activatedLocalPayment = $this->getActivatedPaymentByCountryAndCurrency(
                "local_payment",
                $country,
                $currency,
                $params['cart']->getOrderTotal()
            );

            if (!empty($activatedLocalPayment)) {
                $this->context->smarty->assign(
                    array(
                        'module_dir' => $this->_path,
                        'confHipay' => $this->hipayConfigTool->getConfigHipay(),
                        'hipay_enterprise_tpl_dir' => _PS_MODULE_DIR_.$this->name.'/views/templates',
                        'methodFields' => array()
                    )
                );

                $i = 0;
                foreach ($activatedLocalPayment as $name => $localpayment) {
                    $newOption = new PaymentOption();

                    $this->context->smarty->assign(
                        array(
                            'action' => $this->context->link->getModuleLink(
                                $this->name,
                                'redirectlocal',
                                array("method" => $name),
                                true
                            ),
                            'localPaymentName' => $name
                        )
                    );
                    if (empty($this->hipayConfigTool->getConfigHipay()["payment"]["local_payment"][$name]["additionalFields"])
                        || ($this->hipayConfigTool->getConfigHipay()["payment"]["global"]["operating_mode"]
                            !== 'api' || ($localpayment["forceHpaymentOnElectronicSignature"]
                                && $this->hipayConfigTool->getConfigHipay()["payment"]["global"]["electronic_signature"]))
                    ) {
                        $this->context->smarty->assign(
                            array(
                                'methodFields' => array()
                            )
                        );
                    } else {
                        $this->context->smarty->assign(
                            array(
                                'methodFields' => $this->hipayConfigTool->getConfigHipay(
                                )["payment"]["local_payment"][$name]["additionalFields"]["formFields"]
                            )
                        );
                    }
                    $paymentForm = $this->fetch('module:'.$this->name.'/views/templates/front/paymentLocalForm17.tpl');

                    $newOption->setCallToActionText($this->l("pay by")." ".$localpayment["displayName"])
                        ->setAction(
                            $this->context->link->getModuleLink(
                                $this->name,
                                'redirectlocal',
                                array("method" => $name),
                                true
                            )
                        )
                        ->setModuleName('local_payment_hipay')
                        ->setForm($paymentForm);

                    // if no credit card, we force ioBB input to be displayed
                    if ($i == 0 && empty($activatedCreditCard)) {
                        $ioBB = '<input id="ioBB" type="hidden" name="ioBB">';
                        $newOption->setAdditionalInformation($ioBB);
                    }

                    $paymentOptions[] = $newOption;
                    $i++;
                }
            }

            return $paymentOptions;
        } catch (Exception $exc) {
            $this->logs->logErrors($exc->getTraceAsString());
            $this->logs->logErrors($exc->getMessage());
        }
    }

    /**
     *
     * @param type $cart
     * @return boolean
     */
    public function checkCurrency($cart)
    {
        $currency_order    = new Currency($cart->id_currency);
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
     * add JS to the bottom of the page
     * @param type $params
     */
    public function hipayActionFrontControllerSetMedia($params)
    {

        // Only on order page
        if ('order' === $this->context->controller->php_self) {
            $this->context->controller->registerJavascript(
                'card-tokenize',
                'modules/'.$this->name.'/views/js/card-tokenize.js'
            );
            $this->context->controller->registerJavascript(
                'input-form-control',
                'modules/'.$this->name.'/views/js/form-input-control.js',
                array('position' => 'head')
            );
            $this->context->controller->registerJavascript(
                'device-fingerprint',
                'modules/'.$this->name.'/views/js/devicefingerprint.js'
            );
        }
    }
}