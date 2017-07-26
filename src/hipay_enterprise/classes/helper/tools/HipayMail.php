<?php
/**
 * Helper to send emails for HiPay Module
 *
 * @category    HiPay
 * @package     HiPay\Enterprise
 * @author
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 License
 */

require_once(dirname(__FILE__) . '/../../../translations/HipayStrings.php');

class HipayMail
{

    const PATH_EMAILS_HIPAY = _PS_MODULE_DIR_ . 'hipay_enterprise/mails/';

    /**
     * Send an email to website admin to notify an payment challenged
     *
     * @param $context
     * @param $module
     * @param $order
     */
    public static function sendMailPaymentFraud($context, $module, $order)
    {
        $emails = array();

        // === PREPARE EMAIL VARIABLES == ///
        $templateVars = array(
            '{id_order}' => $order->reference,
            '{order_name}' => $order->getUniqReference()
        );

        // === GET DEFAULT ADMIN INFORMATIONS === ///
        $emails[] = Configuration::get('PS_SHOP_EMAIL');
        $configuration = $module->hipayConfigTool->getConfigHipay();
        $emailBBC = $configuration['fraud']['send_payment_fraud_email_copy_to'] ? : null;
        $copyMethod = $configuration['fraud']['send_payment_fraud_email_copy_method'];

        // === CHECK IF ONE OR MULTIPLE MAIL === //
        if ($copyMethod == hipayForm::TYPE_EMAIL_SEPARATE){
            $emails[] = $emailBBC;
            $emailBBC = null;
        }

        $subject = HipayStrings::SUBJECT_PAYMENT_VALIDATION;

        // === SEND EMAIL === //
        self::sendEmailHipay('fraud',
            $subject,
            $emails,
            $emailBBC,
            $context,
            $module,
            $order,
            $templateVars);

    }

    /**
     * Send an email to website admin to notify an DENY PAYMENT
     *
     * @param $context
     * @param $module
     * @param $order
     */
    public static function sendMailPaymentDeny($context, $module, $order)
    {
        // === GET DEFAULT ADMIN INFORMATIONS === ///
        $customer = new Customer((int) $order->id_customer);

        // === PREPARE EMAIL VARIABLES == ///
        $templateVars = array(
            '{id_order}' => $order->reference,
            '{order_name}' => $order->getUniqReference(),
            '{firstname}'=>  $customer->firstname,
            '{lastname}'=>  $customer->lastname,
        );

        $emails = array($customer->email);
        $subject = HipayStrings::SUBJECT_PAYMENT_DENY;

        // === SEND EMAIL === //
        self::sendEmailHipay('payment_deny',
            $subject,
            $emails,
            null,
            $context,
            $module,
            $order,
            $templateVars
        );
    }


    /**
     *  Generic method to send email when order change of status
     *
     * @param $template string
     * @param $subject string
     * @param $emailsTo array
     * @param $emailBBC string
     * @param $context Prestahsop context
     * @param $module Hipay module instance
     * @param $order Order object
     * @param $templateVars array
     */
    private static function sendEmailHipay($template, $subject, $emailsTo, $emailBBC, $context, $module, $order, $templateVars)
    {
        // === GET DEFAULT ADMIN INFORMATIONS === ///
        $idLang = Configuration::get('PS_LANG_DEFAULT');

        // === SEND EMAIL === ///
        foreach ($emailsTo as $email){
            $mailSuccess = @Mail::Send(
                (int) $idLang,
                $template,
                Context::getContext()->getTranslator()->trans(
                    $subject,
                    array($order->reference),
                    'Emails.Subject',
                    (int)$idLang
                ),
                $templateVars,
                $email,
                '',
                null,
                null,
                null,
                null,
                HipayMail::PATH_EMAILS_HIPAY,
                false,
                (int)$context->shop->id,
                $emailBBC
            );

            if (!$mailSuccess) {
                $module->getLogs()->errorLogsHipay( 'An error occured during email sending to ' .  $email );
            }
        }
    }

}
