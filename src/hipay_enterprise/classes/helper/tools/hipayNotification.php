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
require_once(dirname(__FILE__).'/../../../lib/vendor/autoload.php');
require_once(dirname(__FILE__).'/hipayDBQuery.php');
require_once(dirname(__FILE__).'/hipayOrderMessage.php');
require_once(dirname(__FILE__).'/HipayMail.php');

use HiPay\Fullservice\Enum\Transaction\TransactionStatus;

class HipayNotification
{
    const TRANSACTION_REF_CAPTURE_SUFFIX = "capture";
    const TRANSACTION_REF_REFUND_SUFFIX  = "refund";

    protected $transaction;
    protected $cart;
    protected $orderExist = false;
    protected $order      = null;

    public function __construct($moduleInstance, $data)
    {
        $this->module  = $moduleInstance;
        $this->log     = $this->module->getLogs();
        $this->context = Context::getContext();
        $this->db      = new HipayDBQuery($this->module);

        $this->transaction = (new HiPay\Fullservice\Gateway\Mapper\TransactionMapper($data))->getModelObjectMapped();
        $this->log->logInfos(
            print_r(
                $this->transaction,
                true
            )
        );

        //  print_r($this->transaction);
        // if cart_id exist or not
        if ($this->transaction->getOrder() == null || $this->transaction->getOrder()->getId() == null
        ) {
            $this->log->logErrors('Bad Callback initiated, no cart ID found ');
            die('No cart found');
        }

        $this->cart = new Cart($this->transaction->getOrder()->getId());

        // check if cart is correctly loaded
        if (!Validate::isLoadedObject($this->cart)) {
            $this->log->logErrors('Bad Callback initiated, cart could not be initiated ');
            die('Cart empty');
        }

        // forced shop
        Shop::setContext(
            Shop::CONTEXT_SHOP,
            $this->cart->id_shop
        );

        if ($this->cart->orderExists()) {
            // il existe une commande associée à ce panier
            $this->orderExist = true;
            // init de l'id de commande
            // can't use Order::getOrderByCartId 'cause
            $idOrder          = Order::getOrderByCartId($this->cart->id);
            if ($idOrder) {
                $this->order = new Order((int) $idOrder);
                $this->log->logInfos("# Order with cart ID {$this->cart->id} ");
            }
        }
    }

    /**
     *
     * @return type
     */
    public function getEci()
    {
        return $this->transaction->getEci();
    }

    /**
     * Process notification
     */
    public function processTransaction()
    {
        try {

            $this->db->setSQLLockForCart($this->cart->id);
            $this->log->logInfos("# ProcessTransaction for cart ID : ".$this->cart->id.
                " and status ".$this->transaction->getStatus());

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
                            Configuration::get(
                                'HIPAY_OS_DENIED',
                                null,
                                null,
                                1
                            ),
                            true
                        )
                    ) {
                        $this->updateOrderStatus(
                            Configuration::get(
                                'HIPAY_OS_DENIED',
                                null,
                                null,
                                1
                            )
                        );

                        // Notify website admin for a challenged transaction
                        HipayMail::sendMailPaymentDeny($this->context,
                            $this->module,
                            $this->order);
                    }
                    break;
                case TransactionStatus::AUTHORIZED_AND_PENDING:
                    $this->updateOrderStatus(
                        Configuration::get(
                            'HIPAY_OS_CHALLENGED',
                            null,
                            null,
                            1
                        )
                    );
                    // Notify website admin for a challenged transaction
                    HipayMail::sendMailPaymentFraud($this->context,
                        $this->module,
                        $this->order);
                    break;
                case TransactionStatus::AUTHENTICATION_REQUESTED:
                case TransactionStatus::AUTHORIZATION_REQUESTED:
                case TransactionStatus::PENDING_PAYMENT:
                    $this->updateOrderStatus(
                        Configuration::get(
                            'HIPAY_OS_PENDING',
                            null,
                            null,
                            1
                        )
                    );
                    break;
                case TransactionStatus::EXPIRED:
                    $this->updateOrderStatus(
                        Configuration::get(
                            'HIPAY_OS_EXPIRED',
                            null,
                            null,
                            1
                        )
                    );
                    break;
                case TransactionStatus::CANCELLED:
                    $this->updateOrderStatus(_PS_OS_CANCELED_);
                    break;
                case TransactionStatus::AUTHORIZED: //116
                    $this->updateOrderStatus(Configuration::get("HIPAY_OS_AUTHORIZED"));
                    break;
                case TransactionStatus::CAPTURED: //118
                case TransactionStatus::CAPTURE_REQUESTED: //117
                    $orderState = _PS_OS_PAYMENT_;
                    if ($this->transaction->getCapturedAmount() < $this->transaction->getAuthorizedAmount()) {
                        $orderState = Configuration::get(
                                'HIPAY_OS_PARTIALLY_CAPTURED',
                                null,
                                null,
                                1
                        );
                    }
                    if ($this->updateOrderStatus($orderState)) {
                        $this->captureOrder();
                    }
                    break;
                case TransactionStatus::PARTIALLY_CAPTURED: //119
                    if ($this->updateOrderStatus(
                            Configuration::get(
                                'HIPAY_OS_PARTIALLY_CAPTURED',
                                null,
                                null,
                                1
                            )
                        )
                    ) {
                        $this->captureOrder();
                    }
                    break;
                case TransactionStatus::REFUND_REQUESTED: //124
                case TransactionStatus::REFUNDED: //125
                    $this->refundOrder();
                    break;
                case TransactionStatus::CHARGED_BACK:
                    $this->updateOrderStatus(
                        Configuration::get(
                            'HIPAY_OS_CHARGEDBACK',
                            null,
                            null,
                            1
                        )
                    );
                    break;
                case TransactionStatus::CAPTURE_REFUSED:
                    $this->updateOrderStatus(
                        Configuration::get(
                            'HIPAY_OS_CAPTURE_REFUSED',
                            null,
                            null,
                            1
                        )
                    );
                    break;
            }


            $this->db->releaseSQLLock();
            // END SQL LOCK
            //#################################################################
        } catch (Exception $ex) {
            $this->logs->logException($ex);
            $this->db->releaseSQLLock();
        }
    }

    /**
     * update order status or create order if it doesn't exist
     * @param type $newState
     * @return boolean
     */
    private function updateOrderStatus($newState)
    {
        $return = true;
        if ($this->orderExist) {
            $this->addOrderMessage();
            if ((int) $this->order->getCurrentState() != (int) $newState && !$this->controleIfStatushistoryExist(
                    _PS_OS_PAYMENT_,
                    $newState,
                    true
                ) && !$this->controleIfStatushistoryExist(
                    _PS_OS_OUTOFSTOCK_UNPAID_,
                    $newState,
                    true
                )
            ) {
                $this->changeOrderStatus($newState);
                $return = true;
            }
            $this->addHipayCaptureMessage();

            if ($this->transaction->getStatus() == TransactionStatus::CAPTURE_REQUESTED && $this->transaction->getCapturedAmount()
                < $this->transaction->getAuthorizedAmount()
            ) {
                $this->log->logInfos(
                    'captured_amount ('.$this->transaction->getCapturedAmount(
                    ).') is < than authorized_amount ('.$this->transaction->getAuthorizedAmount().')'
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
     * @param type $state
     */
    private function registerOrder($state)
    {
        if (!$this->orderExist) {
            $message = HipayOrderMessage::formatOrderData($this->transaction);

            // init context
            Context::getContext()->cart     = new Cart((int) $this->cart->id);
            $address                        = new Address((int) Context::getContext()->cart->id_address_invoice);
            Context::getContext()->country  = new Country((int) $address->id_country);
            Context::getContext()->customer = new Customer((int) Context::getContext()->cart->id_customer);
            Context::getContext()->language = new Language((int) Context::getContext()->cart->id_lang);
            Context::getContext()->currency = new Currency((int) Context::getContext()->cart->id_currency);
            $customer                       = new Customer((int) Context::getContext()->cart->id_customer);
            $shop_id                        = $this->cart->id_shop;
            $shop                           = new Shop($shop_id);
            Shop::setContext(
                Shop::CONTEXT_SHOP,
                $this->cart->id_shop
            );

            try {
                $this->module->validateOrder(
                    Context::getContext()->cart->id,
                    $state,
                    (float) $this->transaction->getAuthorizedAmount(),
                    Tools::ucfirst($this->transaction->getPaymentProduct()),
                    $message,
                    array(),
                    Context::getContext()->cart->id_currency,
                    false,
                    $customer->secure_key,
                    $shop
                );
                $this->order = new Order($this->module->currentOrder);

                $this->addOrderMessage();
                return true;
            } catch (Exception $e) {
                $this->log->logException($e);
                return false;
            }
        }
        return true;
    }

    /**
     * create order payment line
     */
    private function createOrderPayment($refund = false)
    {
        if ($this->orderExist) {
            $amount                 = $this->getRealCapturedAmount($refund);
            $payment_method         = HipayDBQuery::HIPAY_PAYMENT_ORDER_PREFIX." ".(string) ucwords(
                    $this->transaction->getPaymentProduct()
            );
            $payment_transaction_id = $this->setTransactionRefForPrestashop($refund);
            $currency               = new Currency($this->order->id_currency);
            $payment_date           = date("Y-m-d H:i:s");
            $order_invoice          = null;

            if ($this->order && Validate::isLoadedObject($this->order)) {
                // Add order payment
                if ($this->order->addOrderPayment(
                        $amount,
                        $payment_method,
                        $payment_transaction_id,
                        $currency,
                        $payment_date,
                        $order_invoice
                    )
                ) {
                    $this->log->logInfos("# Order payment created with success {$this->order->id}");
                }
            } else {
                $this->log->logErrors('# Error, order exist but the object order not loaded');
            }
        }
    }

    /**
     * Capture amount sent by notification
     *
     * @return boolean
     */
    private function captureOrder()
    {
        $this->log->logInfos("# Capture Order {$this->order->reference}");

        $this->db->deleteOrderPaymentDuplicate($this->order->reference);



        if ($this->transaction->getOperation() == NULL) {
            //save capture items and quantity in prestashop
            $captureData = array(
                "hp_ps_order_id" => $this->order->id,
                "hp_ps_product_id" => 0,
                "operation" => 'BO_TPP',
                "type" => 'BO',
                "quantity" => 1,
                "amount" => 0
            );

            $this->db->setCaptureOrRefundOrder($captureData);
        }

        // if transaction doesn't exist we create an order payment (if multiple capture, 1 line by amount captured)
        if ($this->db->countOrderPayment(
                $this->order->reference,
                $this->setTransactionRefForPrestashop()
            ) == 0
        ) {
            $this->createOrderPayment();
        }
        // set invoice order
        if ($this->transaction->getStatus() == TransactionStatus::CAPTURE_REQUESTED || $this->transaction->getStatus() == TransactionStatus::CAPTURED
        ) {
            $this->db->setInvoiceOrder($this->order);
        }

        return true;
    }

    /**
     *  Refund order
     *
     * @return bool
     */
    private function refundOrder()
    {
        $this->log->logInfos("# Refund Order {$this->order->reference} with refund amount {$this->transaction->getRefundedAmount()}");

        if ($this->orderExist) {
            $this->addOrderMessage();

            if ($this->transaction->getOperation() == NULL) {
                //save capture items and quantity in prestashop
                $captureData = array(
                    "hp_ps_order_id" => $this->order->id,
                    "hp_ps_product_id" => 0,
                    "operation" => 'BO_TPP',
                    "type" => 'BO',
                    "quantity" => 1,
                    "amount" => 0
                );

                $this->db->setCaptureOrRefundOrder($captureData);
            }

            if ($this->transaction->getStatus() == TransactionStatus::REFUND_REQUESTED) {
                $this->changeOrderStatus(
                    Configuration::get(
                        'HIPAY_OS_REFUND_REQUESTED',
                        null,
                        null,
                        1
                    )
                );
                return true;
            }

            // if transaction doesn't exist we create an order payment (if multiple capture, 1 line by amount captured)
            if ($this->db->countOrderPayment(
                    $this->order->reference,
                    $this->setTransactionRefForPrestashop(true)
                ) == 0
            ) {
                $this->createOrderPayment(true);

                //force refund order status
                if ($this->transaction->getRefundedAmount() == $this->transaction->getAuthorizedAmount()) {
                    $this->log->logInfos(
                        '# RefundOrder: '.Configuration::get(
                            'HIPAY_OS_REFUNDED',
                            null,
                            null,
                            1
                        )
                    );
                    $this->changeOrderStatus(
                        Configuration::get(
                            'HIPAY_OS_REFUNDED',
                            null,
                            null,
                            1
                        )
                    );
                } else {
                    $this->log->logInfos(
                        '# RefundOrder: '.Configuration::get(
                            'HIPAY_OS_REFUNDED_PARTIALLY',
                            null,
                            null,
                            1
                        )
                    );

                    $this->changeOrderStatus(
                        Configuration::get(
                            'HIPAY_OS_REFUNDED_PARTIALLY',
                            null,
                            null,
                            1
                        )
                    );
                }
            }
        }

        return true;
    }

    /**
     * change order status
     * @param type $newState
     */
    private function changeOrderStatus($newState)
    {
        $orderHistory           = new OrderHistory();
        $orderHistory->id_order = $this->order->id;
        $orderHistory->changeIdOrderState(
            $newState,
            $this->order,
            true
        );

        $orderHistory->addWithemail(true);
    }

    /**
     * create or update order private message with total captured amount
     */
    private function addHipayCaptureMessage()
    {
        HipayOrderMessage::captureMessage(
            $this->order->id,
            $this->order->id_customer,
            $this->transaction
        );
    }

    /**
     * check if order is already at status "payment accepted"
     * @param type $paymentStatus
     * @param type $orderState
     * @param type $forceCtrl
     * @return boolean
     */
    private function controleIfStatushistoryExist(
    $paymentStatus, $orderState, $forceCtrl = false
    )
    {
        $this->log->logInfos("# ControleIfStatushistoryExist ".$this->order->id."Status ".$orderState);

        if (($orderState == $paymentStatus || $forceCtrl) && $this->order != null) {
            $this->log->logInfos("# ControleIfStatushistoryExist Status exist");
            return $this->db->checkOrderStatusExist(
                    $paymentStatus,
                    $this->order->id
            );
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
        HipayOrderMessage::orderMessage(
            $this->order->id,
            $this->order->id_customer,
            $this->transaction
        );
    }

    /**
     * we rename transaction reference to distinct every captured amount when transaction is partially captured
     * every step of the capture is unique (id = {transacRef}-{CapturedAmount}). Prevent from duplicates or overwritting
     * @return string
     */
    private function setTransactionRefForPrestashop($refund = false)
    {
        $suffix = HipayNotification::TRANSACTION_REF_CAPTURE_SUFFIX;
        $amount = $this->transaction->getCapturedAmount();

        if ($refund) {
            $suffix = HipayNotification::TRANSACTION_REF_REFUND_SUFFIX;
            $amount = $this->transaction->getRefundedAmount();
        }

        return $this->transaction->getTransactionReference()."-".$amount.'-'.$suffix;
    }

    /**
     * notification send total captured amount, we want just the amount concerned by the notification
     * @return type
     */
    private function getRealCapturedAmount($refund = false)
    {
        $amount = $this->transaction->getCapturedAmount() - $this->order->getTotalPaid();

        if ($refund) {
            $amount = -1 * ($this->order->getTotalPaid() - ($this->transaction->getCapturedAmount() - $this->transaction->getRefundedAmount()));
        }

        return $amount;
    }
}