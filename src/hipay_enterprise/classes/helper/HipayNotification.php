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

require_once(dirname(__FILE__) . '/../../lib/vendor/autoload.php');
require_once(dirname(__FILE__) . '/dbquery/HipayDBUtils.php');
require_once(dirname(__FILE__) . '/dbquery/HipayDBMaintenance.php');
require_once(dirname(__FILE__) . '/HipayMaintenanceData.php');
require_once(dirname(__FILE__) . '/HipayHelper.php');
require_once(dirname(__FILE__) . '/HipayOrderMessage.php');
require_once(dirname(__FILE__) . '/HipayMail.php');
require_once(dirname(__FILE__) . '/../exceptions/PaymentProductNotFoundException.php');
require_once(dirname(__FILE__) . '/../exceptions/NotificationException.php');

use HiPay\Fullservice\Enum\Transaction\TransactionStatus;

/**
 * Handle notification from TPP
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class HipayNotification
{
    const TRANSACTION_REF_CAPTURE_SUFFIX = "capture";
    const TRANSACTION_REF_REFUND_SUFFIX = "refund";

    const REPEATABLE_NOTIFICATIONS = [
        TransactionStatus::REFUND_REQUESTED,
        TransactionStatus::PARTIALLY_REFUNDED,
        TransactionStatus::CAPTURE_REQUESTED,
        TransactionStatus::CAPTURED,
        TransactionStatus::PARTIALLY_CAPTURED,
        TransactionStatus::CAPTURE_REFUSED
    ];

    const NO_ORDER_NEEDED_NOTIFICATIONS = [
        TransactionStatus::REFUSED,
        TransactionStatus::AUTHENTICATION_FAILED
    ];

    protected $transaction;
    protected $cart;

    /**
     * @var Order
     */
    protected $order = null;
    protected $ccToken;
    protected $module;
    protected $log;
    protected $context;
    protected $dbUtils;
    protected $dbMaintenance;

    /**
     * @var HipayConfig
     */
    protected $configHipay;

    /**
     * HipayNotification constructor.
     * @param $moduleInstance
     * @param $data
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function __construct($moduleInstance, $data)
    {
        $this->module = $moduleInstance;
        $this->log = $this->module->getLogs();
        $this->context = Context::getContext();

        $this->context->language = new Language(Configuration::get('PS_LANG_DEFAULT'));

        $this->dbUtils = new HipayDBUtils($this->module);
        $this->dbMaintenance = new HipayDBMaintenance($this->module);
        $this->configHipay = $this->module->hipayConfigTool->getConfigHipay();

        $this->transaction = (new HiPay\Fullservice\Gateway\Mapper\TransactionMapper($data))->getModelObjectMapped();
        $this->log->logInfos(print_r($this->transaction, true));

        // if cart_id exist or not
        if ($this->transaction->getOrder() == null || $this->transaction->getOrder()->getId() == null) {
            $this->log->logErrors('Bad Callback initiated, no cart ID found ');
            die('No cart found');
        }
        $this->cart = new Cart($this->transaction->getOrder()->getId());
        // check if cart is correctly loaded
        if (!Validate::isLoadedObject($this->cart)) {
            $this->log->logErrors('Bad Callback initiated, cart could not be initiated ');
            die('Cart empty');
        }

        $this->ccToken = new HipayCCToken($this->module);

        // forced shop
        Shop::setContext(Shop::CONTEXT_SHOP, $this->cart->id_shop);
    }

    /**
     * @return mixed
     */
    public function getEci()
    {
        return $this->transaction->getEci();
    }

    /**
     * Process notification
     *
     * @throws Exception
     * @throws NotificationException
     */
    public function processTransaction()
    {
        try {
            $this->log->logInfos(
                "# ProcessTransaction for cart ID : " .
                $this->cart->id .
                " and status " .
                $this->transaction->getStatus()
            );

            $currentAttempt = $this->saveNotificationAttempt();

            $idOrder = $this->dbUtils->getOrderByCartId($this->cart->id);
            if ($idOrder) {
                $this->order = new Order((int)$idOrder);
                $this->log->logInfos("# Order with cart ID {$this->cart->id} ");

                if (!$this->controleIfStatushistoryExist(Configuration::get('HIPAY_OS_PENDING'))) {
                    throw new NotificationException(
                        'Order not ready for cart ID ' . $this->cart->id,
                        Context::getContext(),
                        $this->module,
                        'HTTP/1.0 404 Not found'
                    );
                }
            } else {
                if (in_array($this->transaction->getStatus(), [TransactionStatus::AUTHORIZED, TransactionStatus::AUTHORIZED_AND_PENDING])
                    && $currentAttempt >= Configuration::get('HIPAY_NOTIFICATION_THRESHOLD')) {
                    $this->log->logInfos('Received ' . $currentAttempt . ' ' . $this->transaction->getStatus() . ' Notifications for cart : ' . $this->cart->id . ', creating order now');
                    $this->registerOrder(Configuration::get('HIPAY_OS_PENDING'));
                } elseif (isset($this->getPaymentProductConfig()['orderOnPending']) &&
                    $this->getPaymentProductConfig()['orderOnPending'] &&
                    $this->transaction->getStatus() === TransactionStatus::AUTHORIZATION_REQUESTED) {
                    $this->log->logInfos('Received ' . $this->transaction->getStatus() . ' Notification for cart : ' . $this->cart->id . ', creating order now');
                    $this->registerOrder(Configuration::get('HIPAY_OS_PENDING'));
                } else {
                    if (in_array($this->transaction->getStatus(), self::NO_ORDER_NEEDED_NOTIFICATIONS)) {
                        die();
                    }

                    throw new NotificationException(
                        'Order not found for cart ID ' . $this->cart->id,
                        Context::getContext(),
                        $this->module,
                        'HTTP/1.0 404 Not found'
                    );
                }
            }

            if (!$this->transactionIsValid()) {
                $this->updateNotificationState(NotificationStatus::NOT_HANDLED);
                die('Notification already received and handled.');
            }

            $this->dbUtils->setSQLLockForCart($this->order->id, "# ProcessTransaction for order ID : " . $this->order->id);

            $orderHasBeenPaid = (int)$this->order->getCurrentState() == _PS_OS_OUTOFSTOCK_PAID_ ||
                $this->controleIfStatushistoryExist(_PS_OS_PAYMENT_);

            switch ($this->transaction->getStatus()) {
                // Do nothing - Just log the status and skip further processing
                case TransactionStatus::CREATED:
                case TransactionStatus::CARD_HOLDER_ENROLLED:
                case TransactionStatus::CARD_HOLDER_NOT_ENROLLED:
                case TransactionStatus::UNABLE_TO_AUTHENTICATE:
                case TransactionStatus::CARD_HOLDER_AUTHENTICATED:
                case TransactionStatus::AUTHENTICATION_ATTEMPTED:
                case TransactionStatus::COULD_NOT_AUTHENTICATE:
                case TransactionStatus::AUTHENTICATION_FAILED:
                case TransactionStatus::COLLECTED:
                case TransactionStatus::ACQUIRER_FOUND:
                case TransactionStatus::ACQUIRER_NOT_FOUND:
                case TransactionStatus::RISK_ACCEPTED:
                case TransactionStatus::CAPTURE_REQUESTED:
                default:
                    $orderState = 'skip';
                    break;
                case TransactionStatus::BLOCKED:
                case TransactionStatus::CHARGED_BACK:
                    if (!$orderHasBeenPaid) {
                        $this->updateOrderStatus(_PS_OS_ERROR_);
                    }
                    break;
                case TransactionStatus::DENIED:
                case TransactionStatus::REFUSED:
                    if (!$orderHasBeenPaid) {
                        $this->updateOrderStatus(Configuration::get('HIPAY_OS_DENIED'));

                        // Notify website admin for a challenged transaction
                        HipayMail::sendMailPaymentDeny($this->context, $this->module, $this->order);
                    }
                    break;
                case TransactionStatus::AUTHORIZED_AND_PENDING:
                    if (!$orderHasBeenPaid) {
                        $this->updateOrderStatus(Configuration::get('HIPAY_OS_CHALLENGED'));
                        // Notify website admin for a challenged transaction
                        HipayMail::sendMailPaymentFraud($this->context, $this->module, $this->order);
                    }
                    break;
                case TransactionStatus::AUTHENTICATION_REQUESTED:
                case TransactionStatus::AUTHORIZATION_REQUESTED:
                case TransactionStatus::PENDING_PAYMENT:
                    // If pending and we have already received authorization, then we do not change the status
                    if (!$this->controleIfStatushistoryExist(Configuration::get("HIPAY_OS_AUTHORIZED")) && !$orderHasBeenPaid) {
                        $this->updateOrderStatus(Configuration::get('HIPAY_OS_PENDING'));
                    }
                    break;
                case TransactionStatus::EXPIRED:
                    if (!$orderHasBeenPaid) {
                        $this->updateOrderStatus(Configuration::get('HIPAY_OS_EXPIRED'));
                    }
                    break;
                case TransactionStatus::AUTHORIZATION_CANCELLATION_REQUESTED:
                case TransactionStatus::CANCELLED:
                    if (!$orderHasBeenPaid) {
                        $this->updateOrderStatus(_PS_OS_CANCELED_);
                    }
                    break;
                case TransactionStatus::AUTHORIZED: //116
                    if (!$orderHasBeenPaid) {
                        $this->updateOrderStatus(Configuration::get("HIPAY_OS_AUTHORIZED"));
                    }
                    // set capture type on authorized
                    $this->setOrderCaptureType();
                    break;
                case TransactionStatus::CAPTURED: //118
                    if ($this->controleIfStatushistoryExist(Configuration::get('HIPAY_OS_AUTHORIZED'))) {
                        $orderState = _PS_OS_PAYMENT_;
                        if ($this->transaction->getCapturedAmount() < $this->transaction->getAuthorizedAmount()) {
                            $orderState = Configuration::get('HIPAY_OS_PARTIALLY_CAPTURED');
                        }
                        if (!$orderHasBeenPaid) {
                            $this->updateOrderStatus($orderState);
                            $this->captureOrder();
                        }
                    } else {
                        throw new NotificationException(
                            "Order is not Authorized, could not capture Payment.",
                            Context::getContext(),
                            $this->module,
                            'HTTP/1.0 409 Conflict'
                        );
                    }
                    break;
                case TransactionStatus::PARTIALLY_CAPTURED: //119
                    if (!$orderHasBeenPaid) {
                        $this->updateOrderStatus(Configuration::get('HIPAY_OS_PARTIALLY_CAPTURED'));
                        $this->captureOrder();
                    }
                    break;
                case TransactionStatus::REFUND_REQUESTED: //124
                case TransactionStatus::REFUNDED: //125
                case TransactionStatus::PARTIALLY_REFUNDED: //126
                    $this->refundOrder();
                    break;
                case TransactionStatus::CHARGED_BACK:
                    if (!$orderHasBeenPaid) {
                        $this->updateOrderStatus(Configuration::get('HIPAY_OS_CHARGEDBACK'));
                    }
                    break;
                case TransactionStatus::CAPTURE_REFUSED:
                    if (!$orderHasBeenPaid) {
                        $this->updateOrderStatus(Configuration::get('HIPAY_OS_CAPTURE_REFUSED'));
                    }
                    break;
            }

            $this->dbUtils->releaseSQLLock("# ProcessTransaction for cart ID : " . $this->cart->id);

            /*
             * If 116 or 118, we save the token
             */
            if ($this->transaction->getStatus() === TransactionStatus::AUTHORIZED ||
                $this->transaction->getStatus() === TransactionStatus::CAPTURED) {
                $customData = $this->transaction->getCustomData();
                if (isset($customData["multiUse"]) && $customData["multiUse"]) {
                    $this->saveCardToken();
                }
            }

            $this->updateNotificationState(NotificationStatus::SUCCESS);
        } catch (NotificationException $e) {
            throw $e;
        } catch (Exception $e) {
            $this->updateNotificationState(NotificationStatus::ERROR);
            $this->dbUtils->releaseSQLLock("Exception # ProcessTransaction for cart ID : " . $this->cart->id);
            $this->log->logException($e);
            throw $e;
        }
    }

    /**
     * update order status
     *
     * @param $newState
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function updateOrderStatus($newState)
    {
        if ((int)$this->order->getCurrentState() != (int)$newState) {
            // If order status is OUTOFSTOCK_UNPAID then new state will be OUTOFSTOCK_PAID
            if (($this->controleIfStatushistoryExist(_PS_OS_OUTOFSTOCK_UNPAID_))
                && ($newState == _PS_OS_PAYMENT_)
            ) {
                $newState = _PS_OS_OUTOFSTOCK_PAID_;
            }
            HipayHelper::changeOrderStatus($this->order, $newState);
            $this->addOrderMessage();
        }

        if ($this->transaction->getStatus() == TransactionStatus::CAPTURE_REQUESTED &&
            $this->transaction->getCapturedAmount() < $this->transaction->getAuthorizedAmount()
        ) {
            $this->log->logInfos(
                'captured_amount (' .
                $this->transaction->getCapturedAmount() .
                ') is < than authorized_amount (' .
                $this->transaction->getAuthorizedAmount() .
                ')'
            );
        }
    }

    /**
     * register order if don't exist
     *
     * @param $state
     * @return bool
     * @throws Exception
     * @throws PrestaShopException
     */
    private function registerOrder($state)
    {
        if (!HipayHelper::orderExists($this->cart->id)) {
            $this->log->logInfos('Register New order: ' . $this->cart->id);
            $message = HipayOrderMessage::formatOrderData($this->module, $this->transaction);

            // init context
            Context::getContext()->cart = new Cart((int)$this->cart->id);
            $address = new Address((int)Context::getContext()->cart->id_address_invoice);
            Context::getContext()->country = new Country((int)$address->id_country);
            Context::getContext()->customer = new Customer((int)Context::getContext()->cart->id_customer);
            Context::getContext()->language = new Language((int)Context::getContext()->cart->id_lang);
            Context::getContext()->currency = new Currency((int)Context::getContext()->cart->id_currency);
            $customer = new Customer((int)Context::getContext()->cart->id_customer);
            $shop_id = $this->cart->id_shop;
            $shop = new Shop($shop_id);
            Shop::setContext(Shop::CONTEXT_SHOP, $this->cart->id_shop);

            $paymentProductName = $this->getPaymentProductName();

            try {
                $this->log->logInfos('Prepare Validate order from registerOrder');
                $this->module->validateOrder(
                    Context::getContext()->cart->id,
                    $state,
                    (float)$this->transaction->getAuthorizedAmount(),
                    $paymentProductName,
                    $message,
                    array(),
                    Context::getContext()->cart->id_currency,
                    false,
                    $customer->secure_key,
                    $shop
                );
                $this->order = new Order($this->module->currentOrder);
                return true;
            } catch (Exception $e) {
                $this->log->logException($e);
                throw $e;
            }
        } else {
            $this->log->logInfos('Order ealready exists for cart : ' . $this->cart->id);
        }

        return true;
    }

    /**
     * Save capture type from notification (required for capture and refund form)
     *
     * @throws PrestaShopDatabaseException
     */
    private function setOrderCaptureType()
    {
        if ($this->order !== null && !$this->dbMaintenance->OrderCaptureTypeExist($this->order->id)) {
            $customData = $this->transaction->getCustomData();

            $captureType = array(
                "order_id" => $this->order->id,
                "type" => (isset($customData["captureType"])) ? $customData["captureType"] : "automatic"
            );

            $this->dbMaintenance->setOrderCaptureType($captureType);
        }
    }

    private function getPaymentProductConfig()
    {
        $paymentProductName = $this->transaction->getPaymentProduct();

        try {
            $paymentProduct = $this->module->hipayConfigTool->getPaymentProduct($paymentProductName);
        } catch (PaymentProductNotFoundException $e) {
            $paymentProduct = $paymentProductName;
        }

        return $paymentProduct;
    }

    /**
     * @return mixed
     */
    private function getPaymentProductName()
    {
        return HipayHelper::getPaymentProductName(
            $this->getPaymentProductConfig(),
            $this->module,
            $this->context->language
        );
    }

    /**
     * create order payment line
     *
     * @param bool $refund
     * @throws Exception
     */
    private function createOrderPayment($refund = false)
    {
        if (HipayHelper::orderExists($this->cart->id)) {
            $amount = $this->getRealCapturedAmount($refund);
            if ($amount != 0) {
                $paymentProduct = $this->getPaymentProductName();
                $payment_transaction_id = $this->setTransactionRefForPrestashop();
                $currency = new Currency($this->order->id_currency);
                $payment_date = date("Y-m-d H:i:s");

                $invoices = $this->order->getInvoicesCollection();
                $invoice = $invoices && $invoices->getFirst() ? $invoices->getFirst() : null;

                if ($this->order && Validate::isLoadedObject($this->order)) {
                    $orderPaymentResult = false;

                    if ($refund) {
                        // Turn amount positive
                        $amount *= -1;

                        $operation = $this->transaction->getOperation();

                        // Get existing slips for this order
                        $orderSlipsRequest = $this->order
                            ->getOrderSlipsCollection()
                            ->orderBy('date_add', 'desc');

                        $orderSlips = $orderSlipsRequest->getResults();

                        $alreadyExists = false;
                        foreach ($orderSlips AS $orderSlip) {

                            if (strval($orderSlip->id) === $operation->getId()) {
                                // This notification already corresponds to an existing slip
                                // No need to create a new one
                                $alreadyExists = true;
                            }

                            // Fix amount by removing older refund amounts
                            $amount -= floatval($orderSlip->total_products_tax_incl) + floatval($orderSlip->total_shipping_tax_incl);
                        }

                        if (!$alreadyExists) {
                            if ($amount !== floatval($this->order->total_paid)) {
                                // Force amount to the chosen one
                                $productArray = $this->order->getProducts();

                                $product = array_pop($productArray);


                                $order_detail = new OrderDetail((int)$product['id_order_detail']);
                                $tax_calculator = $order_detail->getTaxCalculator();
                                $amount = $tax_calculator->removeTaxes($amount);

                                $product['unit_price'] = $amount;
                                $product['product_quantity_refunded'] = $product['product_quantity'];
                                $product['quantity'] = 1;

                                $orderPaymentResult = OrderSlip::create(
                                    $this->order,
                                    [$product]
                                );
                            } else {
                                $productArray = $this->order->getProducts();

                                foreach ($productArray as &$product) {
                                    $product['unit_price'] = $product['unit_price_tax_excl'];
                                    $product['product_quantity_refunded'] = $product['product_quantity'];
                                    $product['quantity'] = $product['product_quantity'];
                                }

                                $orderPaymentResult = OrderSlip::create($this->order, $productArray, null);
                            }
                        }
                    } else {
                        $orderPaymentResult = $this->order->addOrderPayment(
                            $amount,
                            $paymentProduct,
                            $payment_transaction_id,
                            $currency,
                            $payment_date,
                            $invoice
                        );
                    }
                    // Add order payment
                    if ($orderPaymentResult) {
                        $this->log->logInfos("# Order payment created with success {$this->order->id}");
                        $orderPayment = $this->dbUtils->findOrderPayment(
                            $this->order->reference,
                            $payment_transaction_id
                        );
                        if ($orderPayment) {
                            $this->setOrderPaymentData($orderPayment);
                        }
                    }
                } else {
                    $this->log->logErrors('# Error, order exist but the object order not loaded');
                }
            } else {
                $this->log->logInfos('# Order Payment of 0 amount not added');
            }
        }
    }

    /**
     * Save card Token for recurring payment
     *
     * @throws Exception
     */
    private function saveCardToken()
    {
        try {
            if ($this->transaction->getPaymentMethod() != null) {
                $configCC = $this->module->hipayConfigTool->getPaymentCreditCard()[strtolower(
                    $this->transaction->getPaymentProduct()
                )];
                if (isset($configCC['canRecurring']) && $configCC['canRecurring']) {
                    $card = array(
                        "token" => $this->transaction->getPaymentMethod()->getToken(),
                        "brand" => $this->transaction->getPaymentProduct(),
                        "pan" => $this->transaction->getPaymentMethod()->getPan(),
                        "card_holder" => $this->transaction->getPaymentMethod()->getCardHolder(),
                        "card_expiry_month" => $this->transaction->getPaymentMethod()->getCardExpiryMonth(),
                        "card_expiry_year" => $this->transaction->getPaymentMethod()->getCardExpiryYear(),
                        "issuer" => $this->transaction->getPaymentMethod()->getIssuer(),
                        "country" => $this->transaction->getPaymentMethod()->getCountry()
                    );

                    $this->ccToken->saveCCToken($this->cart->id_customer, $card);
                }
            }
        } catch (Exception $e) {
            $this->log->logException($e);
            throw $e;
        }
    }

    /**
     * @param $orderPayment
     * @throws Exception
     */
    private function setOrderPaymentData($orderPayment)
    {
        try {
            if ($this->transaction->getPaymentMethod() != null) {
                $orderPayment->card_number = $this->transaction->getPaymentMethod()->getPan();
                $orderPayment->card_brand = $this->transaction->getPaymentMethod()->getBrand();
                $orderPayment->card_expiration = $this->transaction->getPaymentMethod()->getCardExpiryMonth() .
                    '/' .
                    $this->transaction->getPaymentMethod()->getCardExpiryYear();
                $orderPayment->card_holder = $this->transaction->getPaymentMethod()->getCardHolder();
                $orderPayment->update();
            }
        } catch (Exception $e) {
            $this->log->logException($e);
            throw $e;
        }
    }

    /**
     * Capture amount sent by notification
     *
     * @return bool
     * @throws Exception
     */
    private function captureOrder()
    {
        $this->log->logInfos("# Capture Order {$this->order->reference}");

        $this->dbUtils->deleteOrderPaymentDuplicate($this->order);

        // If Capture is originated in the TPP BO the Operation field is null
        // Otherwise transaction has already been saved
        if ($this->transaction->getOperation() == null && $this->transaction->getAttemptId() < 2) {
            try {
                $maintenanceData = new HipayMaintenanceData($this->module);
                // retrieve number of capture or refund request
                $transactionAttempt = $maintenanceData->getNbOperationAttempt('BO_TPP', $this->order->id);

                //save capture items and quantity in prestashop
                $captureData = array(
                    "hp_ps_order_id" => $this->order->id,
                    "hp_ps_product_id" => 0,
                    "operation" => 'BO_TPP',
                    "type" => 'BO',
                    "attempt_number" => $transactionAttempt + 1,
                    "quantity" => 1,
                    "amount" => 0
                );

                $this->dbMaintenance->setCaptureOrRefundOrder($captureData);
            } catch (Exception $e) {
                $this->log->logException($e);
                throw $e;
            }
        }

        // if transaction doesn't exist we create an order payment (if multiple capture, 1 line by amount captured)
        if ($this->dbUtils->countOrderPayment($this->order->reference, $this->setTransactionRefForPrestashop()) == 0) {
            $this->createOrderPayment();
        }

        return true;
    }

    /**
     * Refund order
     *
     * @return bool
     * @throws Exception
     */
    private function refundOrder()
    {
        $this->log->logInfos(
            "# Refund Order {$this->order->reference} with refund amount {$this->transaction->getRefundedAmount()}"
        );

        if (HipayHelper::orderExists($this->cart->id)) {
            // If Capture is originated in the TPP BO the Operation field is null
            // Otherwise transaction has already been saved
            if ($this->transaction->getOperation() == null && $this->transaction->getAttemptId() < 2) {
                //save refund items and quantity in prestashop
                $maintenanceData = new HipayMaintenanceData($this->module);
                // retrieve number of capture or refund request
                $transactionAttempt = $maintenanceData->getNbOperationAttempt('BO_TPP', $this->order->id);

                $captureData = array(
                    "hp_ps_order_id" => $this->order->id,
                    "hp_ps_product_id" => 0,
                    "operation" => 'BO_TPP',
                    "type" => 'BO',
                    "quantity" => 1,
                    "amount" => 0,
                    'attempt_number' => $transactionAttempt + 1
                );

                $this->dbMaintenance->setCaptureOrRefundOrder($captureData);
            }

            if ($this->transaction->getStatus() == TransactionStatus::REFUND_REQUESTED) {
                $this->updateOrderStatus(Configuration::get('HIPAY_OS_REFUND_REQUESTED'));
                return true;
            }

            // if transaction doesn't exist we create an order payment (if multiple refund, 1 line by amount refunded)
            if ($this->dbUtils->countOrderPayment($this->order->reference, $this->setTransactionRefForPrestashop()) ==
                0) {
                $this->createOrderPayment(true);

                //force refund order status
                if ($this->transaction->getRefundedAmount() == $this->transaction->getAuthorizedAmount()) {
                    $this->log->logInfos('# RefundOrder: ' . Configuration::get('HIPAY_OS_REFUNDED'));
                    $this->updateOrderStatus(Configuration::get('HIPAY_OS_REFUNDED'));
                } else {
                    $this->log->logInfos(
                        '# RefundOrder: ' . Configuration::get('HIPAY_OS_REFUNDED_PARTIALLY')
                    );
                    $this->updateOrderStatus(Configuration::get('HIPAY_OS_REFUNDED_PARTIALLY'));
                }
            }
        }

        return true;
    }

    /**
     * check if order is already at status "payment accepted"
     *
     * @param $paymentStatus
     * @return bool
     */
    private function controleIfStatushistoryExist($paymentStatus)
    {

        if ($this->order != null) {
            $this->log->logInfos("# ControleIfStatushistoryExist Checking if Status " . $paymentStatus . ' exists in history for order ' . $this->order->id);
            return $this->dbUtils->checkOrderStatusExist($paymentStatus, $this->order->id);
        }

        $this->log->logInfos("# ControleIfStatushistoryExist Order doesn't exist, can't check status");
        return false;
    }

    /**
     * add private order message with transaction data (json)
     */
    private function addOrderMessage()
    {
        $customData = $this->transaction->getCustomData();

        $data = array(
            "order_id" => $this->order->id,
            "transaction_ref" => $this->transaction->getTransactionReference(),
            "state" => $this->transaction->getState(),
            "status" => $this->transaction->getStatus(),
            "message" => $this->transaction->getMessage(),
            "amount" => $this->transaction->getAuthorizedAmount(),
            "captured_amount" => $this->transaction->getCapturedAmount(),
            "refunded_amount" => $this->transaction->getRefundedAmount(),
            "payment_product" => $this->transaction->getPaymentProduct(),
            "payment_start" => $this->transaction->getDateCreated(),
            "payment_authorized" => $this->transaction->getDateAuthorized(),
            "authorization_code" => $this->transaction->getAuthorizationCode(),
            "basket" => $this->transaction->getBasket(),
            "attempt_create_multi_use" => (isset($customData["multiUse"]) && $customData["multiUse"]) ? 1 : 0,
            "customer_id" => $this->order->id_customer,
            "eci" => $this->transaction->getEci()
        );

        $this->dbMaintenance->setHipayTransaction($data);
        HipayOrderMessage::orderMessage(
            $this->module,
            $this->order->id,
            $this->order->id_customer,
            HipayOrderMessage::formatOrderData($this->module, $this->transaction)
        );
    }

    private function saveNotificationAttempt()
    {
        $data = array(
            "cart_id" => $this->cart->id,
            "transaction_ref" => $this->transaction->getTransactionReference(),
            "notification_code" => $this->transaction->getStatus()
        );

        $currentAttempt = $this->dbMaintenance->getNotificationAttempt($data);

        if (!$currentAttempt) {
            $currentAttempt = 1;
        } else {
            $currentAttempt += 1;
        }

        $this->log->logInfos("# Received Notification " . $data['notification_code'] . " for cart " . $data['cart_id'] . " (received " . $currentAttempt . " times)");

        $data['attempt_number'] = $currentAttempt;
        $data['status'] = NotificationStatus::IN_PROGRESS;

        $this->dbMaintenance->saveHipayNotification($data);

        return $currentAttempt;
    }

    private function updateNotificationState($status)
    {
        $data = array(
            "cart_id" => $this->cart->id,
            "transaction_ref" => $this->transaction->getTransactionReference(),
            "notification_code" => $this->transaction->getStatus(),
            "status" => $status
        );

        $data['attempt_number'] = $this->dbMaintenance->getNotificationAttempt($data);

        $this->log->logInfos("# Notification " . $data['notification_code'] . " for cart " . $data['cart_id'] . " (received " . $data['attempt_number'] . " times) is on status " . $status);

        $this->dbMaintenance->saveHipayNotification($data);
    }

    /**
     * we rename transaction reference to distinct every captured amount when transaction is partially captured
     * every step of the capture is unique (id = {transacRef}-{transactionAttempt}). Prevent from duplicates or overwritting
     *
     * @return string
     * @throws Exception
     */
    private function setTransactionRefForPrestashop()
    {

        $ref = $this->transaction->getTransactionReference();
        try {
            if ($this->transaction->getOperation() != null) {
                $ref .= "-" . $this->transaction->getOperation()->getId();
            } else {
                $operation = "BO_TPP";
                $maintenanceData = new HipayMaintenanceData($this->module);
                // retrieve number of capture or refund request
                $transactionAttempt = $maintenanceData->getNbOperationAttempt('BO_TPP', $this->order->id);
                $operationId = HipayHelper::generateOperationId($this->order, $operation, $transactionAttempt);
                $ref .= "-" . $operationId;
            }
        } catch (Exception $e) {
            $this->log->logException($e);
            throw $e;
        }
        return $ref;
    }

    /**
     * notification send total captured amount, we want just the amount concerned by the notification
     *
     * @param bool $refund
     * @return int
     */
    private function getRealCapturedAmount($refund = false)
    {
        $amount = $this->transaction->getCapturedAmount() - HipayHelper::getOrderPaymentAmount($this->order);

        if ($refund) {
            $amount = -1 * ($this->transaction->getRefundedAmount() - HipayHelper::getOrderPaymentAmount(
                $this->order,
                true
            ));
        }

        return $amount;
    }

    private function transactionIsValid()
    {
        if (in_array($this->transaction->getStatus(), self::REPEATABLE_NOTIFICATIONS)) {
            return true;
        } elseif (!in_array($this->transaction->getStatus(), $this->dbUtils->getNotificationsForOrder($this->order->id))) {
            return true;
        }

        return false;
    }
}
