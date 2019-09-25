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

require_once(dirname(__FILE__) . '/../classes/helper/dbquery/HipayDBSchemaManager.php');

function upgrade_module_2_8_2($module)
{
    $log = $module->getLogs();

    $log->logInfos("Upgrade to 2.8.2");

    try {
        $log->logInfos('Upgrade Hipay transaction table');
        $sql = "ALTER TABLE " . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_TRANSACTION_TABLE . " ADD attempt_create_multi_use INT(10)";
        Db::getInstance()->execute($sql);

        $sql = "ALTER TABLE " . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_TRANSACTION_TABLE . " ADD customer_id INT(10)";
        Db::getInstance()->execute($sql);

        $sql = "ALTER TABLE " . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_TRANSACTION_TABLE . " ADD auth_info_method VARCHAR(2)";
        Db::getInstance()->execute($sql);

        $sql = "ALTER TABLE " . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_TRANSACTION_TABLE . " ADD eci VARCHAR(3)";
        Db::getInstance()->execute($sql);

        return true;
    } catch (Exception $e) {
        $log->logException($e);
        return false;
    }


}
