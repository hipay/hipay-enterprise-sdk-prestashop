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

use HiPay\Fullservice\Enum\ThreeDSTwo\PurchaseIndicator;
use HiPay\Fullservice\Enum\ThreeDSTwo\DeliveryTimeFrame;
use HiPay\Fullservice\Enum\ThreeDSTwo\ReorderIndicator;
use HiPay\Fullservice\Enum\ThreeDSTwo\ShippingIndicator;

/**
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class MerchantRiskStatementFormatter extends ApiFormatterAbstract
{

    public function __construct($module, $cart = false)
    {
        parent::__construct($module, $cart);
    }

    /**
     * @return \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\MerchantRiskStatement
     */
    public function generate()
    {
        $merchantRiskStatement = new \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\MerchantRiskStatement();

        $this->mapRequest($merchantRiskStatement);

        return $merchantRiskStatement;
    }

    /**
     * @param \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\MerchantRiskStatement $merchantRiskStatement
     */
    protected function mapRequest(&$merchantRiskStatement)
    {
        if ($this->containsVirtualItem()) {
            $merchantRiskStatement->email_delivery_address = $this->customer->email;
            $merchantRiskStatement->delivery_time_frame = DeliveryTimeFrame::ELECTRONIC_DELIVERY;
        }

        if (!$this->cart->isAllProductsInStock()) {
            $merchantRiskStatement->purchase_indicator = PurchaseIndicator::FUTURE_AVAILABILITY;
        } else {
            $merchantRiskStatement->purchase_indicator = PurchaseIndicator::MERCHANDISE_AVAILABLE;
        }

        if (!$this->customer->is_guest) {
            $merchantRiskStatement->reorder_indicator = $this->cartAlreadyOrdered();
        }

        $merchantRiskStatement->shipping_indicator = $this->getShippingIndicator();
    }

    private function containsVirtualItem()
    {
        foreach ($this->cart->getProducts() as $product) {
            if ($product["is_virtual"]) {
                return true;
            }
        }

        return false;
    }

    private function cartAlreadyOrdered()
    {
        $productsArray = array();

        foreach ($this->cart->getProducts() as $product) {
            $productsArray[] = array(
                "product_id" => $product["id_product"],
                "id_shop" => $product["id_shop"],
                "product_quantity" => $product["cart_quantity"],
                "product_attribute_id" => $product["id_product_attribute"]
            );
        }

        if ($this->threeDSDB->cartAlreadyOrdered($productsArray)) {
            return ReorderIndicator::REORDERED;
        }

        return ReorderIndicator::FIRST_TIME_ORDERED;
    }

    private function getShippingIndicator()
    {
        if (!$this->cart->hasRealProducts()) {
            return ShippingIndicator::DIGITAL_GOODS;
        }

        if ($this->cart->id_address_delivery === $this->cart->id_address_invoice) {
            return ShippingIndicator::SHIP_TO_CARDHOLDER_BILLING_ADDRESS;
        } elseif ($this->customer->is_guest) {
            return ShippingIndicator::SHIP_TO_DIFFERENT_ADDRESS;
        } elseif ($this->delivery->isUsed()) {
            return ShippingIndicator::SHIP_TO_VERIFIED_ADDRESS;
        }

        return ShippingIndicator::SHIP_TO_DIFFERENT_ADDRESS;
    }
}
