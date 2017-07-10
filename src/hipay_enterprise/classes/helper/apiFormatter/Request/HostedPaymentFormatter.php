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

require_once(dirname(__FILE__) . '/RequestFormatterAbstract.php');
require_once(dirname(__FILE__) . '/../../../../lib/vendor/autoload.php');

class HostedPaymentFormatter extends RequestFormatterAbstract
{
    public function __construct(
        $moduleInstance,
        $params
    ) {
        parent::__construct(
            $moduleInstance,
            $params
        );
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

        $order->template = ($this->configHipay["payment"]["global"]["operating_mode"] !== "iframe") ? $this->configHipay["payment"]["global"]["iframe_hosted_page_template"] : "iframe-js";
        $order->css = $this->configHipay["payment"]["global"]["css_url"];
        $order->display_selector = $this->configHipay["payment"]["global"]["display_card_selector"];
        $order->payment_product_list = $this->productList;
        $order->payment_product_category_list = '';
    }
}
