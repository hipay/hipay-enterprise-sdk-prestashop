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
require_once(dirname(__FILE__) . '/../../classes/helper/HipayDBQuery.php');


/**
 * Class AdminHiPayChallengeController
 *
 * Manage action for transaction in challenging
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link 	https://github.com/hipay/hipay-enterprise-sdk-prestashop
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

    /**
     *
     * AdminHiPayChallengeController constructor.
     *
     */
    public function __construct()
    {
        $this->module = 'hipay_enterprise';
        $this->bootstrap = true;
        $this->context = Context::getContext();

        parent::__construct();

        $this->apiHandler = new ApiHandler(
            $this->module,
            $this->context
        );
        $this->db = new HipayDBQuery($this->module);
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
            $this->transactionReference = $this->db->getTransactionReference($this->order->id);
            $paymentProduct = $this->db->getPaymentProductFromMessage($this->order->id);
            $this->params = array(
                "method" => $paymentProduct,
                "order" => $this->order->id,
                "transaction_reference" => $this->transactionReference
            );
        }
    }
}
