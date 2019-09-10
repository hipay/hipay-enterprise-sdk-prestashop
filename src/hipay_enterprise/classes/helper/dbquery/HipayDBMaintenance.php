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

use HiPay\Fullservice\Enum\Transaction\TransactionStatus;

require_once(dirname(__FILE__) . '/HipayDBQueryAbstract.php');

/**
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class HipayDBMaintenance extends HipayDBQueryAbstract
{
    /**
     * save order capture data (basket)
     *
     * @param $values
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    public function setCaptureOrRefundOrder($values)
    {
        foreach ($values as $key => $value) {
            $values[$key] = pSQL($value);
        }

        return Db::getInstance()->insert(
            HipayDBQueryAbstract::HIPAY_ORDER_REFUND_CAPTURE_TABLE,
            $values
        );
    }

    /**
     * get order capture saved data (basket)
     *
     * @param $orderId
     * @return array
     * @throws PrestaShopDatabaseException
     */
    public function getCapturedItems($orderId)
    {
        return $this->getMaintainedItems(
            $orderId,
            "capture",
            "good"
        );
    }

    /**
     * get order refund saved data (basket)
     *
     * @param $orderId
     * @return array
     * @throws PrestaShopDatabaseException
     */
    public function getRefundedItems($orderId)
    {
        return $this->getMaintainedItems(
            $orderId,
            "refund",
            "good"
        );
    }

    /**
     * return true if a capture or refund have been executed from TPP BO
     *
     * @param $orderId
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    public function captureOrRefundFromBO($orderId)
    {
        $item = $this->getMaintainedItems($orderId, "BO_TPP", "BO");
        if (empty($item)) {
            return false;
        }

        return true;
    }

    /**
     * get number of capture or refund attempt
     *
     * @param $operation
     * @param $orderId
     * @return int
     */
    public function getNbOperationAttempt($operation, $orderId)
    {
        $sql = 'SELECT `attempt_number`
                FROM `' .
            _DB_PREFIX_ .
            HipayDBQueryAbstract::HIPAY_ORDER_REFUND_CAPTURE_TABLE .
            '`
                WHERE `hp_ps_order_id` = ' .
            pSQL((int)$orderId) .
            ' AND `operation` = "' .
            pSQL($operation) .
            '" ORDER BY `attempt_number` DESC';

        $result = Db::getInstance()->getRow($sql);
        if (isset($result['attempt_number'])) {
            return (int)$result['attempt_number'];
        }
        return 0;
    }

    /**
     * @param $orderId
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    public function feesAreCaptured($orderId)
    {
        return $this->feesOrDiscountAreMaintained($orderId, 'fees', 'capture');
    }

    /**
     * @param $orderId
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    public function feesAreRefunded($orderId)
    {
        return $this->feesOrDiscountAreMaintained($orderId, 'fees', 'refund');
    }

    /**
     * @param $orderId
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    public function discountsAreCaptured($orderId)
    {
        return $this->feesOrDiscountAreMaintained($orderId, 'discount', 'capture');
    }

    /**
     * @param $orderId
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    public function discountsAreRefunded($orderId)
    {
        return $this->feesOrDiscountAreMaintained($orderId, 'discount', 'refund');
    }

    /**
     * @param $orderId
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    public function wrappingIsRefunded($orderId)
    {
        return $this->feesOrDiscountAreMaintained($orderId, 'wrapping', 'refund');
    }

    /**
     * @param $orderId
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    public function wrappingIsCaptured($orderId)
    {
        return $this->feesOrDiscountAreMaintained($orderId, 'wrapping', 'capture');
    }

    /**
     * save order capture type
     *
     * @param $values
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    public function setOrderCaptureType($values)
    {
        foreach ($values as $key => $value) {
            $values[$key] = pSQL($value);
        }

        return Db::getInstance()->insert(HipayDBQueryAbstract::HIPAY_ORDER_CAPTURE_TYPE_TABLE, $values);
    }

    /**
     * @param $orderId
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    public function OrderCaptureTypeExist($orderId)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_ORDER_CAPTURE_TYPE_TABLE .
            '` WHERE order_id=' . pSQL((int)$orderId);

        $result = Db::getInstance()->executeS($sql);
        if (!empty($result)) {
            return true;
        }

        return false;
    }

    /**
     * @param $orderId
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    public function isManualCapture($orderId)
    {
        $sql = 'SELECT * FROM `' .
            _DB_PREFIX_ .
            HipayDBQueryAbstract::HIPAY_ORDER_CAPTURE_TYPE_TABLE .
            '` WHERE order_id=' .
            pSQL(
                (int)$orderId
            ) .
            ' AND type = "manual" LIMIT 1;';

        $result = Db::getInstance()->executeS($sql);
        if (!empty($result)) {
            return true;
        }

        return false;
    }

    /**
     * return if  order already captured from hipay transaction
     *
     * @param $orderId
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    public function alreadyCaptured($orderId)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_TRANSACTION_TABLE .
            '` WHERE order_id=' . pSQL((int)$orderId) . ' AND status =' . TransactionStatus::CAPTURED . ' ;';

        $result = Db::getInstance()->executeS($sql);
        if (empty($result)) {
            return false;
        }
        return true;
    }

    /**
     * save hipay transaction (notification)
     *
     * @param $values
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    public function setHipayTransaction($values)
    {
        foreach ($values as $key => $value) {
            $values[$key] = pSQL($value);
        }

        return Db::getInstance()->insert(HipayDBQueryAbstract::HIPAY_TRANSACTION_TABLE, $values);
    }

    /**
     * return order transaction reference from hipay transaction
     *
     * @param $orderId
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    public function getTransactionReference($orderId)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_TRANSACTION_TABLE .
            '` WHERE order_id=' . pSQL((int)$orderId) . ' AND ( status =' . TransactionStatus::AUTHORIZED . ' 
                OR status =' . TransactionStatus::AUTHORIZED_AND_PENDING . ') LIMIT 1 ;';

        $result = Db::getInstance()->executeS($sql);
        if (!empty($result)) {
            return $result[0]["transaction_ref"];
        }
        return false;
    }

    /**
     * return order transaction from hipay transaction
     * @param $transaction_reference
     */
    public function getTransactionById($transaction_reference)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_TRANSACTION_TABLE .
            '` WHERE transaction_ref="' . pSQL($transaction_reference) . '" LIMIT 1 ;';

        $result = Db::getInstance()->executeS($sql);
        if (!empty($result)) {
            return $result[0];
        }
        return false;
    }


    /**
     * return order transaction from hipay transaction
     *
     * @param $orderId
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    public function getTransaction($orderId)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_TRANSACTION_TABLE .
            '` WHERE order_id=' . pSQL((int)$orderId) . ' AND ( status =' . TransactionStatus::AUTHORIZED . ' 
                OR status =' . TransactionStatus::AUTHORIZED_AND_PENDING . ') LIMIT 1 ;';

        $result = Db::getInstance()->executeS($sql);
        if (!empty($result)) {
            return $result[0];
        }
        return false;
    }


    /**
     * return order payment product from hipay transaction
     *
     * @param $orderId
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    public function getPaymentProductFromMessage($orderId)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_TRANSACTION_TABLE .
            '` WHERE order_id=' . pSQL((int)$orderId) .
            ' AND ( status =' . TransactionStatus::AUTHORIZED .' OR status =' . TransactionStatus::AUTHORIZED_AND_PENDING . ')  
             LIMIT 1;';
        $result = Db::getInstance()->executeS($sql);
        if (!empty($result)) {
            return $result[0]["payment_product"];
        }
        return false;
    }

    /**
     * return order basket from hipay transaction
     *
     * @param $orderId
     * @return array|bool
     * @throws PrestaShopDatabaseException
     */
    public function getOrderBasket($orderId)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_TRANSACTION_TABLE .
            '` WHERE order_id=' . pSQL((int)$orderId) . ' AND status =' . TransactionStatus::AUTHORIZED . ' LIMIT 1;';

        $result = Db::getInstance()->executeS($sql);
        if (!empty($result)) {
            return Tools::jsonDecode(
                $result[0]["basket"],
                true
            );
        }
        return false;
    }

    /**
     * get capture or refund saved data (basket)
     *
     * @param $orderId
     * @param $operation
     * @param $type
     * @return array
     * @throws PrestaShopDatabaseException
     */
    private function getMaintainedItems($orderId, $operation, $type)
    {
        $sql = 'SELECT `hp_ps_product_id`, `operation`, `type`, SUM(`quantity`) as quantity, SUM(`amount`) as amount
                FROM `' .
            _DB_PREFIX_ .
            HipayDBQueryAbstract::HIPAY_ORDER_REFUND_CAPTURE_TABLE .
            '`
                WHERE `hp_ps_order_id` = ' .
            pSQL((int)$orderId) .
            ' AND `operation` = "' .
            pSQL($operation) .
            '" AND `type` = "' .
            pSQL($type) .
            '"' .
            ' GROUP BY `hp_ps_product_id`';

        $result = Db::getInstance()->executeS($sql);
        $formattedResult = array();
        foreach ($result as $item) {
            $formattedResult[$item["hp_ps_product_id"]] = $item;
        }
        return $formattedResult;
    }

    /**
     * @param $orderId
     * @param $type
     * @param $operation
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    private function feesOrDiscountAreMaintained($orderId, $type, $operation)
    {
        $sql = 'SELECT *
                FROM `' .
            _DB_PREFIX_ .
            HipayDBQueryAbstract::HIPAY_ORDER_REFUND_CAPTURE_TABLE .
            '`
                WHERE `hp_ps_order_id` = ' .
            pSQL((int)$orderId) .
            ' AND `operation` = "' .
            $operation .
            '" AND `type` = "' .
            $type .
            '"';
        $result = Db::getInstance()->executeS($sql);

        if (!empty($result)) {
            return true;
        }

        return false;
    }
}
