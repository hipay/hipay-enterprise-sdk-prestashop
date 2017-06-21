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
//TODO : ON DUPLICATE KEYS ONLY MYSQL OR MYSQL-LIKE COMPLIANT => SET COMPATIBILITY WITH OTHER DATABASE 
// https://stackoverflow.com/questions/15161578/sql-query-with-on-duplicate-key-update-clarification-needed
// https://stackoverflow.com/questions/1109061/insert-on-duplicate-update-in-postgresql

require_once(dirname(__FILE__) . '/../../../lib/vendor/autoload.php');

use HiPay\Fullservice\Enum\Transaction\TransactionStatus;

class HipayDBQuery {

    const HIPAY_CAT_MAPPING_TABLE = 'hipay_cat_mapping';
    const HIPAY_CARRIER_MAPPING_TABLE = 'hipay_carrier_mapping';
    const HIPAY_ORDER_REFUND_CAPTURE_TABLE = 'hipay_order_refund_capture';
    const HIPAY_PAYMENT_ORDER_PREFIX = 'HiPay Enterprise';

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
     * start sql transaction
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
     * commit transaction and release sql lock
     */
    public function releaseSQLLock() {
        $sql = 'commit;';
        if (!Db::getInstance()->execute($sql)) {
            $this->logs->logsHipay('Bad LockSQL initiated ');
        }
    }

    /**
     * return transaction from Order Id
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

    /**
     * Create categories mapping table
     * @return type
     */
    public function createCatMappingTable() {

        $this->logs->logsHipay('Create Hipay categories mapping table');

        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . HipayDBQuery::HIPAY_CAT_MAPPING_TABLE . '`(
                `ps_cat_id` INT(10) UNSIGNED NOT NULL,
                `hp_cat_id` INT(10) UNSIGNED NOT NULL,
                `shop_id` INT(10) UNSIGNED NOT NULL,
                PRIMARY KEY (`ps_cat_id`, `shop_id`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

        return Db::getInstance()->execute($sql);
    }

    /**
     * Create categories mapping table
     * @return type
     */
    public function createCarrierMappingTable() {

        $this->logs->logsHipay('Create Hipay carrier mapping table');

        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . HipayDBQuery::HIPAY_CARRIER_MAPPING_TABLE . '`(
                `ps_carrier_id` INT(10) UNSIGNED NOT NULL,
                `hp_carrier_mode` VARCHAR(255)  NOT NULL,
                `hp_carrier_shipping` VARCHAR(255) NOT NULL,
                `preparation_eta` FLOAT(10) UNSIGNED NOT NULL,
                `delivery_eta` FLOAT(10) UNSIGNED NOT NULL,
                `shop_id` INT(10) UNSIGNED NOT NULL,
                PRIMARY KEY (`ps_carrier_id`, `shop_id` )
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

        return Db::getInstance()->execute($sql);
    }

    /**
     * 
     * @return type
     */
    public function createOrderRefundCaptureTable() {
        $this->logs->logsHipay('Create Hipay order refund capture table');

        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . HipayDBQuery::HIPAY_ORDER_REFUND_CAPTURE_TABLE . '`(
                `hp_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `ps_order_id` INT(10) UNSIGNED NOT NULL,
                `ps_product_id` INT(10) UNSIGNED NOT NULL,
                `type` VARCHAR(255)  NOT NULL,
                `quantity` INT(10) UNSIGNED NOT NULL,
                `amount` DECIMAL(5,2) UNSIGNED NOT NULL,
                PRIMARY KEY (`hp_id`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

        return Db::getInstance()->execute($sql);
    }

    /**
     * Delete Hipay mapping table
     * @return type
     */
    public function deleteCatMappingTable() {
        $this->logs->logsHipay('Delete Hipay mapping table');

        $sql = 'DROP TABLE `' . _DB_PREFIX_ . HipayDBQuery::HIPAY_CAT_MAPPING_TABLE . '`';
        return Db::getInstance()->execute($sql);
    }

    /**
     * Delete Hipay mapping table
     * @return type
     */
    public function deleteCarrierMappingTable() {
        $this->logs->logsHipay('Delete Hipay carrier mapping table');

        $sql = 'DROP TABLE `' . _DB_PREFIX_ . HipayDBQuery::HIPAY_CARRIER_MAPPING_TABLE . '`';
        return Db::getInstance()->execute($sql);
    }

    public function deleteOrderRefundCaptureTable() {
        $this->logs->logsHipay('Delete Hipay order refund capture table');

        $sql = 'DROP TABLE `' . _DB_PREFIX_ . HipayDBQuery::HIPAY_ORDER_REFUND_CAPTURE_TABLE . '`';
        return Db::getInstance()->execute($sql);
    }

    /**
     * 
     * @param int $idShop
     * @return type
     */
    public function getHipayMappedCategories($idShop) {
        $sql = 'SELECT *
                FROM `' . _DB_PREFIX_ . HipayDBQuery::HIPAY_CAT_MAPPING_TABLE . '`
                WHERE `shop_id` = ' . $idShop;

        return Db::getInstance()->executeS($sql);
    }

    /**
     * 
     * @param int $idShop
     * @return type
     */
    public function getHipayMappedCarriers($idShop) {
        $sql = 'SELECT *
                FROM `' . _DB_PREFIX_ . HipayDBQuery::HIPAY_CARRIER_MAPPING_TABLE . '`
                WHERE `shop_id` = ' . $idShop;

        return Db::getInstance()->executeS($sql);
    }

    /**
     * insert row in HIPAY_CAT_MAPPING_TABLE
     * @param type $values
     */
    public function setHipayCatMapping($values) {
        $sql = 'INSERT INTO  `' . _DB_PREFIX_ . HipayDBQuery::HIPAY_CAT_MAPPING_TABLE . '` (ps_cat_id, hp_cat_id, shop_id)
                VALUES ' . join(",", $values) . ' '
                . 'ON DUPLICATE KEY UPDATE ps_cat_id=VALUES(ps_cat_id), hp_cat_id=VALUES(hp_cat_id), shop_id=VALUES(shop_id);';
        return Db::getInstance()->execute($sql);
    }

    /**
     * insert row in HIPAY_CARRIER_MAPPING_TABLE
     * @param type $values
     */
    public function setHipayCarrierMapping($values) {
        $sql = 'INSERT INTO  `' . _DB_PREFIX_ . HipayDBQuery::HIPAY_CARRIER_MAPPING_TABLE . '` (ps_carrier_id, hp_carrier_mode, hp_carrier_shipping, preparation_eta, delivery_eta, shop_id)
                VALUES ' . join(",", $values) . ' '
                . 'ON DUPLICATE KEY UPDATE ps_carrier_id=VALUES(ps_carrier_id), hp_carrier_mode=VALUES(hp_carrier_mode), hp_carrier_shipping=VALUES(hp_carrier_shipping), preparation_eta=VALUES(preparation_eta), delivery_eta=VALUES(delivery_eta), shop_id=VALUES(shop_id);';

        return Db::getInstance()->execute($sql);
    }

    /**
     * 
     * @param type $PSId
     * @return int
     */
    public function getHipayCatFromPSId($PSId) {
        $sql = 'SELECT hp_cat_id
                FROM `' . _DB_PREFIX_ . HipayDBQuery::HIPAY_CAT_MAPPING_TABLE . '` 
                WHERE ps_cat_id = ' . $PSId;

        $result = Db::getInstance()->getRow($sql);

        return $result;
    }

    /**
     * 
     * @param type $PSId
     * @return int
     */
    public function getHipayCarrierFromPSId($PSId) {
        $sql = 'SELECT *
                FROM `' . _DB_PREFIX_ . HipayDBQuery::HIPAY_CARRIER_MAPPING_TABLE . '` 
                WHERE ps_carrier_id = ' . $PSId;

        $result = Db::getInstance()->getRow($sql);

        return $result;
    }

    /**
     * check if specific order status exist in $idOrder order history
     * @param type $status
     * @param type $idOrder
     * @return boolean
     */
    public function checkOrderStatusExist($status, $idOrder) {
        $sql = 'SELECT COUNT(id_order_history) as count
		FROM `' . _DB_PREFIX_ . 'order_history`
		WHERE `id_order` = ' . (int) $idOrder . ' AND `id_order_state` = ' . (int) $status;

        $this->logs->logsHipay('Check order status exist : ' . $sql);

        $result = Db::getInstance()->getRow($sql);

        $this->logs->logsHipay('Check order status exist : ' . print_r($result, true));

        if (isset($result['count']) && $result['count'] > 0) {
            return true;
        }
        return false;
    }

    /**
     * update order payment line
     * @param type $paymentData
     * @return boolean
     */
    public function updateOrderPayment($paymentData) {

        $cardData = "";

        if ($paymentData['payment_method'] != null) {
            $cardData = " `card_number` = '" . $paymentData['payment_method']['pan'] . "',
                    `card_brand` = '" . $paymentData['payment_method']['brand'] . "',
                    `card_expiration` = '" . $paymentData['payment_method']['card_expiry_month'] . "/" . $paymentData['payment_method']['card_expiry_year'] . "',
                    `card_holder` = '" . $paymentData['payment_method']['card_holder'] . "' ,";
        }

        $sql = "
            UPDATE `" . _DB_PREFIX_ . "order_payment`
            SET     " . $cardData . "
                    `amount` = '" . $paymentData['captured_amount'] . "'
                    
            WHERE 
                 `transaction_id` = '" . $paymentData['transaction_id'] . "' AND
                `payment_method` = '" . HipayDBQuery::HIPAY_PAYMENT_ORDER_PREFIX . " " . $paymentData["name"] . "'
            AND `order_reference`= '" . $paymentData["order_reference"] . "';";


        print_r($sql);

        if (!Db::getInstance()->execute($sql)) {
            //LOG 
            $this->logs->logsHipay("ERROR : updateOrderPayment");
            return false;
        }

        return true;
    }

    /**
     * count order payment line
     * @param type $orderReference
     * @return boolean
     */
    public function countOrderPayment($orderReference, $transactionId = null) {

        $transactWhere = "";

        if ($transactionId != null) {
            $transactWhere = " transaction_id='" . $transactionId . "' AND ";
        }

        $sql = "SELECT COUNT(id_order_payment) as count "
                . "FROM `" . _DB_PREFIX_ . "order_payment` "
                . "WHERE " . $transactWhere . " `order_reference` = '" . $orderReference . "' ;"
        ;

        var_dump($sql);

        $result = Db::getInstance()->getRow($sql);
        if (isset($result['count'])) {
            return $result['count'];
        }
        return 0;
    }

    /**
     * Check if there is a duplicated OrderPayment and remove duplicate from same order ref but with incomplete payment method name
     * @param type $orderReference
     */
    public function deleteOrderPaymentDuplicate($orderReference) {
        $sql = "
            DELETE FROM `" . _DB_PREFIX_ . "order_payment` 
            WHERE 
                payment_method='" . HipayDBQuery::HIPAY_PAYMENT_ORDER_PREFIX . "' 
                AND transaction_id='' 
                AND order_reference='" . $orderReference . "'
            ;";

        Db::getInstance()->execute($sql);
    }

    /**
     * set invoice order
     * @param Order $order
     */
    public function setInvoiceOrder($order) {

        $sql = 'SELECT `id_order_payment`
                FROM `' . _DB_PREFIX_ . 'order_payment`
                WHERE order_reference="' . pSQL($order->reference) . ' LIMIT 1";';

        $result = Db::getInstance()->getRow($sql);
        $idOrderP = isset($result['id_order_payment']) ? $result['id_order_payment'] : false;

        if ($idOrderP) {
            $sqlUpdate = "
			UPDATE `" . _DB_PREFIX_ . "order_invoice_payment`
	                SET `id_order_payment` = " . (int) $idOrderP . "
	                WHERE `id_order` = " . (int) $order->id;
            Db::getInstance()->execute($sqlUpdate);
        }
    }

    /**
     * 
     * @param type $orderId
     * @return boolean
     */
    public function alreadyCaptured($orderId) {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'message` WHERE id_order=\'' . $orderId . '\' AND message LIKE \'%"status":' . TransactionStatus::CAPTURED . '%\' ;';

        $result = Db::getInstance()->executeS($sql);
        if (empty($result))
            return false;
        return true;
    }

    /**
     * 
     * @param type $orderId
     * @return boolean / int
     */
    public function getTransactionReference($orderId) {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'message` WHERE id_order=\'' . $orderId . '\' AND message LIKE \'%"status":' . TransactionStatus::AUTHORIZED . '%\' LIMIT 1 ;';

        $result = Db::getInstance()->executeS($sql);
        if (!empty($result)) {
            $message = Tools::jsonDecode($result[0]["message"], true);
            return $message["transaction_ref"];
        }
        return false;
    }

    /**
     * 
     * @param type $orderId
     * @return boolean
     */
    public function getPaymentProductFromMessage($orderId) {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'message` WHERE id_order=\'' . $orderId . '\' AND message LIKE \'%"status":' . TransactionStatus::AUTHORIZED . '%\' LIMIT 1;';

        $result = Db::getInstance()->executeS($sql);
        if (!empty($result)) {
            $message = Tools::jsonDecode($result[0]["message"], true);
            return $message["payment_product"];
        }
        return false;
    }

    /**
     * 
     * @param type $orderId
     * @return boolean
     */
    public function getOrderBasket($orderId) {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'message` WHERE id_order=\'' . $orderId . '\' AND message LIKE \'%"status":' . TransactionStatus::AUTHORIZED . '%\' LIMIT 1;';

        $result = Db::getInstance()->executeS($sql);
        if (!empty($result)) {
            $message = Tools::jsonDecode($result[0]["message"], true);
            return Tools::jsonDecode($message["basket"], true);
        }
        return false;
    }

    /**
     * save order capture data (basket)
     * @param type $values
     * @return type
     */
    public function setCaptureOrder($values) {
        $sql = 'INSERT INTO  `' . _DB_PREFIX_ . HipayDBQuery::HIPAY_ORDER_REFUND_CAPTURE_TABLE . '` (ps_order_id, ps_product_id, type, quantity, amount)
                VALUES (' . join(",", $values) . ') ;';
        return Db::getInstance()->execute($sql);
    }

    /**
     * get order capture saved data (basket)
     * @param type $orderId
     * @return type
     */
    public function getCapturedItems($orderId) {
        return $this->getCapturedOrRefundedItems($orderId, "capture");
    }

    /**
     * get order refund saved data (basket)
     * @param type $orderId
     * @return type
     */
    public function getRefundedItems($orderId) {
        return $this->getCapturedOrRefundedItems($orderId, "refund");
    }

    /**
     * get capture or refund saved data (basket)
     * @param type $orderId
     * @param type $type
     * @return type
     */
    private function getCapturedOrRefundedItems($orderId, $type) {
        $sql = 'SELECT `ps_product_id`, `type`, SUM(`quantity`) as quantity, SUM(`amount`) as amount
                FROM `' . _DB_PREFIX_ . HipayDBQuery::HIPAY_ORDER_REFUND_CAPTURE_TABLE . '`
                WHERE `ps_order_id` = ' . $orderId . ' AND `type` = "' . $type . '"' .
                ' GROUP BY `ps_product_id`';

        $result = Db::getInstance()->executeS($sql);

        foreach ($result as $key => $item) {
            $result[$item["ps_product_id"]] = $item;
            unset($result[$key]);
        }

        return $result;
    }

}
