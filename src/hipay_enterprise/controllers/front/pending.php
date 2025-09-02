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

require_once(dirname(__FILE__) . '/../../classes/helper/HipayHelper.php');

/**
 * Class Hipay_enterprisePendingModuleFrontController
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class Hipay_enterprisePendingModuleFrontController extends ModuleFrontController
{
    /** @var Hipay_entreprise */
    public $module;

    const PATH_TEMPLATE_PS_17 = '/views/templates/front/paymentReturn/ps17/pending-17.tpl';
    const PATH_TEMPLATE_PS_16 = 'paymentReturn/ps16/pending-16.tpl';

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        $this->display_column_left = false;
        $this->display_column_right = false;
        parent::initContent();

        $cart = HipayHelper::getCustomerCart($this->module);

        HipayHelper::unsetCart($cart);

        // Get SDK script data with SRI support
        $sdkData = $this->module->getSDKScriptData();

        // Pass SDK data to template
        $this->context->smarty->assign([
            'HiPay_sdk_script_tag' => $sdkData['sdk_script_tag'],
            'hipay_enterprise_tpl_dir' => _PS_MODULE_DIR_ . $this->module->name . '/views/templates',
        ]);

        $path = (_PS_VERSION_ >= '1.7' ? 'module:' .
            $this->module->name .
            self::PATH_TEMPLATE_PS_17 : self::PATH_TEMPLATE_PS_16);
        $this->module->getLogs()->logInfos("# Pending payment");
        $this->setTemplate($path);
    }
}
