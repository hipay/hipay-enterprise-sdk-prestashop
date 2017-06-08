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
require_once(dirname(__FILE__) . '/../../tools/hipayMapper.php');

class CartFormatter extends ApiFormatterAbstract {

    /**
     * return mapped cart informations
     * @return json
     */
    public function generate() {

        $cart = new HiPay\Fullservice\Gateway\Model\Cart\Cart();

        $this->mapRequest($cart);

        return $cart->toJson();
    }

    /**
     * map prestashop cart informations to request fields
     * @param HiPay\Fullservice\Gateway\Model\Cart\Cart $cart
     */
    protected function mapRequest(&$cart) {

        $cartSummary = $this->cart->getSummaryDetails();
        // Good items
        foreach ($this->cart->getProducts() as $product) {
            $item = $this->getGoodItem($product);
            $cart->addItem($item);
        }

        // Discount items
        foreach ($this->cart->getCartRules() as $disc) {
            $item = $this->getDiscountItem($disc);
            $cart->addItem($item);
        }

        // Fees items
        $item = $this->getFeesItem($cartSummary);
        $cart->addItem($item);
        var_dump($cart);
    }

    /**
     * create a good item from product line informations
     * @param type $good
     * @return \HiPay\Fullservice\Gateway\Model\Cart\Item
     */
    private function getGoodItem($product) {
        var_dump($product);
        $item = new HiPay\Fullservice\Gateway\Model\Cart\Item();
        $european_article_numbering = $product["ean13"];
        $product_reference = "good_" . $product["id_product"];
        $type = "good";
        $name = $product["name"];
        $quantity = $product["cart_quantity"];

        
        $discount = -1 * Tools::ps_round(( $product["price_without_reduction"] * $product["cart_quantity"] ) - ($product["price_with_reduction"] * $product["cart_quantity"]), 2);
        $total_amount = Tools::ps_round($product["total_wt"], 2);
        $unit_price = Tools::ps_round( (($total_amount - $discount ) / $quantity ), 3);

        $tax_rate = $product["rate"];
        $discount_description = "";
        $product_description = $product["description_short"];
        $delivery_method = "";
        $delivery_company = "";
        $delivery_delay = "";
        $delivery_number = "";
        $product_category = $this->mapper->getMappedHipayCatFromPSId($product['id_category_default']);
        $shop_id = null;

        $item->__constructItem($european_article_numbering, $product_reference, $type, $name, $quantity, $unit_price, $tax_rate, $discount, $total_amount, $discount_description, $product_description, $delivery_method, $delivery_company, $delivery_delay, $delivery_number, $product_category, $shop_id);

        return $item;
    }

    /**
     * create a discount item from discount line informations
     * @param int $discount
     * @return HiPay\Fullservice\Gateway\Model\Cart\Item
     */
    private function getDiscountItem($disc) {
        $product_reference = "discount_" . $disc["id_cart_rule"];
        $name = $disc["name"];
        $unit_price = -1 * Tools::ps_round($disc["value_real"], 3);
        $tax_rate = 0.00;
        $discount = 0.00;
        $discount_description = $disc["description"];
        $total_amount = -1 * Tools::ps_round($disc["value_real"], 3);

        $item = HiPay\Fullservice\Gateway\Model\Cart\Item::buildItemTypeDiscount($product_reference, $name, $unit_price, $tax_rate, $discount, $discount_description, $total_amount);
        // forced category
        $item->setProductCategory(1);
        
        return $item;
    }

    /**
     * create a Fees item from cart informations
     * @return HiPay\Fullservice\Gateway\Model\Cart\Item
     */
    private function getFeesItem($cartSummary) {
        $product_reference = "fees_" . $cartSummary["carrier"]->id_reference;
        $name = $cartSummary["carrier"]->name;
        $unit_price = (float) $cartSummary["total_shipping"];
        $tax_rate = $cartSummary["carrier"]->getTaxesRate($this->delivery);
        $discount = 0.00;
        $total_amount = (float) $cartSummary["total_shipping"];
        $item = HiPay\Fullservice\Gateway\Model\Cart\Item::buildItemTypeFees($product_reference, $name, $unit_price, $tax_rate, $discount, $total_amount);
        // forced category
        $item->setProductCategory(1);
        
        return $item;
    }

}
