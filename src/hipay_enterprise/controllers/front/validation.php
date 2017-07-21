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

require_once(dirname(__FILE__) . '/../../classes/helper/tools/hipayDBQuery.php');
require_once(dirname(__FILE__) . '/../../classes/helper/tools/hipayHelper.php');
require_once(dirname(__FILE__) . '/../../lib/vendor/autoload.php');

class Hipay_enterpriseValidationModuleFrontController extends ModuleFrontController
{

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        $paymentProduct = Tools::getValue('pp');

        if($paymentProduct && $paymentProduct == 'credit_card'){
            $paymentProduct = $this->module->hipayConfigTool->getConfigHipay()["payment"]["global"]["ccDisplayName"];
        }elseif($paymentProduct && isset($this->module->hipayConfigTool->getConfigHipay()["payment"]["local_payment"][$paymentProduct])){
            $paymentProduct = $this->module->hipayConfigTool->getConfigHipay()["payment"]["local_payment"][$paymentProduct]["displayName"];
        }elseif($paymentProduct && isset($this->module->hipayConfigTool->getConfigHipay()["payment"]["credit_card"][$paymentProduct])){
            $paymentProduct = $this->module->hipayConfigTool->getConfigHipay()["payment"]["credit_card"][$paymentProduct]["displayName"];
        }else{
            $paymentProduct = "HiPay Enterprise";
        }
        
        HipayHelper::unsetCart();

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
            $objCart = new Cart((int)$cartId);
        }

        // SQL LOCK
        //#################################################################

        $db->setSQLLockForCart($objCart->id);

        // load order for id_order
        $orderId = Order::getOrderByCartId($objCart->id);

        $customer = new Customer((int)$objCart->id_customer);

        if ($orderId && !empty($orderId) && $orderId > 0) {
            // load transaction by id_order
            $transaction = $db->getTransactionFromOrder($orderId);
        } else {
            $shopId = $objCart->id_shop;
            $shop = new Shop($shopId);
            // forced shop
            Shop::setContext(
                Shop::CONTEXT_SHOP,
                $objCart->id_shop
            );
            $this->module->validateOrder(
                (int)$objCart->id,
                Configuration::get('HIPAY_OS_PENDING'),
                (float)$objCart->getOrderTotal(true),
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

        $transaction = isset($transac['transaction_id']) ? $transac['transaction_id'] : (int)$transac;

        Hook::exec(
            'displayHiPayAccepted',
            array('cart' => $objCart, "order_id" => $orderId)
        );

        $params = http_build_query(
            array(
                'id_cart' => $objCart->id,
                'id_module' => $this->module->id,
                'id_order' => $orderId,
                'key' => $customer->secure_key,
            )
        );

        return Tools::redirect('index.php?controller=order-confirmation&' . $params);
    }
}
