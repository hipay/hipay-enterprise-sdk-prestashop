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
require_once(dirname(__FILE__).'/hipayDBQuery.php');

class HipayMaintenanceData
{
    private $items;

    /**
     *
     * @param type $moduleInstance
     */
    public function __construct($moduleInstance)
    {
        $this->module  = $moduleInstance;
        $this->logs    = $this->module->getLogs();
        $this->context = Context::getContext();
        $this->db      = new HipayDBQuery($this->module);
        $this->items   = array();
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
                $this->db->setCaptureOrRefundOrder($item);
            }
        }
    }
}