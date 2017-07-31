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
require_once(dirname(__FILE__).'/CommonRequestFormatterAbstract.php');
require_once(dirname(__FILE__).'/../Info/CustomerBillingInfoFormatter.php');
require_once(dirname(__FILE__).'/../Info/CustomerShippingInfoFormatter.php');
require_once(dirname(__FILE__).'/../../tools/hipayHelper.php');
require_once(dirname(__FILE__).'/../../../../lib/vendor/autoload.php');

use HiPay\Fullservice\Enum\Transaction\ECI;

abstract class RequestFormatterAbstract extends CommonRequestFormatterAbstract
{
    protected $params;

    public function __construct(
    $moduleInstance, $params, $cart = false
    )
    {
        parent::__construct($moduleInstance,
                            $cart);
        $this->params = $params;
        $this->moto   = (isset($params["moto"]) && $params["moto"]) ? true : false;
    }

    /**
     * map prestashop order informations to request fields (shared information between Hpayment, Iframe and Direct Post)
     * @param type $order
     */
    protected function mapRequest(&$order)
    {
        parent::mapRequest($order);
        $this->setCustomData(
            $order,
            $this->cart,
            $this->params
        );

        $order->orderid = $this->cart->id."(".time().")";
        if ($this->moto) {
            $order->eci = ECI::MOTO;
        }

        if ($this->configHipay["payment"]["global"]["capture_mode"] === "automatic") {
            $order->operation = "Sale";
        } else {
            $order->operation = "Authorization";
        }

        $order->description = $this->generateDescription($order);

        $order->amount   = $this->cart->getOrderTotal(
            true,
            Cart::BOTH
        );
        $order->shipping = $this->cart->getSummaryDetails(
                null,
                true
            )['total_shipping'];
        $order->tax      = $this->cart->getSummaryDetails(
                null,
                true
            )['total_tax'];

        $order->currency = $this->currency->iso_code;

        if ($this->moto) {
            $accept_url    = HipayHelper::getAdminUrl().$this->context->link->getAdminLink('AdminHiPayMoto').'&hipaystatus=valid&id_order='.(int) Order::getOrderByCartId($this->cart->id);
            $decline_url   = HipayHelper::getAdminUrl().$this->context->link->getAdminLink('AdminHiPayMoto').'&hipaystatus=decline&id_order='.(int) Order::getOrderByCartId($this->cart->id);
            $pending_url   = HipayHelper::getAdminUrl().$this->context->link->getAdminLink('AdminHiPayMoto').'&hipaystatus=pending&id_order='.(int) Order::getOrderByCartId($this->cart->id);
            $exception_url = HipayHelper::getAdminUrl().$this->context->link->getAdminLink('AdminHiPayMoto').'&hipaystatus=exception&id_order='.(int) Order::getOrderByCartId($this->cart->id);
            $cancel_url    = HipayHelper::getAdminUrl().$this->context->link->getAdminLink('AdminHiPayMoto').'&hipaystatus=cancel&id_order='.(int) Order::getOrderByCartId($this->cart->id);
        } else {
            $accept_url    = $this->context->link->getModuleLink(
                $this->module->name,
                'validation',
                array("product" => $this->params["method"]),
                true
            );
            $decline_url   = $this->context->link->getModuleLink(
                $this->module->name,
                'decline',
                array(),
                true
            );
            $pending_url   = $this->context->link->getModuleLink(
                $this->module->name,
                'pending',
                array(),
                true
            );
            $exception_url = $this->context->link->getModuleLink(
                $this->module->name,
                'exception',
                array(),
                true
            );
            $cancel_url    = $this->context->link->getModuleLink(
                $this->module->name,
                'cancel',
                array(),
                true
            );
        }

        $order->accept_url    = $accept_url;
        $order->decline_url   = $decline_url;
        $order->pending_url   = $pending_url;
        $order->exception_url = $exception_url;
        $order->cancel_url    = $cancel_url;

        $order->customerBillingInfo  = $this->getCustomerBillingInfo();
        $order->customerShippingInfo = $this->getCustomerShippingInfo();
        $order->firstname            = $this->customer->firstname;
        $order->lastname             = $this->customer->firstname;
        $order->cid                  = (int) $this->customer->id;
        $order->ipaddr               = $_SERVER ['REMOTE_ADDR'];
        $order->language             = $this->getLanguageCode($this->context->language->iso_code);
        $order->basket               = $this->params["basket"];
        $order->delivery_information = $this->params["delivery_informations"];
    }

    /**
     * return well formatted order descritpion
     * @param type $order
     * @return string
     */
    protected function generateDescription($order)
    {
        $description = ''; // Initialize to blank
        foreach ($this->cart->getSummaryDetails(
            null,
            true
        )['products'] as $value) {
            if ($value ['reference']) {
                // Add reference of each product
                $description .= 'ref_'.$value ['reference'].', ';
            }
        }

        // Trim trailing seperator
        $description = Tools::substr(
                $description,
                0,
                -2
        );
        if (Tools::strlen($description) == 0) {
            $description = 'cart_id_'.$order->orderid;
        }
        // If description exceeds 255 char, trim back to 255
        $max_length = 255;
        if (Tools::strlen($description) > $max_length) {
            $offset      = ($max_length - 3) - Tools::strlen($description);
            $description = Tools::substr(
                    $description,
                    0,
                    strrpos(
                        $description,
                        ' ',
                        $offset
                    )
                ).'...';
        }

        return $description;
    }

    /**
     * return mapped customer billing informations
     * @return \HiPay\Fullservice\Gateway\Request\Info\CustomerBillingInfoRequest
     */
    private function getCustomerBillingInfo()
    {
        $billingInfo = new CustomerBillingInfoFormatter($this->module, $this->cart);

        return $billingInfo->generate();
    }

    /**
     * return mapped customer shipping informations
     * @return \HiPay\Fullservice\Gateway\Request\Info\CustomerShippingInfoRequest
     */
    private function getCustomerShippingInfo()
    {
        $billingInfo = new CustomerShippingInfoFormatter($this->module, $this->cart);

        return $billingInfo->generate();
    }
}