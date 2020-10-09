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
        $cartId = Tools::getValue('orderId');
        $dbUtils = new HipayDBUtils($this->module);
        // --------------------------------------------------------------------------
        // check if data are sent by payment page
        if (!$cartId) {
            // if not we retrieve the last cart
            $objCart = $dbUtils->getLastCartFromUser($context->customer->id);
        } else {
            // load cart
            $objCart = new Cart((int)$cartId);
        }

        $token = Tools::getValue('token');

        //check request integrity
        if ($token != HipayHelper::getHipayToken($objCart->id)) {
            $this->module->getLogs()->logErrors("# Wrong token on payment validation");
            $redirectUrl = $context->link->getModuleLink(
                $this->module->name,
                'exception',
                array('status_error' => 405),
                true
            );
            Tools::redirect($redirectUrl);
        }

        if (_PS_VERSION_ >= '1.7.1.0') {
            $orderId = Order::getIdByCartId($objCart->id);
        } else {
            $orderId = Order::getOrderByCartId($objCart->id);
        }
        $customer = new Customer((int)$objCart->id_customer);

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

        $currentOrder = new Order($orderId);
        $currentOrder->payment = $paymentProductName;
        $currentOrder->save();

        $redirectParams = array(
            'id_cart' => $objCart->id,
            'id_module' => $this->module->id,
            'id_order' => $orderId,
            'key' => $customer->secure_key,
        );

        Tools::redirect('index.php?controller=order-confirmation&' . http_build_query($redirectParams));
    }
}
