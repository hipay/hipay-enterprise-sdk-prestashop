/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : CARTE CADEAU ONEY
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
 /**********************************************************************************************/

var paymentType = "HiPay Enterprise carte cadeau Oney";

casper.test.begin('Test Checkout ' + paymentType + ' with ' + typeCC, function (test) {
    phantom.clearCookies();

    casper.start(baseURL)
        .then(function () {
            this.refillOneyGiftCard();
        })
        .then(function () {
            this.logToBackend();
        })
        .then(function () {
            this.gotToHiPayConfiguration();
        })
        .then(function () {
            this.configureCaptureMode("automatic");
        })
        .then(function () {
            this.configureSettingsMode("hosted");
            this.configureHostedDisplay("redirect");
        })
        .then(function () {
            this.activateMethod("carte-cadeau");
            this.configureSettingsMode("hosted_page");
            this.waitForSelector('input[name="carte-cadeau_displayName[fr]"]', function success() {
                label = this.getElementAttribute('input[name="carte-cadeau_displayName[fr]"]', 'value');
                test.info("Display name in checkout should be :" + label);
            }, function fail() {
                test.assertExists('input[name="carte-cadeau_displayName[fr]"]', "Input name exist");
            });
        })
        .thenOpen(baseURL, function () {
            this.selectItemAndOptions();
        })
        .then(function () {
            this.personalInformation();
        })
        .then(function () {
            this.billingInformation('FR');
        })
        .then(function () {
            this.shippingMethod();
        })
        .then(function () {
            this.selectMethodInCheckout("Payer par " + label, true);
        })
        /* Fill carte-cadeau formular */
        .then(function () {
            this.fillPaymentFormularByPaymentProduct("carte-cadeau");
        })
        .then(function () {
            this.orderResultSuccess(paymentType);
        })
        .run(function () {
            test.done();
        });
});
