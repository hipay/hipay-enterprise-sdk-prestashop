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
require_once(dirname(__FILE__) . '/hipayDBQuery.php');

use HiPay\Fullservice\Enum\Transaction\TransactionStatus;

class hipayNotification {

    protected $transaction;
    protected $cart;
    protected $orderExist = false;
    protected $order = null;

    public function __construct($moduleInstance, $data) {
        $this->module = $moduleInstance;
        $this->log = $this->module->getLogs();
        $this->context = Context::getContext();
        $this->db = new HipayDBQuery($this->module);

        $this->transaction = (new HiPay\Fullservice\Gateway\Mapper\TransactionMapper($data))->getModelObjectMapped();
        $this->log->logsHipay("###### Mapped Transaction #####");
        $this->log->logsHipay(print_r($this->transaction, true));

        print_r($this->transaction);
        // if cart_id exist or not
        if ($this->transaction->getOrder() == null || $this->transaction->getOrder()->getId() == null) {
            $this->log->errorLogsHipay('Bad Callback initiated, no cart ID found ');
            die('No cart found');
        }

        $this->cart = new Cart($this->transaction->getOrder()->getId());

        // check if cart is correctly loaded
        if (!Validate::isLoadedObject($this->cart)) {
            $this->log->errorLogsHipay('Bad Callback initiated, cart could not be initiated ');
            die('Cart empty');
        }

        $this->log->logsHipay('---------- Boutique N°' . $this->cart->id_shop);
        $this->log->logsHipay('---------- panier exist ID = ' . $this->cart->id);

        if ($this->cart->orderExists()) {
            // il existe une commande associée à ce panier
            $this->orderExist = true;
            // init de l'id de commande
            $idOrder = Order::getOrderByCartId($this->cart->id);
            if ($idOrder) {
                $this->order = new Order((int) $idOrder);
                $this->log->logsHipay('---------- objOrder initialisé');
            }
            $this->log->logsHipay('---------- order_exist = ' . $this->orderExist);
            $this->log->logsHipay('---------- id_order = ' . $idOrder);
        }
    }

    /**
     * Process notification
     */
    public function processTransaction() {

        try {
            // SQL LOCK 
            //#################################################################
            $this->db->setSQLLockForCart($this->cart->id);

            switch ($this->transaction->getStatus()) {
                // Do nothing - Just log the status and skip further processing
                case TransactionStatus::CREATED : // Created
                case TransactionStatus::CARD_HOLDER_ENROLLED : // Cardholder Enrolled 3DSecure
                case TransactionStatus::CARD_HOLDER_NOT_ENROLLED : // Cardholder Not Enrolled 3DSecure
                case TransactionStatus::UNABLE_TO_AUTHENTICATE : // Unable to Authenticate 3DSecure
                case TransactionStatus::CARD_HOLDER_AUTHENTICATED : // Cardholder Authenticate
                case TransactionStatus::AUTHENTICATION_ATTEMPTED : // Authentication Attempted
                case TransactionStatus::COULD_NOT_AUTHENTICATE : // Could Not Authenticate
                case TransactionStatus::AUTHENTICATION_FAILED : // Authentication Failed
                case TransactionStatus::COLLECTED : // Collected
                case TransactionStatus::ACQUIRER_FOUND : // Acquirer Found
                case TransactionStatus::ACQUIRER_NOT_FOUND : // Acquirer not Found
                case TransactionStatus::RISK_ACCEPTED : // Risk Accepted
                default :
                    $orderState = 'skip';
                    break;
                // Status _PS_OS_ERROR_
                case TransactionStatus::BLOCKED : // Blocked
                case TransactionStatus::CHARGED_BACK : // Charged Back
                    $this->updateOrderStatus(_PS_OS_ERROR_);
                    break;
                // Status HIPAY_DENIED
                case TransactionStatus::DENIED : // Denied
                case TransactionStatus::REFUSED : // Refused
                    // ctrl if payment accepted is already in order history
                    if (!$this->controleIfStatushistoryExist(_PS_OS_PAYMENT_, Configuration::get('HIPAY_OS_DENIED', null, null, 1), true)) {
                        $this->updateOrderStatus(Configuration::get('HIPAY_OS_DENIED', null, null, 1));
                    }
                    break;
                // Status HIPAY_CHALLENGED
                case TransactionStatus::AUTHORIZED_AND_PENDING : // Authorized and Pending
                    $this->updateOrderStatus(Configuration::get('HIPAY_OS_CHALLENGED', null, null, 1));
                    break;
                // Status HIPAY_PENDING
                case TransactionStatus::AUTHENTICATION_REQUESTED : // Authentication Requested
                case TransactionStatus::AUTHORIZATION_REQUESTED : // Authorization Requested
                case TransactionStatus::PENDING_PAYMENT : // Pending Payment
                    $this->updateOrderStatus(Configuration::get('HIPAY_OS_PENDING', null, null, 1));
                    break;
                // Status HIPAY_EXPIRED
                case TransactionStatus::EXPIRED : // Expired
                    $this->updateOrderStatus(Configuration::get('HIPAY_OS_EXPIRED', null, null, 1));
                    break;
                // Status _PS_OS_CANCELED_
                case TransactionStatus::CANCELLED : // Cancelled
                    $this->updateOrderStatus(_PS_OS_CANCELED_);
                    break;
                // Status HIPAY_AUTHORIZED
                case TransactionStatus::AUTHORIZED: //116
                    $this->updateOrderStatus(Configuration::get("HIPAY_OS_AUTHORIZED"));
                    break;
                // Status HIPAY_CAPTURE_REQUESTED
                case TransactionStatus::CAPTURED :
                case TransactionStatus::CAPTURE_REQUESTED : // Capture Requested
                    $orderState = _PS_OS_PAYMENT_;
                    if ($this->transaction->getCapturedAmount() < $this->transaction->getAuthorizedAmount()) {
                        $orderState = Configuration::get('HIPAY_OS_PARTIALLY_CAPTURED', null, null, 1);
                    }
                    // on change de statut de la commande tous les critères sont ok
                    if ($this->updateOrderStatus($orderState)) {
                        // on modifie la transaction de commande pour la référence commande
                        $this->captureOrder();
                    }
                    break;
                // Status HIPAY_PARTIALLY_CAPTURED
                case TransactionStatus::PARTIALLY_CAPTURED : // Partially Captured
                    // on change de statut de la commande tous les critères sont ok
                    if ($this->updateOrderStatus(Configuration::get('HIPAY_OS_PARTIALLY_CAPTURED', null, null, 1))) {
                        // on modifie la transaction de commande pour la référence commande
                        $this->captureOrder();
                    }
                    break;
                // Status HIPAY_REFUND_REQUESTED
                case TransactionStatus::REFUND_REQUESTED : // Refund Requested
                    $orderState = $stt_refunded_rq;
                    // si commande existante, on modifie le statut de la commande
                    if ($this->updateOrderStatus(Configuration::get('HIPAY_OS_REFUND_REQUESTED', null, null, 1))) {
//                        $statuts = array(
//                            'refund_requested' => $orderState,
//                        );
//                        $this->addMessageRefund($objOrder, $callback_arr, $hipay, $statuts);
                    }
                    break;
                // Status HIPAY_REFUNDED
                case TransactionStatus::REFUNDED : // Refunded
                    // si commande existante, on modifie le statut de la commande
                    if ($this->updateOrderStatus(Configuration::get('HIPAY_OS_REFUNDED', null, null, 1))) {
//                        $this->refundOrder($callback_arr, $objOrder, $hipay, $stt_refunded);
//                        $this->addMessageRefund($objOrder, $callback_arr, $hipay, $orderState);
                    }
                    break;
                // Status HIPAY_CHARGED BACK
                case TransactionStatus::CHARGED_BACK : // Charged back
                    // si commande existante, on modifie le statut de la commande
                    $this->updateOrderStatus(Configuration::get('HIPAY_OS_CHARGEDBACK', null, null, 1));
                    break;
                // Status HIPAY_CAPTURE_REFUSED
                case TransactionStatus::CAPTURE_REFUSED : // Capture Refused
                    // si commande existante, on modifie le statut de la commande
                    $this->updateOrderStatus(Configuration::get('HIPAY_OS_CAPTURE_REFUSED', null, null, 1));
                    break;
            }



            $this->db->releaseSQLLock();
            // END SQL LOCK 
            //#################################################################
        } catch (Exception $ex) {
            $this->db->releaseSQLLock();
        }
    }

    /**
     * update order status or create order if it doesn't exist
     * @param type $newState
     * @return boolean
     */
    private function updateOrderStatus($newState) {

        $return = true;

        if ($this->orderExist) {
            if ((int) $this->order->getCurrentState() != (int) $newState && !$this->controleIfStatushistoryExist(_PS_OS_PAYMENT_, $newState, true)) {

                $this->addOrderMessage();

                $orderHistory = new OrderHistory();
                $orderHistory->id_order = $this->order->id;
                $orderHistory->changeIdOrderState($newState, $this->order->id, true);
                $orderHistory->add();

                $return = true;
            }
            if ($this->transaction->getStatus() == TransactionStatus::CAPTURED) {
                $this->addOrderMessage();
            }
            $this->addHipayCaptureMessage();

            if ($this->transaction->getStatus() == TransactionStatus::CAPTURE_REQUESTED && $this->transaction->getCapturedAmount() < $this->transaction->getAuthorizedAmount()) {
                $this->log->logsHipay('--------------- captured_amount (' . $this->transaction->getCapturedAmount() . ') is < to authorized_amount (' . $this->transaction->getAuthorizedAmount() . ')');
                $return = true;
            }
            return $return;
        } else {
            $this->log->logsHipay('--------------- no status changed because there are no order');
            return $this->registerOrder($newState);
        }
    }

    /**
     * register order if don't exist
     * @param type $state
     */
    private function registerOrder($state) {
        if (!$this->orderExist) {

            $message = array(
                "transaction_ref" => $this->transaction->getTransactionReference(),
                "state" => $this->transaction->getState(),
                "status" => $this->transaction->getStatus(),
                "message" => $this->transaction->getMessage(),
                "amount" => $this->transaction->getAuthorizedAmount(),
                    //  "basket" => ($this->transaction->getBasket() != null) ? true : false
            );

            $message = Tools::jsonEncode($message);

            // init context
            Context::getContext()->cart = new Cart((int) $this->cart->id);
            $address = new Address((int) Context::getContext()->cart->id_address_invoice);
            Context::getContext()->country = new Country((int) $address->id_country);
            Context::getContext()->customer = new Customer((int) Context::getContext()->cart->id_customer);
            Context::getContext()->language = new Language((int) Context::getContext()->cart->id_lang);
            Context::getContext()->currency = new Currency((int) Context::getContext()->cart->id_currency);
            $customer = new Customer((int) Context::getContext()->cart->id_customer);
            $shop_id = $this->cart->id_shop;
            $shop = new Shop($shop_id);
            Shop::setContext(Shop::CONTEXT_SHOP, $this->cart->id_shop);

            try {
                $this->module->validateOrder(
                        Context::getContext()->cart->id, $state, (float) $this->transaction->getAuthorizedAmount(), $this->module->displayName . ' via ' . ucfirst($this->transaction->getPaymentProduct()), $message, array(), Context::getContext()->cart->id_currency, false, $customer->secure_key, $shop
                );
                $this->order = new Order($this->module->currentOrder);

                return true;
            } catch (Exception $e) {
                $this->log->logsHipay($e->getCode() . ' : ' . $e->getMessage());
                return false;
            }
        }
        return true;
    }

    /**
     * create order payment line
     */
    private function createOrderPayment() {
        if ($this->orderExist) {
            $amount = $this->getRealCapturedAmount(); 
            $payment_method = HipayDBQuery::HIPAY_PAYMENT_ORDER_PREFIX . " " . (string) ucwords($this->transaction->getPaymentProduct());
            $payment_transaction_id = $this->transaction->getTransactionReference() . '-' . $this->transaction->getCapturedAmount();
            $currency = new Currency($this->order->id_currency);
            $payment_date = date("Y-m-d H:i:s");
            $order_invoice = null;

            if ($this->order && Validate::isLoadedObject($this->order)) {
                // Add order payment 
                if ($this->order->addOrderPayment($amount, $payment_method, $payment_transaction_id, $currency, $payment_date, $order_invoice)) {
                    // LOG
                    $this->log->logsHipay('--------------- Order payment add with success');
                    // Add message for this status
                    $this->addOrderMessage();
                }
            } else {
                $this->log->logsHipay('--------------- Error, order exist but the object order not loaded');
            }
        }
    }

    /**
     * capture amount sent by notification
     * @return boolean
     */
    private function captureOrder() {
        $this->log->logsHipay('--------------- Capture Order');

        $paymentMethod = null;

        $this->db->deleteOrderPaymentDuplicate($this->order->reference);

        // if transaction doesn't exist we create an order payment (if multiple capture, 1 line by amount captured)
        if ($this->db->countOrderPayment($this->order->reference, $this->setTransactionRefForPrestashop()) == 0) {
            $this->log->logsHipay('--------------- Create Order Payment');
            $this->createOrderPayment();
        }
//        else {
//        if ($this->transaction->getPaymentMethod() != null) {
//            $paymentMethod = array(
//                "pan" => $this->transaction->getPaymentMethod()->getPan(),
//                "brand" => $this->transaction->getPaymentMethod()->getBrand(),
//                "card_expiry_month" => $this->transaction->getPaymentMethod()->getCardExpiryMonth(),
//                "card_expiry_year" => $this->transaction->getPaymentMethod()->getCardExpiryYear(),
//                "card_holder" => $this->transaction->getPaymentMethod()->getCardHolder(),
//            );
//        }
//
//        $paymentData = array(
//            "payment_method" => $paymentMethod,
//            "captured_amount" => $this->getRealCapturedAmount(),
//            "transaction_id" => $this->setTransactionRefForPrestashop(),
//            "name" => ucwords($this->transaction->getPaymentProduct()),
//            "order_reference" => $this->order->reference
//        );
//            $this->log->logsHipay('--------------- Update Order Payment');
//            $this->db->updateOrderPayment($paymentData);
//        }
        // set invoice order
        if ($this->transaction->getStatus() == TransactionStatus::CAPTURE_REQUESTED || $this->transaction->getStatus() == TransactionStatus::CAPTURED) {
            $this->db->setInvoiceOrder($this->order);
        }

        $this->addOrderMessage();

        return true;
    }

    /**
     * create or update order private message with total captured amount
     */
    private function addHipayCaptureMessage() {

        $tag = 'HIPAY_CAPTURE ';

        $amount = ($this->transaction->getStatus() == TransactionStatus::AUTHORIZED ? '0.00' : (($this->transaction->getCapturedAmount() != null) ? $this->transaction->getCapturedAmount() : '0.00'));
        $msgs = Message::getMessagesByOrderId($this->order->id, true);
        $create_new_msg = true;
        if (count($msgs)) {
            foreach ($msgs as $msg) {
                $line = $msg['message'];
                if ($tag === "" || strpos($line, $tag) === 0) {
                    $create_new_msg = false;
                    $to_update_msg = new Message($msg['id_message']);
                    $to_update_msg->message = $tag . $amount;
                    $to_update_msg->save();
                    break;
                }
            }
        }
        if ($create_new_msg) {
            // Create msg
            $msg = new Message ();
            $message = $tag . $amount;
            $message = strip_tags($message, '<br>');
            if (Validate::isCleanHtml($message)) {
                $msg->message = $message;
                $msg->id_order = (int) $this->order->id;
                $msg->private = 1;
                $msg->add();
            }
        }
    }

    /**
     * check if order is already at status "payment accepted"
     * @param type $paymentStatus
     * @param type $orderState
     * @param type $forceCtrl
     * @return boolean
     */
    private function controleIfStatushistoryExist($paymentStatus, $orderState, $forceCtrl = false) {
        $this->log->logsHipay('--------------- controleIfStatushistoryExist ----- ');

        $this->log->logsHipay('--------------- Status : ' . $orderState);

        if (($orderState == $paymentStatus || $forceCtrl) && $this->order != null) {
            $this->log->logsHipay('--------------- Control Action: TRUE');
            $this->log->logsHipay('--------------- Status Exist: TRUE');
            return $this->db->checkOrderStatusExist($paymentStatus, $this->order->id);
        }

        $this->log->logsHipay('--------------- Status Exist: FALSE');
        return false;
    }

    /**
     * add private order message with transaction data (json)
     */
    private function addOrderMessage() {
        $data = array(
            "transaction_ref" => $this->transaction->getTransactionReference(),
            "state" => $this->transaction->getState(),
            "status" => $this->transaction->getStatus(),
            "message" => $this->transaction->getMessage(),
            "amount" => $this->transaction->getAuthorizedAmount(),
            "data" => $this->transaction->getCdata1(),
            "payment_product" => $this->transaction->getPaymentProduct(),
            "payment_start" => $this->transaction->getDateCreated(),
            "payment_authorized" => $this->transaction->getDateAuthorized(),
            "authorization_code" => $this->transaction->getAuthorizationCode(),
            "currency" => $this->transaction->getCurrency(),
            "ip_adress" => $this->transaction->getIpAddress(),
                //  "basket" => ($this->transaction->getBasket() != null) ? true : false
        );

        $message = new Message();
        $message->message = Tools::jsonEncode($data);
        $message->id_order = (int) $this->order->id;
        $message->private = 1;
        $message->add();
    }

    /**
     * we rename transaction reference to distinct every captured amount when transaction is partially captured
     * every step of the capture is unique (id = {transacRef}-{CapturedAmount}). Prevent from duplicates or overwritting
     * @return string
     */
    private function setTransactionRefForPrestashop() {
        return $this->transaction->getTransactionReference() . "-" . $this->transaction->getCapturedAmount();
    }

    /**
     * notification send total captured amount, we want just the amount concerned by the notification
     * @return type
     */
    private function getRealCapturedAmount() {

        return $this->transaction->getCapturedAmount() - $this->order->getTotalPaid();
    }

}
