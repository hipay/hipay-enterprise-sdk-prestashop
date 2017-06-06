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

    public function __construct($module) {
        parent::__construct($module);
        $this->mapper = new HipayMapper($module);
    }

    /**
     * return mapped cart informations
     * @return json
     */
    public function generate() {

        $cart = new HiPay\Fullservice\Gateway\Model\Cart\Cart();

        $this->mapRequest($cart);

        return $customerBillingInfo->toJson();
    }

    /**
     * map prestashop cart informations to request fields
     * @param HiPay\Fullservice\Gateway\Model\Cart\Cartt $cart
     */
    protected function mapRequest(&$cart) {

        var_dump($this->cart);
        var_dump($this->cart->getProducts());
        var_dump($this->cart->getCartRules());

        foreach ($this->cart->getProducts() as $product) {
            $item = new HiPay\Fullservice\Gateway\Model\Cart\Item();
            $european_article_numbering = $product["ean13"];
            $product_reference = $product["id_product"];
            $type = "good";
            $name = $product["name"];
            $quantity = $product["cart_quantity"];

            $unit_price = $product["price_without_reduction"];

            $tax_rate = $product["rate"];
            $discount = ( $product["price_without_reduction"] * $product["cart_quantity"] ) - ($product["price_with_reduction"] * $product["cart_quantity"]);

            $total_amount = $product["price_with_reduction"] * $product["cart_quantity"];
            $discount_description = "";
            $product_description = $product["description_short"];

            $category = new Category($product['id_category_default']);

            var_dump($category);
            
            $delivery_method = "";
            $delivery_company = "";
            $delivery_delay = "";
            $delivery_number = "";
            $product_category = $this->mapper->getMappedHipayCatFromPSId($product['id_category_default']);
            $shop_id = $product["id_shop"];

            $item->__constructItem($european_article_numbering, $product_reference, $type, $name, $quantity, $unit_price, $tax_rate, $discount, $total_amount, $discount_description, $product_description, $delivery_method, $delivery_company, $delivery_delay, $delivery_number, $product_category, $shop_id);

            $cart->addItem($item);
        }
        var_dump($cart);
        die();
    }

}
