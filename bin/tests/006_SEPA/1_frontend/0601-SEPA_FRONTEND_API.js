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
 *                       VALIDATION TEST METHOD : SEPA DIRECT DEBIT
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
 /**********************************************************************************************/

var paymentType = "HiPay Enterprise SEPA Direct Debit";

casper.test.begin('Test Checkout ' + paymentType, function (test) {

    phantom.clearCookies();

    var label;

    casper.start(baseURL)
        .then(function () {
            adminMod.logToBackend(test);
        })
        .then(function () {
            adminMod.gotToHiPayConfiguration(test);
        })
        .then(function () {
            adminMod.activateMethod(test, "sdd");
        })
        .then(function () {
            adminMod.configureOperatingMode(test, "direct_post");
        })
        .then(function () {
            this.waitForSelector('input[name="ideal_displayName[fr]"]', function success() {
                label = this.getElementAttribute('input[name="sdd_displayName[fr]"]', 'value');
                test.info("Display name in checkout should be :" + label);
            }, function fail() {
                test.assertExists('input[name="sdd_displayName[fr]"]', "Input name exist");
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
            checkoutMod.selectMethodInCheckout(test, "Payer par " + label, false);
        })
        .then(function () {
            this.echo("Filling SEPA Formular ...", "INFO");

            this.waitUntilVisible('#sdd-hipay', function success() {
                this.fillSelectors('form#sdd-hipay', {
                    'select[name="gender"]': "1",
                    'input[name="firstname"]': "TEST",
                    'input[name="lastname"]': "TEST",
                    'input[name="iban"]': parametersLibHiPay.ibanNumber.fr,
                    'input[name="issuer_bank_id"]': parametersLibHiPay.bicNumber.fr,
                    'input[name="bank_name"]': "BANK TEST"
                }, false);

                this.click('form#conditions-to-approve input');
                this.click("div#payment-confirmation button");
            }, function fail() {
                test.assertExists('#sdd-hipay', "Field Business Identifier exists");
            });
        })
        .then(function () {
            adminMod.orderResultSuccess(test);
        })
        .run(function () {
            test.done();
        });

});
