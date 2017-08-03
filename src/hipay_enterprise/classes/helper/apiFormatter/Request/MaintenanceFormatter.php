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

require_once(dirname(__FILE__) . '/CommonRequestFormatterAbstract.php');
require_once(dirname(__FILE__) . '/../Cart/CartMaintenanceFormatter.php');
require_once(dirname(__FILE__) . '/../../tools/hipayDBQuery.php');
require_once(dirname(__FILE__) . '/../../tools/hipayOrderMessage.php');
require_once(dirname(__FILE__) . '/../../../../lib/vendor/autoload.php');

class MaintenanceFormatter extends CommonRequestFormatterAbstract
{
    public function __construct(
        $module,
        $params,
        $maintenanceData
    ) {
        parent::__construct($module);
        $this->params = $params;

        $this->amount = (isset($params["amount"])) ? $params["amount"]
            : 0.01;
        $this->captureRefundFee = (isset($params["capture_refund_fee"])) ? $params["capture_refund_fee"]
            : false;
        $this->refundItems = (isset($params["refundItems"])) ? $params["refundItems"]
            : false;
        $this->order = (isset($params["order"])) ? new Order($params["order"])
            : false;
        $this->operation = (isset($params["operation"])) ? $params["operation"]
            : false;
        $this->db = new HipayDBQuery($module);
        $this->cart = ($this->order) ? new Cart($this->order->id_cart)
            : false;
        $this->maintenanceData = $maintenanceData;
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
            $maintenance,
            $this->cart,
            $this->params
        );

        $maintenance->amount = $this->amount;
        $maintenance->operation = $this->operation;

        // retrieve number of capture or refund request
        $transactionAttempt = $this->db->getCaptureOrRefundAttempt(
            $this->operation,
            $this->order->id
        );
        // save number of capture or refund attempt
        HipayOrderMessage::captureOrRefundAttemptMessage(
            $this->operation,
            $this->order->id,
            $this->order->id_customer,
            ($transactionAttempt["attempt"] + 1),
            $transactionAttempt["message_id"]
        );

        $maintenance->operation_id = $this->order->id . '-' . $this->operation . '-' . ($transactionAttempt["attempt"]
                + 1);
        //if there's a basket
        if ($this->refundItems || $this->captureRefundFee == "on") {
            $params = array("products" => array(), "discounts" => $this->cart->getCartRules(),
                "order" => $this->order, "captureRefundFee" => $this->captureRefundFee, "operation" => $this->operation);
            foreach ($this->cart->getProducts() as $item) {
                
                if (isset($this->refundItems[$item["id_product"]]) && $this->refundItems[$item["id_product"]]
                    > 0
                ) {
                    $params["products"][] = array("item" => $item, "quantity" => $this->refundItems[$item["id_product"]]);
                } elseif ($this->refundItems == "full") {
                    $params["products"][] = array("item" => $item, "quantity" => $item["cart_quantity"]);
                }
            }
            
            $cart = new CartMaintenanceFormatter(
                $this->module,
                $params,
                $this->maintenanceData
            );

            $maintenance->basket = $cart->generate();

            $maintenance->amount = 0;

            foreach ($maintenance->basket->getAllItems() as $item) {
                $maintenance->amount += $item->getTotalAmount();
            }

            $maintenance->amount = Tools::ps_round(
                $maintenance->amount,
                3
            );
            $maintenance->basket = $maintenance->basket->toJson();
        }
    }
}
