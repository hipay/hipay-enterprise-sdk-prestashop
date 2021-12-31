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
 * Log to Prestashop Backend
 * @param test
 */
exports.logToBackend = function logToBackend(test) {
    casper.thenOpen(baseURL + "/admin-hipay", function () {
        this.echo("Connecting to Prestashop admin panel...", "INFO");

        this.waitForSelector("#login_form", function success() {
            this.fillSelectors('form#login_form', {
                'input[name="email"]': admin_login,
                'input[name="passwd"]': admin_passwd
            }, false);
            this.click('.form-group button[type=submit]');
            this.waitForUrl(/AdminDashboard/, function success() {
                this.echo("Connected", "INFO");
            }, function fail() {
                test.assertExists(".error-msg", "Incorrect credentials !");
            }, 50000);
        }, function fail() {
            this.waitForUrl(/controller=/, function success() {
                this.echo("Already logged to admin panel !", "INFO");
            }, function fail() {
                test.assertUrlMatch(/controller=/, "Already connected");
            }, 50000);
        }, 50000);
    });
};

/**
 * Activate PrestaShop Cache
 * @param test
 */
exports.activateCache = function activateCache(test) {
    casper.then(function () {
        this.waitForSelector('ul.main-menu #subtab-AdminPerformance', function success() {
            this.click('ul.main-menu li#subtab-AdminAdvancedParameters > a');

            this.waitForText('Informations de configuration', function () {
                this.click('li#subtab-AdminPerformance > a');

                this.waitForSelector('select[name="form[smarty][cache]"]', function success() {
                    this.fillSelectors("form[name='form']", {
                            'select[name="form[smarty][cache]"]': 1,
                            'select[name="form[smarty][caching_type]"]': "filesystem"
                        }, true
                    );

                    this.waitForSelector('div.alert.alert-success', function success() {
                        this.echo("Done", "INFO");
                    }, function fail() {
                        test.assertExists('div.alert.alert-success', 'Update configuration success')
                    }, 50000);

                }, function fail() {
                    test.assertExists('select[name="form[smarty][cache]"]', "Cache exists");
                }, 50000);

            }, function fail() {
                test.assertTextExist('INFORMATIONS DE CONFIGURATION', "Configuration page exist");
            }, 50000);

        }, function fail() {
            test.assertExists('ul.main-menu #subtab-AdminPerformance', "Performance page exists");
        }, 50000);

    });
};

/**
 * Go to Module configuration page
 * @param test
 */
exports.gotToHiPayConfiguration = function gotToHiPayConfiguration(test) {
    casper.then(function () {
        this.echo("Go to HiPay panel configuration 1.7", "INFO");
        this.waitForSelector('ul.main-menu #subtab-AdminParentModulesSf a', function success() {
            this.click('ul.main-menu #subtab-AdminParentModulesSf a');

            this.waitForSelector('#subtab-AdminModulesSf a', function success() {
                this.click('#subtab-AdminModulesSf a');

                this.waitForUrl(/improve\/modules\/manage/, function () {
                    var url = casper.getCurrentUrl();
                    var myRegexp = /token=(.*)(&*)/g;
                    var token = myRegexp.exec(url);
                    casper.thenOpen(baseURL + "admin-hipay/index.php/improve/modules/manage/action/configure/hipay_enterprise?_token=" + token[1]);
                });

            }, function fail() {
                test.assertExists('subtab-AdminModulesSf a', "Modules admin page exists");
            }, 50000);
        }, function fail() {
            test.assertExists('ul.menu #subtab-AdminParentModulesSf a', "Modules admin page exists");
        }, 50000);
    })
};

/**
 * Configure Payment Operating mode
 * @param test
 * @param mode api|hosted_page
 */
exports.configureOperatingMode = function configureOperatingMode(test, mode) {
    casper.then(function () {
        this.echo("Configure settings mode with " + mode + " ...", "INFO");
        this.waitForSelector('ul.hipay-enterprise li a[href="#payment_form"]', function success() {
            this.click('ul.hipay-enterprise li a[href="#payment_form"]');

            this.waitForSelector('form#credit_card_form', function success() {
                this.fillSelectors("form#credit_card_form",
                    {'select[name="operating_mode"]': mode},
                    false
                );
                this.click('form#credit_card_form div.panel-footer button[name="submitGlobalPaymentMethods"]');

                this.waitForSelector('.alert.alert-success', function success() {
                    this.echo("Configure settings mode : Done", "INFO");
                }, function fail() {
                    test.assertExists('.alert.alert-success', "'Success' alert exists ");
                }, 50000);

            }, function fail() {
                test.assertExists('form#credit_card_form', "'Credit card' form exists ");
            }, 50000);

        }, function fail() {
            test.assertExists('ul.hipay-enterprise li a[href="#payment_form"]', "Modules admin page exists");
        }, 50000);
    })
};

/**
 * Fill username in sandbox credentials form
 * @param test
 * @param username
 */
exports.fillCredentials = function fillCredentials(test, username) {
    casper.then(function () {
        this.waitForSelector('form#account_form input[name="api_username_sandbox"]', function successful() {
            this.fillSelectors('form#account_form', {
                'input[name="api_username_sandbox"]': username,
            }, false);
            this.click('button[name="submitAccount"]');

            this.waitForSelector('.alert.alert-success', function success() {
                this.echo("Filling credentials (" + username + ") in Module : Done", "INFO");

            }, function fail() {
                test.assertExists('.alert.alert-success', "'Success' alert exists ");
            }, 50000);

        }, function fail() {
            this.assertExists('form#account_form input[name="api_username_sandbox"]', 'Field "api_username_sandbox" exists')
        }, 50000);
    });
};

/**
 * Get username from sandbox credentials form
 * @param test
 * @returns {*}
 */
exports.getCredentials = function getCredentials() {
    return casper.evaluate(function () {
        return document.querySelector('form#account_form input[name="api_username_sandbox"]').value;
    });
};

/**
 *
 * @param test
 * @param mode
 */
exports.configureCaptureMode = function configureCaptureMode(test, mode) {
    casper.then(function () {
        this.echo("Configure capture mode with " + mode + " ...", "INFO");
        this.waitForSelector('ul.hipay-enterprise li a[href="#payment_form"]', function success() {
            this.click('ul.hipay-enterprise li a[href="#payment_form"]');

            this.waitForSelector('form#credit_card_form', function success() {

                this.fillSelectors("form#credit_card_form",
                    {'select[name="capture_mode"]': mode},
                    false
                );
                this.click('form#credit_card_form div.panel-footer button[name="submitGlobalPaymentMethods"]');

                this.waitForSelector('.alert.alert-success', function success() {
                    this.echo("Configure settings mode : Done", "COMMENT");

                }, function fail() {
                    test.assertExists('.alert.alert-success', "'Success' alert exists ");
                }, 50000);

            }, function fail() {
                test.assertExists('form#credit_card_form', "'Credit card' form exists ");
            }, 50000);

        }, function fail() {
            test.assertExists(x('//span[text()="Modules"]'), "Modules admin page exists");
        }, 50000);
    });
};

exports.orderResultSuccess = function orderResultSuccess(test) {
    casper.then(function () {
        this.echo("Checking order success...", "INFO");
        this.waitForUrl(/order-confirmation/, function success() {
            test.assertHttpStatus(200, "Correct HTTP Status Code 200");
            test.assertExists('#content-hook_order_confirmation', "VOTRE COMMANDE EST CONFIRMÉE");
            test.assertTextExists('Référence de la commande');
            order.setReference(false);
            order.setId(false);
            order.setCartId(false);
        }, function fail() {
            this.echo("Success payment page doesn't exists.", "COMMENT");
            this.echo("Checking order success with pending status ...", "INFO");
            this.waitForUrl(/pending/, function success() {
                test.assertHttpStatus(200, "Correct HTTP Status Code 200");
                test.done();
            }, function fail() {
                test.fail("Success payment (Pending status) doesn't exists.", 'WARNING');
            }, 50000);
        }, 50000);
    });
};

/**
 * Configure Operating mode
 * @param test
 * @param type redirect|iframe
 */
exports.configureHostedDisplay = function configureHostedDisplay(test, type) {
    casper.then(function () {
        this.echo("Configure display settings with " + type + " ...", "INFO");
        this.waitForSelector('ul.hipay-enterprise li a[href="#payment_form"]', function success() {
            this.click('ul.hipay-enterprise li a[href="#payment_form"]');
            this.waitForSelector('form#credit_card_form', function success() {
                this.fillSelectors("form#credit_card_form",
                    {'select[name="display_hosted_page"]': type},
                    false
                );
                this.click('form#credit_card_form div.panel-footer button[name="submitGlobalPaymentMethods"]');
                this.waitForSelector('.alert.alert-success', function success() {
                    this.echo("Configure settings mode : Done ", "COMMENT");
                }, function fail() {
                    test.assertExists('.alert.alert-success', "'Success' alert exists ");
                }, 50000);
            }, function fail() {
                test.assertExists('form#credit_card_form', "'Credit card' form exists ");
            }, 50000);
        }, function fail() {
            test.assertExists(x('//span[text()="Modules"]'), "Modules admin page exists");
        }, 50000);
    });
};

/**
 * Activate Payment local method with code
 *
 * @param code
 */
exports.activateMethod = function activateMethod(test, code) {
    var field = code + "_activated";
    setValueOptions(test, field, "1");
};

/**
 * Set config for Payment local method with code
 *
 * @param test
 * @param code
 * @param field
 * @param value
 */
exports.setConfigLocal = function activateMethod(test, code, field, value) {
    var field = code + "_" + field;
    setValueOptions(test, field, value);
};

/**
 * Set value for options payment for local
 *
 * @param test
 * @param code
 * @param value
 */
function setValueOptions(test, code, value) {
    casper.then(function () {
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
                this.waitForSelector('.alert.alert-success', function success() {
                    this.echo("Done ", "COMMENT");
                }, function fail() {
                    test.assertExists('.alert.alert-success', "'Success' alert exists ");
                }, 50000);
            }, function fail() {
                test.assertExists('form#local_payment_for', "'Credit card' form exists ");
            }, 50000);
        }, function fail() {
            test.assertExists(x('//span[text()="Modules"]'), "Modules admin page exists");
        }, 50000);
    });
}

/**
 *
 * @param test
 * @param locale
 */
exports.activateLocalization = function activateLocalization(test, locale) {
    casper.then(function () {
        this.echo("Import and activate localization : " + locale, "INFO");
        this.waitForSelector('#subtab-AdminInternational a', function success() {
            this.click('#subtab-AdminInternational a');
            this.click('#subtab-AdminParentLocalization a');
            this.waitForSelector('select[name="import_localization_pack[iso_localization_pack]"]', function success() {
                this.fillSelectors('form[name="import_localization_pack"]',
                    {'select[name="import_localization_pack[iso_localization_pack]"]': locale.toLowerCase()},
                    true
                );
                //this.click('form#configuration_form button[name="submitLocalizationPack"]');
                this.waitForSelector('.alert.alert-success', function success() {
                    this.echo("Import Done ", "COMMENT");
                    /* Associate Payment module with locale */
                    this.click('#subtab-AdminParentPayment a');

                    this.waitForSelector('#subtab-AdminPaymentPreferences a', function success() {
                        this.click('#subtab-AdminPaymentPreferences a');
                        switch (locale) {
                            case "BR":
                                var id_country = 58;
                                break;
                            case "NL":
                                var id_country = 13;
                                break;
                            case "IT":
                                var id_country = 10;
                                break;
                        }

                        this.waitForSelector('form[name="form"]', function success() {
                            var alreadyChecked = this.getElementAttribute('form[name="form"] input[value="' + id_country + '"][name="form[payment_module_preferences][country_restrictions][hipay_enterprise][]"]', 'checked');
                            if (alreadyChecked.length === 0) {
                                this.echo("Activate country for payment module", "INFO");
                                // this.click('.md-checkbox > label');
                                // this.click('form[name="form"] button');
                                var field = 'input[value="' + id_country + '"][name="form[payment_module_preferences][country_restrictions][hipay_enterprise][]"]';

                                var fillForm = {};

                                fillForm[field] = true;

                                this.fillSelectors('form[name="form"]', fillForm, true);

                                this.waitForSelector('.alert.alert-success > .alert-text', function success() {
                                    this.click('form[name="form"] button');
                                    this.echo("Done ", "COMMENT");
                                }, function fail() {
                                    test.assertExists('.alert.alert-success', "Activate country for payment module error");
                                });
                            }
                        }, function fail() {
                            test.assertExists('form[name="form"]', "Form country exists");
                        }, 50000);
                    });
                }, function fail() {
                    test.assertExists('.success', "'Import pack localization failed' button exists");
                }, 50000);
            }, function fail() {
                test.assertExists('select[name="import_localization_pack[iso_localization_pack]"]', "Localization input exists");
            }, 50000);
        }, function fail() {
            test.assertExists('#subtab-AdminInternational a', "Modules admin page exists");
        }, 50000);
    });
};
