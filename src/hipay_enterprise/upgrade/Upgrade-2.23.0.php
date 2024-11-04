<?php

/**
 * HiPay Enterprise SDK Prestashop.
 *
 * 2024 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright 2017 HiPay
 * @license   https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 */
require_once dirname(__FILE__) . '/../classes/helper/dbquery/HipayDBSchemaManager.php';

function upgrade_module_2_23_0($module)
{
    $log = $module->getLogs();

    $log->logInfos('Upgrade to 2.23.0');

    try {
        $keepParameters = [
            'visa' => [
                'currencies' => '',
                'countries' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'frontPosition' => '',
                'activated' => '',
            ],
            'mastercard' => [
                'currencies' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'countries' => '',
                'frontPosition' => '',
                'activated' => '',
            ],
            'cb' => [
                'currencies' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'countries' => '',
                'frontPosition' => '',
                'activated' => '',
            ],
            'maestro' => [
                'currencies' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'countries' => '',
                'frontPosition' => '',
                'activated' => '',
            ],
            'american-express' => [
                'currencies' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'countries' => '',
                'frontPosition' => '',
                'activated' => '',
            ],
            'bcmc' => [
                'currencies' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'countries' => '',
                'frontPosition' => '',
                'activated' => '',
            ],
            '3xcb' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
                'merchantPromotion' => '',
            ],
            '3xcb-no-fees' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
                'merchantPromotion' => '',
            ],
            '4xcb' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
                'merchantPromotion' => '',
            ],
            '4xcb-no-fees' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
                'merchantPromotion' => '',
            ],
            'alma-3x' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => ''
            ],
            'alma-4x' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => ''
            ],
            'applepay' => [
                'currencies' => '',
                'countries' => '',
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
                'buttonType' => '',
                'buttonStyle' => '',
                'merchantId' => '',
            ],
            'bancontact' => [
                'currencies' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'countries' => '',
                'frontPosition' => '',
                'activated' => '',
            ],
            'bnpp-3xcb' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
            ],
            'bnpp-4xcb' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
            ],
            'giropay' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
            ],
            'ideal' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
            ],
            'paypal' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
                'currencies' => '',
                'countries' => '',
                'merchantId' => '',
                'buttonLabel' => '',
                'buttonShape' => '',
                'buttonHeight' => '',
                'buttonColor' => '',
                'bnpl' => '',
            ],
            'postfinance-card' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
                'currencies' => '',
                'countries' => '',
            ],
            'postfinance-efinance' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
                'currencies' => '',
                'countries' => '',
            ],
            'przelewy24' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
            ],
            'sdd' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
                'currencies' => '',
                'countries' => '',
            ],
            'sisal' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
                'currencies' => '',
                'countries' => '',
            ],
            'sofort-uberweisung' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
                'currencies' => '',
                'countries' => '',
            ],
            'carte-cadeau' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
            ],
            'mybank' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
            ],
            'multibanco' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
            ],
            'illicado' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
            ],
            'credit-long' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
                'merchantPromotion' => '',
            ],
            'credit-long-2' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
                'merchantPromotion' => '',
            ],
            'mbway' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
            ],
            'klarna' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
            ],
        ];

        // needed to update new config coming from the PHP SDK
        $module->hipayConfigTool->updateFromJSONFile($keepParameters);

        return true;
    } catch (Exception $e) {
        $log->logException($e);

        return false;
    }
}
