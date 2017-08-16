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

require_once(dirname(__FILE__) . '/hipayFormInput.php');
require_once(dirname(__FILE__) . '/../apiHandler/ApiHandler.php');
require_once(dirname(__FILE__) . '/../../../translations/HipayStrings.php');

/**
 * Form builder
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link 	https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class HipayForm extends HipayFormInput
{
    protected $context = false;
    protected $helper = false;
    protected $module = false;
    public $name = false;
    public $configHipay;

    const TYPE_EMAIL_BBC = 'separate_email';
    const TYPE_EMAIL_SEPARATE = 'separate_email';


    public function __construct($module_instance)
    {
        // Requirements
        $this->context = Context::getContext();
        $this->module = $module_instance;
        $this->name = $module_instance->name;
        // init config hipay
        $this->configHipay = $module_instance->hipayConfigTool->getConfigHipay();

        // Form
        $this->helper = new HelperForm();

        $this->helper->currentIndex = $this->context->link->getAdminLink(
            'AdminModules',
            false
        );
        $this->helper->currentIndex .= '&' . http_build_query(
                array(
                    'configure' => $this->module->name,
                    'tab_module' => 'payments_gateways',
                    'module_name' => $this->module->name,
                )
            );

        $this->helper->module = $this;
        $this->helper->show_toolbar = false;
        $this->helper->token = Tools::getAdminTokenLite('AdminModules');

        $this->helper->tpl_vars = array(
            'id_language' => $this->context->language->id,
            'languages' => $this->context->controller->getLanguages()
        );

        return $this->helper;
    }

    public function generateForm($form)
    {
        return $this->helper->generateForm($form);
    }

    /**
     * global payment form
     * @return type
     */
    public function getGlobalPaymentMethodsForm()
    {
        $form = array();

        $this->helper->tpl_vars['fields_value'] = $this->getGlobalPaymentMethodsFormValues();

        $form['form']['input'][] = $this->generateInputSelect(
            "operating_mode",
            $this->module->l(
                'Operating mode',
                'HipayForm'
            ),
            array(
                "options" => array(
                    "query" => array(
                        array(
                            "operating_mode_id" => Apihandler::DIRECTPOST,
                            "name" => $this->module->l(
                                "Api",
                                'HipayForm'
                            )
                        ),
                        array(
                            "operating_mode_id" => Apihandler::HOSTEDPAGE,
                            "name" => $this->module->l(
                                "Hosted page",
                                'HipayForm'
                            )
                        ),
                        array(
                            "operating_mode_id" => Apihandler::IFRAME,
                            "name" => $this->module->l(
                                "Iframe",
                                'HipayForm'
                            )
                        )
                    ),
                    "id" => "operating_mode_id",
                    "name" => "name"
                )
            )
        );

        $form['form']['input'][] = $this->generateInputSelect(
            "iframe_hosted_page_template",
            $this->module->l(
                'Iframe/hosted page template',
                'HipayForm'
            ),
            array(
                'hint' => $this->module->l(
                    'Basic hosted page or iFrame template.',
                    'HipayForm'
                ),
                "options" => array(
                    "query" => array(
                        array(
                            "iframe_hosted_page_template_id" => "basic-js",
                            "name" => "basic-js"
                        ),
                        array(
                            "iframe_hosted_page_template_id" => "basic",
                            "name" => "basic"
                        ),
                    ),
                    "id" => "iframe_hosted_page_template_id",
                    "name" => "name"
                )
            )
        );

        $form['form']['input'][] = $this->generateInputSelect(
            "display_card_selector",
            $this->module->l(
                'Display card selector',
                'HipayForm'
            ),
            array(
                'hint' => $this->module->l(
                    'Display card selector on iFrame or hosted page.',
                    'HipayForm'
                ),
                "options" => array(
                    "query" => array(
                        array(
                            "display_card_selector_id" => 1,
                            "name" => $this->module->l(
                                'Show card selector',
                                'HipayForm'
                            )
                        ),
                        array(
                            "display_card_selector_id" => 0,
                            "name" => $this->module->l(
                                'Do not show card selector',
                                'HipayForm'
                            )
                        ),
                    ),
                    "id" => "display_card_selector_id",
                    "name" => "name"
                )
            )
        );

        $form['form']['input'][] = $this->generateInputText(
            "css_url",
            $this->module->l(
                'CSS url',
                'HipayForm'
            ),
            array(
                'hint' => $this->module->l(
                    'URL to your CSS (style sheet) to customize your hosted page or iFrame (Important: the HTTPS protocol is required)',
                    'HipayForm'
                ),
            )
        );

        $form['form']['input'][] = $this->generateInputSelect(
            "capture_mode",
            $this->module->l(
                'Switch to capture in',
                'HipayForm'
            ),
            array(
                "options" => array(
                    "query" => array(
                        array(
                            "capture_mode_id" => "manual",
                            "name" => $this->module->l(
                                'Manual mode',
                                'HipayForm'
                            )
                        ),
                        array(
                            "capture_mode_id" => "automatic",
                            "name" => $this->module->l(
                                'Automatic mode',
                                'HipayForm'
                            )
                        ),
                    ),
                    "id" => "capture_mode_id",
                    "name" => "name"
                )
            )
        );

        $form['form']['input'][] = $this->generateSwitchButton(
            "card_token",
            $this->module->l(
                'Allow memorization of card tokens',
                'HipayForm'
            ),
            array(
                'hint' => $this->module->l(
                    'Allow users to save their card and use saved cards.',
                    'HipayForm'
                ),
            )
        );

        $form['form']['buttons'][] = $this->generateSubmitButton(
            $this->module->l(
                'Save',
                'HipayForm'
            ),
            array(
                'name' => 'submitGlobalPaymentMethods',
                'icon' => 'process-icon-save',
            )
        );


        $form['form']['input'][] = $this->generateSwitchButton(
            "activate_basket",
            $this->module->l(
                'Activate basket',
                'HipayForm'
            ),
            array(
                'hint' => $this->module->l(
                    'Send cart informations on HiPay API call.',
                    'HipayForm'
                ),
                'desc' => "<i class='icon icon-warning text-danger'></i> " . $this->module->l(
                        "If 'Round on the total' is activated in prestashop configuration, cart will not be sent and payment method that force cart to be send will be disabled."
                    ),
            )
        );

        $form['form']['input'][] = $this->generateSwitchButton(
            "regenerate_cart_on_decline",
            $this->module->l(
                'Keep cart when payment fail',
                'HipayForm'
            )
        );

        return $this->helper->generateForm(array($form));
    }

    /**
     * get value for global payment methods form
     * @return type
     */
    public function getGlobalPaymentMethodsFormValues()
    {
        //init fields
        $values = array(
            "operating_mode" => "",
            "iframe_hosted_page_template" => "",
            "display_card_selector" => "",
            "css_url" => "",
            "capture_mode" => "",
            "card_token" => "",
            "activate_basket" => "",
            "regenerate_cart_on_decline" => ""
        );

        // get field value from POST request or config (this way the displayed value is always the good one)
        foreach ($values as $key => $value) {
            if (is_bool(Tools::getValue($key)) && !Tools::getValue($key)) {
                $values[$key] = $this->configHipay["payment"]["global"][$key];
            } else {
                $values[$key] = Tools::getValue($key);
            }
        }

        return $values;
    }

    /**
     * fraud payment form
     * @return type
     */
    public function getFraudForm()
    {
        $form = array();

        $this->helper->tpl_vars['fields_value'] = $this->getFraudFormValues();

        $form['form']['legend'] = array(
            'title' => $this->module->l('Payment fraud email' ),
        );

        $form['form']['input'][] = $this->generateFormNotice();

        $form['form']['input'][] = $this->generateInputText(
            "send_payment_fraud_email_copy_to",
            $this->module->l(
                'Copy To',
                'HipayForm'
            ),
            array(
                'desc' => HipayStrings::DESC_COPY_TO
            )
        );

        $form['form']['input'][] = $this->generateInputSelect(
            "send_payment_fraud_email_copy_method",
            $this->module->l(
                'Copy Method',
                'HipayForm'
            ),
            array(
                'desc' => "<ul class='hipay-notice-list'><li><b>Bbc</b> :" .
                    $this->module->l(HipayStrings::DESC_COPY_METHOD_BBC) .
                    "</li><li><b>Separate Email</b> :" . $this->module->l(HipayStrings::DESC_COPY_METHOD_SEPARATE) . "</li></ul>",
                "options" => array(
                    "query" => array(
                        array(
                            "send_payment_fraud_email_copy_method_id" => $this::TYPE_EMAIL_BBC,
                            "name" => $this->module->l(
                                'Bcc',
                                'HipayForm'
                            ),
                        ),
                        array(
                            "send_payment_fraud_email_copy_method_id" => $this::TYPE_EMAIL_SEPARATE,
                            "name" => $this->module->l(
                                'Separate email',
                                'HipayForm'
                            )
                        ),
                    ),
                    "id" => "send_payment_fraud_email_copy_method_id",
                    "name" => "name"
                )
            )
        );

        $form['form']['buttons'][] = $this->generateSubmitButton(
            $this->module->l(
                'Save configuration changes',
                'HipayForm'
            ),
            array(
                'name' => 'fraudSubmit',
                'icon' => 'process-icon-save',
            )
        );

        return $this->helper->generateForm(array($form));
    }

    /**
     * Generate Form for Fraud payment
     *
     * @return type
     */
    public function getFraudFormValues()
    {
        // init field
        $values = array(
            "input_split" => $this->generateHtmlNoticeAdmin(HipayStrings::NOTICE_FRAUD),
            "payment_fraud_email_sender" => "",
            "send_payment_fraud_email_copy_to" => "",
            "send_payment_fraud_email_copy_method" => "",
        );

        // get field value from POST request or config (this way the displayed value is always the good one)
        foreach ($values as $key => $value) {
            if (is_bool(Tools::getValue($key)) && !Tools::getValue($key) && $key != "input_split") {
                $values[$key] = $this->configHipay["fraud"][$key];
            } elseif ($key != "input_split") {
                $values[$key] = Tools::getValue($key);
            }
        }

        return $values;
    }

    /**
     *  Generate Html content for notice in Admin panels
     *
     * @param $content
     * @return string Html Content
     */
    protected function generateHtmlNoticeAdmin($content){
        return "<div class='notice-hipay-admin alert alert-info'>" . $content . "</div>";
    }
}
