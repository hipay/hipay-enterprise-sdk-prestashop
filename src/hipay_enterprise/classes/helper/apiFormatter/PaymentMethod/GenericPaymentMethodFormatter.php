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
require_once(dirname(__FILE__) . '/../../../../lib/vendor/autoload.php');
require_once(dirname(__FILE__) . '/../ApiFormatterAbstract.php');

class GenericPaymentMethodFormatter extends ApiFormatterAbstract {

    private $params;

    public function __construct($module, $params) {
        parent::__construct($module);
        $this->params = $params;
    }

    /**
     * return mapped customer card payment informations
     * @return \HiPay\Fullservice\Gateway\Request\PaymentMethod\CardTokenPaymentMethod
     */
    public function generate() {

        $PMRequest = null;

        if (!empty($this->configHipay["payment"]["local_payment"][$this->params["method"]]["additionalFields"])) {
            $PMRequest = new $this->configHipay["payment"]["local_payment"][$this->params["method"]]["additionalFields"]["sdkClass"]();

            $this->mapRequest($PMRequest);
        }
        return $PMRequest;
    }

    /**
     * 
     * @param \HiPay\Fullservice\Gateway\Request\PaymentMethod\CardTokenPaymentMethod $cardTokenRequest
     */
    protected function mapRequest(&$PMRequest) {

        $attributes = get_object_vars($PMRequest);

        foreach ($attributes as $attr => $value) {
            if (isset($this->configHipay["payment"]["local_payment"][$this->params["method"]]["additionalFields"]["defaultFieldsValue"][$attr])) {
                $PMRequest->{$attr} = $this->configHipay["payment"]["local_payment"][$this->params["method"]]["additionalFields"]["defaultFieldsValue"][$attr];
            } else if (isset($this->params[$attr])) {
                if ($this->configHipay["payment"]["local_payment"][$this->params["method"]]["additionalFields"]["formFields"][$attr]['type'] == 'gender') {
                    $this->params[$attr] = $this->getGender($this->params[$attr]);
                }
                $PMRequest->{$attr} = $this->params[$attr];
            }
        }
    }

}
