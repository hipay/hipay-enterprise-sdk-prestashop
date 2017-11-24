/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : SISAL
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
/**********************************************************************************************/

var paymentType = "HiPay Enterprise Sisal";

casper.test.begin('Test Checkout ' + paymentType + ' with ' + typeCC, function(test) {
    phantom.clearCookies();

    casper.start(baseURL)
    .then(function() {
        authentification.proceed(test);
    })
    .then(function() {
        this.gotToHiPayConfiguration();
        this.activateMethod("sisal");
        this.waitForSelector('input[name="sisal_displayName"]', function success() {
            label = this.getElementAttribute('input[name="sisal_displayName"]', 'value');
            test.info("Display name in checkout should be :" + label);
        }, function fail() {
            test.assertExists('input[name="sisal_displayName"]', "Input name exist");
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
    /* Fill Sisal formular */
    .then(function() {
        this.fillPaymentFormularByPaymentProduct("sisal");
    })
    .then(function() {
        this.orderResultSuccess(paymentType);
    })
    .run(function() {
        test.done();
    });
});