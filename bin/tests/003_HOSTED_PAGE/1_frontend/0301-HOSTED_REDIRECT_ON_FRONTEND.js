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
    currentBrandCC = utilsHiPay.getTypeCC();

casper.test.begin('Test Checkout ' + paymentType + ' with redirect', function (test) {
    phantom.clearCookies();

    casper.start(baseURL)
        .then(function () {
            adminMod.logToBackend(test);
        })
        .then(function () {
            adminMod.gotToHiPayConfiguration(test);
            adminMod.configureOperatingMode(test, "hosted_page");
            adminMod.configureHostedDisplay(test, "redirect");
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
        .then(function () {
            paymentLibHiPay.fillPaymentFormularByPaymentProduct(currentBrandCC, test);
        })
        .then(function () {
            adminMod.orderResultSuccess(test);
        })
        .run(function () {
            test.done();
        });
});
