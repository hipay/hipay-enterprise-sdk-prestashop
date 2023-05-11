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

        $log->logInfos('Deactivating oneclick for missing public credentials');
        $shops = Shop::getShops(false);
        foreach ($shops as $id => $shop) {
            $log->logInfos(
                "get HIPAY_CONFIG for shop " . $id . " and id shop group " . $shop['id_shop_group']
            );

            $configHipay = json_decode(
                Configuration::get('HIPAY_CONFIG', null, $shop['id_shop_group'], $id),
                true
            );

            if ($configHipay['account']['global']['sandbox_mode']) {
                if (empty($configHipay['account']["sandbox"]["api_tokenjs_username_sandbox"]) ||
                    empty($configHipay['account']["sandbox"]["api_tokenjs_password_publickey_sandbox"])) {
                    $configHipay['payment']["global"]["card_token"] = 0;
                }
            } else {
                if (empty($configHipay['account']["production"]["api_tokenjs_username_production"]) ||
                    empty($configHipay['account']["production"]["api_tokenjs_password_publickey_production"])) {
                    $configHipay['payment']["global"]["card_token"] = 0;
                }
            }

            $module->hipayConfigTool->setAllConfigHiPay($configHipay, $shop['id_shop_group'], $id);
        }

        return true;
    } catch (Exception $e) {
        $log->logException($e);
        return false;
    }
}
