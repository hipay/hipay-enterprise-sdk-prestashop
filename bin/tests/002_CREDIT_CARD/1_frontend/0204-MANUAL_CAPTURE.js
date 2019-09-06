/**
 * HiPay Enterprise SDK Prestashop
 *
 * 2017 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright 2017 HiPay
 * @license   https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 */

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
    currentBrandCC = utilsHiPay.getTypeCC();

casper.test.begin('Test manual capture', function (test) {
    phantom.clearCookies();

    casper.setFilter("page.confirm", function (msg) {
        this.echo("Confirmation message " + msg, "INFO");
        return true;
    });

    casper.start(baseURL)
        .then(function () {
            currentBrandCC = "mastercard";
            adminMod.logToBackend(test);
        })
        .then(function () {
            adminMod.gotToHiPayConfiguration(test);
            adminMod.configureCaptureMode(test, "manual");
        })
        .thenOpen(baseURL, function () {
            checkoutMod.selectItemAndOptions(test);
        })
        .then(function () {
            checkoutMod.personalInformation(test);
        })
        .then(function () {
            checkoutMod.billingInformation(test, 'FR');
        })
        .then(function () {
            checkoutMod.shippingMethod(test);
        })
        .then(function () {
            checkoutMod.fillStepPayment(test);
        })
        .then(function () {
            adminMod.orderResultSuccess(test);
        })
        .then(function () {
            notificationLibHiPay.processNotifications(
                test,
                order.getCartId(),
                true,
                false,
                false,
                true,
                "OGONE_DEV",
                backendLibHiPay,
                loginBackend,
                passBackend,
                baseURL,
                urlNotification
            );
        })
        .thenOpen(baseURL, function () {
            adminMod.logToBackend(test);
            this.waitForSelector("li#subtab-AdminOrders", function success() {
                this.echo("Open order detail  ...", "INFO");
                this.click("li#subtab-AdminParentOrders a");
                this.click("li#subtab-AdminOrders a");
                this.waitForSelector("table.order", function success() {
                    this.click(x('//td[contains(., "' + order.getReference() + '")]/../td[12]/div/a'));
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

                    this.wait(4000, function () {
                        this.click('button[name="hipay_capture_basket_submit"]');
                    });

                    this.waitForAlert(function () {
                        test.info("Alert received", "INFO");
                        return true;
                    }, function fail() {
                        test.info("Alert not received", "INFO");
                    }, 15000);

                    this.waitForUrl(/controller=AdminOrders/, function () {
                        test.info("Capture done", "INFO");
                    }, function fail() {
                        this.assertUrl(/controller=AdminOrders/, 'Capture is in error')
                    }, 15000);
                }, function fail() {
                    this.assertVisible('table.table-item-hipay');
                });

            }, function fail() {
                test.assertUrlMatch(/AdminOrders/, "Order screen exists");
            }, 15000);
        })
        .then(function () {
            notificationLibHiPay.processNotifications(
                test,
                order.getCartId(),
                false,
                false,
                true,
                true,
                "OGONE_DEV",
                backendLibHiPay,
                loginBackend,
                passBackend,
                baseURL,
                urlNotification
            );
        })
        .then(function () {
            adminMod.logToBackend(test);
            this.waitForSelector("li#subtab-AdminOrders", function success() {
                this.echo("Open order detail  ...", "INFO");
                this.click("li#subtab-AdminParentOrders a");
                this.click("li#subtab-AdminOrders a");
                this.waitForSelector("table.order", function success() {
                    this.click(x('//td[contains(., "' + order.getReference() + '")]/../td[12]/div/a'));
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
            test.assertExists("form#hipay_refund_form", "Refund panel is shown");
            test.assertExists("form#hipay_capture_form", "Capture panel is shown");

            var qtyRemain = this.fetchText('table.table-item-hipay input[name="hipaycapture[1]"]+div').split('/')[1];
            test.assert(qtyRemain === ' 6', "Qty remain to capture is 6!");
        })
        .then(function () {
            this.echo("Capture the last item ...", "INFO");
            this.waitForSelector("form#hipay_capture_form", function success() {
                this.waitUntilVisible('table.table-item-hipay', function success() {
                    /* Capture 4 item and shipping fees */
                    this.fillSelectors("form#hipay_capture_form", {
                        'input[name="hipaycapture[1]"]': 6,
                    }, false);

                    this.wait(4000, function () {
                        this.click('button[name="hipay_capture_basket_submit"]');
                    });

                    this.waitForAlert(function (response) {
                        test.info("Alert received", "INFO");
                        return true;
                    }, function fail() {
                        test.info("Alert not received", "INFO");
                    }, 15000);

                    this.waitForUrl(/controller=AdminOrders/, function () {
                        test.info("Capture done", "INFO");
                    }, function fail() {
                        this.assertUrl(/controller=AdminOrders/, 'Capture is in error')
                    }, 15000)

                }, function fail() {
                    this.assertVisible('table.table-item-hipay');
                });
            }, function fail() {
                test.assertUrlMatch(/AdminOrders/, "Order screen exists");
            }, 15000);
        })
        .then(function () {
            notificationLibHiPay.processNotifications(
                test,
                order.getCartId(),
                false,
                false,
                true,
                false,
                "OGONE_DEV",
                backendLibHiPay,
                loginBackend,
                passBackend,
                baseURL,
                urlNotification
            );
        })
        .run(function () {
            adminMod.configureCaptureMode(test, "automatic");
            test.done();
        });
});
