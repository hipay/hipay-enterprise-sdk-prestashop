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
require_once dirname(__FILE__) . '/dbquery/HipayDBUtils.php';
require_once dirname(__FILE__) . '/dbquery/HipayDBMaintenance.php';
require_once dirname(__FILE__) . '/HipayMaintenanceData.php';
require_once dirname(__FILE__) . '/HipayHelper.php';
require_once dirname(__FILE__) . '/HipayOrderMessage.php';
require_once dirname(__FILE__) . '/HipayMail.php';
require_once dirname(__FILE__) . '/../apiHandler/ApiHandler.php';
require_once dirname(__FILE__) . '/../exceptions/PaymentProductNotFoundException.php';
require_once dirname(__FILE__) . '/../exceptions/NotificationException.php';

use HiPay\Fullservice\Enum\Transaction\TransactionStatus;
use HiPay\Fullservice\Gateway\Mapper\TransactionMapper;
use HiPay\Fullservice\Gateway\Model\Transaction;

/**
 * Handle notification from TPP.
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 *
 * @see    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class HipayNotification
{
    public const TRANSACTION_REF_CAPTURE_SUFFIX = 'capture';
    public const TRANSACTION_REF_REFUND_SUFFIX = 'refund';

    public const REPEATABLE_NOTIFICATIONS = [
        TransactionStatus::REFUND_REQUESTED,
        TransactionStatus::PARTIALLY_REFUNDED,
        TransactionStatus::CAPTURE_REQUESTED,
        TransactionStatus::CAPTURED,
        TransactionStatus::PARTIALLY_CAPTURED,
        TransactionStatus::CAPTURE_REFUSED,
    ];

    public const NO_ORDER_NEEDED_NOTIFICATIONS = [
        TransactionStatus::REFUSED,
        TransactionStatus::AUTHENTICATION_FAILED,
    ];

    /** @var Transaction */
    protected $transaction;
    /** @var Cart */
    protected $cart;
    /** @var HipayCCToken */
    protected $ccToken;
    /** @var Hipay_enterprise */
    protected $module;
    /** @var HipayLogs */
    protected $log;
    /** @var Context */
    protected $context;
    /** @var HipayDBUtils */
    protected $dbUtils;
    /** @var HipayDBMaintenance */
    protected $dbMaintenance;
    /** @var HipayConfig */
    protected $configHipay;
    /** @var Apihandler */
    protected $apiHandler;

    /**
     * HipayNotification constructor.
     *
     * @param Hipay_enterprise $moduleInstance
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function __construct($moduleInstance)
    {
        $this->module = $moduleInstance;
        $this->log = $this->module->getLogs();
        $this->context = Context::getContext();

        $this->context->language = new Language(Configuration::get('PS_LANG_DEFAULT'));

        $this->dbUtils = new HipayDBUtils($this->module);
        $this->dbMaintenance = new HipayDBMaintenance($this->module);
        $this->configHipay = $this->module->hipayConfigTool->getConfigHipay();
        $this->ccToken = new HipayCCToken($this->module);
        $this->apiHandler = new Apihandler($this->module, $this->context);
    }

    /**
     * Process notification.
     *
     * @param Transaction $transaction
     *
     * @return void
     *
     * @throws Exception
     * @throws NotificationException
     */
    public function handleNotification($transaction)
    {
        // if cart_id exist or not
        if (!$transaction->getOrder() || !$transaction->getOrder()->getId()) {
            $this->log->logErrors('Bad Callback initiated, no cart ID found ');
            exit('No cart found');
        }

        $cart = new Cart($transaction->getOrder()->getId());
        // check if cart is correctly loaded
        if (!Validate::isLoadedObject($cart)) {
            $this->log->logErrors('Bad Callback initiated, cart could not be initiated ');
            exit('Cart empty');
        }

        // forced shop
        Shop::setContext(Shop::CONTEXT_SHOP, $cart->id_shop);

        $this->log->logInfos('# handleNotification for cart ID : ' . $cart->id . ' and status ' . $transaction->getStatus());

        if (!$this->configHipay['account']['global']['notification_cron']) {
            $result = $this->processTransaction(
                $transaction,
                $cart,
                $this->getNotificationAttemptCount($transaction, $cart)
            );

            // Show result in response
            if (!is_null($result)) {
                echo $result;
            }
        } else {
            $this->saveNotificationAttempt($transaction, $cart, NotificationStatus::WAIT);

            echo 'Notification will be processed later';
        }
    }

    /**
     * Dispatch saved transactions.
     */
    public function dispatchWaitingNotifications()
    {
        $nbExpiredNotifications = $this->dbMaintenance->updateExpiredNotificationsInProgress();
        if ($nbExpiredNotifications > 0) {
            $this->log->logNotificationCron(
                '[DEBUG]: Retry ' . $nbExpiredNotifications . ' expired notifications on ' . NotificationStatus::IN_PROGRESS . ' status'
            );
        }

        $nbExpiredNotifications = $this->dbMaintenance->updateProcessingExpiredNotifications();
        if ($nbExpiredNotifications > 0) {
            $this->log->logNotificationCron(
                '[DEBUG]: Retry ' . $nbExpiredNotifications . ' expired notifications on ' . NotificationStatus::PROCESSING . ' status'
            );
        }

        $notifications = $this->dbMaintenance->getWaitingNotificationsAndUpdateStatus(
            NotificationStatus::IN_PROGRESS,
            $this->configHipay['account']['global']['notification_max_retry'] ?? 50
        );

        $totalError = 0;
        $totalNotification = 0;

        $this->log->logNotificationCron('[INFO]: START dispatching ' . count($notifications) . ' waiting notifications');

        foreach ($notifications as $notification) {
            ++$totalNotification;
            try {
                $this->dbMaintenance->updateNotificationStatusById($notification["hp_id"], NotificationStatus::PROCESSING);

                /** @var Transaction */
                $transaction = (new TransactionMapper(json_decode($notification['data'], true)))->getModelObjectMapped();

                $this->log->logNotificationCron('[INFO]:  Dispatching Notification for cart id ' . $transaction->getOrder()->getId() . ' and status ' . $transaction->getStatus());

                $result = $this->processTransaction(
                    $transaction,
                    new Cart($transaction->getOrder()->getId()),
                    $notification['attempt_number']
                );

                if (!is_null($result)) {
                    $this->log->logNotificationCron('[INFO]: ' . $result);
                }
            } catch (Exception $e) {
                ++$totalError;
                $this->log->logNotificationCron('[Error]: ' . $e->getMessage());
            }
        }

        $errorMessage = $totalError ? 'with ' . $totalError . ' errors' : 'without error';
        $this->log->logNotificationCron('[INFO]: END Dispatching ' . $totalNotification . ' notifications ' . $errorMessage);
    }

    /**
     * @param Transaction $transaction
     * @param Cart        $cart
     * @param int         $currentAttempt
     *
     * @return void|string
     *
     * @throws NotificationException
     * @throws PrestaShopException
     * @throws PrestaShopDatabaseException
     * @throws Exception
     */
    private function processTransaction($transaction, $cart, $currentAttempt)
    {
        $firstOrder = null;
        try {
            if (!empty($orders = $this->getOrdersByCartId($cart->id))) {
                $firstOrder = reset($orders);
                foreach ($orders as $order) {
                    $this->log->logInfos('# Order ' . $order->id . ' with cart ID ' . $cart->id);

                    if (!$this->controleIfStatusHistoryExist($order->id, Configuration::get('HIPAY_OS_PENDING'))) {
                        throw new NotificationException('Order not ready for order ID ' . $order->id . ' and cart ID ' . $cart->id, Context::getContext(), $this->module, 'HTTP/1.0 404 Not found');
                    }
                }
            } else {
                if (
                    in_array($transaction->getStatus(), [TransactionStatus::AUTHORIZED, TransactionStatus::AUTHORIZED_AND_PENDING])
                    && $currentAttempt >= Configuration::get('HIPAY_NOTIFICATION_THRESHOLD')
                    || $this->getPaymentProductConfig($transaction, 'orderOnPending')
                    && TransactionStatus::AUTHORIZATION_REQUESTED === $transaction->getStatus()
                ) {
                    $this->log->logInfos(
                        'Received ' . $currentAttempt . ' of ' . $transaction->getStatus() . ' notifications for cart : ' . $cart->id . ', creating order now'
                    );
                    $orders = $this->registerOrder($transaction, $cart, Configuration::get('HIPAY_OS_PENDING'));
                    $firstOrder = reset($orders);
                } else {
                    if (in_array($transaction->getStatus(), self::NO_ORDER_NEEDED_NOTIFICATIONS)) {
                        return null;
                    }

                    throw new NotificationException('Orders not found for cart ID ' . $cart->id, Context::getContext(), $this->module, 'HTTP/1.0 404 Not found');
                }
            }

            // When empty $order maybe we've got multiple orders from multiple carriers or vendors
            if (!$firstOrder) {
                $multipleCarrierOrder = true;
            } else {
                $this->dbUtils->setSQLLockForCart(
                    $firstOrder->id,
                    '# processTransaction ' . $transaction->getStatus() . ' for order ID : ' . $firstOrder->id
                );
            }

            foreach ($orders as $order) {
                $orderInBase = $this->dbUtils->getTransactionByOrderId($order->id);

                if (!$this->transactionIsValid($transaction->getStatus(), $order->id)) {
                    $this->updateNotificationState($transaction, NotificationStatus::NOT_HANDLED);
                    $message = 'Notification already received and handled.';

                    // If 116 notification is resent and if order in database has a different trx ref than trx ref in notif,
                    // it means double transaction with same Cart, we will cancel/refund the duplicate
                    if (
                        $transaction->getStatus() === TransactionStatus::AUTHORIZED
                        && $orderInBase
                        && $transaction->getTransactionReference() !== $orderInBase['transaction_ref']
                    ) {
                        // Try refund operation firstly because often captured after authorized
                        $this->log->logInfos('Starting duplicate transaction refund for order ' . $order->id . ' with Hipay transaction => ' . $transaction->getTransactionReference()
                            . ' and order in base transaction => ' . $orderInBase['transaction_ref']);

                        $refundOp = false;
                        // Do not refund with basket if it has not been sent during Order request
                        if ($this->dbMaintenance->getOrderBasket($order->id)) {
                            $refundOp = $this->apiHandler->handleRefund([
                                'transaction_reference' => $transaction->getTransactionReference(),
                                'order' => $order->id,
                                'amount' => $transaction->getAuthorizedAmount(),
                                'capture_refund_discount' => true,
                                'capture_refund_fee' => true,
                                'capture_refund_wrapping' => true,
                                'refundItems' => 'full',
                                'duplicate_order' => 1
                            ], $transaction->getEci());
                        }

                        if ($refundOp) {
                            $message = 'Found duplicate transaction which has been refunded for order ' . $order->id;
                        } else {
                            // Refund without basket if no basket sent or if previous refund has failed
                            $this->log->logInfos('Forcing Trigger Full refund without basket for order ' . $order->id);
                            $refundOp = $this->apiHandler->handleRefund([
                                'transaction_reference' => $transaction->getTransactionReference(),
                                'order' => $order->id,
                                'amount' => $transaction->getAuthorizedAmount(),
                                'duplicate_order' => 1
                            ], $transaction->getEci());

                            if ($refundOp) {
                                $message = 'Found duplicate transaction which has been refunded without basket for order ' . $order->id;
                            } else {
                                // If refund maintenance didn't worked, try cancel operation
                                if ($this->apiHandler->handleCancel([
                                    'order' => $order->id,
                                    'transaction_reference' => $transaction->getTransactionReference(),
                                    'duplicate_order' => 1
                                ], $transaction->getEci())) {
                                    $message = 'Found duplicate transaction which has been cancelled for order ' . $order->id;
                                } else {
                                    throw new NotificationException('Failed to cancel or refund duplicate transaction for order ' . $order->id, Context::getContext(), $this->module, 'HTTP/1.0 500 Internal server error');
                                }
                            }
                        }

                        $this->updateNotificationState($transaction, NotificationStatus::SUCCESS);
                    }

                    $this->dbUtils->releaseSQLLock(
                        $order->id,
                        $message . ' # processTransaction ' . $transaction->getStatus() . ' for cart ID : ' . $cart->id
                    );
                    return $message;
                }

                $orderHasBeenPaid = _PS_OS_OUTOFSTOCK_PAID_ == (int) $order->getCurrentState() ||
                    $this->controleIfStatusHistoryExist($order->id, _PS_OS_PAYMENT_);

                switch ($transaction->getStatus()) {
                    // Do nothing - Just log the status and skip further processing
                    default:
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
                        $orderState = 'skip';
                        break;
                    case TransactionStatus::BLOCKED:
                    case TransactionStatus::CHARGED_BACK:
                        if (!$orderHasBeenPaid) {
                            $this->updateOrderStatus($transaction, $order, Configuration::get('HIPAY_OS_CHARGEDBACK'));
                        }
                        break;
                    case TransactionStatus::DENIED:
                    case TransactionStatus::REFUSED:
                        if (!$orderHasBeenPaid) {
                            $this->updateOrderStatus($transaction, $order, Configuration::get('HIPAY_OS_DENIED'));

                            // Notify website admin for a challenged transaction
                            HipayMail::sendMailPaymentDeny($this->context, $this->module, $order);
                        }
                        break;
                    case TransactionStatus::AUTHORIZED_AND_PENDING:
                        if (!$orderHasBeenPaid) {
                            $this->updateOrderStatus($transaction, $order, Configuration::get('HIPAY_OS_CHALLENGED'));
                            // Notify website admin for a challenged transaction
                            HipayMail::sendMailPaymentFraud($this->context, $this->module, $order);
                        }
                        break;
                    case TransactionStatus::AUTHENTICATION_REQUESTED:
                    case TransactionStatus::AUTHORIZATION_REQUESTED:
                    case TransactionStatus::PENDING_PAYMENT:
                        // If pending and we have already received authorization, then we do not change the status
                        if (!$this->controleIfStatusHistoryExist($order->id, Configuration::get('HIPAY_OS_AUTHORIZED')) && !$orderHasBeenPaid) {
                            $this->updateOrderStatus($transaction, $order, Configuration::get('HIPAY_OS_PENDING'));
                        }
                        break;
                    case TransactionStatus::EXPIRED:
                        if (!$orderHasBeenPaid) {
                            $this->updateOrderStatus($transaction, $order, Configuration::get('HIPAY_OS_EXPIRED'));
                        }
                        break;
                    case TransactionStatus::AUTHORIZATION_CANCELLATION_REQUESTED:
                    case TransactionStatus::CANCELLED:
                        // For cancelled duplicate transaction, do not cancel original order
                        if (
                            $orderInBase
                            && $transaction->getTransactionReference() === $orderInBase['transaction_ref']
                            && !$orderHasBeenPaid
                        ) {
                            $this->updateOrderStatus($transaction, $order, _PS_OS_CANCELED_);
                        }
                        break;
                    case TransactionStatus::AUTHORIZED: // 116
                        if (!$orderHasBeenPaid) {
                            $this->updateOrderStatus($transaction, $order, Configuration::get('HIPAY_OS_AUTHORIZED'));
                        }
                        // set capture type on authorized
                        $this->setOrderCaptureType($transaction, $order);
                        break;
                    case TransactionStatus::CAPTURED: // 118
                        if ($this->controleIfStatusHistoryExist($order->id, Configuration::get('HIPAY_OS_AUTHORIZED'))) {
                            $orderState = _PS_OS_PAYMENT_;
                            if ($transaction->getCapturedAmount() < $transaction->getAuthorizedAmount()) {
                                $orderState = Configuration::get('HIPAY_OS_PARTIALLY_CAPTURED');
                            }
                            if (!$orderHasBeenPaid) {
                                $this->updateOrderStatus($transaction, $order, $orderState);
                                $this->captureOrder($transaction, $order);
                            }
                        } else {
                            throw new NotificationException('Order is not Authorized, could not capture Payment.', Context::getContext(), $this->module, 'HTTP/1.0 409 Conflict');
                        }
                        break;
                    case TransactionStatus::PARTIALLY_CAPTURED: // 119
                        if (!$orderHasBeenPaid) {
                            $this->updateOrderStatus($transaction, $order, Configuration::get('HIPAY_OS_PARTIALLY_CAPTURED'));
                            $this->captureOrder($transaction, $order);
                        }
                        break;
                    case TransactionStatus::REFUND_REQUESTED: // 124
                    case TransactionStatus::REFUNDED: // 125
                    case TransactionStatus::PARTIALLY_REFUNDED: // 126
                        $this->refundOrder($transaction, $order, $orderInBase);
                        break;
                    case TransactionStatus::CAPTURE_REFUSED:
                        if (!$orderHasBeenPaid) {
                            $this->updateOrderStatus($transaction, $order, Configuration::get('HIPAY_OS_DENIED'));
                        }
                        break;
                }
            }

            $this->dbUtils->releaseSQLLock(
                $firstOrder->id,
                '# processTransaction ' . $transaction->getStatus() . ' for cart ID : ' . $cart->id
            );

            /*
             * If 116 or 118, we save the token
             */
            if (
                TransactionStatus::AUTHORIZED === $transaction->getStatus() ||
                TransactionStatus::CAPTURED === $transaction->getStatus()
            ) {
                $customData = $transaction->getCustomData();
                if (isset($customData['multiUse']) && $customData['multiUse']) {
                    $this->saveCardToken($transaction, $cart->id_customer);
                }
            }

            if (isset($multipleCarrierOrder) && $multipleCarrierOrder === true) {
                // Trigger notification error for the next order
                throw new NotificationException(
                    'Multiple Vendor/Carrier order',
                    Context::getContext(),
                    $this->module,
                    'HTTP/1.0 404 Not found'
                );
            } else {
                $this->updateNotificationState($transaction, NotificationStatus::SUCCESS);
            }
        } catch (Exception $e) {
            $this->dbUtils->releaseSQLLock(
                $firstOrder->id,
                get_class($e) . ' # processTransaction ' . $transaction->getStatus() . ' for cart ID : ' . $cart->id
            );
            $this->log->logException($e);
            $this->updateNotificationState($transaction, NotificationStatus::ERROR);
            throw $e;
        }
    }

    /**
     * Update order status.
     *
     * @param Transaction $transaction
     * @param Order       $order
     * @param int         $newState
     *
     * @return void
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function updateOrderStatus($transaction, $order, $newState)
    {
        if (
            (int) $order->getCurrentState() !== (int) $newState
            || ((int) $order->getCurrentState() === (int) $newState && $transaction->getStatus() == TransactionStatus::PARTIALLY_REFUNDED)
        ) {
            // If order status is OUTOFSTOCK_UNPAID then new state will be OUTOFSTOCK_PAID
            if (
                $this->controleIfStatusHistoryExist($order->id, _PS_OS_OUTOFSTOCK_UNPAID_)
                && (_PS_OS_PAYMENT_ == $newState)
            ) {
                $newState = _PS_OS_OUTOFSTOCK_PAID_;
            }
            HipayHelper::changeOrderStatus($order, $newState);
            $this->addOrderMessage($transaction, $order);
        } elseif (!$this->dbMaintenance->isTransactionExist($order->id)) {
            $this->addOrderMessage($transaction, $order);
        }

        if (((int) $order->getCurrentState() === (int) $newState
                && $transaction->getStatus() == TransactionStatus::CAPTURED)
            && $transaction->getCapturedAmount() < $transaction->getAuthorizedAmount()
        ) {
            $this->addOrderMessage($transaction, $order);
        }

        if (
            TransactionStatus::CAPTURE_REQUESTED == $transaction->getStatus() &&
            $transaction->getCapturedAmount() < $transaction->getAuthorizedAmount()
        ) {
            $this->log->logInfos(
                'captured_amount (' .
                    $transaction->getCapturedAmount() .
                    ') is < than authorized_amount (' .
                    $transaction->getAuthorizedAmount() .
                    ')'
            );
        }
    }

    /**
     * register order if don't exist.
     *
     * @param Transaction $transaction
     * @param Cart        $cart
     * @param int         $state
     *
     * @return Order[]
     *
     * @throws Exception
     * @throws PrestaShopException
     */
    private function registerOrder($transaction, $cart, $state)
    {
        if (!HipayHelper::orderExists($cart->id)) {
            $this->log->logInfos('Register New order: ' . $cart->id);
            $message = HipayOrderMessage::formatOrderData($this->module, $transaction);

            // init context
            Context::getContext()->cart = new Cart((int) $cart->id);
            Context::getContext()->cart->getProducts();
            $address = new Address((int) Context::getContext()->cart->id_address_invoice);
            Context::getContext()->country = new Country((int) $address->id_country);
            Context::getContext()->customer = new Customer((int) Context::getContext()->cart->id_customer);
            Context::getContext()->language = new Language((int) Context::getContext()->cart->id_lang);
            Context::getContext()->currency = new Currency((int) Context::getContext()->cart->id_currency);
            $customer = new Customer((int) Context::getContext()->cart->id_customer);
            $shop_id = $cart->id_shop;
            $shop = new Shop($shop_id);
            Shop::setContext(Shop::CONTEXT_SHOP, $cart->id_shop);

            $paymentProductName = $this->getPaymentProductName($transaction);

            try {
                $this->log->logInfos('Prepare Validate order from registerOrder');
                $this->module->validateOrder(
                    Context::getContext()->cart->id,
                    $state,
                    (float) $transaction->getAuthorizedAmount(),
                    $paymentProductName,
                    $message,
                    [],
                    Context::getContext()->cart->id_currency,
                    false,
                    $customer->secure_key,
                    $shop
                );

                return [new Order($this->module->currentOrder)];
            } catch (Exception $e) {
                $this->log->logException($e);
                throw $e;
            }
        }

        $this->log->logInfos('Order already exists for cart : ' . $cart->id);

        return $this->getOrdersByCartId($cart->id);
    }

    /**
     * Save capture type from notification (required for capture and refund form).
     *
     * @param Transaction $transaction
     * @param Order       $order
     *
     * @return void
     *
     * @throws PrestaShopDatabaseException
     */
    private function setOrderCaptureType($transaction, $order)
    {
        if (null !== $order && !$this->dbMaintenance->OrderCaptureTypeExist($order->id)) {
            $customData = $transaction->getCustomData();

            $captureType = [
                'order_id' => $order->id,
                'type' => (isset($customData['captureType'])) ? $customData['captureType'] : 'automatic',
            ];

            $this->dbMaintenance->setOrderCaptureType($captureType);
        }
    }

    /**
     * Get the payment product config.
     *
     * @param Transaction $transaction
     * @param string|null $param       param name or null for all params
     *
     * @return mixed
     */
    private function getPaymentProductConfig($transaction, $param = null)
    {
        $paymentProductName = $transaction->getPaymentProduct();

        try {
            $paymentProduct = $this->module->hipayConfigTool->getPaymentProduct($paymentProductName);
        } catch (PaymentProductNotFoundException $e) {
            $paymentProduct = $paymentProductName;
        }

        if (null !== $param) {
            if (isset($paymentProduct[$param])) {
                return $paymentProduct[$param];
            }

            return false;
        }

        return $paymentProduct;
    }

    /**
     * @param Transaction $transaction
     *
     * @return string
     */
    private function getPaymentProductName($transaction)
    {
        return HipayHelper::getPaymentProductName(
            $this->getPaymentProductConfig($transaction),
            $this->module,
            $this->context->language
        );
    }

    /**
     * create order payment line.
     *
     * @param Transaction $transaction
     * @param Order       $order
     * @param bool        $refund
     *
     * @return void
     *
     * @throws Exception
     */
    private function createOrderPayment($transaction, $order, $refund = false)
    {
        if (HipayHelper::orderExists($transaction->getOrder()->getId())) {
            $amount = $this->getRealCapturedAmount($transaction, $order, $refund);
            if (0 != $amount) {
                $paymentProduct = $this->getPaymentProductName($transaction);
                $payment_transaction_id = $this->setTransactionRefForPrestashop($transaction, $order);
                $currency = new Currency($order->id_currency);
                $payment_date = date(HipayDBMaintenance::DATE_FORMAT);

                $invoices = $order->getInvoicesCollection();
                /** @var OrderInvoice|null */
                $invoice = $invoices && $invoices->getFirst() ? $invoices->getFirst() : null;

                if ($order && Validate::isLoadedObject($order)) {
                    $orderPaymentResult = false;

                    if ($refund) {
                        // Turn amount positive
                        $amount *= -1;

                        $operation = $transaction->getOperation();

                        // Get existing slips for this order
                        $orderSlipsRequest = $order
                            ->getOrderSlipsCollection()
                            ->orderBy('date_add', 'desc');

                        $orderSlips = $orderSlipsRequest->getResults();

                        $alreadyExists = false;
                        foreach ($orderSlips as $orderSlip) {
                            if (isset($operation) && strval($orderSlip->id) === $operation->getId()) {
                                // This notification already corresponds to an existing slip
                                // No need to create a new one
                                $alreadyExists = true;
                            }

                            // Fix amount by removing older refund amounts
                            $amount -= floatval($orderSlip->total_products_tax_incl) + floatval($orderSlip->total_shipping_tax_incl);
                        }

                        if (!$alreadyExists) {
                            $this->createOrderSlip($order, $transaction);
                        }
                    } else {
                        $orderPaymentResult = $order->addOrderPayment(
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
                        $this->log->logInfos("# Order payment created with success {$order->id}");
                        $orderPayment = $this->dbUtils->findOrderPayment(
                            $order->reference,
                            $payment_transaction_id
                        );
                        if ($orderPayment) {
                            $this->setOrderPaymentData($transaction, $orderPayment);
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
     * Handle refund products and create order slip.
     *
     * @param Order         $order
     * @param Transaction   $transaction
     *
     * @return void
     *
     * @throws Exception
     */
    private function createOrderSlip($order, $transaction)
    {
        $orderProducts = $order->getProducts();
        $transactionProducts = json_decode($transaction->getBasket()) !== null ? json_decode($transaction->getBasket()) : []; //good, fee, discount

        $refundedProducts = [];
        $fees = false;
        $discount = 0;
        $amountAlreadyRefunded = $this->dbMaintenance->getAmountRefunded($order->id);
        $isCompleteRefund = ((float) $transaction->getRefundedAmount() === (float) $transaction->getAuthorizedAmount()) && $amountAlreadyRefunded == 0;


        if ($isCompleteRefund && count($transactionProducts)) { // complete refund with basket
            foreach ($orderProducts as $orderProduct) {
                $orderDetail = new OrderDetail((int) $orderProduct['id_order_detail']);
                $orderDetail->total_refunded_tax_excl = (float) $orderDetail->total_price_tax_excl;
                $orderDetail->total_refunded_tax_incl = (float) $orderDetail->total_price_tax_incl;
                $orderProduct['quantity'] = $orderDetail->product_quantity;
                $orderProduct['unit_price'] = $orderDetail->unit_price_tax_excl;
                $refundedProducts[] = $orderProduct;
                $orderDetail->update();

                $stock_available = new StockAvailable(StockAvailable::getStockAvailableIdByProductId($orderProduct['product_id'], $orderProduct['product_attribute_id']));
                if (Validate::isLoadedObject($stock_available)) {
                    $stockAvailable = (int) StockAvailable::getQuantityAvailableByProduct($orderDetail->product_id, $orderDetail->product_attribute_id);
                    $quantityToAdd = (int) $orderDetail->product_quantity - (int) $orderDetail->product_quantity_refunded;
                    $newQuantity = $stockAvailable + $quantityToAdd;
                    $stock_available->quantity = $newQuantity;
                    $stock_available->update();
                }
            }

            $fees = (float) $order->total_shipping_tax_excl;
            $discount = (float) $order->total_discounts_tax_incl;
        } elseif (!$isCompleteRefund && count($transactionProducts)) { // partial refund with basket
            foreach ($transactionProducts as $transactionProduct) {
                switch ($transactionProduct->type) {
                    case 'good':
                        foreach ($orderProducts as $orderProduct) {
                            $productCombination = new Combination($orderProduct["product_attribute_id"]);
                            $productAttributes = $productCombination->getAttributesName((int)Context::getContext()->language->id);
                            if (empty($productAttributes)) {
                                $orderProductReference = $this->sanitize_string($orderProduct["product_reference"] . "-n-a");
                            } else {
                                $orderProductReference = $this->sanitize_string($orderProduct["product_reference"] . "-" . $productAttributes[0]["name"]);
                            }
                            if ($transactionProduct->product_reference == $orderProductReference) {
                                $orderDetail = new OrderDetail((int) $orderProduct['id_order_detail']);
                                $orderDetail->total_refunded_tax_excl = ($orderDetail->product_quantity_refunded + $transactionProduct->quantity) * $orderDetail->unit_price_tax_excl;
                                $orderDetail->total_refunded_tax_incl = ($orderDetail->product_quantity_refunded + $transactionProduct->quantity) * $orderDetail->unit_price_tax_incl;
                                $orderProduct['quantity'] = $transactionProduct->quantity;
                                $orderProduct['unit_price'] = $orderDetail->unit_price_tax_excl;
                                $refundedProducts[] = $orderProduct;
                                $orderDetail->update();

                                $stock_available = new StockAvailable(StockAvailable::getStockAvailableIdByProductId($orderProduct['product_id'], $orderProduct['product_attribute_id']));
                                if (Validate::isLoadedObject($stock_available)) {
                                    $newQuantity = StockAvailable::getQuantityAvailableByProduct($orderDetail->product_id, $orderDetail->product_attribute_id) + (int)$transactionProduct->quantity;
                                    $stock_available->quantity = $newQuantity;
                                    $stock_available->update();
                                }
                                break;
                            }
                        }
                        break;
                    case 'fee':
                        $fees = (float) $order->total_shipping_tax_excl;
                        break;
                    case 'discount':
                        $discount = (float) $order->total_discounts_tax_incl;
                        break;
                    default:
                        break;
                }
            }
        } else { // complete or partial refund without basket
            if ($isCompleteRefund) {
                foreach ($orderProducts as $product) {
                    $order_detail = new OrderDetail((int) $product['id_order_detail']);

                    $order_detail->total_refunded_tax_excl = (float) $order_detail->total_price_tax_excl;
                    $order_detail->total_refunded_tax_incl = (float) $order_detail->total_price_tax_incl;
                    $order_detail->product_quantity_refunded = $order_detail->product_quantity;
                    $order_detail->update();

                    // Prepare the product for OrderSlip creation
                    $product['quantity'] = $order_detail->product_quantity;
                    $product['unit_price'] = $order_detail->unit_price_tax_excl;
                    $refundedProducts[] = $product;
                }

                // Create the order slip with all products
                $_POST['cancelProduct'] = true;
                OrderSlip::create(
                    $order,
                    $refundedProducts,
                    (float) $order->total_shipping_tax_excl, // Include shipping in complete refund
                    (float) $order->total_discounts_tax_incl // Include discounts in complete refund
                );
            } else {
                $orderProducts = $order->getProducts();
                $product = end($orderProducts);
                $amountToRefund = (float) $transaction->getRefundedAmount() - (float) $this->dbMaintenance->getAmountRefunded($order->id);

                $order_detail = new OrderDetail((int) $product['id_order_detail']);
                $_POST['cancelProduct'] = true;
                $tax_calculator = $order_detail->getTaxCalculator();
                $amountToRefund = $tax_calculator->removeTaxes($amountToRefund);

                $product['unit_price'] = $amountToRefund;
                $product['product_quantity_refunded'] = $order_detail->product_quantity_refunded;
                $product['quantity'] = 1;

                if (($order_detail->product_quantity_refunded + 1) <= $order_detail->product_quantity) {
                    $this->completeRefundProcess($order, $product);
                } else {
                    foreach ($orderProducts as $availableProduct) {
                        $available_order_detail = new OrderDetail((int) $availableProduct['id_order_detail']);
                        if (($available_order_detail->product_quantity_refunded + 1) <= $available_order_detail->product_quantity) {
                            $availableProduct['unit_price'] = $amountToRefund;
                            $availableProduct['product_quantity_refunded'] = $available_order_detail->product_quantity_refunded;
                            $availableProduct['quantity'] = 1;

                            $this->completeRefundProcess($order, $availableProduct);
                            break;
                        }
                    }
                }
            }

        }

        if ((count($refundedProducts) && count($transactionProducts)) || $fees > 0) {
            OrderSlip::create(
                $order,
                $refundedProducts,
                $fees,
                (float) $discount
            );
        }
    }

    /**
     * Save card Token for recurring payment.
     *
     * @param Transaction $transaction
     * @param int         $customerId
     *
     * @return void
     *
     * @throws Exception
     */
    private function saveCardToken($transaction, $customerId)
    {
        try {
            $paymentMethod = $transaction->getPaymentMethod();

            if ($paymentMethod !== null) {
                $card = [
                    'pan'               => $paymentMethod->getPan(),
                    'card_holder'       => $paymentMethod->getCardHolder(),
                    'token'             => $paymentMethod->getToken(),
                    'brand'             => strtolower($paymentMethod->getBrand()),
                    'card_expiry_month' => $paymentMethod->getCardExpiryMonth(),
                    'card_expiry_year'  => $paymentMethod->getCardExpiryYear(),
                    'authorized'        => 1
                ];

                $this->ccToken->saveCC($customerId, $card);
            }
        } catch (Exception $e) {
            $this->log->logException($e);
            throw $e;
        }
    }

    /**
     * @param Transaction  $transaction
     * @param OrderPayment $orderPayment
     *
     * @return void
     *
     * @throws Exception
     */
    private function setOrderPaymentData($transaction, $orderPayment)
    {
        try {
            if (null != $transaction->getPaymentMethod()) {
                $orderPayment->card_number = $transaction->getPaymentMethod()->getPan();
                $orderPayment->card_brand = $transaction->getPaymentMethod()->getBrand();
                $orderPayment->card_expiration = $transaction->getPaymentMethod()->getCardExpiryMonth() .
                    '/' .
                    $transaction->getPaymentMethod()->getCardExpiryYear();
                $orderPayment->card_holder = $transaction->getPaymentMethod()->getCardHolder();
                $orderPayment->update();
            }
        } catch (Exception $e) {
            $this->log->logException($e);
            throw $e;
        }
    }

    /**
     * Capture amount sent by notification.
     *
     * @param Transaction $transaction
     * @param Order       $order
     *
     * @return true
     *
     * @throws Exception
     */
    private function captureOrder($transaction, $order)
    {
        $this->log->logInfos('# Capture Order ' . $order->reference);

        $this->dbUtils->deleteOrderPaymentDuplicate($order);

        // If Capture is originated in the TPP BO the Operation field is null
        // Otherwise transaction has already been saved
        if (null == $transaction->getOperation() && $transaction->getAttemptId() < 2) {
            try {
                $maintenanceData = new HipayMaintenanceData($this->module);
                // retrieve number of capture or refund request
                $transactionAttempt = $maintenanceData->getNbOperationAttempt('BO_TPP', $order->id);

                // save capture items and quantity in prestashop
                $captureData = [
                    'hp_ps_order_id' => $order->id,
                    'hp_ps_product_id' => 0,
                    'operation' => 'BO_TPP',
                    'type' => 'BO',
                    'attempt_number' => $transactionAttempt + 1,
                    'quantity' => 1,
                    'amount' => 0,
                ];

                $this->dbMaintenance->setCaptureOrRefundOrder($captureData);
            } catch (Exception $e) {
                $this->log->logException($e);
                throw $e;
            }
        }

        // if transaction doesn't exist we create an order payment (if multiple capture, 1 line by amount captured)
        if (0 == $this->dbUtils->countOrderPayment($order->reference, $this->setTransactionRefForPrestashop($transaction, $order))) {
            $this->createOrderPayment($transaction, $order);
        }

        return true;
    }

    /**
     * Refund order.
     *
     * @param Transaction $transaction
     * @param Order       $order
     * @param mixed|false $orderInBase
     *
     * @return true
     *
     * @throws Exception
     */
    private function refundOrder($transaction, $order, $orderInBase)
    {
        $this->log->logInfos('# Refund Order {' . $order->reference . '} with refund amount {' . $transaction->getRefundedAmount() . '}');

        // For refunded duplicate transaction, do not refund original order
        if (
            HipayHelper::orderExists($transaction->getOrder()->getId())
            && $orderInBase
            && $transaction->getTransactionReference() === $orderInBase['transaction_ref']
        ) {
            // If Capture is originated in the TPP BO the Operation field is null
            // Otherwise transaction has already been saved
            if (null == $transaction->getOperation() && $transaction->getAttemptId() < 2) {
                // save refund items and quantity in prestashop
                $maintenanceData = new HipayMaintenanceData($this->module);
                // retrieve number of capture or refund request
                $transactionAttempt = $maintenanceData->getNbOperationAttempt('BO_TPP', $order->id);

                $captureData = [
                    'hp_ps_order_id' => $order->id,
                    'hp_ps_product_id' => 0,
                    'operation' => 'BO_TPP',
                    'type' => 'BO',
                    'quantity' => 1,
                    'amount' => 0,
                    'attempt_number' => $transactionAttempt + 1,
                ];

                $this->dbMaintenance->setCaptureOrRefundOrder($captureData);
            }

            if (TransactionStatus::REFUND_REQUESTED == $transaction->getStatus()) {
                $this->updateOrderStatus($transaction, $order, Configuration::get('HIPAY_OS_REFUND_REQUESTED'));

                return true;
            }

            // if transaction doesn't exist we create an order payment (if multiple refund, 1 line by amount refunded)
            if (0 == $this->dbUtils->countOrderPayment($order->reference, $this->setTransactionRefForPrestashop($transaction, $order))) {
                $this->createOrderPayment($transaction, $order, true);

                // force refund order status
                if ($transaction->getRefundedAmount() == $transaction->getAuthorizedAmount()) {
                    $this->log->logInfos('# RefundOrder: ' . Configuration::get('HIPAY_OS_REFUNDED'));
                    $this->updateOrderStatus($transaction, $order, Configuration::get('HIPAY_OS_REFUNDED'));
                } else {
                    $this->log->logInfos(
                        '# RefundOrder: ' . Configuration::get('HIPAY_OS_REFUNDED_PARTIALLY')
                    );
                    $this->updateOrderStatus($transaction, $order, Configuration::get('HIPAY_OS_REFUNDED_PARTIALLY'));
                }
            }
        }

        return true;
    }

    /**
     * check if order is already at status $paymentStatus.
     *
     * @param int    $orderId
     * @param string $paymentStatus
     *
     * @return bool
     */
    private function controleIfStatusHistoryExist($idOrder, $paymentStatus)
    {
        if ($idOrder) {
            $this->log->logInfos('# controleIfStatusHistoryExist Checking if Status ' . $paymentStatus . ' exists in history for order ' . $idOrder);

            return $this->dbUtils->checkOrderStatusExist($paymentStatus, $idOrder);
        }

        $this->log->logInfos("# controleIfStatusHistoryExist Order doesn't exist, can't check status");

        return false;
    }

    /**
     * add private order message with transaction data (json).
     *
     * @param Transaction $transaction
     * @param Order       $order
     *
     * @return void
     */
    private function addOrderMessage($transaction, $order)
    {
        $customData = $transaction->getCustomData();
        $amountAlreadyRefunded = $this->dbMaintenance->getAmountRefunded($order->id);
        $amountAlreadyCaptured = $this->dbMaintenance->getAmountCaptured($order->id);
        $data = [
            'order_id' => $order->id,
            'transaction_ref' => $transaction->getTransactionReference(),
            'state' => $transaction->getState(),
            'status' => $transaction->getStatus(),
            'message' => $transaction->getMessage(),
            'amount' => $transaction->getAuthorizedAmount(),
            'captured_amount' => (string) ((float) $transaction->getCapturedAmount() - (float) $amountAlreadyCaptured),
            'refunded_amount' => (string) ((float) $transaction->getRefundedAmount() - (float) $amountAlreadyRefunded),
            'payment_product' => $transaction->getPaymentProduct(),
            'payment_start' => $transaction->getDateCreated(),
            'payment_authorized' => $transaction->getDateAuthorized(),
            'authorization_code' => $transaction->getAuthorizationCode(),
            'basket' => $transaction->getBasket(),
            'attempt_create_multi_use' => (isset($customData['multiUse']) && $customData['multiUse']) ? 1 : 0,
            'customer_id' => $order->id_customer,
            'eci' => $transaction->getEci(),
            'reference_to_pay' => $transaction->getReferenceToPay()
        ];

        $this->dbMaintenance->setHipayTransaction($data);
        HipayOrderMessage::orderMessage(
            $this->module,
            $order->id,
            $order->id_customer,
            HipayOrderMessage::formatOrderData($this->module, $transaction)
        );
    }

    /**
     * @param Transaction $transaction
     * @param Cart        $cart
     * @param string      $status
     *
     * @return int
     *
     * @throws PrestaShopException
     * @throws PrestaShopDatabaseException
     */
    private function saveNotificationAttempt($transaction, $cart, $status = NotificationStatus::IN_PROGRESS)
    {
        $data = [
            'cart_id' => $cart->id,
            'transaction_ref' => $transaction->getTransactionReference(),
            'notification_code' => $transaction->getStatus(),
            'status' => $status,
        ];

        $currentAttempt = $this->dbMaintenance->getNotificationAttempt($data) + 1;

        $this->log->logInfos('# Received Notification ' . $data['notification_code'] . ' for cart ' . $data['cart_id'] . ' (received ' . $currentAttempt . ' times)');

        $data += [
            'attempt_number' => $currentAttempt,
            'status' => $status,
            'data' => $transaction->toJson(),
            'updated_at' => (new DateTime())->format(HipayDBMaintenance::DATE_FORMAT),
        ];

        $this->dbMaintenance->saveHipayNotification($data);

        return $currentAttempt;
    }

    /**
     * @param Transaction $transaction
     * @param string      $status
     *
     * @return void
     *
     * @throws PrestaShopException
     * @throws PrestaShopDatabaseException
     */
    private function updateNotificationState($transaction, $status)
    {
        $data = [
            'cart_id' => $transaction->getOrder()->getId(),
            'transaction_ref' => $transaction->getTransactionReference(),
            'notification_code' => $transaction->getStatus(),
            'status' => $status,
            'attempt_number' => $transaction->getAttemptId(),
            'updated_at' => (new DateTime())->format(HipayDBMaintenance::DATE_FORMAT)
        ];

        $this->log->logInfos('# Notification ' . $data['notification_code'] . ' for cart ' . $data['cart_id'] . ' is on status ' . $status . ' with ' . $data['attempt_number'] . ' attempts');

        if ($status === NotificationStatus::ERROR) {
            $data['attempt_number'] = $this->dbMaintenance->getNotificationAttempt($data) + 1;
        }

        $this->dbMaintenance->saveHipayNotification($data);
    }

    /**
     * we rename transaction reference to distinct every captured amount when transaction is partially captured
     * every step of the capture is unique (id = {transacRef}-{transactionAttempt}). Prevent from duplicates or overwritting.
     *
     * @param Transaction $transaction
     * @param Order       $order
     *
     * @return string
     *
     * @throws Exception
     */
    private function setTransactionRefForPrestashop($transaction, $order)
    {
        $ref = $transaction->getTransactionReference();
        try {
            if (null != $transaction->getOperation()) {
                $ref .= '-' . $transaction->getOperation()->getId();
            } else {
                $operation = 'BO_TPP';
                $maintenanceData = new HipayMaintenanceData($this->module);
                // retrieve number of capture or refund request
                $transactionAttempt = $maintenanceData->getNbOperationAttempt('BO_TPP', $order->id);
                $operationId = HipayHelper::generateOperationId($order, $operation, $transactionAttempt);
                $ref .= '-' . $operationId;
            }
        } catch (Exception $e) {
            $this->log->logException($e);
            throw $e;
        }

        return $ref;
    }

    /**
     * notification send total captured amount, we want just the amount concerned by the notification.
     *
     * @param Transaction $transaction
     * @param Order       $order
     * @param bool        $refund
     *
     * @return int
     */
    private function getRealCapturedAmount($transaction, $order, $refund = false)
    {
        $amount = $transaction->getCapturedAmount() - HipayHelper::getOrderPaymentAmount($order);

        if ($refund) {
            $amount = -1 * ($transaction->getRefundedAmount() - HipayHelper::getOrderPaymentAmount(
                $order,
                true
            ));
        }

        return $amount;
    }

    /**
     * @param string $status
     * @param int    $orderId
     *
     * @return bool
     */
    private function transactionIsValid($status, $orderId)
    {
        if (
            in_array($status, self::REPEATABLE_NOTIFICATIONS) ||
            !in_array($status, $this->dbUtils->getNotificationsForOrder($orderId))
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param int cardId
     *
     * @return Order[]
     *
     * @throws PrestaShopException
     * @throws PrestaShopDatabaseException
     */
    private function getOrdersByCartId($cartId)
    {
        $orders = [];
        foreach ($this->dbUtils->getOrderIdsByCartId($cartId) as $orderId) {
            $orders[] = new Order($orderId);
        }

        return $orders;
    }

    /**
     * @param Transaction $transaction
     * @param Cart $cart
     * @param string $status
     * @return int
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function getNotificationAttemptCount($transaction, $cart,  $status = NotificationStatus::IN_PROGRESS)
    {
        $data = [
            'cart_id' => $cart->id,
            'transaction_ref' => $transaction->getTransactionReference(),
            'notification_code' => $transaction->getStatus(),
            'status' => $status,
        ];

        return $this->dbMaintenance->getNotificationAttempt($data);
    }

    /**
     * Remove accents
     *
     * @param string string
     *
     * @return string
     */
    function remove_accents($string)
    {
        if (!preg_match('/[\x80-\xff]/', $string)) {
            return $string;
        }

        $chars = [
            'À' => 'A',
            'Á' => 'A',
            'Â' => 'A',
            'Ã' => 'A',
            'Ä' => 'A',
            'Å' => 'A',
            'Æ' => 'AE',
            'Ç' => 'C',
            'È' => 'E',
            'É' => 'E',
            'Ê' => 'E',
            'Ë' => 'E',
            'Ì' => 'I',
            'Í' => 'I',
            'Î' => 'I',
            'Ï' => 'I',
            'Ð' => 'D',
            'Ñ' => 'N',
            'Ò' => 'O',
            'Ó' => 'O',
            'Ô' => 'O',
            'Õ' => 'O',
            'Ö' => 'O',
            'Ø' => 'O',
            'Ù' => 'U',
            'Ú' => 'U',
            'Û' => 'U',
            'Ü' => 'U',
            'Ý' => 'Y',
            'Þ' => 'Th',
            'ß' => 'ss',
            'à' => 'a',
            'á' => 'a',
            'â' => 'a',
            'ã' => 'a',
            'ä' => 'a',
            'å' => 'a',
            'æ' => 'ae',
            'ç' => 'c',
            'è' => 'e',
            'é' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'ì' => 'i',
            'í' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'ð' => 'd',
            'ñ' => 'n',
            'ò' => 'o',
            'ó' => 'o',
            'ô' => 'o',
            'õ' => 'o',
            'ö' => 'o',
            'ø' => 'o',
            'ù' => 'u',
            'ú' => 'u',
            'û' => 'u',
            'ü' => 'u',
            'ý' => 'y',
            'þ' => 'th',
            'ÿ' => 'y'
        ];

        return strtr($string, $chars);
    }

    /**
     * Remove accents and lowercase text
     *
     * @param string string
     *
     * @return string
     */
    function sanitize_string($string)
    {
        $string = mb_strtolower($string, 'UTF-8');
        $string = $this->remove_accents($string);

        return $string;
    }

    /**
     * Complete the process of refund and create order slip
     *
     * @param Order $order
     * @param $availableProduct
     * @return void
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function completeRefundProcess($order, $availableProduct)
    {
        OrderSlip::create($order, [$availableProduct]);

        $available_order_detail = new OrderDetail((int)$availableProduct['id_order_detail']);
        $available_order_detail->product_quantity_refunded += 1;
        $available_order_detail->total_refunded_tax_excl = $available_order_detail->product_quantity_refunded * $available_order_detail->unit_price_tax_excl;
        $available_order_detail->total_refunded_tax_incl = $available_order_detail->product_quantity_refunded * $available_order_detail->unit_price_tax_incl;
        $available_order_detail->update();
    }
}
