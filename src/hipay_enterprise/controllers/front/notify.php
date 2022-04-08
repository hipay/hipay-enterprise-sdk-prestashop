<?php
/**
 * HiPay Enterprise SDK Prestashop
 *
 * 2017 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright 2017 HiPay
 * @license   https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 */

require_once(dirname(__FILE__) . '/../../classes/exceptions/NotificationException.php');
require_once(dirname(__FILE__) . '/../../classes/helper/HipayNotification.php');
require_once(dirname(__FILE__) . '/../../classes/helper/HipayHelper.php');

use HiPay\Fullservice\Enum\Transaction\ECI;

/**
 * Class Hipay_enterpriseNotifyModuleFrontController
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class Hipay_enterpriseNotifyModuleFrontController extends ModuleFrontController
{

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {

        $this->name = 'notify';
        if ($this->module->active == false) {
            $this->module->getLogs()->logErrors('Notify : postProcess => Module Disable');
            header('HTTP/1.1 403 Forbidden');
            die;
        }

        $params = $_POST;
        $transactionReference = (isset($params["transaction_reference"])) ? $params["transaction_reference"] : '';
        // Process log from notification
        $this->module->getLogs()->logCallback($params);

        // Check if status is present in Post Data
        if (!isset($params['state']) && !isset($params['status'])) {
            $this->module->getLogs()->logErrors('Notify : Status not exist in Post DATA');
            header("HTTP/1.0 500 Internal server error");
            die();
        }

        // Check Notification signature
        $signature = (isset($_SERVER["HTTP_X_ALLOPASS_SIGNATURE"])) ? $_SERVER["HTTP_X_ALLOPASS_SIGNATURE"] : "";

        $notificationHandler = new HipayNotification($this->module, $params);

        $moto = false;
        $isApplePay = false;
        if ($notificationHandler->getEci() == ECI::MOTO) {
            $moto = true;
        } else if($notificationHandler->isApplePayOrder()) {
            $isApplePay = true;
        }

        if (!HipayHelper::checkSignature($signature, $this->module, true, $moto, $isApplePay)) {
            $this->module->getLogs()->logErrors("Notify : Signature is wrong for Transaction $transactionReference.");
            header('HTTP/1.1 403 Forbidden');
            die('Bad Callback initiated - signature');
        }

        try {
            $notificationHandler->processTransaction();
        } catch (NotificationException $e) {
            header($e->getReturnCode());
            die($e->getMessage());
        } catch (Exception $e) {
            header("HTTP/1.0 500 Internal server error");
            die($e->getMessage());
        }

        die();
    }
}
