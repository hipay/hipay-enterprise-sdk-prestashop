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

    casper.setFilter("page.confirm", function(msg) {
        this.echo("Confirmation message " + msg, "INFO");
        return true;
    });

    casper.start(baseURL)
        .then(function () {
            currentBrandCC = "mastercard";
            authentification.proceed(test);
            this.gotToHiPayConfiguration();
            this.configureSettingsMode("api");
        })
        .then(function () {
            this.configureCaptureMode("manual");
        })
        .thenOpen(baseURL, function () {
            this.selectItemAndOptions();
        })
        .then(function () {
            this.personalInformation();
        })
        .then(function () {
            this.billingInformation('FR');
        })
        .then(function () {
            this.shippingMethod();
        })
        .then(function () {
            this.fillStepPayment();
        })
        .then(function () {
            this.orderResultSuccess(paymentType);
        })
        .then(function () {
            orderReference = casper.getOrderReference();
            cartID = casper.getCartId();
            orderID = casper.getOrderId();
            this.processNotifications(true,false,false);
        })
        .thenOpen(baseURL, function () {
            authentification.proceed(test);
            this.waitForSelector("li#subtab-AdminOrders", function success() {
                this.echo("Open order detail  ...", "INFO");
                this.click("li#subtab-AdminParentOrders a");
                this.waitForSelector("table.order", function success() {
                    this.click(x('//td[contains(., "' + casper.getOrderReference() + '")]'));
                    this.waitForUrl(/AdminOrders&id_order/, function success() {
                        test.info("Order tab is ready for capture");
                    }, function fail() {
                        test.assertUrlMatch(/AdminOrders&id_order/, "Order detail screen");
                    }, 15000);
                }, function fail() {
                    test.assertUrlMatch(/AdminOrders/, "Order screen exists");
                }, 15000);
            })
        })
        .then(function () {
            this.echo("Capture 3 item and shiping  ...", "INFO");
            this.waitForSelector("form#hipay_capture_form", function success() {
                /* Select partial capture to show basket */
                this.evaluate(function () {
                    var form = document.querySelector('#hipay_capture_type');
                    form.selectedIndex = 1;
                    $(form).change();
                });

                this.waitUntilVisible('table.table-item-hipay', function success() {
                    /* Capture 3 item and shipping fees */
                    this.fillSelectors("form#hipay_capture_form", {
                        'input[name="hipaycapture[1]"]': 3,
                        'input[name="hipay_capture_fee"]': 1
                    }, false);


                    this.click('button[name="hipay_capture_basket_submit"]');

                    this.waitForAlert(function(response) {
                        myAlert = {exists: true, value: response.data};
                        test.info("Alert received", "INFO");
                        return true;
                    }, function fail () {
                        myAlert = {exists: false};
                        test.info("Alert not received", "INFO");
                    },15000);

                    this.waitForUrl(/controller=AdminOrders/, function(){
                        test.info("Capture done", "INFO");
                    }, function fail() {
                        this.assertUrl(/controller=AdminOrders/,'Capture is in error')
                    },15000);
                }, function fail() {
                    this.assertVisible('table.table-item-hipay');
                });

            }, function fail() {
                test.assertUrlMatch(/AdminOrders/, "Order screen exists");
            }, 15000);
    })
    .then(function () {
        this.processNotifications(false,true,true,true);
    })
    .then(function () {
        authentification.proceed(test);
        this.waitForSelector("li#subtab-AdminOrders", function success() {
            this.echo("Open order detail  ...", "INFO");
            this.click("li#subtab-AdminParentOrders a");
            this.waitForSelector("table.order", function success() {
                this.click(x('//td[contains(., "' + casper.getOrderReference() + '")]'));
                this.waitForUrl(/AdminOrders&id_order/, function success() {
                    test.info("Order tab is ready for capture");
                }, function fail() {
                    test.assertUrlMatch(/AdminOrders&id_order/, "Order detail screen");
                }, 15000);
            }, function fail() {
                test.assertUrlMatch(/AdminOrders/, "Order screen exists");
            }, 15000);
        })
    })
    .then(function () {
        /* Check order is really in status partial capture */
        test.assertExists("form#hipay_refund_form","Refund panel is shown");
        test.assertExists("form#hipay_capture_form","Capture panel is shown");

        var qtyRemain= this.fetchText('table.table-item-hipay input[name="hipaycapture[1]"]+div').split('/')[1];
        test.assert(qtyRemain == ' 3', "Qty remain to capture is 3!");;
    })
    .then(function () {
        this.echo("Capture the last item ...", "INFO");
        this.waitForSelector("form#hipay_capture_form", function success() {
            this.waitUntilVisible('table.table-item-hipay', function success() {
                /* Capture 4 item and shipping fees */
                this.fillSelectors("form#hipay_capture_form", {
                    'input[name="hipaycapture[1]"]': 3,
                }, false);
                this.click('button[name="hipay_capture_basket_submit"]');

                this.waitForAlert(function(response) {
                    myAlert = {exists: true, value: response.data};
                    test.info("Alert received", "INFO");
                    return true;
                }, function fail () {
                    myAlert = {exists: false};
                    test.info("Alert not received", "INFO");
                },15000);

                this.waitForUrl(/controller=AdminOrders/, function(){
                    test.info("Capture done", "INFO");
                }, function fail() {
                    this.assertUrl(/controller=AdminOrders/,'Capture is in error')
                },15000)

            }, function fail() {
                this.assertVisible('table.table-item-hipay');
            });
        }, function fail() {
            test.assertUrlMatch(/AdminOrders/, "Order screen exists");
        }, 15000);
    })
    .then(function () {
        this.processNotifications(false,true,true,false);
    })
    .run(function() {
        this.configureCaptureMode("automatic");
        test.done();
    });
});
