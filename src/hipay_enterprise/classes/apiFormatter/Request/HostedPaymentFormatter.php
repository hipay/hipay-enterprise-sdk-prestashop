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

require_once(dirname(__FILE__) . '/RequestFormatterAbstract.php');
require_once(dirname(__FILE__) . '/../../../lib/vendor/autoload.php');

/**
 *
 * Hosted payment request formatter
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class HostedPaymentFormatter extends RequestFormatterAbstract
{

    public function __construct($moduleInstance, $params, $cart = false)
    {
        parent::__construct($moduleInstance, $params, $cart);
        $this->iframe = $params["iframe"];
        $this->productList = $params["productlist"];
    }

    /**
     * generate request data before API call
     * @return \HiPay\Fullservice\Gateway\Request\Order\HostedPaymentPageRequest
     */
    public function generate()
    {
        $order = new \HiPay\Fullservice\Gateway\Request\Order\HostedPaymentPageRequest();

        $this->mapRequest($order);

        return $order;
    }

    /**
     * map prestashop order informations to request fields (Hpayment Post)
     * @param type $order
     */
    protected function mapRequest(&$order)
    {
        parent::mapRequest($order);

        if (!$this->moto) {
            $order->template = (!$this->iframe) ? $this->configHipay["payment"]["global"]["iframe_hosted_page_template"] : "iframe-js";
        } else {
            $order->template = "basic-js";
        }
        $order->css = $this->configHipay["payment"]["global"]["css_url"];
        $order->display_selector = $this->configHipay["payment"]["global"]["display_card_selector"];
        $order->payment_product_list = $this->productList;
        $order->payment_product_category_list = '';
        $order->multi_use = isset($this->params["multi_use"]) ? $this->params["multi_use"] : null;
        $order->display_cancel_button = $this->configHipay["payment"]["global"]["display_cancel_button"];
        if ((bool) $this->configHipay["payment"]["local_payment"]["paypal"]['activated']) {
            $buttonLabel = $this->configHipay["payment"]["local_payment"]["paypal"]['buttonLabel'] ?? null;
            $buttonShape = $this->configHipay["payment"]["local_payment"]["paypal"]['buttonShape'] ?? null;
            $buttonColor = $this->configHipay["payment"]["local_payment"]["paypal"]['buttonColor'] ?? null;

            $order->paypal_v2_label = is_array($buttonLabel) ? ($buttonLabel[0] ?? null) : $buttonLabel;
            $order->paypal_v2_shape = is_array($buttonShape) ? ($buttonShape[0] ?? null) : $buttonShape;
            $order->paypal_v2_color = is_array($buttonColor) ? ($buttonColor[0] ?? null) : $buttonColor;
            $order->paypal_v2_height = (int) $this->configHipay["payment"]["local_payment"]["paypal"]['buttonHeight'] ?? null;
            $order->paypal_v2_bnpl = (int) $this->configHipay["payment"]["local_payment"]["paypal"]['bnpl'] ?? null;
        }
    }
}