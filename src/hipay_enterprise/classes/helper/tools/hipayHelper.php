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

/**
 * Helper class
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link 	https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class HipayHelper
{

    public static function getPaymentProductName($cardBrand, $paymentProduct, $module)
    {
        if (!$cardBrand) {
            if ($paymentProduct && $paymentProduct == 'credit_card') {
                $paymentProduct = $module->hipayConfigTool->getConfigHipay()["payment"]["global"]["ccDisplayName"];
            } else if ($paymentProduct && isset($module->hipayConfigTool->getConfigHipay()["payment"]["local_payment"][$paymentProduct])) {
                $paymentProduct = $module->hipayConfigTool->getConfigHipay()["payment"]["local_payment"][$paymentProduct]["displayName"];
            } elseif ($paymentProduct && isset($module->hipayConfigTool->getConfigHipay()["payment"]["credit_card"][$paymentProduct])) {
                $paymentProduct = $module->hipayConfigTool->getConfigHipay()["payment"]["credit_card"][$paymentProduct]["displayName"];
            }
        } else {
            $paymentProduct = Tools::ucfirst(Tools::strtolower($cardBrand));
        }

        return $paymentProduct;
    }

    /**
     *
     * @param type $order
     * @param type $operation
     * @param type $maintenanceData
     * @return type
     */
    public static function generateOperationId($order, $operation, $transactionAttempt)
    {
        return $order->id.'-'.$operation.'-'.($transactionAttempt + 1);
    }

    /**
     *
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
    $signature, $config, $fromNotification = false, $moto = false
    )
    {
        $passphrase     = ($config["account"]["global"]["sandbox_mode"]) ? $config["account"]["sandbox"]["api_secret_passphrase_sandbox"]
                : $config["account"]["production"]["api_secret_passphrase_production"];
        $passphraseMoto = ($config["account"]["global"]["sandbox_mode"]) ? $config["account"]["sandbox"]["api_secret_passphrase_sandbox"]
                : $config["account"]["production"]["api_secret_passphrase_production"];

        if (empty($passphrase) && empty($signature)) {
            return true;
        }

        if ($fromNotification) {
            $rawPostData = Tools::file_get_contents("php://input");
            if ($signature == sha1($rawPostData.$passphrase) || ($moto && $signature == sha1($rawPostData.$passphraseMoto))) {
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
        if (!empty($product["reference"])) {
            if (isset($product["attributes_small"])) {
                // Product with declinaison
                $reference = $product["reference"]."-".HipayHelper::slugify($product["attributes_small"]);
            } else {
                // Product simple or virtual
                $reference = $product["reference"];
            }
        } else {
            $reference = $product["id_product"]."-".$product["id_product_attribute"]."-".HipayHelper::slugify($product["name"]);
            if (isset($product["attributes_small"])) {
                $reference .= "-".HipayHelper::slugify($product["attributes_small"]);
            }
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
        $text = Tools::strtolower($text);

        // remove unwanted characters
        $text = preg_replace('#[^-\w]+#',
            '',
            $text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }

    /**
     *
     * @return string
     */
    public static function getAdminUrl()
    {
        if (_PS_VERSION_ < '1.7' && _PS_VERSION_ >= '1.6') {
            $admin       = explode(DIRECTORY_SEPARATOR,
                _PS_ADMIN_DIR_);
            $adminFolder = array_pop((array_slice($admin,
                    -1)));
            $adminUrl    = _PS_BASE_URL_.__PS_BASE_URI__.$adminFolder.'/';
        } else {
            $adminUrl = '';
        }
        return $adminUrl;
    }

    /**
     * Generate unique token
     * @param type $cartId
     * @param type $page
     * @return type
     */
    public static function getHipayToken($cartId, $page = 'validation.php')
    {
        return md5(Tools::getToken($page).$cartId);
    }

    /**
     * Generate unique admin token
     * @param type $cartId
     * @param type $page
     * @return type
     */
    public static function getHipayAdminToken($tab, $orderID)
    {
        return md5(Tools::getAdminTokenLite($tab).$orderID);
    }

    /**
     *
     *  Redirect customer in Error page
     *
     * @param $context
     * @param $moduleInstance
     * @param null $cart
     * @param null $savedCC
     * @return string
     */
    public static function redirectToErrorPage($context, $moduleInstance, $cart = null, $savedCC = null)
    {
        $redirectUrl404 = $context->link->getModuleLink(
            $moduleInstance->name,
            'exception',
            array('status_error' => 500),
            true
        );

        if (_PS_VERSION_ >= '1.7') {
            Tools::redirect($redirectUrl404);
        }

        if ($cart) {
            $context->smarty->assign(
                array(
                    'status_error' => '404',
                    'status_error_oc' => '200',
                    'cart_id' => $cart->id,
                    'savedCC' => $savedCC,
                    'amount' => $cart->getOrderTotal(
                        true,
                        Cart::BOTH
                    ),
                    'confHipay' => $moduleInstance->hipayConfigTool->getConfigHipay()
                )
            );
        } else {
            $context->smarty->assign(
                array(
                    'status_error' => '404',
                    'status_error_oc' => '200',
                    'cart_id' => '',
                    'savedCC' => $savedCC,
                    'amount' => '',
                    'confHipay' => $moduleInstance->hipayConfigTool->getConfigHipay()
                )
            );
        }

        return 'paymentFormApi16.tpl';
    }

    /**
     * Restore error messages in Session or cookie
     * 
     * @param type $context
     */
    public static function resetMessagesHipay($context)
    {
        $context->cookie->__set('hipay_errors', '');
        $context->cookie->__set('hipay_success', '');
    }
}