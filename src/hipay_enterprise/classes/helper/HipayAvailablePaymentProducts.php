<?php

/**
 * HiPay Enterprise SDK Prestashop.
 *
 * 2017 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright 2017 HiPay
 * @license   https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 */
require_once dirname(__FILE__) . '/../../lib/vendor/autoload.php';

class HipayAvailablePaymentProducts
{
    private static $instance = null;
    private $hipayConfigTool;
    private $apiUsername;
    private $apiPassword;
    private $authorizationHeader;
    private $baseUrl;
    private $config;

    public function __construct($hipayConfigTool)
    {
        $this->hipayConfigTool = $hipayConfigTool;
        $this->config = $hipayConfigTool->getConfigHipay();
        $this->setCredentialsAndUrl();
        $this->generateAuthorizationHeader();
    }

    public static function getInstance($hipayConfigTool)
    {
        if (self::$instance === null) {
            self::$instance = new self($hipayConfigTool);
        }
        return self::$instance;
    }

    private function setCredentialsAndUrl()
    {
        if ($this->config['account']['global']['sandbox_mode']) {
            $this->apiUsername = $this->config['account']['sandbox']['api_username_sandbox'];
            $this->apiPassword = $this->config['account']['sandbox']['api_password_sandbox'];
            $this->baseUrl = 'https://stage-secure-gateway.hipay-tpp.com/rest/v2/';
        } else {
            $this->apiUsername = $this->config['account']['production']['api_username_production'];
            $this->apiPassword = $this->config['account']['production']['api_password_production'];
            $this->baseUrl = 'https://secure-gateway.hipay-tpp.com/rest/v2/';
        }
    }

    private function generateAuthorizationHeader()
    {
        $credentials = $this->apiUsername . ':' . $this->apiPassword;
        $encodedCredentials = base64_encode($credentials);
        $this->authorizationHeader = 'Basic ' . $encodedCredentials;
    }

    public function getAvailablePaymentProducts(
        $paymentProduct = 'paypal',
        $eci = '7',
        $operation = '4',
        $withOptions = 'true'
    ) {
        $url = $this->baseUrl . 'available-payment-products.json';
        $url .= '?eci=' . urlencode($eci);
        $url .= '&operation=' . urlencode($operation);
        $url .= '&payment_product=' . urlencode($paymentProduct);
        $url .= '&with_options=' . urlencode($withOptions);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: ' . $this->authorizationHeader,
            'Accept: application/json'
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }

        curl_close($ch);

        return json_decode($response, true);
    }
}
