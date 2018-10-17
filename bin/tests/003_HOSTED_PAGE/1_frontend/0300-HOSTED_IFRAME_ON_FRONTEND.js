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
 *                       VALIDATION TEST METHOD : HOSTED
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
 /**********************************************************************************************/

var paymentType = "HiPay Enterprise Hosted Page",
    currentBrandCC = utilsHiPay.getTypeCC(),
    file_path = "003_HOSTED_PAGE/1_frontend/0300-HOSTED_IFRAME_ON_FRONTEND.js";

casper.test.begin('Test Checkout ' + paymentType + ' with Iframe and ' + currentBrandCC, function (test) {
    phantom.clearCookies();

    casper.start(baseURL)
    /* Active Hosted payment method with display iframe */
        .then(function () {
            adminMod.logToBackend(test);
        })
        .then(function () {
            adminMod.gotToHiPayConfiguration(test);
            adminMod.configureOperatingMode(test, "hosted_page");
            adminMod.configureHostedDisplay(test, "iframe");
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
        .then(function () {
            checkoutMod.selectMethodInCheckout(test, labelPayByCard, true);
        })
        /* Fill payment formular inside iframe */
        .then(function () {
            this.wait(10000, function () {
                this.withFrame(0, function () {
                    paymentLibHiPay.fillPaymentFormularByPaymentProduct(currentBrandCC, test);
                });
            });
        })
        .then(function () {
            adminMod.orderResultSuccess(test);
        })
        .run(function () {
            test.done();
        });
});
