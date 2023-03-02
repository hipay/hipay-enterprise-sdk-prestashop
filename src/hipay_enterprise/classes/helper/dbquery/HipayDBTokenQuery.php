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
require_once dirname(__FILE__).'/HipayDBQueryAbstract.php';
require_once dirname(__FILE__).'/../../../lib/vendor/autoload.php';

use HiPay\Fullservice\Enum\Transaction\TransactionStatus;

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
     * @param string $token
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     */
    public function ccTokenExist($customerId, $token)
    {
        $sql = 'SELECT *'
        .' FROM `'._DB_PREFIX_.HipayDBQueryAbstract::HIPAY_CC_TOKEN_TABLE.'`'
        .' WHERE customer_id = '.(int) $customerId
        .' AND token = "'.pSQL($token).'"'
        .'LIMIT 1';

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
     * get all credit card saved for this customer.
     *
     * @param int $customerId
     *
     * @return bool|array<string,mixed>
     */
    public function getSavedCC($customerId)
    {
        $sql = 'SELECT *'
        .' FROM `'._DB_PREFIX_.HipayDBQueryAbstract::HIPAY_CC_TOKEN_TABLE.'`'
        .' WHERE customer_id = '.(int) $customerId;

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
    public function getToken($customerId, $token)
    {
        $sql = 'SELECT *'
        .' FROM `'._DB_PREFIX_.HipayDBQueryAbstract::HIPAY_CC_TOKEN_TABLE.'`'
        .' WHERE customer_id = '.(int) $customerId
        .' AND token = "'.pSQL($token).'"'
        .' LIMIT 1';

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
    public function deleteToken($customerId, $tokenId)
    {
        // check if tokenID exist for this user
        $sqlExist = 'SELECT *'
        .' FROM `'._DB_PREFIX_.HipayDBQueryAbstract::HIPAY_CC_TOKEN_TABLE.'`'
        .' WHERE customer_id = '.(int) $customerId
        .' AND hp_id = '.(int) $tokenId;

        $result = Db::getInstance()->executeS($sqlExist);

        if (!empty($result)) {
            // delete
            $where = 'customer_id = '.(int) $customerId.' AND hp_id = '.(int) $tokenId;
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
    public function deleteAllToken($customerId)
    {
        // delete
        $where = 'customer_id = '.(int) $customerId;
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
    public function nbAttemptCreateCard($customerId, $paymentStart)
    {
        $status = [
            TransactionStatus::AUTHORIZED,
            TransactionStatus::DENIED,
            TransactionStatus::REFUSED,
            TransactionStatus::EXPIRED,
            TransactionStatus::CANCELLED,
        ];

        $sql = 'SELECT COUNT(*) as sum'
        .' FROM ('
            .'SELECT order_id'
            .' FROM `'._DB_PREFIX_.HipayDBQueryAbstract::HIPAY_TRANSACTION_TABLE.'`'
            .' WHERE customer_id = '.(int) $customerId
            .' AND payment_start >= "'.pSQL($paymentStart).'"'
            .' AND status IN ('.implode(',', $status).')'
            .' AND attempt_create_multi_use = 1'
            .' GROUP BY customer_id, order_id'
        .') TMP';

        $result = Db::getInstance()->getRow($sql);
        if (isset($result['sum'])) {
            return (int) $result['sum'];
        }

        return 0;
    }
}
