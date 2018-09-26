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
 *                       VALIDATION TEST METHOD : HOSTED FIELDS
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
 /**********************************************************************************************/

var paymentType = "HiPay Enterprise Hosted Fields",
    currentBrandCC = utilsHiPay.getTypeCC(),
    file_path = "004_HOSTED_FIELDS/1_frontend/0400-HOSTED_FIELDS.js";

casper.test.begin('Test Checkout ' + paymentType + ' and ' + currentBrandCC, function (test) {
    phantom.clearCookies();

    casper.start(baseURL)
    /* Active Hosted payment method with display iframe */
        .then(function () {
            adminMod.logToBackend(test);
        })
        .then(function () {
            adminMod.gotToHiPayConfiguration(test);
            adminMod.configureOperatingMode(test, "hosted_fields");
        })
        .thenOpen(baseURL, function () {
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
        /* Fill payment formular inside iframe */
        .then(function () {
            this.wait(1000, function () {
                checkoutMod.fillStepPayment(test, true);
            });
        })
        .then(function () {
            adminMod.orderResultSuccess(test);

            /* Test it again with another card type */
            if (currentBrandCC == 'visa') {
                utilsHiPay.testOtherTypeCC(test, file_path, 'mastercard');
            }
            if (currentBrandCC == 'mastercard') {
                utilsHiPay.testOtherTypeCC(test, file_path, 'maestro');
            }
        })
        .run(function () {
            test.done();
        });
});
