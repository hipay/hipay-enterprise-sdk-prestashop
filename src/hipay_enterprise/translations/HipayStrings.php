<?php

abstract class HipayStrings {

    //=========================================//
    //              FRAUD VIEW                 //
    //=========================================//
    const NOTICE_FRAUD = 'When a transaction is likely to be a fraud then an email is sent to the contact email from your shop as well as to an additional sender. Here you can configure the additional recipient email';
    const DESC_COPY_METHOD_BBC = "The recipient will be in copy of the email";
    const DESC_COPY_METHOD_SEPARATE = "Two mails are sent";
    const DESC_COPY_TO = "Enter a valid email, during a transaction challenged an email will be sent to this address";

    //=========================================//
    //              FRAUD MAILS                 //
    //=========================================//
    const SUBJECT_PAYMENT_VALIDATION = 'A payment transaction is awaiting validation for the order %s';
    const SUBJECT_PAYMENT_DENY = 'Refused payment for order %s';

    //=========================================//
    //              PAYMENT PAGE               //
    //=========================================//
    const HOSTED_REDIRECT_MESSAGE = 'You will be redirected to an external payment page';
}