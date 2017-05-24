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
require_once(dirname(__FILE__) . '/../../classes/helper/apiHandler/ApiHandler.php');

class Hipay_enterpriseRedirectlocalModuleFrontController extends ModuleFrontController {

    /**
     * display payment form API/Iframe/HostedPage(PS16)
     * @return type
     */
    public function initContent() {

        $this->display_column_left = false;
        $this->display_column_right = false;
        parent::initContent();

        $context = Context::getContext();
        $cart = $context->cart;

        $this->apiHandler = new ApiHandler($this->module, $this->context);

        if ($cart->id == NULL)
            Tools::redirect('index.php?controller=order');

        $params = array("paymentproduct" => Tools::getValue("method"), "iframe" => true);

        if (!$params)
            Tools::redirect('index.php?controller=order');

        switch ($this->module->hipayConfigTool->getConfigHipay()["payment"]["global"]["operating_mode"]) {
            case "hosted_page":
                $this->apiHandler->handleLocalPayment(Apihandler::HOSTEDPAGE, $params);
                break;
            case "api":
                // if form is sent
//                if (Tools::getValue('card-token') && Tools::getValue('card-brand') && Tools::getValue('card-pan')) {
//                    $this->apiHandler->handleLocalPayment(Apihandler::DIRECTPOST, $params);
//                } else {
//                    $context->smarty->assign(array(
//                        'status_error' => '200', // Force to ok for first call
//                        'cart_id' => $cart->id,
//                        'amount' => $cart->getOrderTotal(true, Cart::BOTH),
//                        'confHipay' => $this->module->hipayConfigTool->getConfigHipay()
//                    ));
//                    $path = 'paymentFormApi16.tpl';
//                }
                break;
            case "iframe":
                $context->smarty->assign(array(
                    'url' => $this->apiHandler->handleLocalPayment(Apihandler::IFRAME, $params)
                ));
                $path = (_PS_VERSION_ >= '1.7' ? 'module:' . $this->module->name . '/views/templates/front/17' : '16') . 'paymentFormIframe.tpl';
                break;
            default :

                break;
        }

        return $this->setTemplate($path);
    }

    /**
     * add JS and CSS in page
     */
    public function setMedia() {
        parent::setMedia();
        $this->addJS(array(_MODULE_DIR_ . 'hipay_enterprise/views/js/card-js.min.js'));
        $this->addJS(array(_MODULE_DIR_ . 'hipay_enterprise/views/js/devicefingerprint.js'));
        $this->addCSS(array(_MODULE_DIR_ . 'hipay_enterprise/views/css/card-js.min.css'));
        $this->context->controller->addJS(array(_MODULE_DIR_ . 'hipay_enterprise/lib/bower_components/hipay-fullservice-sdk-js/dist/hipay-fullservice-sdk.min.js'));
    }

}
