/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : HOSTED
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
/**********************************************************************************************/

var paymentType = "HiPay Enterprise Hosted Page",
    currentBrandCC = typeCC,
    file_path="003_HOSTED_PAGE/1_frontend/0300-HOSTED_IFRAME_ON_FRONTEND.js";

casper.test.begin('Test Checkout ' + paymentType + ' with Iframe and ' + currentBrandCC, function(test) {
	phantom.clearCookies();

	casper.start(baseURL)
    /* Active Hosted payment method with display iframe */
    .then(function() {
        authentification.proceed(test);
        this.gotToHiPayConfiguration();
        this.configureSettingsMode("hosted_page");
        this.configureHostedDisplay("iframe");
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
        this.selectMethodInCheckout(labelPayByCard,true);
    })
    /* Fill payment formular inside iframe */
    .then(function() {
    	this.wait(10000, function() {
			this.withFrame(0, function() {
                this.fillPaymentFormularByPaymentProduct(currentBrandCC);
			});
    	});
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