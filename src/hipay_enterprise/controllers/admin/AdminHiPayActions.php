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

require_once(dirname(__FILE__) . '/../../classes/apiHandler/ApiHandler.php');
require_once(dirname(__FILE__) . '/../../classes/apiHandler/ApiHandler.php');
require_once(dirname(__FILE__) . '/../../classes/helper/dbquery/HipayDBMaintenance.php');


/**
 * Class AdminHiPayChallengeController
 *
 * Manage action for transaction in challenging
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class AdminHiPayActionsController extends ModuleAdminController
{
    /**
     * @var OrderCore
     */
    protected $order;

    /**
     * @var string
     */
    protected $transactionReference;

    /**
     * @var
     */
    protected $params;

    protected $apiHandler;

    protected $db;

    protected $dbMaintenance;

    /**
     *
     * AdminHiPayChallengeController constructor.
     *
     */
    public function __construct()
    {
        $this->module = 'hipay_enterprise';
        $this->bootstrap = true;

        parent::__construct();

        if (Tools::isSubmit('id_order') && Tools::getValue('id_order') > 0) {
            $order = new Order(Tools::getValue('id_order'));
            Shop::setContext(Shop::CONTEXT_SHOP, (int)$order->id_shop);
        }
        $this->context = Context::getContext();
        $this->apiHandler = new ApiHandler($this->module, $this->context);
        $this->dbMaintenance = new HipayDBMaintenance($this->module);
    }

    /**
     *  Post Process
     *
     * @throws PrestaShopException
     */
    public function postProcess()
    {
        if (Tools::isSubmit('id_order') && Tools::getValue('id_order') > 0) {
            $this->order = new Order(Tools::getValue('id_order'));
            if (!Validate::isLoadedObject($this->order)) {
                throw new PrestaShopException('Can\'t load Order object');
            }
            ShopUrl::cacheMainDomainForShop((int)$this->order->id_shop);
            $this->transactionReference = $this->dbMaintenance->getTransactionReference($this->order->id);
            $paymentProduct = $this->dbMaintenance->getPaymentProductFromMessage($this->order->id);
            $this->params = array(
                "method" => $paymentProduct,
                "order" => $this->order->id,
                "transaction_reference" => $this->transactionReference
            );
        }
    }
}
