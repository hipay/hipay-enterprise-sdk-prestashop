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
class PreviousAuthInfoFormatter extends ApiFormatterAbstract
{

    public function __construct($module, $cart = false)
    {
        parent::__construct($module, $cart);
    }

    /**
     * @return \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\PreviousAuthInfo
     */
    public function generate()
    {
        $previousAuthInfo = new \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\PreviousAuthInfo();

        $this->mapRequest($previousAuthInfo);

        return $previousAuthInfo;
    }

    /**
     * @param \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\PreviousAuthInfo $previousAuthInfo
     */
    protected function mapRequest(&$previousAuthInfo)
    {
        if (!$this->customer->is_guest) {
            $lastOrder = $this->threeDSDB->getLastOrder($this->customer->id);
            if($lastOrder){
                $previousAuthInfo->transaction_reference = $this->threeDSDB->getTransactionReference($lastOrder);
            }
        }
    }
}
