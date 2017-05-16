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
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

class HipayEnterpriseNew extends Hipay_enterprise {

    /**
     * Display new payment options
     * @param type $params
     * @return type
     */
    public function hipayPaymentOptions($params) {
        if (!$this->active) {
            return;
        }

        if (!$this->checkCurrency($params['cart'])) {
            return;
        }


        $payment_options = $this->hipayExternalPaymentOption($params);
        return $payment_options;
    }

    /**
     * Add CSS and JS in header
     * @param type $params
     */
    public function hookDisplayHeader($params) {
        $this->context->controller->addCSS(_MODULE_DIR_ . $this->name . '/views/css/card-js.min.css', 'all');
        $this->context->controller->addJS(_MODULE_DIR_ . $this->name . '/views/js/card-js.min.js', 'all');
        $this->context->controller->addJS(array(_MODULE_DIR_ . $this->name . '/views/js/devicefingerprint.js'));
    }

    /**
     * Display payment forms
     * @param type $params
     * @return PaymentOption
     */
    public function hipayExternalPaymentOption($params) {

        $address = new Address(intval($params['cart']->id_address_delivery));
        $country = new Country(intval($address->id_country));
        $currency = new Currency(intval($params['cart']->id_currency));

        // get activated card for customer currency and country
        $activatedCreditCard = $this->getActivatedCreditCardByCountryAndCurrency($country, $currency);

        $paymentOptions = array();

        if (!empty($activatedCreditCard)) {

            $this->context->smarty->assign(array(
                'module_dir' => $this->_path,
                'config_hipay' => $this->configHipay,
                'hipay_enterprise_tpl_dir' => _PS_MODULE_DIR_ . $this->name . '/views/templates/hook'
            ));

            $paymentForm = $this->fetch('module:' . $this->name . '/views/templates/hook/paymentForm17.tpl');
            $newOption = new PaymentOption();
            //TODO: translate call to action text
            $newOption->setCallToActionText("pay by card")
                    ->setForm($paymentForm)
                    ->setAction($this->context->link->getModuleLink($this->name, 'validation', array(), true))
            ;

            $paymentOptions[] = $newOption;
        }
        return $paymentOptions;
    }

    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);
        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }
        return false;
    }

}
