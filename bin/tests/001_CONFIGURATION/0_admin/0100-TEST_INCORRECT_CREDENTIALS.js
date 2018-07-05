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
 *
 *  @purpose    : This scenario makes it possible for the entry of the API identifiers to be taken into account for the tokenization and for the placement of the orders.
 *  @Scenario   : Entering false identifiers and test a command via API (Credit card), if the payment fails then the test is successful
 *  @screen     : Module Settings
 *
 /**********************************************************************************************/

var paymentType = "HiPay Enterprise Credit Card",
    currentBrandCC = utilsHiPay.getTypeCC();

casper.test.begin('Test filling wrong credentials and pay ' + paymentType + ' with ' + currentBrandCC, function (test) {
    phantom.clearCookies();
    var initialCredential;

    casper.start(baseURL)
        .then(function () {
            adminMod.logToBackend(test);
        })
        .then(function () {
            adminMod.gotToHiPayConfiguration(test);
        })
        .then(function () {
            this.echo("Filling wrong credentials in Module Settings", "INFO");
            this.waitForSelector('form#account_form input[name="api_username_sandbox"]', function successful() {
                initialCredential = adminMod.getCredentials();
                test.info("Initial credential for api_username_sandbox was :" + initialCredential);
                adminMod.fillCredentials(test, "WRONG CREDENTIALS");

            }, function fail() {
                this.assertExists(
                    'form#account_form input[name="api_username_sandbox"]',
                    'Field "api_username_sandbox" exists'
                );
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
            checkoutMod.fillStepPayment();
        })
        .then(function () {
            this.echo("Checking order error ... ", "INFO");
            this.waitForSelector("form#tokenizerForm div#error-js ul li.error", function success() {
                test.assertHttpStatus(200, "Correct HTTP Status Code 200");
                test.assertExists("form#tokenizerForm div#error-js ul li.error", "Error message exists. Payment error");
            }, function fail() {
                test.assertExists("form#tokenizerForm div#error-js ul li.error", "Error message is not present");
            }, 30000);
        })
        .thenOpen(baseURL, function () {
            adminMod.logToBackend();
            adminMod.gotToHiPayConfiguration();
        })
        .then(function () {
            /* Restore credentials with initials credentials  */
            this.echo("Restore true credentials in Module Settings");
            adminMod.fillCredentials(test, initialCredential);
        })
        .run(function () {
            test.done();
        });
});
