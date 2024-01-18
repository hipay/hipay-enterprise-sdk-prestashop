<?php
/**
 * HiPay Enterprise SDK Prestashop.
 *
 * 2022 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright 2017 HiPay
 * @license   https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 */
require_once dirname(__FILE__).'/../classes/helper/dbquery/HipayDBSchemaManager.php';

function upgrade_module_2_20_3($module)
{
    $log = $module->getLogs();

    $log->logInfos('Upgrade to 2.20.3');

    $sql = 'ALTER TABLE `'._DB_PREFIX_.HipayDBQueryAbstract::HIPAY_NOTIFICATION_TABLE.'`
        ADD COLUMN `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP';

    if (!Db::getInstance()->execute($sql)) {
        throw new Exception('Error during SQL request');
    }
}
