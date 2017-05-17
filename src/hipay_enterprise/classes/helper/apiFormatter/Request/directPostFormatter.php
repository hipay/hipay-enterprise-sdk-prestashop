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
require_once(_PS_ROOT_DIR_ . _MODULE_DIR_ . 'hipay_enterprise/classes/helper/apiFormatter/apiFormatterAbstract.php');
require_once(_PS_ROOT_DIR_ . _MODULE_DIR_ . 'hipay_enterprise/classes/helper/apiFormatter/Info/customerBillingInfoFormatter.php');
require_once(_PS_ROOT_DIR_ . _MODULE_DIR_ . 'hipay_enterprise/lib/vendor/autoload.php');

class directPostFormatter extends apiFormatterAbstract {

    /**
     * 
     * @return \HiPay\Fullservice\Gateway\Request\Order\OrderRequest
     */
    public function generate() {

        $order = new \HiPay\Fullservice\Gateway\Request\Order\OrderRequest();

        $this->mapRequest($order);

        return $order;
    }

    /**
     * 
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

        $order->accept_url = $this->context->link->getModuleLink($this->module->name, 'validation', array(), true);
        $order->decline_url = $this->context->link->getModuleLink($this->module->name, 'validation', array(), true);
        $order->pending_url = $this->context->link->getModuleLink($this->module->name, 'validation', array(), true);
        $order->exception_url = $this->context->link->getModuleLink($this->module->name, 'validation', array(), true);
        $order->cancel_url = $this->context->link->getModuleLink($this->module->name, 'validation', array(), true);
        $order->customerBillingInfo = $this->getCustomerBillingInfo();
        $order->firstname = "Jean-Michel";
        $order->lastname = "Test";
    }

    /**
     * 
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

    private function getCustomerBillingInfo(){
        
        $billingInfo = new customerBillingInfoFormatter($this->module);
        
        return $billingInfo->generate();
    }
    
}
