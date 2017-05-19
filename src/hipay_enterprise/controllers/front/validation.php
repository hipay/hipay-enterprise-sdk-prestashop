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
require_once(_PS_ROOT_DIR_ . _MODULE_DIR_ . 'hipay_enterprise/classes/helper/tools/hipayDBQuery.php');

class Hipay_enterpriseValidationModuleFrontController extends ModuleFrontController {

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess() {
        $this->unsetCart();

        $cartId = Tools::getValue('orderId');
        $transac = Tools::getValue('reference');
        $context = Context::getContext();
        $db = new HipayDBQuery($this->module);
        // --------------------------------------------------------------------------
        // check if data are sent by payment page
        if (!$cartId) {
            // if not we retrieve the last cart
            $objCart = $db->getLastCartFromUser($context->customer->id);
        } else {
            // load cart
            $objCart = new Cart((int) $cartId);
        }

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
            $shopId = $objCart->id_shop;
            $shop = new Shop($shopId);
            // forced shop
            Shop::setContext(Shop::CONTEXT_SHOP, $objCart->id_shop);
            $this->module->validateOrder(
                    (int) $objCart->id, Configuration::get('HIPAY_OS_PENDING'), (float) $objCart->getOrderTotal(true), $this->module->displayName, 'Order created by HiPay after success payment.', array(), $context->currency->id, false, $customer->secure_key, $shop
            );
            // get order id
            $orderId = $this->module->currentOrder;
        }

        $sql = 'commit;';
        if (!Db::getInstance()->execute($sql)) {
            HipayLogger::addLog('Bad LockSQL initiated', 'hipay for id_cart = ' . $objCart->id);
        }
        // END SQL LOCK 
        //#################################################################

        $transaction = isset($transac['transaction_id']) ? $transac['transaction_id'] : (int) $transac;

        Hook::exec('displayHiPayAccepted', array('cart' => $objCart, "order_id" => $orderId));

        $params = http_build_query([
            'id_cart' => $objCart->id,
            'id_module' => $this->module->id,
            'id_order' => $orderId,
             'key' => $customer->secure_key,
        ]);

        return Tools::redirect('index.php?controller=order-confirmation&' . $params);
    }

    /**
     * 
     * @return boolean
     */
    public function unsetCart() {
        $context = Context::getContext();
        $cart = new Cart($context->cookie->id_cart);
        unset($context->cookie->id_cart, $cart, $context->cookie->checkedTOS);
        $context->cookie->check_cgv = false;
        $context->cookie->write();
        $context->cookie->update();
        return true;
    }

}

require_once(_PS_ROOT_DIR_ . _MODULE_DIR_ . 'hipay_enterprise/lib/vendor/autoload.php');
