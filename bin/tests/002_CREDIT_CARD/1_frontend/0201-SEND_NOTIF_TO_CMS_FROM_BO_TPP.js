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

	/* Open URL to BackOffice HiPay TPP */
	casper.start(urlBackend)
	.thenOpen(urlBackend,function() {
		this.processNotifications(true,true,true,false);
	})
	.then(function() {
		this.checkOrderStatus(true,true,true,false);
	})
	.then(function() {
		this.checkNotifPrestashop("118");
	})
	.run(function() {
        test.done();
    });
});