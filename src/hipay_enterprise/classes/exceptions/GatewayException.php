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
 * Class GatewayException
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class GatewayException extends Exception
{
    private $context;

    private $moduleInstance;

    /**
     * GatewayException constructor.
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     * @param $context
     * @param $moduleInstance
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null, $context, $moduleInstance)
    {
        parent::__construct($message, $code, $previous);

        $this->context = $context;
        $this->moduleInstance = $moduleInstance;
    }

    public function handleException()
    {
        HipayHelper::redirectToExceptionPage($this->context, $this->moduleInstance);
    }
}
