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
require_once(dirname(__FILE__) . '/../../helper/HipayMapper.php');
require_once(dirname(__FILE__) . '/../../helper/HipayHelper.php');

/**
 *
 * Cart for payment request formatter
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class CartFormatter extends ApiFormatterAbstract
{

    /**
     * return mapped cart informations
     * @return json
     */
    public function generate()
    {
        $cart = new HiPay\Fullservice\Gateway\Model\Cart\Cart();

        $this->mapRequest($cart);

        return $cart->toJson();
    }

    /**
     * map prestashop cart informations to request fields
     * @param HiPay\Fullservice\Gateway\Model\Cart\Cart $cart
     */
    protected function mapRequest(&$cart)
    {
        $cartSummary = $this->cart->getSummaryDetails();
        // Good items
        foreach ($this->cart->getProducts() as $product) {
            $item = $this->getGoodItem($product);
            $cart->addItem($item);
        }

        if($this->cart->gift){
            $item = $this->getWrappingGoodItem();
            $cart->addItem($item);
        }

        // Discount items

        if (!empty($this->cart->getCartRules())) {
            $item = $this->getDiscountItem();
            $cart->addItem($item);
        }
        // Fees items
        $item = $this->getFeesItem($cartSummary);
        if ($item) {
            $cart->addItem($item);
        }
    }

    private function getWrappingGoodItem(){
        $item = new HiPay\Fullservice\Gateway\Model\Cart\Item();

        $product_reference = "wrapping";
        $type = "good";

        $name = "wrapping";
        $quantity = 1;

        $unit_price = $total_amount = $this->cart->getOrderTotal(true, Cart::ONLY_WRAPPING);

        $item->__constructItem(
            null,
            $product_reference,
            $type,
            $name,
            $quantity,
            $unit_price,
            0,
            0,
            $total_amount,
            "",
            "gift wrapping",
            null,
            null,
            null,
            null,
            1,
            null
        );

        return $item;
    }

    /**
     * create a good item from product line informations
     * @param type $good
     * @return \HiPay\Fullservice\Gateway\Model\Cart\Item
     */
    private function getGoodItem($product)
    {
        $item = new HiPay\Fullservice\Gateway\Model\Cart\Item();

        $european_article_numbering = null;
        if (isset($product["ean13"]) && $product["ean13"] != "0") {
            $european_article_numbering = $product["ean13"];
        }

        $product_reference = HipayHelper::getProductRef($product);
        $type = "good";
        $name = $product["name"];
        $quantity = (int)$product["cart_quantity"];


        $discount = -1 *
            Tools::ps_round(
                ($product["price_without_reduction"] * $product["cart_quantity"]) -
                ($product["price_with_reduction"] * $product["cart_quantity"]),
                2
            );
        $total_amount = Tools::ps_round($product["total_wt"], 2);
        $unit_price = Tools::ps_round((($total_amount - $discount) / $quantity), 3);

        $tax_rate = $product["rate"];
        $discount_description = "";
        $product_description = $product["description_short"];
        $delivery_method = null;
        $delivery_company = null;
        $delivery_delay = null;
        $delivery_number = null;
        $product_category = $this->mapper->getMappedHipayCatFromPSId($product['id_category_default']);
        $shop_id = null;

        $item->__constructItem(
            $european_article_numbering,
            $product_reference,
            $type,
            $name,
            $quantity,
            $unit_price,
            $tax_rate,
            $discount,
            $total_amount,
            $discount_description,
            $product_description,
            $delivery_method,
            $delivery_company,
            $delivery_delay,
            $delivery_number,
            $product_category,
            $shop_id
        );

        return $item;
    }

    /**
     * create a discount item from discount line informations
     * @return HiPay\Fullservice\Gateway\Model\Cart\Item
     */
    private function getDiscountItem()
    {
        $product_reference = array();
        $name = array();
        $unit_price = 0;
        $discount_description = array();
        $total_amount = 0;

        foreach ($this->cart->getCartRules() as $disc) {
            $product_reference[] = HipayHelper::getDiscountRef($disc);
            $name[] = $disc["name"];
            $unit_price += -1 * Tools::ps_round($disc["value_real"], 2);
            $tax_rate = 0.00;
            $discount = 0.00;
            $discount_description[] = $disc["description"];
            $total_amount += -1 * Tools::ps_round($disc["value_real"], 2);
        }
        $product_reference = join("/", $product_reference);
        $name = join("/", $name);
        $discount_description = join("/", $discount_description);

        $item = HiPay\Fullservice\Gateway\Model\Cart\Item::buildItemTypeDiscount(
            $product_reference,
            $name,
            $unit_price,
            $tax_rate,
            $discount,
            $discount_description,
            $total_amount
        );
        // forced category
        $item->setProductCategory(1);

        return $item;
    }

    /**
     * create a Fees item from cart informations
     * @return HiPay\Fullservice\Gateway\Model\Cart\Item
     */
    private function getFeesItem($cartSummary)
    {
        if ($cartSummary["carrier"]->id) {
            $carrier = new Carrier($cartSummary["carrier"]->id);
            $product_reference = HipayHelper::getCarrierRef($carrier);
            $name = $cartSummary["carrier"]->name;
            $unit_price = $this->cart->getTotalShippingCost();
            $tax_rate = $cartSummary["carrier"]->getTaxesRate($this->delivery);
            $discount = 0.00;
            $total_amount = $this->cart->getTotalShippingCost();
            $item = HiPay\Fullservice\Gateway\Model\Cart\Item::buildItemTypeFees(
                $product_reference,
                $name,
                $unit_price,
                $tax_rate,
                $discount,
                $total_amount
            );
            // forced category
            $item->setProductCategory(1);

            return $item;
        }
        return null;
    }
}
