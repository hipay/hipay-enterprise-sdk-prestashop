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
require_once(dirname(__FILE__) . '/../apiFormatter/Info/DeliveryShippingInfoFormatter.php');
require_once(dirname(__FILE__) . '/../apiFormatter/Cart/CartFormatter.php');

use HiPay\Fullservice\Enum\Transaction\TransactionState;
use HiPay\Fullservice\Enum\Transaction\Operation;

/**
 * Handle Hipay Api call
 */
class Apihandler
{
    private $module;
    private $context;

    const IFRAME = 'iframe';
    const HOSTEDPAGE = 'hosted_page';
    const DIRECTPOST = 'api';

    public function __construct(
        $moduleInstance,
        $contextInstance
    ) {
        $this->module = $moduleInstance;
        $this->context = $contextInstance;
        $this->configHipay = $this->module->hipayConfigTool->getConfigHipay();
    }

    /**
     * handle credit card api call
     * @param type $mode
     * @param type $params
     */
    public function handleCreditCard(
        $mode = Apihandler::HOSTEDPAGE,
        $params = array()
    ) {
        $this->baseParamsInit($params);

        $cart = $this->context->cart;
        $delivery = new Address((int)$cart->id_address_delivery);
        $deliveryCountry = new Country((int)$delivery->id_country);
        $currency = new Currency((int)$cart->id_currency);

        switch ($mode) {
            case Apihandler::DIRECTPOST:
                $params ["paymentmethod"] = $this->getPaymentMethod($params);
                $this->handleDirectOrder($params);
                break;
            case Apihandler::IFRAME:
                $params["iframe"] = true;
                $params["productlist"] = $this->getCreditCardProductList(
                    $deliveryCountry,
                    $currency
                );
                return $this->handleIframe($params);
            case Apihandler::HOSTEDPAGE:
                $params["iframe"] = true;
                $params["productlist"] = $this->getCreditCardProductList(
                    $deliveryCountry,
                    $currency
                );

                $this->handleHostedPayment($params);
                break;
            default:
                $this->module->getLogs()->logsHipay("Unknown payment mode");
        }
    }

    /**
     * handle all local payment api call
     * @param type $mode
     * @param type $params
     * @return type
     */
    public function handleLocalPayment(
        $mode = Apihandler::HOSTEDPAGE,
        $params = array()
    ) {
        $this->baseParamsInit(
            $params,
            false
        );

        switch ($mode) {
            case Apihandler::DIRECTPOST:
                $params ["paymentmethod"] = $this->getPaymentMethod(
                    $params,
                    false
                );
                var_dump($params);
                $this->handleDirectOrder($params);
                break;
            case Apihandler::IFRAME:
                return $this->handleIframe($params);
            case Apihandler::HOSTEDPAGE:
                $this->handleHostedPayment($params);
                break;
            default:
                $this->module->getLogs()->logsHipay("Unknown payment mode");
        }
    }

    /**
     *
     * @param type $params
     */
    public function handleCapture($params)
    {
        $this->handleMaintenance(
            Operation::CAPTURE,
            $params
        );
    }

    /**
     *
     * @param type $params
     */
    public function handleRefund($params)
    {
        $this->handleMaintenance(
            Operation::REFUND,
            $params
        );
    }

    /**
     *
     * @param type $mode
     * @param type $params
     */
    private function handleMaintenance(
        $mode,
        $params = array()
    ) {
        switch ($mode) {
            case Operation::CAPTURE:
                $params["operation"] = Operation::CAPTURE;
                ApiCaller::requestMaintenance(
                    $this->module,
                    $params
                );
                break;
            case Operation::REFUND:
                $params["operation"] = Operation::REFUND;
                ApiCaller::requestMaintenance(
                    $this->module,
                    $params
                );
                break;
            default:
                $this->module->getLogs()->logsHipay("Unknown maintenance operation");
        }
    }

    /**
     * Init params send to the api caller
     * @param type $params
     * @param type $creditCard
     */
    private function baseParamsInit(
        &$params,
        $creditCard = true
    ) {
        // no basket sent if PS_ROUND_TYPE is ROUND_TOTAL (prestashop config)
        if (Configuration::get('PS_ROUND_TYPE') == Order::ROUND_TOTAL) {
            $params["basket"] = null;
            $params["delivery_informations"] = null;
        } elseif ($creditCard && $this->configHipay["payment"]["global"]["activate_basket"]) {
            $params["basket"] = $this->getCart();
            $params["delivery_informations"] = $this->getDeliveryInformation();
        } elseif ($this->configHipay["payment"]["global"]["activate_basket"] || (isset($params["method"])
                && isset($this->configHipay["payment"]["local_payment"][$params["method"]]["forceBasket"]))
            && $this->configHipay["payment"]["local_payment"][$params["method"]]["forceBasket"]
        ) {
            $params["basket"] = $this->getCart();
            $params["delivery_informations"] = $this->getDeliveryInformation();
        } else {
            $params["basket"] = null;
            $params["delivery_informations"] = null;
        }
    }

    /**
     * return mapped cart
     * @return type
     */
    private function getCart()
    {
        $cart = new CartFormatter($this->module);

        return $cart->generate();
    }

    /**
     * return mapped delivery informations
     * @return type
     */
    private function getDeliveryInformation()
    {
        $deliveryInformation = new DeliveryShippingInfoFormatter($this->module);

        return $deliveryInformation->generate();
    }

    /**
     * call Api to get forwarding URL
     */
    private function handleHostedPayment($params)
    {
        Tools::redirect(
            ApiCaller::getHostedPaymentPage(
                $this->module,
                $params
            )
        );
    }

    /**
     * return iframe URL
     * @return string
     */
    private function handleIframe($params)
    {
        return ApiCaller::getHostedPaymentPage(
            $this->module,
            $params
        );
    }

    /**
     * call api and redirect to success or error page
     */
    private function handleDirectOrder($params)
    {
        $response = ApiCaller::requestDirectPost(
            $this->module,
            $params
        );

        $acceptUrl = $this->context->link->getModuleLink(
            $this->module->name,
            'validation',
            array(),
            true
        );
        $failUrl = $this->context->link->getModuleLink(
            $this->module->name,
            'decline',
            array(),
            true
        );
        $pendingUrl = $this->context->link->getModuleLink(
            $this->module->name,
            'pending',
            array(),
            true
        );
        $exceptionUrl = $this->context->link->getModuleLink(
            $this->module->name,
            'exception',
            array(),
            true
        );
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
                $this->module->getLogs()->logsHipay(
                    'There was an error request new transaction: ' . $reason['message']
                );
                $redirectUrl = $failUrl;
                break;
            case TransactionState::ERROR:
                $reason = $response->getReason();
                $this->module->getLogs()->logsHipay(
                    'There was an error request new transaction: ' . $reason['message']
                );
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
    private function getCreditCardProductList(
        $deliveryCountry,
        $currency
    ) {
        $creditCard = $this->module->getActivatedPaymentByCountryAndCurrency(
            "credit_card",
            $deliveryCountry,
            $currency
        );
        $productList = join(
            ",",
            array_keys($creditCard)
        );
        return $productList;
    }

    /**
     * return mapped payment method
     * @param type $params
     * @param type $creditCard
     * @return mixte
     */
    private function getPaymentMethod(
        $params,
        $creditCard = true
    ) {
        if ($creditCard) {
            $paymentMethod = new CardTokenFormatter(
                $this->module,
                $params
            );
        } else {
            $paymentMethod = new GenericPaymentMethodFormatter(
                $this->module,
                $params
            );
        }

        return $paymentMethod->generate();
    }
}
