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

require_once(dirname(__FILE__).'/../../classes/helper/HipayDBQuery.php');
require_once(dirname(__FILE__).'/../../classes/helper/HipayHelper.php');
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
                $this->module->name, 'exception', array('status_error' => 405), true
            );
            Tools::redirect($redirectUrl);
        }

        // SQL LOCK
        //#################################################################

        $db->setSQLLockForCart($objCart->id);

        // If Gateway send payment product in redirection card brand
        $cardBrand      = Tools::getValue('cardbrand');
        $paymentProduct = Tools::getValue('product');

        $paymentProduct = HipayHelper::getPaymentProductName($cardBrand, $paymentProduct, $this->module);

        HipayHelper::validateOrder($this->module, $context, $this->module->hipayConfigTool->getConfigHipay(), $db,
                                   $objCart, $paymentProduct);
    }
}