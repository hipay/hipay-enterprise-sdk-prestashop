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

require_once(dirname(__FILE__) . '/CommonRequestFormatterAbstract.php');
require_once(dirname(__FILE__) . '/../../../lib/vendor/autoload.php');

/**
 *
 * Available payment product formatter
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class AvailablePaymentProductFormatter extends CommonRequestFormatterAbstract
{
    public function __construct($moduleInstance, $params, $cart = false)
    {
        parent::__construct($moduleInstance, $cart);
        $this->params = $params;
    }

    /**
     * generate request data before API call
     * @return \HiPay\Fullservice\Gateway\Request\Info\AvailablePaymentProductRequest
     */
    public function generate()
    {
        $paymentProduct = new \HiPay\Fullservice\Gateway\Request\Info\AvailablePaymentProductRequest();

        $this->mapRequest($paymentProduct);

        return $paymentProduct;
    }

    /**
     * map prestashop Payment Product to request fields (Available Payment Product)
     * @param type $paymentProduct
     */
    protected function mapRequest(&$paymentProduct)
    {
        parent::mapRequest($paymentProduct);
        $paymentProduct->payment_product = $this->params["payment_product"];
        $paymentProduct->with_options = $this->params["with_options"];
    }
}
