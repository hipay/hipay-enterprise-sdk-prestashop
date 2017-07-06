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
require_once(dirname(__FILE__).'/../../../../lib/vendor/autoload.php');
require_once(dirname(__FILE__).'/../ApiFormatterAbstract.php');
require_once(dirname(__FILE__).'/../../tools/hipayConfig.php');

use HiPay\Fullservice\Enum\Transaction\ECI;
use HiPay\Fullservice\Gateway\Request\PaymentMethod\CardTokenPaymentMethod;

class CardTokenFormatter extends ApiFormatterAbstract
{
    private $cardToken;

    public function __construct($module, $params)
    {
        parent::__construct($module);
        $this->cardToken = $params['cardtoken'];
        $this->oneClick  = (isset($params['oneClick']) && $params['oneClick']) ? true
                : false;
    }

    /**
     * return mapped customer card payment informations
     * @return \HiPay\Fullservice\Gateway\Request\PaymentMethod\CardTokenPaymentMethod
     */
    public function generate()
    {

        $cardTokenRequest = new CardTokenPaymentMethod();

        $this->mapRequest($cardTokenRequest);

        return $cardTokenRequest;
    }

    /**
     * 
     * @param \HiPay\Fullservice\Gateway\Request\PaymentMethod\CardTokenPaymentMethod $cardTokenRequest
     */
    protected function mapRequest(&$cardTokenRequest)
    {

        $cardTokenRequest->cardtoken                = $this->cardToken;
        $cardTokenRequest->eci                      = ($this->oneClick) ? ECI::RECURRING_ECOMMERCE
                : ECI::SECURE_ECOMMERCE;
        $cardTokenRequest->authentication_indicator = $this->setAuthenticationIndicator();
    }

    /**
     * set 3D-secure or not from configuration
     * @return int
     */
    private function setAuthenticationIndicator()
    {

        switch ($this->configHipay["payment"]["global"]["activate_3d_secure"]) {
            case HipayConfig::THREE_D_S_DISABLED :
                return 0;
            case HipayConfig::THREE_D_S_TRY_ENABLE_ALL :
                return 1;
            case HipayConfig::THREE_D_S_TRY_ENABLE_RULES :
                $cartSummary = $this->cart->getSummaryDetails();
                foreach ($this->configHipay["payment"]["global"]["3d_secure_rules"] as $rule) {
                    if (isset($cartSummary[$rule["field"]]) && !$this->criteriaMet($cartSummary[$rule["field"]],
                            html_entity_decode($rule["operator"]),
                            $rule["value"])) {
                        return 0;
                    }
                }
                return 1;
            case HipayConfig::THREE_D_S_FORCE_ENABLE_RULES :
                $cartSummary = $this->cart->getSummaryDetails();

                foreach ($this->configHipay["payment"]["global"]["3d_secure_rules"] as $rule) {
                    if (isset($cartSummary[$rule["field"]]) && !$this->criteriaMet((int) $cartSummary[$rule["field"]],
                            html_entity_decode($rule["operator"]),
                            (int) $rule["value"])) {
                        return 0;
                    }
                }
                return 2;
            case HipayConfig::THREE_D_S_FORCE_ENABLE_ALL :
                return 2;
            default :
                return 0;
        }
    }

    /**
     * Test 2 value with $operator
     * @param type $value1
     * @param type $operator
     * @param type $value2
     * @return boolean
     */
    private function criteriaMet($value1, $operator, $value2)
    {
        switch ($operator) {
            case '<':
                return $value1 < $value2;
                break;
            case '<=':
                return $value1 <= $value2;
                break;
            case '>':
                return $value1 > $value2;
                break;
            case '>=':
                return $value1 >= $value2;
                break;
            case '==':
                return $value1 == $value2;
                break;
            case '!=':
                return $value1 != $value2;
                break;
            default:
                return false;
        }
        return false;
    }
}