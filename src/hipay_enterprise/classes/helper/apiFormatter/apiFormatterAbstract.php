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
require_once(_PS_ROOT_DIR_ . _MODULE_DIR_ . 'hipay_enterprise/lib/vendor/autoload.php');
require_once(_PS_ROOT_DIR_ . _MODULE_DIR_ . 'hipay_enterprise/classes/helper/apiFormatter/apiFormatterInterface.php');

use \HiPay\Fullservice\Enum\Customer\Gender as Gender;

abstract class apiFormatterAbstract implements apiFormatterInterface {

    protected $module;

    public function __construct($module) {
        $this->module = $module;
        $this->context = Context::getContext();
        $this->cart = $this->context->cart;
        $this->configHipay = $this->module->hipayConfigTool->getConfigHipay();
        $this->customer = new Customer((int) $this->cart->id_customer);
        $this->store = new Store((int) $this->cart->id_shop);
    }

    protected function getGender($idGender = NULL) {
        // Gender of the customer (M=male, F=female, U=unknown).
        $gender = Gender::UNKNOWN;

        if ($idGender == NULL)
            $gender = 'U';
        switch ($idGender) {
            case '1' :
                $gender = Gender::MALE;
                break;
            case '2' :
                $gender = Gender::FEMALE;
                break;
            default :
                $gender = Gender::UNKNOWN;
                break;
        }

        return $gender;
    }

    abstract public function generate();

    abstract protected function mapRequest(&$request);
}
