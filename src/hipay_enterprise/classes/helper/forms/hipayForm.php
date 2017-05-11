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
require_once(dirname(__FILE__) . '/hipayFormInput.php');

class HipayForm extends HipayFormInput {

    protected $context = false;
    protected $helper = false;
    protected $module = false;
    public $name = false;
    public $configHipay;

    public function __construct($module_instance) {
        // Requirements
        $this->context = Context::getContext();
        $this->module = $module_instance;
        $this->name = $module_instance->name;
        // init config hipay
        $this->configHipay = $module_instance->configHipay;

        // Form
        $this->helper = new HelperForm();

        $this->helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false);
        $this->helper->currentIndex .= '&' . http_build_query(array(
                    'configure' => $this->module->name,
                    'tab_module' => 'payments_gateways',
                    'module_name' => $this->module->name,
        ));

        $this->helper->module = $this;
        $this->helper->show_toolbar = false;
        $this->helper->token = Tools::getAdminTokenLite('AdminModules');

        $this->helper->tpl_vars = array(
            'id_language' => $this->context->language->id,
            'languages' => $this->context->controller->getLanguages()
        );

        return $this->helper;
    }

    public function generateForm($form) {
        return $this->helper->generateForm($form);
    }

    public function getGlobalPaymentMethodsForm() {
        
    }

}
