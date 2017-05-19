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
require_once(dirname(__FILE__) . '/../../lib/vendor/autoload.php');

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


