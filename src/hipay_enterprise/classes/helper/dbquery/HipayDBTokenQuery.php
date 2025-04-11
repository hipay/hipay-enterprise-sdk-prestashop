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
require_once dirname(__FILE__) . '/HipayDBQueryAbstract.php';
require_once dirname(__FILE__) . '/../../../lib/vendor/autoload.php';

use HiPay\Fullservice\Enum\Transaction\TransactionStatus;
use PrestaShop\PrestaShop\Core\Domain\Customer\ValueObject\CustomerId;

/**
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 *
 * @see    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class HipayDBTokenQuery extends HipayDBQueryAbstract
{
    /**
     * check if token exist for this customer.
     *
     * @param int    $customerId
     * @param string $pan
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     */
    public function isCCAlreadySaved($customerId, $pan)
    {
        $sql = 'SELECT *'
            . ' FROM `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_CC_TOKEN_TABLE . '`'
            . ' WHERE customer_id = ' . (int) $customerId
            . ' AND pan = "' . pSQL($pan) . '"'
            . 'LIMIT 1';

        return !empty(Db::getInstance()->executeS($sql));
    }

    /**
     * save credit card token and other information.
     *
     * @param array<string,mixed> $values
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     */
    public function setCCToken($values)
    {
        foreach ($values as $key => $value) {
            $values[$key] = pSQL($value);
        }

        return Db::getInstance()->insert(HipayDBQueryAbstract::HIPAY_CC_TOKEN_TABLE, $values);
    }

    /**
     * Save new credit card
     *
     * @param array<string,mixed> $values
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     */
    public function insertNewCC($values)
    {
        foreach ($values as $key => $value) {
            $values[$key] = pSQL($value);
        }

        return Db::getInstance()->insert(HipayDBQueryAbstract::HIPAY_CC_TOKEN_TABLE, $values);
    }

    /**
     * Update credit card
     *
     * @param array<string,mixed> $values
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     */
    public function updateSavedCC($values)
    {
        foreach ($values as $key => $value) {
            $values[$key] = pSQL($value);
        }
        $where = 'customer_id = "' . $values['customer_id'] . '"'
            . ' AND pan = "' . $values['pan'] . '"';

        return Db::getInstance()->update(HipayDBQueryAbstract::HIPAY_CC_TOKEN_TABLE, $values, $where);
    }

    /**
     * get all credit card saved for this customer.
     *
     * @param int $customerId
     *
     * @return bool|array<string,mixed>
     */
    public function getSavedCC($customerId)
    {
        $sql = 'SELECT *'
            . ' FROM `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_CC_TOKEN_TABLE . '`'
            . ' WHERE customer_id = ' . (int) $customerId;

        try {
            $result = Db::getInstance()->executeS($sql);
        } catch (Exception $exc) {
            $this->logs->logException($exc);

            return false;
        }

        if (!empty($result)) {
            return $result;
        }

        return false;
    }

    /**
     * get token information.
     *
     * @param int    $customerId
     * @param string $token
     *
     * @return array<string,mixed>|false
     *
     * @throws PrestaShopDatabaseException
     */
    public function getSavedCCWithToken($customerId, $token)
    {
        $sql = 'SELECT *'
            . ' FROM `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_CC_TOKEN_TABLE . '`'
            . ' WHERE customer_id = ' . (int) $customerId
            . ' AND token = "' . pSQL($token) . '"'
            . ' LIMIT 1';

        if (!empty($result = Db::getInstance()->executeS($sql))) {
            return $result[0];
        }

        return false;
    }

    /**
     * get credit card information with PAN.
     *
     * @param int $customerId
     * @param string $pan
     *
     * @return array<string,mixed>|false
     * @throws PrestaShopDatabaseException
     */
    public function getSavedCCWithPan(int $customerId, string $pan)
    {
        $sql = 'SELECT *'
            . ' FROM `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_CC_TOKEN_TABLE . '`'
            . ' WHERE customer_id = ' . (int) $customerId
            . ' AND pan = "' . pSQL($pan) . '"'
            . ' LIMIT 1';

        if (!empty($result = Db::getInstance()->executeS($sql))) {
            return $result[0];
        }

        return false;
    }

    /**
     * delete credit card token.
     *
     * @param int $customerId
     * @param int $tokenId
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     */
    public function deleteCC($customerId, $tokenId)
    {
        // check if tokenID exist for this user
        $sqlExist = 'SELECT *'
            . ' FROM `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_CC_TOKEN_TABLE . '`'
            . ' WHERE customer_id = ' . (int) $customerId
            . ' AND hp_id = ' . (int) $tokenId;

        $result = Db::getInstance()->executeS($sqlExist);

        if (!empty($result)) {
            // delete
            $where = 'customer_id = ' . (int) $customerId . ' AND hp_id = ' . (int) $tokenId;
            Db::getInstance()->delete(HipayDBQueryAbstract::HIPAY_CC_TOKEN_TABLE, $where);

            return true;
        }

        return false;
    }

    /**
     * Delete credit card by PAN.
     *
     * @param int    $customerId
     * @param string $pan
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     */
    public function deleteCCbyPan($customerId, $pan)
    {
        // Check if the PAN exists for this user
        $sqlExist = 'SELECT *'
            . ' FROM `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_CC_TOKEN_TABLE . '`'
            . ' WHERE customer_id = ' . (int) $customerId
            . ' AND pan = "' . pSQL($pan) . '"';

        $result = Db::getInstance()->executeS($sqlExist);

        if (!empty($result)) {
            $where = 'customer_id = ' . (int) $customerId . ' AND pan = "' . pSQL($pan) . '"';
            Db::getInstance()->delete(HipayDBQueryAbstract::HIPAY_CC_TOKEN_TABLE, $where);

            return true;
        }

        return false;
    }

    /**
     * @param int $customerId
     *
     * @return true
     *
     * @throws PrestaShopException
     * @throws PrestaShopDatabaseException
     */
    public function deleteAllCCFromCustomer($customerId)
    {
        // delete
        $where = 'customer_id = ' . (int) $customerId;
        Db::getInstance()->delete(HipayDBQueryAbstract::HIPAY_CC_TOKEN_TABLE, $where);

        return true;
    }

    /**
     * @param int    $customerId
     * @param string $paymentStart
     *
     * @return int
     *
     * @throws PrestaShopException
     * @throws PrestaShopDatabaseException
     */
    public function nbAttemptCreateCC($customerId, $paymentStart)
    {
        $status = [
            TransactionStatus::AUTHORIZED,
            TransactionStatus::DENIED,
            TransactionStatus::REFUSED,
            TransactionStatus::EXPIRED,
            TransactionStatus::CANCELLED,
        ];

        $sql = 'SELECT COUNT(*) as sum'
            . ' FROM ('
            . 'SELECT order_id'
            . ' FROM `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_TRANSACTION_TABLE . '`'
            . ' WHERE customer_id = ' . (int) $customerId
            . ' AND payment_start >= "' . pSQL($paymentStart) . '"'
            . ' AND status IN (' . implode(',', $status) . ')'
            . ' AND attempt_create_multi_use = 1'
            . ' GROUP BY customer_id, order_id'
            . ') TMP';

        $result = Db::getInstance()->getRow($sql);
        if (isset($result['sum'])) {
            return (int) $result['sum'];
        }

        return 0;
    }
}
