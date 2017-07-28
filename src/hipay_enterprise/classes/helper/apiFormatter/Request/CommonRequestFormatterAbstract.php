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

    public function __construct($module)
    {
        parent::__construct($module);

        spl_autoload_register(array($this, 'autoloadCustomData'));
    }

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

        $this->module->getLogs()->logInfos('# Process Custom Request source');
        $request->source = Tools::jsonEncode($source);
    }

    /**
     * @param $request
     * @param $cart
     * @param $params
     */
    protected function setCustomData(&$request, $cart, $params)
    {
        $this->module->getLogs()->logInfos('# Process Custom Data Hipay ');
        $cartSummary = $cart->getSummaryDetails();

        $customer = new Customer($cartSummary["delivery"]->id_customer);
        $group    = new Group($customer->id_default_group);
        $iframe   = ($this->configHipay["payment"]["global"]["operating_mode"] === "iframe") ? 1 : 0;

        $paymentCode = "hipay_hosted";
        if (isset($this->params["method"])) {
            $paymentCode = $this->params["method"];
        }

        $customDataHipay = array(
            "shipping_description" => $cartSummary["carrier"]->name,
            "customer_code" => array_shift($group->name),
            "payment_code" => $paymentCode,
            "display_iframe" => $iframe,
        );


        // Add custom data for transaction request
        if (file_exists(dirname(__FILE__).'/../../HipayEnterpriseHelperCustomData.php')) {
            if (class_exists(
                    'HipayEnterpriseHelperCustomData',
                    true
                )) {
                $this->module->getLogs()->logInfos('## Process Custom Data from Custom Files : HipayEnterpriseHelperCustomData');
                $customDataHelper = new HipayEnterpriseHelperCustomData();
                if (method_exists(
                        $customDataHelper,
                        'getCustomData'
                    )) {
                    $customData = $customDataHelper->getCustomData(
                        $cart,
                        $params
                    );
                    if (is_array($customData)) {
                        $customDataHipay = array_merge(
                            $customData,
                            $customDataHipay
                        );
                    }
                }
            }
        }
        $request->custom_data = Tools::jsonEncode($customDataHipay);
    }

    /**
     * AutoloadCustomData
     *
     * @param $class_name
     */
    public function autoloadCustomData($class_name)
    {
        //PS 1.6 fix 
        if (in_array($class_name,array('HipayEnterpriseHelperCustomData'))) {
            require_once dirname(__FILE__).'/../../'.$class_name.'.php';
        }
    }
}