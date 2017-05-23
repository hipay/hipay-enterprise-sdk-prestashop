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
require_once(dirname(__FILE__) . '/../PaymentMethod/CardTokenFormatter.php');
require_once(dirname(__FILE__) . '/../../../../lib/vendor/autoload.php');

class DirectPostFormatter extends RequestFormatterAbstract {

    private $cardToken;
    private $cardBrand;
    private $deviceFingerprint;

    public function __construct($moduleInstance, $params) {
        parent::__construct($moduleInstance);
        $this->cardToken = $params["card-token"];
        $this->cardBrand = $params["card-brand"];
        $this->deviceFingerprint = $params["deviceFingerprint"];
    }

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
        $order->payment_product = $this->cardBrand;
        $order->device_fingerprint = $this->deviceFingerprint;
        $order->paymentMethod = $this->getPaymentMethod();
    }

    private function getPaymentMethod() {

        var_dump($this->cardToken);
        $paymentMethod = new CardTokenFormatter($this->module, $this->cardToken);

        return $paymentMethod->generate();
    }

}
