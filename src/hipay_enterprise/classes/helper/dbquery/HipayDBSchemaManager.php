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
class HipayDBSchemaManager extends HipayDBQueryAbstract
{
    /**
     * Create categories mapping table
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
     * Create categories mapping table
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

        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_CC_TOKEN_TABLE . '`(
                `hp_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `customer_id` INT(10) UNSIGNED NOT NULL,
                `token` VARCHAR(255) NOT NULL,
                `brand` VARCHAR(255) NOT NULL,
                `pan` VARCHAR(20)  NOT NULL,
                `card_holder` VARCHAR(255) NOT NULL,
                `card_expiry_month` INT(2) UNSIGNED NOT NULL,
                `card_expiry_year` INT(4) UNSIGNED NOT NULL,
                `issuer` VARCHAR(255) NOT NULL,
                `country` VARCHAR(15) NOT NULL,
                `created_at` DATE,
                PRIMARY KEY (`hp_id`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

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

    /**
     * Delete Hipay mapping table
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
     * Delete Hipay mapping table
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
}
