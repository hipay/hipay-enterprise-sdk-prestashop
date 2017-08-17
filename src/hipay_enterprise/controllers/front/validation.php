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

require_once(dirname(__FILE__).'/../../classes/helper/hipayDBQuery.php');
require_once(dirname(__FILE__).'/../../classes/helper/hipayHelper.php');
require_once(dirname(__FILE__).'/../../lib/vendor/autoload.php');

/**
 * Class Hipay_enterpriseValidationModuleFrontController
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link 	https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class Hipay_enterpriseValidationModuleFrontController extends ModuleFrontController
{

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {

        $context = Context::getContext();
        $cartId  = Tools::getValue('orderId');
        $transac = Tools::getValue('reference');
        $db      = new HipayDBQuery($this->module);
        // --------------------------------------------------------------------------
        // check if data are sent by payment page
        if (!$cartId) {
            // if not we retrieve the last cart
            $objCart = $db->getLastCartFromUser($context->customer->id);
        } else {
            // load cart
            $objCart = new Cart((int) $cartId);
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

        // If Gateway send payment product in redirection card brand
        $cardBrand      = Tools::getValue('cardbrand');
        $paymentProduct = Tools::getValue('product');

        $paymentProduct = HipayHelper::getPaymentProductName($cardBrand,
                $paymentProduct,
                $this->module);

        HipayHelper::unsetCart();

        // SQL LOCK
        //#################################################################

        $db->setSQLLockForCart($objCart->id);

        // load order for id_order
        $orderId = Order::getOrderByCartId($objCart->id);

        $customer = new Customer((int) $objCart->id_customer);

        if ($orderId && !empty($orderId) && $orderId > 0) {
            // load transaction by id_order
            $transaction = $db->getTransactionFromOrder($orderId);
        } else {
            $shopId  = $objCart->id_shop;
            $shop    = new Shop($shopId);
            // forced shop
            Shop::setContext(
                Shop::CONTEXT_SHOP,
                $objCart->id_shop
            );
            $this->module->validateOrder(
                (int) $objCart->id,
                Configuration::get('HIPAY_OS_PENDING'),
                (float) $objCart->getOrderTotal(true),
                $paymentProduct,
                'Order created by HiPay after success payment.',
                array(),
                $context->currency->id,
                false,
                $customer->secure_key,
                $shop
            );
            // get order id
            $orderId = $this->module->currentOrder;
        }

        $db->releaseSQLLock();
        // END SQL LOCK
        //#################################################################

        $transaction = isset($transac['transaction_id']) ? $transac['transaction_id'] : (int) $transac;

        Hook::exec(
            'displayHiPayAccepted',
            array('cart' => $objCart, "order_id" => $orderId)
        );

        $captureType = array(
            "order_id" => $orderId,
            "type" => $this->module->hipayConfigTool->getConfigHipay()["payment"]["global"]["capture_mode"]
        );

        $db->setOrderCaptureType($captureType);

        $params = http_build_query(
            array(
                'id_cart' => $objCart->id,
                'id_module' => $this->module->id,
                'id_order' => $orderId,
                'key' => $customer->secure_key,
            )
        );

        return Tools::redirect('index.php?controller=order-confirmation&'.$params);
    }
}