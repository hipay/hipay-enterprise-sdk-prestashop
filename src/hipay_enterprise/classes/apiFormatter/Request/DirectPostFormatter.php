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
 * Direct post request formatter
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class DirectPostFormatter extends RequestFormatterAbstract
{
    private $paymentProduct;

    private $deviceFingerprint;

    private $paymentMethod;

    private $cardHolder;

    private $provider_data;

    public function __construct($moduleInstance, $params)
    {
        parent::__construct($moduleInstance, $params);
        $this->paymentProduct = $params["paymentProductCode"] ?? $params["productlist"];
        $this->deviceFingerprint = $params["deviceFingerprint"];
        $this->paymentMethod = $params["paymentmethod"];
        $this->provider_data = (isset($params["provider_data"])) ? $params["provider_data"] : '';
        $this->cardHolder = (isset($params["card_holder"])) ? $params["card_holder"] : '';
    }

    /**
     * generate request data before API call
     * @return \HiPay\Fullservice\Gateway\Request\Order\OrderRequest
     */
    public function generate()
    {
        $order = new \HiPay\Fullservice\Gateway\Request\Order\OrderRequest();

        $this->mapRequest($order);

        return $order;
    }

    /**
     * map prestashop order informations to request fields (Direct Post)
     * @param type $order
     */
    protected function mapRequest(&$order)
    {
        parent::mapRequest($order);
        $order->payment_product = $this->paymentProduct;
        $order->device_fingerprint = $this->deviceFingerprint;
        $order->paymentMethod = $this->paymentMethod;
        $order->provider_data = $this->provider_data;
        $this->getCustomerNames($order);
    }

    /**
     * Get correct Names for transaction, must be equivalent to the card holder(only for Amex)
     *
     * @param $order
     */
    public function getCustomerNames(&$order)
    {
        if ($this->paymentProduct === "american-express") {
            $names = explode(' ', trim($this->cardHolder));
            $order->firstname = $names[0];
            $order->lastname = trim(preg_replace('/' . $names[0] . '/', "", $this->cardHolder, 1));
        }
    }
}
