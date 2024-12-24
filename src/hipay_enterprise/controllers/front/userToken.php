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

require_once(dirname(__FILE__) . '/../../classes/helper/HipayCCToken.php');

/**
 * Class Hipay_enterpriseUserTokenModuleFrontController
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class Hipay_enterpriseUserTokenModuleFrontController extends ModuleFrontController
{
    public $auth = true;
    public $ssl = true;

    public function __construct()
    {
        parent::__construct();

        $this->ccToken = new HipayCCToken($this->module);
    }

    public function initContent()
    {
        parent::initContent();
        $context = Context::getContext();

        $path = (_PS_VERSION_ >= '1.7' ? 'module:' .
            $this->module->name .
            '/views/templates/front/account/ps17/user-token-17.tpl'
            : 'account/ps16/user-token-16.tpl');

        $savedCC = $this->ccToken->getSavedCC($context->customer->id);
        if (!$savedCC && empty($this->success)) {
            $this->warning[] = $this->module->l('You have no saved credit/debit card.');
        }
        $this->context->smarty->assign(
            array(
                'HiPay_page' => (_PS_VERSION_ >= '1.7') ? $this->getTemplateVarPage() : "",
                'HiPay_this_path_ssl' => Tools::getShopDomainSsl(true, true) .
                    __PS_BASE_URI__ .
                    'modules/' .
                    $this->module->name .
                    '/',
                'HiPay_savedCC' => $savedCC
            )
        );

        $this->setTemplate($path);
    }

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        $this->display_column_left = false;
        $this->display_column_right = false;
        parent::initContent();

        $context = Context::getContext();

        if (Tools::isSubmit('hipayCCTokenId')) {
            if ($this->ccToken->deleteCC($context->customer->id, Tools::getValue('hipayCCTokenId'))) {
                $this->success[] = $this->module->l('Credit card successfully deleted.');
            } else {
                $this->errors[] = $this->module->l('This credit card doesn\'t exist.');
            }
        }

        $path = (_PS_VERSION_ >= '1.7' ? 'module:' .
            $this->module->name .
            '/views/templates/front/account/ps17/user-token-17.tpl'
            : 'account/ps16/user-token-16.tpl');

        $savedCC = $this->ccToken->getSavedCC($context->customer->id);

        $this->context->smarty->assign(
            array(
                'HiPay_this_path_ssl' => Tools::getShopDomainSsl(true, true) .
                    __PS_BASE_URI__ .
                    'modules/' .
                    $this->module->name .
                    '/',
                'HiPay_page' => (_PS_VERSION_ >= '1.7') ? $this->getTemplateVarPage() : "",
                'HiPay_savedCC' => $savedCC
            )
        );

        $this->setTemplate($path);
    }

    /**
     * Prestashop 1.7
     * @return type
     */
    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();

        $breadcrumb['links'][] = $this->addMyAccountToBreadcrumb();

        return $breadcrumb;
    }

    /**
     * Prestashop 1.7
     * @return boolean
     */
    public function getTemplateVarPage()
    {
        $page = parent::getTemplateVarPage();

        $page['body_classes']['page-customer-account'] = true;

        return $page;
    }
}
