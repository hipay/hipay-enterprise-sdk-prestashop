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
require_once(dirname(__FILE__) . '/../../../../lib/vendor/autoload.php');
require_once(dirname(__FILE__) . '/../ApiFormatterAbstract.php');

class CardTokenFormatter extends ApiFormatterAbstract {

    private $cardToken;

    public function __construct($module, $cardToken) {
        parent::__construct($module);
        $this->cardToken = $cardToken;
    }

    /**
     * return mapped customer card payment informations
     * @return \HiPay\Fullservice\Gateway\Request\PaymentMethod\CardTokenPaymentMethod
     */
    public function generate() {

        $cardTokenRequest = new \HiPay\Fullservice\Gateway\Request\PaymentMethod\CardTokenPaymentMethod();

        $this->mapRequest($cardTokenRequest);

        return $cardTokenRequest;
    }

    /**
     * 
     * @param \HiPay\Fullservice\Gateway\Request\PaymentMethod\CardTokenPaymentMethod $cardTokenRequest
     */
    protected function mapRequest(&$cardTokenRequest) {
        
        $cardTokenRequest->cardtoken = $this->cardToken;
        $cardTokenRequest->eci = 7;
        $cardTokenRequest->authentication_indicator = 0;
    }

}
