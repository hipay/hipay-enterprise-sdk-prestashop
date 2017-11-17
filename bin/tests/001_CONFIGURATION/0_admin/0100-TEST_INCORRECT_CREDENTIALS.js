/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : CREDIT CART (DIRECT)
 *
 *
 *  @purpose    : This scenario makes it possible for the entry of the API identifiers to be taken into account for the tokenization and for the placement of the orders.
 *  @Scenario   : Entering false identifiers and test a command via API (Credit card), if the payment fails then the test is successful
 *  @screen     : Module Settings
 *
/**********************************************************************************************/

var paymentType = "HiPay Enterprise Credit Card",
    currentBrandCC = typeCC;

casper.test.begin('Test filling wrong credentials and pay ' + paymentType + ' with ' + currentBrandCC, function(test) {
    phantom.clearCookies();

    casper.start(baseURL)
    .then(function() {
        if(typeof casper.cli.get('type-cc') == "undefined" && currentBrandCC == "visa" || typeof casper.cli.get('type-cc') != "undefined") {
           authentification.proceed(test);
           this.gotToHiPayConfiguration();
           this.configureSettingsMode("api");
        }
    })
    .then(function() {
        /* Fill wrong api user name in module Settings */
        this.echo("Filling wrong credentials in Module Settings","INFO");
        this.waitForSelector('form#account_form input[name="api_username_sandbox"]' , function successful() {
            initialCredential = this.evaluate(function() { return document.querySelector('form#account_form input[name="api_username_sandbox"]').value; });
            test.info("Initial credential for api_username_sandbox was :" + initialCredential);

            this.fillSelectors('form#account_form', {
                'input[name="api_username_sandbox"]': "WRONG CREDENTIALS",
            }, false);
            this.click('button[name="submitAccount"]');
            this.waitForSelector('.alert.alert-success' , function success() {
                test.info("Filling wrong credentials in Module SettingsTes : Done");
            }, function fail(){
                test.assertExists('.alert.alert-success', "'Success' alert exists ");
            });
        }, function fail() {
            this.assertExists('form#account_form input[name="api_username_sandbox"]','Field "api_username_sandbox" exists')
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
        this.orderResultError(paymentType);
    })
    .thenOpen(baseURL, function() {
        authentification.proceed(test);
        this.gotToHiPayConfiguration();
    })
    .then(function() {
        /* Restore credentials with initials credentials  */
        this.echo("Restore true credentials in Module Settings");
        this.waitForSelector('form#account_form' , function success() {
            this.fillSelectors('form#account_form', {
                'input[name="api_username_sandbox"]': initialCredential,
            }, false);
            this.click('button[name="submitAccount"]');
            this.waitForSelector('.alert.alert-success' , function success() {
                test.info("Restore true credentials in Module Settings : Done");
            }, function fail(){
                test.assertExists('.alert.alert-success', "'Success' alert exists ");
            });
        }, function fail(){
            test.assertExists('form#account_form', "Formular Module Settings alert exists ");
        });

    })
    .run(function() {
        test.done();
    });
});
