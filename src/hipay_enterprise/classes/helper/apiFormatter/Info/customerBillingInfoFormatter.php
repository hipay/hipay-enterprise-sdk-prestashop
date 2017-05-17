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
require_once(_PS_ROOT_DIR_ . _MODULE_DIR_ . 'hipay_enterprise/lib/vendor/autoload.php');
require_once(_PS_ROOT_DIR_ . _MODULE_DIR_ . 'hipay_enterprise/classes/helper/apiFormatter/apiFormatterAbstract.php');

class customerBillingInfoFormatter extends apiFormatterAbstract {

    public function __construct($module) {
        parent::__construct($module);

        $this->invoice = new Address((int) $this->cart->id_address_invoice);
    }

    /**
     * 
     * @return \HiPay\Fullservice\Gateway\Request\Info\CustomerBillingInfoRequest
     */
    public function generate() {

        $customerBillingInfo = new \HiPay\Fullservice\Gateway\Request\Info\CustomerBillingInfoRequest();

        $this->mapRequest($customerBillingInfo);

        return $customerBillingInfo;
    }

    /**
     * 
     * @param type $order
     */
    protected function mapRequest(&$customerBillingInfo) {


        $customerBillingInfo->firstname = $this->customer->firstname;
        $customerBillingInfo->lastname = $this->customer->lastname;
        $customerBillingInfo->email = $this->customer->email;

        $dob = $this->customer->birthday;
        if (!is_null($dob) && !empty($dob)) {
            $customerBillingInfo->birthdate = str_replace('-', '', $dob);
            ;
        }

        $customerBillingInfo->gender = $this->getGender($this->customer->id_gender);

        $customerBillingInfo->streetaddress = $this->invoice->address1;
        $customerBillingInfo->streetaddress2 = $this->invoice->address2;
        $customerBillingInfo->city = $this->invoice->city;
        $customerBillingInfo->zipcode = $this->invoice->postcode;
        $customerBillingInfo->country = $this->invoice->address1;
        $customerBillingInfo->phone = $this->getPhone();
        $customerBillingInfo->state = '';
        $customerBillingInfo->recipientinfo = $this->store->name;
    }

    /**
     * 
     * @return string
     */
    private function getPhone() {
        if (isset($this->invoice->phone) && $this->invoice->phone != '')
            return $this->invoice->phone;
        elseif (isset($this->invoice->phone_mobile) && $this->invoice->phone_mobile != '')
            return $this->invoice->phone_mobile;
        else
            return '';
    }

}
