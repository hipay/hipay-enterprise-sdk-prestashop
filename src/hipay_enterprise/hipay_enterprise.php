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

class Hipay_enterprise extends PaymentModule{

  public function __construct(){
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

    $this->configHipay = $this->getConfigHiPay();

  }


  /**
  * Functions installation HiPay module or uninstall
  */
  public function install(){
    if (extension_loaded('soap') == false) {
      $this->_errors[] = $this->l('You have to enable the SOAP extension on your server to install this module');
      return false;
    }
    $iso_code = Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT'));
    if (in_array($iso_code, $this->limited_countries) == false) {
      $this->_errors[] = $this->l('This module cannot work in your country');
      return false;
    }
    return parent::install() && $this->installHipay();
  }

  public function uninstall()
  {
    return /*$this->uninstallAdminTab() &&*/ parent::uninstall() /*&&  $this->clearAccountData()*/;
  }

  public function installHipay(){
    return $this->installAdminTab();
  }

  public function installAdminTab(){
    $class_names = [
      'AdminHiPayCapture',
      'AdminHiPayRefund',
      'AdminHiPayConfig',
    ];
    return $this->createTabAdmin($class_names);
  }

  protected function createTabAdmin($class_names){
    foreach ($class_names as $class_name) {
      $tab = new Tab();
      $tab->active = 1;
      $tab->module = $this->name;
      $tab->class_name = $class_name;
      $tab->id_parent = -1;
      foreach (Language::getLanguages(true) as $lang) {
        $tab->name[$lang['id_lang']] = $this->name;
      }
      if (!$tab->add()) {
        return false;
      }
    }
    return true;
  }


  /**
  * Load configuration page
  * @return string
  */
  public function getContent(){

    $configuration = $this->local_path . 'views/templates/admin/configuration.tpl';

    $this->context->smarty->assign(array(
      //      'alerts' => $this->context->smarty->fetch($alerts),
            'module_dir' => $this->_path,
            'config_hipay' => $this->objectToArray($this->configHipay),
    //        'url_test_hipay_direct' => Hipay_Professional::URL_TEST_HIPAY_DIRECT,
    //        'url_prod_hipay_direct' => Hipay_Professional::URL_PROD_HIPAY_DIRECT,
    //        'url_test_hipay_wallet' => Hipay_Professional::URL_TEST_HIPAY_WALLET,
    //        'url_prod_hipay_wallet' => Hipay_Professional::URL_PROD_HIPAY_WALLET,
    //        'ajax_url' => $this->context->link->getAdminLink('AdminHiPayConfig'),
        ));

    return $this->context->smarty->fetch($configuration);
  }

  /**
     * Functions to init the configuration HiPay
     */
    public function getConfigHiPay()
    {
        // init multistore
        $id_shop = (int)$this->context->shop->id;
        $id_shop_group = (int)Shop::getContextShopGroupID();
        $confHipay = Configuration::get('HIPAY_CONFIG', null, $id_shop_group, $id_shop);

        // if config exist but empty, init new object for configHipay
        if (!$confHipay || empty($confHipay)) {
            $this->insertConfigHiPay();
        }

        // not empty in bdd and the config is stacked in JSON
        $result = Tools::jsonDecode(Configuration::get('HIPAY_CONFIG', null, $id_shop_group, $id_shop));
        return (object)$result;
    }

    public function insertConfigHiPay()
    {
      //  $this->logs->logsHipay('---- >> function insertConfigHiPay');
        // init objet config for HiPay
        $objHipay = new StdClass();

        // settings configuration
        $objHipay->user_mail = '';
        $objHipay->sandbox_mode = 0;
        $objHipay->sandbox_ws_login = '';
        $objHipay->sandbox_ws_password = '';
        $objHipay->production_ws_login = '';
        $objHipay->production_ws_password = '';
        $objHipay->welcome_message_shown = 0;
        $objHipay->proxyUrl = '';
        $objHipay->proxyLogin = '';
        $objHipay->proxyPassword = '';
        $objHipay->sandbox = '';
        $objHipay->production = '';
        $objHipay->selected = '';

        // payment button configuration
        $objHipay->payment_form_type = 1;
        $objHipay->manual_capture = 0;
        $objHipay->button_text_fr = 'Payer par carte bancaire';
        $objHipay->button_text_en = 'Pay by credit or debit card';
        $objHipay->button_images = 'default.png';
        $objHipay->mode_debug = 1;

        // information about the account
        $objHipay->production_entity = '';
        $objHipay->bank_info_validated = 0;
        $objHipay->identified = 0;
        $objHipay->production_status = 0;

        return $this->setAllConfigHiPay($objHipay);
    }

    public function setAllConfigHiPay($objHipay = null)
    {
      //  $this->logs->logsHipay('---- >> function setAllConfigHiPay');
        // use this function if you have a few variables to update
        if ($objHipay != null) {
            $for_json_hipay = $objHipay;
        } else {
            $for_json_hipay = $this->configHipay;
        }
        // init multistore
        $id_shop = (int)$this->context->shop->id;
        $id_shop_group = (int)Shop::getContextShopGroupID();
        // the config is stacked in JSON
        if (Configuration::updateValue('HIPAY_CONFIG', Tools::jsonEncode($for_json_hipay), false, $id_shop_group, $id_shop)) {
            return true;
        } else {
            throw new Exception($this->l('Update failed, try again.'));
        }
    }

    /**
     * various functions
     */
    public function objectToArray($data)
    {
        // convert the config object to array config
        // used for the templates for example
        if (is_array($data) || is_object($data)) {
            $result = array();
            foreach ($data as $key => $value) {
                $result[$key] = $this->objectToArray($value);
            }
            return $result;
        }
        return $data;
    }
}

if (_PS_VERSION_ >= '1.7') {
  // version 1.7
  require_once(_PS_ROOT_DIR_ . _MODULE_DIR_ . 'hipay_enterprise/hipay_enterprise-17.php');
} elseif (_PS_VERSION_ < '1.6') {
  // Version < 1.6
  Tools::displayError('The module HiPay Professional is not compatible with your PrestaShop');
}
