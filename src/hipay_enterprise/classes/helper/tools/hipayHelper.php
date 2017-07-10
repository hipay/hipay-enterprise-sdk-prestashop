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

class HipayHelper
{

    /**
     * empty customer cart
     * @return boolean
     */
    public static function unsetCart()
    {
        $context = Context::getContext();
        $cart = new Cart($context->cookie->id_cart);
        unset($context->cookie->id_cart, $cart, $context->cookie->checkedTOS);
        $context->cookie->check_cgv = false;
        $context->cookie->write();
        $context->cookie->update();
        return true;
    }

    /**
     * Check if hipay server signature match post data + passphrase
     * @param type $signature
     * @param type $config
     * @param type $fromNotification
     * @return boolean
     */
    public static function checkSignature(
        $signature,
        $config,
        $fromNotification = false
    ) {
        $passphrase = ($config["account"]["global"]["sandbox_mode"]) ? $config["account"]["sandbox"]["api_secret_passphrase_sandbox"]
            : $config["account"]["production"]["api_secret_passphrase_production"];

        if (empty($passphrase) && empty($signature)) {
            return true;
        }

        if ($fromNotification) {
            $rawPostData = Tools::file_get_contents("php://input");
            if ($signature == sha1($rawPostData . $passphrase)) {
                return true;
            }
            return false;
        }

        return false;
    }
}
