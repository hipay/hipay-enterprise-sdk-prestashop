<?php
/**
 * HiPay Enterprise SDK Prestashop.
 *
 * 2017 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright 2017 HiPay
 * @license   https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 */
require_once dirname(__FILE__).'/dbquery/HipayDBTokenQuery.php';

/**
 * handle credit card token (OneClik payment).
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 *
 * @see    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class HipayCCToken
{
    /** @var Hipay_enterprise */
    private $module;
    /** @var HipayLogs */
    private $logs;
    /** @var HipayDBTokenQuery */
    private $dbTokenQuery;

    /**
     * @param Hipay_entreprise $moduleInstance
     *
     * @return void
     */
    public function __construct($moduleInstance)
    {
        $this->module = $moduleInstance;
        $this->logs = $this->module->getLogs();
        $this->dbTokenQuery = new HipayDBTokenQuery($this->module);
    }

    /**
     * save credit card token and other informations.
     *
     * @param int   $customerId
     * @param array $card
     *
     * @return void
     */
    public function saveCCToken($customerId, $card)
    {
        if (!$this->tokenExist($customerId, $card['token'])) {
            $this->logs->logInfos("# SaveCCToken for customer ID $customerId");
            $card = array_merge(['customer_id' => $customerId, 'created_at' => (new DateTime())->format('Y-m-d')], $card);

            $this->dbTokenQuery->setCCToken($card);
        }
    }

    /**
     * get all saved credit card from customer.
     *
     * @param int $customerId
     *
     * @return bool|array
     */
    public function getSavedCC($customerId)
    {
        return $this->dbTokenQuery->getSavedCC($customerId);
    }

    /**
     * check if customer credit card token exit.
     *
     * @param int    $customerId
     * @param string $token
     *
     * @return bool
     */
    public function tokenExist($customerId, $token)
    {
        return $this->dbTokenQuery->ccTokenExist($customerId, $token);
    }

    /**
     * get token informations.
     *
     * @param int    $customerId
     * @param string $token
     *
     * @return array<string,mixed>|false
     */
    public function getTokenDetails($customerId, $token)
    {
        return $this->dbTokenQuery->getToken($customerId, $token);
    }

    /**
     * delete customer credit card token.
     *
     * @param int    $customerId
     * @param string $tokenId
     *
     * @return bool
     */
    public function deleteToken($customerId, $tokenId)
    {
        return $this->dbTokenQuery->deleteToken($customerId, $tokenId);
    }

    /**
     * @param int $customerId
     *
     * @return true
     */
    public function deleteAllToken($customerId)
    {
        return $this->dbTokenQuery->deleteAllToken($customerId);
    }
}
