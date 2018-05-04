/* Return 1D array from multiple dimensional array */
function concatTable(arrToConvert) {
    var newArr = [];
    for(var i = 0; i < arrToConvert.length; i++)
    {
        newArr = newArr.concat(arrToConvert[i]);
    }
    return newArr;
};
/* return random number between 2 specific numbers */
function randNumbInRange(min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
};

var output = "",
    notif117 = true,
    reload = false;


casper.test.begin('Functions', function(test) {

    /* Check status notification from server on the order */
    casper.checkNotifPrestashop = function(status) {
        test.assertExists(x('//div[@class="message-item"]//div[@class="message-body"]//p[@class="message-item-text"][contains(., "Statut HiPay :' + status + '")]'), "Notification " + status + " is recorded per CMS !");
    };

    casper.checkOrderStatus = function(authorize,request,capture,partial) {
        /* Open admin panel and access to details of this order */
        casper.thenOpen(baseURL , function() {
            this.logToBackend();
            this.waitForSelector("li#subtab-AdminOrders", function success() {
                this.echo("Checking status notifications in order ...", "INFO");
                this.click("li#subtab-AdminParentOrders a");
                this.waitForSelector("table.order", function success() {
                    this.click(x('//td[contains(., "' + casper.getOrderReference() + '")]'));
                    this.waitForUrl(/AdminOrders&id_order/, function success() {
                        /* 1 - Check History status according the notifications */
                        if (capture && partial === false) {
                            test.assertExists(x('//table[@class="table history-status row-margin-bottom"]//td[contains(., "Paiement accepté")]'), "Notification process change order s status to Payment accepted");
                        } else if ( capture && partial) {
                            test.assertExists(x('//table[@class="table history-status row-margin-bottom"]//td[contains(., "Capture partielle (HiPay)")]'), "Notification process change order s status to Capture partielle (HiPay)");
                        } else {
                            test.assertExists(x('//table[@class="table history-status row-margin-bottom"]//td[contains(., "Paiement autorisé (HiPay)")]'), "Notification process change order s status to Paiement autorisé (HiPay)");
                        }
                    }, function fail() {
                        test.assertUrlMatch(/AdminOrders&id_order/, "Order detail screen");
                    },15000);
                }, function fail() {
                    test.assertUrlMatch(/AdminOrders/, "Order screen exists");
                },15000);
            })
        })
        /* Idem Notification with code 117 */
        .then(function() {
            if (capture) {
                this.checkNotifPrestashop("118");
            }
        })
        /* Check returned CURL status code 403 from this shell command */
        .then(function() {
            // TODO Implement correct http response in module
        })
    }

	casper.echo('Functions for notification loaded !', 'INFO');
	test.info("Based URL: " + baseURL);
    test.done();
});
