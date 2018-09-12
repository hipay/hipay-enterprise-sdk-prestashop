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
 *                       VALIDATION TEST METHOD : CARTE CADEAU ONEY
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
 /**********************************************************************************************/

var paymentType = "HiPay Enterprise carte cadeau Oney";

casper.test.begin('Test Checkout ' + paymentType + ' with Iframe', function (test) {

    var label;

    phantom.clearCookies();

    casper.start(baseURL)
        .then(function () {
            utilsLibHiPay.refillOneyGiftCard(test);
        })
        .then(function () {
            adminMod.logToBackend(test);
        })
        .then(function () {
            adminMod.gotToHiPayConfiguration(test);
        })
        .then(function () {
            adminMod.configureCaptureMode(test, "automatic");
        })
        .then(function () {
            adminMod.configureOperatingMode(test, "hosted_page");
            adminMod.configureHostedDisplay(test, "iframe");
        })
        .then(function () {
            adminMod.activateMethod(test, "carte-cadeau");
        })
        .then(function () {
            this.waitForSelector('input[name="carte-cadeau_displayName[fr]"]', function success() {
                label = this.getElementAttribute('input[name="carte-cadeau_displayName[fr]"]', 'value');
                test.info("Display name in checkout should be :" + label);
            }, function fail() {
                test.assertExists('input[name="carte-cadeau_displayName[fr]"]', "Input name exist");
            });
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
            checkoutMod.selectMethodInCheckout(test, "Payer par " + label, true);
        })
        .then(function () {
            this.wait(10000, function () {
                this.withFrame(0, function () {
                    paymentLibHiPay.fillPaymentFormularByPaymentProduct("carte-cadeau", test);
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
