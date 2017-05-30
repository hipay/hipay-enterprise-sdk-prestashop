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
require_once(dirname(__FILE__) . '/../../../lib/vendor/autoload.php');
require_once(dirname(__FILE__) . '/../apiCaller/ApiCaller.php');
require_once(dirname(__FILE__) . '/../apiFormatter/PaymentMethod/CardTokenFormatter.php');
require_once(dirname(__FILE__) . '/../apiFormatter/PaymentMethod/GenericPaymentMethodFormatter.php');

use HiPay\Fullservice\Enum\Transaction\TransactionState;

/**
 * Handle Hipay Api call 
 */
class Apihandler {

    private $module;
    private $context;

    const IFRAME = 'iframe';
    const HOSTEDPAGE = 'hosted_page';
    const DIRECTPOST = 'api';

    public function __construct($moduleInstance, $contextInstance) {
        $this->module = $moduleInstance;
        $this->context = $contextInstance;
    }

    /**
     * handle credit card api call
     * @param type $mode
     * @param type $params
     */
    public function handleCreditCard($mode = Apihandler::HOSTEDPAGE, $params = array()) {

        $cart = $this->context->cart;
        $delivery = new Address((int) $cart->id_address_delivery);
        $deliveryCountry = new Country((int) $delivery->id_country);
        $currency = new Currency((int) $cart->id_currency);

        switch ($mode) {
            case Apihandler::DIRECTPOST:
                $params ["paymentmethod"] = $this->getPaymentMethod($params['cardtoken']);
                $this->handleDirectOrder($params);
                break;
            case Apihandler::IFRAME:

                $params["iframe"] = true;
                $params["productlist"] = $this->getCreditCardProductList($deliveryCountry, $currency, $orderTotal);
                return $this->handleIframe($params);
                break;
            case Apihandler::HOSTEDPAGE:
                $params["iframe"] = true;
                $params["productlist"] = $this->getCreditCardProductList($deliveryCountry, $currency);

                $this->handleHostedPayment($params);
                break;
            default :
                $this->module->getLogs()->logsHipay("Unknown payment mode");
        }
    }

    /**
     * handle all local payment api call
     * @param type $mode
     * @param type $params
     * @return type
     */
    public function handleLocalPayment($mode = Apihandler::HOSTEDPAGE, $params = array()) {

        switch ($mode) {
            case Apihandler::DIRECTPOST:

                $params ["paymentmethod"] = $this->getPaymentMethod($params, false);
                var_dump($params);
                $this->handleDirectOrder($params);
                break;
            case Apihandler::IFRAME :

                return $this->handleIframe($params);
                break;
            case Apihandler::HOSTEDPAGE :

                $this->handleHostedPayment($params);
                break;
            default :
                $this->module->getLogs()->logsHipay("Unknown payment mode");
        }
    }

    /**
     * call Api to get forwarding URL 
     */
    private function handleHostedPayment($params) {
        Tools::redirect(ApiCaller::getHostedPaymentPage($this->module, $params));
    }

    /**
     * return iframe URL
     * @return string
     */
    private function handleIframe($params) {
        return ApiCaller::getHostedPaymentPage($this->module, $params);
    }

    /**
     * call api and redirect to success or error page 
     */
    private function handleDirectOrder($params) {

        $response = ApiCaller::requestDirectPost($this->module, $params);

        $acceptUrl = $this->context->link->getModuleLink($this->module->name, 'validation', array(), true);
        $failUrl = $this->context->link->getModuleLink($this->module->name, 'decline', array(), true);
        $pendingUrl = $this->context->link->getModuleLink($this->module->name, 'pending', array(), true);
        $exceptionUrl = $this->context->link->getModuleLink($this->module->name, 'exception', array(), true);
        $forwardUrl = $response->getForwardUrl();


        switch ($response->getState()) {
            case TransactionState::COMPLETED:
                $redirectUrl = $acceptUrl;
                break;
            case TransactionState::PENDING:
                $redirectUrl = $pendingUrl;
                break;
            case TransactionState::FORWARDING:
                $redirectUrl = $forwardUrl;
                break;
            case TransactionState::DECLINED:
                $reason = $response->getReason();
                $this->module->getLogs()->logsHipay('There was an error request new transaction: ' . $reason['message']);
                $redirectUrl = $failUrl;
                break;
            case TransactionState::ERROR:
                $reason = $response->getReason();
                $this->module->getLogs()->logsHipay('There was an error request new transaction: ' . $reason['message']);
                $redirectUrl = $exceptionUrl;
                break;
            default:
                $redirectUrl = $failUrl;
        }

        var_dump($response);
        
        Tools::redirect($redirectUrl);
    }

    /**
     * return well formatted authorize credit card payment methods 
     * @return string
     */
    private function getCreditCardProductList($deliveryCountry, $currency) {
        $creditCard = $this->module->getActivatedPaymentByCountryAndCurrency("credit_card", $deliveryCountry, $currency);
        $productList = join(",", array_keys($creditCard));
        return $productList;
    }

    /**
     * 
     * @param type $params
     * @param type $creditCard
     * @return mixte
     */
    private function getPaymentMethod($params, $creditCard = true) {

        if ($creditCard) {
            $paymentMethod = new CardTokenFormatter($this->module, $params);
        } else {
            $paymentMethod = new GenericPaymentMethodFormatter($this->module, $params);
        }

        return $paymentMethod->generate();
    }

}
