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
require_once(dirname(__FILE__) . '/../ApiFormatterAbstract.php');
require_once(dirname(__FILE__) . '/../../../../lib/vendor/autoload.php');

class MaintenanceFormatter implements ApiFormatterInterface {

    public function __construct($module, $params) {
        $this->module = $module;
        $this->context = Context::getContext();
        $this->configHipay = $this->module->hipayConfigTool->getConfigHipay();
    }

    /**
     * generate request data before API call
     * @return \HiPay\Fullservice\Gateway\Request\Maintenance\MaintenanceRequest
     */
    public function generate() {

        $maintenance = new \HiPay\Fullservice\Gateway\Request\Maintenance\MaintenanceRequest();

        $this->mapRequest($maintenance);

        return $maintenance;
    }

    /**
     * map prestashop order informations to request fields 
     * @param type $maintenance
     */
    protected function mapRequest(&$maintenance) {
        
    }

}
