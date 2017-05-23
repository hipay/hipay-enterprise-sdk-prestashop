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
require_once(dirname(__FILE__) . '/../../classes/helper/apiCaller/ApiCaller.php');
require_once(dirname(__FILE__) . '/../../lib/vendor/autoload.php');

use HiPay\Fullservice\Enum\Transaction\TransactionState;

class Hipay_enterpriseRedirectModuleFrontController extends ModuleFrontController {

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

        if ($cart->id == NULL)
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

        //displaying different forms depending of the operating mode chosen in the BO configuration
        switch ($this->module->hipayConfigTool->getConfigHipay()["payment"]["global"]["operating_mode"]) {
            case "hosted_page":
                $this->handleHostedPayment();
                break;
            case "api":
                // if form is sent
                if (Tools::getValue('card-token') && Tools::getValue('card-brand') && Tools::getValue('card-pan')) {
                    $this->handleDirectOrder();
                } else {
                    $context->smarty->assign(array(
                        'status_error' => '200', // Force to ok for first call
                        'cart_id' => $cart->id,
                        'amount' => $cart->getOrderTotal(true, Cart::BOTH),
                        'confHipay' => $this->module->hipayConfigTool->getConfigHipay()
                    ));
                    $path = 'paymentFormApi16.tpl';
                }
                break;
            case "iframe":
                $context->smarty->assign(array(
                    'url' => $this->handleIframe()
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
     * call Api to get forwarding URL 
     */
    private function handleHostedPayment() {
        Tools::redirect(ApiCaller::getHostedPaymentPage($this->module));
    }

    /**
     * return iframe URL
     * @return string
     */
    private function handleIframe() {

        return ApiCaller::getHostedPaymentPage($this->module);
    }

    /**
     * call api and redirect to success or error page 
     */
    private function handleDirectOrder() {
        var_dump(Tools::getValue('card-token'));
        var_dump(Tools::getValue('card-brand'));
        
        $params = array(
            "deviceFingerprint" => Tools::getValue('ioBB'),
            "card-token" => Tools::getValue('card-token'),
            "card-brand" => Tools::getValue('card-brand')
        );

        $response = ApiCaller::requestDirectPost($this->module, $params);

        $acceptUrl = $this->context->link->getModuleLink($this->module->name, 'validation', array(), true);
        $failUrl = $this->context->link->getModuleLink($this->module->name, 'decline', array(), true);
        $pendingUrl = $this->context->link->getModuleLink($this->module->name, 'pending', array(), true);
        $exceptionUrl = $this->context->link->getModuleLink($this->module->name, 'exception', array(), true);
        $forwardUrl = $response->getForwardUrl();
        
        
        switch ($response->getState()) {
            case TransactionState::COMPLETED:
                $redirectUrl = $acceptUrl;
                break;
            case TransactionState::PENDING:
                $redirectUrl = $pendingUrl;
                break;
            case TransactionState::FORWARDING:
                $redirectUrl = $forwardUrl;
                break;
            case TransactionState::DECLINED:
                $reason = $response->getReason();
                $this->module->getLogs()->logsHipay('There was an error request new transaction: ' . $reason['message']);
                $redirectUrl = $failUrl;
                break;
            case TransactionState::ERROR:
                $reason = $response->getReason();
                $this->module->getLogs()->logsHipay('There was an error request new transaction: ' . $reason['message']);
                $redirectUrl = $exceptionUrl;
                break;
            default:
                $redirectUrl = $failUrl;
        }

        Tools::redirect($redirectUrl);
    }

}
