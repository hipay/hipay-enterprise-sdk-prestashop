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

casper.test.begin('Send Notification to Prestashop from TPP BackOffice via ' + paymentType + ' with ' + utilsHiPay.getTypeCC(), function (test) {
    phantom.clearCookies();

    /* Open URL to BackOffice HiPay TPP */
    casper.start(urlBackend)
        .thenOpen(urlBackend, function () {
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
        .thenOpen(baseURL, function() {
            adminMod.logToBackend(test);
        })
        .then(function () {
            notificationMod.checkOrderStatus(test, true, true, true, false);
        })
        .then(function () {
            notificationMod.checkNotifPrestashop(test, "118");
        })
        .run(function () {
            test.done();
        });
});
