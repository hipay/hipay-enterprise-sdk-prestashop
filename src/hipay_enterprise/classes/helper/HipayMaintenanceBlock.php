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
require_once dirname(__FILE__).'/HipayHelper.php';
require_once dirname(__FILE__).'/dbquery/HipayDBMaintenance.php';

/**
 * handle credit card token (OneClik payment).
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 *
 * @see    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class HipayMaintenanceBlock
{
    private $module;
    private $context;
    private $order;
    private $cart;
    private $paymentProduct;
    private $captureOrRefundFromBo;
    private $basket;
    private $statusAvailableForCapture;
    private $statusNotAvailableForCapture;
    private $statusAvailableForRefund;
    private $statusNotAvailableForRefund;
    private $dbMaintenance;

    /**
     * HipayMaintenanceBlock constructor.
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function __construct($module, $orderID)
    {
        $this->module = $module;
        $this->context = Context::getContext();
        $this->dbMaintenance = new HipayDBMaintenance($this->module);
        $this->order = new Order($orderID);
        $this->cart = new Cart($this->order->id_cart);
        $this->paymentProduct = $this->dbMaintenance->getPaymentProductFromMessage($this->order->id);
        $this->captureOrRefundFromBo = $this->dbMaintenance->captureOrRefundFromBO($this->order->id);
        $this->basket = $this->dbMaintenance->getOrderBasket($this->order->id);

        $this->statusAvailableForCapture = [
            Configuration::get('HIPAY_OS_AUTHORIZED', null, null, 1),
            Configuration::get('HIPAY_OS_PARTIALLY_CAPTURED', null, null, 1),
            Configuration::get('HIPAY_OS_REFUNDED_PARTIALLY', null, null, 1),
        ];
        $this->statusNotAvailableForCapture = [
            _PS_OS_PAYMENT_,
            _PS_OS_ERROR_,
            _PS_OS_CANCELED_,
            Configuration::get('HIPAY_OS_EXPIRED', null, null, 1),
            Configuration::get('HIPAY_OS_REFUND_REQUESTED', null, null, 1),
            Configuration::get('HIPAY_OS_REFUNDED', null, null, 1),
        ];
        $this->statusAvailableForRefund = [
            _PS_OS_PAYMENT_,
            Configuration::get('HIPAY_OS_PARTIALLY_CAPTURED', null, null, 1),
            Configuration::get('HIPAY_OS_REFUNDED_PARTIALLY', null, null, 1),
        ];
        $this->statusNotAvailableForRefund = [
            _PS_OS_ERROR_,
            _PS_OS_CANCELED_,
            Configuration::get('HIPAY_OS_EXPIRED', null, null, 1),
            Configuration::get('HIPAY_OS_REFUND_REQUESTED', null, null, 1),
            Configuration::get('HIPAY_OS_REFUNDED', null, null, 1),
        ];
    }

    /**
     * @return mixed
     *
     * @throws PrestaShopException
     */
    public function displayBlock()
    {
        $this->context->smarty->assign(
            [
                'errorHipay' => $this->context->cookie->__get('hipay_errors'),
                'messagesHipay' => $this->context->cookie->__get('hipay_success'),
                'showMoto' => false,
                'showChallenge' => false,
                'refundRequestedOS' => $this->isRefundRequested(),
                'refundStartedFromBo' => false,
                'id_currency' => $this->order->id_currency,
                'orderId' => $this->order->id,
                'employeeId' => $this->context->employee->id,
            ]
        );

        $moto = $this->checkMoto();
        // Display MOTO form, no need to display anything else
        if (!$moto) {
            $challenge = $this->checkChallenged();
            // Display Challenge form, no need to display anything else
            if (!$challenge) {
                $this->checkCapture();
                $this->checkRefund();
            }
        }

        HipayHelper::resetMessagesHipay($this->context);

        return $this->module->display(
            $this->module->name,
            'views/templates/hook/maintenance.tpl'
        );
    }

    /**
     * Check if Order is awaiting for MOTO payment.
     *
     * @return bool
     *
     * @throws PrestaShopException
     */
    private function checkMoto()
    {
        if ($this->order->getCurrentState() == $this->getStatusId('HIPAY_OS_MOTO_PENDING') &&
            !$this->dbMaintenance->getTransactionReference($this->order->id)
        ) {
            $this->context->smarty->assign(
                [
                    'config_hipay' => $this->module->hipayConfigTool->getConfigHipay(),
                    'showMoto' => true,
                    'showCapture' => false,
                    'showRefund' => false,
                    'cartId' => $this->cart->id,
                    'motoLink' => $this->context->link->getAdminLink('AdminHiPayMoto'),
                ]
            );

            return true;
        }

        return false;
    }

    /**
     * Check if Order is awaiting for Challenge.
     *
     * @return bool
     *
     * @throws PrestaShopException
     */
    private function checkChallenged()
    {
        if ($this->order->current_state == $this->getStatusId('HIPAY_OS_CHALLENGED')) {
            $this->context->smarty->assign(
                [
                    'showCapture' => false,
                    'showRefund' => false,
                    'showChallenge' => true,
                    'challengeLink' => $this->context->link->getAdminLink('AdminHiPayChallenge'),
                ]
            );

            return true;
        }

        return false;
    }

    /**
     * Check if we must display refundForm.
     *
     * @return bool
     *
     * @throws PrestaShopException
     */
    private function checkRefund()
    {
        if ($this->paymentProduct) {
            $refundedItems = $this->dbMaintenance->getRefundedItems($this->order->id);
            $refundedFees = $this->dbMaintenance->feesAreRefunded($this->order->id);
            $refundedDiscounts = $this->dbMaintenance->discountsAreRefunded($this->order->id);
            $capturedFees = $this->dbMaintenance->feesAreCaptured($this->order->id);
            $capturedDiscounts = $this->dbMaintenance->discountsAreCaptured($this->order->id);
            $capturedWrapping = $this->dbMaintenance->wrappingIsCaptured($this->order->id);
            $refundedWrapping = $this->dbMaintenance->wrappingIsRefunded($this->order->id);

            if (
                $this->paymentMethodCanRefundOrCapture('refund')
                && !$this->statusNotAvailableForOperation('refund')
                && !$this->hasRefundStartedFromBO()
                && $this->statusAvailableForOperation('capture')
            ) {
                $discount = $this->getDiscount();

                $shippingFees = 0;
                if ($shipping = $this->order->getShipping() && !empty($shipping[0]['shipping_cost_tax_incl'])) {
                    $shippingFees = $shipping[0]['shipping_cost_tax_incl'];
                }

                $this->context->smarty->assign([
                        'showRefund' => !$this->module->hipayConfigTool->getAccountGlobal()['use_prestashop_refund_form'],
                        'manualCapture' => $this->isManualCapture(),
                        'stillToCapture' => $this->order->total_paid_tax_incl -
                            HipayHelper::getOrderPaymentAmount($this->order),
                        'alreadyCaptured' => $this->dbMaintenance->alreadyCaptured($this->order->id),
                        'refundableAmount' => HipayHelper::getOrderPaymentAmount($this->order) -
                            HipayHelper::getOrderPaymentAmount($this->order, true),
                        'refundedFees' => $refundedFees,
                        'refundLink' => $this->context->link->getAdminLink('AdminHiPayRefund'),
                        'basket' => $this->basket,
                        'refundedItems' => $refundedItems,
                        'tokenRefund' => Tools::getAdminTokenLite('AdminHiPayRefund'),
                        'partiallyRefunded' => $this->isPartiallyRefunded(
                            $refundedItems,
                            $refundedFees,
                            $this->isTotallyCaptured(),
                            $refundedDiscounts,
                            $refundedWrapping
                        ),
                        'totallyRefunded' => $this->isTotallyRefunded(),
                        'products' => $this->order->getProducts(),
                        'amountFees' => $shippingFees,
                        'shippingCost' => $this->order->total_shipping,
                        'discount' => $discount,
                        'capturedDiscounts' => $capturedDiscounts,
                        'refundedDiscounts' => $refundedDiscounts,
                        'capturedFees' => $capturedFees,
                        'orderId' => $this->order->id,
                        'cartId' => $this->cart->id,
                        'ajaxCalculatePrice' => $this->context->link->getAdminLink('AdminHiPayCalculatePrice'),
                        'wrappingGift' => (bool) $this->order->gift && $this->order->total_wrapping > 0,
                ]);

                if ((bool) $this->order->gift && $this->order->total_wrapping > 0) {
                    $this->context->smarty->assign(
                        [
                            'wrapping' => [
                                'value' => $this->order->total_wrapping,
                                'refunded' => $refundedWrapping,
                                'captured' => $capturedWrapping,
                            ],
                        ]
                    );
                }

                return true;
            }
        }

        $this->context->smarty->assign(['showRefund' => false]);

        return false;
    }

    /**
     * Check if we should display capture form.
     */
    private function checkCapture()
    {
        if ($this->paymentProduct) {
            if (
                $this->paymentMethodCanRefundOrCapture('capture')
                && !$this->statusNotAvailableForOperation('capture')
                && !($this->captureOrRefundFromBo && (null !== $this->basket))
                && $this->statusAvailableForOperation('capture')
                && !$this->isTotallyCaptured()
            ) {
                $capturedItems = $this->dbMaintenance->getCapturedItems($this->order->id);
                $capturedFees = $this->dbMaintenance->feesAreCaptured($this->order->id);
                $capturedDiscounts = $this->dbMaintenance->discountsAreCaptured($this->order->id);
                $capturedWrapping = $this->dbMaintenance->wrappingIsCaptured($this->order->id);

                $shippingFees = 0;
                if ($shipping = $this->order->getShipping() && !empty($shipping[0]['shipping_cost_tax_incl'])) {
                    $shippingFees = $shipping[0]['shipping_cost_tax_incl'];
                }

                $this->context->smarty->assign(
                    [
                        'showCapture' => true,
                        'stillToCapture' => $this->order->total_paid_tax_incl -
                            HipayHelper::getOrderPaymentAmount($this->order),
                        'manualCapture' => $this->isManualCapture(),
                        'capturedAmount' => HipayHelper::getOrderPaymentAmount($this->order),
                        'captureLink' => $this->context->link->getAdminLink('AdminHiPayCapture'),
                        'tokenCapture' => Tools::getAdminTokenLite('AdminHiPayCapture'),
                        'partiallyCaptured' => $this->isPartiallyCaptured(
                            $capturedItems,
                            $capturedFees,
                            $capturedDiscounts,
                            $capturedWrapping
                        ),
                        'capturedItems' => $capturedItems,
                        'capturedFees' => $capturedFees,
                        'capturedDiscounts' => $capturedDiscounts,
                        'basket' => $this->basket,
                        'products' => $this->order->getProducts(),
                        'amountFees' => $shippingFees,
                        'shippingCost' => $this->order->total_shipping,
                        'discount' => $this->getDiscount(),
                        'orderId' => $this->order->id,
                        'cartId' => $this->cart->id,
                        'ajaxCalculatePrice' => $this->context->link->getAdminLink('AdminHiPayCalculatePrice'),
                        'wrappingGift' => (bool) $this->order->gift && $this->order->total_wrapping > 0,
                        'canPartiallyCapture' => $this->paymentMethodCanRefundOrCapture('capturePartial')
                    ]
                );

                if ((bool) $this->order->gift && $this->order->total_wrapping > 0) {
                    $this->context->smarty->assign(
                        [
                            'wrapping' => [
                                'value' => $this->order->total_wrapping,
                                'captured' => $capturedWrapping,
                            ],
                        ]
                    );
                }

                return true;
            }
        }

        $this->context->smarty->assign(['showCapture' => false]);

        return false;
    }

    /**
     * Check if order is totally captured.
     *
     * @return bool
     */
    private function isTotallyCaptured()
    {
        $isPaid = $this->order->getHistory(
            $this->context->language->id,
            _PS_OS_PAYMENT_
        );

        $isOutOfStockPaid = $this->order->getHistory(
            $this->context->language->id,
            _PS_OS_OUTOFSTOCK_PAID_
        );

        return $isPaid || $isOutOfStockPaid;
    }

    /**
     * Check if order is totally refunded.
     *
     * @return bool
     */
    private function isTotallyRefunded()
    {
        $totallyRefunded = true;
        $refundedItems = $this->dbMaintenance->getRefundedItems($this->order->id);
        $refundedFees = $this->dbMaintenance->feesAreRefunded($this->order->id);
        $refundedDiscounts = $this->dbMaintenance->discountsAreRefunded($this->order->id);

        foreach ($this->order->getProducts() as $product) {
            $totallyRefunded &= (isset($refundedItems[$product['product_id']]) &&
                $refundedItems[$product['product_id']]['quantity'] >= $product['product_quantity']);
        }

        if (!$refundedFees || !$refundedDiscounts) {
            $totallyRefunded = false;
        }

        return $totallyRefunded;
    }

    /**
     * Check if order is partially refunded.
     *
     * @return bool
     */
    private function isPartiallyRefunded(
        $refundedItems,
        $refundedFees,
        $totallyCaptured,
        $refundedDiscounts,
        $refundedWrapping
    ) {
        if ($this->order->getCurrentState() == Configuration::get('HIPAY_OS_REFUNDED_PARTIALLY', null, null, 1) ||
            !empty($refundedItems) ||
            $refundedFees ||
            !$totallyCaptured ||
            $refundedDiscounts ||
            $refundedWrapping
        ) {
            return true;
        }

        return false;
    }

    /**
     * Check if Order is partially captured.
     *
     * @return bool
     */
    private function isPartiallyCaptured($capturedItems, $capturedFees, $capturedDiscounts, $capturedWrapping)
    {
        if ($this->order->getCurrentState() == Configuration::get('HIPAY_OS_PARTIALLY_CAPTURED', null, null, 1) ||
            !empty($capturedItems) ||
            $capturedFees ||
            $capturedDiscounts ||
            $capturedWrapping
        ) {
            return true;
        }

        return false;
    }

    /**
     * Check if Order was manually captured.
     *
     * @return bool
     */
    private function isManualCapture()
    {
        // challenged Order are always manually captured
        $isChallenged = $this->order->getHistory(
            $this->context->language->id,
            Configuration::get('HIPAY_OS_CHALLENGED', null, null, 1)
        );

        if (
            $this->dbMaintenance->isManualCapture($this->order->id)
            || (bool) $isChallenged
        ) {
            return true;
        }

        return false;
    }

    /**
     * Check in Order history if Operation is available for this Order.
     *
     * @return bool
     */
    private function statusAvailableForOperation($operation)
    {
        $available = false;
        $status = ('capture' === $operation) ? $this->statusAvailableForCapture : $this->statusAvailableForRefund;

        foreach ($status as $statusItem) {
            $available = $available || $this->order->getHistory(
                $this->context->language->id,
                $statusItem
            );
        }

        return $available;
    }

    /**
     * Check if current Order status allow us to perform the operation (capture or refund).
     *
     * @return bool
     */
    private function statusNotAvailableForOperation($operation)
    {
        $status = ('capture' === $operation) ? $this->statusNotAvailableForCapture : $this->statusNotAvailableForRefund;

        return in_array($this->order->getCurrentState(), $status);
    }

    /**
     * Check if payment method can refund or capture (HipayConfig).
     *
     * @return bool
     */
    private function paymentMethodCanRefundOrCapture($operation)
    {
        switch($operation) {
            case 'capture':
                $label  = 'canManualCapture';
                break;
            case 'capturePartial':
                $label  = 'canManualCapturePartially';
                break;
            default:
                $label  = 'canRefund';
                break;
        }

        if (
            (isset($this->module->hipayConfigTool->getLocalPayment()[$this->paymentProduct])
                && !(bool) $this->module->hipayConfigTool->getLocalPayment()[$this->paymentProduct][$label])
            ||
            (isset($this->module->hipayConfigTool->getPaymentCreditCard()[$this->paymentProduct])
                && !(bool) $this->module->hipayConfigTool->getPaymentCreditCard()[$this->paymentProduct][$label])
        ) {
            return false;
        }

        return true;
    }

    /**
     * Return status id from Prestashop configuration.
     *
     * @return string
     */
    private function getStatusId($statusName)
    {
        return Configuration::get($statusName, null, null, 1);
    }

    /**
     * Return discount from Order
     * All Order discounts are agglomerate in one discount for capture and refund.
     *
     * @return array
     */
    private function getDiscount()
    {
        $discounts = $this->order->getCartRules();

        $discount = [];

        if (!empty($discounts)) {
            foreach ($discounts as $disc) {
                $discount['name'][] = $disc['name'];
                $discount['value'] = (!isset($discount['value'])) ? $disc['value'] : $discount['value'] +
                    $disc['value'];
            }
            $discount['name'] = join('/', $discount['name']);
        }

        return $discount;
    }

    /**
     * Check if current order state is HIPAY_OS_REFUND_REQUESTED.
     *
     * @return bool
     */
    private function isRefundRequested()
    {
        return $this->order->getCurrentState() == Configuration::get('HIPAY_OS_REFUND_REQUESTED', null, null, 1);
    }

    /**
     * Check if refund has started from HiPay BO
     * if true it is not possible to pursue from prestashop BO.
     *
     * @return bool
     */
    private function hasRefundStartedFromBO()
    {
        if (!($this->captureOrRefundFromBo && (null !== $this->basket)) || !$this->isManualCapture()) {
            return false;
        }

        // if started from BO, displays an message to the BO User
        $this->context->smarty->assign(['refundStartedFromBo' => true]);

        return true;
    }
}
