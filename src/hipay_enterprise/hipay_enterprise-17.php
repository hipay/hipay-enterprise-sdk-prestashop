<?php
/**
* 2016 HiPay
*
* NOTICE OF LICENSE
*
* @author    HiPay <support.wallet@hipay.com>
* @copyright 2016 HiPay
* @license   https://github.com/hipay/hipay-wallet-sdk-prestashop/blob/master/LICENSE.md
*/
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

class HipayEnterpriseNew extends Hipay_enterprise
{
  public function hipayPaymentOptions($params){
    if (!$this->active) {
      return;
    }

    $payment_options = [
      $this->hipayExternalPaymentOption(),
    ];
    return $payment_options;
  }

  public function hookDisplayHeader($params){
    $this->context->controller->addCSS(_MODULE_DIR_ . $this->name . '/views/css/card-js.min.css', 'all');
    $this->context->controller->addJS(_MODULE_DIR_ . $this->name . '/views/js/card-js.min.js', 'all');
  }

  public function hipayExternalPaymentOption(){
    $lang = Tools::strtolower($this->context->language->iso_code);

    $this->context->smarty->assign(array(
      'module_dir' => $this->_path,
      'config_hipay' => $this->configHipay,
    ));



    $paymentForm = $this->fetch('module:hipay_enterprise/views/templates/hook/paymentForm17.tpl');
    $newOption = new PaymentOption();
    $newOption->setCallToActionText("pay by card")
    ->setForm($paymentForm)
    ->setAction($this->context->link->getModuleLink($this->name, 'redirect', array(), true))
    ;

    return $newOption;
  }
}
