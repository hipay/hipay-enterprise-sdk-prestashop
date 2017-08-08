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

/**
 * Class GatewayException
 *
 *
 */

class GatewayException extends Exception
{
    /**
     * GatewayException constructor.
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     * @param $context
     * @param $moduleInstance
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null, $context, $moduleInstance )
    {
        parent::__construct($message, $code, $previous);
        HipayHelper::redirectToErrorPage($context,$moduleInstance);
    }


}