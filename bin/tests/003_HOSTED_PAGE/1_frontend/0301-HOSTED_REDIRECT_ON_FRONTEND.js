/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : HOSTED
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
/**********************************************************************************************/

var paymentType = "HiPay Enterprise Hosted Page";

casper.test.begin('Test Checkout ' + paymentType + ' with redirect', function(test) {
	phantom.clearCookies();

	casper.start(baseURL)
    /* Active Hosted payment method with display iframe */
    .then(function() {
        authentification.proceed(test);
        this.gotToHiPayConfiguration();
        this.configureSettingsMode("hosted_page");
        this.configureHostedDisplay("redirect");
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
    /* Fill payment formular in redirect page */
    .then(function() {
        this.fillPaymentFormularByPaymentProduct("visa");
    })
    .then(function() {
        this.orderResultSuccess(paymentType);
    })
    .run(function() {
        test.done();
    });
});