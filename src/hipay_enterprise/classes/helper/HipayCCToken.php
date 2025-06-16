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
require_once dirname(__FILE__) . '/dbquery/HipayDBTokenQuery.php';

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
     * Save credit card token and other informations.
     *
     * @param int   $customerId
     * @param array $card
     *
     * @return void
     */
    public function saveCC($customerId, $card)
    {
        $card = array_merge([
            'customer_id' => $customerId,
            'created_at' => (new DateTime())->format('Y-m-d')
        ], $card);

        $originalPan = $card['pan'];
        $asteriskPan = str_replace('x', '*', $originalPan);

        // Normalize PAN masking
        $card['pan'] = $asteriskPan;

        if (strpos($originalPan, 'x') !== false && $this->isCCAlreadySaved($customerId, $originalPan)) {
            $this->logs->logInfos("# Migrating CC masking format for customer ID " . $customerId);
            $this->dbTokenQuery->deleteCCbyPan($customerId, $originalPan);
            $card['authorized'] = 0; // Reasonable default for migrated legacy format
            $this->dbTokenQuery->insertNewCC($card);
            return;
        }

        // New or existing card insert/update
        if ($this->isCCAlreadySaved($customerId, $asteriskPan)) {
            $this->logs->logInfos("# Update existing CC for customer ID " . $customerId);
            $this->dbTokenQuery->updateSavedCC($card);
        } else {
            $this->logs->logInfos("# Save new CC for customer ID " . $customerId);
            // Do not override authorized flag if already passed
            if (!isset($card['authorized'])) {
                $card['authorized'] = 0;
            }
            $this->dbTokenQuery->insertNewCC($card);
        }
    }

    /**
     * Replace asterisk masking with 'x' in card PAN fields.
     *
     * @param bool|array $cards
     *
     * @return bool|array
     */
    private function cleanCardPanMasking($cards)
    {
        if ($cards && is_array($cards)) {
            foreach ($cards as &$card) {
                if (isset($card['pan'])) {
                    $card['pan'] = str_replace('*', 'x', $card['pan']);
                }
            }
        }

        return $cards;
    }

    /**
     * Get all saved credit card from customer.
     *
     * @param int $customerId
     *
     * @return bool|array
     */
    public function getSavedCC($customerId)
    {
        return $this->cleanCardPanMasking($this->dbTokenQuery->getSavedCC($customerId));
    }

    /**
     * Check if customer credit card is already saved
     *
     * @param int    $customerId
     * @param string $pan
     *
     * @return bool
     */
    public function isCCAlreadySaved($customerId, $pan)
    {
        return $this->dbTokenQuery->isCCAlreadySaved($customerId, $pan);
    }

    /**
     * Get CC informations with token.
     *
     * @param int    $customerId
     * @param string $token
     *
     * @return array<string,mixed>|false
     */
    public function getCCDetails($customerId, $token)
    {
        return $this->dbTokenQuery->getSavedCCWithToken($customerId, $token);
    }

    /**
     * Delete customer credit card token.
     *
     * @param int    $customerId
     * @param string $tokenId
     *
     * @return bool
     */
    public function deleteCC($customerId, $tokenId)
    {
        return $this->dbTokenQuery->deleteCC($customerId, $tokenId);
    }

    /**
     * @param int $customerId
     *
     * @return true
     */
    public function deleteAllCCFromCustomer($customerId)
    {
        return $this->dbTokenQuery->deleteAllCCFromCustomer($customerId);
    }
}
