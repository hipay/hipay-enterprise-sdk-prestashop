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

require_once(dirname(__FILE__) . '/classes/apiCaller/ApiCaller.php');
require_once(dirname(__FILE__) . '/classes/helper/HipayCCToken.php');
require_once(dirname(__FILE__) . '/classes/helper/HipayHelper.php');
require_once(dirname(__FILE__) . '/classes/apiHandler/ApiHandler.php');

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

/**
 * HipayEnterpriseNew
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class HipayEnterpriseNew extends Hipay_enterprise
{
    private $customer;

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
        $this->context->controller->addCSS(_MODULE_DIR_ . $this->name . '/views/css/card-js.min.css', 'all');
        $this->context->controller->addCSS(_MODULE_DIR_ . $this->name . '/views/css/hipay-enterprise.css', 'all');
        $this->context->controller->addJS(array(_MODULE_DIR_ . $this->name . '/views/js/devicefingerprint.js'));
        $this->context->controller->addJS(
            array(
                _MODULE_DIR_ .
                $this->name .
                '/lib/bower_components/hipay-fullservice-sdk-js/dist/hipay-fullservice-sdk.min.js'
            )
        );
    }

    /**
     * Display payment forms (PS17)
     *
     * @param type $params
     * @return PaymentOption
     */
    public function hipayExternalPaymentOption($params)
    {
        try {
            $idAddress = $params['cart']->id_address_invoice ? $params['cart']->id_address_invoice : $params['cart']->id_address_delivery;
            $address = new Address((int)$idAddress);
            $country = new Country((int)$address->id_country);
            $currency = new Currency((int)$params['cart']->id_currency);
            $this->customer = new Customer((int)$params['cart']->id_customer);


            $paymentOptions = array();
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
                    array(
                        'module_dir' => $this->_path,
                        'confHipay' => $this->hipayConfigTool->getConfigHipay(),
                        'hipay_enterprise_tpl_dir' => _PS_MODULE_DIR_ . $this->name . '/views/templates',
                        'methodFields' => array()
                    )
                );
                $i = 0;
                foreach ($sortedPaymentProducts as $key => $paymentProduct) {
                    if ($key == "credit_card") {
                        $this->setCCPaymentOptions(
                            $paymentOptions,
                            $paymentProduct,
                            $params
                        );
                    } else {
                        $this->setLocalPaymentOptions(
                            $paymentOptions,
                            $key,
                            $paymentProduct,
                            isset($sortedPaymentProducts["credit_card"]) &&
                            empty($sortedPaymentProducts["credit_card"]["products"]),
                            $i
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
     * set local payment options
     * @param array $paymentOptions
     * @param type $name
     * @param type $paymentProduct
     * @param type $emptyCreditCard
     * @param type $i
     */
    private function setLocalPaymentOptions(&$paymentOptions, $name, $paymentProduct, $emptyCreditCard, &$i)
    {
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

        if (isset($paymentProduct["errorMsg"])) {
            $this->context->smarty->assign(
                array(
                    'errorMsg' => $paymentProduct["errorMsg"]
                )
            );
        }

        $mode = $this->hipayConfigTool->getPaymentGlobal()["operating_mode"];

        if (
            isset($this->hipayConfigTool->getLocalPayment()[$name]["forceApiOrder"]) &&
            $this->hipayConfigTool->getLocalPayment()[$name]["forceApiOrder"]
        ) {
            $mode = Apihandler::DIRECTPOST;
        }

        if (empty($this->hipayConfigTool->getLocalPayment()[$name]["additionalFields"]) ||
            $mode !== Apihandler::DIRECTPOST ||
            (isset($paymentProduct["forceHpayment"]) && $paymentProduct["forceHpayment"])
        ) {
            $this->context->smarty->assign(
                array(
                    'methodFields' => array()
                )
            );
        } else {
            $this->context->smarty->assign(
                array(
                    'methodFields' => $this->hipayConfigTool->getLocalPayment(
                    )[$name]["additionalFields"]["formFields"],
                    'language' => $this->context->language->language_code
                )
            );
        }
        $paymentForm = $this->fetch(
            'module:' . $this->name . '/views/templates/front/payment/ps17/paymentLocalForm-17.tpl'
        );

        if (isset(
            $paymentProduct["displayName"][$this->context->language->iso_code])
        ) {
            $displayName = $paymentProduct["displayName"][$this->context->language->iso_code];
        } else if (
            isset($paymentProduct["displayName"])
            && !is_array($paymentProduct["displayName"])
        ) {
            $displayName = $paymentProduct["displayName"];
        } else {
            $displayName = $paymentProduct["displayName"]['en'];
        }

        $newOption->setCallToActionText($this->l('Pay by') . " " . $displayName)
            ->setAction(
                $this->context->link->getModuleLink($this->name, 'redirectlocal', array("method" => $name), true)
            )
            ->setModuleName('local_payment_hipay')
            ->setForm($paymentForm);

        // if no credit card, we force ioBB input to be displayed
        if ($i == 0 && $emptyCreditCard) {
            $ioBB = '<input id="ioBB" type="hidden" name="ioBB">';
            $newOption->setAdditionalInformation($ioBB);
        }
        $i++;
        $paymentOptions[] = $newOption;
    }

    /**
     * set credit card payment option
     * @param type $paymentOptions
     * @param type $paymentProduct
     * @param type $params
     */
    private function setCCPaymentOptions(&$paymentOptions, $paymentProduct, $params)
    {
        if (!empty($paymentProduct["products"])) {
            if (isset(
                $this->hipayConfigTool->getPaymentGlobal()["ccDisplayName"][$this->context->language->iso_code])
            ) {
                $displayName = $this->hipayConfigTool->getPaymentGlobal()["ccDisplayName"][$this->context->language->iso_code];
            } else if (
                isset($this->hipayConfigTool->getPaymentGlobal()["ccDisplayName"])
                && !is_array($this->hipayConfigTool->getPaymentGlobal()["ccDisplayName"])
            ) {
                $displayName = $this->hipayConfigTool->getPaymentGlobal()["ccDisplayName"];
            } else {
                $displayName = $this->hipayConfigTool->getPaymentGlobal()["ccDisplayName"]['en'];
            }

            //displaying different forms depending of the operating mode chosen in the BO configuration
            switch ($this->hipayConfigTool->getPaymentGlobal()["operating_mode"]) {
                case "hosted_page":
                    $newOption = new PaymentOption();
                    $newOption->setCallToActionText(
                        $this->l('Pay by') . " " . $displayName
                    )->setAction($this->context->link->getModuleLink($this->name, 'redirect', array(), true));

                    if ($this->hipayConfigTool->getPaymentGlobal()["display_hosted_page"] == "redirect") {
                        $newOption->setAdditionalInformation("<p>" . $params['translation_checkout'] . "</p>");
                    }
                    $paymentOptions[] = $newOption;
                    break;
                case "api":
                    // set credit card for one click
                    $this->ccToken = new HipayCCToken($this);
                    $savedCC = $this->ccToken->getSavedCC($params['cart']->id_customer);

                    $this->context->smarty->assign(
                        array(
                            'module_dir' => $this->_path,
                            'this_path_ssl' => Tools::getShopDomainSsl(true, true) .
                                __PS_BASE_URI__ .
                                'modules/' .
                                $this->name .
                                '/',
                            'savedCC' => $savedCC,
                            'activatedCreditCard' => array_keys($paymentProduct["products"]),
                            'confHipay' => $this->hipayConfigTool->getConfigHipay(),
                            'is_guest' => $this->customer->is_guest,
                            'action' => $this->context->link->getModuleLink($this->name, 'redirect', array(), true),
                        )
                    );

                    $paymentForm = $this->fetch(
                        'module:' . $this->name . '/views/templates/front/payment/ps17/paymentForm-17.tpl'
                    );
                    $newOption = new PaymentOption();

                    $newOption->setCallToActionText(
                        $this->l('Pay by') . " " . $displayName
                    )
                        ->setAdditionalInformation("")
                        ->setModuleName("credit_card")
                        ->setForm($paymentForm);

                    $paymentOptions[] = $newOption;

                    break;
                default:
                    break;
            }
        }
    }

    /**
     *
     * @param type $cart
     * @return boolean
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
     * add JS to the bottom of the page
     * @param type $params
     */
    public function hipayActionFrontControllerSetMedia()
    {

        // Only on order page
        if ('order' === $this->context->controller->php_self) {
            $this->context->controller->registerJavascript(
                'strings',
                'modules/' . $this->name . '/views/js/strings.js'
            );
            $this->context->controller->registerJavascript(
                'card-js',
                'modules/' . $this->name . '/views/js/card-js.min.js'
            );
            $this->context->controller->registerJavascript(
                'card-tokenize',
                'modules/' . $this->name . '/views/js/card-tokenize.js'
            );
            $this->context->controller->registerJavascript(
                'input-form-control',
                'modules/' . $this->name . '/views/js/form-input-control.js',
                array('position' => 'head')
            );
            $this->context->controller->registerJavascript(
                'device-fingerprint',
                'modules/' . $this->name . '/views/js/devicefingerprint.js'
            );
        }
    }
}
