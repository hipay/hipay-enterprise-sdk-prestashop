/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : ING HOME'PAY
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
/**********************************************************************************************/

var paymentType = "HiPay Enterprise ING Home'Pay";

casper.test.begin('Test Checkout ' + paymentType + ' with ' + typeCC, function(test) {
    phantom.clearCookies();

    casper.start(baseURL)
    .then(function() {
        authentification.proceed(test);
        this.gotToHiPayConfiguration();
        this.activateMethod("ing-homepay");
        this.waitForSelector('input[name="ing-homepay_displayName"]', function success() {
            label = this.getElementAttribute('input[name="ing-homepay_displayName"]', 'value');
            test.info("Display name in checkout should be :" + label);
        }, function fail() {
            test.assertExists('input[name="ing-homepay_displayName"]', "Input name exist");
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
    /* Fill ING formular */
    .then(function() {
        this.fillPaymentFormularByPaymentProduct("ing-homepay");
    })
    .then(function() {
        this.orderResultSuccess(paymentType);
    })
    .run(function() {
        test.done();
    });
});