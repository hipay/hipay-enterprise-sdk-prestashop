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
require_once(dirname(__FILE__) . '/../../classes/helper/tools/hipayNotification.php');

class Hipay_enterpriseNotifyModuleFrontController extends ModuleFrontController {

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess() {

        if ($this->module->active == false) {
            die;
        }

        $postData = (array) $_POST;
        $data = array();
        foreach ($postData as $key => $value) {
            $data[$key] = $value;
        }
        //LOG 
        $this->module->getLogs()->logsHipay('##########################################');
        $this->module->getLogs()->logsHipay('##########################################');
        $this->module->getLogs()->logsHipay('CALLBACK HANDLING START');
        $this->module->getLogs()->logsHipay(print_r($data, TRUE));

        print_r($data);
        
        // if state and status exist or not
        if (!isset($data['state']) && !isset($data['status'])) {
            $this->module->getLogs()->errorLogsHipay($this->module->l('Bad Callback initiated', 'hipay'));
            die();
        }

        $this->module->getLogs()->logsHipay('state exist');
        
        $notificationHandler = new hipayNotification($this->module, $data);
        
        $notificationHandler->processTransaction();
        
        die();
        
        
        
    }

}
