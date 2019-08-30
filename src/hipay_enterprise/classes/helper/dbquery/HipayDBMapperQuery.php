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

/**
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class HipayDBMapperQuery extends HipayDBQueryAbstract
{
    /**
     * @param $idShop
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public function getHipayMappedCategories($idShop)
    {
        $sql = 'SELECT *
                FROM `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_CAT_MAPPING_TABLE . '`
                WHERE `shop_id` = ' . pSQL((int)$idShop);

        return Db::getInstance()->executeS($sql);
    }

    /**
     * @param $idShop
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public function getHipayMappedCarriers($idShop)
    {
        $sql = 'SELECT *
                FROM `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_CARRIER_MAPPING_TABLE . '`
                WHERE `shop_id` = ' . pSQL((int)$idShop);

        return Db::getInstance()->executeS($sql);
    }

    /**
     * insert row in HIPAY_CAT_MAPPING_TABLE
     *
     * @param $values
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    public function setHipayCatMapping($values)
    {
        return Db::getInstance()->insert(
            HipayDBQueryAbstract::HIPAY_CAT_MAPPING_TABLE,
            $values,
            false,
            true,
            Db::REPLACE
        );
    }

    /**
     * insert row in HIPAY_CARRIER_MAPPING_TABLE
     *
     * @param $values
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    public function setHipayCarrierMapping($values)
    {
        return Db::getInstance()->insert(
            HipayDBQueryAbstract::HIPAY_CARRIER_MAPPING_TABLE,
            $values,
            false,
            true,
            Db::REPLACE
        );
    }

    /**
     * @param $PSId
     * @return array|bool|null|object
     */
    public function getHipayCatFromPSId($PSId)
    {
        $sql = 'SELECT hp_cat_id
                FROM `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_CAT_MAPPING_TABLE . '` 
                WHERE hp_ps_cat_id = ' . pSQL((int)$PSId);

        $result = Db::getInstance()->getRow($sql);

        return $result;
    }

    /**
     * @param $PSId
     * @return array|bool|null|object
     */
    public function getHipayCarrierFromPSId($PSId)
    {
        $sql = 'SELECT *
                FROM `' . _DB_PREFIX_ . HipayDBQueryAbstract::HIPAY_CARRIER_MAPPING_TABLE . '` 
                WHERE hp_ps_carrier_id = ' . pSQL((int)$PSId);

        $result = Db::getInstance()->getRow($sql);

        return $result;
    }
}
