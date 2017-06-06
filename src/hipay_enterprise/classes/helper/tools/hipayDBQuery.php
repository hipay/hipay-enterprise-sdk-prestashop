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

    const HIPAY_CAT_MAPPING_TABLE = 'hipay_cat_mapping';
    const HIPAY_CARRIER_MAPPING_TABLE = 'hipay_carrier_mapping';

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
            $this->logs->logsHipay('Bad LockSQL initiated for id_cart = ' . $objCart->id);
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
                `hp_carrier_id` INT(10) UNSIGNED NOT NULL,
                `preparation_eta` FLOAT(10) UNSIGNED NOT NULL,
                `delivery_eta` FLOAT(10) UNSIGNED NOT NULL,
                `shop_id` INT(10) UNSIGNED NOT NULL,
                PRIMARY KEY (`ps_carrier_id`, `shop_id` )
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
        $sql = 'INSERT INTO  `' . _DB_PREFIX_ . HipayDBQuery::HIPAY_CARRIER_MAPPING_TABLE . '` (ps_carrier_id, hp_carrier_id, preparation_eta, delivery_eta, shop_id)
                VALUES ' . join(",", $values) . ' '
                . 'ON DUPLICATE KEY UPDATE ps_carrier_id=VALUES(ps_carrier_id), hp_carrier_id=VALUES(hp_carrier_id), preparation_eta=VALUES(preparation_eta), delivery_eta=VALUES(delivery_eta), shop_id=VALUES(shop_id);';

        return Db::getInstance()->execute($sql);
    }

    /**
     * 
     * @param type $PSId
     * @return int
     */
    public function getHipayCatFromPSId($PSId) {
        $sql = 'SELECT hp_cat_id
                FROM `' . _DB_PREFIX_. HipayDBQuery::HIPAY_CAT_MAPPING_TABLE . '` 
                WHERE ps_cat_id = ' . $PSId;

        $result = Db::getInstance()->getRow($sql);

        return $result;
    }

}
