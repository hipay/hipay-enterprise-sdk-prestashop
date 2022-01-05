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

require_once(dirname(__FILE__) . '/../../classes/helper/HipayHelper.php');

/**
 * Class Hipay_enterpriseExceptionModuleFrontController
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class Hipay_enterpriseExceptionModuleFrontController extends ModuleFrontController
{
    const PATH_TEMPLATE_PS_17 = '/views/templates/front/paymentReturn/ps17/exception-17.tpl';
    const PATH_TEMPLATE_PS_16 = 'paymentReturn/ps16/exception-16.tpl';


    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        $this->display_column_left = false;
        $this->display_column_right = false;
        parent::initContent();

        $this->context->smarty->assign(
            array(
                'status_error' => Tools::getValue('status_error')
            )
        );

        $path = (_PS_VERSION_ >= '1.7' ? 'module:' .
            $this->module->name .
            self::PATH_TEMPLATE_PS_17 : self::PATH_TEMPLATE_PS_16);
        $this->module->getLogs()->logInfos("# Exception payment");

        $context = Context::getContext();
        $cartId = Tools::getValue('orderid');
        $dbUtils = new HipayDBUtils($this->module);
        // --------------------------------------------------------------------------
        // check if data are sent by payment page
        if ($context->cart) {
            // load cart from context
            $objCart = $context->cart;
        } elseif (!$cartId) {
            // if not we retrieve the last cart
            $objCart = $dbUtils->getLastCartFromUser($context->customer->id);
        } else {
            // load cart
            $objCart = new Cart((int)$cartId);
        }

        if (_PS_VERSION_ >= '1.7.1.0') {
            $orderId = Order::getIdByCartId($objCart->id);
        } else {
            $orderId = Order::getOrderByCartId($objCart->id);
        }

        if ($orderId) {
            HipayHelper::changeOrderStatus(new Order($orderId), _PS_OS_ERROR_);
        }

        $this->setTemplate($path);
    }
}
