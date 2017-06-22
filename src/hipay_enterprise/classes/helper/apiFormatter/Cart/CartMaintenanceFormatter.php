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
require_once(dirname(__FILE__) . '/../ApiFormatterInterface.php');
require_once(dirname(__FILE__) . '/../../tools/hipayMapper.php');

class CartMaintenanceFormatter implements ApiFormatterInterface {

    public function __construct($module, $params) {
        $this->module = $module;
        $this->context = Context::getContext();
        $this->configHipay = $this->module->hipayConfigTool->getConfigHipay();
        $this->products = $params["products"];
        $this->discounts = $params["discounts"];
        $this->order = $params["order"];
        $this->mapper = new HipayMapper($module);
        $this->totalItem = 0;
        // $this->db = new HipayDBQuery($module);
    }

    /**
     * return mapped cart informations
     * @return json
     */
    public function generate() {

        $cart = new HiPay\Fullservice\Gateway\Model\Cart\Cart();

        $this->mapRequest($cart);

        return $cart;
    }

    /**
     * map prestashop cart informations to request fields
     * @param HiPay\Fullservice\Gateway\Model\Cart\Cart $cart
     */
    protected function mapRequest(&$cart) {

        $totalQty = 0;
        // Good items
        foreach ($this->products as $product) {
            $totalQty += $product["quantity"];
            $item = $this->getGoodItem($product["item"], $product["quantity"]);
            $cart->addItem($item);
        }

        // Discount items
        if (!empty($this->discounts)) {
            $item = $this->getDiscountItem($totalQty);
            $cart->addItem($item);
        }

        // Fees items
        $item = $this->getFeesItem();
    //    $cart->addItem($item);
        var_dump($cart);
    }

    /**
     * create a good item from product line informations
     * @param type $good
     * @return \HiPay\Fullservice\Gateway\Model\Cart\Item
     */
    private function getGoodItem($product, $qty) {
        var_dump($product);
        $item = new HiPay\Fullservice\Gateway\Model\Cart\Item();
        $european_article_numbering = $product["ean13"];
        $product_reference = "good_" . $product["id_product"];
        $type = "good";
        $name = $product["name"];
        $quantity = $product["cart_quantity"];

        $this->totalItem += $quantity;

        $discount = -1 * Tools::ps_round(( $product["price_without_reduction"] * $product["cart_quantity"] ) - ($product["price_with_reduction"] * $product["cart_quantity"]), 2);
        $total_amount = Tools::ps_round($product["total_wt"], 2);
        $unit_price = Tools::ps_round((($total_amount - $discount ) / $quantity), 3);
        $discount = Tools::ps_round(($discount * $qty ) / $quantity, 3);
        $total_amount = Tools::ps_round(($total_amount * $qty ) / $quantity, 3);


        $tax_rate = $product["rate"];
        $discount_description = "";
        $product_description = "";
        $delivery_method = "";
        $delivery_company = "";
        $delivery_delay = "";
        $delivery_number = "";
        $product_category = $this->mapper->getMappedHipayCatFromPSId($product['id_category_default']);
        $shop_id = null;

        $item->__constructItem($european_article_numbering, $product_reference, $type, $name, $qty, $unit_price, $tax_rate, $discount, $total_amount, $discount_description, $product_description, $delivery_method, $delivery_company, $delivery_delay, $delivery_number, $product_category, $shop_id);

        return $item;
    }

    /**
     * create a discount item from discount line informations
     * @return HiPay\Fullservice\Gateway\Model\Cart\Item
     */
    private function getDiscountItem($totalQty) {

        $product_reference = "";
        $name = "";
        $unit_price = 0;
        $discount_description = "";
        $total_amount = 0;

        foreach ($this->discounts as $disc) {
            $product_reference .= "discount_" . $disc["id_cart_rule"] . "/";
            $name .= $disc["name"] . "/";
            $unit_price += -1 * Tools::ps_round($disc["value_real"], 3);
            $tax_rate = 0.00;
            $discount = 0.00;
            $discount_description .= $disc["description"] . "/";
            $total_amount += -1 * Tools::ps_round($disc["value_real"], 3);
        }

        $unit_price = Tools::ps_round(($unit_price * $totalQty ) / $this->totalItem, 3);
        $total_amount = Tools::ps_round(($total_amount * $totalQty) / $this->totalItem, 3);

        $item = HiPay\Fullservice\Gateway\Model\Cart\Item::buildItemTypeDiscount($product_reference, $name, $unit_price, $tax_rate, $discount, $discount_description, $total_amount);
        // forced category
        $item->setProductCategory(1);

        return $item;
    }

    /**
     * create a Fees item from cart informations
     * @return HiPay\Fullservice\Gateway\Model\Cart\Item
     */
    private function getFeesItem() {

        $carrier = new Carrier($this->order->id_carrier);
        $delivery = new Address($this->order->id_address_delivery);
        $product_reference = "fees_" . $carrier->id_reference;
        $name = $carrier->name;
        $unit_price = (float) $this->order->total_shipping;
        $tax_rate = $carrier->getTaxesRate($delivery);
        $discount = 0.00;
        $total_amount = (float) $this->order->total_shipping;
        $item = HiPay\Fullservice\Gateway\Model\Cart\Item::buildItemTypeFees($product_reference, $name, $unit_price, $tax_rate, $discount, $total_amount);
        // forced category
        $item->setProductCategory(1);

        return $item;
    }

}
