var paymentType = "HiPay Enterprise Credit Card",
	currentBrandCC = typeCC;

casper.test.begin('Change Hash Algorithm ' + paymentType + ' with ' + typeCC, function(test) {
	phantom.clearCookies();

	casper.setFilter("page.confirm", function(msg) {
		this.echo("Confirmation message " + msg, "INFO");
		return true;
	});

	casper.start(baseURL)
	.thenOpen(urlBackend, function() {
		this.logToHipayBackend(loginBackend,passBackend);
	})
	.then(function() {
		this.selectAccountBackend("OGONE_DEV");
	})
	/* Open Integration tab */
	.then(function() {
		this.echo("Open Integration nav", "INFO");
		this.waitForUrl(/maccount/, function success() {
			this.selectHashingAlgorithm("SHA512");
		}, function fail() {
			test.assertUrlMatch(/maccount/, "Dashboard page with account ID exists");
		});
	})
	.then(function() {
		this.logToBackend();
	})
	.then(function() {
		this.gotToHiPayConfiguration();
	})
	.then(function() {
		this.echo("Synchronize Hashing Algorithm", "INFO");
		this.waitForSelector('input#synchronize-hash', function success() {
			var current = this.evaluate(function () {
				return document.querySelector('select#hash_algorithm_test').value;
			});
			test.info("Initial Hashing Algorithm :" + current);
			if (current != 'SHA1') {
				test.fail("Initial value is wrong for Hashing : " + current );
			}
			this.thenClick('input#synchronize-hash', function() {
				this.wait(4000, function() {
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
	.thenOpen(urlBackend, function() {
		//this.logToHipayBackend(loginBackend,passBackend);
	})
	.then(function() {
		//this.selectAccountBackend("OGONE_DEV");
	})
	.then(function() {
		orderReference = casper.getOrderReference();
		cartID = casper.getCartId();
		orderID = casper.getOrderId();
		this.processNotifications(true,false,true,false,"OGONE_DEV");
	})
	.thenOpen(urlBackend, function() {
		//this.logToHipayBackend(loginBackend,passBackend);
	})
	.then(function() {
		this.selectAccountBackend("OGONE_DEV");
	})
	/* Open Integration tab */
	.then(function() {
		this.echo("Open Integration nav", "INFO");
		this.waitForUrl(/maccount/, function success() {
			this.selectHashingAlgorithm("SHA1");
		}, function fail() {
			test.assertUrlMatch(/maccount/, "Dashboard page with account ID exists");
		});
	})
	.run(function() {
        test.done();
    });
});