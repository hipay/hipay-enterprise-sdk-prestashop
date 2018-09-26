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
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class OperatingMode
{

    const DIRECT_POST_UX = 'direct_post';

    const HOSTED_PAGE_UX = 'hosted_page';

    const HOSTED_FIELDS_UX = 'hosted_fields';

    const DIRECT_POST_API = 'direct_post';

    const HOSTED_PAGE_API = 'hosted';

    const HOSTED_PAGE_IFRAME = 'iframe';

    const DIRECT_POST = array('UXMode' => self::DIRECT_POST_UX, 'APIMode' => self::DIRECT_POST_API);

    const HOSTED_PAGE = array('UXMode' => self::HOSTED_PAGE_UX, 'APIMode' => self::HOSTED_PAGE_API);

    const HOSTED_FIELDS = array('UXMode' =>  self::HOSTED_FIELDS_UX, 'APIMode' => self::DIRECT_POST_API);

}
