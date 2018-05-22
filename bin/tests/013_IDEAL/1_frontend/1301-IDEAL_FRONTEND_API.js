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
        this.logToBackend();
        this.gotToHiPayConfiguration();
        this.activateMethod("ideal");
        this.configureSettingsMode("api");
        this.waitForSelector('input[name="ideal_displayName[fr]"]', function success() {
            label = this.getElementAttribute('input[name="ideal_displayName[fr]"]', 'value');
            test.info("Display name in checkout should be :" + label);
        }, function fail() {
            test.assertExists('input[name="ideal_displayName[fr]"]', "Input name exist");
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
        this.selectMethodInCheckout("Payer par " + label,false);
    })
    .then(function() {
        this.echo("Filling Business Identifier BIC with wrong value...", "INFO");

        this.waitForSelector('input#ideal-issuer_bank_id', function success() {
            this.fillSelectors('form#ideal-hipay', {
                'input[name="issuer_bank_id"]': 'INGBNL2A WRONG',
            }, false);

            this.click('form#conditions-to-approve input');
            this.click("div#payment-confirmation button");
            test.assertTextExists('BIC incorrect', 'Validation error done');
        }, function fail() {
            test.assertExists('input#ideal-issuer_bank_id', "Field Business Identifier exists");
        });
    })
    /* Fill IDeal formular */
    .then(function() {
        this.echo("Filling Business Identifier BIC...", "INFO");

        this.waitForSelector('input#ideal-issuer_bank_id', function success() {
            this.fillSelectors('form#ideal-hipay', {
                'input[name="issuer_bank_id"]': 'INGBNL2A',
            }, false);

            this.click("div#payment-confirmation button");
        }, function fail() {
            test.assertExists('input#ideal-issuer_bank_id', "Field Business Identifier exists");
        });
    })
    /* Fill IDeal formular */
    .then(function() {
        this.echo("Filling payment formular...", "INFO");
        this.payIDeal();
    })
    .then(function() {
        this.orderResultSuccess(paymentType);
    })
    .run(function() {
        test.done();
    });
});
