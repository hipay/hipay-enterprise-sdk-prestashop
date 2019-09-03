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

require_once(dirname(__FILE__) . '/CommonRequestFormatterAbstract.php');
require_once(dirname(__FILE__) . '/../Info/CustomerBillingInfoFormatter.php');
require_once(dirname(__FILE__) . '/../Info/CustomerShippingInfoFormatter.php');
require_once(dirname(__FILE__) . '/../ThreeDS/BrowserInfoFormatter.php');
require_once(dirname(__FILE__) . '/../ThreeDS/MerchantRiskStatementFormatter.php');
require_once(dirname(__FILE__) . '/../ThreeDS/AccountInfoFormatter.php');
require_once(dirname(__FILE__) . '/../ThreeDS/PreviousAuthInfoFormatter.php');
require_once(dirname(__FILE__) . '/../../helper/HipayHelper.php');
require_once(dirname(__FILE__) . '/../../../lib/vendor/autoload.php');

use HiPay\Fullservice\Enum\Transaction\ECI;
use HiPay\Fullservice\Enum\ThreeDSTwo\DeviceChannel;

/**
 *
 * Request formatter abstract
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
abstract class RequestFormatterAbstract extends CommonRequestFormatterAbstract
{
    protected $params;

    public function __construct($moduleInstance, $params, $cart = false)
    {
        parent::__construct($moduleInstance, $cart);
        $this->params = $params;
        $this->moto = (isset($params["moto"]) && $params["moto"]) ? true : false;
    }

    /**
     * map prestashop order informations to request fields (shared information between Hpayment, Iframe and Direct Post)
     * @param \HiPay\Fullservice\Gateway\Request\Order\OrderRequest $order
     */
    protected function mapRequest(&$order)
    {
        parent::mapRequest($order);
        $this->setCustomData($order, $this->cart, $this->params);

        if (in_array(strtolower($this->params["method"]), $this->cardPaymentProduct)) {
            $order->browser_info = $this->getBrowserInfo();
            $order->previous_auth_info = $this->getPreviousAuthInfo();
            $order->merchant_risk_statement = $this->getMerchantRiskStatement();
            $order->account_info = $this->getAccountInfo();
            $order->device_channel = DeviceChannel::BROWSER;

            // Triggering apiRequest hook to allow merchants to add their own data to the request
            Hook::exec('actionHipayApiRequest', array("OrderRequest" => &$order, "Cart" => $this->cart));
        }

        $order->orderid = $this->cart->id . "(" . time() . ")";
        if ($this->moto) {
            $order->eci = ECI::MOTO;
        }

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

        if ($this->moto) {
            //set token for request integrity

            if (_PS_VERSION_ >= '1.7.1.0') {
                $orderId = Order::getIdByCartId($this->cart->id);
            } else {
                $orderId = Order::getOrderByCartId($this->cart->id);
            }

            $token = HipayHelper::getHipayAdminToken('AdminOrders', $orderId);
            $accept_url = HipayHelper::getAdminUrl() .
                $this->context->link->getAdminLink('AdminHiPayMoto') .
                '&hipaytoken=' .
                $token .
                '&hipaystatus=valid&id_order=' .
                (int)$orderId;
            $decline_url = HipayHelper::getAdminUrl() .
                $this->context->link->getAdminLink('AdminHiPayMoto') .
                '&hipaytoken=' .
                $token .
                '&hipaystatus=decline&id_order=' .
                (int)$orderId;
            $pending_url = HipayHelper::getAdminUrl() .
                $this->context->link->getAdminLink('AdminHiPayMoto') .
                '&hipaytoken=' .
                $token .
                '&hipaystatus=pending&id_order=' .
                (int)$orderId;
            $exception_url = HipayHelper::getAdminUrl() .
                $this->context->link->getAdminLink('AdminHiPayMoto') .
                '&hipaytoken=' .
                $token .
                '&hipaystatus=exception&id_order=' .
                (int)$orderId;
            $cancel_url = HipayHelper::getAdminUrl() .
                $this->context->link->getAdminLink('AdminHiPayMoto') .
                '&hipaytoken=' .
                $token .
                '&hipaystatus=cancel&id_order=' .
                (int)$orderId;
        } else {
            //set token for request integrity
            $token = HipayHelper::getHipayToken($this->cart->id, 'validation.php');
            $accept_url = $this->context->link->getModuleLink(
                $this->module->name,
                'validation',
                array("product" => $this->params["method"], "token" => $token),
                true
            );
            $decline_url = $this->context->link->getModuleLink(
                $this->module->name,
                'decline',
                array("token" => $token),
                true
            );
            $pending_url = $this->context->link->getModuleLink(
                $this->module->name,
                'pending',
                array("token" => $token),
                true
            );
            $exception_url = $this->context->link->getModuleLink(
                $this->module->name,
                'exception',
                array("token" => $token),
                true
            );
            $cancel_url = $this->context->link->getModuleLink(
                $this->module->name,
                'cancel',
                array("token" => $token),
                true
            );
        }

        $order->accept_url = $accept_url;
        $order->decline_url = $decline_url;
        $order->pending_url = $pending_url;
        $order->exception_url = $exception_url;
        $order->cancel_url = $cancel_url;

        $config = $this->module->hipayConfigTool->getPaymentGlobal();
        if ($config["send_url_notification"]) {
            $order->notify_url = $this->context->link->getModuleLink(
                $this->module->name,
                'notify',
                array(),
                true
            );
        }

        $order->firstname = $this->customer->firstname;
        $order->lastname = $this->customer->lastname;

        $order->customerBillingInfo = $this->getCustomerBillingInfo();
        $order->customerShippingInfo = $this->getCustomerShippingInfo();
        $order->cid = (int)$this->customer->id;
        $order->ipaddr = Tools::getRemoteAddr();
        $order->language = $this->getLanguageCode($this->context->language->iso_code);
        $order->http_user_agent = $_SERVER ['HTTP_USER_AGENT'];
        $order->basket = $this->params["basket"];
        $order->delivery_information = $this->params["delivery_informations"];
        $order->authentication_indicator = $this->params["authentication_indicator"];
    }

    /**
     * return well formatted order description
     * @param \HiPay\Fullservice\Gateway\Request\Order\OrderRequest $order
     * @return string
     */
    protected function generateDescription($order)
    {
        $description = ''; // Initialize to blank
        foreach ($this->cart->getSummaryDetails(null, true)['products'] as $value) {
            if ($value ['reference']) {
                // Add reference of each product
                $description .= 'ref_' . $value ['reference'] . ', ';
            }
        }

        // Trim trailing seperator
        $description = Tools::substr($description, 0, -2);
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
    private function getCustomerBillingInfo()
    {
        $billingInfo = new CustomerBillingInfoFormatter($this->module, $this->cart, $this->params["method"]);

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

    private function getBrowserInfo()
    {
        $browserInfo = new BrowserInfoFormatter($this->module, $this->cart, $this->params);

        return $browserInfo->generate();
    }

    private function getPreviousAuthInfo()
    {
        $previousAuthInfo = new PreviousAuthInfoFormatter($this->module, $this->cart);

        return $previousAuthInfo->generate();
    }

    private function getMerchantRiskStatement()
    {
        $merchantRiskStatement = new MerchantRiskStatementFormatter($this->module, $this->cart);

        return $merchantRiskStatement->generate();
    }

    private function getAccountInfo()
    {
        $accountInfo = new AccountInfoFormatter($this->module, $this->cart, $this->params);

        return $accountInfo->generate();
    }

}
