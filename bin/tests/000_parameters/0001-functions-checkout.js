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

function $_GET(param,casper) {
    var vars = {};
    casper.getCurrentUrl().replace(
        /[?&]+([^=&]+)=?([^&]*)?/gi, // regexp
        function( m, key, value ) { // callback
            vars[key] = value !== undefined ? value : '';
        }
    );

    if ( param ) {
        return vars[param] ? vars[param] : null;
    }
    return vars;
}

casper.test.begin('Functions', function(test) {
	/* For each fails, show current successful tests and show current URL and capture image */
    var img = 0;
	test.on('fail', function() {
        img++;
		casper.echo("URL: " + casper.currentUrl, "WARNING");
		casper.capture(pathErrors + 'fail' + img + '.png');
		test.comment("Image 'fail" + img + ".png' captured into '" + pathErrors + "'");
		casper.echo('Tests réussis : ' + test.currentSuite.passes.length, 'WARNING');
	});

    /* Fill HiPayCC formular */
    casper.fillFormPaymentHipayCC = function(card) {
        this.fillSelectors('form#tokenizerForm', {
            'input[name="card-number"]': card,
            'input[name="card-holders-name"]': 'Mr Test',
            'input[name="expiry-month"]': '02',
            'input[name="expiry-year"]': '20',
            'input[class="expiry"]': '02 / 20',
            'input[name="cvc"]': '500'
        }, false);
    };

    /* Choose item on featured product */
	casper.selectItemAndOptions = function() {
        this.echo("Selecting item and its options...", "INFO");
        this.waitForSelector('section.featured-products .products article:first-child a img', function success() {
            this.click('section.featured-products .products article:first-child a img');
        }, function fail() {
            var altImg = this.getElementAttribute('section.featured-products .products article:first-child a img', 'alt');
            test.assertExists('section.featured-products .products article:first-child a img', "'" + altImg + "' image exists");
        });

        this.waitForSelector('.product-add-to-cart button.add-to-cart', function success() {
            this.fillSelectors('form#add-to-cart-or-refresh', {
                'input[name="qty"]': 7,
            }, false);

            this.click('.product-add-to-cart button.add-to-cart');
        }, function fail() {
            test.assertExists('.product-add-to-cart button.add-to-cart', "Button Product add exists");
        });
        this.waitForSelector('#blockcart-modal .cart-content-btn button', function success() {
            this.click('#blockcart-modal .cart-content-btn button');
            test.info("Continue checkout ...");
        }, function fail() {
            test.assertExists("#blockcart-modal .cart-content-btn button", "Continue button exist");
        });
        this.waitForSelector('nav.header-nav .header a', function success() {
            this.click('nav.header-nav .header a');
            test.info("Open cart detail");
        }, function fail() {
            test.assertExists("nav.header-nav .header a", "Cart button");
        });
        this.waitForUrl(/controller=cart&action=show/, function success() {
            this.click('.cart-summary .checkout a')
        }, function fail() {
            test.assertUrlMatch(/index.php?controller=cart&action=show/, 'Cart detail');
        });
	};

    /* Fill personal Information*/
    casper.personalInformation = function() {
        this.echo("Filling 'Personal information' formular...", "INFO");
        this.waitUntilVisible('div#checkout-guest-form form#customer-form', function success() {
            this.fillSelectors('div#checkout-guest-form form#customer-form', {
                'input[name="id_gender"]': 'true',
                'input[name="firstname"]': 'TEST',
                'input[name="lastname"]': 'TEST',
                'input[name="email"]': 'email@yopmail.com',
            }, false);
            this.click("section#checkout-personal-information-step footer.form-footer button.continue");
            test.info("Done");
        }, function fail() {
            test.assertVisible("div#checkout-guest-form form#customer-form", "'Personal information' formular exists");
        }, 15000);
    };
	/* Fill billing operation */
	casper.billingInformation = function(country) {
        this.echo("Filling 'Billing Information' formular...", "INFO");
        this.waitForSelector("section#checkout-addresses-step .js-address-form form", function success() {
            var street = '1249 Tongass Avenue, Suite B', city = 'Ketchikan', cp = '99901', region = '2'; id_country = 8;
            switch(country) {
                case "FR":
                    street = 'Rue de la paix'; city = 'PARIS'; cp = '75000'; region = '257'; id_country='8';
                    test.comment("French Address");
                    break;
                case "BR":
                    test.comment("Brazilian Address");
                    break;
                case "NL":
                    street = 'Rue de la paix'; city = 'Amsterdam'; cp = '1000 AA'; region = '257'; id_country='13';
                    test.comment("Netherlands Address");
                    break;
                default:
                    country = 'US';
                    test.comment("US Address");
            }
            this.fillSelectors('#checkout-addresses-step form', {
                'input[name="address1"]': street,
                'input[name="city"]': city,
                'input[name="postcode"]': cp,
                'select[name="id_country"]': id_country,
                'input[name="phone"]': '0171000000'
            }, false);
            this.click("section#checkout-addresses-step footer.form-footer button.continue");
            test.info("Done");
        }, function fail() {
            test.assertExists("#checkout-addresses-step .js-address-form form", "'Billing Information' formular exists");
        });
	};
	/* Fill shipping method */
	casper.shippingMethod = function() {
	    this.echo("Filling 'Shipping Method' formular...", "INFO");
	    this.waitUntilVisible('section#checkout-delivery-step div.delivery-options-list input#delivery_option_2', function success() {
            this.click('section#checkout-delivery-step div.delivery-options-list input#delivery_option_2');
            this.click("section#checkout-delivery-step button.continue");
            test.info("Done");
        }, function fail() {
            test.assertVisible("section#checkout-delivery-step div.delivery-options-list input#delivery_option_2", "'Shipping Method' formular or delivery option exists");
        }, 20000);
	};

    /* Fill Step Payment */
	casper.fillStepPayment = function () {
        this.echo("Choosing payment method and filling 'Payment Information' formular with " + currentBrandCC + "...", "INFO");
        this.waitUntilVisible('section#checkout-payment-step', function success() {
            this.echo(labelPayByCard);
            this.clickLabel(labelPayByCard,'span');
            if(currentBrandCC == 'visa')
                this.fillFormPaymentHipayCC(cardsNumber.visa);
            else if(currentBrandCC == 'cb' || currentBrandCC == "mastercard")
                this.fillFormPaymentHipayCC(cardsNumber.cb);
            else if(currentBrandCC == 'amex' )
                this.fillFormPaymentHipayCC(cardsNumber.amex);
            else if(currentBrandCC == 'visa_3ds' )
                this.fillFormPaymentHipayCC( cardsNumber.visa_3ds);
            else if(currentBrandCC == 'maestro' )
                this.fillFormPaymentHipayCC(cardsNumber.maestro);
            this.click('form#conditions-to-approve input');
            this.click("div#payment-confirmation button");
            test.info("Done");
        }, function fail() {
            test.assertVisible("section#checkout-payment-step", "'Payment Information' formular exists");
        }, 15000);
    };

    /* Select Hosted payment in checkout */
    casper.selectMethodInCheckout = function (labelMethod, confirm) {
        this.echo("Choosing payment method...", "INFO");
        this.waitUntilVisible('section#checkout-payment-step', function success() {
            this.clickLabel(labelMethod,'span');
            if (confirm) {
                this.click('form#conditions-to-approve input');
                this.click("div#payment-confirmation button");
            }
            test.info("Done");
        }, function fail() {
            test.assertVisible("section#checkout-payment-step", "'Payment Information' formular exists");
        }, 10000);
    },

    casper.processCheckout = function(casper) {
        casper.thenOpen(baseURL, function() {
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
    }

	casper.echo('Functions checkout loaded !', 'INFO');
    test.done();
});