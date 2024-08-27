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

require_once(dirname(__FILE__) . '/../apiFormatter/Request/HostedPaymentFormatter.php');
require_once(dirname(__FILE__) . '/../apiFormatter/Request/DirectPostFormatter.php');
require_once(dirname(__FILE__) . '/../apiFormatter/Request/MaintenanceFormatter.php');
require_once(dirname(__FILE__) . '/../exceptions/GatewayException.php');
require_once(dirname(__FILE__) . '/../helper/HipayMaintenanceData.php');
require_once(dirname(__FILE__) . '/../helper/dbquery/HipayDBUtils.php');
require_once(dirname(__FILE__) . '/../helper/dbquery/HipayDBThreeDSQuery.php');
require_once(dirname(__FILE__) . '/../../lib/vendor/autoload.php');

/**
 * Handle Hipay Api call
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class ApiCaller
{

    /**
     *  Get Security Settings form Backend Hipay
     *
     * @param $moduleInstance
     * @return string
     * @throws GatewayException
     */
    public static function getSecuritySettings($moduleInstance, $plateform)
    {
        $isMoto = false;
        $isApplePay = false;
        try {
            if (
                $plateform == HipayHelper::TEST_MOTO
                || $plateform == HipayHelper::PRODUCTION_MOTO
            ) {
                $isMoto = true;
            } elseif (
                $plateform == HipayHelper::TEST_APPLE_PAY
                || $plateform == HipayHelper::PRODUCTION_APPLE_PAY
            ) {
                $isApplePay = true;
            }

            // HiPay Gateway
            $gatewayClient = ApiCaller::createGatewayClient($moduleInstance, $isMoto, $plateform, $isApplePay);

            $response = $gatewayClient->requestSecuritySettings();

            $moduleInstance->getLogs()->logInfos("# RequestSecuritySettings for ${plateform}");

            return $response;
        } catch (Exception $e) {
            $moduleInstance->getLogs()->logException($e);
            throw new GatewayException(
                Context::getContext(),
                $moduleInstance,
                'An error occured during request requestSecuritySettings. Please Retry later. Reason [' .
                    $e->getMessage() .
                    ']',
                $e->getCode(),
                null
            );
        }
    }

    /**
     * return hosted payment page URL for forwarding
     *
     * @param $moduleInstance
     * @param $params
     * @param bool $cart
     * @param bool $moto
     * @return string
     * @throws GatewayException
     */
    public static function getHostedPaymentPage($moduleInstance, $params, $cart = false, $moto = false)
    {
        try {
            // HiPay Gateway
            $gatewayClient = ApiCaller::createGatewayClient($moduleInstance, $moto);

            //Set data to send to the API
            $hostedPaymentFormatter = new HostedPaymentFormatter($moduleInstance, $params, $cart);

            $orderRequest = $hostedPaymentFormatter->generate();
            $moduleInstance->getLogs()->logRequest($orderRequest, 'HostedPage');
            //Make a request and return \HiPay\Fullservice\Gateway\Model\Transaction.php object
            $transaction = $gatewayClient->requestHostedPaymentPage($orderRequest);
            $moduleInstance->getLogs()->logInfos("# RequestHostedPaymentPage " . $orderRequest->orderid);
            $moduleInstance->getLogs()->logCallback($transaction, 'HostedPage', $orderRequest->orderid);

            return $transaction->getForwardUrl();
        } catch (Exception $e) {
            $moduleInstance->getLogs()->logException($e);
            throw new GatewayException(
                Context::getContext(),
                $moduleInstance,
                'An error occured during request getHostedPaymentPage. Please Retry later. Reason [' .
                    $e->getMessage() .
                    ']',
                $e->getCode(),
                null
            );
        }
    }

    /**
     * return transaction from Direct Post Api call
     *
     * @param $moduleInstance
     * @param $params
     * @return \HiPay\Fullservice\Gateway\Model\Transaction|\HiPay\Fullservice\Model\AbstractModel
     * @throws GatewayException
     */
    public static function requestDirectPost($moduleInstance, $params)
    {
        try {
            $params['isApplePay'] = isset($params['isApplePay']) && $params['isApplePay'] == true;
            // Gateway
            $gatewayClient = ApiCaller::createGatewayClient($moduleInstance, false, false, $params['isApplePay']);

            //Set data to send to the API
            $directPostFormatter = new DirectPostFormatter($moduleInstance, $params);
            $orderRequest = $directPostFormatter->generate();
            $moduleInstance->getLogs()->logRequest($orderRequest);

            //Make a request and return \HiPay\Fullservice\Gateway\Model\Transaction.php object
            $response = $gatewayClient->requestNewOrder($orderRequest);
            $moduleInstance->getLogs()->logCallback($response);

            return $response;
        } catch (Exception $e) {
            $moduleInstance->getLogs()->logException($e);
            throw new GatewayException(
                Context::getContext(),
                $moduleInstance,
                'An error occured during request requestDirectPost. Please Retry later. Reason [' .
                    $e->getMessage() . ']',
                $e->getCode(),
                null
            );
        }
    }

    /**
     * Request capture or refund to HiPay API
     *
     * @param $moduleInstance
     * @param $params
     * @param $eci
     * @return \HiPay\Fullservice\Gateway\Model\Operation|\HiPay\Fullservice\Model\AbstractModel
     * @throws GatewayException
     */
    public static function requestMaintenance($moduleInstance, $params, $eci = null)
    {
        try {
            $hipayDBMaintenance = new HipayDBMaintenance($moduleInstance);
            $transaction = $hipayDBMaintenance->getTransactionByRef($params["transaction_reference"]);

            if (is_null($eci)) {
                $eci = $transaction['eci'];
            }

            //Create your gateway client
            $gatewayClient = ApiCaller::createGatewayClient($moduleInstance, (\HiPay\Fullservice\Enum\Transaction\ECI::MOTO == $eci));

            //Manage maintenance data local storage
            $maintenanceData = new HipayMaintenanceData($moduleInstance);

            //Set data to send to the API
            $maintenanceFormatter = new MaintenanceFormatter($moduleInstance, $params, $maintenanceData);

            $maintenanceRequest = $maintenanceFormatter->generate();

            // Use custom operation id to identify notification
            if (isset($params['orderSlipId'])) {
                $maintenanceRequest->operation_id = $params['orderSlipId'];
            }

            $moduleInstance->getLogs()->logRequest($maintenanceRequest, $params["operation"], $params["transaction_reference"]);

            //Make a request and return \HiPay\Fullservice\Gateway\Model\OperationResponse.php object
            $operation = $gatewayClient->requestMaintenanceOperation(
                $params["operation"],
                $params["transaction_reference"],
                $maintenanceRequest->amount,
                null,
                $maintenanceRequest
            );
            $moduleInstance->getLogs()->logCallback($operation, $params["operation"]);

            // save maintenance data in db
            if ( $params['duplicate_order'] != 1) {
                $maintenanceData->saveData();
            }

            return $operation;
        } catch (Exception $e) {
            $moduleInstance->getLogs()->logException($e);
            throw new GatewayException(
                Context::getContext(),
                $moduleInstance,
                'An error occured during request Maintenance. Please Retry later. Reason [' .
                    $e->getMessage() . ']',
                $e->getCode(),
                null
            );
        }
    }

    /**
     * create gateway client from config and client provider
     * @param type $moduleInstance
     * @param boolean $moto
     * @param boolean|string $forceConfig
     * @param boolean $isApplePay
     * @return \HiPay\Fullservice\Gateway\Client\GatewayClient
     */
    private static function createGatewayClient($moduleInstance, $moto = false, $forceConfig = false, $isApplePay = false)
    {
        $sandbox = false;
        $proxy = array();

        if (!$forceConfig) {
            $sandbox = $moduleInstance->hipayConfigTool->getAccountGlobal()["sandbox_mode"];
        } else {
            // Some calls do not take into account the general configuration (Security Settings)
            if (
                is_string($forceConfig)
                && $forceConfig == HipayHelper::TEST
                || $forceConfig == HipayHelper::TEST_MOTO
                || $forceConfig == HipayHelper::TEST_APPLE_PAY
            ) {
                $sandbox = true;
            }
        }

        if ($moduleInstance->hipayConfigTool->getAccountGlobal()["host_proxy"] !== "") {
            $proxy = array(
                "host" => $moduleInstance->hipayConfigTool->getAccountGlobal()["host_proxy"],
                "port" => $moduleInstance->hipayConfigTool->getAccountGlobal()["port_proxy"],
                "user" => $moduleInstance->hipayConfigTool->getAccountGlobal()["user_proxy"],
                "password" => $moduleInstance->hipayConfigTool->getAccountGlobal()["password_proxy"]
            );
        }

        if (
            $moto &&
            (
                (
                    $sandbox
                    && !empty($moduleInstance->hipayConfigTool->getAccountSandbox()["api_moto_username_sandbox"])
                    && !empty($moduleInstance->hipayConfigTool->getAccountSandbox()["api_moto_password_sandbox"])
                )
                || (
                    !$sandbox
                    && !empty($moduleInstance->hipayConfigTool->getAccountProduction()["api_moto_username_production"])
                    && !empty($moduleInstance->hipayConfigTool->getAccountProduction()["api_moto_password_production"])
                )
            )
        ) {
            $username = ($sandbox) ? $moduleInstance->hipayConfigTool->getAccountSandbox()["api_moto_username_sandbox"]
                : $moduleInstance->hipayConfigTool->getAccountProduction()["api_moto_username_production"];
            $password = ($sandbox) ? $moduleInstance->hipayConfigTool->getAccountSandbox()["api_moto_password_sandbox"]
                : $moduleInstance->hipayConfigTool->getAccountProduction()["api_moto_password_production"];
        } elseif (
            $isApplePay
            && (
                (
                    $sandbox
                    && !empty($moduleInstance->hipayConfigTool->getAccountSandbox()["api_apple_pay_username_sandbox"])
                    && !empty($moduleInstance->hipayConfigTool->getAccountSandbox()["api_apple_pay_password_sandbox"])
                )
                || (
                    !$sandbox
                    && !empty($moduleInstance->hipayConfigTool->getAccountProduction()["api_apple_pay_username_production"])
                    && !empty($moduleInstance->hipayConfigTool->getAccountProduction()["api_apple_pay_password_production"])
                )
            )
        ) {
            $username = ($sandbox) ? $moduleInstance->hipayConfigTool->getAccountSandbox()["api_apple_pay_username_sandbox"]
                : $moduleInstance->hipayConfigTool->getAccountProduction()["api_apple_pay_username_production"];
            $password = ($sandbox) ? $moduleInstance->hipayConfigTool->getAccountSandbox()["api_apple_pay_password_sandbox"]
                : $moduleInstance->hipayConfigTool->getAccountProduction()["api_apple_pay_password_production"];
        } else {
            $username = ($sandbox) ? $moduleInstance->hipayConfigTool->getAccountSandbox()["api_username_sandbox"]
                : $moduleInstance->hipayConfigTool->getAccountProduction()["api_username_production"];
            $password = ($sandbox) ? $moduleInstance->hipayConfigTool->getAccountSandbox()["api_password_sandbox"]
                : $moduleInstance->hipayConfigTool->getAccountProduction()["api_password_production"];
        }

        $env = ($sandbox) ? HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENV_STAGE
            : HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENV_PRODUCTION;

        $config = new \HiPay\Fullservice\HTTP\Configuration\Configuration(
            array(
                "apiUsername" => $username,
                "apiPassword" => $password,
                "apiEnv" => $env,
                "proxy" => $proxy,
                "hostedPageV2" => $moduleInstance->hipayConfigTool->getPaymentGlobal()["enable_api_v2"] ? true : false
            )
        );

        //Instantiate client provider with configuration object
        $clientProvider = new \HiPay\Fullservice\HTTP\SimpleHTTPClient($config);

        //Create your gateway client
        return new \HiPay\Fullservice\Gateway\Client\GatewayClient($clientProvider);
    }
}