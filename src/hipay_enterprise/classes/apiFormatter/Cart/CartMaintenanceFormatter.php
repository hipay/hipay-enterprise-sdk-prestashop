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
require_once(dirname(__FILE__) . '/../ApiFormatterInterface.php');
require_once(dirname(__FILE__) . '/../../helper/HipayMapper.php');
require_once(dirname(__FILE__) . '/../../helper/dbquery/HipayDBMaintenance.php');

/**
 *
 * Cart for maintenance request formatter
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class CartMaintenanceFormatter implements ApiFormatterInterface
{

    public function __construct($module, $params, $maintenanceData = null)
    {
        $this->module = $module;
        $this->context = Context::getContext();
        $this->configHipay = $this->module->hipayConfigTool->getConfigHipay();
        $this->dbMaintenance = new HipayDBMaintenance($module);
        $this->products = $params["products"];
        $this->discounts = $params["discounts"];
        $this->order = $params["order"];
        $this->originalHipayBasket = $this->dbMaintenance->getOrderBasket($this->order->id);
        $this->cart = $params["cart"];
        $this->operation = $params["operation"];
        $this->captureRefundFee = $params["captureRefundFee"];
        $this->captureRefundDiscount = $params["captureRefundDiscount"];
        $this->captureRefundWrapping = $params["captureRefundWrapping"];
        $this->transactionAttempt = $params["transactionAttempt"];
        $this->mapper = new HipayMapper($module);
        $this->totalItem = 0;
        $this->maintenanceData = $maintenanceData;
        $this->hipayCart = null;
    }

    /**
     * return mapped cart informations
     * @return json
     */
    public function generate()
    {
        // Reload and set customer for (autoAddToCart)
        $customer = new Customer($this->cart->id_customer);
        Context::getContext()->customer = $customer;

        $cart = new HiPay\Fullservice\Gateway\Model\Cart\Cart();

        $this->mapRequest($cart);
        $this->hipayCart = $cart;

        return $cart;
    }

    /**
     * map prestashop cart informations to request fields
     * @param HiPay\Fullservice\Gateway\Model\Cart\Cart $cart
     */
    protected function mapRequest(&$cart)
    {
        // Good items
        foreach ($this->products as $product) {
            $item = $this->getGoodItem(
                $product["item"],
                $product["quantity"]
            );
            $cart->addItem($item);
        }

        // Discount items
        if ($this->captureRefundWrapping) {
            $item = $this->getWrappingGoodItem();
            if ($item) {
                $cart->addItem($item);
            }
        }

        // Discount items
        if ($this->captureRefundDiscount && sizeof($this->discounts) > 0) {
            $item = $this->getDiscountItem();
            $cart->addItem($item);
        }

        // Fees items
        if ($this->captureRefundFee) {
            $item = $this->getFeesItem();
            if ($item) {
                $cart->addItem($item);
            }
        }
    }

    private
    function getWrappingGoodItem()
    {
        $item = new HiPay\Fullservice\Gateway\Model\Cart\Item();

        $originalWrapping = $this->getOriginalGood("wrapping");

        if ($originalWrapping === null) {
            return false;
        }

        $item->__constructItem(
            null,
            $originalWrapping["product_reference"],
            "good",
            $originalWrapping["name"],
            1,
            $originalWrapping["unit_price"],
            0,
            0,
            $originalWrapping["total_amount"],
            "",
            "gift wrapping",
            null,
            null,
            null,
            null,
            1,
            null
        );

        //save capture items and quantity in prestashop
        if ($this->maintenanceData) {
            $captureData = array(
                "hp_ps_order_id" => $this->order->id,
                "hp_ps_product_id" => 0,
                "operation" => $this->operation,
                "type" => 'wrapping',
                "attempt_number" => $this->transactionAttempt + 1,
                "quantity" => 1,
                "amount" => $originalWrapping["total_amount"]
            );
            $this->maintenanceData->addItem($captureData);
        }

        return $item;
    }


    /**
     * create a good item from product line informations
     * @param type $good
     * @return \HiPay\Fullservice\Gateway\Model\Cart\Item
     */
    private
    function getGoodItem(
        $product,
        $qty
    ) {
        $item = new HiPay\Fullservice\Gateway\Model\Cart\Item();
        $productFromCart = $this->cart->getProducts(true, (int)$product["product_id"])[0];

        $european_article_numbering = null;
        if (!empty($product["ean13"]) && $product["ean13"] != "0") {
            $european_article_numbering = $product["ean13"];
        }

        $product_reference = HipayHelper::getProductRef($productFromCart);
        $originalGood = $this->getOriginalGood($product_reference);

        $type = "good";
        $quantity = (int)$product["product_quantity"];

        $totalDiscount = $originalGood["discount"] ? $originalGood["discount"] : -0;
        $total_amount = $originalGood["total_amount"];
        $unit_price = $originalGood["unit_price"];

        $discount = Tools::ps_round(($totalDiscount * $qty) / $quantity, 3);
        $total_amount = Tools::ps_round(($total_amount * $qty) / $quantity, 3);

        $tax_rate = $product["tax_rate"];
        $discount_description = "";
        $product_description = "";
        $delivery_method = "";
        $delivery_company = "";
        $delivery_delay = "";
        $delivery_number = "";
        $product_category = $this->mapper->getMappedHipayCatFromPSId($product['id_category_default']);
        $shop_id = null;

        $item->__constructItem(
            $european_article_numbering,
            $product_reference,
            $type,
            $productFromCart["name"],
            $qty,
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

        //save capture items and quantity in prestashop
        if ($this->maintenanceData) {
            $captureData = array(
                "hp_ps_order_id" => $this->order->id,
                "hp_ps_product_id" => $product["product_id"],
                "operation" => $this->operation,
                "type" => 'good',
                "attempt_number" => $this->transactionAttempt + 1,
                "quantity" => $qty,
                "amount" => Tools::ps_round($item->getTotalAmount(), 2)
            );
            $this->maintenanceData->addItem($captureData);
        }

        return $item;
    }

    /**
     *  Retrieve discount from original cart
     *
     * @param $name
     * @return mixed
     */
    private
    function getOriginalDiscount(
        $name
    ) {
        foreach ($this->originalHipayBasket as $key => $value) {
            if ($value["name"] == $name
                && $value["type"] == 'discount') {
                return $value;
            }
        }
    }

    /**
     *  Retrieve good from original cart
     *
     * @param $productReference
     * @return mixed
     */
    private
    function getOriginalGood(
        $productReference
    ) {
        foreach ($this->originalHipayBasket as $key => $value) {
            if ($value["product_reference"] == $productReference) {
                return $value;
            }
        }
    }

    /**
     * create a discount item from discount line informations
     * @return HiPay\Fullservice\Gateway\Model\Cart\Item
     */
    private
    function getDiscountItem()
    {
        $product_reference = array();
        $name = array();
        $unit_price = 0;
        $discount_description = array();
        $total_amount = 0;

        foreach ($this->discounts as $disc) {
            $cartRule = new CartRule($disc["id_cart_rule"]);
            $name[] = $disc["name"];
            $product_reference[] = $this->getOriginalDiscount($disc["name"])["product_reference"];
            $unit_price += -1 * Tools::ps_round($disc["value"], 2);
            $tax_rate = 0.00;
            $discount = 0.00;
            $discount_description[] = $cartRule->description;
            $total_amount += -1 * Tools::ps_round($disc["value"], 2);
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

        //save capture items and quantity in prestashop
        if ($this->maintenanceData) {
            $captureData = array(
                "hp_ps_order_id" => $this->order->id,
                "hp_ps_product_id" => 0,
                "operation" => $this->operation,
                "type" => 'discount',
                "attempt_number" => $this->transactionAttempt + 1,
                "quantity" => 1,
                "amount" => $total_amount
            );
            $this->maintenanceData->addItem($captureData);
        }

        return $item;
    }

    /**
     * create a Fees item from cart informations
     * @return HiPay\Fullservice\Gateway\Model\Cart\Item
     */
    private
    function getFeesItem()
    {
        $carrier = new Carrier($this->order->id_carrier);
        $delivery = new Address($this->order->id_address_delivery);
        $product_reference = HipayHelper::getCarrierRef($carrier);
        $name = $carrier->name;
        $unit_price = (float)$this->order->total_shipping;
        $tax_rate = $carrier->getTaxesRate($delivery);
        $discount = 0.00;
        $total_amount = (float)$this->order->total_shipping;
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


        //save capture items and quantity in prestashop
        if ($this->maintenanceData) {
            $captureData = array(
                "hp_ps_order_id" => $this->order->id,
                "hp_ps_product_id" => 0,
                "operation" => $this->operation,
                "type" => 'fees',
                "attempt_number" => $this->transactionAttempt + 1,
                "quantity" => 1,
                "amount" => $total_amount
            );
            $this->maintenanceData->addItem($captureData);
        }

        return $item;
    }

    /**
     * Get Total Amount
     */
    public
    function getTotalAmount()
    {
        $amount = 0;
        if ($this->hipayCart == null) {
            $this->hipayCart = $this->generate();
        }
        foreach ($this->hipayCart->getAllItems() as $item) {
            $amount += $item->getTotalAmount();
        }
        return Tools::ps_round($amount, 3);
    }
}
