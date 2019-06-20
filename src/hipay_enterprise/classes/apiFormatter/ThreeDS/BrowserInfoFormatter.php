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

require_once(dirname(__FILE__) . '/../../../lib/vendor/autoload.php');
require_once(dirname(__FILE__) . '/../ApiFormatterAbstract.php');

/**
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class BrowserInfoFormatter extends ApiFormatterAbstract
{
    private $params;

    public function __construct($module, $cart, $params)
    {
        parent::__construct($module, $cart);
        $this->params = $params;
    }

    /**
     * @return \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\BrowserInfo
     */
    public function generate()
    {
        $browserInfo = new \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\BrowserInfo();

        $this->mapRequest($browserInfo);

        return $browserInfo;
    }

    /**
     * @param \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\BrowserInfo $browserInfo
     */
    protected function mapRequest(&$browserInfo)
    {
        $browserInfo->ipaddr = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : null;
        $browserInfo->http_accept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : null;
        $browserInfo->javascript_enabled = true;

        //TODO: Get from params (information fill by SD JS)
        $browserInfo->color_depth = "";
        $browserInfo->device_fingerprint = "";
        $browserInfo->http_user_agent = "";
        $browserInfo->java_enabled = "";
        $browserInfo->language = "";
        $browserInfo->screen_height = "";
        $browserInfo->screen_width = "";
        $browserInfo->timezone = "";
    }
}
