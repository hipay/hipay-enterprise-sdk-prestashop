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

require_once(dirname(__FILE__) . '/../../classes/apiHandler/ApiHandler.php');
require_once(dirname(__FILE__) . '/../../lib/vendor/autoload.php');
require_once(dirname(__FILE__) . '/../../classes/helper/HipayFormControl.php');
require_once(dirname(__FILE__) . '/../../classes/helper/enums/ApiMode.php');

/**
 * Class Hipay_enterpriseRedirectlocalModuleFrontController
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class Hipay_enterpriseRedirectlocalModuleFrontController extends ModuleFrontController
{

    /**
     * display payment form API/Iframe/HostedPage(PS16)
     *
     * @throws PrestaShopException
     */
    public function initContent()
    {
        $this->display_column_left = false;
        $this->display_column_right = false;
        parent::initContent();

        $context = Context::getContext();
        $cart = $context->cart;

        $this->apiHandler = new ApiHandler($this->module, $this->context);

        if ($cart->id == null) {
            Tools::redirect('index.php?controller=order');
        }

        $params = array("productlist" => Tools::getValue("method"), "iframe" => true, "deviceFingerprint" => null);

        if (!$params) {
            Tools::redirect('index.php?controller=order');
        }

        $method = Tools::getValue("method");

        $params["deviceFingerprint"] = Tools::getValue('ioBB');
        $params["method"] = $method;

        $params["authentication_indicator"] = 0;

        $context->smarty->assign(
            array(
                'HiPay_nbProducts' => $cart->nbProducts(),
                'HiPay_cust_currency' => $cart->id_currency,
                'HiPay_currencies' => $this->module->getCurrency((int)$cart->id_currency),
                'HiPay_total' => $cart->getOrderTotal(true, Cart::BOTH),
                'HiPay_this_path' => $this->module->getPathUri(),
                'HiPay_this_path_bw' => $this->module->getPathUri(),
                'HiPay_this_path_ssl' => Tools::getShopDomainSsl(true, true) .
                    __PS_BASE_URI__ .
                    'modules/' .
                    $this->module->name .
                    '/',
                'hipay_enterprise_tpl_dir' => _PS_MODULE_DIR_ . $this->module->name . '/views/templates',
                'HiPay_action' => $this->context->link->getModuleLink(
                    $this->module->name,
                    'redirectlocal',
                    array("method" => $method),
                    true
                )
            )
        );

        $mode = ApiMode::DIRECT_POST;

        if ((
                isset($this->module->hipayConfigTool->getLocalPayment()[$method]["forceHpayment"])
                && $this->module->hipayConfigTool->getLocalPayment()[$method]["forceHpayment"]
            )
            || (
                isset($this->module->hipayConfigTool->getLocalPayment()[$method]["handleHpayment"])
                && $this->module->hipayConfigTool->getLocalPayment()[$method]["handleHpayment"]
                && $this->module->hipayConfigTool->getPaymentGlobal()['operating_mode']['APIMode'] === ApiMode::HOSTED_PAGE
            )
            || ($this->module::isPaypalV2($this->module->hipayConfigTool))
        ) {
            $mode = ApiMode::HOSTED_PAGE;
        }

        switch ($mode) {
            case ApiMode::HOSTED_PAGE:
                if (isset($this->module->hipayConfigTool->getLocalPayment()[$method]['iframe'])
                    && $this->module->hipayConfigTool->getLocalPayment()[$method]['iframe']
                ) {
                    $context->smarty->assign(
                        array(
                            'HiPay_url' => $this->apiHandler->handleLocalPayment(ApiMode::HOSTED_PAGE_IFRAME, $params)
                        )
                    );
                    $path = (_PS_VERSION_ >= '1.7' ? 'module:' .
                            $this->module->name .
                            '/views/templates/front/payment/ps17/paymentFormIframe-17'
                            : 'payment/ps16/paymentFormIframe-16') . '.tpl';
                } else {
                    $this->apiHandler->handleLocalPayment(ApiMode::HOSTED_PAGE, $params);
                }
                break;
            case ApiMode::DIRECT_POST:
                if (Tools::isSubmit("localSubmit")) {
                    foreach (Tools::getAllValues() as $name => $value) {
                        if (strpos($name, 'HF-') === 0) {
                            $params[substr($name, 3)] = $value;
                        }
                    }

                    $path = $this->apiHandler->handleLocalPayment(ApiMode::DIRECT_POST, $params);
                } else {
                    $formFields = [];
                    // Check if any additional fields dans be filed using values already filled by the client
                    if (isset($this->module->hipayConfigTool->getLocalPayment()[$method]["additionalFields"])
                        && isset($this->module->hipayConfigTool->getLocalPayment()[$method]["additionalFields"]["formFields"])
                    ) {
                        $formFields = $this->module->hipayConfigTool->getLocalPayment()[$method]["additionalFields"]["formFields"];
                        foreach ($formFields as $fieldName => $field) {
                            switch ($fieldName) {
                                case 'phone':
                                    $cart = $this->context->cart;
                                    $idAddress = $cart->id_address_invoice ? $cart->id_address_invoice : $cart->id_address_delivery;
                                    $address = new Address((int)$idAddress);

                                    $phone = $address->phone_mobile ? $address->phone_mobile : $address->phone;
                                    $formFields[$fieldName]['defaultValue'] = $phone;

                                    break;
                            }
                        }
                    }

                    // display form
                    $context->smarty->assign(
                        array(
                            'HiPay_status_error' => '200', // Force to ok for first call
                            'HiPay_cart_id' => $cart->id,
                            'HiPay_amount' => $cart->getOrderTotal(true, Cart::BOTH),
                            'HiPay_confHipay' => $this->module->hipayConfigTool->getConfigHipay(),
                            'HiPay_methodName' => $this->module->hipayConfigTool->getLocalPayment()[$method]["displayName"],
                            'HiPay_localPaymentName' => $method,
                            'HiPay_language' => $context->language->iso_code,
                            'HiPay_methodFields' => $formFields,
                            'HiPay_forceHpayment' => false
                        )
                    );
                    $path = (_PS_VERSION_ >= '1.7' ? 'module:' .
                            $this->module->name .
                            '/views/templates/front/payment/ps17/paymentLocalForm-17'
                            : 'payment/ps16/paymentLocalForm-16') . '.tpl';
                }
                break;
            default:
                break;
        }

        return $this->setTemplate($path);
    }

    /**
     * add JS and CSS in page
     */
    public function setMedia()
    {
        parent::setMedia();
        $this->addJS(array(_MODULE_DIR_ . 'hipay_enterprise/views/js/devicefingerprint.js'));
        $this->addJS(array(_MODULE_DIR_ . 'hipay_enterprise/views/js/form-input-control.js'));
        $this->addCSS(array(_MODULE_DIR_ . 'hipay_enterprise/views/css/hipay-enterprise.css'));
    }
}
