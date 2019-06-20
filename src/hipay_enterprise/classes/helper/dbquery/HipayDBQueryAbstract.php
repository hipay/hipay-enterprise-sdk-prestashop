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

/**
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
abstract class HipayDBQueryAbstract
{

    const HIPAY_CAT_MAPPING_TABLE = 'hipay_cat_mapping';
    const HIPAY_CARRIER_MAPPING_TABLE = 'hipay_carrier_mapping';
    const HIPAY_ORDER_REFUND_CAPTURE_TABLE = 'hipay_order_refund_capture';
    const HIPAY_CC_TOKEN_TABLE = 'hipay_cc_token';
    const HIPAY_TRANSACTION_TABLE = 'hipay_transaction';
    const HIPAY_ORDER_CAPTURE_TYPE_TABLE = 'hipay_order_capture_type';
    const HIPAY_PAYMENT_ORDER_PREFIX = 'HiPay Enterprise';

    public function __construct($moduleInstance)
    {
        $this->module = $moduleInstance;
        $this->logs = $this->module->getLogs();
    }
}
