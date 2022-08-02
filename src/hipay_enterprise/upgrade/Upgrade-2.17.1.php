<?php
/**
 * HiPay Enterprise SDK Prestashop
 *
 * 2022 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright 2017 HiPay
 * @license   https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 */

require_once(dirname(__FILE__) . '/../classes/helper/dbquery/HipayDBSchemaManager.php');

function upgrade_module_2_17_1($module)
{
    $log = $module->getLogs();

    $log->logInfos('Upgrade to 2.17.1');

    try {
        $hipaySchemaManager = new HipayDBSchemaManager($module);
        $hipaySchemaManager->createHipayPaymentConfigTable();

        $module->hipayConfigTool->getConfigHipay();
        $module->hipayConfigTool->setAllConfigHiPay();

        return true;
    } catch (Exception $e) {
        $log->logException($e);
        return false;
    }
}