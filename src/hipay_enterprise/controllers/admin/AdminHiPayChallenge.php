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

require_once(dirname(__FILE__) . '/../../controllers/admin/AdminHiPayActions.php');


/**
 * Class AdminHiPayChallengeController
 *
 * Manage action for transaction in challenging status
 *
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
            $action = Tools::getValue('btn-challenge');
            try {
                switch ($action) {
                    case $this::ACCEPT_CHALLENGE:
                        $this->acceptChallenge();
                        break;
                    case $this::DENY_CHALLENGE:
                        $this->denyChallenge();
                }
            } catch (GatewayException $e) {
                $this->context->cookie->__set('hipay_errors', $e->getMessage());
                Tools::redirectAdmin(
                    $this->context->link->getAdminLink(
                        'AdminOrders'
                    ) . '&id_order=' . (int)$this->order->id . '&vieworder#hipay'
                );
            }
        }

        $this->context->cookie->__set('hipay_success', $this->module->l('The challenge has been validated'));
        Tools::redirectAdmin(
            $this->context->link->getAdminLink(
                'AdminOrders'
            ) . '&id_order=' . (int)$this->order->id . '&vieworder#hipay');
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
