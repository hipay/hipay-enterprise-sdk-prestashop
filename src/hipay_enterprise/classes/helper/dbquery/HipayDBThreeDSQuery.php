<?php
/**
 * HiPay Enterprise SDK Prestashop
 *
 * 2019 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright 2019 HiPay
 * @license   https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 */

require_once(dirname(__FILE__) . '/HipayDBQueryAbstract.php');
/**
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2019 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class HipayDBThreeDSQuery extends HipayDBQueryAbstract
{
    public function cartAlreadyOrdered($customerId, $products)
    {
        $where = array();
        foreach ($products as $product) {
            $where[] = "(product_id = " .
                $product["product_id"] .
                " and product_quantity = " .
                $product["product_quantity"] .
                " and id_shop = " .
                $product["id_shop"] .
                " and product_attribute_id = "
                . $product["product_attribute_id"] .
                ")";
        }

        $sql = 'SELECT COUNT(DISTINCT id_order) AS count 
		FROM `' . _DB_PREFIX_ . 'order_detail`' .
		' WHERE (' . implode(" OR ", $where) .
		') AND id_order IN' .
            ' (SELECT id_order FROM `' . _DB_PREFIX_ . 'orders` WHERE id_customer = ' . $customerId . ')';

        $result = Db::getInstance()->getRow($sql);

        if (isset($result['count']) && $result['count'] > 0) {
            return true;
        }
        return false;
    }

    public function getNbPaymentAttempt($customerId, $paymentStart, $paymentMethods)
    {

        $sql = 'SELECT customer_id, COUNT(DISTINCT order_id) AS count 
            FROM `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_TRANSACTION_TABLE .
            '` WHERE customer_id = ' . pSQL((int)$customerId) .
            ' AND payment_start >= "' . $paymentStart . '"' .
            ' AND payment_product IN (\'' . implode("','", $paymentMethods) . '\')' .
            ' GROUP BY customer_id;';

        $result = Db::getInstance()->getRow($sql);

        if (isset($result['count'])) {
            return $result['count'];
        }

        return 0;
    }

    public function getDateAddressFirstUsed($addressId)
    {

        $sql = 'SELECT date_add 
            FROM `' . _DB_PREFIX_ . 'orders`' .
            ' WHERE id_address_delivery = ' . pSQL((int)$addressId) .
            ' OR id_address_invoice = ' . $addressId .
            ' ORDER BY date_add ASC;';

        $result = Db::getInstance()->getRow($sql);

        if (isset($result['date_add'])) {
            return $result['date_add'];
        }

        return false;
    }

    public function getLastOrder($customerId)
    {
        $sql = 'SELECT id_order, reference FROM `' . _DB_PREFIX_ . 'orders`' .
            ' WHERE id_customer = ' . pSQL((int)$customerId) .
            ' ORDER BY date_add DESC;';

        $result = Db::getInstance()->getRow($sql);

        if (isset($result)) {
            return $result;
        }

        return false;

    }

    public function getTransactionReference($lastOrder)
    {
        $sql = 'SELECT transaction_id FROM `' . _DB_PREFIX_ . 'order_payment`' .
            ' WHERE order_reference = \'' . pSQL($lastOrder['reference']) . '\'' .
            ' ORDER BY date_add ASC;';

        $result = Db::getInstance()->getRow($sql);

        if (isset($result['transaction_id'])) {
            if(strpos($result['transaction_id'], "BO_TPP") !== FALSE){
                $transactionId = substr($result['transaction_id'], 0, strpos($result['transaction_id'], '-'));
            } else {
                $transactionId = $result['transaction_id'];
            }
            return $transactionId;
        } else {
            $sql = 'SELECT transaction_ref FROM `' . _DB_PREFIX_ . 'hipay_transaction`' .
                ' WHERE order_id = ' . pSQL((int)$lastOrder['id_order']) .
                ' ORDER BY hp_id ASC;';

            $result = Db::getInstance()->getRow($sql);

            if (isset($result['transaction_ref'])) {
                return $result['transaction_ref'];
            }
        }


        return null;
    }
}