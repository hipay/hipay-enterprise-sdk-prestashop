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

require_once(dirname(__FILE__) . '/../../controllers/admin/AdminHiPayActions.php');


/**
 * Class AdminHiPayChallengeController
 *
 * Manage action for transaction in challenging status
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class AdminHiPayChallengeController extends AdminHiPayActionsController
{
    const ACCEPT_CHALLENGE = 'accept';

    const DENY_CHALLENGE = 'deny';

    /**
     * Post process method from HipayChallengeController
     */
    public function postProcess()
    {
        parent::postProcess();
        if (Tools::isSubmit('btn-challenge')) {
            $this->module->getLogs()->logInfos('# Challenge from Back-Office order id' . $this->order->id);
            $action = Tools::getValue('btn-challenge');
            try {
                switch ($action) {
                    case $this::ACCEPT_CHALLENGE:
                        $this->module->getLogs()->logInfos('# Challenge from Back-Office Accept');
                        $this->acceptChallenge();
                        break;
                    case $this::DENY_CHALLENGE:
                        $this->module->getLogs()->logInfos('# Challenge from Back-Office Deny');
                        $this->denyChallenge();
                }
            } catch (GatewayException $e) {
                $this->module->getLogs()->logErrors(
                    '# Errors Challenge from Back-Office reason :  ' . $e->getMessage()
                );
                $this->context->cookie->__set('hipay_errors', $e->getMessage());
                $this->redirectToOrder();
            }
        }

        $this->module->getLogs()->logInfos('# Challenge success');
        $this->context->cookie->__set('hipay_success', $this->module->l('The challenge has been validated'));
        $this->redirectToOrder();
    }

    /**
     *   Call API Maintenance for accept the challenge
     */
    private function acceptChallenge()
    {
        $this->apiHandler->handleAcceptChallenge($this->params);
    }

    /**
     * Call API Maintenance for deny the challenge
     */
    private function denyChallenge()
    {
        $this->apiHandler->handleDenyChallenge($this->params);
    }
}
