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

function upgrade_module_2_9_0($module)
{
    $log = $module->getLogs();

    $log->logInfos("Upgrade to 2.9.0");

    try {
        $keepParameters = array(
            "visa" => array(
                "currencies" => "",
                "countries" => "",
                "minAmount" => "",
                "maxAmount" => "",
                "frontPosition" => "",
                "activated" => ""
            ),
            "mastercard" => array(
                "currencies" => "",
                "minAmount" => "",
                "maxAmount" => "",
                "countries" => "",
                "frontPosition" => "",
                "activated" => ""
            ),
            "cb" => array(
                "currencies" => "",
                "minAmount" => "",
                "maxAmount" => "",
                "countries" => "",
                "frontPosition" => "",
                "activated" => ""
            ),
            "maestro" => array(
                "currencies" => "",
                "minAmount" => "",
                "maxAmount" => "",
                "countries" => "",
                "frontPosition" => "",
                "activated" => ""
            ),
            "american-express" => array(
                "currencies" => "",
                "minAmount" => "",
                "maxAmount" => "",
                "countries" => "",
                "frontPosition" => "",
                "activated" => ""
            ),
            "bcmc" => array(
                "currencies" => "",
                "minAmount" => "",
                "maxAmount" => "",
                "countries" => "",
                "frontPosition" => "",
                "activated" => ""
            ),
            "3xcb" => array(
                "displayName" => "",
                "frontPosition" => "",
                "minAmount" => "",
                "maxAmount" => "",
                "activated" => ""
            ),
            "3xcb-no-fees" => array(
                "displayName" => "",
                "frontPosition" => "",
                "minAmount" => "",
                "maxAmount" => "",
                "activated" => ""
            ),
            "4xcb" => array(
                "displayName" => "",
                "frontPosition" => "",
                "minAmount" => "",
                "maxAmount" => "",
                "activated" => ""
            ),
            "4xcb-no-fees" => array(
                "displayName" => "",
                "frontPosition" => "",
                "minAmount" => "",
                "maxAmount" => "",
                "activated" => ""
            ),
            "4xcb-no-fees" => array(
                "displayName" => "",
                "frontPosition" => "",
                "minAmount" => "",
                "maxAmount" => "",
                "activated" => ""
            ),
            "aura" => array(
                "displayName" => "",
                "frontPosition" => "",
                "minAmount" => "",
                "maxAmount" => "",
                "activated" => ""
            ),
            "banamex" => array(
                "displayName" => "",
                "frontPosition" => "",
                "minAmount" => "",
                "maxAmount" => "",
                "activated" => ""
            ),
            "banco-do-brasil" => array(
                "displayName" => "",
                "frontPosition" => "",
                "minAmount" => "",
                "maxAmount" => "",
                "activated" => ""
            ),
            "bbva-bancomer" => array(
                "displayName" => "",
                "frontPosition" => "",
                "minAmount" => "",
                "maxAmount" => "",
                "activated" => ""
            ),
            "boleto-bancario" => array(
                "displayName" => "",
                "frontPosition" => "",
                "minAmount" => "",
                "maxAmount" => "",
                "activated" => ""
            ),
            "bradesco" => array(
                "displayName" => "",
                "frontPosition" => "",
                "minAmount" => "",
                "maxAmount" => "",
                "activated" => ""
            ),
            "caixa" => array(
                "displayName" => "",
                "frontPosition" => "",
                "minAmount" => "",
                "maxAmount" => "",
                "activated" => ""
            ),
            "itau" => array(
                "displayName" => "",
                "frontPosition" => "",
                "minAmount" => "",
                "maxAmount" => "",
                "activated" => ""
            ),
            "oxxo" => array(
                "displayName" => "",
                "frontPosition" => "",
                "minAmount" => "",
                "maxAmount" => "",
                "activated" => ""
            ),
            "santander-cash" => array(
                "displayName" => "",
                "frontPosition" => "",
                "minAmount" => "",
                "maxAmount" => "",
                "activated" => ""
            ),
            "santander-home-banking" => array(
                "displayName" => "",
                "frontPosition" => "",
                "minAmount" => "",
                "maxAmount" => "",
                "activated" => ""
            ),
            "dexia-directnet" => array(
                "displayName" => "",
                "frontPosition" => "",
                "minAmount" => "",
                "maxAmount" => "",
                "activated" => "",
                "currencies" => "",
                "countries" => ""
            ),
            "bnpp-3xcb" => array(
                "displayName" => "",
                "frontPosition" => "",
                "minAmount" => "",
                "maxAmount" => "",
                "activated" => ""
            ),
            "bnpp-4xcb" => array(
                "displayName" => "",
                "frontPosition" => "",
                "minAmount" => "",
                "maxAmount" => "",
                "activated" => ""
            ),
            "giropay" => array(
                "displayName" => "",
                "frontPosition" => "",
                "minAmount" => "",
                "maxAmount" => "",
                "activated" => ""
            ),
            "ideal" => array(
                "displayName" => "",
                "frontPosition" => "",
                "minAmount" => "",
                "maxAmount" => "",
                "activated" => ""
            ),
            "ing-homepay" => array(
                "displayName" => "",
                "frontPosition" => "",
                "minAmount" => "",
                "maxAmount" => "",
                "activated" => "",
                "currencies" => "",
                "countries" => ""
            ),
            "klarnainvoice" => array(
                "displayName" => "",
                "frontPosition" => "",
                "minAmount" => "",
                "maxAmount" => "",
                "activated" => "",
                "currencies" => "",
                "countries" => ""
            ),
            "paypal" => array(
                "displayName" => "",
                "frontPosition" => "",
                "minAmount" => "",
                "maxAmount" => "",
                "activated" => "",
                "currencies" => "",
                "countries" => ""
            ),
            "postfinance-card" => array(
                "displayName" => "",
                "frontPosition" => "",
                "minAmount" => "",
                "maxAmount" => "",
                "activated" => "",
                "currencies" => "",
                "countries" => ""
            ),
            "postfinance-efinance" => array(
                "displayName" => "",
                "frontPosition" => "",
                "minAmount" => "",
                "maxAmount" => "",
                "activated" => "",
                "currencies" => "",
                "countries" => ""
            ),
            "przelewy24" => array(
                "displayName" => "",
                "frontPosition" => "",
                "minAmount" => "",
                "maxAmount" => "",
                "activated" => ""
            ),
            "sdd" => array(
                "displayName" => "",
                "frontPosition" => "",
                "minAmount" => "",
                "maxAmount" => "",
                "activated" => "",
                "currencies" => "",
                "countries" => ""
            ),
            "sisal" => array(
                "displayName" => "",
                "frontPosition" => "",
                "minAmount" => "",
                "maxAmount" => "",
                "activated" => "",
                "currencies" => "",
                "countries" => ""
            ),
            "sofort-uberweisung" => array(
                "displayName" => "",
                "frontPosition" => "",
                "minAmount" => "",
                "maxAmount" => "",
                "activated" => "",
                "currencies" => "",
                "countries" => ""
            ),
            "yandex" => array(
                "displayName" => "",
                "frontPosition" => "",
                "minAmount" => "",
                "maxAmount" => "",
                "activated" => ""
            ),
            "carte-cadeau" => array(
                "displayName" => "",
                "frontPosition" => "",
                "minAmount" => "",
                "maxAmount" => "",
                "activated" => ""
            ),
            "mybank" => array(
                "displayName" => "",
                "frontPosition" => "",
                "minAmount" => "",
                "maxAmount" => "",
                "activated" => ""
            ),
            "multibanco" => array(
                "displayName" => "",
                "frontPosition" => "",
                "minAmount" => "",
                "maxAmount" => "",
                "activated" => ""
            )
        );

        // needed to update new config coming from the PHP SDK
        $module->hipayConfigTool->updateFromJSONFile($keepParameters);

        return true;
    } catch (Exception $e) {
        $log->logException($e);
        return false;
    }
}
