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
 * Class NotificationException
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class NotificationException extends Exception
{
    private $context;
    private $returnCode;

    private $moduleInstance;

    /**
     * GatewayException constructor.
     *
     * @param string $message
     * @param $context
     * @param $moduleInstance
     * @param string $returnCode
     */
    public function __construct($message, $context, $moduleInstance, $returnCode = "HTTP/1.0 500 Internal server error")
    {
        parent::__construct($message);

        $this->context = $context;
        $this->moduleInstance = $moduleInstance;
        $this->returnCode = $returnCode;
    }

    public function getReturnCode()
    {
        return $this->returnCode;
    }
}
