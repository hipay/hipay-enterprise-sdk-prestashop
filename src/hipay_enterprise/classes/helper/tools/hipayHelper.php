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
        $context                    = Context::getContext();
        $cart                       = new Cart($context->cookie->id_cart);
        unset($context->cookie->id_cart,
              $cart,
              $context->cookie->checkedTOS);
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
    $signature, $config, $fromNotification = false
    )
    {
        $passphrase = ($config["account"]["global"]["sandbox_mode"]) ? $config["account"]["sandbox"]["api_secret_passphrase_sandbox"]
                : $config["account"]["production"]["api_secret_passphrase_production"];

        if (empty($passphrase) && empty($signature)) {
            return true;
        }

        if ($fromNotification) {
            $rawPostData = Tools::file_get_contents("php://input");
            if ($signature == sha1($rawPostData.$passphrase)) {
                return true;
            }
            return false;
        }

        return false;
    }

    /**
     * Generate Product reference for basket
     * @param type $product
     * @return string
     */
    public static function getProductRef($product)
    {
        $parentProduct = new Product($product["id_product"]);

        if (!empty($product["reference"]) && $product["reference"] != $parentProduct->reference) {
            $reference = $product["reference"];
        } else if (!empty($product["reference"])) {
            $reference = $product["reference"]."-".HipayHelper::slugify($product["attributes_small"]);
        } else {
            $reference = $product["id_product"]."-".$product["id_product_attribute"]."-".HipayHelper::slugify($product["name"])."-".HipayHelper::slugify($product["attributes_small"]);
        }

        return $reference;
    }

    /**
     * Generate carrier product reference for basket
     * @param type $carrier
     * @return string
     */
    public static function getCarrierRef($carrier)
    {
        $reference = $carrier->id."-".HipayHelper::slugify($carrier->name);

        return $reference;
    }

    /**
     * Generate discount product reference for basket
     * @param type $discount
     * @return string
     */
    public static function getDiscountRef($discount)
    {
        if (!empty($discount["code"])) {
            $reference = $discount["code"];
        } else {
            $reference = $discount["id_cart_rule"]."-".HipayHelper::slugify($discount["name"]);
        }

        return $reference;
    }

    /**
     * Slugify text
     * @param type $text
     * @return string
     */
    public static function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('#[^\\pL\d]+#u',
                             '-',
                             $text);

        // trim
        $text = trim($text,
                     '-');

        // transliterate
        if (function_exists('iconv')) {
            $text = iconv('utf-8',
                          'us-ascii//TRANSLIT',
                          $text);
        }

        // lowercase
        $text = strtolower($text);

        // remove unwanted characters
        $text = preg_replace('#[^-\w]+#',
                             '',
                             $text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}