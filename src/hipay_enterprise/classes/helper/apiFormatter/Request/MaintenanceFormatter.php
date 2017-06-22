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
require_once(dirname(__FILE__).'/../ApiFormatterAbstract.php');
require_once(dirname(__FILE__).'/../Cart/CartMaintenanceFormatter.php');
require_once(dirname(__FILE__).'/../../tools/hipayDBQuery.php');
require_once(dirname(__FILE__).'/../../tools/hipayOrderMessage.php');
require_once(dirname(__FILE__).'/../../../../lib/vendor/autoload.php');

class MaintenanceFormatter implements ApiFormatterInterface
{

    public function __construct($module, $params)
    {
        $this->module      = $module;
        $this->context     = Context::getContext();
        $this->configHipay = $this->module->hipayConfigTool->getConfigHipay();
        $this->amount      = (isset($params["amount"])) ? $params["amount"] : 0.01;
        $this->refundItems = (isset($params["refundItems"])) ? $params["refundItems"]
                : false;
        $this->order       = (isset($params["order"])) ? new Order($params["order"])
                : false;
        $this->operation   = (isset($params["operation"])) ? $params["operation"]
                : false;
        $this->db          = new HipayDBQuery($module);
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
        $maintenance->amount    = $this->amount;
        $maintenance->operation = $this->operation;

        // retrieve number of capture or refund request
        $transactionAttempt = $this->db->getCaptureOrRefundAttempt($this->operation,
            $this->order->id);
        // save number of capture or refund attempt
        HipayOrderMessage::captureOrRefundAttemptMessage($this->operation,
            $this->order->id, ($transactionAttempt["attempt"] + 1),
            $transactionAttempt["message_id"]);

        $maintenance->operation_id = $this->order->id.'-'.$this->operation.'-'.($transactionAttempt["attempt"]
            + 1);

        //if there's a basket
        if ($this->refundItems) {

            $cart = new Cart($this->order->id_cart);

            $params = array("products" => array(), "discounts" => $cart->getCartRules(),
                "order" => $this->order);

            $originalBasket = $this->db->getOrderBasket($this->order->id);

            foreach ($cart->getProducts() as $item) {
                if (isset($this->refundItems[$item["id_product"]]) && $this->refundItems[$item["id_product"]]
                    > 0) {
                    $params["products"][] = array("item" => $item, "quantity" => $this->refundItems[$item["id_product"]]);
                } else if ($this->refundItems == "full") {
                    $params["products"][] = array("item" => $item, "quantity" => $item["cart_quantity"]);
                }
            }

            $cart = new CartMaintenanceFormatter($this->module, $params);

            $maintenance->basket = $cart->generate();

            $maintenance->amount = 0;

            foreach ($maintenance->basket->getAllItems() as $item) {
                if ($item->getType() == "good") {
                    //save capture items and quantity in prestashop
                    $captureData = array($this->order->id, str_replace('good_',
                            '', $item->getProductReference()), '"'.$this->operation.'"',
                        $item->getQuantity(), Tools::ps_round($item->getTotalAmount(),
                            2));
                    $this->db->setCaptureOrRefundOrder($captureData);
                }
                $maintenance->amount += $item->getTotalAmount();
            }

            $maintenance->amount = Tools::ps_round($maintenance->amount, 3);
            $maintenance->basket = $maintenance->basket->toJson();
        }
    }
}