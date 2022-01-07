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

require_once(dirname(__FILE__) . '/../ApiFormatterAbstract.php');
require_once(dirname(__FILE__) . '/../../../lib/vendor/autoload.php');

/**
 *
 * Common request formatter
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
abstract class CommonRequestFormatterAbstract extends ApiFormatterAbstract
{

    public function __construct($module, $cart = false)
    {
        parent::__construct($module, $cart);

        spl_autoload_register(array($this, 'autoloadCustomData'));
    }

    /**
     * map prestashop order informations to request fields (shared information between Hpayment, Iframe, Direct Post and Maintenance )
     * @param type $order
     */
    protected function mapRequest(&$request)
    {
        $source = array(
            "source" => "CMS",
            "brand" => "prestashop",
            "brand_version" => _PS_VERSION_,
            "integration_version" => $this->module->version
        );

        $this->module->getLogs()->logInfos('# Process Custom Request source');
        $request->source = Tools::jsonEncode($source);
    }

    /**
     * @param $request
     * @param $cart
     * @param $params
     */
    protected function setCustomData(&$request, $cart, $params)
    {
        $this->module->getLogs()->logInfos('# Process Custom Data Hipay ');
        // getSummaryDetails Needs customer in context since 1.7.4.2
        $customer = new Customer($cart->id_customer);
        Context::getContext()->customer = $customer;
        $cartSummary = $cart->getSummaryDetails();

        $group = new Group($customer->id_default_group);
        $iframe = ($this->configHipay["payment"]["global"]["operating_mode"] === "iframe") ? 1 : 0;

        $paymentCode = "hipay_hosted";
        $captureType = $this->configHipay["payment"]["global"]["capture_mode"];

        if (isset($this->params["method"]) && $this->params["method"] !== "credit_card") {
            $paymentProduct = $this->module->hipayConfigTool->getPaymentProduct($this->params["method"]);
            $paymentCode = $this->params["method"];
            //if method doesn't allow capture, capture is automatic
            if (!$paymentProduct["canManualCapture"] && !$paymentProduct["canManualCapturePartially"]) {
                $captureType = "automatic";
            }
        }

        $shippingDescription = $cartSummary["carrier"]->name ?: "";
        $customDataHipay = array(
            "shipping_description" => $shippingDescription,
            "customer_code" => array_shift(
                $group->name
            ),
            "payment_code" => $paymentCode,
            "display_iframe" => $iframe,
            "captureType" => $captureType,
        );

        // Handling one-click data
        if (isset($this->params["multi_use"]) && $this->params["multi_use"]) {
            $customDataHipay["multi_use"] = true;
        }

        // Add custom data for transaction request
        if (file_exists(dirname(__FILE__) . '/../../helper/HipayEnterpriseHelperCustomData.php')) {
            if (class_exists('HipayEnterpriseHelperCustomData', true)) {
                $this->module->getLogs()->logInfos(
                    '## Process Custom Data from Custom Files : HipayEnterpriseHelperCustomData'
                );
                $customDataHelper = new HipayEnterpriseHelperCustomData();
                if (method_exists($customDataHelper, 'getCustomData')) {
                    $customData = $customDataHelper->getCustomData($cart, $params);
                    if (is_array($customData)) {
                        $customDataHipay = array_merge($customData, $customDataHipay);
                    }
                }
            }
        }
        $request->custom_data = Tools::jsonEncode($customDataHipay);
    }

    /**
     * AutoloadCustomData
     *
     * @param $class_name
     */
    public function autoloadCustomData($class_name)
    {
        //PS 1.6 fix
        if (in_array($class_name, array('HipayEnterpriseHelperCustomData'))) {
            require_once(dirname(__FILE__) . '/../../helper/' . $class_name . '.php');
        }
    }
}
