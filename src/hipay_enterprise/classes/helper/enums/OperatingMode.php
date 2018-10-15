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

require_once(dirname(__FILE__) . '/ApiMode.php');
require_once(dirname(__FILE__) . '/UXMode.php');

/**
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class OperatingMode
{
    const DIRECT_POST = array('UXMode' => UXMode::DIRECT_POST, 'APIMode' => ApiMode::DIRECT_POST);

    const HOSTED_PAGE = array('UXMode' => UXMode::HOSTED_PAGE, 'APIMode' => ApiMode::HOSTED_PAGE);

    const HOSTED_FIELDS = array('UXMode' => UXMode::HOSTED_FIELDS, 'APIMode' => ApiMode::DIRECT_POST);

    public static function getOperatingMode($mode)
    {
        switch ($mode) {
            case UXMode::DIRECT_POST:
                return self::DIRECT_POST;
            case UXMode::HOSTED_PAGE:
                return self::HOSTED_PAGE;
            case UXMode::HOSTED_FIELDS:
                return self::HOSTED_FIELDS;
            default:
                return self::HOSTED_PAGE;
        }
    }
}
