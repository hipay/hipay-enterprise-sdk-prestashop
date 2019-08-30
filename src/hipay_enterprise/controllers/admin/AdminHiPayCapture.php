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
 * Class AdminHiPayCaptureController
 *
 * Manage action for capture transaction
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class AdminHiPayCaptureController extends AdminHiPayActionsController
{

    public function postProcess()
    {
        parent::postProcess();
        // First check
        if (Tools::isSubmit('hipay_capture_submit')) {
            $this->module->getLogs()->logInfos('# Manual Capture without basket order ID {$this->order->id}');
            //capture with no basket
            if (Tools::isSubmit('hipay_capture_type')) {
                $capture_type = Tools::getValue('hipay_capture_type');
                $capture_amount = Tools::getValue('hipay_capture_amount');
                $capture_amount = str_replace(' ', '', $capture_amount);
                $capture_amount = (float)str_replace(',', '.', $capture_amount);
            }

            if (!$capture_amount) {
                $hipay_redirect_status = $this->module->l('Please enter an amount', 'capture');
                $this->context->cookie->__set('hipay_errors', $hipay_redirect_status);
                Tools::redirectAdmin(
                    $this->context->link->getAdminLink('AdminOrders') .
                    '&id_order=' .
                    (int)$this->order->id .
                    '&vieworder#hipay'
                );
                die('');
            }
            if ($capture_amount <= 0) {
                $hipay_redirect_status = $this->module->l('Please enter an amount greater than zero', 'capture');
                $this->context->cookie->__set('hipay_errors', $hipay_redirect_status);
                Tools::redirectAdmin(
                    $this->context->link->getAdminLink('AdminOrders') .
                    '&id_order=' .
                    (int)$this->order->id .
                    '&vieworder#hipay'
                );
                die('');
            }

            if (!is_numeric($capture_amount)) {
                $hipay_redirect_status = $this->module->l('Please enter an amount', 'capture');
                $this->context->cookie->__set('hipay_errors', $hipay_redirect_status);
                Tools::redirectAdmin(
                    $this->context->link->getAdminLink('AdminOrders') .
                    '&id_order=' .
                    (int)$this->order->id .
                    '&vieworder#hipay'
                );
                die('');
            }

            // total captured amount
            $totalPaid = $this->order->getTotalPaid();
            // remaining amount to capture
            $stillToCapture = $this->order->total_paid_tax_incl - $totalPaid;

            if (round($capture_amount, 2) > round($stillToCapture, 2)) {
                $hipay_redirect_status = $this->module->l('Amount exceeding authorized amount', 'capture');
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
                $hipay_redirect_status = $this->module->l('No transaction reference link to this order', 'capture');

                $this->context->cookie->__set('hipay_errors', $hipay_redirect_status);
                Tools::redirectAdmin(
                    $this->context->link->getAdminLink('AdminOrders') .
                    '&id_order=' .
                    (int)$this->order->id .
                    '&vieworder#hipay'
                );
                die('');
            }

            if ($capture_type == 'complete') {
                $this->params["amount"] = $stillToCapture;
                $this->apiHandler->handleCapture($this->params);
            } elseif ($capture_type == 'partial') {
                $this->params["amount"] = $capture_amount;
                $this->apiHandler->handleCapture($this->params);
            }
        } elseif ((Tools::isSubmit('hipay_capture_basket_submit'))) {
            $this->module->getLogs()->logInfos('# Manual Capture with basket');
            //capture with basket
            if (Tools::getValue('hipay_capture_type') == "partial") {
                $refundItems = (!Tools::getValue('hipaycapture')) ? array() : Tools::getValue('hipaycapture');
                // total captured amount
                $totalPaid = $this->order->getTotalPaid();
                // remaining amount to capture
                $stillToCapture = Tools::ps_round($this->order->total_paid_tax_incl - $totalPaid, 2);
                $capturedDiscounts = $this->dbMaintenance->discountsAreCaptured($this->order->id);

                //check if no items has been sent
                if (array_sum($refundItems) == 0 &&
                    Tools::getValue('hipay_capture_fee') !== "on" &&
                    Tools::getValue('hipay_capture_discount') !== "on" &&
                    Tools::getValue('hipay_capture_wrapping') !== "on"
                ) {
                    $hipay_redirect_status = $this->module->l('Select at least one item to capture', 'capture');
                    $this->context->cookie->__set('hipay_errors', $hipay_redirect_status);
                    Tools::redirectAdmin(
                        $this->context->link->getAdminLink('AdminOrders') .
                        '&id_order=' .
                        (int)$this->order->id .
                        '&vieworder#hipay'
                    );
                    die('');
                } else if (Tools::getValue('total-capture-input') <= 0) {
                    $hipay_redirect_status = $this->module->l('Capture amount must be greater than zero.', 'capture');
                    $this->context->cookie->__set('hipay_errors', $hipay_redirect_status);
                    Tools::redirectAdmin(
                        $this->context->link->getAdminLink('AdminOrders') .
                        '&id_order=' .
                        (int)$this->order->id .
                        '&vieworder#hipay'
                    );
                    die('');
                } else if (Tools::getValue('total-capture-input') > $stillToCapture + 0.01) {
                    $hipay_redirect_status = $this->module->l('Capture amount must be lower than the amount still to be captured.');
                    $this->context->cookie->__set('hipay_errors', $hipay_redirect_status);
                    Tools::redirectAdmin(
                        $this->context->link->getAdminLink('AdminOrders') .
                        '&id_order=' .
                        (int)$this->order->id .
                        '&vieworder#hipay'
                    );
                    die('');
                } else if (Tools::getValue('hipay_capture_discount')) {
                    if (!$capturedDiscounts &&
                        Tools::getValue('hipay_capture_discount') !== "on" &&
                        ($stillToCapture - Tools::getValue('total-capture-input') <=
                            Tools::getValue('capture-discount-amount'))
                    ) {
                        $hipay_redirect_status = $this->module->l('You must capture discount because next capture amount will be lower than total discount amount.');
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

                $this->params["refundItems"] = $refundItems;
                $this->params["capture_refund_fee"] = Tools::getValue('hipay_capture_fee');
                $this->params["capture_refund_discount"] = Tools::getValue('hipay_capture_discount');
                $this->params["capture_refund_wrapping"] = Tools::getValue('hipay_capture_wrapping');
            } else {
                $this->params["capture_refund_fee"] = true;
                $this->params["capture_refund_discount"] = true;
                $this->params["capture_refund_wrapping"] = true;
                $this->params["refundItems"] = "full";
            }
            if ($this->apiHandler->handleCapture($this->params)) {
                $this->module->getLogs()->logInfos('# Manual Capture success');
                $this->context->cookie->__set('hipay_success', $this->module->l('Capture has been validated'));
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
