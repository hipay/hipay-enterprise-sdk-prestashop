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
require_once dirname(__FILE__).'/../../lib/vendor/autoload.php';
require_once dirname(__FILE__).'/../apiCaller/ApiCaller.php';
require_once dirname(__FILE__).'/../apiFormatter/PaymentMethod/CardTokenFormatter.php';
require_once dirname(__FILE__).'/../apiFormatter/PaymentMethod/GenericPaymentMethodFormatter.php';
require_once dirname(__FILE__).'/../apiFormatter/Info/DeliveryShippingInfoFormatter.php';
require_once dirname(__FILE__).'/../apiFormatter/Cart/CartFormatter.php';
require_once dirname(__FILE__).'/../helper/dbquery/HipayDBUtils.php';
require_once dirname(__FILE__).'/../helper/HipayHelper.php';
require_once dirname(__FILE__).'/../../classes/helper/enums/ApiMode.php';

use HiPay\Fullservice\Enum\Transaction\Operation;
use HiPay\Fullservice\Enum\Transaction\TransactionState;
use HiPay\Fullservice\Enum\Transaction\TransactionStatus;

/**
 * Handle Hipay Api call.
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 *
 * @see    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class Apihandler
{
    /** @var Hipay_enterprise */
    private $module;
    /** @var Context */
    private $context;
    /** @var HipayConfig */
    private $configHipay;
    /** @var HipayDBUtils */
    private $dbUtils;

    public function __construct($moduleInstance, $contextInstance)
    {
        $this->module = $moduleInstance;
        $this->context = $contextInstance;
        $this->configHipay = $this->module->hipayConfigTool->getConfigHipay();
        $this->dbUtils = new HipayDBUtils($this->module);
    }

    /**
     * Handle moto payment request.
     *
     * @param type $cart
     */
    public function handleMoto($cart)
    {
        $delivery = new Address((int) $cart->id_address_delivery);
        $deliveryCountry = new Country((int) $delivery->id_country);
        $currency = new Currency((int) $cart->id_currency);
        $params = [];

        $params['method'] = 'credit_card';
        $params['moto'] = true;
        $params['iframe'] = false;
        $params['authentication_indicator'] = 0;
        $params['productlist'] = HipayHelper::getCreditCardProductList(
            $this->module,
            $this->configHipay,
            $deliveryCountry,
            $currency
        );

        $params = $this->baseParamsInit($params, true, $cart);

        $this->handleHostedPayment($params, $cart, true);
    }

    /**
     * handle credit card api call.
     *
     * @param string $mode
     * @param array  $params
     *
     * @return string
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function handleCreditCard($mode = ApiMode::HOSTED_PAGE, $params = [])
    {
        $params = $this->baseParamsInit($params);
        $cart = $this->context->cart;
        $delivery = new Address((int) $cart->id_address_delivery);
        $deliveryCountry = new Country((int) $delivery->id_country);
        $currency = new Currency((int) $cart->id_currency);
        $customer = new Customer((int) $cart->id_customer);

        $params['multi_use'] = !$customer->is_guest && Tools::isSubmit('saveTokenHipay');

        switch ($mode) {
            case ApiMode::DIRECT_POST:
                $params['paymentmethod'] = $this->getPaymentMethod($params);
                $this->handleDirectOrder($params);
                break;
            case ApiMode::HOSTED_PAGE_IFRAME:
                $params['productlist'] = HipayHelper::getCreditCardProductList(
                    $this->module,
                    $this->configHipay,
                    $deliveryCountry,
                    $currency
                );

                return $this->handleIframe($params);
            case ApiMode::HOSTED_PAGE:
                $params['productlist'] = HipayHelper::getCreditCardProductList(
                    $this->module,
                    $this->configHipay,
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
     * handle all local payment api call.
     *
     * @param string $mode
     * @param array  $params
     *
     * @return string
     */
    public function handleLocalPayment($mode = ApiMode::HOSTED_PAGE, $params = [])
    {
        $params = $this->baseParamsInit($params, false);

        $params['paymentmethod'] = $this->getPaymentMethod($params, false);

        switch ($mode) {
            case ApiMode::DIRECT_POST:
                return $this->handleDirectOrder($params);
                break;
            case ApiMode::HOSTED_PAGE:
                return $this->handleHostedPayment($params);
                break;
            case ApiMode::HOSTED_PAGE_IFRAME:
                return $this->handleIframe($params);
                break;
        }
    }

    /**
     * Handle capture request.
     *
     * @return bool
     */
    public function handleCapture($params, $eci = null)
    {
        return $this->handleMaintenance(Operation::CAPTURE, $params, $eci);
    }

    /**
     * Handle refund request.
     *
     * @return bool
     */
    public function handleRefund($params, $eci = null)
    {
        return $this->handleMaintenance(Operation::REFUND, $params, $eci);
    }

    /**
     * Accept any challenge.
     *
     * @return bool
     */
    public function handleAcceptChallenge($params, $eci = null)
    {
        return $this->handleMaintenance(Operation::ACCEPT_CHALLENGE, $params, $eci);
    }

    /**
     * Accept any challenge.
     *
     * @return bool
     */
    public function handleDenyChallenge($params, $eci = null)
    {
        return $this->handleMaintenance(Operation::DENY_CHALLENGE, $params, $eci);
    }

    public function handleCancel($params, $eci = null)
    {
        return $this->handleMaintenance(Operation::CANCEL, $params, $eci);
    }

    /**
     * handle maintenance request.
     *
     * @param $mode
     * @param array $params
     * @param $eci
     *
     * @return bool
     */
    private function handleMaintenance($mode, $params = [], $eci = null)
    {
        try {
            switch ($mode) {
                case Operation::CAPTURE:
                    $params['operation'] = Operation::CAPTURE;
                    ApiCaller::requestMaintenance($this->module, $params, $eci);
                    break;
                case Operation::REFUND:
                    $params['operation'] = Operation::REFUND;
                    ApiCaller::requestMaintenance($this->module, $params, $eci);
                    break;
                case Operation::ACCEPT_CHALLENGE:
                    $params['operation'] = Operation::ACCEPT_CHALLENGE;
                    ApiCaller::requestMaintenance($this->module, $params, $eci);
                    break;
                case Operation::DENY_CHALLENGE:
                    $params['operation'] = Operation::DENY_CHALLENGE;
                    ApiCaller::requestMaintenance($this->module, $params, $eci);
                    break;
                case Operation::CANCEL:
                    $params['operation'] = Operation::CANCEL;
                    $displayMsg = null;
                    $order = new Order($params['order']);

                    $transactionRef = '';
                    $status = '';

                    if ($order->getCurrentState() == Configuration::get('HIPAY_OS_AUTHORIZED') ||
                        $order->getCurrentState() == Configuration::get('HIPAY_OS_PENDING')) {
                        if (false !== $params['transaction_reference']) {
                            $hipayDbMaintenance = new HipayDBMaintenance($this->module);

                            // If current transaction status is cancelled, it means we are currently handling the 115 notification from HiPay,
                            // and the transaction is already cancelled
                            if (!$hipayDbMaintenance->isTransactionCancelled($order->id)) {
                                try {
                                    $result = ApiCaller::requestMaintenance($this->module, $params, $eci);

                                    if (!in_array($result->getStatus(), [TransactionStatus::AUTHORIZATION_CANCELLATION_REQUESTED, TransactionStatus::CANCELLED])) {
                                        $displayMsg = $this->module->l("There was an error on the cancellation of the HiPay transaction. You can see and cancel the transaction directly from HiPay's BackOffice");
                                        $displayMsg .= ' (https://merchant.hipay-tpp.com/default/auth/login)';
                                        $status = $result->getStatus();
                                        $transactionRef = $result->getTransactionReference();
                                    } else {
                                        HipayOrderMessage::orderMessage(
                                            $this->module,
                                            $order->id,
                                            $order->id_customer,
                                            HipayOrderMessage::formatOrderData($this->module, $result)
                                        );
                                    }
                                } catch (GatewayException $e) {
                                    $errorMsg = [];
                                    $transaction = $hipayDbMaintenance->getTransactionByRef($params['transaction_reference']);

                                    preg_match('/\\[(.*)\\]/s', $e->getMessage(), $errorMsg);
                                    $displayMsg = $this->module->l("There was an error on the cancellation of the HiPay transaction. You can see and cancel the transaction directly from HiPay's BackOffice");
                                    $displayMsg .= " (https://merchant.hipay-tpp.com/default/auth/login)\n";
                                    $displayMsg .= $this->module->l('Message was : ').preg_replace("/\r|\n/", '', $errorMsg[0]);

                                    if ($transaction) {
                                        $transactionRef = $transaction['transaction_ref'];
                                        $status = $transaction['status'];
                                    }
                                }
                            }
                        } else {
                            $displayMsg = $this->module->l("The HiPay transaction was not canceled because no transaction reference exists. You can see and cancel the transaction directly from HiPay's BackOffice");
                            $displayMsg .= ' (https://merchant.hipay-tpp.com/default/auth/login)';

                            $transactionRef = '';
                            $status = '';
                        }
                    } else {
                        $displayMsg = $this->module->l("The HiPay transaction was not canceled because it's status doesn't allow cancellation. You can see and cancel the transaction directly from HiPay's BackOffice");
                        $displayMsg .= ' (https://merchant.hipay-tpp.com/default/auth/login)';

                        $transactionRef = '';
                        $status = '';
                    }

                    if (!empty($displayMsg)) {
                        HipayOrderMessage::orderMessage(
                            $this->module,
                            $order->id,
                            $order->id_customer,
                            HipayOrderMessage::formatErrorOrderData($this->module, $displayMsg, $transactionRef, $status)
                        );
                    }

                    break;
                default:
                    $this->module->getLogs()->logInfos('# Unknown maintenance operation');
            }

            return true;
        } catch (GatewayException $e) {
            $errorMessage = $this->module->l('An error occured during request Maintenance.', 'capture');
            $this->context->cookie->__set('hipay_errors', $errorMessage);

            return false;
        } catch (PrestaShopDatabaseException $e) {
        }
    }

    /**
     * Init params send to the api caller.
     *
     * @param array $params
     * @param bool  $creditCard
     * @param bool  $cart
     */
    private function baseParamsInit($params, $creditCard = true, $cart = false)
    {
        $params['basket'] = null;
        $params['delivery_informations'] = null;

        // no basket sent if PS_ROUND_TYPE is ROUND_TOTAL (prestashop config)
        if (Order::ROUND_TOTAL == Configuration::get('PS_ROUND_TYPE')) {
            return $params;
        }

        $globalBasket = $this->configHipay['payment']['global']['activate_basket'];
        $localBasket = (
            isset($params['method'])
            && isset($this->configHipay['payment']['local_payment'][$params['method']]['basketRequired'])
            && $this->configHipay['payment']['local_payment'][$params['method']]['basketRequired']
        );

        if ($creditCard && $globalBasket) {
            $params['basket'] = $this->getCart($cart);
            $params['delivery_informations'] = $this->getDeliveryInformation($cart);
        } elseif ($globalBasket || $localBasket) {
            $params['basket'] = $this->getCart($cart);
            $params['delivery_informations'] = $this->getDeliveryInformation($cart);
        }

        return $params;
    }

    /**
     * return mapped cart.
     *
     * @param bool $cart
     *
     * @return json
     */
    private function getCart($cart = false)
    {
        $cart = new CartFormatter($this->module, $cart);

        return $cart->generate();
    }

    /**
     * return mapped delivery informations.
     *
     * @param bool $cart
     *
     * @return \HiPay\Fullservice\Gateway\Request\Info\DeliveryShippingInfoRequest
     */
    private function getDeliveryInformation($cart = false)
    {
        $deliveryInformation = new DeliveryShippingInfoFormatter($this->module, $cart);

        return $deliveryInformation->generate();
    }

    /**
     * call Api to get forwarding URL.
     *
     * @param bool $cart
     * @param bool $moto
     */
    private function handleHostedPayment($params, $cart = false, $moto = false)
    {
        try {
            $params['iframe'] = false;
            Tools::redirect(ApiCaller::getHostedPaymentPage($this->module, $params, $cart, $moto));
        } catch (GatewayException $e) {
            $e->handleException();
        }
    }

    /**
     * Return  iframe URL.
     *
     * @return string
     */
    private function handleIframe($params)
    {
        try {
            $params['iframe'] = true;

            return ApiCaller::getHostedPaymentPage($this->module, $params);
        } catch (GatewayException $e) {
            $e->handleException();
        }
    }

    /**
     * call api and redirect to success or error page.
     *
     * @throws PrestaShopException
     */
    private function handleDirectOrder($params)
    {
        $failUrl = $this->context->link->getModuleLink($this->module->name, 'decline', [], true);
        $pendingUrl = $this->context->link->getModuleLink($this->module->name, 'pending', [], true);
        $exceptionUrl = $this->context->link->getModuleLink($this->module->name, 'exception', [], true);

        try {
            $params['paymentProduct'] = $this->module->hipayConfigTool->getPaymentProduct($params['method']);

            $params['methodDisplayName'] = HipayHelper::getPaymentProductName(
                $params['paymentProduct'],
                $this->module,
                $this->context->language
            );

            $response = ApiCaller::requestDirectPost($this->module, $params);

            $forwardUrl = $response->getForwardUrl();

            switch ($response->getState()) {
                case TransactionState::COMPLETED:
                    $redirectParams = HipayHelper::validateOrder(
                        $this->module,
                        $this->context,
                        $this->context->cart,
                        $params['methodDisplayName']
                    );

                    Hook::exec('displayHiPayAccepted', ['cart' => $this->context->cart, 'order_id' => $redirectParams['id_order']]);
                    $redirectUrl = 'index.php?controller=order-confirmation&'.http_build_query($redirectParams);
                    break;
                case TransactionState::PENDING:
                    HipayHelper::validateOrder(
                        $this->module,
                        $this->context,
                        $this->context->cart,
                        $params['methodDisplayName']
                    );

                    $redirectUrl = $pendingUrl;
                    break;
                case TransactionState::FORWARDING:
                    if (in_array($params['method'], ['multibanco', 'sisal']) && $response->getReferenceToPay()) {
                        // If it's a local payment and there is a referenceToPay in the response
                        // Handle it as a pending to display the reference
                        $redirectParams = HipayHelper::validateOrder(
                            $this->module,
                            $this->context,
                            $this->context->cart,
                            $params['methodDisplayName']
                        );

                        if (!preg_match("/\?/", $pendingUrl)) {
                            $pendingUrl .= '?';
                        }

                        $redirectUrl = $pendingUrl.'&referenceToPay=1&method='.$params['method'].'&'.http_build_query(json_decode($response->getReferenceToPay()));
                        break;
                    } else {
                        $redirectUrl = $forwardUrl;
                    }
                    break;
                case TransactionState::DECLINED:
                    $reason = $response->getReason();
                    $this->module->getLogs()->logInfos(
                        'There was an error request new transaction: '.$reason['message']
                    );
                    $redirectUrl = $failUrl;
                    break;
                case TransactionState::ERROR:
                    $reason = $response->getReason();
                    $this->module->getLogs()->logInfos(
                        'There was an error request new transaction: '.$reason['message']
                    );
                    $redirectUrl = $exceptionUrl;
                    break;
                default:
                    $redirectUrl = $failUrl;
            }

            Tools::redirect($redirectUrl);
        } catch (GatewayException $e) {
            $e->handleException();
        } catch (Exception $e) {
            HipayHelper::redirectToExceptionPage($this->context, $this->module);
            exit;
        }
    }

    /**
     * return mapped payment method.
     *
     * @param bool $creditCard
     *
     * @return \HiPay\Fullservice\Gateway\Request\PaymentMethod\CardTokenPaymentMethod|mixed
     */
    private function getPaymentMethod($params, $creditCard = true)
    {
        if ($creditCard) {
            $paymentMethod = new CardTokenFormatter($this->module, $params);
        } else {
            $paymentMethod = new GenericPaymentMethodFormatter($this->module, $params);
        }

        return $paymentMethod->generate();
    }
}
