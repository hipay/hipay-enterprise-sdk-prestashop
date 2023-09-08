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

function upgrade_module_2_21_0($module)
{
    $log = $module->getLogs();

    $log->logInfos("Upgrade to 2.21.0");

    try {
        $log->logInfos('Upgrade Hipay transaction table');

        $sql = "ALTER TABLE " . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_TRANSACTION_TABLE . " ADD reference_to_pay TEXT";
        Db::getInstance()->execute($sql);

        return true;
    } catch (Exception $e) {
        $log->logException($e);
        return false;
    }
}
