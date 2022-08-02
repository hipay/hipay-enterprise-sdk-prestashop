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
     * @param int $orderId
     */
    public function setSQLLockForCart($orderId, $origin)
    {
        $this->logs->logInfos('# Start LockSQL  for id_order = ' . $orderId . 'in :' . $origin);

        $sql = 'START TRANSACTION;';
        $sql .= 'SELECT hp_id, order_id FROM ' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_TRANSACTION_TABLE . ' WHERE order_id = ' . pSQL((int)$orderId) . ' FOR UPDATE;';

        if (!Db::getInstance()->execute($sql)) {
            $this->logs->logInfos('Bad LockSQL initiated, Lock could not be initiated for id_order = ' . $orderId);
            die('Lock not initiated');
        }
        $this->logs->logInfos('# LockSQL for id_order = ' . $orderId . 'in :' . $origin . ' is now free');
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
     * @param $order
     * @throws PrestaShopDatabaseException
     */
    public function deleteOrderPaymentDuplicate($order)
    {
        // delete
        $where = "payment_method='" .
            HipayDBQueryAbstract::HIPAY_PAYMENT_ORDER_PREFIX .
            "' AND transaction_id='' AND order_reference='" .
            pSQL($order->reference) .
            "'";

        // Querying for to-be-deleted order payments to substract total_paid_real on order
        $originalData = Db::getInstance()->executeS('SELECT * FROM `' .
            _DB_PREFIX_ .
            'order_payment` WHERE ' . $where);

        foreach ($originalData as $paymentRow) {
            $order->total_paid_real -= $paymentRow['amount'];
        }
        $order->save();

        Db::getInstance()->delete('order_payment', $where);
    }

    /**
     * Returns a module's version from database
     * @param $moduleName The module's name
     * @return mixed
     * @throws PrestaShopDatabaseException
     */
    public function getModuleVersion($moduleName)
    {
        $sql = 'SELECT version FROM `' .
            _DB_PREFIX_ .
            'module` WHERE name = \'' .
            pSQL(
                $moduleName
            ) .
            '\' LIMIT 1;';

        $result = Db::getInstance()->executeS($sql);

        if (isset($result[0]) && is_array($result[0])) {
            return $result[0]['version'];
        } else {
            return null;
        }
    }

    public function getNotificationsForOrder($orderId)
    {
        $sql = 'SELECT status FROM `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_TRANSACTION_TABLE .
            '` WHERE order_id=' . pSQL((int)$orderId) . ' ;';

        return array_map(function ($value) {
            return $value['status'];
        }, Db::getInstance()->executeS($sql));
    }

    public function getPaymentConfig($id_shop = null, $id_shop_group = null)
    {
        if($id_shop == null){
            $id_shop = -1;
        }

        if($id_shop_group == null) {
            $id_shop_group = -1;
        }

        $sql = 'SELECT method_id, method_group, config FROM `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_PAYMENT_CONFIG_TABLE .
            '` WHERE id_shop=' . pSQL((int)$id_shop) . ' AND id_shop_group=' . pSQL((int)$id_shop_group) . ';';

        return array_map(function ($value) {
            return array('method_id' => $value['method_id'], 'method_group' => $value['method_group'], 'config' => json_decode($value['config'], true));
        }, Db::getInstance()->executeS($sql));
    }

    public function savePaymentConfig($paymentConfig, $id_shop = null, $id_shop_group = null)
    {
        if($id_shop == null){
            $id_shop = -1;
        }

        if($id_shop_group == null) {
            $id_shop_group = -1;
        }

        foreach ($paymentConfig as $methodGroup => $methods) {
            foreach ($methods as $methodId => $methodConfig) {
                $values = array('method_id' => pSQL($methodId), 'method_group' => pSQL($methodGroup), 'config' => pSQL(json_encode($methodConfig)), 'id_shop' => pSQL((int)$id_shop), 'id_shop_group' => pSQL((int)$id_shop_group));
                Db::getInstance()->insert(HipayDBQueryAbstract::HIPAY_PAYMENT_CONFIG_TABLE, $values, true, true, Db::REPLACE);
            }
        }
    }
}
