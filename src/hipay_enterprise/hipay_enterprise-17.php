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
require_once(dirname(__FILE__) . '/classes/helper/enums/UXMode.php');

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
     * set local payment options
     * @param array $paymentOptions
     * @param type $name
     * @param type $paymentProduct
     */
    private function setLocalPaymentOptions(&$paymentOptions, $name, $paymentProduct)
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


        if (empty($this->hipayConfigTool->getLocalPayment()[$name]["additionalFields"]) ||
            isset($paymentProduct["forceHpayment"]) && $paymentProduct["forceHpayment"]
        ) {
            $iframe = false;

            if (
                isset($this->hipayConfigTool->getLocalPayment()[$name]["iframe"])
                && $this->hipayConfigTool->getLocalPayment()[$name]["iframe"]
            ) {
                $iframe = true;
            }

            $this->context->smarty->assign(array('methodFields' => array(), 'iframe' => $iframe));
        } else {
            $this->context->smarty->assign(
                array(
                    'methodFields' => $this->hipayConfigTool->getLocalPayment()[$name]["additionalFields"]["formFields"],
                    'language' => $this->context->language->language_code
                )
            );
        }
        $paymentForm = $this->fetch(
            'module:' . $this->name . '/views/templates/front/payment/ps17/paymentLocalForm-17.tpl'
        );

        if (isset($paymentProduct["displayName"][$this->context->language->iso_code])) {
            $displayName = $paymentProduct["displayName"][$this->context->language->iso_code];
        } else {
            if (isset($paymentProduct["displayName"]) && !is_array($paymentProduct["displayName"])) {
                $displayName = $paymentProduct["displayName"];
            } else {
                $displayName = $paymentProduct["displayName"]['en'];
            }
        }

        $newOption->setCallToActionText($this->l('Pay by') . " " . $displayName)
            ->setAction(
                $this->context->link->getModuleLink($this->name, 'redirectlocal', array("method" => $name), true)
            )
            ->setModuleName('local_payment_hipay')
            ->setForm($paymentForm);

        // if no credit card, we force ioBB input to be displayed
        if (count($paymentOptions) == 0) {
            $ioBB = '<input id="ioBB" type="hidden" name="ioBB">';
            $newOption->setAdditionalInformation($ioBB);
        }
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
            } else {
                if (
                    isset($this->hipayConfigTool->getPaymentGlobal()["ccDisplayName"])
                    && !is_array($this->hipayConfigTool->getPaymentGlobal()["ccDisplayName"])
                ) {
                    $displayName = $this->hipayConfigTool->getPaymentGlobal()["ccDisplayName"];
                } else {
                    $displayName = $this->hipayConfigTool->getPaymentGlobal()["ccDisplayName"]['en'];
                }
            }

            //displaying different forms depending of the operating mode chosen in the BO configuration
            $uxMode = $this->hipayConfigTool->getPaymentGlobal()["operating_mode"]["UXMode"];
            $newOption = new PaymentOption();


            $this->context->smarty->assign(
                array('action' => $this->context->link->getModuleLink($this->name, 'redirect', array(), true))
            );

            switch ($uxMode) {
                case UXMode::DIRECT_POST:
                case UXMode::HOSTED_FIELDS:
                case UXMode::HOSTED_PAGE:
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
                            'customerFirstName' => $this->customer->firstname,
                            'customerLastName' => $this->customer->lastname
                        )
                    );

                    $newOption->setModuleName("credit_card");
                    break;
            }

            $paymentForm = $this->fetch(
                'module:' . $this->name . "/views/templates/front/payment/ps17/paymentForm-$uxMode-17.tpl"
            );

            $newOption->setCallToActionText($this->l('Pay by') . " " . $displayName)
                ->setAction($this->context->link->getModuleLink($this->name, 'redirect', array(), true))
                ->setForm($paymentForm);

            $paymentOptions[] = $newOption;
        }
    }

    /**
     * @param $cart
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
     * add JS to the bottom of the page
     */
    public function hipayActionFrontControllerSetMedia()
    {
        $uxMode = $this->hipayConfigTool->getPaymentGlobal()["operating_mode"]["UXMode"];

        // Only on order page
        if ('order' === $this->context->controller->php_self) {

            $this->context->controller->addCSS(_MODULE_DIR_ . $this->name . '/views/css/card-js.min.css', 'all');
            $this->context->controller->addCSS(_MODULE_DIR_ . $this->name . '/views/css/hipay-enterprise.css', 'all');

            switch ($uxMode) {
                case UXMode::DIRECT_POST:
                    $this->context->controller->registerJavascript(
                        'card-js',
                        'modules/' . $this->name . '/views/js/card-js.min.js'
                    );
                    $this->context->controller->registerJavascript(
                        'card-tokenize',
                        'modules/' . $this->name . '/views/js/card-tokenize.js'
                    );
                    break;
                case UXMode::HOSTED_FIELDS:
                    $this->context->controller->registerJavascript(
                        'hosted-fields',
                        'modules/' . $this->name . '/views/js/hosted-fields.js'
                    );
                    break;
            }

            $this->context->controller->registerJavascript(
                'strings',
                'modules/' . $this->name . '/views/js/strings.js'
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
            $this->context->controller->registerJavascript(
                'cc-functions',
                'modules/' . $this->name . '/views/js/cc.functions.js'
            );
            $this->context->controller->registerJavascript(
                'hipay-sdk-js',
                $this->hipayConfigTool->getPaymentGlobal()['sdk_js_url'],
                ['server' => 'remote', 'position' => 'bottom', 'priority' => 20]
            );
        }
    }
}
