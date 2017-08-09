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

use HiPay\Fullservice\Enum\Transaction\ECI;

class Hipay_enterpriseNotifyModuleFrontController extends ModuleFrontController
{

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        if ($this->module->active == false) {
            $this->module->getLogs()->logErrors('Notify : postProcess => Module Disable');
            die;
        }
        $params = $_POST;
        $transactionReference = $params["transaction_reference"];
        // Process log from notification
        $this->module->getLogs()->logCallback($params);

        // Check if status is present in Post Data
        if (!isset($params['state']) && !isset($params['status'])) {
            $this->module->getLogs()->logErrors('Notify : Status not exist in Post DATA');
            die();
        }

        // Check Notification signature
        $signature = (isset($_SERVER["HTTP_X_ALLOPASS_SIGNATURE"])) ? $_SERVER["HTTP_X_ALLOPASS_SIGNATURE"]
            : "";

        $notificationHandler = new HipayNotification(
            $this->module,
            $params
        );

        $moto = false;
        if($notificationHandler->getEci() == ECI::MOTO){
            $moto = true;
        }

        if (!HipayHelper::checkSignature(
            $signature,
            $this->module->hipayConfigTool->getConfigHipay(),
            true,
            $moto
        )
        ) {
            $this->module->getLogs()->logErrors("Notify : Signature is wrong for Transaction $transactionReference.");
            die('Bad Callback initiated - signature');
        }
        
        
        $notificationHandler->processTransaction();
        die();
    }
}
