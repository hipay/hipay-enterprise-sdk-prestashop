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
require_once(dirname(__FILE__) . '/HipayDBQuery.php');
require_once(dirname(__FILE__) . '/HipayMaintenanceData.php');
require_once(dirname(__FILE__) . '/HipayHelper.php');
require_once(dirname(__FILE__) . '/HipayOrderMessage.php');
require_once(dirname(__FILE__) . '/HipayMail.php');

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

    protected $transaction;
    protected $cart;
    protected $orderExist = false;
    protected $order = null;
    protected $ccToken;

    /**
     * HipayNotification constructor.
     * @param $moduleInstance
     * @param $data
     */
    public function __construct($moduleInstance, $data)
    {
        $this->module = $moduleInstance;
        $this->log = $this->module->getLogs();
        $this->context = Context::getContext();

        $this->context->language = new Language(Configuration::get('PS_LANG_DEFAULT'));

        $this->db = new HipayDBQuery($this->module);
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
     */
    public function processTransaction()
    {
        try {
            $this->db->setSQLLockForCart($this->cart->id, "# ProcessTransaction for cart ID : " . $this->cart->id);
            $this->log->logInfos(
                "# ProcessTransaction for cart ID : " .
                $this->cart->id .
                " and status " .
                $this->transaction->getStatus()
            );

            //Fix Bug where Order is created while transaction is processed
            if (HipayHelper::orderExists($this->cart->id)) {
                // can't use Order::getOrderByCartId 'cause add shop restrictions
                $idOrder = $this->db->getOrderByCartId($this->cart->id);
                if ($idOrder) {
                    $this->order = new Order((int)$idOrder);
                    $this->log->logInfos("# Order with cart ID {$this->cart->id} ");
                }
            }

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
                default:
                    $orderState = 'skip';
                    break;
                case TransactionStatus::BLOCKED:
                case TransactionStatus::CHARGED_BACK:
                    $this->updateOrderStatus(_PS_OS_ERROR_);
                    break;
                case TransactionStatus::DENIED:
                case TransactionStatus::REFUSED:
                    if (!$this->controleIfStatushistoryExist(
                        _PS_OS_PAYMENT_,
                        Configuration::get('HIPAY_OS_DENIED', null, null, 1),
                        true
                    )
                    ) {
                        $this->updateOrderStatus(Configuration::get('HIPAY_OS_DENIED', null, null, 1));

                        // Notify website admin for a challenged transaction
                        HipayMail::sendMailPaymentDeny($this->context, $this->module, $this->order);
                    }
                    break;
                case TransactionStatus::AUTHORIZED_AND_PENDING:
                    $this->updateOrderStatus(Configuration::get('HIPAY_OS_CHALLENGED', null, null, 1));
                    // Notify website admin for a challenged transaction
                    HipayMail::sendMailPaymentFraud($this->context, $this->module, $this->order);
                    break;
                case TransactionStatus::AUTHENTICATION_REQUESTED:
                case TransactionStatus::AUTHORIZATION_REQUESTED:
                case TransactionStatus::PENDING_PAYMENT:
                    $this->updateOrderStatus(Configuration::get('HIPAY_OS_PENDING', null, null, 1));
                    break;
                case TransactionStatus::EXPIRED:
                    $this->updateOrderStatus(Configuration::get('HIPAY_OS_EXPIRED', null, null, 1));
                    break;
                case TransactionStatus::CANCELLED:
                    $this->updateOrderStatus(_PS_OS_CANCELED_);
                    break;
                case TransactionStatus::AUTHORIZED: //116
                    $this->updateOrderStatus(Configuration::get("HIPAY_OS_AUTHORIZED"));

                    $customData = $this->transaction->getCustomData();
                    if (isset($customData["createOneClick"]) && $customData["createOneClick"]) {
                        $this->saveCardToken();
                    }
                    break;
                case TransactionStatus::CAPTURED: //118
                case TransactionStatus::CAPTURE_REQUESTED: //117
                    $orderState = _PS_OS_PAYMENT_;
                    if ($this->transaction->getCapturedAmount() < $this->transaction->getAuthorizedAmount()) {
                        $orderState = Configuration::get('HIPAY_OS_PARTIALLY_CAPTURED', null, null, 1);
                    }
                    if ($this->updateOrderStatus($orderState)) {
                        $this->captureOrder();
                    }
                    break;
                case TransactionStatus::PARTIALLY_CAPTURED: //119
                    if ($this->updateOrderStatus(Configuration::get('HIPAY_OS_PARTIALLY_CAPTURED', null, null, 1))) {
                        $this->captureOrder();
                    }
                    break;
                case TransactionStatus::REFUND_REQUESTED: //124
                case TransactionStatus::REFUNDED: //125
                case TransactionStatus::PARTIALLY_REFUNDED: //126
                    $this->refundOrder();
                    break;
                case TransactionStatus::CHARGED_BACK:
                    $this->updateOrderStatus(Configuration::get('HIPAY_OS_CHARGEDBACK', null, null, 1));
                    break;
                case TransactionStatus::CAPTURE_REFUSED:
                    $this->updateOrderStatus(Configuration::get('HIPAY_OS_CAPTURE_REFUSED', null, null, 1));
                    break;
            }


            $this->db->releaseSQLLock("# ProcessTransaction for cart ID : " . $this->cart->id);
        } catch (Exception $e) {
            $this->db->releaseSQLLock("Exception # ProcessTransaction for cart ID : " . $this->cart->id);
            $this->log->logException($e);
            throw $e;
        }
    }

    /**
     * update order status or create order if it doesn't exist
     *
     * @param $newState
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function updateOrderStatus($newState)
    {
        $return = true;
        if (HipayHelper::orderExists($this->cart->id)) {
            $this->addOrderMessage();
            if ((int)$this->order->getCurrentState() != (int)$newState &&
                (int)$this->order->getCurrentState() != _PS_OS_OUTOFSTOCK_PAID_ &&
                !$this->controleIfStatushistoryExist(_PS_OS_PAYMENT_, $newState, true)
            ) {
                // If order status is OUTOFSTOCK_UNPAID then new state will be OUTOFSTOCK_PAID
                if (($this->controleIfStatushistoryExist(_PS_OS_OUTOFSTOCK_UNPAID_, $newState, true))
                    && ($newState == _PS_OS_PAYMENT_)
                ) {
                    $newState = _PS_OS_OUTOFSTOCK_PAID_;
                }
                $this->changeOrderStatus($newState);
                $return = true;
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
                $return = true;
            }
            return $return;
        } else {
            $this->log->logInfos('no status changed because there are no order');
            return $this->registerOrder($newState);
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

            $paymentProduct = $this->getPaymentProductName();

            try {
                $this->log->logInfos('Prepare Validate order from registerOrder');
                $this->module->validateOrder(
                    Context::getContext()->cart->id,
                    $state,
                    (float)$this->transaction->getAuthorizedAmount(),
                    $paymentProduct,
                    $message,
                    array(),
                    Context::getContext()->cart->id_currency,
                    false,
                    $customer->secure_key,
                    $shop
                );
                $this->order = new Order($this->module->currentOrder);

                $captureType = array(
                    "order_id" => $this->order->id,
                    "type" => $this->configHipay["payment"]["global"]["capture_mode"]
                );

                $this->db->setOrderCaptureType($captureType);

                $this->addOrderMessage();
                return true;
            } catch (Exception $e) {
                $this->log->logException($e);
                throw $e;
            }
        }
        return true;
    }

    /**
     * @return mixed
     */
    private function getPaymentProductName()
    {
        $cardBrand = false;
        if ($this->transaction->getPaymentMethod() != null) {
            $cardBrand = $this->transaction->getPaymentMethod()->getBrand();
        }
        $paymentProduct = $this->transaction->getPaymentProduct();

        return HipayHelper::getPaymentProductName(
            $cardBrand,
            $paymentProduct,
            $this->module,
            $this->context->language->iso_code
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
                    // Add order payment
                    if ($this->order->addOrderPayment(
                        $amount,
                        $paymentProduct,
                        $payment_transaction_id,
                        $currency,
                        $payment_date,
                        $invoice
                    )
                    ) {
                        $this->log->logInfos("# Order payment created with success {$this->order->id}");
                        $orderPayment = $this->db->findOrderPayment($this->order->reference, $payment_transaction_id);
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
                $configCC = $this->module->hipayConfigTool->getPaymentCreditCard()[strtolower($this->transaction->getPaymentMethod()->getBrand())];

                if (isset($configCC['recurring']) && $configCC['recurring']) {

                    $card = array(
                        "token" => $this->transaction->getPaymentMethod()->getToken(),
                        "brand" => $this->transaction->getPaymentMethod()->getBrand(),
                        "pan" => $this->transaction->getPaymentMethod()->getPan(),
                        "card_holder" => $this->transaction->getPaymentMethod()->getCardHolder(),
                        "card_expiry_month" => $this->transaction->getPaymentMethod()->getCardExpiryMonth(),
                        "card_expiry_year" => $this->transaction->getPaymentMethod()->getCardExpiryYear(),
                        "issuer" =>  $this->transaction->getPaymentMethod()->getIssuer(),
                        "country" =>  $this->transaction->getPaymentMethod()->getCountry()
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

        $this->db->deleteOrderPaymentDuplicate($this->order->reference);

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

                $this->db->setCaptureOrRefundOrder($captureData);
            } catch (Exception $e) {
                $this->log->logException($e);
                throw $e;
            }
        }

        // if transaction doesn't exist we create an order payment (if multiple capture, 1 line by amount captured)
        if ($this->db->countOrderPayment($this->order->reference, $this->setTransactionRefForPrestashop()) == 0) {
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
            $this->addOrderMessage();

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

                $this->db->setCaptureOrRefundOrder($captureData);
            }

            if ($this->transaction->getStatus() == TransactionStatus::REFUND_REQUESTED) {
                $this->changeOrderStatus(Configuration::get('HIPAY_OS_REFUND_REQUESTED', null, null, 1));
                return true;
            }

            // if transaction doesn't exist we create an order payment (if multiple refund, 1 line by amount refunded)
            if ($this->db->countOrderPayment($this->order->reference, $this->setTransactionRefForPrestashop()) == 0) {
                $this->createOrderPayment(true);

                //force refund order status
                if ($this->transaction->getRefundedAmount() == $this->transaction->getAuthorizedAmount()) {
                    $this->log->logInfos('# RefundOrder: ' . Configuration::get('HIPAY_OS_REFUNDED', null, null, 1));
                    $this->changeOrderStatus(Configuration::get('HIPAY_OS_REFUNDED', null, null, 1));
                } else {
                    $this->log->logInfos(
                        '# RefundOrder: ' . Configuration::get('HIPAY_OS_REFUNDED_PARTIALLY', null, null, 1)
                    );

                    $this->changeOrderStatus(Configuration::get('HIPAY_OS_REFUNDED_PARTIALLY', null, null, 1));
                }
            }
        }

        return true;
    }

    /**
     * change order status
     *
     * @param $newState
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function changeOrderStatus($newState)
    {
        $orderHistory = new OrderHistory();
        $orderHistory->id_order = $this->order->id;
        $orderHistory->changeIdOrderState($newState, $this->order, true);

        $orderHistory->addWithemail(true);
    }

    /**
     * check if order is already at status "payment accepted"
     *
     * @param $paymentStatus
     * @param $orderState
     * @param bool $forceCtrl
     * @return bool
     */
    private function controleIfStatushistoryExist($paymentStatus, $orderState, $forceCtrl = false)
    {
        $this->log->logInfos("# ControleIfStatushistoryExist Status " . $orderState);

        if (($orderState == $paymentStatus || $forceCtrl) && $this->order != null) {
            $this->log->logInfos("# ControleIfStatushistoryExist Status exist");
            return $this->db->checkOrderStatusExist($paymentStatus, $this->order->id);
        }

        $this->log->logInfos("# ControleIfStatushistoryExist Status not exist");
        return false;
    }

    /**
     * add private order message with transaction data (json)
     */
    private function addOrderMessage()
    {
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
            "basket" => $this->transaction->getBasket()
        );

        $this->db->setHipayTransaction($data);
        HipayOrderMessage::orderMessage($this->module, $this->order->id, $this->order->id_customer, $this->transaction);
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
            $amount = -1 * ($this->transaction->getRefundedAmount() - HipayHelper::getOrderPaymentAmount($this->order,
                        true));
        }

        return $amount;
    }
}
