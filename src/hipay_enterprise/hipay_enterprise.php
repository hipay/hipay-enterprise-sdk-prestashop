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

  public $limited_countries = array();
  public $configHipay;
  public $_errors = array();
  public $min_amount = 1;


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

    // init log object
    $this->logs = new HipayLogs($this);

    // Compliancy
    $this->limited_countries = array(
      'AT', 'BE', 'CH', 'CY', 'CZ', 'DE', 'DK',
      'EE', 'ES', 'FI', 'FR', 'GB', 'GR', 'HK',
      'HR', 'HU', 'IE', 'IT', 'LI', 'LT', 'LU',
      'LV', 'MC', 'MT', 'NL', 'NO', 'PL', 'PT',
      'RO', 'RU', 'SE', 'SI', 'SK', 'TR'
    );

    parent::__construct();

    if (!Configuration::get('HIPAY_CONFIG')) {
      $this->warning = $this->l('Please, do not forget to configure your module');
    }

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
    return /*$this->uninstallAdminTab() &&*/ parent::uninstall() &&  $this->clearAccountData();
  }

  public function installHipay(){

    $return = $this->installAdminTab();
    if(_PS_VERSION_ >= '1.7'){
      $return17 = $this->registerHook('paymentOptions') && $this->registerHook("header");
      $return = $return && $return17;
    }else if(_PS_VERSION_ < '1.7' && _PS_VERSION_ >= '1.6'){
      $return16 = $this->registerHook('payment');
      $return = $return && $return16;
    }else

    return $return;
  }

  public function hookPayment($params){
  //  var_dump($params);

    $address = new Address(intval($params['cart']->id_address_delivery));
    $country = new Country(intval($address->id_country));
    $currency = new Currency(intval($params['cart']->id_currency));

    $this->smarty->assign(array(
                'domain' => Tools::getShopDomainSSL(true),
                'module_dir' => $this->_path,
                'payment_button' => $this->_path . 'views/img/amexa200.png' ,
                'min_amount' => $this->min_amount,
                'configHipay' => $this->configHipay,
                'activated_credit_card' => $this->getActivatedCreditCardByCountryAndCurrency($country, $currency),
                'lang' => Tools::strtolower($this->context->language->iso_code),
            ));
    $this->smarty->assign('hipay_prod', !(bool)$this->configHipay["account"]["global"]["sandbox_mode"]);

    return $this->display(dirname(__FILE__), 'views/templates/hook/payment.tpl');
  }

  protected function getActivatedCreditCardByCountryAndCurrency($country, $currency){
    $activatedCreditCard = array();
    foreach($this->configHipay["payment"]["credit_card"] as $name => $settings){
      if($settings["activated"] && (empty($settings["countries"]) || in_array( $country->iso_code, $settings["countries"]) ) && (empty($settings["currencies"]) || in_array( $currency->iso_code, $settings["currencies"]) ) ){
        $activatedCreditCard[$name] = $settings;
      }
    }
    return $activatedCreditCard;
  }

  /*
    * VERSION PS 1.7
    *
    */
    public function hookPaymentOptions($params)
    {
        $hipay17 = new HipayEnterpriseNew();
        return $hipay17->hipayPaymentOptions($params);
    }

    /*
      * VERSION PS 1.7
      *
      */
      public function hookHeader($params)
      {
          $hipay17 = new HipayEnterpriseNew();
          return $hipay17->hookDisplayHeader($params);
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

    $this->logs->logsHipay('##########################');
    $this->logs->logsHipay('---- START function getContent');
    $formGenerator = new HipayForm($this);

    $this->postProcess();

    $configuration = $this->local_path . 'views/templates/admin/configuration.tpl';

    $this->context->smarty->assign(array(
            'module_dir' => $this->_path,
            'config_hipay' => $this->configHipay,
            'logs' => $this->getLogFiles(),
            'module_url' => AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'),
            'form_errors' => $this->_errors,
        ));

    $this->logs->logsHipay('---- END function getContent');
    $this->logs->logsHipay('##########################');

    return $this->context->smarty->fetch($configuration);
  }

  /**
  * Process HTTP request send by module conifguration page
  */
  protected function postProcess(){
      $ur_redirection = AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules');
      $this->logs->logsHipay('---- >> function postProcess');

      if (Tools::isSubmit('logfile')) {
          $logFile = Tools::getValue('logfile');
          $path = _PS_MODULE_DIR_ . $this->logs->getBasePath() . $logFile;
          if (!file_exists($path)) {
              http_response_code(404);
              die('<h1>File not found</h1>');
          } else {
              header('Content-Type: text/plain');
              $content = file_get_contents($path);
              echo $content;
              die();
          }
      }else if (Tools::isSubmit('submitAccount')) {
        $this->logs->logsHipay('---- >> submitAccount');

        $this->saveAccountInformations();

        $this->context->smarty->assign('active_tab', 'account_form');
      }

  }

  /**
  * Save Account informations send by config page form
  *
  * @return : bool
  **/
  protected function saveAccountInformations(){
      $this->logs->logsHipay('---- >> function saveAccountInformations');

      try{
        // saving all array "account" in $this->configHipay
        $accountConfig = array("global" => array(),"sandbox" => array(),"production" => array());

        //requirement : input name in tpl must be the same that name of indexes in $this->configHipay

        foreach($this->configHipay["account"]["global"] as $key => $value){
            $fieldValue = Tools::getValue($key);
            $accountConfig["global"][$key] = $fieldValue;
        }

        foreach($this->configHipay["account"]["sandbox"] as $key => $value){
            $fieldValue = Tools::getValue($key);
            $accountConfig["sandbox"][$key] = $fieldValue;
        }

        foreach($this->configHipay["account"]["production"] as $key => $value){
            $fieldValue = Tools::getValue($key);
            $accountConfig["production"][$key] = $fieldValue;
        }

        //save configuration
        $this->setConfigHiPay('account', $accountConfig);

        $this->_successes[] = $this->l('Settings configuration saved successfully.');
        $this->logs->logsHipay(print_r($this->configHipay, true));
        return true;

      }catch(Exception $e){
        // LOGS
        $this->logs->errorLogsHipay($e->getMessage());
        $this->_errors[] = $this->l($e->getMessage());
      }

      return false;
  }

    /**
     * Functions to init the configuration HiPay
     * @return : array
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
        $result = Tools::jsonDecode(Configuration::get('HIPAY_CONFIG', null, $id_shop_group, $id_shop),true);

        return $result;
    }

    /**
    * init module configuration
    * @return : bool
    */
    public function insertConfigHiPay()
    {
        $this->logs->logsHipay('---- >> function insertConfigHiPay');

        //TODO mock config for front test. credit_card and payment indexes must be injected through json

        $configFields = array(
          "account" => array(
            "global" => array(
              "sandbox_mode" => 0,
              "host_proxy" => "",
              "host_proxy" => "",
              "port_proxy" => "",
              "user_proxy" => "",
              "password_proxy" => ""
            ),
            "sandbox" => array(
              "api_username_sandbox" => "",
              "api_password_sandbox" => "",
              "api_tokenjs_username_sandbox" => "",
              "api_tokenjs_password_publickey_sandbox" => "",
              "api_secret_passphrase_sandbox" => "",
              "api_moto_username_sandbox" => "",
              "api_moto_password_sandbox" => "",
              "api_moto_secret_passphrase_sandbox" => ""
            ),
            "production" => array(
              "api_username_production" => "",
              "api_password_production" => "",
              "api_tokenjs_username_production" => "",
              "api_tokenjs_password_publickey_production" => "",
              "api_secret_passphrase_production" => "",
              "api_moto_username_production" => "",
              "api_moto_password_production" => "",
              "api_moto_secret_passphrase_production" => ""
            )
          ),
          "payment" => array(
            "global" => array(
              "operating_mode" => "hosted_page",
              "iframe_hosted_page_template" => "basic-js",
              "display_card_selector" => 0,
              "css_url" => "",
              "activate_3d_secure" => 1,
              "capture_mode" => "manual",
              "card_token" => 1
            ),
            "credit_card" => array(
              "mastercard" => array(
                "activated" => 1,
                "currencies" => array("EUR"),
                "countries" => array("EN")
              ),
              "visa" => array(
                "activated" => 1,
                "currencies" => array("USD", "EUR"),
                "countries" => array("EN", "FR")
              ),
            ),
            "local_payment" => array(
              "sisal" => array(
                "activated" => 1,
                "currencies" => array(),
                "countries" => array(),
                'logo' => 'sisal.png'
              ),
            )
          )
        );

        return $this->setAllConfigHiPay($configFields);
    }

    /**
    *  save a specific key of the module config
    * @param: string $key
    * @param: mixed $value
    * @return : bool
    **/
    public function setConfigHiPay($key, $value)
    {
        $this->logs->logsHipay('---- >> function setConfigHiPay');
        // Use this function only if you have just one variable to update
        // init multistore
        $id_shop = (int)$this->context->shop->id;
        $id_shop_group = (int)Shop::getContextShopGroupID();
        // the config is stacked in JSON
        $this->configHipay[$key] = $value;
        if (Configuration::updateValue('HIPAY_CONFIG', Tools::jsonEncode($this->configHipay), false, $id_shop_group, $id_shop)) {
            return true;
        } else {
            throw new Exception($this->l('Update failed, try again.'));
        }
    }

    /**
    * Save initial module config
    * @param : array $arrayHipay
    *
    * @return : bool
    **/
    public function setAllConfigHiPay($arrayHipay = null)
    {
        $this->logs->logsHipay('---- >> function setAllConfigHiPay');
        // use this function if you have a few variables to update
        if ($arrayHipay != null) {
            $for_json_hipay = $arrayHipay;
        } else {
            $for_json_hipay = $this->configHipay;
        }

        // init multistore
        $id_shop = (int)$this->context->shop->id;
        $id_shop_group = (int)Shop::getContextShopGroupID();
        // the config is stacked in JSON
        $this->logs->logsHipay(print_r(Tools::jsonEncode($for_json_hipay),true));
        if (Configuration::updateValue('HIPAY_CONFIG', Tools::jsonEncode($for_json_hipay), false, $id_shop_group, $id_shop)) {
            return true;
        } else {
            throw new Exception($this->l('Update failed, try again.'));
        }
    }

    /**
     * Get the appropriate logs
     * @return string
     */
    protected function getLogFiles()
    {
        // scan log dir
        $dir = _PS_MODULE_DIR_ . $this->logs->getBasePath();
        $files = scandir($dir, 1);
        // init array files
        $error_files = [];
        $info_files = [];
        $callback_files = [];
        $request_files = [];
        $refund_files = [];
        // dispatch files
        foreach ($files as $file) {
            if (preg_match("/error/i", $file) && count($error_files) < 10) {
                $error_files[] = $file;
            }
            if (preg_match("/callback/i", $file) && count($callback_files) < 10) {
                $callback_files[] = $file;
            }
            if (preg_match("/infos/i", $file) && count($info_files) < 10) {
                $info_files[] = $file;
            }
            if (preg_match("/request/i", $file) && count($request_files) < 10) {
                $request_files[] = $file;
            }
            if (preg_match("/refund/i", $file) && count($refund_files) < 10) {
                $refund_files[] = $file;
            }
        }
        return [
            'error' => $error_files,
            'infos' => $info_files,
            'callback' => $callback_files,
            'request' => $request_files,
            'refund' => $refund_files
        ];
    }

    /**
   * Clear every single merchant account data
   * @return boolean
   */
  protected function clearAccountData()
  {
      $this->logs->logsHipay('---- >> function clearAccountData');
      Configuration::deleteByName('HIPAY_CONFIG');
      return true;
  }

}

if (_PS_VERSION_ >= '1.7') {
  // version 1.7
  require_once(_PS_ROOT_DIR_ . _MODULE_DIR_ . 'hipay_enterprise/hipay_enterprise-17.php');
} elseif (_PS_VERSION_ < '1.6') {
  // Version < 1.6
  Tools::displayError('The module HiPay Enterprise is not compatible with your PrestaShop');
}

require_once(_PS_ROOT_DIR_ . _MODULE_DIR_ . 'hipay_enterprise/classes/helper/tools/hipayLogs.php');
require_once(_PS_ROOT_DIR_ . _MODULE_DIR_ . 'hipay_enterprise/classes/helper/forms/hipayForm.php');
