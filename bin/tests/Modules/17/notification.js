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

/**
 * Check notification in Prestashop back-office
 *
 * @param test
 */
exports.checkNotifPrestashop = function checkNotifPrestashop(test, status) {
    test.assertExists(
        x(
            '//div[@class="message-item"]//div[@class="message-body"]//p[@class="message-item-text"][contains' +
            '(., "Statut HiPay :' + status + '")]'
        ),
        "Notification " + status + " is recorded per CMS !"
    );
};

/**
 * Check notification in Prestashop back-office
 *
 * @param test
 */
exports.checkOrderStatus = function checkOrderStatus(test, authorize, request, capture, partial) {
    casper.then(function () {
        this.waitForSelector("li#subtab-AdminOrders", function success() {
            this.echo("Checking status notifications in order ...", "INFO");
            this.click("li#subtab-AdminParentOrders a");
            this.waitForSelector("table.order", function success() {
                this.click(x('//td[contains(., "' + order.getReference() + '")]'));
                this.waitForUrl(/AdminOrders&id_order/, function success() {
                    checkHistoryByNotification(test, authorize, request, capture, partial);
                }, function fail() {
                    test.assertUrlMatch(/AdminOrders&id_order/, "Order detail screen");
                }, 15000);
            }, function fail() {
                test.assertUrlMatch(/AdminOrders/, "Order screen exists");
            }, 15000);
        })
    });
};

/**
 * Check History status according the notifications
 *
 * @param test
 * @param authorize
 * @param request
 * @param capture
 * @param partial
 */
function checkHistoryByNotification(test, authorize, request, capture, partial) {
    if (capture && partial === false) {
        test.assertExists(
            x('//table[@class="table history-status row-margin-bottom"]//td[contains(., "Paiement accepté")]'),
            "Notification process change order s status to Payment accepted"
        );
    } else if (capture && partial) {
        test.assertExists(
            x('//table[@class="table history-status row-margin-bottom"]//td[contains(., "Capture partielle (HiPay)")]'),
            "Notification process change order s status to Capture partielle (HiPay)"
        );
    } else {
        test.assertExists(
            x('//table[@class="table history-status row-margin-bottom"]//td[contains(., "Paiement autorisé (HiPay)")]'),
            "Notification process change order s status to Paiement autorisé (HiPay)"
        );
    }
}
