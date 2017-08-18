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
require_once(dirname(__FILE__).'/../ApiFormatterInterface.php');
require_once(dirname(__FILE__).'/../../helper/HipayMapper.php');
require_once(dirname(__FILE__).'/../../helper/HipayDBQuery.php');

/**
 *
 * Cart for maintenance request formatter
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link 	https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class CartMaintenanceFormatter implements ApiFormatterInterface
{

    public function __construct($module, $params, $maintenanceData)
    {
        $this->module                = $module;
        $this->context               = Context::getContext();
        $this->configHipay           = $this->module->hipayConfigTool->getConfigHipay();
        $this->products              = $params["products"];
        $this->discounts             = $params["discounts"];
        $this->order                 = $params["order"];
        $this->operation             = $params["operation"];
        $this->captureRefundFee      = $params["captureRefundFee"];
        $this->captureRefundDiscount = $params["captureRefundDiscount"];
        $this->transactionAttempt    = $params["transactionAttempt"];
        $this->mapper                = new HipayMapper($module);
        $this->totalItem             = 0;
        $this->db                    = new HipayDBQuery($module);
        $this->maintenanceData       = $maintenanceData;
    }

    /**
     * return mapped cart informations
     * @return json
     */
    public function generate()
    {
        $cart = new HiPay\Fullservice\Gateway\Model\Cart\Cart();

        $this->mapRequest($cart);

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
                $product["item"], $product["quantity"]
            );
            $cart->addItem($item);
        }

        // Discount items
        if ($this->captureRefundDiscount && sizeof($this->discounts) > 0) {
            $item = $this->getDiscountItem();
            $cart->addItem($item);
        }

        // Fees items
        if ($this->captureRefundFee) {
            $item = $this->getFeesItem();
            $cart->addItem($item);
        }
    }

    /**
     * create a good item from product line informations
     * @param type $good
     * @return \HiPay\Fullservice\Gateway\Model\Cart\Item
     */
    private function getGoodItem($product, $qty)
    {
        $item                       = new HiPay\Fullservice\Gateway\Model\Cart\Item();
        $european_article_numbering = null;
        if (isset($product["ean13"]) && $product["ean13"] != "0") {
            $european_article_numbering = $product["ean13"];
        }
        $product_reference = HipayHelper::getProductRef($product);
        $type              = "good";
        $name              = $product["name"];
        $quantity          = $product["cart_quantity"];

        $this->totalItem += $quantity;

        $discount     = -1 * Tools::ps_round(
                ($product["price_without_reduction"] * $product["cart_quantity"]) - ($product["price_with_reduction"] * $product["cart_quantity"]),
                2
        );
        $total_amount = Tools::ps_round(
                $product["total_wt"], 2
        );
        $unit_price   = Tools::ps_round(
                (($total_amount - $discount) / $quantity), 3
        );
        $discount     = Tools::ps_round(
                ($discount * $qty) / $quantity, 3
        );
        $total_amount = Tools::ps_round(
                ($total_amount * $qty) / $quantity, 3
        );


        $tax_rate             = $product["rate"];
        $discount_description = "";
        $product_description  = "";
        $delivery_method      = "";
        $delivery_company     = "";
        $delivery_delay       = "";
        $delivery_number      = "";
        $product_category     = $this->mapper->getMappedHipayCatFromPSId($product['id_category_default']);
        $shop_id              = null;

        $item->__constructItem(
            $european_article_numbering, $product_reference, $type, $name, $qty, $unit_price, $tax_rate, $discount,
            $total_amount, $discount_description, $product_description, $delivery_method, $delivery_company,
            $delivery_delay, $delivery_number, $product_category, $shop_id
        );

        //save capture items and quantity in prestashop
        $captureData = array(
            "hp_ps_order_id" => $this->order->id,
            "hp_ps_product_id" => $product["id_product"],
            "operation" => $this->operation,
            "type" => 'good',
            "attempt_number" => $this->transactionAttempt + 1,
            "quantity" => $item->getQuantity(),
            "amount" => Tools::ps_round($item->getTotalAmount(), 2)
        );
        $this->maintenanceData->addItem($captureData);

        return $item;
    }

    /**
     * create a discount item from discount line informations
     * @return HiPay\Fullservice\Gateway\Model\Cart\Item
     */
    private function getDiscountItem()
    {
        $product_reference    = "";
        $name                 = "";
        $unit_price           = 0;
        $discount_description = "";
        $total_amount         = 0;

        foreach ($this->discounts as $disc) {
            $product_reference[]    = HipayHelper::getDiscountRef($disc);
            $name[]                 = $disc["name"];
            $unit_price += -1 * Tools::ps_round(
                    $disc["value_real"], 2
            );
            $tax_rate               = 0.00;
            $discount               = 0.00;
            $discount_description[] = $disc["description"];
            $total_amount += -1 * Tools::ps_round(
                    $disc["value_real"], 2
            );
        }

        $product_reference    = join("/", $product_reference);
        $name                 = join("/", $name);
        $discount_description = join("/", $discount_description);

        $item = HiPay\Fullservice\Gateway\Model\Cart\Item::buildItemTypeDiscount(
                $product_reference, $name, $unit_price, $tax_rate, $discount, $discount_description, $total_amount
        );
        // forced category
        $item->setProductCategory(1);

        //save capture items and quantity in prestashop
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

        return $item;
    }

    /**
     * create a Fees item from cart informations
     * @return HiPay\Fullservice\Gateway\Model\Cart\Item
     */
    private function getFeesItem()
    {
        $carrier           = new Carrier($this->order->id_carrier);
        $delivery          = new Address($this->order->id_address_delivery);
        $product_reference = HipayHelper::getCarrierRef($carrier);
        $name              = $carrier->name;
        $unit_price        = (float) $this->order->total_shipping;
        $tax_rate          = $carrier->getTaxesRate($delivery);
        $discount          = 0.00;
        $total_amount      = (float) $this->order->total_shipping;
        $item              = HiPay\Fullservice\Gateway\Model\Cart\Item::buildItemTypeFees(
                $product_reference, $name, $unit_price, $tax_rate, $discount, $total_amount
        );
        // forced category
        $item->setProductCategory(1);



        //save capture items and quantity in prestashop
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

        return $item;
    }
}