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
 * Class AdminHiPayRefundController
 *
 * Manage action for refund transaction
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class AdminHiPayRefundController extends AdminHiPayActionsController
{

    public function postProcess()
    {
        parent::postProcess();
        // First check
        if (Tools::isSubmit('hipay_refund_submit')) {
            $this->module->getLogs()->logInfos('# Refund Capture without basket order ID {$this->order->id}');
            //refund with no basket
            if (Tools::isSubmit('hipay_refund_type')) {
                $refund_type = Tools::getValue('hipay_refund_type');
                $refund_amount = Tools::getValue('hipay_refund_amount');
                $refund_amount = str_replace(' ', '', $refund_amount);
                $refund_amount = (float)str_replace(',', '.', $refund_amount);
            }

            if (!$refund_amount) {
                $hipay_redirect_status = $this->module->l('Please enter an amount', 'refund');
                $this->context->cookie->__set('hipay_errors', $hipay_redirect_status);
                Tools::redirectAdmin(
                    $this->context->link->getAdminLink('AdminOrders') .
                    '&id_order=' .
                    (int)$this->order->id .
                    '&vieworder#hipay'
                );
                die('');
            }
            if ($refund_amount <= 0) {
                $hipay_redirect_status = $this->module->l('Please enter an amount greater than zero', 'refund');
                $this->context->cookie->__set('hipay_errors', $hipay_redirect_status);
                Tools::redirectAdmin(
                    $this->context->link->getAdminLink('AdminOrders') .
                    '&id_order=' .
                    (int)$this->order->id .
                    '&vieworder#hipay'
                );
                die('');
            }

            if (!is_numeric($refund_amount)) {
                $hipay_redirect_status = $this->module->l('Please enter an amount', 'refund');
                $this->context->cookie->__set('hipay_errors', $hipay_redirect_status);
                Tools::redirectAdmin(
                    $this->context->link->getAdminLink('AdminOrders') .
                    '&id_order=' .
                    (int)$this->order->id .
                    '&vieworder#hipay'
                );
                die('');
            }
            // we can refund only what has been captured
            $refundableAmount = $this->order->getTotalPaid();

            if (round($refund_amount, 2) > round($refundableAmount, 2)) {
                $hipay_redirect_status = $this->module->l('Amount exceeding authorized amount', 'refund');
                $this->context->cookie->__set('hipay_errors', $hipay_redirect_status);
                Tools::redirectAdmin(
                    $this->context->link->getAdminLink('AdminOrders') .
                    '&id_order=' .
                    (int)$this->order->id .
                    '&vieworder#hipay'
                );
                die('');
            }

            if (!$this->transactionReference) {
                $hipay_redirect_status = $this->module->l('No transaction reference link to this order', 'refund');

                $this->module->getLogs()->logInfos('# Refund errors Message {$hipay_redirect_status} ');
                $this->context->cookie->__set('hipay_errors', $hipay_redirect_status);

                Tools::redirectAdmin(
                    $this->context->link->getAdminLink('AdminOrders') .
                    '&id_order=' .
                    (int)$this->order->id .
                    '&vieworder#hipay'
                );
                die('');
            }

            if ($refund_type == 'complete') {
                $this->params["amount"] = $refundableAmount;
                $this->apiHandler->handleRefund($this->params);
            } elseif ($refund_type == 'partial') {
                $this->params["amount"] = $refund_amount;
                $this->apiHandler->handleRefund($this->params);
            }
        } elseif ((Tools::isSubmit('hipay_refund_basket_submit'))) {
            $this->module->getLogs()->logInfos('# Refund Capture with basket order ID {$this->order->id}');
            // we can refund only what has been captured
            $refundableAmount = $this->order->getTotalPaid();
            $refundedDiscounts = $this->db->discountsAreRefunded($this->order->id);

            //refund with basket
            if (Tools::getValue('hipay_refund_type') == "partial") {
                $refundItems = (!Tools::getValue('hipayrefund')) ? array() : Tools::getValue('hipayrefund');
                if (array_sum($refundItems) == 0 &&
                    Tools::getValue('hipay_refund_fee') !== "on" &&
                    Tools::getValue('hipay_refund_discount') !== "on"
                ) {
                    $hipay_redirect_status = $this->module->l('Select at least one item to refund', 'capture');

                    $this->module->getLogs()->logInfos('# Refund errors Message {$hipay_redirect_status} ');
                    $this->context->cookie->__set('hipay_errors', $hipay_redirect_status);
                    Tools::redirectAdmin(
                        $this->context->link->getAdminLink('AdminOrders') .
                        '&id_order=' .
                        (int)$this->order->id .
                        '&vieworder#hipay'
                    );
                    die('');
                } else if (Tools::getValue('total-refund-input') <= 0) {
                    $hipay_redirect_status = $this->module->l('Refund amount must be greater than zero.', 'capture');
                    $this->context->cookie->__set('hipay_errors', $hipay_redirect_status);
                    Tools::redirectAdmin(
                        $this->context->link->getAdminLink('AdminOrders') .
                        '&id_order=' .
                        (int)$this->order->id .
                        '&vieworder#hipay'
                    );
                    die('');
                } else if (Tools::getValue('total-refund-input') > $refundableAmount) {
                    $hipay_redirect_status = $this->module->l('Refund amount must be lower than the amount still to be refunded.');
                    $this->context->cookie->__set('hipay_errors', $hipay_redirect_status);
                    Tools::redirectAdmin(
                        $this->context->link->getAdminLink('AdminOrders') .
                        '&id_order=' .
                        (int)$this->order->id .
                        '&vieworder#hipay'
                    );
                    die('');
                } else if (Tools::getValue('hipay_refund_discount')) {
                    if (!$refundedDiscounts &&
                        Tools::getValue('hipay_refund_discount') !== "on" &&
                        ($refundableAmount - Tools::getValue('total-refund-input') <=
                            Tools::getValue('capture-refund-amount'))
                    ) {
                        $hipay_redirect_status = $this->module->l('You must refund discount because next refund amount will be lower than total discount amount.');
                        $this->context->cookie->__set('hipay_errors', $hipay_redirect_status);
                        Tools::redirectAdmin(
                            $this->context->link->getAdminLink('AdminOrders') .
                            '&id_order=' .
                            (int)$this->order->id .
                            '&vieworder#hipay'
                        );
                        die('');
                    }
                }

                $this->params = array(
                    "refundItems" => $refundItems,
                    "order" => $this->order->id,
                    "transaction_reference" => $this->transactionReference,
                    "capture_refund_fee" => Tools::getValue('hipay_refund_fee')
                );
                $this->params["refundItems"] = $refundItems;
                $this->params["capture_refund_fee"] = Tools::getValue('hipay_refund_fee');
                $this->params["capture_refund_discount"] = Tools::getValue('hipay_refund_discount');
            } else {
                $this->params["capture_refund_discount"] = true;
                $this->params["capture_refund_fee"] = true;
                $this->params["refundItems"] = "full";
            }

            if ($this->apiHandler->handleRefund($this->params)) {
                $this->module->getLogs()->logInfos('# Refund Capture success');
                $this->context->cookie->__set('hipay_success', $this->module->l('The refund has been validated'));
            }
        }

        Tools::redirectAdmin(
            $this->context->link->getAdminLink('AdminOrders') .
            '&id_order=' .
            (int)$this->order->id .
            '&vieworder#hipay'
        );
    }
}
