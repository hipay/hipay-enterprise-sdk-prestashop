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
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class HipayHelper
{

    /**
     * Clear every single merchant account data
     * @return boolean
     */
    public static function clearAccountData()
    {
        Configuration::deleteByName('HIPAY_CONFIG');
        return true;
    }

    public static function getPaymentProductName($cardBrand, $paymentProduct, $module, $language)
    {
        if (!$cardBrand) {
            if ($paymentProduct && $paymentProduct == 'credit_card') {
                $paymentProduct = $module->hipayConfigTool->getPaymentGlobal()["ccDisplayName"];
            } else if ($paymentProduct && isset($module->hipayConfigTool->getLocalPayment()[$paymentProduct])) {
                $config = $paymentProduct = $module->hipayConfigTool->getLocalPayment()[$paymentProduct];
                if (is_array($config["displayName"])) {
                    $paymentProduct = $config["displayName"][$language];
                } else {
                    $paymentProduct = $config["displayName"];
                }
            } elseif ($paymentProduct && isset($module->hipayConfigTool->getPaymentCreditCard()[$paymentProduct])) {
                $config = $paymentProduct = $module->hipayConfigTool->getPaymentCreditCard()[$paymentProduct];
                if (is_array($config["displayName"])) {
                    $paymentProduct = $config["displayName"][$language];
                } else {
                    $paymentProduct = $config["displayName"];
                }
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
        return $order->id . '-' . $operation . '-' . ($transactionAttempt + 1);
    }

    /**
     *
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
    public static function checkSignature($signature, $config, $fromNotification = false, $moto = false)
    {
        $passphrase = ($config["account"]["global"]["sandbox_mode"]) ? $config["account"]["sandbox"]["api_secret_passphrase_sandbox"]
            : $config["account"]["production"]["api_secret_passphrase_production"];
        $passphraseMoto = ($config["account"]["global"]["sandbox_mode"]) ? $config["account"]["sandbox"]["api_secret_passphrase_sandbox"]
            : $config["account"]["production"]["api_secret_passphrase_production"];

        if (empty($passphrase) && empty($signature)) {
            return true;
        }

        if ($fromNotification) {
            $rawPostData = Tools::file_get_contents("php://input");
            if ($signature == sha1($rawPostData . $passphrase) ||
                ($moto && $signature == sha1($rawPostData . $passphraseMoto))
            ) {
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
                $reference = $product["reference"] . "-" . HipayHelper::slugify($product["attributes_small"]);
            } else {
                // Product simple or virtual
                $reference = $product["reference"];
            }
        } else {
            $reference = $product["id_product"] .
                "-" .
                $product["id_product_attribute"] .
                "-" .
                HipayHelper::slugify($product["name"]);
            if (isset($product["attributes_small"])) {
                $reference .= "-" . HipayHelper::slugify($product["attributes_small"]);
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
        $reference = $carrier->id . "-" . HipayHelper::slugify($carrier->name);

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
            $reference = $discount["id_cart_rule"] . "-" . HipayHelper::slugify($discount["name"]);
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
        $text = preg_replace('#[^\\pL\d]+#u', '-', $text);

        // trim
        $text = trim($text, '-');

        // transliterate
        if (function_exists('iconv')) {
            $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        }

        // lowercase
        $text = Tools::strtolower($text);

        // remove unwanted characters
        $text = preg_replace('#[^-\w]+#', '', $text);

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
            $admin = explode(DIRECTORY_SEPARATOR, _PS_ADMIN_DIR_);
            $adminFolder = array_pop((array_slice($admin, -1)));
            $adminUrl = _PS_BASE_URL_ . __PS_BASE_URI__ . $adminFolder . '/';
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
        return md5(Tools::getToken($page) . $cartId);
    }

    /**
     * Generate unique admin token
     * @param type $cartId
     * @param type $page
     * @return type
     */
    public static function getHipayAdminToken($tab, $orderID)
    {
        return md5(Tools::getAdminTokenLite($tab) . $orderID);
    }

    /**
     * @param $context
     * @param $moduleInstance
     */
    public static function redirectToExceptionPage($context, $moduleInstance)
    {
        $redirectUrl404 = $context->link->getModuleLink(
            $moduleInstance->name,
            'exception',
            array('status_error' => 500),
            true
        );

        Tools::redirect($redirectUrl404);
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

        if (_PS_VERSION_ >= '1.7') {
            self::redirectToExceptionPage($context, $moduleInstance);
        }

        if ($cart) {
            $context->smarty->assign(
                array(
                    'status_error' => '404',
                    'status_error_oc' => '200',
                    'cart_id' => $cart->id,
                    'savedCC' => $savedCC,
                    'amount' => $cart->getOrderTotal(true, Cart::BOTH),
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

        return 'payment/ps16/paymentFormApi-16.tpl';
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

    /**
     * Get sorted and filtered available payment methods
     *
     * @param $module
     * @param $configHipay
     * @param $country
     * @param $currency
     * @param int $orderTotal
     * @return array
     */
    public static function getSortedActivatedPaymentByCountryAndCurrency(
        $module,
        $configHipay,
        $country,
        $currency,
        $orderTotal = 1,
        $address,
        $customer
    ) {
        $activatedCreditCard = array();
        $creditCards = self::getActivatedPaymentByCountryAndCurrency(
            $module,
            $configHipay,
            "credit_card",
            $country,
            $currency,
            $orderTotal
        );

        if (!empty($creditCards)) {
            $activatedCreditCard["credit_card"]["frontPosition"] = $configHipay["payment"]["global"]["ccFrontPosition"];
            $activatedCreditCard["credit_card"]["products"] = $creditCards;
        }

        $activatedLocalPayment = self::getActivatedPaymentByCountryAndCurrency(
            $module,
            $configHipay,
            "local_payment",
            $country,
            $currency,
            $orderTotal,
            $address,
            $customer
        );

        $paymentProducts = array_merge($activatedCreditCard, $activatedLocalPayment);

        uasort($paymentProducts, array("HipayHelper", "cmpPaymentProduct"));

        return $paymentProducts;
    }

    /**
     * return an array of payment methods (set in BO configuration) for the customer country and currency
     *
     * @param $module
     * @param $configHipay
     * @param $paymentMethodType
     * @param $country
     * @param $currency
     * @param int $orderTotal
     * @return array
     */
    public static function getActivatedPaymentByCountryAndCurrency(
        $module,
        $configHipay,
        $paymentMethodType,
        $country,
        $currency,
        $orderTotal = 1,
        $address = null,
        $customer = null
    ) {
        $context = Context::getContext();
        $activatedPayment = array();
        foreach ($configHipay["payment"][$paymentMethodType] as $name => $settings) {
            if ($settings["activated"] &&
                (empty($settings["countries"]) || in_array($country->iso_code, $settings["countries"])) &&
                (empty($settings["currencies"]) || in_array($currency->iso_code, $settings["currencies"])) &&
                $orderTotal >= $settings["minAmount"]["EUR"] &&
                ($orderTotal <= $settings["maxAmount"]["EUR"] || !$settings["maxAmount"]["EUR"])
            ) {
                if ($paymentMethodType == "local_payment") {
                    if (Configuration::get('PS_ROUND_TYPE') == Order::ROUND_LINE ||
                        Configuration::get('PS_ROUND_TYPE') == Order::ROUND_ITEM ||
                        !$settings["forceBasket"]
                    ) {
                        $activatedPayment[$name] = $settings;
                        $activatedPayment[$name]["link"] = $context->link->getModuleLink(
                            $module->name,
                            'redirectlocal',
                            array("method" => $name),
                            true
                        );
                        $activatedPayment[$name]['payment_button'] = $module->getPath() .
                            'views/img/' .
                            $settings["logo"];

                        $checkoutFieldsMandatory = isset($module->hipayConfigTool->getLocalPayment(
                            )[$name]["checkoutFieldsMandatory"]) ?
                            $module->hipayConfigTool->getLocalPayment()[$name]["checkoutFieldsMandatory"] : "";
                        $fieldMandatory = array();
                        if (!empty($checkoutFieldsMandatory)) {
                            foreach ($checkoutFieldsMandatory as $field) {
                                switch ($field) {
                                    case "phone":
                                        if (empty($address->{$field})) {
                                            $fieldMandatory[] = $module->l(
                                                'Please enter your phone number to use this payment method.'
                                            );
                                        } else if (!preg_match('"(0|\\+33|0033)[1-9][0-9]{8}"', $address->{$field})) {
                                            $fieldMandatory[] = $module->l('Please check the phone number entered.');
                                        }
                                        break;
                                    case "gender":
                                        if (empty($customer->id_gender)) {
                                            $fieldMandatory[] = $module->l(
                                                'Please inform your civility to use this method of payment.'
                                            );
                                        }
                                        break;
                                    default:
                                        $fieldMandatory[] = $module->l('Please check the information entered.');
                                        break;
                                }
                            }

                            $activatedPayment[$name]['errorMsg'] = $fieldMandatory;
                        }

                    }
                } else {
                    $activatedPayment[$name] = $settings;
                }
            }
        }
        return $activatedPayment;
    }

    /**
     * return well formatted authorize credit card payment methods
     *
     * @param $module
     * @param $configHipay
     * @param $deliveryCountry
     * @param $currency
     * @return string
     */
    public static function getCreditCardProductList($module, $configHipay, $deliveryCountry, $currency)
    {
        $creditCard = self::getActivatedPaymentByCountryAndCurrency(
            $module,
            $configHipay,
            "credit_card",
            $deliveryCountry,
            $currency
        );
        $productList = join(",", array_keys($creditCard));

        return $productList;
    }

    /**
     * Create order from successfull HiPay transaction
     * @param type $module
     * @param type $context
     * @param type $configHipay
     * @param type $db
     * @param type $cart
     * @param type $productName
     * @return type
     */
    public static function validateOrder($module, $context, $configHipay, $db, $cart, $productName)
    {
        $params = array();
        if (_PS_VERSION_ >= '1.7.1.0') {
            $orderId = Order::getIdByCartId($cart->id);
        } else {
            $orderId = Order::getOrderByCartId($cart->id);
        }

        $customer = new Customer((int)$cart->id_customer);

        if ($cart && (!$orderId || empty($orderId))) {
            $module->getLogs()->logInfos("## Validate order for cart $cart->id $orderId");

            HipayHelper::unsetCart();

            $shopId = $cart->id_shop;
            $shop = new Shop($shopId);
            // forced shop
            Shop::setContext(Shop::CONTEXT_SHOP, $cart->id_shop);

            $module->validateOrder(
                (int)$cart->id,
                Configuration::get('HIPAY_OS_PENDING'),
                (float)$cart->getOrderTotal(true),
                $productName,
                $module->l('Order created by HiPay after success payment.'),
                array(),
                $context->currency->id,
                false,
                $customer->secure_key,
                $shop
            );

            // get order id
            $orderId = $module->currentOrder;
            $db->releaseSQLLock('validateOrder');

            $captureType = array("order_id" => $orderId, "type" => $configHipay["payment"]["global"]["capture_mode"]);

            $db->setOrderCaptureType($captureType);

            Hook::exec('displayHiPayAccepted', array('cart' => $cart, "order_id" => $orderId));
        } else {
            $module->getLogs()->logInfos("## Validate order ( order exist  $orderId )");
            $db->releaseSQLLock("validateOrder ( order exist  $orderId )");
        }

        if ($customer) {
            $params = http_build_query(
                array(
                    'id_cart' => $cart->id,
                    'id_module' => $module->id,
                    'id_order' => $orderId,
                    'key' => $customer->secure_key,
                )
            );
        }

        return Tools::redirect('index.php?controller=order-confirmation&' . $params);
    }

    /**
     * sorting function for payment products
     * @param type $a
     * @param type $b
     * @return int
     */
    private static function cmpPaymentProduct($a, $b)
    {
        if ($a["frontPosition"] == $b["frontPosition"]) {
            return 0;
        }
        return ($a["frontPosition"] < $b["frontPosition"]) ? -1 : 1;
    }

    /**
     * Check if order has already been placed ( Without prestashop cache)
     *
     * @return bool result
     */
    public static function orderExists($cart_id)
    {
        if ($cart_id) {

            $result = (bool)Db::getInstance()->getValue(
                'SELECT count(*) FROM `' . _DB_PREFIX_ . 'orders` WHERE `id_cart` = ' . (int)$cart_id
            );

            return $result;
        }
        return false;
    }

    /**
     * Get a value from $_POST / $_GET
     * if unavailable, take a default value
     * Duplicate from Prestashop core, without anti-slashes handling
     *
     * @param string $key Value key
     * @param mixed $default_value (optional)
     * @return mixed Value
     */
    public static function getValue($key, $default_value = false)
    {
        if (!isset($key) || empty($key) || !is_string($key)) {
            return false;
        }

        $ret = (isset($_POST[$key]) ? $_POST[$key] : (isset($_GET[$key]) ? $_GET[$key] : $default_value));

        return $ret;
    }

}
