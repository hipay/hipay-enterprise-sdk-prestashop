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

/**
 * Class AdminHiPayMotoController
 *
 * Manage action for MOTO payment
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class AdminHiPayMotoController extends ModuleAdminController
{
    /**
     * @var OrderCore
     */
    protected $order;

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

        $this->apiHandler = new ApiHandler($this->module, $this->context);
    }

    /**
     *  Post Process
     *
     * @throws PrestaShopException
     */
    public function postProcess()
    {
        if (Tools::isSubmit('motoPayment')) {
            $cartId = Tools::getValue('cart_id');

            $cart = new Cart((int)$cartId);
            $this->apiHandler->handleMoto($cart);
        }

        if (Tools::getValue('hipaystatus') && Tools::getValue('id_order')) {
            $this->order = new Order(Tools::getValue('id_order'));
            if (!Validate::isLoadedObject($this->order)) {
                throw new PrestaShopException('Can\'t load Order object');
            }

            $token = Tools::getValue('hipaytoken');

            // check requesty integrity
            if ($token != HipayHelper::getHipayAdminToken('AdminOrders', $this->order->id)) {
                throw new PrestaShopException('Can\'t load Order object');
            }

            ShopUrl::cacheMainDomainForShop((int)$this->order->id_shop);

            switch (Tools::getValue('hipaystatus')) {
                case 'valid':
                    $this->context->cookie->__set('hipay_success', $this->module->l('The payment has been processed'));

                    if ($this->order->getCurrentState() == Configuration::get('HIPAY_OS_MOTO_PENDING', null, null, 1)) {
                        $orderHistory = new OrderHistory();
                        $orderHistory->id_order = $this->order->id;
                        $orderHistory->changeIdOrderState(
                            Configuration::get('HIPAY_OS_PENDING', null, null, 1),
                            $this->order,
                            true
                        );

                        $orderHistory->addWithemail(true);
                    }

                    break;
                case 'decline':
                    $this->context->cookie->__set('hipay_errors', $this->module->l('The payment has been declined'));
                    break;
                case 'pending':
                    $this->context->cookie->__set('hipay_errors', $this->module->l('The payment is pending'));
                    break;
                case 'exception':
                    $this->context->cookie->__set(
                        'hipay_errors',
                        $this->module->l('There was an error with your payment')
                    );
                    break;
                case 'cancel':
                    $this->context->cookie->__set('hipay_errors', $this->module->l('The payment has been canceled'));
                    break;
            }
        }


        $this->redirectToOrder();
    }
}
