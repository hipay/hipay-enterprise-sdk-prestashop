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
if (!defined('_PS_VERSION_')) {
    exit;
}

class Hipay_enterprise extends PaymentModule
{
    public function __construct()
    {
        $this->name = 'hipay_enterprise';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        $this->author = 'HiPay';
        $this->is_eu_compatible = 1;

        $this->bootstrap = true;
        $this->display = 'view';

        $this->displayName = $this->l('HiPay Enterprise');
        $this->description = $this->l('Accept payments by credit card and other local methods with HiPay Enterprise. Very competitive rates, no configuration required!');

        // Compliancy
        $this->limited_countries = array(
            'AT', 'BE', 'CH', 'CY', 'CZ', 'DE', 'DK',
            'EE', 'ES', 'FI', 'FR', 'GB', 'GR', 'HK',
            'HR', 'HU', 'IE', 'IT', 'LI', 'LT', 'LU',
            'LV', 'MC', 'MT', 'NL', 'NO', 'PL', 'PT',
            'RO', 'RU', 'SE', 'SI', 'SK', 'TR'
        );

        parent::__construct();

    }


    /**
     * Functions installation HiPay module or uninstall
     */
    public function install()
    {
        if (extension_loaded('soap') == false) {
            $this->_errors[] = $this->l('You have to enable the SOAP extension on your server to install this module');
            return false;
        }
        $iso_code = Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT'));
        if (in_array($iso_code, $this->limited_countries) == false) {
            $this->_errors[] = $this->l('This module cannot work in your country');
            return false;
        }
        return parent::install() /*&& $this->installHipay()*/;
    }

    public function uninstall()
    {
        return /*$this->uninstallAdminTab() &&*/ parent::uninstall() /*&&  $this->clearAccountData()*/;
    }
}

if (_PS_VERSION_ >= '1.7') {
    // version 1.7
    require_once(_PS_ROOT_DIR_ . _MODULE_DIR_ . 'hipay_enterprise/hipay_enterprise-17.php');
} elseif (_PS_VERSION_ < '1.6') {
    // Version < 1.6
    Tools::displayError('The module HiPay Professional is not compatible with your PrestaShop');
}
