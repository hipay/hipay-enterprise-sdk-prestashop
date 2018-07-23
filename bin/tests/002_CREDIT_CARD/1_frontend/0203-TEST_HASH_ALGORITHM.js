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

var paymentType = "HiPay Enterprise Credit Card";

casper.test.begin('Change Hash Algorithm ' + paymentType + ' with ' + utilsHiPay.getTypeCC(), function (test) {
    phantom.clearCookies();

    casper.setFilter("page.confirm", function (msg) {
        this.echo("Confirmation message " + msg, "INFO");
        return true;
    });

    casper.start(baseURL)
        .thenOpen(urlBackend, function () {
            backendLibHiPay.logToHipayBackend(test, loginBackend, passBackend);
        })
        .then(function () {
            backendLibHiPay.selectAccountBackend(test, "OGONE_DEV");
        })
        /* Open Integration tab */
        .then(function () {
            this.echo("Open Integration nav", "INFO");
            this.waitForUrl(/maccount/, function success() {
                backendLibHiPay.selectHashingAlgorithm(test, "SHA512");
            }, function fail() {
                test.assertUrlMatch(/maccount/, "Dashboard page with account ID exists");
            });
        })
        .then(function () {
            adminMod.logToBackend(test);
        })
        .then(function () {
            adminMod.gotToHiPayConfiguration(test);
        })
        .then(function () {
            this.echo("Synchronize Hashing Algorithm", "INFO");
            this.waitForSelector('input#synchronize-hash', function success() {
                var current = this.evaluate(function () {
                    return document.querySelector('select#hash_algorithm_test').value;
                });
                test.info("Initial Hashing Algorithm :" + current);
                if (current != 'SHA1') {
                    test.fail("Initial value is wrong for Hashing : " + current);
                }
                this.thenClick('input#synchronize-hash', function () {
                    this.wait(4000, function () {
                        var newHashingAlgo = this.evaluate(function () {
                            return document.querySelector('select#hash_algorithm_test').value;
                        });
                        if (newHashingAlgo != 'SHA512') {
                            test.fail("Synchronize doesn't work : " + current);
                        } else {
                            test.info("Done");
                        }
                    });
                });
            }, function fail() {
                test.assertExists('input#synchronize-hash', "Syncronize button exist");
            });
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
        .thenOpen(urlBackend)
        .then(function () {
            backendLibHiPay.selectAccountBackend(test, "OGONE_DEV");
        })
        /* Open Integration tab */
        .then(function () {
            this.echo("Open Integration nav", "INFO");
            this.waitForUrl(/maccount/, function success() {
                backendLibHiPay.selectHashingAlgorithm("SHA1");
            }, function fail() {
                test.assertUrlMatch(/maccount/, "Dashboard page with account ID exists");
            });
        })
        .run(function () {
            test.done();
        });
});
