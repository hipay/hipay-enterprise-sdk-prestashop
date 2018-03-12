/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : CREDIT CART (DIRECT)
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
/**********************************************************************************************/

var paymentType = "HiPay Enterprise Credit Card",
    currentBrandCC = typeCC,
    file_path="002_CREDIT_CARD/1_frontend/0200-CREDIT_CARD_FRONTEND.js";

casper.test.begin('Test Checkout ' + paymentType + ' with ' + currentBrandCC, function(test) {
    phantom.clearCookies();

    casper.start(baseURL)
    .thenOpen(urlBackend, function() {
        this.logToHipayBackend(loginBackend,passBackend);
    })
    .then(function() {
        this.selectAccountBackend("OGONE_DEV");
    })
    /* Open Integration tab */
    .then(function() {
        this.echo("Open Integration nav", "INFO");
        this.waitForUrl(/maccount/, function success() {
            this.selectHashingAlgorithm("SHA51");
        }, function fail() {
            test.assertUrlMatch(/maccount/, "Dashboard page with account ID exists");
        })
    })
    /* Active API Mode in Global Settings  */
    .then(function() {
        this.logToBackend();
    })
    .then(function() {
        this.gotToHiPayConfiguration();
        this.configureSettingsMode("api");
        this.configureCaptureMode("automatic");
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
        this.fillStepPayment();
    })
    .then(function() {
        this.orderResultSuccess(paymentType);

        /* Test it again with another card type */
        if (currentBrandCC == 'visa') {
            casper.testOtherTypeCC(file_path,'mastercard');
        }
        if (currentBrandCC == 'mastercard') {
            // Waiting AMEX for test account
            //casper.testOtherTypeCC(file_path, 'AMEX');
        }
        if (currentBrandCC == 'mastercard') {
            casper.testOtherTypeCC(file_path, 'maestro');
        }

        if (currentBrandCC == 'maestro') {
            casper.testOtherTypeCC(file_path, 'visa_3ds');
        }

        if (currentBrandCC == 'visa_3ds') {
            casper.testOtherTypeCC(file_path, '');
        }
    })
    .run(function() {
        test.done();
    });
});

