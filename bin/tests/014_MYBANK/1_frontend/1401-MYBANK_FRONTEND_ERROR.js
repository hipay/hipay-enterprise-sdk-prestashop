/**********************************************************************************************
 *
 *                       VALIDATION TEST METHOD : MyBank
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
 /**********************************************************************************************/

var paymentType = "HiPay Enterprise MyBank";

casper.test.begin('Test Checkout ' + paymentType, function (test) {
    phantom.clearCookies();

    var label;

    casper.start(baseURL + "admin/")
        .then(function () {
            adminMod.logToBackend(test);
        })
        .then(function () {
            adminMod.gotToHiPayConfiguration(test);
        })
        .then(function () {
            adminMod.activateMethod(test, "mybank");
        })
        .then(function () {
            this.waitForSelector('input[name="mybank_displayName[fr]"]', function success() {
                label = this.getElementAttribute('input[name="mybank_displayName[fr]"]', 'value');
                test.info("Display name in checkout should be :" + label);
            }, function fail() {
                test.assertExists('input[name="mybank_displayName[fr]"]', "Input name exist");
            });
        })
        .thenOpen(baseURL, function () {
            checkoutMod.selectItemAndOptions(test);
        })
        .then(function () {
            checkoutMod.personalInformation(test);
        })
        .then(function () {
            checkoutMod.billingInformation(test, 'FR');
        })
        .then(function () {
            checkoutMod.shippingMethod(test);
        })
        .then(function () {
            test.assertDoesntExist(x('//%s[text()=Payer par ' + label + ']'));
        })
        .run(function () {
            test.done();
        });
});
