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
require_once(dirname(__FILE__).'/../../../../lib/vendor/autoload.php');

abstract class CommonRequestFormatterAbstract extends ApiFormatterAbstract
{

    /**
     * map prestashop order informations to request fields (shared information between Hpayment, Iframe, Direct Post and Maintenance )
     * @param type $order
     */
    protected function mapRequest(&$request)
    {
        $source = array(
            "source" => "CMS",
            "brand" => "prestashop",
            "brand_version" => _PS_VERSION_,
            "integration_version" => $this->module->version,
        );

        $request->source = Tools::jsonEncode($source);
    }

    protected function setCustomData(&$request, $cart, $params)
    {

        var_dump($params);
        $cartSummary = $cart->getSummaryDetails();

        $customer = new Customer($cartSummary["delivery"]->id_customer);
        $group    = new Group($customer->id_default_group);
        $iframe   = ($this->configHipay["payment"]["global"]["operating_mode"] === "iframe")
                ? 1 : 0;

        $paymentCode = "hipay_hosted";

        if (isset($this->params["method"])) {
            $paymentCode = $this->params["method"];
        } else if (isset($this->params["productlist"]) && !is_array($this->params["productlist"])) {
            $paymentCode = $this->params["productlist"];
        }

        $customData = array(
            "shipping_description" => $cartSummary["carrier"]->name,
            "customer_code" => array_shift($group->name),
            "payment_code" => $paymentCode,
            "display_iframe" => $iframe,
        );

        $request->custom_data = Tools::jsonEncode($customData);
    }
}