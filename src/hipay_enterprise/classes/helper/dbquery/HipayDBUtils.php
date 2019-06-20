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

require_once(dirname(__FILE__) . '/HipayDBQueryAbstract.php');

/**
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class HipayDBUtils extends HipayDBQueryAbstract
{
    /**
     * set activated currency for the module
     *
     * @param $moduleId
     * @param $shopId
     * @param $iso
     * @return bool
     */
    public function setCurrencies($moduleId, $shopId, $iso)
    {

        $sql = 'INSERT IGNORE INTO `' . _DB_PREFIX_ . 'module_currency` (`id_module`, `id_shop`, `id_currency`)
                    SELECT ' . (int)$moduleId . ', "' . (int)$shopId . '", `id_currency`
                    FROM `' . _DB_PREFIX_ . 'currency`
                    WHERE `deleted` = \'0\' AND `iso_code` = \'' . $iso . '\'';
        return (bool)Db::getInstance()->execute($sql);
    }

    /**
     * get last cart from user ID
     *
     * @param int $userId
     * @return boolean / Cart
     */
    public function getLastCartFromUser($userId)
    {
        $sql = 'SELECT `id_cart`
                FROM `' . _DB_PREFIX_ . 'cart`
                WHERE `id_customer` = ' . pSQL($userId) . '
                ORDER BY date_upd DESC';

        $result = Db::getInstance()->getRow($sql);
        $cart_id = isset($result['id_cart']) ? $result['id_cart'] : false;

        if ($cart_id) {
            $objCart = new Cart((int)$cart_id);
        } else {
            $objCart = false;
        }

        return $objCart;
    }

    /**
     * start sql transaction
     *
     * @param int $cartId
     */
    public function setSQLLockForCart($cartId, $origin)
    {
        $this->logs->logInfos('# Start LockSQL  for id_cart = ' . $cartId . 'in :' . $origin);

        $sql = 'begin;';
        $sql .= 'SELECT id_cart FROM ' . _DB_PREFIX_ . 'cart WHERE id_cart = ' . pSQL((int)$cartId) . ' FOR UPDATE;';

        if (!Db::getInstance()->execute($sql)) {
            $this->logs->logInfos('Bad LockSQL initiated, Lock could not be initiated for id_cart = ' . $cartId);
            die('Lock not initiated');
        }
        $this->logs->logInfos('# LockSQL for id_cart = ' . $cartId . 'in :' . $origin . ' is now free');
    }

    /**
     * commit transaction and release sql lock
     *
     * @param $origin
     */
    public function releaseSQLLock($origin)
    {
        $this->logs->logInfos('# Commit LockSQL for ' . $origin);

        $sql = 'commit;';
        if (!Db::getInstance()->execute($sql)) {
            $this->logs->logInfos('Bad LockSQL initiated ');
        }
    }

    /**
     * @param $cartId
     * @return bool
     */
    public function getOrderByCartId($cartId)
    {
        $sql = 'SELECT `id_order`
                    FROM `' . _DB_PREFIX_ . 'orders`
                    WHERE `id_cart` = ' . (int)$cartId;
        $result = Db::getInstance()->getRow($sql);
        return isset($result['id_order']) ? $result['id_order'] : false;
    }

    /**
     * check if specific order status exist in $idOrder order history
     *
     * @param $status
     * @param $idOrder
     * @return bool
     */
    public function checkOrderStatusExist($status, $idOrder)
    {
        $sql = 'SELECT COUNT(id_order_history) as count
		FROM `' . _DB_PREFIX_ . 'order_history`
		WHERE `id_order` = ' . pSQL((int)$idOrder) . ' AND `id_order_state` = ' . pSQL((int)$status);

        $this->logs->logInfos('# Check order status exist : ' . $sql);

        $result = Db::getInstance()->getRow($sql);

        $this->logs->logInfos('# Check order status exist : ' . print_r($result, true));

        if (isset($result['count']) && $result['count'] > 0) {
            return true;
        }
        return false;
    }

    /**
     * @param $order_ref
     * @param $trans_id
     * @return bool|OrderPayment
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function findOrderPayment($order_ref, $trans_id)
    {
        $payment_id = Db::getInstance()->getValue(
            'SELECT `id_order_payment` FROM `' . _DB_PREFIX_ . 'order_payment`
            WHERE `order_reference` = \'' . pSQL($order_ref) . '\' AND transaction_id = \'' . pSQL($trans_id) . '\''
        );

        if (!$payment_id) {
            return false;
        }

        return new OrderPayment((int)$payment_id);
    }

    /**
     * count order payment line
     *
     * @param $orderReference
     * @param null $transactionId
     * @return int
     */
    public function countOrderPayment($orderReference, $transactionId = null)
    {
        $transactWhere = "";

        if ($transactionId != null) {
            $transactWhere = " transaction_id='" . pSQL($transactionId) . "' AND ";
        }

        $sql = "SELECT COUNT(id_order_payment) as count "
            . "FROM `" . _DB_PREFIX_ . "order_payment` "
            . "WHERE " . $transactWhere . " `order_reference` = '" . pSQL($orderReference) . "' ;";


        $result = Db::getInstance()->getRow($sql);
        if (isset($result['count'])) {
            return $result['count'];
        }
        return 0;
    }

    /**
     * Check if there is a duplicated OrderPayment and remove duplicate from same order ref but with incomplete payment method name
     * When order is set to Payed order status Prestashop create order payment with remaining amount to pay
     * we need to erase this line
     *
     * @param $orderReference
     */
    public function deleteOrderPaymentDuplicate($orderReference)
    {
        // delete
        $where = "payment_method='" .
            HipayDBQueryAbstract::HIPAY_PAYMENT_ORDER_PREFIX .
            "' AND transaction_id='' AND order_reference='" .
            $orderReference .
            "'";
        Db::getInstance()->delete('order_payment', $where);
    }

    public function cartAlreadyOrdered($products)
    {
        $where = array();
        foreach ($products as $product) {
            $where[] = "(product_id = " .
                $product["product_id"] .
                " and product_quantity = " .
                $product["product_quantity"] .
                " and id_shop = " .
                $product["id_shop"] .
                " and product_attribute_id = "
                . $product["product_attribute_id"] .
                ")";
        }

        $sql = 'SELECT id_order, COUNT(id_order) AS count 
		FROM `' . _DB_PREFIX_ . 'order_detail`
		WHERE ' . implode(" OR ", $where) . '
		GROUP BY id_order HAVING COUNT(id_order) = ' . count($products);

        $result = Db::getInstance()->getRow($sql);

        if (isset($result['count']) && $result['count'] > 0) {
            return true;
        }
        return false;

    }

    public function getNbPaymentAttempt($customerId, $paymentStart, $paymentMethods)
    {

        $sql = 'SELECT customer_id, COUNT(DISTINCT order_id) AS count 
            FROM `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_TRANSACTION_TABLE .
            '` WHERE customer_id = ' . pSQL((int)$customerId) .
            ' AND payment_start >= "' . $paymentStart . '"' .
            ' AND payment_product IN (' . implode("','", $paymentMethods) . ')' .
            ' GROUP BY customer_id;';

        $result = Db::getInstance()->getRow($sql);

        if (isset($result['count'])) {
            return $result['count'];
        }

        return 0;
    }

    public function getDateAddressFirstUsed($addressId)
    {

        $sql = 'SELECT date_add 
            FROM `' . _DB_PREFIX_ . 'orders`' .
            ' WHERE id_address_delivery = ' . pSQL((int)$addressId) .
            ' OR id_address_invoice = ' . $addressId .
            ' ORDER BY date_add ASC;';

        $result = Db::getInstance()->getRow($sql);

        if (isset($result['date_add'])) {
            return $result['date_add'];
        }

        return false;
    }

    public function getCartFirstUsed($cardNumber, $expirationDate, $cardHolder, $customerId)
    {

        $sql = 'SELECT date_add 
            FROM `' . _DB_PREFIX_ . 'order_payment`' .
            ' WHERE card_number LIKE "' . pSQL($cardNumber) . '"' .
            ' AND card_expiration LIKE "' . $expirationDate . '"' .
            ' AND card_holder LIKE "' . $cardHolder . '"' .
            ' AND order_reference IN (
                SELECT reference
                FROM `' . _DB_PREFIX_ . 'orders` 
                WHERE id_customer = ' . pSQL((int)$customerId) . '  
            )' .
            ' ORDER BY date_add ASC;';

        $result = Db::getInstance()->getRow($sql);

        if (isset($result['date_add'])) {
            return $result['date_add'];
        }

        return false;
    }
}
