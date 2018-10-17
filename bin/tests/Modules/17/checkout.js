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

/**************************************
 *              Add Product
 **************************************/

/**
 *
 * @param test
 */
exports.selectItemAndOptions = function selectItemAndOptions(test) {
    casper.then(function () {
        this.echo("Selecting item and its options with prestashop 1.7", "INFO");
        var altImg = this.getElementAttribute('section.featured-products .products article:first-child a img', 'alt');
        this.echo(altImg, "INFO");
        this.waitForSelector(
            'section.featured-products .products article:first-child a img',
            function success() {
                selectItemForCart(test)
            },
            function fail() {
                test.assertExists(
                    'section.featured-products .products article:first-child a img',
                    "'" + altImg + "' image exists"
                );
            }
            , 50000
        );
    });
};

/**
 *
 * @param test
 */
function selectItemForCart(test) {
    casper.echo("Select Item for cart", "COMMENT");
    casper.click('section.featured-products .products article:first-child a img');

    casper.waitForSelector(
        '.product-add-to-cart button.add-to-cart',
        function success() {
            addProductToCart(test)
        },
        function fail() {
            test.assertExists('.product-add-to-cart button.add-to-cart', "Button Product add exists")
        }, 50000
    );
}

/**
 *
 * @param test
 */
function addProductToCart(test) {
    casper.fillSelectors(
        'form#add-to-cart-or-refresh',
        {
            'input[name="qty"]': 9
        },
        false
    );
    casper.click('.product-add-to-cart button.add-to-cart');

    casper.waitForSelector(
        '#blockcart-modal',
        function success() {
            submitCart(test)
        },
        function fail() {
            test.assertExists("#blockcart-modal", "Modal exist")
        }, 50000
    );
}

/**
 *
 * @param test
 */
function submitCart(test) {
    casper.click('#blockcart-modal .cart-content-btn a');
    casper.click('#blockcart-modal .cart-content-btn button');
    casper.echo("Continue checkout ...", "COMMENT");

    casper.waitForSelector('nav.header-nav .header a', function success() {
            casper.click('nav.header-nav .header a');
            casper.echo("Open cart detail", "COMMENT");

            casper.waitForUrl(/controller=cart&action=show/, function success() {
                casper.click('.cart-summary .checkout a')

            }, function fail() {
                test.assertUrlMatch(/index.php?controller=cart&action=show/, 'Cart detail');
            }, 50000);

        },
        function fail() {
            test.assertExists("nav.header-nav .header a", "Cart button")
        }
        , 50000
    );
}

/**************************************
 *      Filling Personal Information
 **************************************/

/**
 *
 * @param test
 */
exports.personalInformation = function personalInformation(test) {
    casper.then(function () {
        this.echo("Filling 'Personal information' formular...", "INFO");
        this.waitUntilVisible('div#checkout-guest-form form#customer-form', function success() {
            this.fillSelectors('div#checkout-guest-form form#customer-form', {
                'input[name="id_gender"]': 'true',
                'input[name="firstname"]': 'TEST',
                'input[name="lastname"]': 'TEST',
                'input[name="email"]': 'email@yopmail.com'
            }, false);
            this.click("section#checkout-personal-information-step footer.form-footer button.continue");
            this.echo("Done", "COMMENT");

        }, function fail() {
            test.assertVisible("div#checkout-guest-form form#customer-form", "'Personal information' formular exists");
        }, 50000);
    });
};

/**************************************
 *      Filling Billing Information
 **************************************/

/**
 *
 * @param test
 * @param country
 */
exports.billingInformation = function billingInformation(test, country) {
    casper.then(function () {
        this.echo("Filling 'Billing Information' formular...", "INFO");
        this.waitForSelector("section#checkout-addresses-step .js-address-form form", function success() {
            var adress = getAdressByCountry(country);
            this.fillSelectors('#checkout-addresses-step form', {
                'input[name="address1"]': adress['street'],
                'input[name="city"]': adress['city'],
                'input[name="postcode"]': adress['cp'],
                'select[name="id_country"]': adress['id_country'],
                'input[name="phone"]': '0171000000'
            }, false);
            this.click("section#checkout-addresses-step footer.form-footer button.continue");
            this.echo("Done", "COMMENT");
        }, function fail() {
            test.assertExists(
                "#checkout-addresses-step .js-address-form form",
                "'Billing Information' formular exists"
            );
        }, 50000);
    });
};

/**
 *
 * @param country
 * @returns {{street: string, city: string, cp: string, region: string, id_country: number}}
 */
function getAdressByCountry(country) {
    var adress = {
        'street': '1249 Tongass Avenue, Suite B',
        'city': 'Ketchikan',
        'cp': '9901',
        'region': '2',
        'id_country': '8'
    };
    switch (country) {
        case "FR":
            adress['street'] = 'Rue de la paix';
            adress['city'] = 'PARIS';
            adress['cp'] = '75000';
            adress['region'] = '257';
            casper.echo("French Address", "COMMENT");
            break;
        case "BR":
            casper.echo("Brazilian Address", "COMMENT");
            break;
        case "NL":
            adress['street'] = 'Rue de la paix';
            adress['city'] = 'Amsterdam';
            adress['cp'] = '1000 AA';
            adress['region'] = '257';
            adress['id_country'] = '13';
            casper.echo("Netherlands Address", "COMMENT");
            break;
        default:
            casper.echo("US Address", "COMMENT");
    }

    return adress;
}

/**************************************
 *      Filling Shipping Method
 **************************************/

/**
 *
 * @param test
 */
exports.shippingMethod = function shippingMethod(test) {
    casper.then(function () {
        this.echo("Filling 'Shipping Method' formular...", "INFO");
        this.waitUntilVisible('section#checkout-delivery-step div.delivery-options-list input#delivery_option_2', function success() {
            this.click('section#checkout-delivery-step div.delivery-options-list input#delivery_option_2');
            this.click("section#checkout-delivery-step button.continue");
            this.echo("Done", "COMMENT");

        }, function fail() {
            test.assertVisible(
                "section#checkout-delivery-step div.delivery-options-list input#delivery_option_2",
                "'Shipping Method' formular or delivery option exists"
            );
        }, 50000);
    });
};

/**************************************
 *      Filling Payment Method
 **************************************/

/**
 *
 * @param test
 */
exports.fillStepPayment = function fillStepPayment(test, hostedFields) {
    casper.then(function () {
        this.echo(
            "Choosing payment method and filling 'Payment Information' formular with " + currentBrandCC + "...",
            "INFO"
        );
        this.waitUntilVisible('section#checkout-payment-step', function success() {
            this.echo(labelPayByCard);
            this.clickLabel(labelPayByCard, 'span');

            if (currentBrandCC == 'visa') {
                fillFormPaymentHipayCC(parametersLibHiPay.cardsNumber.visa, '666', hostedFields);
            } else if (currentBrandCC == 'cb' || currentBrandCC == "mastercard") {
                fillFormPaymentHipayCC(parametersLibHiPay.cardsNumber.cb, '666', hostedFields);
            } else if (currentBrandCC == 'amex') {
                fillFormPaymentHipayCC(parametersLibHiPay.cardsNumber.amex, '666', hostedFields);
            } else if (currentBrandCC == 'visa_3ds') {
                fillFormPaymentHipayCC(parametersLibHiPay.cardsNumber.visa_3ds, '666', hostedFields);
            } else if (currentBrandCC == 'maestro') {
                fillFormPaymentHipayCC(parametersLibHiPay.cardsNumber.maestro, '', hostedFields);
            }
            this.wait(10000, function () {
                this.click('form#conditions-to-approve input');
                this.click("div#payment-confirmation button");
                this.echo("Done", "COMMENT");
            });

        }, function fail() {
            test.assertVisible("section#checkout-payment-step", "'Payment Information' formular exists");
        }, 50000);
    });
};

/**
 *
 * @param card
 * @param cvv
 * @param hostedFields
 */
function fillFormPaymentHipayCC(card, cvv, hostedFields) {
    if (hostedFields) {
        casper.withFrame(0, function () {
            casper.sendKeys('input[name="ccname"]', 'Mr Test');
        });
        casper.withFrame(1, function () {
            casper.sendKeys('input[name="cardnumber"]', card);
        });
        casper.withFrame(2, function () {
            casper.sendKeys('input[name="cc-exp"]', '06/21');
        });
        casper.withFrame(3, function () {
            casper.sendKeys('input[name="cvc"]', cvv);
        });
    } else {
        casper.fillSelectors('form#tokenizerForm', {
            'input[name="card-number"]': card,
            'input[name="card-holders-name"]': 'Mr Test',
            'select[name="expiry-month"]': '02',
            'select[name="expiry-year"]': '20',
            'input[name="cvc"]': cvv
        }, false);
    }
}

/**
 *
 * @param test
 * @param labelMethod
 * @param confirm
 */
exports.selectMethodInCheckout = function selectMethodInCheckout(test, labelMethod, confirm) {
    casper.then(function () {
        this.echo("Choosing payment method...", "INFO");
        this.waitUntilVisible('section#checkout-payment-step', function success() {
            this.clickLabel(labelMethod, 'span');
            if (confirm) {
                this.click('form#conditions-to-approve input');
                this.click("div#payment-confirmation button");
            }
            this.echo("Done", "COMMENT");
        }, function fail() {
            test.assertVisible("section#checkout-payment-step", "'Payment Information' formular exists");
        }, 50000);
    });
};
