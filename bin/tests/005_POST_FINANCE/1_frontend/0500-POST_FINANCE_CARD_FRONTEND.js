/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : POST FINANCE CARD
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
/**********************************************************************************************/

var paymentType = "HiPay Fullservice PostFinance Card";

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
        this.activateMethod("postfinance-card");
    })
    .then(function() {
        this.waitForSelector('input[name="postfinance-card_displayName[fr]"]', function success() {
            label = this.getElementAttribute('input[name="postfinance-card_displayName[fr]"]', 'value');
            test.info("Display name in checkout should be :" + label);
        }, function fail() {
            test.assertExists('input[name="postfinance-card_displayName[fr]"]', "Input name exist");
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
    /* Fill Post Finance formular */
    .then(function() {
        this.fillPaymentFormularByPaymentProduct("postfinance-card");
    })
    .then(function() {
        this.orderResultSuccess(paymentType);
    })
    .run(function() {
        test.done();
    });
});