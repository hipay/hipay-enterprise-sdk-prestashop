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

require_once(dirname(__FILE__).'/../../../lib/vendor/autoload.php');
require_once(dirname(__FILE__).'/../ApiFormatterAbstract.php');
require_once(dirname(__FILE__).'/../../helper/hipayConfig.php');

use HiPay\Fullservice\Enum\Transaction\ECI;
use HiPay\Fullservice\Gateway\Request\PaymentMethod\CardTokenPaymentMethod;

/**
 *
 * card payment method request formatter
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link 	https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class CardTokenFormatter extends ApiFormatterAbstract
{
    private $cardToken;

    public function __construct(
    $module, $params
    )
    {
        parent::__construct($module);
        $this->cardToken = $params['cardtoken'];
        $this->authenticationIndicator = $params['authentication_indicator'];
        $this->oneClick  = (isset($params['oneClick']) && $params['oneClick']) ? true : false;
    }

    /**
     * return mapped customer card payment informations
     * @return \HiPay\Fullservice\Gateway\Request\PaymentMethod\CardTokenPaymentMethod
     */
    public function generate()
    {
        $cardTokenRequest = new CardTokenPaymentMethod();

        $this->mapRequest($cardTokenRequest);

        return $cardTokenRequest;
    }

    /**
     *
     * @param \HiPay\Fullservice\Gateway\Request\PaymentMethod\CardTokenPaymentMethod $cardTokenRequest
     */
    protected function mapRequest(&$cardTokenRequest)
    {
        $cardTokenRequest->cardtoken                = $this->cardToken;
        $cardTokenRequest->eci                      = ($this->oneClick) ? ECI::RECURRING_ECOMMERCE : ECI::SECURE_ECOMMERCE;
        $cardTokenRequest->authentication_indicator = $this->authenticationIndicator;
    }

}