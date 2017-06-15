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
    public static function getHostedPaymentPage($moduleInstance, $params) {

        //Create your gateway client
        $gatewayClient = ApiCaller::createGatewayClient($moduleInstance);
        //Set data to send to the API
        $orderRequest = new HostedPaymentFormatter($moduleInstance, $params);

        $moduleInstance->getLogs()->requestLogs(print_r($orderRequest->generate(), true));

        var_dump($orderRequest->generate());
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

        //Create your gateway client
        $gatewayClient = ApiCaller::createGatewayClient($moduleInstance);
        //Set data to send to the API
        $orderRequest = new DirectPostFormatter($moduleInstance, $params);
        $moduleInstance->getLogs()->requestLogs(print_r($orderRequest->generate(), true));
        var_dump($orderRequest->generate());
        //    die();
        //Make a request and return \HiPay\Fullservice\Gateway\Model\Transaction.php object
        $transaction = $gatewayClient->requestNewOrder($orderRequest->generate());

        return $transaction;
    }

    /**
     * create gateway client from config and client provider
     * @param type $moduleInstance
     * @return \HiPay\Fullservice\Gateway\Client\GatewayClient
     */
    private static function createGatewayClient($moduleInstance) {

        if ($moduleInstance->hipayConfigTool->getConfigHipay()["account"]["global"]["sandbox_mode"]) {
            $config = new \HiPay\Fullservice\HTTP\Configuration\Configuration(
                    $moduleInstance->hipayConfigTool->getConfigHipay()["account"]["sandbox"]["api_username_sandbox"], $moduleInstance->hipayConfigTool->getConfigHipay()["account"]["sandbox"]["api_password_sandbox"]
            );
        } else {
            $config = new \HiPay\Fullservice\HTTP\Configuration\Configuration(
                    $moduleInstance->hipayConfigTool->getConfigHipay()["account"]["production"]["api_username_production"], $moduleInstance->hipayConfigTool->getConfigHipay()["account"]["production"]["api_password_production"], HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENV_PRODUCTION
            );
        }

        //Instantiate client provider with configuration object
        $clientProvider = new \HiPay\Fullservice\HTTP\SimpleHTTPClient($config);

        //Create your gateway client
        $gatewayClient = new \HiPay\Fullservice\Gateway\Client\GatewayClient($clientProvider);

        return $gatewayClient;
    }

}
