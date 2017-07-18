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


class AdminHiPayRefundController extends AdminHiPayActionsController
{

    public function postProcess()
    {
        parent::postProcess();
        // First check
        if (Tools::isSubmit('hipay_refund_submit')) {
            //refund with no basket
            if (Tools::isSubmit('hipay_refund_type')) {
                $refund_type = Tools::getValue('hipay_refund_type');
                $refund_amount = Tools::getValue('hipay_refund_amount');
                $refund_amount = str_replace(
                    ' ',
                    '',
                    $refund_amount
                );
                $refund_amount = (float)str_replace(
                    ',',
                    '.',
                    $refund_amount
                );
            }

            if (!$refund_amount) {
                $hipay_redirect_status = $this->module->l(
                    'Please enter an amount',
                    'refund'
                );
                $this->context->cookie->__set('hipay_errors', $hipay_redirect_status);
                Tools::redirectAdmin(
                    $this->context->link->getAdminLink(
                        'AdminOrders'
                    ) . '&id_order=' . (int)$this->order->id . '&vieworder#hipay'
                );
                die('');
            }
            if ($refund_amount <= 0) {
                $hipay_redirect_status = $this->module->l(
                    'Please enter an amount greater than zero',
                    'refund'
                );
                $this->context->cookie->__set('hipay_errors', $hipay_redirect_status);
                Tools::redirectAdmin(
                    $this->context->link->getAdminLink(
                        'AdminOrders'
                    ) . '&id_order=' . (int)$this->order->id . '&vieworder#hipay'
                );
                die('');
            }

            if (!is_numeric($refund_amount)) {
                $hipay_redirect_status = $this->module->l(
                    'Please enter an amount',
                    'refund'
                );
                $this->context->cookie->__set('hipay_errors', $hipay_redirect_status);
                Tools::redirectAdmin(
                    $this->context->link->getAdminLink(
                        'AdminOrders'
                    ) . '&id_order=' . (int)$this->order->id . '&vieworder#hipay'
                );
                die('');
            }
            // we can refund only what has been captured
            $refundableAmount = $this->order->getTotalPaid();

            if (round(
                    $refund_amount,
                    2
                ) > round(
                    $refundableAmount,
                    2
                )
            ) {
                $hipay_redirect_status = $this->module->l(
                    'Amount exceeding authorized amount',
                    'refund'
                );
                $this->context->cookie->__set('hipay_errors', $hipay_redirect_status);
                Tools::redirectAdmin(
                    $this->context->link->getAdminLink(
                        'AdminOrders'
                    ) . '&id_order=' . (int)$this->order->id . '&vieworder#hipay'
                );
                die('');
            }

            if (!$this->transactionReference) {
                $hipay_redirect_status = $this->module->l(
                    'No transaction reference link to this order',
                    'refund'
                );
                $this->context->cookie->__set('hipay_errors', $hipay_redirect_status);
                Tools::redirectAdmin(
                    $this->context->link->getAdminLink(
                        'AdminOrders'
                    ) . '&id_order=' . (int)$this->order->id . '&vieworder#hipay'
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
            //refund with basket
            if (Tools::getValue('hipay_refund_type') == "partial") {
                $refundItems = (!Tools::getValue('hipayrefund')) ? array() : Tools::getValue('hipayrefund');
                if (array_sum($refundItems) == 0 && Tools::getValue('hipay_refund_fee')
                    !== "on"
                ) {
                    $hipay_redirect_status = $this->module->l(
                        'Select at least one item to refund',
                        'capture'
                    );
                    $this->context->cookie->__set('hipay_errors', $hipay_redirect_status);
                    Tools::redirectAdmin(
                        $this->context->link->getAdminLink(
                            'AdminOrders'
                        ) . '&id_order=' . (int)$this->order->id . '&vieworder#hipay'
                    );
                    die('');
                }

                $this->params = array("refundItems" => $refundItems,
                    "order" => $this->order->id, "transaction_reference" => $this->transactionReference,
                    "capture_refund_fee" => Tools::getValue('hipay_refund_fee'));
                $this->params["refundItems"] = $refundItems;
                $this->params["capture_refund_fee"] = Tools::getValue('hipay_refund_fee');
            } else {
                $this->params["refundItems"] = "full";
            }

            $this->apiHandler->handleRefund($this->params);
        }

        $this->context->cookie->__set('hipay_success', $this->module->l('The refund has been validated'));
        Tools::redirectAdmin(
            $this->context->link->getAdminLink(
                'AdminOrders'
            ) . '&id_order=' . (int)$this->order->id . '&vieworder#hipay'
        );
    }
}
