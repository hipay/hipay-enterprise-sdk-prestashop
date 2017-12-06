/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : SEPA DIRECT DEBIT
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
/**********************************************************************************************/

var paymentType = "HiPay Enterprise SEPA Direct Debit";

casper.test.begin('Test Checkout ' + paymentType + ' without Electronic Signature', function(test) {
	phantom.clearCookies();

    casper.start(baseURL)
    /* Active SEPA payment method with electronic signature */
	.then(function() {
		authentification.proceed(test);
	})
	.then(function() {
		this.gotToHiPayConfiguration();
	})
	.then(function() {
		this.configureSettingsMode("hosted_page");
	})
	.then(function() {
	this.activateMethod("sdd");
	})
	.then(function() {
	this.setValueOptions("sdd_electronicSignature","0");
	})
		.then(function() {
		this.waitForSelector('input[name="sdd_displayName"]', function success() {
			label = this.getElementAttribute('input[name="sdd_displayName"]', 'value');
			test.info("Display name in checkout should be :" + label);
		}, function fail() {
			test.assertExists('input[name="sdd_displayName"]', "Input name exist");
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
    /* Fill SEPA payment formular */
    .then(function() {
		this.fillPaymentFormularByPaymentProduct("sdd");
    })
    .then(function() {
        test.info("Done");
        this.orderResultSuccess(paymentType);
    })
    .run(function() {
        test.done();
    });
});