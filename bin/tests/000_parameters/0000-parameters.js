var fs = require('fs'),
	utils = require('utils'),
	childProcess = require("child_process"),
	spawn = childProcess.spawn,
	x = require('casper').selectXPath,
	defaultViewPortSizes = { width: 1920, height: 1080 },
	baseURL = casper.cli.get('url'),
	urlMailCatcher = casper.cli.get('url-mailcatcher'),
	typeCC = casper.cli.get('type-cc'),
	loginBackend = 'ogone.dev.test@gmail.com',
	passBackend = 'testcasperjs',
	loginPaypal = casper.cli.get('login-paypal'),
	passPaypal = casper.cli.get('pass-paypal'),
	countryPaypal = 'US',
	order = casper.cli.get('order'),
	orderID = 0,
    cartID = 0,
    orderReference = 0,
	headerModule = "../../Modules/",
	urlBackend = "https://merchant.hipay-tpp.com/",
	urlNotification = "index.php?fc=module&module=hipay_enterprise&controller=notify",
    checkout = require(headerModule + 'step-checkout'),
    authentification = require(headerModule + 'step-authentification'),
    configuration = require(headerModule + 'step-configuration'),
    mailcatcher = require(headerModule + 'step-mailcatcher'),
    pathHeader = "bin/tests/",
    pathErrors = pathHeader + "errors/",
    allowedCurrencies = [
    	{ currency: 'EUR', symbol: '€' },
    	{ currency: 'USD', symbol: '$' }
    ],
    currentCurrency = allowedCurrencies[0],
	labelPayByCard = 'Payer par Credit card',
    generatedCPF = "373.243.176-26",
    admin_login = "demo@hipay.com",
	admin_passwd = "hipay123";

casper.test.begin('Parameters', function(test) {
	/* Set default viewportSize and UserAgent */
	casper.userAgent('Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36');
	casper.options.viewportSize = {width: defaultViewPortSizes["width"], height: defaultViewPortSizes["height"]};

	//casper.options.waitTimeout = 10000;

	/* Set default card type if it's not defined */
	if(typeof typeCC == "undefined")
		typeCC = "visa";

	/* Say if BackOffice TPP credentials are set or not */
	if(loginBackend != "" && passBackend != "")
		test.info("Backend credentials set");
	else
		test.comment("No Backend credentials");

	if(loginPaypal != "" && passPaypal != "")
		test.info("PayPal credentials set");
	else
		test.comment("No PayPal credentials");

	casper.echo('Paramètres chargés !', 'INFO');
	test.done();
});
	