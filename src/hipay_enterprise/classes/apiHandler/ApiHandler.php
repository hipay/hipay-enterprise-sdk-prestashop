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
require_once(dirname(__FILE__).'/../../lib/vendor/autoload.php');
require_once(dirname(__FILE__).'/../apiCaller/ApiCaller.php');
require_once(dirname(__FILE__).'/../apiFormatter/PaymentMethod/CardTokenFormatter.php');
require_once(dirname(__FILE__).'/../apiFormatter/PaymentMethod/GenericPaymentMethodFormatter.php');
require_once(dirname(__FILE__).'/../apiFormatter/Info/DeliveryShippingInfoFormatter.php');
require_once(dirname(__FILE__).'/../apiFormatter/Cart/CartFormatter.php');
require_once(dirname(__FILE__).'/../helper/HipayDBQuery.php');
require_once(dirname(__FILE__).'/../helper/HipayHelper.php');

use HiPay\Fullservice\Enum\Transaction\TransactionState;
use HiPay\Fullservice\Enum\Transaction\Operation;

/**
 * Handle Hipay Api call
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link 	https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class Apihandler
{
    private $module;
    private $context;

    const IFRAME     = 'iframe';
    const HOSTEDPAGE = 'hosted_page';
    const DIRECTPOST = 'api';

    public function __construct(
    $moduleInstance, $contextInstance
    )
    {
        $this->module      = $moduleInstance;
        $this->context     = $contextInstance;
        $this->configHipay = $this->module->hipayConfigTool->getConfigHipay();
        $this->db          = new HipayDBQuery($this->module);
    }

    /**
     *
     * @param type $cart
     */
    public function handleMoto($cart)
    {
        $delivery        = new Address((int) $cart->id_address_delivery);
        $deliveryCountry = new Country((int) $delivery->id_country);
        $currency        = new Currency((int) $cart->id_currency);

        $params["method"]                   = "credit_card";
        $params["moto"]                     = true;
        $params["iframe"]                   = false;
        $params["authentication_indicator"] = 0;
        $params["productlist"]              = $this->getCreditCardProductList(
            $deliveryCountry,
            $currency
        );

        $this->baseParamsInit($params,
            true,
            $cart);

        $this->handleHostedPayment($params,
            $cart,
            true);
    }

    /**
     * handle credit card api call
     * @param type $mode
     * @param type $params
     */
    public function handleCreditCard(
    $mode = Apihandler::HOSTEDPAGE, $params = array()
    )
    {
        $this->baseParamsInit($params);
        $cart            = $this->context->cart;
        $delivery        = new Address((int) $cart->id_address_delivery);
        $deliveryCountry = new Country((int) $delivery->id_country);
        $currency        = new Currency((int) $cart->id_currency);

        switch ($mode) {
            case Apihandler::DIRECTPOST:
                $params ["paymentmethod"] = $this->getPaymentMethod($params);
                $this->handleDirectOrder($params,
                    true);
                break;
            case Apihandler::IFRAME:
                $params["iframe"]         = true;
                $params["productlist"]    = $this->getCreditCardProductList(
                    $deliveryCountry,
                    $currency
                );
                return $this->handleIframe($params);
            case Apihandler::HOSTEDPAGE:
                $params["iframe"]         = true;
                $params["productlist"]    = $this->getCreditCardProductList(
                    $deliveryCountry,
                    $currency
                );

                $this->handleHostedPayment($params);
                break;
            default:
                $this->module->getLogs()->logInfos("# Unknown payment mode $mode");
        }
    }

    /**
     * handle all local payment api call
     * @param type $mode
     * @param type $params
     * @return type
     */
    public function handleLocalPayment(
    $mode = Apihandler::HOSTEDPAGE, $params = array()
    )
    {
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
                $this->handleDirectOrder($params);
                break;
            case Apihandler::IFRAME:
                return $this->handleIframe($params);
            case Apihandler::HOSTEDPAGE:
                $this->handleHostedPayment($params);
                break;
            default:
                $this->module->getLogs()->logInfos("# Unknown payment mode");
        }
    }

    /**
     *
     * @param type $params
     */
    public function handleCapture($params)
    {
        return $this->handleMaintenance(
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
        return $this->handleMaintenance(
                Operation::REFUND,
                $params
        );
    }

    /**
     * Accept any challenge
     *
     * @param type $params
     */
    public function handleAcceptChallenge($params)
    {
        return $this->handleMaintenance(
                Operation::ACCEPT_CHALLENGE,
                $params
        );
    }

    /**
     * Accept any challenge
     *
     * @param type $params
     */
    public function handleDenyChallenge($params)
    {
        return $this->handleMaintenance(
                Operation::DENY_CHALLENGE,
                $params
        );
    }

    /**
     *
     * @param type $mode
     * @param type $params
     */
    private function handleMaintenance($mode, $params = array())
    {
        try {
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
                case Operation::ACCEPT_CHALLENGE:
                    $params["operation"] = Operation::ACCEPT_CHALLENGE;
                    ApiCaller::requestMaintenance(
                        $this->module,
                        $params
                    );
                    break;
                case Operation::DENY_CHALLENGE:
                    $params["operation"] = Operation::DENY_CHALLENGE;
                    ApiCaller::requestMaintenance(
                        $this->module,
                        $params
                    );
                    break;
                default:
                    $this->module->getLogs()->logInfos("# Unknown maintenance operation");
            }
            return true;
        } catch (GatewayException $e) {
            $errorMessage = $this->module->l(
                'An error occured during request Maintenance.',
                'capture'
            );
            $this->context->cookie->__set('hipay_errors',
                $errorMessage);
            return false;
        }
    }

    /**
     * Init params send to the api caller
     * @param type $params
     * @param type $creditCard
     */
    private function baseParamsInit(&$params, $creditCard = true, $cart = false)
    {
        // no basket sent if PS_ROUND_TYPE is ROUND_TOTAL (prestashop config)
        if (Configuration::get('PS_ROUND_TYPE') == Order::ROUND_TOTAL) {
            $params["basket"]                = null;
            $params["delivery_informations"] = null;
        } elseif ($creditCard && $this->configHipay["payment"]["global"]["activate_basket"]) {
            $params["basket"]                = $this->getCart($cart);
            $params["delivery_informations"] = $this->getDeliveryInformation($cart);
        } elseif ($this->configHipay["payment"]["global"]["activate_basket"] || (isset($params["method"]) && isset($this->configHipay["payment"]["local_payment"][$params["method"]]["forceBasket"]))
            && $this->configHipay["payment"]["local_payment"][$params["method"]]["forceBasket"]
        ) {
            $params["basket"]                = $this->getCart($cart);
            $params["delivery_informations"] = $this->getDeliveryInformation($cart);
        } else {
            $params["basket"]                = null;
            $params["delivery_informations"] = null;
        }
    }

    /**
     * return mapped cart
     * @return type
     */
    private function getCart($cart = false)
    {
        $cart = new CartFormatter($this->module,
            $cart);

        return $cart->generate();
    }

    /**
     * return mapped delivery informations
     * @return type
     */
    private function getDeliveryInformation($cart = false)
    {
        $deliveryInformation = new DeliveryShippingInfoFormatter($this->module,
            $cart);

        return $deliveryInformation->generate();
    }

    /**
     * call Api to get forwarding URL
     */
    private function handleHostedPayment($params, $cart = false, $moto = false)
    {
        Tools::redirect(
            ApiCaller::getHostedPaymentPage(
                $this->module,
                $params,
                $cart,
                $moto
            )
        );
    }

    /**
     * Return  iframe URL
     *
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
    private function handleDirectOrder($params, $cc = false)
    {
        if ($cc) {
            $params["methodDisplayName"] = $this->configHipay["payment"]["credit_card"][$params["method"]]["displayName"];
        } else {
            $params["methodDisplayName"] = $this->configHipay["payment"]["local_payment"][$params["method"]]["displayName"];
        }

        $response = ApiCaller::requestDirectPost(
                $this->module,
                $params
        );

        $acceptUrl    = $this->context->link->getModuleLink(
            $this->module->name,
            'validation',
            array(),
            true
        );
        $failUrl      = $this->context->link->getModuleLink(
            $this->module->name,
            'decline',
            array(),
            true
        );
        $pendingUrl   = $this->context->link->getModuleLink(
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
        $forwardUrl   = $response->getForwardUrl();

        switch ($response->getState()) {
            case TransactionState::COMPLETED:
                $this->validateOrder($params);
                break;
            case TransactionState::PENDING:
                $redirectUrl = $pendingUrl;
                break;
            case TransactionState::FORWARDING:
                $redirectUrl = $forwardUrl;
                break;
            case TransactionState::DECLINED:
                $reason      = $response->getReason();
                $this->module->getLogs()->logInfos(
                    'There was an error request new transaction: '.$reason['message']
                );
                $redirectUrl = $failUrl;
                break;
            case TransactionState::ERROR:
                $reason      = $response->getReason();
                $this->module->getLogs()->logInfos(
                    'There was an error request new transaction: '.$reason['message']
                );
                $redirectUrl = $exceptionUrl;
                break;
            default:
                $redirectUrl = $failUrl;
        }

        Tools::redirect($redirectUrl);
    }

    /**
     * return well formatted authorize credit card payment methods
     * @return string
     */
    private function getCreditCardProductList(
    $deliveryCountry, $currency
    )
    {
        $creditCard  = HipayHelper::getActivatedPaymentByCountryAndCurrency(
            $this->module,
            $this->configHipay,
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
    $params, $creditCard = true
    )
    {
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

    private function validateOrder($params)
    {
        // SQL LOCK
        //#################################################################
        $cart    = $this->context->cart;
        // load order for id_order
        $orderId = Order::getOrderByCartId($cart->id);

        $cart = $this->context->cart;
        $this->db->setSQLLockForCart($cart->id);

        if ($cart && (!$orderId || empty($orderId))) {
            $this->module->getLogs()->logInfos("## Validate order for cart $cart->id $orderId");
            $customer = new Customer((int) $cart->id_customer);
            $shopId   = $cart->id_shop;
            $shop     = new Shop($shopId);
            // forced shop
            Shop::setContext(
                Shop::CONTEXT_SHOP,
                $cart->id_shop
            );
            $this->module->validateOrder(
                (int) $cart->id,
                Configuration::get('HIPAY_OS_PENDING'),
                (float) $cart->getOrderTotal(true),
                $params["methodDisplayName"],
                'Order created by HiPay after success payment.',
                array(),
                $this->context->currency->id,
                false,
                $customer->secure_key,
                $shop
            );
            // get order id
            $orderId  = $this->module->currentOrder;
            $this->db->releaseSQLLock();

            $captureType = array(
                "order_id" => $orderId,
                "type" => $this->configHipay["payment"]["global"]["capture_mode"]
            );

            $this->db->setOrderCaptureType($captureType);

            Hook::exec(
                'displayHiPayAccepted',
                array('cart' => $cart, "order_id" => $orderId)
            );

            $params = http_build_query(
                array(
                    'id_cart' => $cart->id,
                    'id_module' => $this->module->id,
                    'id_order' => $orderId,
                    'key' => $customer->secure_key,
                )
            );
        } else {
            $this->module->getLogs()->logInfos("## Validate order ( order exist  $orderId )");
        }
        return Tools::redirect('index.php?controller=order-confirmation&'.$params);
    }
}