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
require_once(dirname(__FILE__).'/CommonRequestFormatterAbstract.php');
require_once(dirname(__FILE__).'/../Cart/CartMaintenanceFormatter.php');
require_once(dirname(__FILE__).'/../../tools/hipayDBQuery.php');
require_once(dirname(__FILE__).'/../../tools/hipayHelper.php');
require_once(dirname(__FILE__).'/../../tools/hipayOrderMessage.php');
require_once(dirname(__FILE__).'/../../../../lib/vendor/autoload.php');

/**
 *
 * Maintenance request formatter
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link 	https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class MaintenanceFormatter extends CommonRequestFormatterAbstract
{

    public function __construct(
    $module, $params, $maintenanceData
    )
    {
        parent::__construct($module);
        $this->params = $params;

        $this->amount                = (isset($params["amount"])) ? $params["amount"] : 0.01;
        $this->captureRefundFee      = (isset($params["capture_refund_fee"])) ? $params["capture_refund_fee"] : false;
        $this->captureRefundDiscount = (isset($params["capture_refund_discount"])) ? $params["capture_refund_discount"] : false;
        $this->refundItems           = (isset($params["refundItems"])) ? $params["refundItems"] : false;
        $this->order                 = (isset($params["order"])) ? new Order($params["order"]) : false;
        $this->operation             = (isset($params["operation"])) ? $params["operation"] : false;
        $this->db                    = new HipayDBQuery($module);
        $this->context               = Context::getContext();
        $this->cart                  = ($this->order) ? new Cart($this->order->id_cart) : false;
        $currency                    = ($this->order) ?new Currency($this->cart->id_currency): null;
        $this->context->currency     = $currency;
        $this->maintenanceData       = $maintenanceData;
    }

    /**
     * generate request data before API call
     * @return \HiPay\Fullservice\Gateway\Request\Maintenance\MaintenanceRequest
     */
    public function generate()
    {
        $maintenance = new \HiPay\Fullservice\Gateway\Request\Maintenance\MaintenanceRequest();

        $this->mapRequest($maintenance);
        return $maintenance;
    }

    /**
     * map prestashop order informations to request fields
     * @param type $maintenance
     */
    protected function mapRequest(&$maintenance)
    {
        parent::mapRequest($maintenance);
        $this->setCustomData(
            $maintenance, $this->cart, $this->params
        );

        $maintenance->amount    = $this->amount;
        $maintenance->operation = $this->operation;

        // retrieve number of capture or refund request
        $transactionAttempt = $this->maintenanceData->getNbOperationAttempt(
            $this->operation, $this->order->id
        );

        $maintenance->operation_id = HipayHelper::generateOperationId($this->order, $this->operation,
                                                                      $transactionAttempt);
        //if there's a basket
        if ($this->refundItems || $this->captureRefundFee == "on" || $this->captureRefundDiscount == "on") {
            $params = array("products" => array(), "discounts" => $this->cart->getCartRules(),
                "order" => $this->order, "captureRefundFee" => $this->captureRefundFee, "captureRefundDiscount" => $this->captureRefundDiscount,
                "operation" => $this->operation,
                "transactionAttempt" => $transactionAttempt);
            foreach ($this->cart->getProducts() as $item) {

                if (isset($this->refundItems[$item["id_product"]]) && $this->refundItems[$item["id_product"]] > 0
                ) {
                    $params["products"][] = array("item" => $item, "quantity" => $this->refundItems[$item["id_product"]]);
                } elseif ($this->refundItems == "full") {
                    $params["products"][] = array("item" => $item, "quantity" => $item["cart_quantity"]);
                }
            }

            $cart = new CartMaintenanceFormatter(
                $this->module, $params, $this->maintenanceData
            );

            $maintenance->basket = $cart->generate();

            $maintenance->amount = 0;

            foreach ($maintenance->basket->getAllItems() as $item) {
                $maintenance->amount += $item->getTotalAmount();
            }

            $maintenance->amount = Tools::ps_round(
                    $maintenance->amount, 3
            );
            $maintenance->basket = $maintenance->basket->toJson();
        }
    }
}