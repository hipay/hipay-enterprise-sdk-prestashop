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

class DeliveryShippingInfoFormatter extends apiFormatterAbstract
{
    public function __construct($module)
    {
        parent::__construct($module);
        $this->mappedShipping = $this->mapper->getMappedHipayCarrierFromPSId($this->cart->id_carrier);
    }

    /**
     * return mapped delivery shipping informations
     * @return \HiPay\Fullservice\Gateway\Request\Info\DeliveryShippingInfoRequest
     */
    public function generate()
    {
        $deliveryShippingInfo = new \HiPay\Fullservice\Gateway\Request\Info\DeliveryShippingInfoRequest();

        $this->mapRequest($deliveryShippingInfo);

        return $deliveryShippingInfo;
    }

    /**
     * map prestashop delivery shipping informations to request fields (Hpayment Post)
     * @param \HiPay\Fullservice\Gateway\Request\Info\DeliveryShippingInfoRequest $deliveryShippingInfo
     */
    protected function mapRequest(&$deliveryShippingInfo)
    {
        $deliveryShippingInfo->delivery_date = $this->calculateEstimatedDate();
        $deliveryShippingInfo->delivery_method = $this->getMappingShippingMethod();
    }

    /**
     * According to the mapping, provide a approximated date delivery
     *
     * @return date format YYYY-MM-DD
     */
    private function calculateEstimatedDate()
    {
        if ($this->mappedShipping != null) {
            $today = new \Datetime();
            $daysDelay = $this->mappedShipping["preparation_eta"] + $this->mappedShipping["delivery_eta"];
            $interval = new \DateInterval("P{$daysDelay}D");

            return $today->add($interval)->format("Y-m-d");
        }
        return null;
    }

    /**
     * Provide a delivery Method compatible with gateway
     *
     * @return null|string
     */
    private function getMappingShippingMethod()
    {
        if ($this->mappedShipping != null) {
            return json_encode(
                array('mode' => $this->mappedShipping["hp_carrier_mode"],
                    'shipping' => $this->mappedShipping["hp_carrier_shipping"])
            );
        }
        return null;
    }
}
