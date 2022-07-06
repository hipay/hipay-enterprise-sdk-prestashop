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

require_once(dirname(__FILE__) . '/../classes/helper/enums/UXMode.php');
require_once(dirname(__FILE__) . '/../classes/helper/enums/OperatingMode.php');

function upgrade_module_2_17_0($module)
{
    $log = $module->getLogs();

    $log->logInfos('Upgrade to 2.17.0');

    $shops = Shop::getShops(false);
    foreach ($shops as $id => $shop) {
        $shopIdGroup = $shop['id_shop_group'];
        $log->logInfos("Get HIPAY_CONFIG for shop $id and id shop group $shopIdGroup");

        $configHipay = Tools::jsonDecode(
            Configuration::get('HIPAY_CONFIG', null, $shopIdGroup, $id),
            true
        );

        if ($configHipay['payment']['global']['operating_mode']['UXMode'] === UXMode::DIRECT_POST) {
            $configHipay['payment']['global']['operating_mode'] = OperatingMode::getOperatingMode(UXMode::HOSTED_FIELDS);
        }

        $module->hipayConfigTool->setAllConfigHiPay($configHipay, $shopIdGroup, $id);
    }

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
                'merchantId' => ''
            ],
            'bancontact' => [
                'currencies' => '',
                'minAmount' => '',
                'maxAmount' => '',
                'countries' => '',
                'frontPosition' => '',
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
