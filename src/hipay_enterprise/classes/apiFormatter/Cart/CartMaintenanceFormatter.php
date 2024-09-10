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
        if ($this->captureRefundDiscount && !empty($this->order->getCartRules())) {
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

        return $cart;
    }

    private
    function getWrappingGoodItem()
    {
        $item = new HiPay\Fullservice\Gateway\Model\Cart\Item();
        $total_wrapping_amount = $this->order->total_wrapping;
        if ($this->order->gift == 1 && $total_wrapping_amount > 0) {
            $item->__constructItem(
                null,
                "wrapping",
                "good",
                "wrapping",
                1,
                floatval($this->order->total_wrapping_tax_incl),
                0,
                0,
                floatval($total_wrapping_amount),
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
                    "amount" => $total_wrapping_amount
                );
                $this->maintenanceData->addItem($captureData);
            }

            return $item;
        }

        return false;
    }

    /**
     * create a good item from product line informations
     * @param type $good
     * @return \HiPay\Fullservice\Gateway\Model\Cart\Item
     */
    private function getGoodItem($product, $qty)
    {
        $item = new HiPay\Fullservice\Gateway\Model\Cart\Item();
        $productsFromCartWithId = $this->cart->getProducts(true, (int)$product["product_id"]);

        if(count($productsFromCartWithId) > 1){
            foreach ($productsFromCartWithId as $currentProduct) {
                if($product['product_attribute_id'] == $currentProduct['id_product_attribute']){
                    $productFromCart = $currentProduct;
                    break;
                }
            }
        }else{
            $productFromCart = $productsFromCartWithId[0];
        }

        $european_article_numbering = null;
        if (!empty($product["ean13"]) && $product["ean13"] != "0") {
            $european_article_numbering = $product["ean13"];
        }

        $product_reference = HipayHelper::getProductRef($productFromCart);
        $orderDetail = $this->getOrderDetailByReference((int)$product["product_id"],
            $productFromCart['id_product_attribute']);

        if ($orderDetail) {
            $reduction_percent = $orderDetail['reduction_percent'] / 100;
            $unit_price = floatval($orderDetail['unit_price']);
            $unit_price_without_reduction = $reduction_percent > 0 ? $unit_price / (1 - $reduction_percent) : $unit_price;

            // Calculate discount
            $discount_per_unit = Tools::ps_round($unit_price_without_reduction - $unit_price, 2);
            $discount = -1
                * Tools::ps_round( $discount_per_unit * $qty, 2);

            $tax_rate = floatval($orderDetail['tax_rate']);
            $unit_price = Tools::ps_round( $unit_price * $qty, 2);
        }

        $item->__constructItem(
            $european_article_numbering,
            $product_reference,
            "good",
            $productFromCart["name"],
            $qty,
            $unit_price_without_reduction,
            $tax_rate,
            $discount,
            $unit_price,
            "", // discount_description
            "", // product_description
            "", // delivery_method
            "", // delivery_company
            "", // delivery_delay
            "", // delivery_number
            $this->mapper->getMappedHipayCatFromPSId($product['id_category_default']),
            null // shop_id
        );

        //save capture items and quantity in prestashop
        if ($this->maintenanceData) {
            $captureData = array(
                "hp_ps_order_id" => $this->order->id,
                "hp_ps_product_id" => (int)($product["id_product"].$product["product_attribute_id"]),
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
        $cartRules = $this->order->getCartRules();
        foreach ($cartRules as $rule) {
            $name[] = $rule['name'];
            $cartRule = new CartRule($rule['id_cart_rule']);
            $discount_description[] = $cartRule->description;
            $product_reference[] = $cartRule->code;
            $tax_rate = 0.00;
            $discount = 0.00;
            $unit_price += -1 * Tools::ps_round($rule["value"], 2);
            $total_amount += -1 * Tools::ps_round($rule['value'], 2);
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
        $unit_price = is_bool($this->captureRefundFee) ? (float)$this->order->total_shipping : floatval(number_format($this->captureRefundFee, 2));
        $tax_rate = $carrier->getTaxesRate($delivery);
        $discount = 0.00;
        $total_amount = is_bool($this->captureRefundFee) ? (float)$this->order->total_shipping : floatval($this->captureRefundFee);
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

    /**
     * Retrieves order detail information for a given product reference.
     *
     * @param string $productReference The unique reference of the product.
     * @return array|null Array of order detail information or null if not found.
     */
    private function getOrderDetailByReference($productId, $productAttributeId)
    {
        $orderItems = $this->order->getOrderDetailList();
        foreach ($orderItems as $item) {
            if ($item['product_id'] == $productId
                && $item['product_attribute_id'] == $productAttributeId) {
                return [
                    'unit_price' => $item['unit_price_tax_incl'],
                    'total_price' => $item['total_price_tax_incl'],
                    'quantity' => $item['product_quantity'],
                    'reduction_percent' => $item['reduction_percent'],
                    'tax_rate' => $item['tax_rate']
                ];
            }
        }
        return null;
    }
}
