<?php
/**
 * 2017 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.wallet@hipay.com>
 * @copyright 2017 HiPay
 * @license   https://github.com/hipay/hipay-wallet-sdk-prestashop/blob/master/LICENSE.md
 */
require_once(dirname(__FILE__).'/../../../lib/vendor/autoload.php');
require_once(dirname(__FILE__).'/ApiFormatterInterface.php');

use \HiPay\Fullservice\Enum\Customer\Gender as Gender;

abstract class ApiFormatterAbstract implements ApiFormatterInterface
{
    protected $module;

    public function __construct($module)
    {
        $this->module          = $module;
        $this->context         = Context::getContext();
        $this->configHipay     = $this->module->hipayConfigTool->getConfigHipay();
        $this->mapper          = new HipayMapper($module);
        $this->cart            = $this->context->cart;
        $this->customer        = (is_null($this->cart)) ? false : new Customer((int) $this->cart->id_customer);
        $this->store           = (is_null($this->cart)) ? false : new Store((int) $this->cart->id_shop);
        $this->delivery        = (is_null($this->cart)) ? false : new Address((int) $this->cart->id_address_delivery);
        $this->deliveryCountry = (is_null($this->cart)) ? false : new Country((int) $this->delivery->id_country);
        $this->currency        = (is_null($this->cart)) ? false : new Currency((int) $this->cart->id_currency);
    }

    /**
     * return correctly formatted gender code from prestashop gender ID
     * @param type $idGender
     * @return type
     */
    protected function getGender($idGender = NULL)
    {
        // Gender of the customer (M=male, F=female, U=unknown).
        $gender = Gender::UNKNOWN;

        if ($idGender == NULL) $gender = 'U';
        switch ($idGender) {
            case '1' :
                $gender = Gender::MALE;
                break;
            case '2' :
                $gender = Gender::FEMALE;
                break;
            default :
                $gender = Gender::UNKNOWN;
                break;
        }

        return $gender;
    }

    /**
     * return correctly formatted country code from iso country code
     * @param type $isoCode
     * @return string
     */
    protected function getLanguageCode($isoCode = 'en')
    {
        $langCode = 'en_GB';
        switch (Tools::strtolower($isoCode)) {
            case 'fr' :
                $langCode = 'fr_FR';
                break;
            case 'fr' :
                $langCode = 'fr_BE';
                break;
            case 'fr' :
                $langCode = 'fr_LU';
                break;
            case 'lv' :
                $langCode = 'lv_LV';
                break;
            case 'es' :
                $langCode = 'es_ES';
                break;
            case 'pt' :
                $langCode = 'pt_PT';
                break;
            case 'nl' :
                $langCode = 'nl_NL';
                break;
            case 'nl' :
                $langCode = 'nl_BE';
                break;
            case 'de' :
                $langCode = 'de_DE';
                break;
            case 'de' :
                $langCode = 'de_AT';
                break;
            case 'de' :
                $langCode = 'de_LU';
                break;
            case 'it' :
                $langCode = 'it_IT';
                break;
            case 'da' :
                $langCode = 'da_DK';
                break;
            case 'cs' :
                $langCode = 'cs_CZ';
                break;
            case 'pl' :
                $langCode = 'pl_PL';
                break;
            case 'fi' :
                $langCode = 'fi_FI';
                break;
            case 'hu' :
                $langCode = 'hu_HU';
                break;
            case 'no' :
                $langCode = 'no_NO';
                break;
            case 'sv' :
                $langCode = 'sv_SE';
                break;
            case 'zh' :
                $langCode = 'zh_CN';
                break;
            case 'en' :
            default :
                $langCode = 'en_GB';
                break;
        }
        return $langCode;
    }

    abstract public function generate();

    abstract protected function mapRequest(&$request);
}