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

use HiPay\Fullservice\Enum\Transaction\TransactionStatus;

class HipayOrderMessage
{
    const HIPAY_CAPTURE_TAG = "HIPAY_CAPTURE";

    /**
     * write notification order message
     * @param type $orderId
     * @param type $transaction
     */
    public static function orderMessage(
    $orderId, $transaction
    )
    {
        $data = HipayOrderMessage::formatOrderData($transaction);
        HipayOrderMessage::addMessage(
            $orderId,
            $data
        );
    }

    /**
     * format notification data for order message
     * @param type $transaction
     * @return type
     */
    public static function formatOrderData($transaction)
    {
        $message = "HiPay Notification ".$transaction->getState()."\n";
        switch ($transaction->getStatus()) {
            case TransactionStatus::CAPTURED: //118
            case TransactionStatus::CAPTURE_REQUESTED: //117
                $message .= "Registered notification from HiPay about captured amount of ".$transaction->getCapturedAmount()."\n";
                break;
            case TransactionStatus::REFUND_REQUESTED: //124
            case TransactionStatus::REFUNDED: //125
                $message .= "Registered notification from HiPay about refunded amount of ".$transaction->getRefundedAmount()."\n";
                break;
        }

        $message .= "Order total amount :".$transaction->getAuthorizedAmount()."\n";
        $message .= "\n";
        $message .= "Transaction ID: ".$transaction->getTransactionReference()."\n";
        $message .= "HiPay status: ".$transaction->getStatus()."\n";
        if (Validate::isCleanHtml($message)) {
            return $message;
        }

        return "Something went wrong";
    }

    /**
     * write or update capture order message
     * @param type $orderId
     * @param type $transaction
     */
    public static function captureMessage(
    $orderId, $transaction
    )
    {
        $amount    = ($transaction->getStatus() == TransactionStatus::AUTHORIZED
                    ? '0.00' : (($transaction->getCapturedAmount() != null) ? $transaction->getCapturedAmount()
                        : '0.00'));
        $messages  = Message::getMessagesByOrderId(
                $orderId,
                true
        );
        $createNew = true;
        if (count($messages)) {
            foreach ($messages as $message) {
                $line = $message['message'];
                if (HipayOrderMessage::HIPAY_CAPTURE_TAG === "" || strpos(
                        $line,
                        HipayOrderMessage::HIPAY_CAPTURE_TAG
                    ) === 0
                ) {
                    $createNew           = false;
                    $updatedMsg          = new Message($message['id_message']);
                    $updatedMsg->message = HipayOrderMessage::HIPAY_CAPTURE_TAG.' - '.$amount;
                    $updatedMsg->save();
                    break;
                }
            }
        }
        if ($createNew) {
            $data = HipayOrderMessage::HIPAY_CAPTURE_TAG.' : '.$amount;
            $data = strip_tags(
                $data,
                '<br>'
            );
            if (Validate::isCleanHtml($data)) {
                HipayOrderMessage::addMessage(
                    $orderId,
                    $data
                );
            }
        }
    }

    /**
     * wrtie or update number of refund or capture request
     * @param type $type
     * @param type $orderId
     * @param type $attempt
     * @param type $messageId
     */
    public static function captureOrRefundAttemptMessage(
    $type, $orderId, $attempt, $messageId = false
    )
    {
        $data = Tools::jsonEncode(array($type."_attempt" => $attempt));
        if (!$messageId) {
            HipayOrderMessage::addMessage(
                $orderId,
                $data
            );
        } else {
            $updatedMsg          = new Message($messageId);
            $updatedMsg->message = $data;
            $updatedMsg->save();
        }
    }

    /**
     * write message when fees are refunded or captured
     * @param type $orderId
     */
    public static function captureOrRefundFeesMessage(
    $orderId, $type
    )
    {
        $data = Tools::jsonEncode(array("fees_".$type => 1));
        HipayOrderMessage::addMessage(
            $orderId,
            $data
        );
    }

    /**
     * generic function to add prestashop order message
     * @param type $orderId
     * @param type $data
     */
    private static function addMessage(
    $orderId, $data
    )
    {
        $message           = new Message();
        $message->message  = $data;
        $message->id_order = (int) $orderId;
        $message->private  = 1;
        $message->add();
    }
}