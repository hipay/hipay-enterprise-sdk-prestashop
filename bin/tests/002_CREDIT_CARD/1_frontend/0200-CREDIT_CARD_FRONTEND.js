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

/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : CREDIT CART (DIRECT)
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
 /**********************************************************************************************/

var currentBrandCC = utilsHiPay.getTypeCC(),
    file_path = "002_CREDIT_CARD/1_frontend/0200-CREDIT_CARD_FRONTEND.js";

casper.test.begin('Test Checkout HiPay Enterprise Credit Card with ' + currentBrandCC, function (test) {
    phantom.clearCookies();

    casper.start(baseURL)
        .then(function () {
            checkoutMod.selectItemAndOptions(test);
        })
        .then(function () {
            checkoutMod.personalInformation(test);
        })
        .then(function () {
            checkoutMod.billingInformation(test, 'FR');
        })
        .then(function () {
            checkoutMod.shippingMethod(test);
        })
        .then(function () {
            checkoutMod.fillStepPayment(test);
        })
        .then(function () {
            adminMod.orderResultSuccess(test);

            /* Test it again with another card type */
            if (currentBrandCC == 'visa') {
                utilsHiPay.testOtherTypeCC(test, file_path, 'visa_3ds');
            }
            if (currentBrandCC == 'mastercard') {
                // Waiting AMEX for test account
                //casper.testOtherTypeCC(file_path, 'AMEX');
            }
            if (currentBrandCC == 'mastercard') {
                utilsHiPay.testOtherTypeCC(test, file_path, 'maestro');
            }

            if (currentBrandCC == 'maestro') {
                utilsHiPay.testOtherTypeCC(test, file_path, 'visa_3ds');
            }

            if (currentBrandCC == 'visa_3ds') {
                utilsHiPay.testOtherTypeCC(test, file_path, '');
            }
        })
        .run(function () {
            test.done();
        });
});

