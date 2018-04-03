/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : BELFIUS/DEXIA DIRECT NET
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
/**********************************************************************************************/

var paymentType = "HiPay Enterprise Belfius / Dexia Direct Net";

casper.test.begin('Test Checkout ' + paymentType + ' with ' + typeCC, function(test) {
    phantom.clearCookies();

    casper.start(baseURL)
    .then(function() {
        this.logToBackend();
    })
    .then(function() {
        this.gotToHiPayConfiguration();
    })
    .then(function() {
        this.activateMethod("dexia-directnet");
    })
    .then(function() {
        this.waitForSelector('input[name="dexia-directnet_displayName[fr]"]', function success() {
            label = this.getElementAttribute('input[name="dexia-directnet_displayName[fr]"]', 'value');
            test.info("Display name in checkout should be :" + label);
        }, function fail() {
            test.assertExists('input[name="dexia-directnet_displayName[fr]"]', "Input name exist");
        });
    })
    .thenOpen(baseURL, function() {
        this.selectItemAndOptions();
    })
    .then(function() {
        this.personalInformation();
    })
    .then(function() {
        this.billingInformation('FR');
    })
    .then(function() {
        this.shippingMethod();
    })
    .then(function() {
        this.selectMethodInCheckout("Payer par " + label,true);
    })
    /* Fill Belfius formular */
    .then(function() {
        this.fillPaymentFormularByPaymentProduct("dexia-directnet");
    })
    .then(function() {
        this.orderResultSuccess(paymentType);
    })
    .run(function() {
        test.done();
    });
});
