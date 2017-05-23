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
require_once(dirname(__FILE__) . '/../apiFormatter/Request/HostedPaymentFormatter.php');
require_once(dirname(__FILE__) . '/../apiFormatter/Request/DirectPostFormatter.php');
require_once(dirname(__FILE__) . '/../../../lib/vendor/autoload.php');

/**
 * Handle Hipay Api call 
 */
class ApiCaller {

    /**
     * return hosted payment page URL for forwarding
     * @param type $moduleInstance
     * @return type
     */
    public static function getHostedPaymentPage($moduleInstance) {

        $config = new \HiPay\Fullservice\HTTP\Configuration\Configuration(
                $moduleInstance->hipayConfigTool->getConfigHipay()["account"]["sandbox"]["api_username_sandbox"], $moduleInstance->hipayConfigTool->getConfigHipay()["account"]["sandbox"]["api_password_sandbox"]
        );
        //Instantiate client provider with configuration object
        $clientProvider = new \HiPay\Fullservice\HTTP\SimpleHTTPClient($config);

        //Create your gateway client
        $gatewayClient = new \HiPay\Fullservice\Gateway\Client\GatewayClient($clientProvider);
        //Set data to send to the API
        $orderRequest = new HostedPaymentFormatter($moduleInstance);
        //Make a request and return \HiPay\Fullservice\Gateway\Model\Transaction.php object
        $transaction = $gatewayClient->requestHostedPaymentPage($orderRequest->generate());

        return $transaction->getForwardUrl();
    }

    /**
     * return transaction from Direct Post Api call
     * @param type $moduleInstance
     * @param type $cardToken
     * @return type
     */
    public static function requestDirectPost($moduleInstance, $params) {

        $config = new \HiPay\Fullservice\HTTP\Configuration\Configuration(
                $moduleInstance->hipayConfigTool->getConfigHipay()["account"]["sandbox"]["api_username_sandbox"], $moduleInstance->hipayConfigTool->getConfigHipay()["account"]["sandbox"]["api_password_sandbox"]
        );
        //Instantiate client provider with configuration object
        $clientProvider = new \HiPay\Fullservice\HTTP\SimpleHTTPClient($config);

        //Create your gateway client
        $gatewayClient = new \HiPay\Fullservice\Gateway\Client\GatewayClient($clientProvider);
        //Set data to send to the API
        $orderRequest = new DirectPostFormatter($moduleInstance, $params);
        //Make a request and return \HiPay\Fullservice\Gateway\Model\Transaction.php object
        $transaction = $gatewayClient->requestNewOrder($orderRequest->generate());

        return $transaction;
    }

}
