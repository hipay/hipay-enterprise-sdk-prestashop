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
class HipayDBQuery {

    public function __construct($moduleInstance) {
        $this->module = $moduleInstance;
        $this->logs = $this->module->getLogs();
    }

    /**
     * get last cart from user ID 
     * @param int $userId
     * @return boolean / Cart
     */
    public function getLastCartFromUser($userId) {

        $sql = 'SELECT `id_cart`
                FROM `' . _DB_PREFIX_ . 'cart`
                WHERE `id_customer` = ' . $userId . '
                ORDER BY date_upd DESC';

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
     * 
     * @param int $cartId
     */
    public function setSQLLockForCart($cartId) {

        $this->logs->logsHipay('start LockSQL  for id_cart = ' . $cartId);

        $sql = 'begin;';
        $sql .= 'SELECT id_cart FROM ' . _DB_PREFIX_ . 'cart WHERE id_cart = ' . (int) $cartId . ' FOR UPDATE;';

        if (!Db::getInstance()->execute($sql)) {
            $this->logs->logsHipay('Bad LockSQL initiated, Lock could not be initiated for id_cart = ' . $cartId);
            die('Lock not initiated');
        }
    }

    /**
     * 
     * @return type
     */
    public function getTransactionFromOrder($orderId) {
        
        $sql = 'SELECT DISTINCT(op.transaction_id)
                FROM `' . _DB_PREFIX_ . 'order_payment` op
                INNER JOIN `' . _DB_PREFIX_ . 'orders` o ON o.reference = op.order_reference
                WHERE o.id_order = ' . $orderId;

        $result = Db::getInstance()->getRow($sql);

        return $result;
    }

}
