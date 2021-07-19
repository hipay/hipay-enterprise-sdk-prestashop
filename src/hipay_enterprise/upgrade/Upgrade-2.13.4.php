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

function upgrade_module_2_13_4($module)
{
    $log = $module->getLogs();

    $log->logInfos('Upgrade to 2.13.4');

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
            '4xcb-no-fees' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
            ],
            'aura' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
            ],
            'banamex' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
            ],
            'banco-do-brasil' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
            ],
            'bbva-bancomer' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
            ],
            'boleto-bancario' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
            ],
            'bradesco' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
            ],
            'caixa' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
            ],
            'itau' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
            ],
            'oxxo' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
            ],
            'santander-cash' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
            ],
            'santander-home-banking' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
            ],
            'dexia-directnet' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
                'currencies' => '',
                'countries' => '',
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
            'ing-homepay' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
                'currencies' => '',
                'countries' => '',
            ],
            'klarnainvoice' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
                'currencies' => '',
                'countries' => '',
            ],
            'paypal' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
                'currencies' => '',
                'countries' => '',
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
            'yandex' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => '',
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
            'mbway' => [
                'displayName' => '',
                'frontPosition' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'activated' => ''
            ]
        ];

        // needed to update new config coming from the PHP SDK
        $module->hipayConfigTool->updateFromJSONFile($keepParameters);

        return true;
    } catch (Exception $e) {
        $log->logException($e);
        return false;
    }
}
