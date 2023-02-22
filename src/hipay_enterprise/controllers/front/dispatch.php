<?php
/**
 * HiPay Enterprise SDK Prestashop.
 *
 * 2017 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright 2017 HiPay
 * @license   https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 */
require_once dirname(__FILE__).'/../../classes/exceptions/NotificationException.php';
require_once dirname(__FILE__).'/../../classes/helper/HipayNotification.php';
require_once dirname(__FILE__).'/../../classes/helper/HipayHelper.php';

/**
 * Class Hipay_enterpriseNotifyModuleFrontController.
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 *
 * @see    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class Hipay_enterprisedispatchModuleFrontController extends ModuleFrontController
{
    /** @var Hipay_entreprise */
    public $module;

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        if (false == $this->module->active) {
            $this->module->getLogs()->logErrors('Notify : postProcess => Module Disable');
            header('HTTP/1.1 403 Forbidden');
            exit;
        }

        try {
            $config = $this->module->hipayConfigTool->getConfigHipay();

            if (!isset($_REQUEST['token'])) {
                $this->module->getLogs()->logErrors('Dispatch : postProcess => missing token');
                header('HTTP/1.1 403 Forbidden');
                exit;
            }

            if ($_REQUEST['token'] !== $config['account']['global']['notification_cron_token']) {
                $this->module->getLogs()->logErrors('Dispatch : postProcess => invalid token');
                header('HTTP/1.1 403 Forbidden');
                exit;
            }

            $notificationHandler = new HipayNotification($this->module);
            $notificationHandler->dispatchWaitingNotifications();
        } catch (Exception $e) {
            header('HTTP/1.0 500 Internal server error');
            exit($e->getMessage());
        }
        exit;
    }
}
