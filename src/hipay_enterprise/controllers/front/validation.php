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

require_once(dirname(__FILE__) . '/../../classes/helper/dbquery/HipayDBUtils.php');
require_once(dirname(__FILE__) . '/../../classes/helper/HipayHelper.php');
require_once(dirname(__FILE__) . '/../../classes/exceptions/PaymentProductNotFoundException.php');
require_once(dirname(__FILE__) . '/../../lib/vendor/autoload.php');

/**
 * Class Hipay_enterpriseValidationModuleFrontController
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class Hipay_enterpriseValidationModuleFrontController extends ModuleFrontController
{

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        $context = Context::getContext();
        $cartId = Tools::getValue('orderid');
        $dbUtils = new HipayDBUtils($this->module);
        // --------------------------------------------------------------------------
        // check if data are sent by payment page
        if ($context->cart) {
            $objCart = $context->cart;
            $this->module->getLogs()->logInfos("Cart $objCart->id loaded from context");
        } elseif (!$cartId) {
            // if not we retrieve the last cart
            $objCart = $dbUtils->getLastCartFromUser($context->customer->id);
            $this->module->getLogs()->logInfos("Last cart $objCart->id loaded from customer " . $context->customer->id);
        } else {
            // load cart
            $objCart = new Cart((int)$cartId);
            $this->module->getLogs()->logInfos("Cart $objCart->id loaded from orderId $cartId");
        }

        try {
            $paymentProduct = $this->module->hipayConfigTool->getPaymentProduct(Tools::getValue('product'));
        } catch (PaymentProductNotFoundException $e) {
            $paymentProduct = Tools::getValue('product');
        }

        $paymentProductName = HipayHelper::getPaymentProductName(
            $paymentProduct,
            $this->module,
            $context->language
        );

        $redirectParams = HipayHelper::validateOrder(
            $this->module,
            $this->context,
            $objCart,
            $paymentProductName
        );

        Hook::exec('displayHiPayAccepted', array('cart' => $objCart, "order_id" => $redirectParams['id_order']));
        Tools::redirect('index.php?controller=order-confirmation&' . http_build_query($redirectParams));
    }
}
