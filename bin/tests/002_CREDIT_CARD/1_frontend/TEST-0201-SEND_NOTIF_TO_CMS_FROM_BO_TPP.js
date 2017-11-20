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

	/* Open notification details */
	casper.openingNotif = function(status) {
		this.click('a[href="#payment-notification"]');
		if(status != "116")
			this.echo("Opening Notification details with status " + status + "...", "INFO");
		this.waitForSelector(x('//tr/td/span[text()="' + status + '"]/parent::td/following-sibling::td[@class="cell-right"]/a'), function success() {
			this.click(x('//tr/td/span[text()="' + status + '"]/parent::td/following-sibling::td[@class="cell-right"]/a'));
			test.info("Done");
		}, function fail() {
			if(status == "117") {
				notif117 = false;
				this.echo("Notification 117 not exists", "WARNING");
			}
			else {
				if(!reload) {
					this.echo("Waiting for notifications...", "WARNING")
					this.wait(5000, function() {
						reload = true;
						this.reload();
						test.info("Done");
						this.openingNotif(status);
					});
				}
				else
					test.assertExists(x('//tr/td/span[text()="' + status + '"]/parent::td/following-sibling::td[@class="cell-right"]/a'), "Notification " + status + " exists");
			}
		});
	};
	/* Get data request and hash code from the details */
	casper.gettingData = function(status) {
		this.echo("Getting data request from details...", "INFO");
		this.waitUntilVisible('div#fsmodal', function success() {
			hash = this.fetchText(x('//tr/td/pre[contains(., "Hash")]')).split('\n')[7].split(':')[1].trim();
			data = this.fetchText('textarea.copy-transaction-message-textarea');
			try {
				test.assert(hash.length > 1, "Hash Code captured !");
				test.assertNotEquals(data.indexOf("status=" + status), -1, "Data request captured !");
			} catch(e) {
				if(String(e).indexOf("Hash") != -1)
					test.fail("Failure: Hash Code not captured");
				else
					test.fail("Failure: data request not captured");
			}
			this.click("div.modal-backdrop");
		}, function fail() {
			test.assertVisible('div#fsmodal', "Modal window exists");
		});
	};
	/* Execute shell command in order to simulate notification to server */
	casper.execCommand = function(code, retry) {
		data = data.replace(/\n/g, '&');
		child = spawn('/bin/bash', ['bin/generator/generator.sh', data, code, baseURL + urlNotification]);
		try {
			child.stdout.on('data', function(out) {
				casper.wait(3000, function() {
					if(out.indexOf("CURL") != -1)
						this.echo(out.trim(), "INFO");
					else if(out.indexOf("200") != -1 || out.indexOf("503") != -1)
						test.info("Done");
					output = out;
				});
			});
			child.stderr.on('data', function(err) {
				casper.wait(2000, function() {
					this.echo(err, "WARNING");
				});
			});
		} catch(e) {
			if(!retry) {
				this.echo("Error during file execution! Retry command...", "WARNING");
				this.execCommand(code, true,'bin/tests/000_lib/bower_components/hipay-casperjs-lib/generator/generator.sh');
			}
			else
				test.fail("Failure on child processing command");
		}
	};
	/* Test CURL status code from shell command */
	casper.checkCurl = function(httpCode) {
		try {
			test.assertNotEquals(output.indexOf(httpCode), -1, "Correct CURL Status Code " + httpCode + " from CURL command !");
		} catch(e) {
			if(output.indexOf("503") != -1)
				test.fail("Failure on CURL Status Code from CURL command: 503");
			else if(output == "") {
				test.comment("Too early to check CURL status code");
				this.wait(15000, function() {
					this.checkCurl(httpCode);
				});
			}
			else
				test.fail("Failure on CURL Status Code from CURL command: " + output.trim());
		}
	};
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
		this.waitForUrl(/maccount/, function success() {
			this.click('a.nav-transactions');
			test.info("Done");
		}, function fail() {
			test.assertUrlMatch(/maccount/, "Dashboard page with account ID exists");
		});
	})
	/* Search last created order */
	.then(function() {
		this.echo("Finding cart ID # " + cartID + " in order list...", "INFO");
		this.waitForUrl(/manage/, function success() {
			this.click('input#checkbox-orderid');
			this.fillSelectors('form#form-manage', {
					'input#searchfilters-orderid-start':cartID,
					'select#searchfilters-orderid-type':"startwith"
				},false);
			this.click('input[name="submitbutton"]');

			this.waitForUrl(/list/, function success() {
				test.info("Done list");
				// Select the first order if several orders are present
				this.waitForSelector("table.datatable-transactions tbody tr:first-child", function success() {
					this.click('table.datatable-transactions tbody tr:first-child a[data-original-title="View transaction details"]');
				}, function fail(){
					test.assertExists('table.datatable-transactions tbody tr:first-child', "History block of this order exists");
				},25000);
			}, function fail() {
				test.assertUrlMatch(/list/, "Manage list exists");
			},25000);

		}, function fail() {
			test.assertUrlMatch(/manage/, "Manage page exists");
		});
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
		this.execCommand(hash,true,'bin/tests/000_lib/bower_components/hipay-casperjs-lib/generator/generator.sh');
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
				this.execCommand(hash,true,'bin/tests/000_lib/bower_components/hipay-casperjs-lib/generator/generator.sh');
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
		this.execCommand(hash,true,'bin/tests/000_lib/bower_components/hipay-casperjs-lib/generator/generator.sh');
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
        //test.done();
    });
});