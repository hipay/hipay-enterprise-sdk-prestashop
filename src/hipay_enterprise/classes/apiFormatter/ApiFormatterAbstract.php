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

require_once(dirname(__FILE__) . '/../../lib/vendor/autoload.php');
require_once(dirname(__FILE__) . '/ApiFormatterInterface.php');
require_once(dirname(__FILE__) . '/../helper/enums/CardPaymentProduct.php');

use \HiPay\Fullservice\Enum\Customer\Gender as Gender;

/**
 *
 * Api formatter abstract
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
abstract class ApiFormatterAbstract implements ApiFormatterInterface
{
    protected $module;
    protected $cart;
    protected $customer;
    protected $dbUtils;
    protected $threeDSDB;
    protected $dbToken;
    protected $delivery;
    protected $cardPaymentProduct = array(
        CardPaymentProduct::AMERICAN_EXPRESS,
        CardPaymentProduct::BCMC,
        CardPaymentProduct::CB,
        CardPaymentProduct::MAESTRO,
        CardPaymentProduct::MASTERCARD,
        CardPaymentProduct::VISA,
        CardPaymentProduct::HOSTED
    );

    public function __construct($module, $cart = false)
    {
        $this->module = $module;
        $this->context = Context::getContext();
        $this->configHipay = $this->module->hipayConfigTool->getConfigHipay();
        $this->mapper = new HipayMapper($module);
        $this->dbUtils = new HipayDBUtils($module);
        $this->threeDSDB = new HipayDBThreeDSQuery($module);
        $this->dbToken = new HipayDBTokenQuery($module);
        $this->cart = (!$cart) ? $this->context->cart : $cart;
        $this->customer = (is_null($this->cart)) ? false : new Customer((int)$this->cart->id_customer);
        $this->store = (is_null($this->cart)) ? false : new Store((int)$this->cart->id_shop);
        $this->delivery = (is_null($this->cart)) ? false : $this->getDeliveryAddress();
        $this->deliveryCountry = (is_null($this->cart)) ? false : new Country((int)$this->delivery->id_country);
        $this->deliveryState = (is_null($this->cart)) ? false : new State((int)$this->delivery->id_state);
        $this->currency = (is_null($this->cart)) ? false : new Currency((int)$this->cart->id_currency);
    }

    /**
     * Return correct delivery address
     */
    private function getDeliveryAddress()
    {
        $defaultAddress = new Address((int)$this->cart->id_address_delivery);
        try {
            $mappedCarrier = $this->mapper->getMappedHipayCarrierFromPSId((int)$this->cart->id_carrier);
            $method = $this->module->hipayConfigTool->getPaymentProduct(Tools::getValue('method'));
            if (
                $mappedCarrier['hp_carrier_mode'] === 'STORE'
                && isset($method['group'])
                && isset($method['group']['label'])
                && $method['group']['label'] === 'Oney'
            ) {
                // Create Address using store address fields
                $address = $defaultAddress;
                $address->address1 = $this->store->address1;
                if (is_array($address->address1)) {
                    $address->address1 = array_shift($address->address1);
                }
                $address->address2 = $this->store->address2;
                if (is_array($address->address2)) {
                    $address->address2 = array_shift($address->address2);
                }
                $address->postcode = $this->store->postcode;
                $address->city = $this->store->city;
                $address->phone = $this->store->phone;
                $address->country = Country::getNameById($this->cart->id_lang, $this->store->id_country);
                $address->id_country = $this->store->id_country;
                $address->id_state = $this->store->id_state;
                return $address;
            }

            return $defaultAddress;
        } catch (Exception $e) {
            if (!$e instanceof PaymentProductNotFoundException) {
                $this->module->getLogs()->logErrors($e->getMessage());
            }
            return $defaultAddress;
        }
    }

    /**
     * return correctly formatted gender code from prestashop gender ID
     * @param type $idGender
     * @return type
     */
    protected function getGender($idGender = null)
    {
        switch ($idGender) {
            case '1':
                $gender = Gender::MALE;
                break;
            case '2':
                $gender = Gender::FEMALE;
                break;
            default:
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
            case 'fr':
                $langCode = 'fr_FR';
                break;
            case 'fr':
                $langCode = 'fr_BE';
                break;
            case 'fr':
                $langCode = 'fr_LU';
                break;
            case 'lv':
                $langCode = 'lv_LV';
                break;
            case 'es':
                $langCode = 'es_ES';
                break;
            case 'pt':
                $langCode = 'pt_PT';
                break;
            case 'nl':
                $langCode = 'nl_NL';
                break;
            case 'nl':
                $langCode = 'nl_BE';
                break;
            case 'de':
                $langCode = 'de_DE';
                break;
            case 'de':
                $langCode = 'de_AT';
                break;
            case 'de':
                $langCode = 'de_LU';
                break;
            case 'it':
                $langCode = 'it_IT';
                break;
            case 'da':
                $langCode = 'da_DK';
                break;
            case 'cs':
                $langCode = 'cs_CZ';
                break;
            case 'pl':
                $langCode = 'pl_PL';
                break;
            case 'fi':
                $langCode = 'fi_FI';
                break;
            case 'hu':
                $langCode = 'hu_HU';
                break;
            case 'no':
                $langCode = 'no_NO';
                break;
            case 'sv':
                $langCode = 'sv_SE';
                break;
            case 'zh':
                $langCode = 'zh_CN';
                break;
            case 'en':
            default:
                $langCode = 'en_GB';
                break;
        }
        return $langCode;
    }

    abstract public function generate();

    abstract protected function mapRequest(&$request);
}
