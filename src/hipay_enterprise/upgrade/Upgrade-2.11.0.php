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

function upgrade_module_2_11_0($module)
{
    $log = $module->getLogs();

    $log->logInfos("Upgrade to 2.11.0");

    try {
        $hipaySchemaManager = new HipayDBSchemaManager($module);
        $hipaySchemaManager->createHipayNotificationTable();

        Configuration::updateValue('HIPAY_NOTIFICATION_THRESHOLD', 4);
        return true;
    } catch (Exception $e) {
        $log->logException($e);
        return false;
    }
}
