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
 *                       VALIDATION TEST METHOD : IDEAL
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
 /**********************************************************************************************/

var paymentType = "HiPay Enterprise iDeal";

casper.test.begin('Test Checkout ' + paymentType + " with IFrame", function (test) {

    var label;

    phantom.clearCookies();

    casper.start(baseURL)
        .then(function () {
            adminMod.logToBackend(test);
        })
        .then(function () {
            adminMod.gotToHiPayConfiguration(test);
        })
        .then(function () {
            adminMod.activateMethod(test, "ideal");
        })
        .then(function () {
            adminMod.configureOperatingMode(test, "hosted_page");
            adminMod.configureHostedDisplay(test, "iframe");
        })
        .then(function () {
            this.waitForSelector('input[name="ideal_displayName[fr]"]', function success() {
                label = this.getElementAttribute('input[name="ideal_displayName[fr]"]', 'value');
                test.info("Display name in checkout should be :" + label);
            }, function fail() {
                test.assertExists('input[name="ideal_displayName[fr]"]', "Input name exist");
            });
        })
        .then(function () {
            adminMod.activateLocalization(test, 'NL');
        })
        .thenOpen(baseURL, function () {
            checkoutMod.selectItemAndOptions(test);
        })
        .then(function () {
            checkoutMod.personalInformation(test);
        })
        .then(function () {
            checkoutMod.billingInformation(test, 'NL');
        })
        .then(function () {
            checkoutMod.shippingMethod(test);
        })
        .then(function () {
            checkoutMod.selectMethodInCheckout(test, "Payer par " + label, true);
        })
        .then(function () {
            this.wait(10000, function () {
                this.withFrame(0, function () {
                    paymentLibHiPay.fillPaymentFormularByPaymentProduct("ideal", test);
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
