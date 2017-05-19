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
require_once(_PS_ROOT_DIR_ . _MODULE_DIR_ . 'hipay_enterprise/classes/helper/apiFormatter/Request/RequestFormatterAbstract.php');
require_once(_PS_ROOT_DIR_ . _MODULE_DIR_ . 'hipay_enterprise/lib/vendor/autoload.php');

class HostedPaymentFormatter extends RequestFormatterAbstract {

    /**
     * generate request data before API call
     * @return \HiPay\Fullservice\Gateway\Request\Order\HostedPaymentPageRequest
     */
    public function generate() {

        $order = new \HiPay\Fullservice\Gateway\Request\Order\HostedPaymentPageRequest();

        $this->mapRequest($order);
        return $order;
    }

    /**
     * map prestashop order informations to request fields (Hpayment Post)
     * @param type $order
     */
    protected function mapRequest(&$order) {
        parent::mapRequest($order);

        $order->template = $this->configHipay["payment"]["global"]["iframe_hosted_page_template"];
        $order->css = $this->configHipay["payment"]["global"]["css_url"];
        $order->display_selector = $this->configHipay["payment"]["global"]["display_card_selector"];
        $order->payment_product_list = $this->getProductList();
        $order->payment_product_category_list = '';
    }

    /**
     * return well formatted authorize payment methods 
     * @return string
     */
    private function getProductList() {
        $creditCard = $this->module->getActivatedPaymentByCountryAndCurrency("credit_card", $this->deliveryCountry, $this->currency);
        $localPayment = $this->module->getActivatedPaymentByCountryAndCurrency("local_payment", $this->deliveryCountry, $this->currency);

        $productList = array_merge(array_keys($creditCard), array_keys($localPayment));
        $productList = join(",", $productList);
        return $productList;
    }

}
