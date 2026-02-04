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
require_once dirname(__FILE__) . '/../classes/helper/dbquery/HipayDBSchemaManager.php';

function upgrade_module_2_27_5($module)
{
    $log = $module->getLogs();

    $log->logInfos('Upgrade to 2.27.5');

    try {
        $log->logInfos('Checking Hipay transaction table columns');

        $tableName = _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_TRANSACTION_TABLE;

        // Check if reference_to_pay column exists, add it if missing
        if (!columnExists($tableName, 'reference_to_pay')) {
            $log->logInfos("Adding missing column 'reference_to_pay' to $tableName");
            $sql = "ALTER TABLE `$tableName` ADD `reference_to_pay` TEXT";
            Db::getInstance()->execute($sql);
            $log->logInfos("Column 'reference_to_pay' added successfully");
        } else {
            $log->logInfos("Column 'reference_to_pay' already exists in $tableName");
        }

        return true;
    } catch (Exception $e) {
        $log->logException($e);
        return false;
    }
}

/**
 * Check if a column exists in a table
 *
 * @param string $tableName
 * @param string $columnName
 * @return bool
 */
function columnExists($tableName, $columnName)
{
    $sql = "SHOW COLUMNS FROM `$tableName` LIKE '$columnName'";
    $result = Db::getInstance()->executeS($sql);
    return !empty($result);
}
