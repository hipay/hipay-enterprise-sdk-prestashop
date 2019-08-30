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
        if (isset($this->params['browser_info'])) {
            $browserInfo->ipaddr = Tools::getRemoteAddr();
            $browserInfo->http_accept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : null;
            $browserInfo->javascript_enabled = isset($this->params['browser_info']) && ($this->params['browser_info'] !== false);
            $browserInfo->java_enabled = isset($this->params['browser_info']->java_enabled) ? $this->params['browser_info']->java_enabled : null;
            $browserInfo->language = isset($this->params['browser_info']->language) ? $this->params['browser_info']->language : null;
            $browserInfo->color_depth = isset($this->params['browser_info']->color_depth) ? $this->params['browser_info']->color_depth : null;
            $browserInfo->screen_height = isset($this->params['browser_info']->screen_height) ? $this->params['browser_info']->screen_height : null;
            $browserInfo->screen_width = isset($this->params['browser_info']->screen_width) ? $this->params['browser_info']->screen_width : null;
            $browserInfo->timezone = isset($this->params['browser_info']->timezone) ? $this->params['browser_info']->timezone : null;
            $browserInfo->http_user_agent = isset($this->params['browser_info']->http_user_agent) ? $this->params['browser_info']->http_user_agent : null;
        }
    }
}
