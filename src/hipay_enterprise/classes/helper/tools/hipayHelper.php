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
class HipayHelper {

    /**
     * empty customer cart 
     * @return boolean
     */
    public static function unsetCart() {
        $context = Context::getContext();
        $cart = new Cart($context->cookie->id_cart);
        unset($context->cookie->id_cart, $cart, $context->cookie->checkedTOS);
        $context->cookie->check_cgv = false;
        $context->cookie->write();
        $context->cookie->update();
        return true;
    }

}
