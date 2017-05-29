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
require_once(dirname(__FILE__) . '/../../lib/vendor/autoload.php');

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

        $params = array("productlist" => Tools::getValue("method"), "iframe" => true, "deviceFingerprint" => null);

        if (!$params)
            Tools::redirect('index.php?controller=order');

        $products = $this->getSDKPaymentMethod();

        //verify if the payment method exist in the SDK
        if (!in_array(Tools::getValue("method"), $products))
            Tools::redirect('index.php?controller=order');


        $context->smarty->assign(array(
            'nbProducts' => $cart->nbProducts(),
            'cust_currency' => $cart->id_currency,
            'currencies' => $this->module->getCurrency((int) $cart->id_currency),
            'total' => $cart->getOrderTotal(true, Cart::BOTH),
            'this_path' => $this->module->getPathUri(),
            'this_path_bw' => $this->module->getPathUri(),
            'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->module->name . '/',
            'hipay_enterprise_tpl_dir' => _PS_MODULE_DIR_ . $this->module->name . '/views/templates/hook'
        ));


        switch ($this->module->hipayConfigTool->getConfigHipay()["payment"]["global"]["operating_mode"]) {
            case "hosted_page":
                $this->apiHandler->handleLocalPayment(Apihandler::HOSTEDPAGE, $params);
                break;
            case "api":
                $params["deviceFingerprint"] = Tools::getValue('ioBB');
                $this->apiHandler->handleLocalPayment(Apihandler::DIRECTPOST, $params);
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

    /**
     * return all code of Payment method present in the SDK
     * @return type
     */
    private function getSDKPaymentMethod() {
        $collection = HiPay\Fullservice\Data\PaymentProduct\Collection::getItems();

        $paymentName = array();

        foreach ($collection as $payment) {
            $paymentName[] = $payment->getProductCode();
        }

        return $paymentName;
    }

}
