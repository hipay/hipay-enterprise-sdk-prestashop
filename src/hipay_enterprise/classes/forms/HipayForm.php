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

require_once(dirname(__FILE__) . '/HipayFormInput.php');
require_once(dirname(__FILE__) . '/../apiHandler/ApiHandler.php');

/**
 * Form builder
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class HipayForm extends HipayFormInput
{
    protected $context = false;
    protected $helper = false;
    protected $module = false;
    public $name = false;
    public $configHipay;

    const TYPE_EMAIL_BCC = 'bcc';
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

        $this->helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false);
        $this->helper->currentIndex .= '&' .
            http_build_query(
                array('configure' => $this->module->name, 'tab_module' => 'payments_gateways', 'module_name' => $this->module->name,)
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
     * fraud payment form
     * @return type
     */
    public function getFraudForm()
    {
        $form = array();

        $this->helper->tpl_vars['fields_value'] = $this->getFraudFormValues();

        $form['form']['legend'] = array('title' => $this->module->l('Payment fraud email','HipayForm'));

        $form['form']['input'][] = $this->generateFormNotice();

        $form['form']['input'][] = $this->generateInputText(
            "send_payment_fraud_email_copy_to",
            $this->module->l('Copy To', 'HipayForm'),
            array(
                'desc' => $this->module->l(
                    'Enter a valid email, during a transaction challenged an email will be sent to this address',
                    'HipayForm'
                )
            )
        );

        $form['form']['input'][] = $this->generateInputSelect(
            "send_payment_fraud_email_copy_method",
            $this->module->l('Copy Method', 'HipayForm'),
            array(
                'desc' => "<ul class='hipay-notice-list'><li><b>Bcc</b> :" .
                    $this->module->l('The recipient will be in copy of the email', 'HipayForm') .
                    "</li><li><b>".$this->module->l('Separate email', 'HipayForm')."</b> :" .
                    $this->module->l('Two mails are sent', 'HipayForm') .
                    "</li></ul>", "options" =>
                array(
                    "query" => array(
                        array(
                            "send_payment_fraud_email_copy_method_id" => $this::TYPE_EMAIL_BCC,
                            "name" => $this->module->l(
                                'Bcc',
                                'HipayForm'
                            )
                        ),
                        array(
                            "send_payment_fraud_email_copy_method_id" => $this::TYPE_EMAIL_SEPARATE,
                            "name" => $this->module->l('Separate email', 'HipayForm')
                        )
                    ),
                    "id" => "send_payment_fraud_email_copy_method_id",
                    "name" => "name"
                )
            )
        );

        $form['form']['buttons'][] = $this->generateSubmitButton(
            $this->module->l('Save configuration changes', 'HipayForm'),
            array('name' => 'fraudSubmit', 'icon' => 'process-icon-save')
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
            "input_split" => $this->generateHtmlNoticeAdmin(
                $this->module->l('When a transaction is likely to be a fraud then an email is sent to the contact email from your shop as well as to an additional sender. Here you can configure the additional recipient email','HipayForm')
            ),
            "payment_fraud_email_sender" => "",
            "send_payment_fraud_email_copy_to" => "",
            "send_payment_fraud_email_copy_method" => ""
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
    protected function generateHtmlNoticeAdmin($content)
    {
        return "<div class='notice-hipay-admin alert alert-info'>" . $content . "</div>";
    }
}
