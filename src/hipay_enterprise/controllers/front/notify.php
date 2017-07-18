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
require_once(dirname(__FILE__) . '/../../classes/helper/tools/hipayHelper.php');

class Hipay_enterpriseNotifyModuleFrontController extends ModuleFrontController
{

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        if ($this->module->active == false) {
            die;
        }

        $postData = (array)$_POST;
        $data = array();
        foreach ($postData as $key => $value) {
            $data[$key] = $value;
        }
        //LOG
        $this->module->getLogs()->callbackLogs('##########################################');
        $this->module->getLogs()->callbackLogs('##########################################');
        $this->module->getLogs()->callbackLogs('CALLBACK HANDLING START');
        $this->module->getLogs()->callbackLogs(
            print_r(
                $data,
                true
            )
        );

        //   print_r($data);
        // if state and status exist or not
        if (!isset($data['state']) && !isset($data['status'])) {
            $this->module->getLogs()->errorLogsHipay(
                $this->module->l(
                    'Bad Callback initiated',
                    'hipay'
                )
            );
            $this->module->getLogs()->callbackLogs(
                $this->module->l(
                    'Bad Callback initiated',
                    'hipay'
                )
            );
            die();
        }

        $signature = (isset($_SERVER["HTTP_X_ALLOPASS_SIGNATURE"])) ? $_SERVER["HTTP_X_ALLOPASS_SIGNATURE"]
            : "";

        if (HipayHelper::checkSignature(
            $signature,
            $this->module->hipayConfigTool->getConfigHipay(),
            true
        )
        ) {
            $this->module->getLogs()->errorLogsHipay(
                $this->module->l(
                    'Bad Callback initiated - signature',
                    'hipay'
                )
            );
            $this->module->getLogs()->callbackLogs(
                $this->module->l(
                    'Bad Callback initiated',
                    'hipay'
                )
            );
            die('Bad Callback initiated');
        }
        $this->module->getLogs()->callbackLogs('state exist');

        $notificationHandler = new HipayNotification(
            $this->module,
            $data
        );

        $notificationHandler->processTransaction();

        die();
    }
}
