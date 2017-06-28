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

class HipayCCToken
{

    public function __construct($moduleInstance)
    {
        $this->module  = $moduleInstance;
        $this->logs    = $this->module->getLogs();
        $this->context = Context::getContext();
        $this->db      = new HipayDBQuery($this->module);
    }

    /**
     *
     * @param int $customerId
     * @param array $card
     */
    public function saveCCToken($customerId, $card)
    {
        if (!$this->db->ccTokenExist($customerId, $card["token"])) {

            $card = array_merge(array("customer" => $customerId), $card);

            $this->db->setCCToken($card);
        }
    }
}