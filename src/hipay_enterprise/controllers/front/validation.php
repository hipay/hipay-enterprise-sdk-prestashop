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
class Hipay_enterpriseValidationModuleFrontController extends ModuleFrontController {

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess() {
        $this->display_column_left = false;
        $this->display_column_right = false;
        parent::initContent();

        $context = Context::getContext();

        $config = new \HiPay\Fullservice\HTTP\Configuration\Configuration("username", "password");
        //Instantiate client provider with configuration object
        $clientProvider = new \HiPay\Fullservice\HTTP\SimpleHTTPClient($config);

        //Create your gateway client
        $gatewayClient = new \HiPay\Fullservice\Gateway\Client\GatewayClient($clientProvider);

        //Instantiate order request
        $orderRequest = new \HiPay\Fullservice\Gateway\Request\Order\OrderRequest();
        $orderRequest->orderid = "123456";
        $orderRequest->operation = "Sale";
        //etc.
        //Make a request and return \HiPay\Fullservice\Gateway\Model\Transaction.php object
        $transaction = $gatewayClient->requestNewOrder($orderRequest);
    }

}

require_once(_PS_ROOT_DIR_ . _MODULE_DIR_ . 'hipay_enterprise/lib/vendor/autoload.php');
