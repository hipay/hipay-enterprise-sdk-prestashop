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

/**
 * Class AdminHiPayAjaxCalculatePrice
 *
 * Manage synchronization for Hashing Algorithm with Hipay Backend
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class AdminHiPayCalculatePriceController extends ModuleAdminController
{

    /**
     * AdminHiPayAjaxCalculatePriceController constructor.
     */
    public function __construct()
    {
        $this->module = 'hipay_enterprise';
        $this->bootstrap = true;
        $this->context = Context::getContext();

        parent::__construct();
    }

    /**
     * Get cart total amount for for refund or capture
     *
     */
    public function displayAjaxCalculatePrice()
    {
        try {
            $captureRefundFee = Tools::getValue("captureRefundFee");
            $captureRefundDiscount = Tools::getValue("captureRefundDiscount");
            $captureRefundWrapping = Tools::getValue("captureRefundWrapping");
            $items = Tools::getValue("items");
            $operation = Tools::getValue("operation");
            $order = new Order(Tools::getValue("orderId"));
            $cart = new Cart(Tools::getValue("cartId"));

            $params = array(
                "products" => array(),
                "discounts" => $order->getCartRules(),
                "order" => $order,
                "cart" => $cart,
                "captureRefundFee" => $captureRefundFee === "false" ? 0 : 1,
                "captureRefundDiscount" => $captureRefundDiscount === "false" ? 0 : 1,
                "captureRefundWrapping" => $captureRefundWrapping === "false" ? 0 : 1,
                "operation" => $operation,
                "transactionAttempt" => 0
            );

            if (!empty($items)) {
                foreach ($order->getProducts() as $product) {
                    foreach ($items as $item) {
                        if ($item["id"] == $product["id_product"]) {
                            $params["products"][] = array(
                                "item" => $product,
                                "quantity" => $item["qty"]
                            );
                        }
                    }
                }
            }

            $cartFormatter = new CartMaintenanceFormatter($this->module, $params);
            $amount = $cartFormatter->getTotalAmount();

            ob_end_clean();
            header('Content-Type: application/json');
            die(
            json_encode(
                array(
                    "amount" => $amount
                )
            )
            );
        } catch (Exception $e) {
            ob_end_clean();
            header('Content-Type: application/json');
            die(
            json_encode(
                array(
                    "state" => "error",
                    "message" => $e->getTraceAsString()
                )
            )
            );
        }
    }
}
