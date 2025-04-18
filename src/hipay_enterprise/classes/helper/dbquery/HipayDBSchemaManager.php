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
require_once dirname(__FILE__) . '/HipayDBQueryAbstract.php';

/**
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 *
 * @see    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class HipayDBSchemaManager extends HipayDBQueryAbstract
{
    /**
     * Create categories mapping table.
     *
     * @return bool
     */
    public function createCatMappingTable()
    {
        $this->logs->logInfos('Create Hipay categories mapping table');

        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_CAT_MAPPING_TABLE . '`(
                `hp_ps_cat_id` INT(10) UNSIGNED NOT NULL,
                `hp_cat_id` INT(10) UNSIGNED NOT NULL,
                `shop_id` INT(10) UNSIGNED NOT NULL,
                PRIMARY KEY (`hp_ps_cat_id`, `shop_id`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

        return Db::getInstance()->execute($sql);
    }

    /**
     * Create categories mapping table.
     *
     * @return bool
     */
    public function createCarrierMappingTable()
    {
        $this->logs->logInfos('# Create Hipay carrier mapping table');

        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_CARRIER_MAPPING_TABLE . '`(
                `hp_ps_carrier_id` INT(10) UNSIGNED NOT NULL,
                `hp_carrier_mode` VARCHAR(255)  NOT NULL,
                `hp_carrier_shipping` VARCHAR(255) NOT NULL,
                `preparation_eta` FLOAT(10) UNSIGNED NOT NULL,
                `delivery_eta` FLOAT(10) UNSIGNED NOT NULL,
                `shop_id` INT(10) UNSIGNED NOT NULL,
                PRIMARY KEY (`hp_ps_carrier_id`, `shop_id` )
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

        return Db::getInstance()->execute($sql);
    }

    public function createOrderRefundCaptureTable()
    {
        $this->logs->logInfos('Create Hipay order refund capture table');

        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_ORDER_REFUND_CAPTURE_TABLE . '`(
                `hp_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `hp_ps_order_id` INT(10) UNSIGNED NOT NULL,
                `hp_ps_product_id` INT(10) UNSIGNED NOT NULL,
                `operation` VARCHAR(255)  NOT NULL,
                `type` VARCHAR(255)  NOT NULL,
                `attempt_number` INT(10) UNSIGNED NOT NULL,
                `quantity` INT(10) UNSIGNED NOT NULL,
                `amount` DECIMAL(5,2) UNSIGNED NOT NULL,
                PRIMARY KEY (`hp_id`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

        return Db::getInstance()->execute($sql);
    }

    public function createCCTokenTable()
    {
        $this->logs->logInfos('Create Hipay credit card token table');

        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_CC_TOKEN_TABLE . '` (
            `hp_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
            `customer_id` INT(10) UNSIGNED NOT NULL,
            `token` VARCHAR(255) NOT NULL,
            `brand` VARCHAR(255) NOT NULL,
            `pan` VARCHAR(20) NOT NULL,
            `card_holder` VARCHAR(255) NOT NULL,
            `card_expiry_month` VARCHAR(2) NOT NULL,
            `card_expiry_year` VARCHAR(4) NOT NULL,
            `authorized` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` DATE,
            PRIMARY KEY (`customer_id`, `pan`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;
        ';

        return Db::getInstance()->execute($sql);
    }

    public function createHipayTransactionTable()
    {
        $this->logs->logInfos('Create Hipay transaction table');

        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_TRANSACTION_TABLE . '`(
                `hp_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `order_id` INT(10) UNSIGNED NOT NULL,
                `transaction_ref` VARCHAR(45) NOT NULL,
                `state` VARCHAR(255) NOT NULL,
                `status` INT(4) UNSIGNED NOT NULL,
                `message` VARCHAR(255) NOT NULL,
                `payment_product` VARCHAR(255) NOT NULL,
                `amount` FLOAT NOT NULL,
                `captured_amount` FLOAT ,
                `refunded_amount` FLOAT ,
                `payment_start` VARCHAR(255) ,
                `payment_authorized` VARCHAR(255) ,
                `authorization_code` VARCHAR(255) ,
                `basket` TEXT ,
                `attempt_create_multi_use` INT(10),
                `customer_id` INT(10),
                `auth_info_method` VARCHAR(2),
                `eci` VARCHAR(3),
                `reference_to_pay` TEXT,
                PRIMARY KEY (`hp_id`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

        return Db::getInstance()->execute($sql);
    }

    public function createHipayOrderCaptureType()
    {
        $this->logs->logInfos('Create Hipay order capture type table');

        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_ORDER_CAPTURE_TYPE_TABLE . '`(
                `hp_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `order_id` INT(10) UNSIGNED NOT NULL,
                `type` VARCHAR(255) NOT NULL,
                PRIMARY KEY (`hp_id`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

        return Db::getInstance()->execute($sql);
    }

    public function createHipayNotificationTable()
    {
        $this->logs->logInfos('Create Hipay Notification table');

        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_NOTIFICATION_TABLE . '`(
                `hp_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `cart_id` INT(10) UNSIGNED NOT NULL,
                `transaction_ref` VARCHAR(45) NOT NULL,
                `notification_code` INT(4) UNSIGNED NOT NULL,
                `attempt_number` INT(3) UNSIGNED NOT NULL,
                `status` VARCHAR(255) NOT NULL,
                `data` TEXT NOT NULL,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`hp_id`),
                INDEX `hipay_notification.update_keys` (`cart_id`, `transaction_ref`, `notification_code`, `status`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

        return Db::getInstance()->execute($sql);
    }

    public function createHipayProcessedOrderTable()
    {
        $this->logs->logInfos('Create Hipay processed order table');

        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_PROCESSED_ORDER_TABLE . '`(
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `cart_id` int(11) NOT NULL,
                `new_cart_id` int(11) NOT NULL,
                `hipay_order_id` varchar(255) NOT NULL,
                `total_amount` decimal(20,6) NOT NULL,
                `status` SMALLINT NOT NULL DEFAULT "0" CHECK (status IN (0,1)),
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                KEY `cart_id` (`cart_id`),
                KEY `hipay_order_id` (`hipay_order_id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

        return Db::getInstance()->execute($sql);
    }

    /**
     * Delete Hipay mapping table.
     *
     * @return bool
     */
    public function deleteCatMappingTable()
    {
        $this->logs->logInfos('Delete Hipay mapping table');

        $sql = 'DROP TABLE `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_CAT_MAPPING_TABLE . '`';

        return Db::getInstance()->execute($sql);
    }

    /**
     * Delete Hipay mapping table.
     *
     * @return bool
     */
    public function deleteCarrierMappingTable()
    {
        $this->logs->logInfos('Delete Hipay carrier mapping table');

        $sql = 'DROP TABLE `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_CARRIER_MAPPING_TABLE . '`';

        return Db::getInstance()->execute($sql);
    }

    public function deleteOrderRefundCaptureTable()
    {
        $this->logs->logInfos('Delete Hipay order refund capture table');

        $sql = 'DROP TABLE `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_ORDER_REFUND_CAPTURE_TABLE . '`';

        return Db::getInstance()->execute($sql);
    }

    public function deleteCCTokenTable()
    {
        $this->logs->logInfos('Delete credit card table');

        $sql = 'DROP TABLE `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_CC_TOKEN_TABLE . '`';

        return Db::getInstance()->execute($sql);
    }

    public function deleteHipayNotificationTable()
    {
        $this->logs->logInfos('Delete Hipay Notification table');

        $sql = 'DROP TABLE `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_NOTIFICATION_TABLE . '`';

        return Db::getInstance()->execute($sql);
    }

    public function deleteHipayProcessedOrderTable()
    {
        $this->logs->logInfos('Delete Hipay processed order table');

        $sql = 'DROP TABLE `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_PROCESSED_ORDER_TABLE . '`';

        return Db::getInstance()->execute($sql);
    }
}
