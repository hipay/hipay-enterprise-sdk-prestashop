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

use \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\AccountInfo\Customer as CustomerInfo;
use \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\AccountInfo\Purchase as PurchaseInfo;
use \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\AccountInfo\Payment as PaymentInfo;
use \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\AccountInfo\Shipping as ShippingInfo;
use HiPay\Fullservice\Enum\ThreeDSTwo\NameIndicator;
use HiPay\Fullservice\Enum\ThreeDSTwo\SuspiciousActivity;

/**
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class AccountInfoFormatter extends ApiFormatterAbstract
{
    private $params;

    public function __construct($module, $cart, $params)
    {
        parent::__construct($module, $cart);
        $this->params = $params;
    }

    /**
     * @return \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\AccountInfo
     */
    public function generate()
    {
        $accountInfo = new \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\AccountInfo();

        $this->mapRequest($accountInfo);

        return $accountInfo;
    }

    /**
     * @param \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\AccountInfo $accountInfo
     */
    protected function mapRequest(&$accountInfo)
    {
        $accountInfo->customer = $this->getCustomerInfo();
        $accountInfo->purchase = $this->getPurchaseInfo();
        $accountInfo->payment = $this->getPaymentInfo();
        $accountInfo->shipping = $this->getShippingInfo();
    }

    private function getCustomerInfo()
    {
        $customerInfo = new CustomerInfo();

        if (!$this->customer->is_guest) {
            $customerInfo->account_change = date('Ymd', strtotime($this->customer->date_upd));
            $customerInfo->opening_account_date = date('Ymd', strtotime($this->customer->date_add));
            $customerInfo->password_change = date('Ymd', strtotime($this->customer->last_passwd_gen));
        }

        return $customerInfo;
    }

    private function getPurchaseInfo()
    {
        $purchaseInfo = new PurchaseInfo();

        if (!$this->customer->is_guest) {
            $now = new \DateTime('now');
            $now = $now->format('Y-m-d H:i:s');
            $sixMonthAgo = new \DateTime('6 months ago');
            $sixMonthAgo = $sixMonthAgo->format('Y-m-d H:i:s');
            $twentyFourHoursAgo = new \DateTime('24 hours ago');
            $twentyFourHoursAgo = $twentyFourHoursAgo->format('Y-m-d H:i:s');
            $oneYearAgo = new \DateTime('1 years ago');
            $oneYearAgo = $oneYearAgo->format('Y-m-d H:i:s');


            $purchaseInfo->count = count(Order::getOrdersIdByDate($sixMonthAgo, $now, $this->customer->id));
            $purchaseInfo->card_stored_24h = $this->dbToken->nbAttemptCreateCard(
                $this->customer->id,
                $twentyFourHoursAgo
            );
            $purchaseInfo->payment_attempts_24h = $this->threeDSDB->getNbPaymentAttempt(
                $this->customer->id,
                $twentyFourHoursAgo,
                $this->cardPaymentProduct
            );
            $purchaseInfo->payment_attempts_1y = $this->threeDSDB->getNbPaymentAttempt(
                $this->customer->id,
                $oneYearAgo,
                $this->cardPaymentProduct
            );
        }

        return $purchaseInfo;
    }

    private function getPaymentInfo()
    {
        $paymentInfo = new PaymentInfo();

        if (!$this->customer->is_guest && isset($this->params["oneClick"]) && $this->params["oneClick"]) {
            $dateCartFirstUsed = $this->dbToken->getToken(
                $this->customer->id,
                $this->params["cardtoken"]
            );

            if ($dateCartFirstUsed['created_at']) {
                $paymentInfo->enrollment_date = date('Ymd', strtotime($dateCartFirstUsed['created_at']));
            }
        }

        return $paymentInfo;
    }

    private function getShippingInfo()
    {
        $shippingInfo = new ShippingInfo();

        if (!$this->customer->is_guest) {
            $addressFirstUsed = $this->threeDSDB->getDateAddressFirstUsed($this->delivery->id);
            $shippingInfo->shipping_used_date = ($addressFirstUsed) ? date('Ymd', strtotime($addressFirstUsed)) : null;

            $customerFullName = $this->customer->firstname . $this->customer->lastname;
            $shippingName = $this->delivery->firstname . $this->delivery->lastname;

            $shippingInfo->name_indicator = NameIndicator::DIFFERENT;

            if ($shippingName === "" || strtoupper($shippingName) === strtoupper($customerFullName)) {
                $shippingInfo->name_indicator = NameIndicator::IDENTICAL;
            }
        }


        return $shippingInfo;
    }
}
