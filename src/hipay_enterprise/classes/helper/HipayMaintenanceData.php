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

require_once(dirname(__FILE__) . '/dbquery/HipayDBMaintenance.php');

/**
 * Handle maintenance data
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class HipayMaintenanceData
{
    private $items;

    /**
     *
     * @param type $moduleInstance
     */
    public function __construct($moduleInstance)
    {
        $this->module = $moduleInstance;
        $this->logs = $this->module->getLogs();
        $this->context = Context::getContext();
        $this->dbMaintenance = new HipayDBMaintenance($this->module);
        $this->items = array();
    }

    /**
     *
     * @param type $item
     */
    public function addItem($item)
    {
        $this->items[] = $item;
    }

    /**
     *
     * @return type
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     *
     */
    public function saveData()
    {
        if (!empty($this->items)) {
            foreach ($this->items as $item) {
                $this->dbMaintenance->setCaptureOrRefundOrder($item);
            }
        }
    }

    public function getNbOperationAttempt($type, $orderId)
    {
        return $this->dbMaintenance->getNbOperationAttempt($type, $orderId);
    }
}
