var paymentType = "HiPay Enterprise Credit Card";

casper.test.begin('Send Notification to Prestashop from TPP BackOffice via ' + paymentType + ' with ' + typeCC, function(test) {
	phantom.clearCookies();
	var	data = "",
		hash = "",
		output = "",
		notif117 = true,
		reload = false,
		orderReference = casper.getOrderReference(),
        cartID = casper.getCartId(),
		orderID = casper.getOrderId();

	/* Check status notification from server on the order */
	casper.checkNotifPrestashop = function(status) {
		test.assertExists(x('//div[@class="message-item"]//div[@class="message-body"]//p[@class="message-item-text"][contains(., "Statut HiPay :' + status + '")]'), "Notification " + status + " is recorded per CMS !");
	};

	/* Open URL to BackOffice HiPay TPP */
	casper.start(urlBackend)
	/* Log on the BackOffice */
	.then(function() {
		if (loginBackend == '' && passBackend == '') {
			loginBackend = casper.cli.get('login-backend');
			passBackend = casper.cli.get('pass-backend');
		}
		this.logToBackend(loginBackend,passBackend);
	})
	/* Select sub-account use for test*/
	.then(function() {
		this.selectAccountBackend("OGONE_DEV");
	})
	/* Open Transactions tab */
	.then(function() {
		this.goToTabTransactions();
	})
	/* Search last created order */
	.then(function() {
		this.searchAndSelectOrder(cartID);
	})
	/* Open Notification tab and opening this notifications details */
	.then(function() {
		this.echo("Opening Notification details with status 116...", "INFO");
		this.openingNotif("116");
	})
	/* Get data from Notification with code 116 */
	.then(function() {
		this.gettingData("116");
	})
	/* Execute shell script */
	.then(function() {
		this.execCommand(hash,'bin/tests/000_lib/bower_components/hipay-casperjs-lib/generator/generator.sh');
	})
	/* Check CURL status code */
	.then(function() {
		this.checkCurl("200");
	})
	/* Open Notification details with code 117 */
	.then(function() {
		this.openingNotif("117");
	})
	/* If Notification with code 117 doesn't exists, do not check this notification */
	.then(function() {
		if(notif117) {
			/* Idem Notification with code 116 */
			this.then(function() {
				this.gettingData("117");
			});
			this.then(function() {
				this.execCommand(hash,'bin/tests/000_lib/bower_components/hipay-casperjs-lib/generator/generator.sh');
			});
			this.then(function() {
				this.checkCurl("200");
			});
		}
	})
	/* Idem Notification with code 117 */
	.then(function() {
		this.openingNotif("118");
	})
	.then(function() {
		this.gettingData("118");
	})
	.then(function() {
		this.execCommand(hash,'bin/tests/000_lib/bower_components/hipay-casperjs-lib/generator/generator.sh');
	})
	.then(function() {
		this.checkCurl("200");
	})
	/* Open admin panel and access to details of this order */
	.thenOpen(baseURL , function() {
		authentification.proceed(test);
		this.waitForSelector("li#subtab-AdminOrders", function success() {
			this.echo("Checking status notifications in order ...", "INFO");
			this.click("li#subtab-AdminParentOrders a");
			this.waitForSelector("table.order", function success() {
				this.click(x('//td[contains(., "' + casper.getOrderReference() + '")]'));
				this.waitForUrl(/AdminOrders&id_order/, function success() {
					/* 1 - Check History status */
					test.assertExists(x('//table[@class="table history-status row-margin-bottom"]//td[contains(., "Payment accepted")]'), "Notification process change order s status to Payment accepted");
				}, function fail() {
					test.assertUrlMatch(/AdminOrders&id_order/, "Order detail screen");
				},15000);
			}, function fail() {
				test.assertUrlMatch(/AdminOrders/, "Order screen exists");
			},15000);
		})
	})
	/* Idem Notification with code 116 */
	.then(function() {
		//this.checkNotifPrestashop("117");
	})
	/* Idem Notification with code 117 */
	.then(function() {
		this.checkNotifPrestashop("118");
	})
	/* Check returned CURL status code 403 from this shell command */
	.then(function() {
		// TODO Implement correct http response in module
		//if(typeof order == "undefined")
		//	this.checkCurl("403");
	})
	.run(function() {
        test.done();
    });
});