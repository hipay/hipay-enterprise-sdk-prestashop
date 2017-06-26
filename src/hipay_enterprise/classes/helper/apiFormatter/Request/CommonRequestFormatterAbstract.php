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

    protected function setCustomData(&$request, $cart)
    {

        var_dump($cart->getSummaryDetails());
        $cartSummary = $cart->getSummaryDetails();

        $customer = new Customer($cartSummary["delivery"]->id_customer);
        $group    = new Group($customer->id_default_group);
        $iframe   = ($this->configHipay["payment"]["global"]["operating_mode"] === "iframe")
                ? 1 : 0;

        $customData = array(
            "shipping_description" => $cartSummary["carrier"]->name,
            "customer_code" => array_shift($group->name),
            "payment_code" => "",
            "display_iframe" => $iframe,
            "payment_type" => "",
        );

        $request->custom_data = Tools::jsonEncode($customData);
    }
}