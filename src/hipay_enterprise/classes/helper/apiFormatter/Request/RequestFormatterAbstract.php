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
require_once(dirname(__FILE__) . '/../ApiFormatterAbstract.php');
require_once(dirname(__FILE__) . '/../Info/CustomerBillingInfoFormatter.php');
require_once(dirname(__FILE__) . '/../Info/CustomerShippingInfoFormatter.php');
require_once(dirname(__FILE__) . '/../../../../lib/vendor/autoload.php');

abstract class RequestFormatterAbstract extends ApiFormatterAbstract {

    protected $params;
    
    public function __construct($moduleInstance, $params){
        parent::__construct($moduleInstance);
        $this->params = $params;
    }
    
    /**
     * map prestashop order informations to request fields (shared information between Hpayment, Iframe and Direct Post)
     * @param type $order
     */
    protected function mapRequest(&$order) {

        $order->orderid = $this->cart->id . "(" . time() . ")";

        if ($this->configHipay["payment"]["global"]["capture_mode"] === "automatic") {
            $order->operation = "Sale";
        } else {
            $order->operation = "Authorization";
        }

        $order->description = $this->generateDescription($order);

        $order->amount = $this->cart->getOrderTotal(true, Cart::BOTH);
        $order->shipping = $this->cart->getSummaryDetails(null, true)['total_shipping'];
        $order->tax = $this->cart->getSummaryDetails(null, true)['total_tax'];

        $order->currency = $this->currency->iso_code;
        $order->accept_url = $this->context->link->getModuleLink($this->module->name, 'validation', array(), true);
        $order->decline_url = $this->context->link->getModuleLink($this->module->name, 'decline', array(), true);
        $order->pending_url = $this->context->link->getModuleLink($this->module->name, 'pending', array(), true);
        $order->exception_url = $this->context->link->getModuleLink($this->module->name, 'exception', array(), true);
        $order->cancel_url = $this->context->link->getModuleLink($this->module->name, 'cancel', array(), true);
        $order->customerBillingInfo = $this->getCustomerBillingInfo();
        $order->customerShippingInfo = $this->getCustomerShippingInfo();
        $order->firstname = $this->customer->firstname;
        $order->lastname = $this->customer->firstname;
        $order->cid = (int) $this->customer->id;
        $order->ipaddr = $_SERVER ['REMOTE_ADDR'];
        $order->language = $this->getLanguageCode($this->context->language->iso_code);
        $order->custom_data = null;
        $order->source = null;
        $order->basket = $this->params["basket"];
        $order->delivery_information = $this->params["delivery_informations"];
    }

    /**
     * return well formatted order descritpion
     * @param type $order
     * @return string
     */
    protected function generateDescription($order) {
        $description = ''; // Initialize to blank
        foreach ($this->cart->getSummaryDetails(null, true)['products'] as $value) {
            if ($value ['reference']) {
                // Add reference of each product
                $description .= 'ref_' . $value ['reference'] . ', ';
            }
        }

        // Trim trailing seperator
        $description = Tools::substr($description, 0, - 2);
        if (Tools::strlen($description) == 0) {
            $description = 'cart_id_' . $order->orderid;
        }
        // If description exceeds 255 char, trim back to 255
        $max_length = 255;
        if (Tools::strlen($description) > $max_length) {
            $offset = ($max_length - 3) - Tools::strlen($description);
            $description = Tools::substr($description, 0, strrpos($description, ' ', $offset)) . '...';
        }

        return $description;
    }

    /**
     * return mapped customer billing informations
     * @return \HiPay\Fullservice\Gateway\Request\Info\CustomerBillingInfoRequest
     */
    private function getCustomerBillingInfo() {

        $billingInfo = new CustomerBillingInfoFormatter($this->module);

        return $billingInfo->generate();
    }

    /**
     * return mapped customer shipping informations
     * @return \HiPay\Fullservice\Gateway\Request\Info\CustomerShippingInfoRequest
     */
    private function getCustomerShippingInfo() {

        $billingInfo = new CustomerShippingInfoFormatter($this->module);

        return $billingInfo->generate();
    }

}
