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
require_once(dirname(__FILE__) . '/../../classes/helper/forms/hipayForm.php');

class Hipay_enterpriseRedirectlocalModuleFrontController extends ModuleFrontController
{

    /**
     * display payment form API/Iframe/HostedPage(PS16)
     * @return type
     */
    public function initContent()
    {
        $this->display_column_left = false;
        $this->display_column_right = false;
        parent::initContent();

        $context = Context::getContext();
        $cart = $context->cart;

        $this->apiHandler = new ApiHandler(
            $this->module,
            $this->context
        );

        if ($cart->id == null) {
            Tools::redirect('index.php?controller=order');
        }

        $params = array("productlist" => Tools::getValue("method"), "iframe" => true,
            "deviceFingerprint" => null);

        if (!$params) {
            Tools::redirect('index.php?controller=order');
        }

        $mode = $this->module->hipayConfigTool->getConfigHipay()["payment"]["global"]["operating_mode"];
        $method = Tools::getValue("method");

        // check if hosted payment is forced
        if ($this->module->hipayConfigTool->getConfigHipay()["payment"]["local_payment"][$method]["forceHpayment"]) {
            $mode = Apihandler::HOSTEDPAGE;
        }

        $params["deviceFingerprint"] = Tools::getValue('ioBB');
        $params["method"] = $method;

        $context->smarty->assign(
            array(
                'nbProducts' => $cart->nbProducts(),
                'cust_currency' => $cart->id_currency,
                'currencies' => $this->module->getCurrency((int)$cart->id_currency),
                'total' => $cart->getOrderTotal(
                    true,
                    Cart::BOTH
                ),
                'this_path' => $this->module->getPathUri(),
                'this_path_bw' => $this->module->getPathUri(),
                'this_path_ssl' => Tools::getShopDomainSsl(
                        true,
                        true
                    ) . __PS_BASE_URI__ . 'modules/' . $this->module->name . '/',
                'hipay_enterprise_tpl_dir' => _PS_MODULE_DIR_ . $this->module->name . '/views/templates',
                'action' => $this->context->link->getModuleLink(
                    $this->module->name,
                    'redirectlocal',
                    array("method" => $method),
                    true
                )
            )
        );


        switch ($mode) {
            case Apihandler::HOSTEDPAGE:
                $this->apiHandler->handleLocalPayment(
                    Apihandler::HOSTEDPAGE,
                    $params
                );
                break;
            case Apihandler::DIRECTPOST:
                // if electronic signature is on and payment force hpayment when electronic signature is on  OR form is submit OR there's no additional fields
                if (Tools::isSubmit("localSubmit") || empty(
                    $this->module->hipayConfigTool->getConfigHipay(
                    )["payment"]["local_payment"][$method]["additionalFields"]
                    )
                    || ($this->module->hipayConfigTool->getConfigHipay(
                        )["payment"]["local_payment"][$method]["forceHpaymentOnElectronicSignature"]
                        && $this->module->hipayConfigTool->getConfigHipay(
                        )["payment"]["global"]["electronic_signature"])
                ) {
                    // if form submit
                    if (Tools::isSubmit("localSubmit")) {
                        foreach ($this->module->hipayConfigTool->getConfigHipay(
                        )["payment"]["local_payment"][$method]["additionalFields"]["formFields"] as $name => $field) {
                            $params[$name] = Tools::getValue($name);
                        }
                    }
                    // set authentication_indicator depending if lectronic signature is on or not
                    if ($this->module->hipayConfigTool->getConfigHipay()["payment"]["global"]["electronic_signature"]) {
                        $params["authentication_indicator"] = 1;
                    } else {
                        $params["authentication_indicator"] = 0;
                    }

                    $this->apiHandler->handleLocalPayment(
                        Apihandler::DIRECTPOST,
                        $params
                    );
                } else {
                    // display form
                    $context->smarty->assign(
                        array(
                            'status_error' => '200', // Force to ok for first call
                            'cart_id' => $cart->id,
                            'amount' => $cart->getOrderTotal(
                                true,
                                Cart::BOTH
                            ),
                            'confHipay' => $this->module->hipayConfigTool->getConfigHipay(),
                            'methodFields' => $this->module->hipayConfigTool->getConfigHipay(
                            )["payment"]["local_payment"][$method]["additionalFields"]["formFields"]
                        )
                    );
                    $path = 'paymentLocalForm16.tpl';
                }
                break;
            case Apihandler::IFRAME:
                $context->smarty->assign(
                    array(
                        'url' => $this->apiHandler->handleLocalPayment(
                            Apihandler::IFRAME,
                            $params
                        )
                    )
                );
                $path = (_PS_VERSION_ >= '1.7' ? 'module:' . $this->module->name . '/views/templates/front/17'
                        : '16') . 'paymentFormIframe.tpl';
                break;
            default:
                break;
        }

        return $this->setTemplate($path);
    }

    /**
     * add JS and CSS in page
     */
    public function setMedia()
    {
        parent::setMedia();
        $this->addJS(array(_MODULE_DIR_ . 'hipay_enterprise/views/js/card-js.min.js'));
        $this->addJS(array(_MODULE_DIR_ . 'hipay_enterprise/views/js/devicefingerprint.js'));
        $this->addCSS(array(_MODULE_DIR_ . 'hipay_enterprise/views/css/card-js.min.css'));
        $this->context->controller->addJS(
            array(_MODULE_DIR_ . 'hipay_enterprise/lib/bower_components/hipay-fullservice-sdk-js/dist/hipay-fullservice-sdk.min.js')
        );
    }
}
