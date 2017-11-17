/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : POST FINANCE CARD
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
/**********************************************************************************************/

var paymentType = "HiPay Fullservice ONEY Facily Pay";

casper.test.begin('Test Checkout ' + paymentType, function(test) {
    phantom.clearCookies();

    casper.start(baseURL)
    .then(function() {
        authentification.proceed(test);
        this.gotToHiPayConfiguration();
        this.activateMethod("3xcb");
        this.waitForSelector('input[name="3xcb_displayName"]', function success() {
            label = this.getElementAttribute('input[name="3xcb_displayName"]', 'value');
            test.info("Display name in checkout should be :" + label);
        }, function fail() {
            test.assertExists('input[name="3xcb_displayName"]', "Input name exist");
        });
    })
    .then(function() {
        this.echo("Do the delivery method matching...", "INFO");
        this.waitForSelector('ul.hipay-enterprise li a[href="#carrier-mapping"]', function success() {
            this.click('ul.hipay-enterprise li a[href="#carrier-mapping"]');
            this.waitForSelector('form#category_form', function success() {
                /* Test if alert and table is built*/
                test.assertExists('.alert.alert-info');
                test.assertExists('form#category_form .form-group table.table');

                /* Test filling for default delivery method 'My Carrier' */
                this.fillSelectors("#carrier-mapping form#category_form",
                    {'input[name="ps_map_prep_eta_2"]': 1 },
                    {'input[name="ps_map__delivery_eta_2"]': 1 },
                    {'select[name="hipay_map_mode_2"]': "STORE" },
                    {'select[name="hipay_map_shipping_2"]': "STANDARD" },
                    false
                );
                this.click('form#category_form div.panel-footer button[name="submitCarrierMapping"]');
                this.waitForSelector('.alert.alert-success' , function success() {
                    test.info("Done");
                }, function fail(){
                    test.assertExists('.alert.alert-success', "'Success' alert exists ");
                });
            }, function fail() {
                test.assertExists('form#category_form', "'Delivery Method delivery' form exists ");
            }, 10000);
        }, function fail() {
            test.assertExists('ul.hipay-enterprise li a[href="#carrier-mapping"]', "Delivery mapping admin page exists");
        }, 10000);
    })
        .then(function() {
            this.echo("Do the category matching...", "INFO");
            this.waitForSelector('ul.hipay-enterprise li a[href="#category-mapping"]', function success() {
                this.click('ul.hipay-enterprise li a[href="#category-mapping"]');
                this.waitForSelector('form#category_form', function success() {
                    /* Test if alert and table is built*/
                    test.assertExists('.alert.alert-info');
                    test.assertExists('form#category_form .form-group table.table');

                    /* Test filling for default delivery method 'My Carrier' */
                    this.fillSelectors("#category-mapping form#category_form",
                        {'select[name="hipay_map_3"]': "3" }, // Home & Appliance
                        false
                    );
                    this.click('form#category_form div.panel-footer button[name="submitCategoryMapping"]');
                    this.waitForSelector('.alert.alert-success' , function success() {
                        test.info("Configure settings mode : Done");
                    }, function fail(){
                        test.assertExists('.alert.alert-success', "'Success' alert exists ");
                    });
                }, function fail() {
                    test.assertExists('form#category_form', "'Delivery Method delivery' form exists ");
                }, 10000);
            }, function fail() {
                test.assertExists('ul.hipay-enterprise li a[href="#category-mapping"]', "Delivery mapping admin page exists");
            }, 10000);
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
        this.selectMethodInCheckout("Payer par " + label,true);
    })
    .then(function() {
        this.fillPaymentFormularByPaymentProduct("3xcb");
    })
    .then(function() {
        this.orderResultSuccess(paymentType);
    })
    .run(function() {
        test.done();
    });
});