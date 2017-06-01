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
require_once(dirname(__FILE__) . '/../../../lib/vendor/autoload.php');
require_once(dirname(__FILE__) . '/hipayDBQuery.php');

class HipayMapper {

    const PS_CAT_LEVEL_DEPTH = 3;
    const HIPAY_CAT_MAPPING = 'category';
    const HIPAY_CARRIER_MAPPING = 'carrier';

    public function __construct($moduleInstance) {
        $this->module = $moduleInstance;
        $this->logs = $this->module->getLogs();
        $this->context = Context::getContext();
        $this->db = new HipayDBQuery($this->module);
    }

    /**
     * return all Hipay categories from SDK
     * @return array
     */
    public function getHipayCategories() {
        return HiPay\Fullservice\Data\Category\Collection::getItems();
    }

    /**
     * return all active prestashop categories (depth level = PS_CAT_LEVEL_DEPTH)
     * @return array
     */
    public function getPrestashopCategories() {
        return Category::getCategories($this->context->language->id, true, false, 'AND `level_depth` = ' . HipayMapper::PS_CAT_LEVEL_DEPTH);
    }

    /**
     * return all Hipay carriers from SDK
     * @return array
     */
    public function getHipayCarriers() {
        return HiPay\Fullservice\Data\DeliveryMethod\Collection::getItems();
    }

    /**
     * return all active prestashop carriers (depth level = PS_CAT_LEVEL_DEPTH)
     * @return array
     */
    public function getPrestashopCarriers() {
        return Carrier::getCarriers($this->context->language->id, true);
    }

    /**
     * retrieve mapped categories
     * @param type $idShop
     * @return array
     */
    public function getMappedCategories($idShop) {
        $categoriesDB = $this->db->getHipayMappedCategories($idShop);

        if (!$categoriesDB) {
            $categories = array();
        } else {
            foreach ($categoriesDB as $cat) {
                $categories[$cat["ps_cat_id"]] = $cat["hp_cat_id"];
            }
        }

        return $categories;
    }

    /**
     * retrieve mapped carriers
     * @param type $idShop
     * @return array
     */
    public function getMappedCarriers($idShop) {
        $carriersDB = $this->db->getHipayMappedCarriers($idShop);

        if (!$carriersDB) {
            $carriers = array();
        } else {
            foreach ($carriersDB as $car) {
                $carriers[$car["ps_carrier_id"]]["id"] = $car["hp_carrier_id"];
                $carriers[$car["ps_carrier_id"]]["preparation_eta"] = $car["preparation_eta"];
                $carriers[$car["ps_carrier_id"]]["delivery_eta"] = $car["delivery_eta"];
            }
        }

        return $carriers;
    }

    /**
     * create mapping tables
     */
    public function createTable() {
        if (!$this->db->createCatMappingTable() || !$this->db->createCarrierMappingTable()) {
            $this->logs->logsHipay('Cannot create Mapping table');
            die('Module DB Error');
        }
    }

    /**
     * delete mapping tables
     */
    public function deleteTable() {
        if (!$this->db->deleteCatMappingTable() || !$this->db->deleteCarrierMappingTable()) {
            $this->logs->logsHipay('Cannot delete Mapping table');
        }
    }

    /**
     * save mapping
     * @param type $values
     */
    public function setMapping($type, $values) {
        switch ($type) {
            case HipayMapper::HIPAY_CAT_MAPPING :
                if (!empty($values)) {

                    foreach ($values as $val) {
                        $row[] = '(' . $val["pscat"] . ',' . $val["hipaycat"] . ',' . $this->context->shop->id . ')';
                    }

                    return $this->db->setHipayCatMapping($row);
                }
                return true;
                break;
            case HipayMapper::HIPAY_CARRIER_MAPPING :
                if (!empty($values)) {
                    foreach ($values as $val) {
                        $row[] = '(' . $val["pscar"] . ',' 
                                . $val["hipaycar"] . ','
                                . $val["prepeta"] . ','  
                                . $val["deliveryeta"] . ',' 
                                . $this->context->shop->id . ')';
                    }

                    $this->db->setHipayCarrierMapping($row);
                }
                return true;
                break;
        }
    }

    /**
     * check if id is an hipay category code
     * @param int $catId
     * @return boolean
     */
    public function hipayCategoryExist($catId) {
        $hipayCat = $this->getHipayCategories();
        $hipayCatId = array();

        foreach ($hipayCat as $cat) {
            $hipayCatId[] = $cat->getCode();
        }
        return in_array($catId, $hipayCatId);
    }

    /**
     * check if id is an hipay carrier code
     * @param int $catId
     * @return boolean
     */
    public function hipayCarrierExist($carId) {
        $hipayCar = $this->getHipayCarriers();
        $hipayCarId = array();

        foreach ($hipayCar as $car) {
            $hipayCarId[] = $car->getCode();
        }
        return in_array($carId, $hipayCarId);
    }

}
