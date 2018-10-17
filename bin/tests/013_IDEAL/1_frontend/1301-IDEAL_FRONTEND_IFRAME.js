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
        /* Fill IDeal formular */
        .then(function () {
            this.echo("Filling Business Identifier BIC...", "INFO");

            this.waitForSelector('input#ideal-issuer_bank_id', function success() {
                this.fillSelectors('form#ideal-hipay', {
                    'input[name="issuer_bank_id"]': 'INGBNL2A'
                }, false);

                this.click("div#payment-confirmation button");
            }, function fail() {
                test.assertExists('input#ideal-issuer_bank_id', "Field Business Identifier exists");
            });
        })
        .then(function () {
            this.echo("Filling payment formular...", "INFO");
            paymentLibHiPay.payIDeal(test);
        })
        .then(function () {
            adminMod.orderResultSuccess(test);
        })
        .run(function () {
            test.done();
        });
});
