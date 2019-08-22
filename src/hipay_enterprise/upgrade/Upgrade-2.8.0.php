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

function upgrade_module_2_8_0($module)
{
    $log = $module->getLogs();

    $log->logInfos("Upgrade to 2.8.0");

    try {
        $log->logInfos('Upgrade Hipay credit card token table');
        $sql = "ALTER TABLE " . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_CC_TOKEN_TABLE . " ADD created_at DATE;";
        Db::getInstance()->execute($sql);

        return true;
    } catch (Exception $e) {
        $log->logException($e);
        return false;
    }
}
