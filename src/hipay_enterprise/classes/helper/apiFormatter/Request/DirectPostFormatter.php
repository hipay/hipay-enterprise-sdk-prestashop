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

class DirectPostFormatter extends RequestFormatterAbstract {

    /**
     * generate request data before API call
     * @return \HiPay\Fullservice\Gateway\Request\Order\OrderRequest
     */
    public function generate() {

        $order = new \HiPay\Fullservice\Gateway\Request\Order\OrderRequest();

        $this->mapRequest($order);

        return $order;
    }

    /**
     * map prestashop order informations to request fields (Direct Post)
     * @param type $order
     */
    protected function mapRequest(&$order) {
        parent::mapRequest($order);
        $order->payment_product = '';
        $order->device_fingerprint = '';
    }

}
