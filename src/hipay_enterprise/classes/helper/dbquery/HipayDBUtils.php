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
require_once dirname(__FILE__).'/HipayDBQueryAbstract.php';

/**
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 *
 * @see    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class HipayDBUtils extends HipayDBQueryAbstract
{
    /**
     * set activated currency for the module.
     *
     * @param int    $moduleId
     * @param int    $shopId
     * @param string $iso
     *
     * @return bool
     */
    public function setCurrencies($moduleId, $shopId, $iso)
    {
        $sql = 'INSERT IGNORE INTO `'._DB_PREFIX_.'module_currency` (`id_module`, `id_shop`, `id_currency`)'
            .' SELECT '.(int) $moduleId.', "'.(int) $shopId.'", `id_currency`'
            .' FROM `'._DB_PREFIX_.'currency`'
            .' WHERE `deleted` = 0 AND `iso_code` = "'.pSQL($iso).'"';

        return (bool) Db::getInstance()->execute($sql);
    }

    /**
     * get last cart from user ID.
     *
     * @param int $userId
     *
     * @return bool|Cart
     */
    public function getLastCartFromUser($userId)
    {
        $sql = 'SELECT `id_cart`'
            .' FROM `'._DB_PREFIX_.'cart`'
            .' WHERE `id_customer` = '.(int) $userId
            .' ORDER BY date_upd DESC';

        $result = Db::getInstance()->getRow($sql);
        $cart_id = isset($result['id_cart']) ? $result['id_cart'] : false;

        if ($cart_id) {
            $objCart = new Cart((int) $cart_id);
        } else {
            $objCart = false;
        }

        return $objCart;
    }

    /**
     * start sql transaction.
     *
     * @param int    $orderId
     * @param string $origin
     */
    public function setSQLLockForCart($orderId, $origin)
    {
        $this->logs->logInfos('# Start LockSQL for id_order = '.$orderId.'in :'.$origin);

        $sql = 'SELECT `hp_id`, `order_id`'
            .' FROM `'._DB_PREFIX_.HipayDBQueryAbstract::HIPAY_TRANSACTION_TABLE.'`'
            .' WHERE `order_id` = '.(int) $orderId.' FOR UPDATE';

        if (!Db::getInstance()->execute('START TRANSACTION;') || !Db::getInstance()->execute($sql)) {
            $this->logs->logInfos('Bad LockSQL initiated, Lock could not be initiated for id_order = '.$orderId);
            exit('Lock not initiated');
        }
        $this->logs->logInfos('# LockSQL for id_order = '.$orderId.'in :'.$origin.' is now free');
    }

    /**
     * commit transaction and release sql lock.
     *
     * @param string $origin
     */
    public function releaseSQLLock($origin)
    {
        $this->logs->logInfos('# Commit LockSQL for '.$origin);

        $sql = 'COMMIT';
        if (!Db::getInstance()->execute($sql)) {
            $this->logs->logInfos('Bad LockSQL initiated ');
        }
    }

    /**
     * @param int $cartId
     *
     * @return int[]
     */
    public function getOrderIdsByCartId($cartId)
    {
        $sql = 'SELECT `id_order`'
            .' FROM `'._DB_PREFIX_.'orders`'
            .' WHERE `id_cart` = '.(int) $cartId;

        $result = Db::getInstance()->executeS($sql);

        return $result ? array_column($result, 'id_order') : [];
    }

    /**
     * check if specific order status exist in $idOrder order history.
     *
     * @param int $status
     * @param int $idOrder
     *
     * @return bool
     */
    public function checkOrderStatusExist($status, $idOrder)
    {
        $sql = 'SELECT COUNT(id_order_history) as count'
            .' FROM `'._DB_PREFIX_.'order_history`'
            .' WHERE `id_order` = '.(int) $idOrder
            .' AND `id_order_state` = '.(int) $status;

        $this->logs->logInfos('# Check order status exist : '.$sql);

        $result = Db::getInstance()->getRow($sql);

        $this->logs->logInfos('# Check order status exist : '.print_r($result, true));

        return isset($result['count']) && $result['count'] > 0;
    }

    /**
     * @param string $order_ref
     * @param int    $trans_id
     *
     * @return bool|OrderPayment
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function findOrderPayment($order_ref, $trans_id)
    {
        $payment_id = Db::getInstance()->getValue(
            'SELECT `id_order_payment`'
                .' FROM `'._DB_PREFIX_.'order_payment`'
                .' WHERE `order_reference` = "'.pSQL($order_ref).'"'
                .' AND transaction_id = '.(int) $trans_id
        );

        if (!$payment_id) {
            return false;
        }

        return new OrderPayment((int) $payment_id);
    }

    /**
     * count order payment line.
     *
     * @param string   $orderReference
     * @param int|null $transactionId
     *
     * @return int
     */
    public function countOrderPayment($orderReference, $transactionId = null)
    {
        $sql = 'SELECT COUNT(id_order_payment) as count'
            .' FROM `'._DB_PREFIX_.'order_payment`'
            .' WHERE `order_reference` = "'.pSQL($orderReference).'"';

        if (null != $transactionId) {
            $sql .= ' AND transaction_id = "'.pSQL($transactionId).'"';
        }

        $result = Db::getInstance()->getRow($sql);
        if (isset($result['count'])) {
            return $result['count'];
        }

        return 0;
    }

    /**
     * Check if there is a duplicated OrderPayment and remove duplicate from same order ref but with incomplete payment method name
     * When order is set to Payed order status Prestashop create order payment with remaining amount to pay
     * we need to erase this line.
     *
     * @param Order $order
     *
     * @throws PrestaShopDatabaseException
     */
    public function deleteOrderPaymentDuplicate($order)
    {
        // delete
        $where = 'payment_method = "'.HipayDBQueryAbstract::HIPAY_PAYMENT_ORDER_PREFIX.'"'
            .' AND transaction_id = ""'
            .' AND order_reference = "'.pSQL($order->reference).'"';

        // Querying for to-be-deleted order payments to substract total_paid_real on order
        $originalData = Db::getInstance()->executeS(
            'SELECT * FROM `'._DB_PREFIX_.'order_payment` WHERE '.$where
        );

        foreach ($originalData as $paymentRow) {
            $order->total_paid_real -= $paymentRow['amount'];
        }
        $order->save();

        Db::getInstance()->delete('order_payment', $where);
    }

    /**
     * Returns a module's version from database.
     *
     * @param string $moduleName The module's name
     *
     * @return mixed
     *
     * @throws PrestaShopDatabaseException
     */
    public function getModuleVersion($moduleName)
    {
        $sql = 'SELECT version'
            .' FROM `'._DB_PREFIX_.'module`'
            .' WHERE name = "'.pSQL($moduleName).'"'
            .' LIMIT 1';

        $result = Db::getInstance()->executeS($sql);

        if (isset($result[0]) && is_array($result[0])) {
            return $result[0]['version'];
        } else {
            return null;
        }
    }

    /**
     * @param int $orderId
     */
    public function getNotificationsForOrder($orderId)
    {
        $sql = 'SELECT status FROM `'._DB_PREFIX_.HipayDBQueryAbstract::HIPAY_TRANSACTION_TABLE.'`'
            .' WHERE order_id = '.(int) $orderId;

        return array_map(function ($value) {
            return $value['status'];
        }, Db::getInstance()->executeS($sql));
    }

    /**
     * @param int $orderId
     * @return mixed|false
     */
    public function getTransactionByOrderId($orderId)
    {
        $sql = 'SELECT * '
            .' FROM `'._DB_PREFIX_.HipayDBQueryAbstract::HIPAY_TRANSACTION_TABLE.'`'
            .' WHERE order_id = '.(int) $orderId
            .' LIMIT 1';

        if (!empty($result = Db::getInstance()->executeS($sql))) {
            return $result[0];
        }

        return false;
    }

    /**
     * Insert new processed order into hipay_processed_orders table
     *
     * @param int $cartId
     * @param string $hipayOrderId
     * @param float $totalAmount
     * @param int $status
     * @return bool
     */
    public function insertProcessedOrder($cartId, $newCartId, $hipayOrderId, $totalAmount, $status = 0)
    {
        $sql = 'INSERT INTO `'._DB_PREFIX_.'hipay_processed_orders`
            (`cart_id`, `new_cart_id` ,`hipay_order_id`, `total_amount`, `status`, `created_at`, `updated_at`)
            VALUES (
                '.(int)$cartId.',
                '.(int)$newCartId.',
                "'.pSQL($hipayOrderId).'",
                '.(float)$totalAmount.',
                '.(int)$status.',
                NOW(),
                NOW()
            )';

        return (bool)Db::getInstance()->execute($sql);
    }

    /**
     * Delete processed order by cart ID
     *
     * @param int $cartId
     * @return bool
     */
    public static function deleteProcessedOrderByCartId($cartId)
    {
        $sql = 'DELETE FROM `'._DB_PREFIX_.'hipay_processed_orders`
            WHERE `cart_id` = '.(int)$cartId;

        return (bool)Db::getInstance()->execute($sql);
    }

    /**
     * Get hipay order ID by cart ID
     *
     * @param int $cartId
     * @return string|false
     */
    public function getHipayOrderIdByCartId($cartId)
    {
        $sql = 'SELECT `hipay_order_id`
            FROM `'._DB_PREFIX_.'hipay_processed_orders`
            WHERE `cart_id` = '.(int)$cartId.'
            LIMIT 1';
        if (!empty($result = Db::getInstance()->executeS($sql))) {
            return $result[0];
        }

        return false;
    }

    /**
     * Get hipay Current cart by new cart id
     *
     * @param int $cartId
     * @return string|false
     * @throws PrestaShopDatabaseException
     */
    public static function getAssociatedCartId($newCartId)
    {
        $sql = 'SELECT `cart_id`
            FROM `'._DB_PREFIX_.'hipay_processed_orders`
            WHERE `new_cart_id` = '.(int)$newCartId.'
            LIMIT 1';
        if (!empty($result = Db::getInstance()->executeS($sql))) {
            return $result[0]["cart_id"];
        }

        return false;
    }

    /**
     * Get hipay New Cart by current Cart id
     *
     * @param int $cartId
     * @return string|false
     * @throws PrestaShopDatabaseException
     */
    public static function getHipayNewCartIdByCartId($cartId)
    {
        $sql = 'SELECT `new_cart_id`
            FROM `'._DB_PREFIX_.'hipay_processed_orders`
            WHERE `cart_id` = '.(int)$cartId.'
            LIMIT 1';
        if (!empty($result = Db::getInstance()->executeS($sql))) {
            return $result[0]["new_cart_id"];
        }

        return false;
    }
}