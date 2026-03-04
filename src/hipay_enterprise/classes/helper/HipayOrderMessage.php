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

use HiPay\Fullservice\Enum\Transaction\TransactionStatus;

/**
 * Handle order message
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class HipayOrderMessage
{
    /**
     * write notification order message
     * @param type $module
     * @param type $orderId
     * @param type $customerId
     * @param type $data
     */
    public static function orderMessage($module, $orderId, $customerId, $data)
    {
        // Only send notification message if functionality is activated
        if ($module->hipayConfigTool->getAccountGlobal()["order_message_on_notification"]) {
            HipayOrderMessage::addMessage($orderId, $customerId, $data);
        }
    }

    /**
     * format notification data for order message
     * @param type $transaction
     * @return type
     */
    public static function formatOrderData($module, $transaction)
    {
        $message = "HiPay Notification " . $transaction->getState() . "\n";
        switch ($transaction->getStatus()) {
            case TransactionStatus::CAPTURED: //118
            case TransactionStatus::CAPTURE_REQUESTED: //117
                $message .= $module->l('Registered notification from HiPay about captured amount of ') .
                    $transaction->getCapturedAmount() .
                    "\n";
                break;
            case TransactionStatus::REFUND_REQUESTED: //124
            case TransactionStatus::REFUNDED: //125
                $message .= $module->l('Registered notification from HiPay about refunded amount of ') .
                    $transaction->getRefundedAmount() .
                    "\n";
                break;
            case 175: //175
                $message .= $module->l('Transaction cancellation requested') . "\n";
                break;
            case TransactionStatus::CANCELLED: //115
                $message .= $module->l('Transaction cancelled') . "\n";
                break;
        }

        $message .= $module->l('Order total amount :') . $transaction->getAuthorizedAmount() . "\n";
        $message .= "\n";
        $message .= $module->l('Transaction ID: ') . $transaction->getTransactionReference() . "\n";
        $message .= $module->l('HiPay status: ') . $transaction->getStatus() . "\n";
        if (Validate::isCleanHtml($message)) {
            return $message;
        }

        return "Something went wrong";
    }

    /**
     * format error data for order message
     * @param $module
     * @param string $message
     * @param string $transactionReference
     * @param string $transactionStatus
     * @return string
     */
    public static function formatErrorOrderData($module, $message, $transactionReference, $transactionStatus)
    {
        $message .= "\n";
        $message .= $module->l('Transaction ID: ') . $transactionReference . "\n";
        $message .= $module->l('HiPay status: ') . $transactionStatus . "\n";
        if (Validate::isCleanHtml($message)) {
            return $message;
        }

        return "Something went wrong";
    }

    /**
     * generic function to add prestashop order message
     * @param type $orderId
     * @param type $customerId
     * @param type $data
     */
    private static function addMessage($orderId, $customerId, $data)
    {
        $message = new Message();
        $message->message = $data;
        $message->id_order = (int)$orderId;
        $message->private = 1;

        $message->add();
    }
}
