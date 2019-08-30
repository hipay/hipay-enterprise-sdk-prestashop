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

require_once(dirname(__FILE__) . '/HipayDBQueryAbstract.php');
require_once(dirname(__FILE__) . '/../../../lib/vendor/autoload.php');

use HiPay\Fullservice\Enum\Transaction\TransactionStatus;

/**
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class HipayDBTokenQuery extends HipayDBQueryAbstract
{
    /**
     * check if token exist for this customer
     *
     * @param $customerId
     * @param $token
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    public function ccTokenExist(
        $customerId,
        $token
    ) {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_CC_TOKEN_TABLE .
            '` WHERE customer_id=' . pSQL((int)$customerId) . ' AND token LIKE "' . pSQL($token) . '" LIMIT 1;';

        $result = Db::getInstance()->executeS($sql);
        if (!empty($result)) {
            return true;
        }

        return false;
    }

    /**
     * save credit card token and other information
     *
     * @param $values
     * @return bool
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
     * get all credit card saved for this customer
     *
     * @param type $customerId
     * @return boolean
     */
    public function getSavedCC($customerId)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_CC_TOKEN_TABLE .
            '` WHERE customer_id=' . pSQL((int)$customerId) . ' ;';

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
     * get token information
     *
     * @param $customerId
     * @param $token
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    public function getToken($customerId, $token)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_CC_TOKEN_TABLE .
            '` WHERE customer_id=' . pSQL((int)$customerId) . ' AND token LIKE "' . pSQL($token) . '" LIMIT 1;';

        $result = Db::getInstance()->executeS($sql);
        if (!empty($result)) {
            return $result[0];
        }

        return false;
    }

    /**
     * delete credit card token
     *
     * @param $customerId
     * @param $tokenId
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    public function deleteToken($customerId, $tokenId)
    {
        // check if tokenID exist for this user
        $sqlExist = 'SELECT * FROM `' .
            _DB_PREFIX_ .
            HipayDBQueryAbstract::HIPAY_CC_TOKEN_TABLE .
            '` WHERE customer_id=' .
            pSQL(
                (int)$customerId
            ) .
            ' AND hp_id = ' .
            pSQL((int)$tokenId) .
            ';';

        $result = Db::getInstance()->executeS($sqlExist);

        if (!empty($result)) {
            // delete
            $where = 'customer_id=' . pSQL((int)$customerId) . ' AND hp_id=' . pSQL((int)$tokenId);
            Db::getInstance()->delete(HipayDBQueryAbstract::HIPAY_CC_TOKEN_TABLE, $where);

            return true;
        }
        return false;
    }

    public function deleteAllToken($customerId)
    {
        // delete
        $where = 'customer_id=' . pSQL((int)$customerId);
        Db::getInstance()->delete(HipayDBQueryAbstract::HIPAY_CC_TOKEN_TABLE, $where);

        return true;
    }

    public function nbAttemptCreateCard($customerId, $paymentStart)
    {
        $status = array(
            TransactionStatus::AUTHORIZED,
            TransactionStatus::DENIED,
            TransactionStatus::REFUSED,
            TransactionStatus::EXPIRED,
            TransactionStatus::CANCELLED
        );

        $sql = 'SELECT COUNT(*) as sum FROM (' .
                'SELECT order_id' .
                ' FROM `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_TRANSACTION_TABLE .
                '` WHERE customer_id = ' . pSQL((int)$customerId) .
                ' AND payment_start >= "' . $paymentStart . '"' .
                ' AND status IN (' . implode(",", $status) . ')' .
                ' AND attempt_create_multi_use = 1' .
                ' GROUP BY customer_id, order_id' .
            ') TMP;';

        $result = Db::getInstance()->getRow($sql);
        if (isset($result['sum'])) {
            return $result['sum'];
        }

        return 0;
    }
}
