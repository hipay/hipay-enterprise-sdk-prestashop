/**********************************************************************************************
 *
 *                         VALIDATION TEST ADMIN CONFIGURATION
 *
 *
 *  @purpose    : This scenario makes it possible to test module with multiple manual capture
 *  @Scenario   : Configure module for manual capture, process payment with credit card and make several capture
 *  @screen     : Order view ( Panel Capture HiPay )
 *
/**********************************************************************************************/

var paymentType = "HiPay Enterprise Credit Card",
    currentBrandCC = typeCC;

casper.test.begin('Test manual capture', function(test) {
    phantom.clearCookies();

    casper.start(baseURL)
    .then(function() {
        if(typeof casper.cli.get('type-cc') == "undefined" && currentBrandCC == "visa" || typeof casper.cli.get('type-cc') != "undefined") {
           authentification.proceed(test);
           this.gotToHiPayConfiguration();
           this.configureSettingsMode("api")
           this.configureCaptureMode("manual");
        }
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
    })
    .thenOpen(baseURL, function() {
        phantom.injectJs(pathHeader + "002_CREDIT_CARD/1_frontend/TEST-0201-SEND_NOTIF_TO_CMS_FROM_BO_TPP.js");
    })
    .thenOpen(baseURL, function() {
        authentification.proceed(test);
        this.waitForSelector("li#subtab-AdminOrders", function success() {
            this.echo("Open order detail  ...", "INFO");
            this.click("li#subtab-AdminParentOrders a");
            this.waitForSelector("table.order", function success() {
                this.click(x('//td[contains(., "' + casper.getOrderReference() + '")]'));
                this.waitForUrl(/AdminOrders&id_order/, function success() {
                    test.done();
                }, function fail() {
                test.assertUrlMatch(/AdminOrders&id_order/, "Order detail screen");
                },15000);
            }, function fail() {
                test.assertUrlMatch(/AdminOrders/, "Order screen exists");
            },15000);
        })
    })
    .then(function() {

    })
    .run(function() {
        this.configureCaptureMode("automatic");
        test.done();
    });
});
