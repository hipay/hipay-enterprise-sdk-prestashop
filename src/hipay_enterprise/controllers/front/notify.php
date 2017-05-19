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
class Hipay_enterpriseNotifyModuleFrontController extends ModuleFrontController {

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess() {
        $context = Context::getContext();
        
        $postData = (array) $_POST;
        $getData = (array) $_GET;
        
        $this->module->getLogs()->logsHipay("================Notification===================");
        $this->module->getLogs()->logsHipay(print_r($postData,true));
        $this->module->getLogs()->logsHipay(print_r($getData,true));
    }

}

require_once(_PS_ROOT_DIR_ . _MODULE_DIR_ . 'hipay_enterprise/lib/vendor/autoload.php');
