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

class GenericPaymentMethodFormatter extends ApiFormatterAbstract
{
    private $params;

    public function __construct(
        $module,
        $params
    ) {
        parent::__construct($module);
        $this->params = $params;
    }

    /**
     * return mapped pyament method informations
     * @return mixed
     */
    public function generate()
    {
        $PMRequest = null;

        if (!empty($this->configHipay["payment"]["local_payment"][$this->params["method"]]["additionalFields"])) {
            $PMRequest = new $this->configHipay["payment"]["local_payment"][$this->params["method"]]["additionalFields"]["sdkClass"]();

            $this->mapRequest($PMRequest);
        }
        return $PMRequest;
    }

    /**
     * hydrate object define in json config
     * @param mixed
     */
    protected function mapRequest(&$PMRequest)
    {

        // we get all attributes
        $attributes = get_object_vars($PMRequest);

        foreach ($attributes as $attr => $value) {
            //if field has default value in json config
            if (isset($this->configHipay["payment"]["local_payment"][$this->params["method"]]["additionalFields"]["defaultFieldsValue"][$attr])) {
                $PMRequest->{$attr} = $this->configHipay["payment"]["local_payment"][$this->params["method"]]["additionalFields"]["defaultFieldsValue"][$attr];
            } elseif (isset($this->params[$attr])) {
                // format gender data
                if ($this->configHipay["payment"]["local_payment"][$this->params["method"]]["additionalFields"]["formFields"][$attr]['type'] == 'gender') {
                    $this->params[$attr] = $this->getGender($this->params[$attr]);
                }
                $PMRequest->{$attr} = $this->params[$attr];
            }
        }
    }
}
