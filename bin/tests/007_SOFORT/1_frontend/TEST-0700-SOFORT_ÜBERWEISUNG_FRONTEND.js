/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : SOFOR ÜBERWEISUNG
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
/**********************************************************************************************/

var paymentType = "HiPay Enterprise Sofort Überweisung";

casper.test.begin('Test Checkout ' + paymentType + ' with ' + typeCC, function(test) {
    phantom.clearCookies();

    casper.start(baseURL)
    .then(function() {
        this.logToBackend();
    })
    .then(function() {
        this.gotToHiPayConfiguration();
        this.activateMethod("sofort-uberweisung");
        this.waitForSelector('input[name="sofort-uberweisung_displayName"]', function success() {
            label = this.getElementAttribute('input[name="sofort-uberweisung_displayName"]', 'value');
            test.info("Display name in checkout should be :" + label);
        }, function fail() {
            test.assertExists('input[name="sofort-uberweisung_displayName"]', "Input name exist");
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
    /* Fill Sofort formular */
    .then(function() {
        this.fillPaymentFormularByPaymentProduct("sofort-uberweisung");
    })
    .then(function() {
        this.orderResultSuccess(paymentType);
    })
    .run(function() {
        test.done();
    });
});