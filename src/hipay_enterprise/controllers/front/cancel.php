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

require_once(dirname(__FILE__) . '/../../classes/helper/hipayHelper.php');

/**
 * Class Hipay_enterpriseCancelModuleFrontController
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link 	https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class Hipay_enterpriseCancelModuleFrontController extends ModuleFrontController
{
    const PATH_TEMPLATE_PS_17 = '/views/templates/front/paymentReturn/cancel17.tpl';
    const PATH_TEMPLATE_PS_16 =  'paymentReturn/cancel.tpl';

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        $this->display_column_left = false;
        $this->display_column_right = false;
        parent::initContent();

        $context = Context::getContext();

        if (!(bool)$this->module->hipayConfigTool->getConfigHipay(
        )["payment"]["global"]["regenerate_cart_on_decline"]
        ) {
            HipayHelper::unsetCart();
        }

        $path = (_PS_VERSION_ >= '1.7' ? 'module:' . $this->module->name . self::PATH_TEMPLATE_PS_17 : self::PATH_TEMPLATE_PS_16);

        $this->module->getLogs()->logInfos("# Cancel payment : Your order has been canceled");
        $this->setTemplate($path);
    }
}
