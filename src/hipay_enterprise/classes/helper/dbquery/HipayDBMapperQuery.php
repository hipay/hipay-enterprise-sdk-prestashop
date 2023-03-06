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

/**
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 *
 * @see    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class HipayDBMapperQuery extends HipayDBQueryAbstract
{
    /**
     * @param int $idShop
     *
     * @return array|false|mysqli_result|PDOStatement|resource|null
     *
     * @throws PrestaShopDatabaseException
     */
    public function getHipayMappedCategories($idShop)
    {
        $sql = 'SELECT *'
        .' FROM `'._DB_PREFIX_.HipayDBQueryAbstract::HIPAY_CAT_MAPPING_TABLE.'`'
        .' WHERE `shop_id` = '.(int) $idShop;

        return Db::getInstance()->executeS($sql);
    }

    /**
     * @param int $idShop
     *
     * @return array|false|mysqli_result|PDOStatement|resource|null
     *
     * @throws PrestaShopDatabaseException
     */
    public function getHipayMappedCarriers($idShop)
    {
        $sql = 'SELECT *'
        .' FROM `'._DB_PREFIX_.HipayDBQueryAbstract::HIPAY_CARRIER_MAPPING_TABLE.'`'
        .' WHERE `shop_id` = '.(int) $idShop;

        return Db::getInstance()->executeS($sql);
    }

    /**
     * insert row in HIPAY_CAT_MAPPING_TABLE.
     *
     * @param array<string,mixed> $values
     *
     * @return bool
     *
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
     * insert row in HIPAY_CARRIER_MAPPING_TABLE.
     *
     * @param array<string,mixed> $values
     *
     * @return bool
     *
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
     * @param int $PSId
     *
     * @return array|bool|object|null
     */
    public function getHipayCatFromPSId($PSId)
    {
        $sql = 'SELECT hp_cat_id'
        .' FROM `'._DB_PREFIX_.HipayDBQueryAbstract::HIPAY_CAT_MAPPING_TABLE.'`'
        .' WHERE hp_ps_cat_id = '.(int) $PSId;

        return Db::getInstance()->getRow($sql);
    }

    /**
     * @param int $PSId
     *
     * @return array|bool|object|null
     */
    public function getHipayCarrierFromPSId($PSId)
    {
        $sql = 'SELECT *'
        .' FROM `'._DB_PREFIX_.HipayDBQueryAbstract::HIPAY_CARRIER_MAPPING_TABLE.'`'
        .' WHERE hp_ps_carrier_id = '.(int) $PSId;

        return Db::getInstance()->getRow($sql);
    }
}
