<?php

/**
 * HiPay Enterprise SDK Prestashop.
 *
 * 2017 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright 2017 HiPay
 * @license   https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 */

use PrestaShop\PrestaShop\Core\Domain\CartRule\ValueObject\GiftProduct;

/**
 * Helper class.
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 *
 * @see    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class HipayHelper
{
    /**
     * @var string
     */
    public const PRODUCTION = 'production';

    /**
     * @var string
     */
    public const PRODUCTION_MOTO = 'production_moto';

    /**
     * @var string
     */
    public const PRODUCTION_APPLE_PAY = 'production_apple_pay';

    /**
     * @var string
     */
    public const TEST = 'test';

    /**
     * @var string
     */
    public const TEST_MOTO = 'test_moto';

    /**
     * @var string
     */
    public const TEST_APPLE_PAY = 'test_apple_pay';

    /**
     * @var array
     */
    public static $platforms = [
        self::PRODUCTION,
        self::TEST,
        self::PRODUCTION_MOTO,
        self::TEST_MOTO,
        self::PRODUCTION_APPLE_PAY,
        self::TEST_APPLE_PAY,
    ];

    /**
     * Clear every single merchant account data.
     *
     * @return bool
     */
    public static function clearAccountData()
    {
        Configuration::deleteByName('HIPAY_CONFIG');

        return true;
    }

    public static function getPaymentProductName($paymentProduct, $module, $language)
    {
        if ('credit_card' == $paymentProduct) {
            if (isset($module->hipayConfigTool->getPaymentGlobal()['ccDisplayName'])) {
                if (isset($module->hipayConfigTool->getPaymentGlobal()['ccDisplayName'][$language->iso_code])) {
                    $paymentProductName = $module->hipayConfigTool->getPaymentGlobal()['ccDisplayName'][$language->iso_code];
                } else {
                    $paymentProductName = $module->hipayConfigTool->getPaymentGlobal()['ccDisplayName']['en'];
                }
            } else {
                $paymentProductName = $module->hipayConfigTool->getPaymentGlobal()['ccDisplayName'];
            }
        } elseif (is_array($paymentProduct['displayName'])) {
            if (isset($paymentProduct['displayName'][$language->iso_code])) {
                $paymentProductName = $paymentProduct['displayName'][$language->iso_code];
            } else {
                $paymentProductName = $paymentProduct['displayName']['en'];
            }
        } else {
            $paymentProductName = $paymentProduct['displayName'];
        }

        return $paymentProductName;
    }

    /**
     * @return string
     */
    public static function generateOperationId($order, $operation, $transactionAttempt)
    {
        return $order->id . '-' . $operation . '-' . ($transactionAttempt + 1);
    }

    /**
     * empty customer cart.
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function unsetCart($cart)
    {
        $context = Context::getContext();

        unset($context->cookie->id_cart, $cart, $context->cookie->checkedTOS);
        $context->cookie->check_cgv = false;
        $context->cookie->write();
        $context->cookie->update();

        return true;
    }

    /**
     * Check if HiPay server signature match post data + passphrase.
     *
     * @param type $signature
     * @param type $module           Module Instance
     * @param type $fromNotification
     *
     * @return bool
     */
    public static function checkSignature($module, $isMoto = false, $isApplePay = false)
    {
        $config = $module->hipayConfigTool->getConfigHipay();

        // Init passphrase and Environment for production
        $passphrase = $isMoto && HipayHelper::existCredentialForPlateform($module, self::PRODUCTION_MOTO) ?
            $config['account']['production']['api_moto_secret_passphrase_production']
            : $config['account']['production']['api_secret_passphrase_production'];

        $passphrase = $isApplePay && HipayHelper::existCredentialForPlateform($module, self::PRODUCTION_APPLE_PAY) ?
            $config['account']['production']['api_apple_pay_passphrase_production']
            : $passphrase;

        $environment = $isMoto && HipayHelper::existCredentialForPlateform($module, self::PRODUCTION_MOTO) ?
            self::PRODUCTION_MOTO : self::PRODUCTION;

        $environment = $isApplePay && HipayHelper::existCredentialForPlateform($module, self::PRODUCTION_APPLE_PAY) ?
            self::PRODUCTION_APPLE_PAY : $environment;

        // Get Environment and passphrase for sandbox
        if ($config['account']['global']['sandbox_mode']) {
            $environment = $isMoto && HipayHelper::existCredentialForPlateform($module, self::TEST_MOTO) ?
                self::TEST_MOTO : self::TEST;
            $environment = $isApplePay && HipayHelper::existCredentialForPlateform($module, self::TEST_APPLE_PAY) ?
                self::TEST_APPLE_PAY : $environment;

            $passphrase = $isMoto && HipayHelper::existCredentialForPlateform($module, self::TEST_MOTO) ?
                $config['account']['sandbox']['api_moto_secret_passphrase_sandbox']
                : $config['account']['sandbox']['api_secret_passphrase_sandbox'];
            $passphrase = $isApplePay && HipayHelper::existCredentialForPlateform($module, self::TEST_APPLE_PAY) ?
                $config['account']['sandbox']['api_apple_pay_passphrase_sandbox']
                : $passphrase;
        }

        // Validate Signature with Hash
        $hashAlgorithm = $config['account']['hash_algorithm'][$environment];

        $isValidSignature = HiPay\Fullservice\Helper\Signature::isValidHttpSignature($passphrase, $hashAlgorithm);
        if (
            !$isValidSignature
            && !HiPay\Fullservice\Helper\Signature::isSameHashAlgorithm($passphrase, $hashAlgorithm)
        ) {
            $module->getLogs()->logInfos(
                "# Signature is not valid. Hash is the same. Try to synchronize for {$environment}"
            );
            try {
                if (HipayHelper::existCredentialForPlateform($module, $environment)) {
                    $hashAlgorithmAccount = ApiCaller::getSecuritySettings($module, $environment);
                    if ($hashAlgorithm != $hashAlgorithmAccount->getHashingAlgorithm()) {
                        $configHash = $module->hipayConfigTool->getHashAlgorithm();
                        $configHash[$environment] = $hashAlgorithmAccount->getHashingAlgorithm();
                        $module->hipayConfigTool->setHashAlgorithm($configHash);
                        $module->getLogs()->logInfos("# Hash Algorithm is now synced for {$environment}");
                        $isValidSignature = HiPay\Fullservice\Helper\Signature::isValidHttpSignature(
                            $passphrase,
                            $hashAlgorithmAccount->getHashingAlgorithm()
                        );
                    }
                }
            } catch (Exception $e) {
                $module->getLogs()->logErrors(sprintf('Update hash failed for %s', $environment));
            }
        }

        return $isValidSignature;
    }

    /**
     * Test if credentials are filled for plateform ( If no exists then no synchronization ).
     *
     * @return bool True if Credentials are filled
     */
    public static function existCredentialForPlateform($module, $platform)
    {
        switch ($platform) {
            case self::PRODUCTION:
                $exist = !empty($module->hipayConfigTool->getAccountProduction()['api_username_production']);
                break;
            case self::TEST:
                $exist = !empty($module->hipayConfigTool->getAccountSandbox()['api_username_sandbox']);
                break;
            case self::PRODUCTION_MOTO:
                $exist = !empty($module->hipayConfigTool->getAccountProduction()['api_moto_username_production']);
                break;
            case self::TEST_MOTO:
                $exist = !empty($module->hipayConfigTool->getAccountSandbox()['api_moto_username_sandbox']);
                break;
            case self::TEST_APPLE_PAY:
                $exist = !empty($module->hipayConfigTool->getAccountSandbox()['api_apple_pay_username_sandbox']);
                break;
            case self::PRODUCTION_APPLE_PAY:
                $exist = !empty($module->hipayConfigTool->getAccountProduction()['api_apple_pay_username_production']);
                break;
            default:
                $exist = false;
                break;
        }

        return $exist;
    }

    /**
     * Return label from platform code.
     *
     * @return string Label for plateform
     */
    public static function getLabelForPlatform($platform)
    {
        switch ($platform) {
            case self::PRODUCTION:
                $label = 'Production';
                break;
            case self::TEST:
                $label = 'Test';
                break;
            case self::PRODUCTION_MOTO:
                $label = 'Production MO/TO';
                break;
            case self::TEST_MOTO:
                $label = 'Test MO/TO';
                break;
            case self::TEST_APPLE_PAY:
                $label = 'Test Apple Pay';
                break;
            case self::PRODUCTION_APPLE_PAY:
                $label = 'Production Apple Pay';
                break;
            default:
                $label = '';
                break;
        }

        return $label;
    }

    /**
     * Generate Product reference for basket.
     *
     * @param type $product
     *
     * @return string
     */
    public static function getProductRef($product)
    {
        if (!empty($product['reference'])) {
            if (isset($product['attributes_small'])) {
                // Product with declinaison
                $reference = $product['reference'] . '-' . HipayHelper::slugify($product['attributes_small']);
            } else {
                // Product simple or virtual
                $reference = $product['reference'];
            }
        } else {
            $reference = $product['id_product'] .
                '-' .
                $product['id_product_attribute'] .
                '-' .
                HipayHelper::slugify($product['name']);
            if (isset($product['attributes_small'])) {
                $reference .= '-' . HipayHelper::slugify($product['attributes_small']);
            }
        }

        return $reference;
    }

    /**
     * Generate carrier product reference for basket.
     *
     * @param type $carrier
     *
     * @return string
     */
    public static function getCarrierRef($carrier)
    {
        $reference = $carrier->id . '-' . HipayHelper::slugify($carrier->name);

        return $reference;
    }

    /**
     * Generate discount product reference for basket.
     *
     * @param type $discount
     *
     * @return string
     */
    public static function getDiscountRef($discount)
    {
        if (!empty($discount['code'])) {
            $reference = $discount['code'];
        } else {
            $reference = $discount['id_cart_rule'] . '-' . HipayHelper::slugify($discount['name']);
        }

        return $reference;
    }

    /**
     * Slugify text.
     *
     * @param type $text
     *
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
     * @return string
     */
    public static function getAdminUrl()
    {
        if (_PS_VERSION_ < '1.7' && _PS_VERSION_ >= '1.6') {
            $admin = explode(DIRECTORY_SEPARATOR, _PS_ADMIN_DIR_);
            $lastElement = array_slice($admin, -1);
            $adminFolder = array_pop($lastElement);
            $adminUrl = _PS_BASE_URL_ . __PS_BASE_URI__ . $adminFolder . '/';
        } else {
            $adminUrl = '';
        }

        return $adminUrl;
    }

    /**
     * Generate unique token.
     *
     * @param type $cartId
     * @param type $page
     *
     * @return type
     */
    public static function getHipayToken($cartId, $page = 'validation.php')
    {
        return md5(Tools::getToken($page) . $cartId);
    }

    /**
     * Generate unique admin token.
     *
     * @param type $cartId
     * @param type $page
     *
     * @return type
     */
    public static function getHipayAdminToken($tab, $orderID)
    {
        return md5(Tools::getAdminTokenLite($tab) . $orderID);
    }

    public static function redirectToExceptionPage($context, $moduleInstance)
    {
        $redirectUrl404 = $context->link->getModuleLink(
            $moduleInstance->name,
            'exception',
            ['status_error' => 500],
            true
        );

        Tools::redirect($redirectUrl404);
    }

    public static function transactionAlreadyProcessed($context, $moduleInstance)
    {
        self::regenerateCart($context, $context->cart);
        $redirectUrl404 = $context->link->getModuleLink(
            $moduleInstance->name,
            'exception',
            ['status_error' => 501],
            true
        );

        Tools::redirect($redirectUrl404);
    }

    /**
     *  Redirect customer in Error page.
     *
     * @param null $cart
     * @param null $savedCC
     *
     * @return string
     */
    public static function redirectToErrorPage($context, $moduleInstance, $cart = null, $savedCC = null)
    {
        if (_PS_VERSION_ >= '1.7') {
            self::redirectToExceptionPage($context, $moduleInstance);
        }

        if ($cart) {
            $context->smarty->assign(
                [
                    'HiPay_status_error' => '404',
                    'HiPay_status_error_oc' => '200',
                    'HiPay_cart_id' => $cart->id,
                    'HiPay_savedCC' => $savedCC,
                    'HiPay_amount' => $cart->getOrderTotal(true, Cart::BOTH),
                    'HiPay_confHipay' => $moduleInstance->hipayConfigTool->getConfigHipay(),
                ]
            );
        } else {
            $context->smarty->assign(
                [
                    'HiPay_status_error' => '404',
                    'HiPay_status_error_oc' => '200',
                    'HiPay_cart_id' => '',
                    'HiPay_savedCC' => $savedCC,
                    'HiPay_amount' => '',
                    'HiPay_confHipay' => $moduleInstance->hipayConfigTool->getConfigHipay(),
                ]
            );
        }

        return 'payment/ps16/paymentFormApi-16.tpl';
    }

    /**
     * Restore error messages in Session or cookie.
     *
     * @param type $context
     */
    public static function resetMessagesHipay($context)
    {
        $context->cookie->__set('hipay_errors', '');
        $context->cookie->__set('hipay_success', '');
    }

    /**
     * Get sorted and filtered available payment methods.
     *
     * @param int $orderTotal
     *
     * @return array
     */
    public static function getSortedActivatedPaymentByCountryAndCurrency(
        $module,
        $configHipay,
        $country,
        $currency,
        $address,
        $customer,
        $orderTotal = 1
    ) {
        $activatedCreditCard = [];
        $creditCards = self::getActivatedPaymentByCountryAndCurrency(
            $module,
            $configHipay,
            'credit_card',
            $country,
            $currency,
            $orderTotal
        );

        if (!empty($creditCards)) {
            $activatedCreditCard['credit_card']['frontPosition'] = $configHipay['payment']['global']['ccFrontPosition'];
            $activatedCreditCard['credit_card']['products'] = $creditCards;
        }

        $activatedLocalPayment = self::getActivatedPaymentByCountryAndCurrency(
            $module,
            $configHipay,
            'local_payment',
            $country,
            $currency,
            $orderTotal,
            $address,
            $customer
        );

        $paymentProducts = array_merge($activatedCreditCard, $activatedLocalPayment);

        uasort($paymentProducts, ['HipayHelper', 'cmpPaymentProduct']);

        return $paymentProducts;
    }

    /**
     * return an array of payment methods (set in BO configuration) for the customer country and currency.
     *
     * @param int $orderTotal
     *
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
        $activatedPayment = [];
        foreach ($configHipay['payment'][$paymentMethodType] as $name => $settings) {
            // Only show if the payment method is
            // - activated
            // - has no country or is available in the active country
            // - has no specific currency or is active in the active currency
            // - Has the right amount
            // Accepts this version of prestashop
            if (
                $settings['activated'] &&
                (empty($settings['countries']) || in_array($country->iso_code, $settings['countries'])) &&
                (empty($settings['currencies']) || in_array($currency->iso_code, $settings['currencies'])) &&
                self::isOrderTotalWithinLimits($module, $orderTotal, $settings) &&
                (empty($settings['minPrestashopVersion']) || ($settings['minPrestashopVersion'] <= _PS_VERSION_))
            ) {
                if ('local_payment' == $paymentMethodType) {
                    if (
                        Order::ROUND_LINE == Configuration::get('PS_ROUND_TYPE') ||
                        Order::ROUND_ITEM == Configuration::get('PS_ROUND_TYPE') ||
                        !$settings['basketRequired']
                    ) {
                        $activatedPayment[$name] = $settings;
                        $activatedPayment[$name]['link'] = $context->link->getModuleLink(
                            $module->name,
                            'redirectlocal',
                            ['method' => $name],
                            true
                        );

                        $activatedPayment[$name]['payment_button'] = $module->getPath() .
                            'views/img/' .
                            (isset($settings['logo']) ? $settings['logo'] : 'logo.png');

                        $checkoutFieldsMandatory = isset(
                            $module->hipayConfigTool->getLocalPayment()[$name]['checkoutFieldsMandatory']
                        ) ?
                            $module->hipayConfigTool->getLocalPayment()[$name]['checkoutFieldsMandatory'] : '';
                        $fieldMandatory = [];
                        if (!empty($checkoutFieldsMandatory)) {
                            foreach ($checkoutFieldsMandatory as $field) {
                                switch ($field) {
                                    case 'phone':
                                        break;
                                    case 'gender':
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
     * return well formatted authorize credit card payment methods.
     *
     * @return string
     */
    public static function getCreditCardProductList($module, $configHipay, $deliveryCountry, $currency)
    {
        $creditCard = self::getActivatedPaymentByCountryAndCurrency(
            $module,
            $configHipay,
            'credit_card',
            $deliveryCountry,
            $currency
        );
        $productList = join(',', array_keys($creditCard));

        return $productList;
    }

    /**
     * Create order from successfull HiPay transaction.
     *
     * @return array
     *
     * @throws PrestaShopException
     */
    public static function validateOrder($module, $context, $cart, $productName, $status = null)
    {
        $params = [];
        if (_PS_VERSION_ >= '1.7.1.0') {
            $orderId = Order::getIdByCartId($cart->id);
        } else {
            $orderId = Order::getOrderByCartId($cart->id);
        }

        $customer = new Customer((int) $cart->id_customer);

        if ($cart && (!$orderId || empty($orderId))) {
            if (!$status) {
                $status = Configuration::get('HIPAY_OS_PENDING');
            }

            $module->getLogs()->logInfos("## Validate order for cart $cart->id $orderId with status $status");

            HipayHelper::unsetCart($cart);

            $shopId = $cart->id_shop;
            $shop = new Shop($shopId);
            // forced shop
            Shop::setContext(Shop::CONTEXT_SHOP, $cart->id_shop);
            if (!self::orderExists((int) $cart->id)) {
                $module->validateOrder(
                    (int) $cart->id,
                    $status,
                    (float) $cart->getOrderTotal(true),
                    $productName,
                    $module->l('Order created by HiPay.'),
                    [],
                    $context->currency->id,
                    false,
                    $customer->secure_key,
                    $shop
                );
            } else {
                $module->getLogs()->logInfos('## Validate order ( cart ' . $cart->id . ' exist but order ' . $orderId . ' too )');
            }
        } else {
            $module->getLogs()->logInfos('## Validate order ( order ' . $orderId . ' already exist )');
        }

        if ($customer) {
            $params = [
                'id_cart' => $cart->id,
                'id_module' => $module->id,
                'id_order' => $orderId,
                'key' => $customer->secure_key,
            ];
        }

        return $params;
    }

    /**
     * Duplicates cart when payment is declined, so prestashop will keep the customer's cart alive.
     *
     * @return bool
     */
    public static function duplicateCart($currentCart)
    {
        $context = Context::getContext();
        $cart = new Cart($currentCart->id);
        $cartRules = $cart->getCartRules();
        $duplicationCart = $cart->duplicate();

        foreach ($cartRules as $rule) {
            $duplicationCart['cart']->addCartRule($rule['id_cart_rule']);
            // If the discount is a gift, you don't want to re-apply the discount to add another quantity.
            if (!empty($rule['gift_product']) && (int) $rule['gift_product'] > 0) {
                $duplicationCart['cart']->updateQty(-1, $rule['gift_product'], $rule['gift_product_attribute']);
            }
        }

        if ($duplicationCart['success']) {
            $context->cookie->id_cart = $duplicationCart['cart']->id;
            $context->cookie->write();
            $context->cookie->update();

            return true;
        }

        return false;
    }

    /**
     * sorting function for payment products.
     *
     * @param type $a
     * @param type $b
     *
     * @return int
     */
    private static function cmpPaymentProduct($a, $b)
    {
        if ($a['frontPosition'] == $b['frontPosition']) {
            return 0;
        }

        return ($a['frontPosition'] < $b['frontPosition']) ? -1 : 1;
    }

    /**
     * Check if order has already been placed ( Without prestashop cache).
     *
     * @param int $cart_id
     *
     * @return bool
     */
    public static function orderExists($cart_id)
    {
        if ($cart_id) {
            $result = (bool) Db::getInstance()->getValue(
                'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'orders` WHERE `id_cart` = ' . (int) $cart_id
            );

            return $result;
        }

        return false;
    }

    /**
     * Get a value from $_POST / $_GET
     * if unavailable, take a default value
     * Duplicate from Prestashop core, without anti-slashes handling.
     *
     * @param string $key           Value key
     * @param mixed  $default_value (optional)
     *
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

    /**
     * Calculate refunded or captured amount from Order Payments.
     *
     * @param Order $order
     * @param bool  $refund
     *
     * @return float|int
     */
    public static function getOrderPaymentAmount($order, $refund = false)
    {
        if ($refund) {
            $orderSlips = $order->getOrderSlipsCollection();
            $amount = 0;

            foreach ($orderSlips as $slip) {
                /**
                 * @var OrderSlip $slip
                 */
                if ('1' === $slip->order_slip_type) {
                    $amount += $slip->total_products_tax_incl;
                }
            }
        } else {
            $orderPayments = $order->getOrderPaymentCollection();
            $amount = 0;

            foreach ($orderPayments as $payment) {
                if ($payment->amount > 0) {
                    $amount += $payment->amount;
                }
            }
        }

        return abs($amount);
    }

    /**
     * change order status.
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function changeOrderStatus($order, $newState)
    {
        $orderHistory = new OrderHistory();
        $orderHistory->id_order = $order->id;
        $orderHistory->changeIdOrderState($newState, $order, true);

        $orderHistory->addWithemail(true);
    }

    /**
     * Retrieves cart pointed by the cookies OR last used cart for a customer.
     *
     * @return bool|Cart
     */
    public static function getCustomerCart($module)
    {
        $context = Context::getContext();

        $dbUtils = new HipayDBUtils($module);
        if ($context->cart) {
            // load cart from context
            $cart = $context->cart;
        } elseif (!$context->cookie->id_cart) {
            // if not we retrieve the last cart
            $cart = $dbUtils->getLastCartFromUser($context->customer->id);
        } else {
            // load cart
            $cart = new Cart($context->cookie->id_cart);
        }

        return $cart;
    }

    /**
     * Delete an orderSlip and its relations.
     *
     * @param OrderSlip $orderSlip
     *
     * @return bool
     */
    public static function deleteOrderSlip($orderSlip)
    {
        $result = true;

        // Delete all the details of the slip
        $result &= Db::getInstance()->delete(
            'order_slip_detail',
            'id_order_slip = ' . $orderSlip->id
        );

        // Delete de slip
        $result &= Db::getInstance()->delete(
            'order_slip',
            'id_order_slip = ' . $orderSlip->id
        );

        return $result;
    }

    /**
     * Checks if the order was fullfiled using the HiPay Gateway.
     *
     * @param Hipay_enterprise $module
     * @param Order            $order
     *
     * @return bool
     */
    public static function isHipayOrder($module, $order)
    {
        return $order->module === $module->name;
    }

    /**
     * Check if the order total is within the allowed limits for a payment method.
     *
     * @param Hipay_enterprise $module The HiPay module instance
     * @param float $orderTotal The total amount of the order
     * @param array $settings An array containing payment method settings
     * @return bool True if the order total is within limits, false otherwise
     * @throws Exception If there's an error in API communication for Alma products
     */
    public static function isOrderTotalWithinLimits($module, $orderTotal, $settings)
    {
        // Check for Alma products
        if (isset($settings["productCode"]) && stripos($settings["productCode"], 'alma') !== false) {
            try {
                $availablePaymentProducts = ApiCaller::getAvailablePaymentProduct($module, [
                    'payment_product' => $settings["productCode"],
                    'with_options' => true
                ]);

                foreach ($availablePaymentProducts as $product) {
                    if ($product->getCode() === $settings["productCode"]) {
                        $options = $product->getOptions();
                        $installments = substr($product->getCode(), -2, 1);
                        $minKey = "basketAmountMin{$installments}x";
                        $maxKey = "basketAmountMax{$installments}x";

                        if (isset($options[$minKey], $options[$maxKey])) {
                            return $orderTotal >= (float)$options[$minKey] && $orderTotal <= (float)$options[$maxKey];
                        }
                    }
                }

                return false;
            } catch (Exception $e) {
                $module->getLogs()->logError("Error fetching Alma payment product: " . $e->getMessage());
                throw $e;
            }
        }

        // For non-Alma products
        $minAmount = $settings['minAmount']['EUR'] ?? 0;
        $maxAmount = $settings['maxAmount']['EUR'] ?? false;

        $hasUpperLimit = $maxAmount && $maxAmount > 0;
        return $orderTotal >= $minAmount && (!$hasUpperLimit || $orderTotal <= $maxAmount);
    }

    /**
     * Saves a processed HiPay order in the database
     *
     * @param Hipay_enterprise $module
     * @param Cart $cart
     * @param string $hipayOrderId
     * @return bool
     *
     * @throws Exception
     */
    public static function saveHipayProcessedOrder($module, $context, $cart, $hipayOrderId)
    {
        try {
            $hipayDBUtils = new HipayDBUtils($module);
            $newCartId = self::regenerateCart($context, $cart) ?? null;
            if($newCartId) {
                if ($hipayDBUtils->insertProcessedOrder($cart->id, $newCartId->id, $hipayOrderId, $cart->getOrderTotal(true, Cart::BOTH) )) {
                    return true;
                }
            }

        } catch (Exception $e) {
            $module->getLogs()->logErrors("Error insert new order item in Hipay order process table" . $e->getMessage());
            throw $e;
        }

        return false;
    }

    /**
     * Retrieves the HiPay order ID associated with a cart
     *
     * @param Hipay_enterprise $module
     * @param Cart $cart
     * @return string|null
     *
     * @throws Exception
     */
    public static function getHipayProcessedOrderByCartId($module, $cart)
    {
        try {
            $hipayDBUtils = new HipayDBUtils($module);

            return $hipayDBUtils->getHipayOrderIdByCartId($cart->id)["hipay_order_id"] ?? null;
        } catch (Exception $e) {
            $module->getLogs()->logErrors("Error insert new order item in Hipay order process table" . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Requests transaction information for a specific order from the HiPay API
     *
     * @param Hipay_enterprise $module
     * @param string $orderId
     * @return array|null
     *
     * @throws Exception
     */
    public static function requestOrderTransactionInformation($module, $orderId)
    {
        return ApiCaller::requestOrderTransactionInformation($module, $orderId) ?? null;
    }

    /**
     * Retrieves the transaction reference for a given order ID
     *
     * @param Hipay_enterprise $module
     * @param string $orderId
     * @return string|null
     */
    public static function getTransactionReference($module, $orderId)
    {
        return ($transactions = self::requestOrderTransactionInformation($module, $orderId))
            ? $transactions[0]->getTransactionReference()
            : null;
    }

    /**
     * Creates a new cart for the current customer and assigns it to the context
     *
     * @param Context $context
     * @return bool
     * @throws PrestaShopException
     */
    public static function assignNewCart($context)
    {
        $newCart = new Cart();
        $newCart->id_customer = (int) $context->customer->id;
        $newCart->id_currency = (int) $context->currency->id;
        $newCart->id_lang = (int) $context->language->id;
        $newCart->save();

        $context->cookie->id_cart = (int) $newCart->id;
        $context->cart = $newCart;

        return true;
    }

    /**
     * Regenerates a new cart and transfers products from the old cart
     *
     * @param Context $context
     * @param Cart $oldCart
     * @return Cart|false
     */
    public static function regenerateCart($context, $oldCart)
    {
        try {
            if (!isset($context->currency->precision) || $context->currency->precision === null) {
                $context->currency = new Currency((int)$context->currency->id);
            }

            $newCart = new Cart();
            $newCart->id_shop = $oldCart->id_shop;
            $newCart->id_shop_group = $oldCart->id_shop_group;
            $newCart->id_currency = $oldCart->id_currency;
            $newCart->id_lang = $oldCart->id_lang;
            $newCart->id_customer = $oldCart->id_customer;
            $newCart->id_guest = $oldCart->id_guest;
            $newCart->id_carrier = $oldCart->id_carrier;
            $newCart->recyclable = $oldCart->recyclable;
            $newCart->gift = $oldCart->gift;
            $newCart->gift_message = $oldCart->gift_message;
            $newCart->mobile_theme = $oldCart->mobile_theme;

            if (!$newCart->save()) {
                return false;
            }

            try {
                $oldProducts = $oldCart->getProducts();

                if (is_array($oldProducts)) {
                    foreach ($oldProducts as $product) {
                        $newCart->updateQty(
                            $product['quantity'],
                            $product['id_product'],
                            $product['id_product_attribute'],
                            isset($product['id_customization']) ? $product['id_customization'] : null,
                            'up',
                            0,
                            null,
                            false
                        );
                    }
                }

                $cartRules = $oldCart->getCartRules();
                if (is_array($cartRules)) {
                    foreach ($cartRules as $rule) {
                        $newCart->addCartRule($rule['id_cart_rule']);
                    }
                }
            } catch (Exception $e) {

                PrestaShopLogger::addLog(
                    'HiPay cart duplication error: ' . $e->getMessage(),
                    3,
                    null,
                    'Cart',
                    (int)$newCart->id
                );
            }

            $context->cart = $newCart;
            $context->cookie->id_cart = (int)$newCart->id;
            $context->cookie->write();

            return $newCart;
        } catch (Exception $e) {
            PrestaShopLogger::addLog(
                'HiPay cart regeneration failed: ' . $e->getMessage(),
                3,
                null,
                'Cart',
                (int)$oldCart->id
            );
            return false;
        }
    }

    /**
     * Handles cart protection and regeneration for HiPay transactions
     *
     * @param Hipay_enterprise $module
     * @param Context $context
     * @param array $params
     * @return bool
     */
    public static function handleCartProtection($module, $context, $params)
    {
        try {
            if (!isset($params['controller_class'])) {
                return true;
            }

            $controller = $params['controller_class'];
            if (!in_array($controller, ['CartController', 'OrderController'])) {
                return true;
            }

            if (!isset($context->currency) || $context->currency === null) {
                $idCurrency = (int)Configuration::get('PS_CURRENCY_DEFAULT');
                $context->currency = new Currency($idCurrency);

                if (!Validate::isLoadedObject($context->currency)) {
                    $module->getLogs()->logErrors("Could not load default currency");
                    return false;
                }
            }
            else if (!isset($context->currency->precision)) {
                $context->currency = new Currency((int)$context->currency->id);
            }

            if (!$context->cart || !Validate::isLoadedObject($context->cart)) {
                if (isset($context->cookie->id_cart) && $context->cookie->id_cart) {
                    $cart = new Cart((int)$context->cookie->id_cart);
                    if (Validate::isLoadedObject($cart) &&
                        (!isset($context->customer) || $cart->id_customer == $context->customer->id)) {
                        $context->cart = $cart;
                    }
                }
            }
            if (isset($context->cart) && Validate::isLoadedObject($context->cart)) {
                $hipayOrderId = self::getHipayProcessedOrderByCartId($module, $context->cart);
                if ($hipayOrderId) {
                    $transactionReference = self::getTransactionReference($module, $hipayOrderId);
                    if ($transactionReference !== null) {
                        $action = Tools::getValue('action', '');

                        // Detect cart modification attempts
                        $isCartModification = (
                            $controller === 'CartController' &&
                            in_array($action, ['update', 'add', 'delete']) ||
                            Tools::isSubmit('add') ||
                            Tools::isSubmit('update') ||
                            Tools::isSubmit('delete')
                        );

                        if ($isCartModification) {
                            // Duplicate the cart before modifications apply
                            if (self::regenerateCart($context, $context->cart)) {
                                $errorMessage = $module->l(
                                    'This cart cannot be modified because it has a pending payment transaction. 
                                    Please refresh the page and try again.'
                                );

                                // Check if this is an AJAX request
                                $isAjax = Tools::getValue('ajax') ||
                                    (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                                        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

                                if ($isAjax) {
                                    header('Content-Type: application/json');
                                    die(json_encode([
                                        'hasError' => true,
                                        'errors' => [$errorMessage],
                                        'success' => false
                                    ]));
                                } else {
                                    $context->controller->errors[] = $errorMessage;
                                    Tools::redirect($context->link->getPageLink('cart'));
                                }
                            } else {
                                $module->getLogs()->logErrors("Failed to duplicate cart #{$context->cart->id}");
                                try {
                                    $newCart = new Cart();
                                    $newCart->id_shop = $context->shop->id;
                                    $newCart->id_shop_group = $context->shop->id_shop_group;
                                    $newCart->id_currency = $context->currency->id;
                                    $newCart->id_lang = $context->language->id;
                                    if (isset($context->customer) && $context->customer->id) {
                                        $newCart->id_customer = $context->customer->id;
                                    }

                                    if ($newCart->save()) {
                                        $context->cart = $newCart;
                                        $context->cookie->id_cart = (int)$newCart->id;
                                        $context->cookie->write();
                                        Tools::redirect($context->link->getPageLink('cart'));
                                    }
                                } catch (Exception $e) {
                                    $module->getLogs()->logErrors("Failed to create fallback cart: " . $e->getMessage());
                                }
                            }
                        }
                    }
                }
            }

            return true;
        } catch (Exception $e) {
            $module->getLogs()->logErrors("Error in handleCartProtection: " . $e->getMessage());
            if (!isset($context->cart) || !Validate::isLoadedObject($context->cart)) {
                try {
                    if (!isset($context->currency) || !Validate::isLoadedObject($context->currency)) {
                        $idCurrency = (int)Configuration::get('PS_CURRENCY_DEFAULT');
                        $context->currency = new Currency($idCurrency);
                    }
                    if (Validate::isLoadedObject($context->currency)) {
                        $newCart = new Cart();
                        $newCart->id_shop = $context->shop->id;
                        $newCart->id_shop_group = $context->shop->id_shop_group;
                        $newCart->id_currency = $context->currency->id;
                        $newCart->id_lang = $context->language->id;
                        if (isset($context->customer) && $context->customer->id) {
                            $newCart->id_customer = $context->customer->id;
                        }
                        $newCart->save();

                        $context->cart = $newCart;
                        $context->cookie->id_cart = (int)$newCart->id;
                        $context->cookie->write();
                    }
                } catch (Exception $cartException) {
                    $module->getLogs()->logErrors("Critical error creating recovery cart: " . $cartException->getMessage());
                }
            }

            return false;
        }
    }
}