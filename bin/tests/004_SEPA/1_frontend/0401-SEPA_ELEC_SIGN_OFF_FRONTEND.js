/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : SEPA DIRECT DEBIT
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
/**********************************************************************************************/

var paymentType = "HiPay Enterprise SEPA Direct Debit";

casper.test.begin('Test Checkout ' + paymentType + ' with Electronic Signature', function(test) {
	phantom.clearCookies();

    casper.start(baseURL)
    /* Active SEPA payment method without electronic signature */
    .then(function() {
        this.logToBackend();
    })
    .then(function() {
        this.gotToHiPayConfiguration();
        this.configureSettingsMode("api");
        this.activateMethod("sdd");
        this.setValueOptions("sdd_electronicSignature","0");
        this.waitForSelector('input[name="sdd_displayName[fr]"]', function success() {
            label = this.getElementAttribute('input[name="sdd_displayName[fr]"]', 'value');
            test.info("Display name in checkout should be :" + label);
        }, function fail() {
            test.assertExists('input[name="sdd_displayName[fr]"]', "Input name exist");
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
        this.selectMethodInCheckout("Payer par " + label);
    })
    .then(function() {
    	this.echo("Filling SEPA Formular ...", "INFO");
        this.fillSelectors('form#conditions-to-approve', {
            'input' : "1"
        }, false);
    	this.waitUntilVisible('#sdd-hipay', function success() {
            this.fillSelectors('form#sdd-hipay', {
                'select[name="gender"]': "1",
                'input[name="firstname"]': "TEST",
                'input[name="lastname"]': "TEST",
                'input[name="iban"]': ibanNumber.fr,
                'input[name="issuer_bank_id"]': bicNumber.fr,
                'input[name="bank_name"]': "BANK TEST"
            }, false);

            this.click("div#payment-confirmation button");
    		test.info("Done");
		}, function fail() {
        	test.assertVisible("form#sdd-hipay", "'Payment Information' formular exists");
        }, 10000);
    })
    .then(function() {
        this.orderResultSuccess(paymentType);
    })
    .run(function() {
        test.done();
    });
});