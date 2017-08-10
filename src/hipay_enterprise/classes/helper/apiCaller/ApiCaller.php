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
require_once(dirname(__FILE__).'/../apiFormatter/Request/HostedPaymentFormatter.php');
require_once(dirname(__FILE__).'/../apiFormatter/Request/DirectPostFormatter.php');
require_once(dirname(__FILE__).'/../apiFormatter/Request/MaintenanceFormatter.php');
require_once(dirname(__FILE__).'/../exceptions/GatewayException.php');
require_once(dirname(__FILE__).'/../tools/hipayMaintenanceData.php');
require_once(dirname(__FILE__).'/../../../lib/vendor/autoload.php');

/**
 * Handle Hipay Api call
 */
class ApiCaller
{

    /**
     * return hosted payment page URL for forwarding
     * @param type $moduleInstance
     * @return type
     */
    public static function getHostedPaymentPage($moduleInstance, $params, $cart = false, $moto = false)
    {
        try {
            // HiPay Gateway
            $gatewayClient = ApiCaller::createGatewayClient($moduleInstance,
                    $moto);

            //Set data to send to the API
            $hostedPaymentFormatter = new HostedPaymentFormatter(
                $moduleInstance,
                $params,
                $cart
            );

            $orderRequest = $hostedPaymentFormatter->generate();
            $moduleInstance->getLogs()->logRequest($orderRequest);

            //Make a request and return \HiPay\Fullservice\Gateway\Model\Transaction.php object
            $transaction = $gatewayClient->requestHostedPaymentPage($orderRequest);
            $moduleInstance->getLogs()->logInfos("# RequestHostedPaymentPage ".$orderRequest->orderid);

            return $transaction->getForwardUrl();
        } catch (Exception $e) {
            $moduleInstance->getLogs()->logException($e);
            throw new GatewayException('An error occured during request requestDirectPost. Please Retry later. Reason ['.
            $e->getMessage().']',
            $e->getCode(),
            null,
            Context::getContext(),
            $moduleInstance);
        }
    }

    /**
     * return transaction from Direct Post Api call
     * @param type $moduleInstance
     * @param type $cardToken
     * @return type
     */
    public static function requestDirectPost($moduleInstance, $params)
    {
        try {
            // Gateway
            $gatewayClient = ApiCaller::createGatewayClient($moduleInstance);

            //Set data to send to the API
            $directPostFormatter = new DirectPostFormatter(
                $moduleInstance,
                $params
            );

            // @var \HiPay\Fullservice\Gateway\Request\Order\OrderRequest
            $orderRequest = $directPostFormatter->generate();
            $moduleInstance->getLogs()->logRequest($orderRequest);

            //Make a request and return \HiPay\Fullservice\Gateway\Model\Transaction.php object
            $transaction = $gatewayClient->requestNewOrder($orderRequest);

            return $transaction;
        } catch (Exception $e) {
            $moduleInstance->getLogs()->logException($e);
            throw new GatewayException('An error occured during request requestDirectPost. Please Retry later. Reason ['.
            $e->getMessage().']',
            $e->getCode(),
            null,
            Context::getContext(),
            $moduleInstance);
        }
    }

    /**
     * Request capture or refund to HiPay API
     *
     * @param type $moduleInstance
     * @param type $params
     * @return type
     */
    public static function requestMaintenance($moduleInstance, $params)
    {
        try {
            //Create your gateway client
            $gatewayClient = ApiCaller::createGatewayClient($moduleInstance);

            //Manage maintenance data local storage
            $maintenanceData = new HipayMaintenanceData($moduleInstance);

            //Set data to send to the API
            $maintenanceFormatter = new MaintenanceFormatter(
                $moduleInstance,
                $params,
                $maintenanceData
            );

            $maintenanceRequest = $maintenanceFormatter->generate();
            $moduleInstance->getLogs()->logRequest($maintenanceRequest);
            //Make a request and return \HiPay\Fullservice\Gateway\Model\Transaction.php object
            $transaction = $gatewayClient->requestMaintenanceOperation(
                $params["operation"],
                $params["transaction_reference"],
                $maintenanceRequest->amount,
                null,
                $maintenanceRequest
            );
            // save maintenance data in db
            $maintenanceData->saveData();
            return $transaction;
        } catch (Exception $e) {
            $moduleInstance->getLogs()->logException($e);
            throw new GatewayException('An error occured during request Maintenance. Please Retry later. Reason ['.
            $e->getMessage().']',
            $e->getCode(),
            null,
            Context::getContext(),
            $moduleInstance);

        }
    }

    /**
     * create gateway client from config and client provider
     * @param type $moduleInstance
     * @return \HiPay\Fullservice\Gateway\Client\GatewayClient
     */
    private static function createGatewayClient($moduleInstance, $moto = false)
    {
        $sandbox = $moduleInstance->hipayConfigTool->getConfigHipay()["account"]["global"]["sandbox_mode"];

        if ($moto && !empty($moduleInstance->hipayConfigTool->getConfigHipay()["account"]["sandbox"]["api_moto_username_sandbox"])
            && !empty($moduleInstance->hipayConfigTool->getConfigHipay()["account"]["sandbox"]["api_moto_password_sandbox"])) {

            $username = ($sandbox) ? $moduleInstance->hipayConfigTool->getConfigHipay()["account"]["sandbox"]["api_moto_username_sandbox"]
                    : $moduleInstance->hipayConfigTool->getConfigHipay()["account"]["production"]["api_moto_username_production"];
            $password = ($sandbox) ? $moduleInstance->hipayConfigTool->getConfigHipay()["account"]["sandbox"]["api_moto_password_sandbox"]
                    : $moduleInstance->hipayConfigTool->getConfigHipay()["account"]["production"]["api_moto_password_production"];
        } else {

            $username = ($sandbox) ? $moduleInstance->hipayConfigTool->getConfigHipay()["account"]["sandbox"]["api_username_sandbox"]
                    : $moduleInstance->hipayConfigTool->getConfigHipay()["account"]["production"]["api_username_production"];
            $password = ($sandbox) ? $moduleInstance->hipayConfigTool->getConfigHipay()["account"]["sandbox"]["api_password_sandbox"]
                    : $moduleInstance->hipayConfigTool->getConfigHipay()["account"]["production"]["api_password_production"];
        }

        $env = ($sandbox) ? HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENV_STAGE : HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENV_PRODUCTION;

        $config = new \HiPay\Fullservice\HTTP\Configuration\Configuration($username,
            $password,
            $env);

        //Instantiate client provider with configuration object
        $clientProvider = new \HiPay\Fullservice\HTTP\SimpleHTTPClient($config);

        //Create your gateway client
        $gatewayClient = new \HiPay\Fullservice\Gateway\Client\GatewayClient($clientProvider);

        return $gatewayClient;
    }
}