/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : IDEAL
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
/**********************************************************************************************/

var paymentType = "HiPay Enterprise iDeal";

casper.test.begin('Test Checkout ' + paymentType + ' with ' + typeCC, function(test) {
    phantom.clearCookies();

    casper.start(baseURL)
    .then(function() {
        authentification.proceed(test);
        this.gotToHiPayConfiguration();
        this.activateMethod("ideal");
        this.configureSettingsMode("hosted_page");
        this.waitForSelector('input[name="ideal_displayName"]', function success() {
            label = this.getElementAttribute('input[name="ideal_displayName"]', 'value');
            test.info("Display name in checkout should be :" + label);
        }, function fail() {
            test.assertExists('input[name="ideal_displayName"]', "Input name exist");
        });
    })
    .then(function() {
        this.activateLocalization('NL');
    })
    .thenOpen(baseURL, function() {
        this.selectItemAndOptions();
    })
    .then(function() {
        this.personalInformation();
    })
    .then(function() {
        this.billingInformation('NL');
    })
    .then(function() {
        this.shippingMethod();
    })
    .then(function() {
        this.selectMethodInCheckout("Payer par " + label,true);
    })
    /* Fill IDeal formular */
    .then(function() {
        this.fillPaymentFormularByPaymentProduct("ideal");
    })
    .then(function() {
        this.orderResultSuccess(paymentType);
    })
    .run(function() {
        test.done();
    });
});