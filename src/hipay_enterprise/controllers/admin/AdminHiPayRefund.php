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
require_once(dirname(__FILE__).'/../../classes/helper/apiHandler/ApiHandler.php');
require_once(dirname(__FILE__).'/../../classes/helper/tools/hipayDBQuery.php');

use HiPay\Fullservice\Enum\Transaction\Operation;

class AdminHiPayRefundController extends ModuleAdminController
{
    public function __construct()
    {
        $this->module    = 'hipay_enterprise';
        $this->bootstrap = true;
        $this->context   = Context::getContext();
        
        parent::__construct();

        $this->apiHandler = new ApiHandler($this->module, $this->context);
        $this->db         = new HipayDBQuery($this->module);
    }

    public function postProcess()
    {
        $context = Context::getContext();

        if (Tools::isSubmit('id_order') && Tools::getValue('id_order') > 0) {
            $order = new Order(Tools::getValue('id_order'));
            if (!Validate::isLoadedObject($order)) {
                throw new PrestaShopException('Can\'t load Order object');
            }
            ShopUrl::cacheMainDomainForShop((int) $order->id_shop);
            $transactionReference = $this->db->getTransactionReference($order->id);
        }

        // First check
        if (Tools::isSubmit('hipay_refund_submit')) {
            //refund with no basket
            if (Tools::isSubmit('hipay_refund_type')) {
                $refund_type   = Tools::getValue('hipay_refund_type');
                $refund_amount = Tools::getValue('hipay_refund_amount');
                $refund_amount = str_replace(' ', '', $refund_amount);
                $refund_amount = (float) str_replace(',', '.', $refund_amount);
            }

            if (!$refund_amount) {
                $hipay_redirect_status = $this->module->l('Please enter an amount',
                    'refund');
                Tools::redirectAdmin($context->link->getAdminLink('AdminOrders').'&id_order='.(int) $order->id.'&vieworder&hipay_err_refund='.$hipay_redirect_status.'#hipay');
                die('');
            }
            if ($refund_amount <= 0) {
                $hipay_redirect_status = $this->module->l('Please enter an amount greater than zero',
                    'refund');
                Tools::redirectAdmin($context->link->getAdminLink('AdminOrders').'&id_order='.(int) $order->id.'&vieworder&hipay_err_refund='.$hipay_redirect_status.'#hipay');
                die('');
            }

            if (!is_numeric($refund_amount)) {
                $hipay_redirect_status = $this->module->l('Please enter an amount',
                    'refund');
                Tools::redirectAdmin($context->link->getAdminLink('AdminOrders').'&id_order='.(int) $order->id.'&vieworder&hipay_err_refund='.$hipay_redirect_status.'#hipay');
                die('');
            }
            // we can refund only what has been captured
            $refundableAmount = $order->getTotalPaid();

            if (round($refund_amount, 2) > round($refundableAmount, 2)) {
                $hipay_redirect_status = $this->module->l('Amount exceeding authorized amount',
                    'refund');
                Tools::redirectAdmin($context->link->getAdminLink('AdminOrders').'&id_order='.(int) $order->id.'&vieworder&hipay_err_refund='.$hipay_redirect_status.'#hipay');
                die('');
            }

            if (!$transactionReference) {
                $hipay_redirect_status = $this->module->l('No transaction reference link to this order',
                    'refund');
                Tools::redirectAdmin($context->link->getAdminLink('AdminOrders').'&id_order='.(int) $order->id.'&vieworder&hipay_err_refund='.$hipay_redirect_status.'#hipay');
                die('');
            }

            if ($refund_type == 'complete') {
                $params = array("amount" => $refundableAmount, "transaction_reference" => $transactionReference);
                $this->apiHandler->handleRefund($params);
            } elseif ($refund_type == 'partial') {
                $params = array("amount" => $refund_amount, "transaction_reference" => $transactionReference);
                $this->apiHandler->handleRefund($params);
            }
        } elseif ((Tools::isSubmit('hipay_refund_basket_submit'))) {
            //refund with basket
            if (Tools::getValue('hipay_refund_type') == "partial") {
                $refundItems = (!Tools::getValue('hipayrefund')) ? array() : Tools::getValue('hipayrefund');
                if (array_sum($refundItems) == 0 && Tools::getValue('hipay_refund_fee')
                    !== "on") {
                    $hipay_redirect_status = $this->module->l('Select at least one item to refund',
                        'capture');
                    Tools::redirectAdmin($context->link->getAdminLink('AdminOrders').'&id_order='.(int) $order->id.'&vieworder&hipay_err_refund='.$hipay_redirect_status.'#hipay');
                    die('');
                }

                $params = array("refundItems" => $refundItems,
                    "order" => $order->id, "transaction_reference" => $transactionReference,
                    "capture_refund_fee" => Tools::getValue('hipay_refund_fee'));
            } else {
                $params = array("refundItems" => "full", "order" => $order->id, "transaction_reference" => $transactionReference);
            }

            $this->apiHandler->handleRefund($params);
        }

        Tools::redirectAdmin($context->link->getAdminLink('AdminOrders').'&id_order='.(int) $order->id.'&vieworder&hipay_err_refund=ok#hipay');
    }
}
