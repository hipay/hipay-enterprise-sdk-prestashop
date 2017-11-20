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

casper.test.begin('Functions', function(test) {



   casper.getOrderReference = function() {
        return orderReference;
   };

    /* Get order ID, if it exists, after purchase, and set it in variable */
	casper.setOrderReference = function(pending) {
		if(pending)
			var orderReference = this.fetchText(x('//p[contains(., "Order #")]')).split('#')[1];
		else {
			var text = this.fetchText(x('//li[contains(., "Référence de la commande : ")]')).split(':')[1];
			var orderReference = text.substring(1, text.length - 1);
		}
		test.info("Order Référence : " + orderReference);
	};

    casper.setCartId = function(pending) {
        cartId = $_GET('id_cart',this);
        test.info("Cart id : " + cartId);
    };

    casper.getCartId = function(pending) {
        return cartId;
        test.info("Cart id : " + orderID);
    };

    casper.setOrderId= function(pending) {
        orderID = $_GET('id_order',this);
        test.info("Order Id : " + orderID);
    };

	/* Get order ID variable value */
	casper.getOrderId = function() {
        if(typeof order == "undefined" || order == "")
            return orderID;
        else
            return order;
	};

	/* Check if success payment */
	casper.orderResultSuccess = function(paymentType) {
        this.echo("Checking order success...", "INFO");
        this.waitForUrl(/order-confirmation/, function success() {
            test.assertHttpStatus(200, "Correct HTTP Status Code 200");
            test.assertExists('#content-hook_order_confirmation', "VOTRE COMMANDE EST CONFIRMÉE");
            test.assertTextExists('Référence de la commande');
            this.setOrderReference(false);
            this.setOrderId(false);
            this.setCartId(false);
        }, function fail() {
        	test.info("Success payment page doesn't exists.", 'WARNING');
            this.echo("Checking order success with pending status ...", "INFO");
            this.waitForUrl(/pending/, function success() {
                test.assertHttpStatus(200, "Correct HTTP Status Code 200");
                test.done();
            }, function fail() {
                test.fail("Success payment (Pending status) doesn't exists.", 'WARNING');
            }, 30000);
        }, 30000);
	};

    /* Check if error payment */
    casper.orderResultError = function(paymentType) {
        this.echo("Checking order error ... ", "INFO");
        this.waitForSelector("form#tokenizerForm div#error-js ul li.error", function success() {
            test.assertHttpStatus(200, "Correct HTTP Status Code 200");
            test.assertExists("form#tokenizerForm div#error-js ul li.error", "Error message exists. Payment error");
        }, function fail() {
            test.assertExists("form#tokenizerForm div#error-js ul li.error", "Error message is not present");
        }, 30000);
    };

    /* Test file again with another card type */
    casper.testOtherTypeCC = function(file,new_typeCC) {
        casper.then(function() {
            this.echo("Configure Test other Type cc with" + new_typeCC + file, "INFO");
            if (new_typeCC && new_typeCC != typeCC ) {
                typeCC = new_typeCC;
                test.info("New type CC is configured and new test is injected");
                phantom.injectJs(pathHeader + file);
            } else if(typeof this.cli.get('type-cc') == "undefined") {
                if(typeCC == "visa") {
                    typeCC = "mastercard";
                    phantom.injectJs(pathHeader + file);
                }
                else {
                    typeCC = "visa"; // retour du typeCC à la normale --> VISA pour la suite des tests
                }
            } else {
                typeCC = "visa";
            }
        });
    };
    /* Test file again with another currency */
    casper.testOtherCurrency = function(file) {
        casper.then(function() {
            if(currentCurrency == allowedCurrencies[0]) {
                currentCurrency = allowedCurrencies[1];
                phantom.injectJs(pathHeader + file);
            }
            else if(currentCurrency == allowedCurrencies[1])
                currentCurrency = allowedCurrencies[0]; // retour du currency à la normale --> EURO pour la suite des tests
        });
    };
    /* Configure HiPay Enterprise options via formular */
    casper.fillFormHipayEnterprise = function(credentials, moto) {
        var stringMoto = "";
        if(moto)
            stringMoto = " MOTO";
        if(credentials == "blabla")
            this.echo("Editing Credentials" + stringMoto + " configuration with bad credentials...", "INFO");
        else
            this.echo("Reinitializing Credentials" + stringMoto + " configuration...", "INFO");
        if(moto)
            this.fillSelectors("form#config_edit_form", { 'input[name="groups[hipay_api_moto][fields][api_username_test][value]"]': credentials }, false);
        else
            this.fillSelectors("form#config_edit_form", { 'input[name="groups[hipay_api][fields][api_username_test][value]"]': credentials }, false);
        this.click(x('//span[text()="Save Config"]'));
        this.waitForSelector(x('//span[contains(.,"The configuration has been saved.")]'), function success() {
            test.info("HiPay Enterprise credentials configuration done");
        }, function fail() {
            test.fail('Failed to apply HiPay Enterprise credentials configuration on the system');
        },20000);
    };

    /* Configure Device Fingerprint options via formular */
    casper.setDeviceFingerprint = function(state) {
        this.echo("Accessing Hipay Enterprise menu...", "INFO");
        this.click(x('//span[text()="Configuration"]'));
        this.waitForUrl(/admin\/system_config/, function success() {
            this.click(x('//span[contains(., "HiPay Enterprise")]'));
            test.info("Done");
            this.waitForSelector(x('//h3[text()="HiPay Enterprise"]'), function success() {
                this.echo("Changing 'Device Fingerprint' field...", "INFO");
                var valueFingerprint = this.evaluate(function() { return document.querySelector('select[name="groups[hipay_api][fields][fingerprint][value]"]').value; });
                if(valueFingerprint == state)
                    test.info("Device Fingerprint configuration already done");
                else {
                    this.fillSelectors("form#config_edit_form", {
                        'select[name="groups[hipay_api][fields][fingerprint][value]"]': state
                    }, false);
                    this.click(x('//span[text()="Save Config"]'));
                    this.waitForSelector(x('//span[contains(.,"The configuration has been saved.")]'), function success() {
                        test.info("Device Fingerprint Configuration done");
                    }, function fail() {
                        test.fail('Failed to apply Device Fingerprint Configuration on the system');
                    }, 15000);
                }
            }, function fail() {
                test.assertExists(x('//h3[text()="HiPay Enterprise"]'), "Hipay Enterprise admin page exists");
            }, 10000);
        }, function fail() {
            test.assertUrlMatch(/admin\/system_config/, "Configuration admin page exists");
        }, 10000);
    };

    /* Go to HiPay configuration panel  */
    casper.gotToHiPayConfiguration = function () {
        this.echo("Go to HiPay panel configuration", "INFO");
        this.waitForSelector('ul.menu #subtab-AdminParentModulesSf a', function success() {
            this.click('ul.menu #subtab-AdminParentModulesSf a');
            this.waitForSelector(x('//a[text()="Modules installés"]'), function success() {
                this.click(x('//a[text()="Modules installés"]'));
                this.waitForSelector('#modules-list-container-all div[data-name="HiPay Enterprise"] form.btn-group button', function success() {
                    this.click('#modules-list-container-all div[data-name="HiPay Enterprise"] form.btn-group button');
                    test.info("Done");
                }, function fail() {
                    test.assertExists('#modules-list-container-all div[data-name="HiPay Enterprise"] form.btn-group button',"'Configuration' button exists");
                }, 10000);
            }, function fail() {
                test.assertExists(x('//a[text()="Modules installés"]'), "Installed Modules admin page exists");
            }, 10000);
        }, function fail() {
            test.assertExists('ul.menu #subtab-AdminParentModulesSf a', "Modules admin page exists");
        }, 10000);
    };

    /* Configure Operating mode  ( mode=api|hosted_page )  */
    casper.configureSettingsMode = function (mode) {
        this.echo("Configure settings mode with " + mode + " ...", "INFO");
        this.waitForSelector('ul.hipay-enterprise li a[href="#payment_form"]', function success() {
            this.click('ul.hipay-enterprise li a[href="#payment_form"]');
            this.waitForSelector('form#credit_card_form', function success() {

                this.fillSelectors("form#credit_card_form",
                    {'select[name="operating_mode"]': mode },
                false
                );
                this.click('form#credit_card_form div.panel-footer button[name="submitGlobalPaymentMethods"]');
                this.waitForSelector('.alert.alert-success' , function success() {
                    test.info("Configure settings mode : Done");
                }, function fail(){
                    test.assertExists('.alert.alert-success', "'Success' alert exists ");
                });
            }, function fail() {
                test.assertExists('form#credit_card_form', "'Credit card' form exists ");
            }, 10000);
        }, function fail() {
            test.assertExists(x('//span[text()="Modules"]'), "Modules admin page exists");
        }, 10000);
    },

    /* Configure Capture mode  ( mode=automatic|manual )  */
    casper.configureCaptureMode = function (mode) {
        this.echo("Configure capture mode with " + mode + " ...", "INFO");
        this.waitForSelector('ul.hipay-enterprise li a[href="#payment_form"]', function success() {
            this.click('ul.hipay-enterprise li a[href="#payment_form"]');
            this.waitForSelector('form#credit_card_form', function success() {

                this.fillSelectors("form#credit_card_form",
                    {'select[name="capture_mode"]': mode },
                    false
                );
                this.click('form#credit_card_form div.panel-footer button[name="submitGlobalPaymentMethods"]');
                this.waitForSelector('.alert.alert-success' , function success() {
                    test.info("Configure settings mode : Done");
                }, function fail(){
                    test.assertExists('.alert.alert-success', "'Success' alert exists ");
                });
            }, function fail() {
                test.assertExists('form#credit_card_form', "'Credit card' form exists ");
            }, 10000);
        }, function fail() {
            test.assertExists(x('//span[text()="Modules"]'), "Modules admin page exists");
        }, 10000);
    },

    /* Configure Operating mode  ( type=redirect|iframe)  */
    casper.configureHostedDisplay = function (type) {
        this.echo("Configure display settings with " + type + " ...", "INFO");
        this.waitForSelector('ul.hipay-enterprise li a[href="#payment_form"]', function success() {
            this.click('ul.hipay-enterprise li a[href="#payment_form"]');
            this.waitForSelector('form#credit_card_form', function success() {
                this.fillSelectors("form#credit_card_form",
                    {'select[name="display_hosted_page"]': type },
                    false
                );
                this.click('form#credit_card_form div.panel-footer button[name="submitGlobalPaymentMethods"]');
                this.waitForSelector('.alert.alert-success' , function success() {
                    test.info("Configure settings mode : Done");
                }, function fail(){
                    test.assertExists('.alert.alert-success', "'Success' alert exists ");
                });
            }, function fail() {
                test.assertExists('form#credit_card_form', "'Credit card' form exists ");
            }, 10000);
        }, function fail() {
            test.assertExists(x('//span[text()="Modules"]'), "Modules admin page exists");
        }, 10000);
    },

    /* Set value for options payment for local */
    casper.setValueOptions= function(code,value) {
        this.echo("Configure " + code + " with value : " + value + " ...", "INFO");
        this.waitForSelector('ul.hipay-enterprise li a[href="#payment_form"]', function success() {
            this.click('ul.hipay-enterprise li a[href="#payment_form"]');
            this.waitForSelector('form#local_payment_form', function success() {
                var fill = {};
                fill['input[name="' + code + '"]'] = value;
                this.fillSelectors("form#local_payment_form",
                    fill,
                    false
                );
                this.click('form#local_payment_form div.panel-footer button[name="localPaymentSubmit"]');
                this.waitForSelector('.alert.alert-success' , function success() {
                    test.info(" Done");
                }, function fail(){
                    test.assertExists('.alert.alert-success', "'Success' alert exists ");
                });
            }, function fail() {
                test.assertExists('form#local_payment_for', "'Credit card' form exists ");
            }, 10000);
        }, function fail() {
            test.assertExists(x('//span[text()="Modules"]'), "Modules admin page exists");
        }, 10000);
    },


    /* Activate Payment local method with code */
    casper.activateMethod = function(code) {
        var field=code + "_activated";
        this.setValueOptions(field,"1");
    },

    /* Import and Activate localization pack*/
    casper.activateLocalization = function (locale) {
        this.echo("Import and activate localization : " + locale, "INFO");
        this.waitForSelector('#subtab-AdminParentLocalization a', function success() {
            this.click('#subtab-AdminInternational a');
            this.click('#subtab-AdminParentLocalization a');
            this.waitForSelector('select[name="iso_localization_pack"]', function success() {
                this.fillSelectors("form#configuration_form_4",
                    {'select[name="iso_localization_pack"]': locale.toLowerCase()},
                    false
                );
                this.click('form#configuration_form_4 button[name="submitLocalizationPack"]');
                this.waitForSelector('.alert.alert-success', function success() {
                    test.info("Import done");
                    /* Associate Payment module with locale */
                    this.click('#subtab-AdminParentPayment a');

                    this.waitForSelector('#subtab-AdminPaymentPreferences a', function success() {
                        this.click('#subtab-AdminPaymentPreferences a');
                        switch (locale) {
                            case "BR":
                                var id_country=58;
                                break;
                            case "NL":
                                var id_country=13;
                        }

                        this.waitForSelector('form#form_country', function success() {
                            var alreadyChecked = this.getElementAttribute('form#form_country input[value="' + id_country + '"][name="hipay_enterprise_country[]"]', 'checked');
                            if (alreadyChecked.length === 0) {
                                this.echo("Activate country for payment module", "INFO");
                                this.click('form#form_country input[value="' + id_country + '"][name="hipay_enterprise_country[]"]');
                                this.click('button[name="submitModulecountry"]');
                                this.waitForSelector('.alert.alert-success', function success() {
                                    test.info("Done");
                                }, function fail() {
                                    test.assertExists('.alert.alert-success',"Activate country for payment module error");
                                });
                            }
                        }, function fail() {
                            test.assertExists('form#form_country', "Form country exists");
                        }, 15000);
                    });
                }, function fail() {
                    test.assertExists('.success',"'Import pack localization failed' button exists");
                }, 30000);
            }, function fail() {
                test.assertExists('select[name="iso_localization_pack"]', "Localization input exists");
            }, 35000);
        }, function fail() {
            test.assertExists('#subtab-AdminParentLocalization a', "Modules admin page exists");
        }, 30000);
    };

	casper.echo('Fonctions Adnimistration loaded !', 'INFO');
	test.info("Based URL: " + baseURL);
    test.done();
});