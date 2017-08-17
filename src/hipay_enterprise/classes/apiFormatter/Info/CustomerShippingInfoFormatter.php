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

/**
 *
 * Customer shipping information request formatter
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link 	https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class CustomerShippingInfoFormatter extends apiFormatterAbstract
{

    /**
     * return mapped customer shipping informations
     * @return \HiPay\Fullservice\Gateway\Request\Info\CustomerShippingInfoRequest
     */
    public function generate()
    {
        $customerHippingInfo = new \HiPay\Fullservice\Gateway\Request\Info\CustomerShippingInfoRequest();

        $this->mapRequest($customerHippingInfo);

        return $customerHippingInfo;
    }

    /**
     * map prestashop shipping informations to request fields (Hpayment Post)
     * @param \HiPay\Fullservice\Gateway\Request\Info\CustomerShippingInfoRequest $customerHippingInfo
     */
    protected function mapRequest(&$customerHippingInfo)
    {
        $customerHippingInfo->shipto_firstname      = $this->delivery->firstname;
        $customerHippingInfo->shipto_lastname       = $this->delivery->lastname;
        $customerHippingInfo->shipto_streetaddress  = $this->delivery->address1;
        $customerHippingInfo->shipto_streetaddress2 = $this->delivery->address2;
        $customerHippingInfo->shipto_city           = $this->delivery->city;
        $customerHippingInfo->shipto_zipcode        = $this->delivery->postcode;
        $customerHippingInfo->shipto_country        = $this->deliveryCountry->iso_code;
        $customerHippingInfo->shipto_phone          = $this->getPhone();
        $customerHippingInfo->shipto_state          = ($this->deliveryState) ? $this->deliveryState->name : '';
        $customerHippingInfo->shipto_recipientinfo  = $this->store->name;
    }

    /**
     * return well formatted phone number
     * @return string
     */
    private function getPhone()
    {
        if (isset($this->delivery->phone) && $this->delivery->phone != '') {
            return $this->delivery->phone;
        } elseif (isset($this->delivery->phone_mobile) && $this->delivery->phone_mobile != '') {
            return $this->delivery->phone_mobile;
        } else {
            return '';
        }
    }
}