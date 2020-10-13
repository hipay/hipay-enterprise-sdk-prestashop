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

require_once(dirname(__FILE__) . '/../classes/helper/HipayDBQuery.php');

function upgrade_module_2_11_0($module)
{
    $log = $module->getLogs();

    $log->logInfos("Upgrade to 2.11.0");

    try {
        $log->logInfos('Add Hipay Notification table');

        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_NOTIFICATION_TABLE . '`(
                `hp_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `cart_id` INT(10) UNSIGNED NOT NULL,
                `transaction_ref` VARCHAR(45) NOT NULL,
                `notification_code` INT(4) UNSIGNED NOT NULL,
                `attempt_number` INT(3) UNSIGNED NOT NULL,
                `status` VARCHAR(255) NOT NULL,
                PRIMARY KEY (`hp_id`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

        Db::getInstance()->execute($sql);

        return true;
    } catch (Exception $e) {
        $log->logException($e);
        return false;
    }
}
