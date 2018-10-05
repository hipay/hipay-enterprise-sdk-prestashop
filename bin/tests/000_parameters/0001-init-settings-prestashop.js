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

/**********************************************************************************************
 *
 *                       Activate option to improve prestashop
 *
 *
 /**********************************************************************************************/
casper.test.begin('Activate cache to improve prestashop performances', function (test) {
    phantom.clearCookies();

    casper.start(baseURL)
        .thenOpen(urlBackend, function () {
            backendLibHiPay.logToHipayBackend(test, loginBackend, passBackend);
        })
        .then(function () {
            backendLibHiPay.selectAccountBackend(test, "OGONE_DEV");
        })
        /* Open Integration tab */
        .then(function () {
            this.echo("Open Integration nav", "INFO");
            this.waitForUrl(/maccount/, function success() {
                backendLibHiPay.selectHashingAlgorithm(test, "SHA1");
            }, function fail() {
                test.assertUrlMatch(/maccount/, "Dashboard page with account ID exists");
            })
        })
        .then(function () {
            adminMod.logToBackend(test);
        })
        .then(function () {
            adminMod.gotToHiPayConfiguration(test);
            adminMod.configureOperatingMode(test, "direct_post");
            adminMod.configureCaptureMode(test, "automatic");
        })
        .then(function () {
            this.echo("Activate cache to optimize response time", "INFO");
       //     adminMod.activateCache(test);
            return true;
        })
        .run(function () {
            test.done();
        });
});
