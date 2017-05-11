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

class Hipay_enterpriseRedirectModuleFrontController extends ModuleFrontController{

  public function initContent(){
    $this->display_column_left = false;
    $this->display_column_right = false;
    parent::initContent();

    $context = Context::getContext();
    $cart = $context->cart;

    $context->smarty->assign(array(
      'nbProducts' => $cart->nbProducts(),
      'cust_currency' => $cart->id_currency,
      'currencies' => $this->module->getCurrency((int) $cart->id_currency),
      'total' => $cart->getOrderTotal(true, Cart::BOTH),
      'this_path' => $this->module->getPathUri(),
      'this_path_bw' => $this->module->getPathUri(),
      'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->module->name . '/',
      'hipay_enterprise_tpl_dir' => _PS_MODULE_DIR_ . $this->module->name .'/views/templates/hook'
    ));

    switch($this->module->configHipay["payment"]["global"]["operating_mode"]){
      case "hosted_page":
        $path =  'paymentFormHostedPage16.tpl';
        break;
      case "api":
        $context->smarty->assign(array(
          'status_error' => '200', // Force to ok for first call
          'cart_id' => $cart->id,
          'amount' => $cart->getOrderTotal(true, Cart::BOTH)
        ));
        $path =  'paymentFormApi16.tpl';
        break;
      case "iframe":
        $path =  'paymentFormIframe16.tpl';
        break;
      default :
        $path =  'paymentFormHostedPage16.tpl';
        break;
      }

      return $this->setTemplate($path);
    }

    public function setMedia(){
      parent::setMedia();
      $this->addJS(array( _MODULE_DIR_ . 'hipay_enterprise/views/js/card-js.min.js'));
      $this->addJS(array( _MODULE_DIR_ . 'hipay_enterprise/views/js/devicefingerprint.js'));
      $this->addCSS(array( _MODULE_DIR_ . 'hipay_enterprise/views/css/card-js.min.css'));
    }


  }
