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

var x = require('casper').selectXPath,
    defaultViewPortSizes = {width: 1920, height: 1080},
    baseURL = casper.cli.get('url'),
    psVersion = casper.cli.get('ps-version'),
    loginBackend = 'ogone.dev.test@gmail.com',
    passBackend = 'testcasperjs',
    loginPaypal = casper.cli.get('login-paypal'),
    passPaypal = casper.cli.get('pass-paypal'),
    headerModule = "../../Modules/",
    headerLib = "../../000_lib/node_modules/hipay-casperjs-lib/",
    urlBackend = "https://stage-merchant.hipay-tpp.com/",
    urlNotification = "index.php?fc=module&module=hipay_enterprise&controller=notify",
    utilsHiPay = require(headerModule + 'utils'),
    pathHeader = "bin/tests/",
    pathErrors = pathHeader + "errors/",
    labelPayByCard = 'Payer par Carte de crédit',
    admin_login = "demo@hipay.com",
    admin_passwd = "hipay123",
    order = require(headerModule + 'order'),
    parametersLibHiPay = require(headerLib + '0000-parameters'),
    paymentLibHiPay = require(headerLib + '0001-functions-payment'),
    backendLibHiPay = require(headerLib + '0002-functions-backend'),
    notificationLibHiPay = require(headerLib + '0003-functions-notification'),
    utilsLibHiPay = require(headerLib + '0004-utils');

if (psVersion == "1.7") {
    var adminMod = require(headerModule + '17/admin'),
        checkoutMod = require(headerModule + '17/checkout'),
        notificationMod = require(headerModule + '17/notification');
} else {
    var adminMod = require(headerModule + '16/admin'),
        checkoutMod = require(headerModule + '16/checkout'),
        notificationMod = require(headerModule + '16/notification');
}

casper.test.begin('Parameters', function (test) {

    var img = 0;
    test.on('fail', function () {
        img++;
        casper.echo("URL: " + casper.currentUrl, "WARNING");
        casper.capture(pathErrors + 'fail' + img + '.png');
        test.comment("Image 'fail" + img + ".png' captured into '" + pathErrors + "'");
        casper.echo('Tests réussis : ' + test.currentSuite.passes.length, 'WARNING');
    });

    //debug
    // casper.on('remote.message', function(message) {
    //     this.echo('remote message caught: ' + message);
    // });
    //
    // casper.on("resource.error", function(resourceError) {
    //     this.echo("Resource error: " + "Error code: "+resourceError.errorCode+" ErrorString: "+resourceError.errorString+" url: "+resourceError.url+" id: "+resourceError.id, "ERROR");
    // });
    //
    // casper.on("page.error", function(msg, trace) {
    //     this.echo("Error: " + msg, "ERROR");
    // });
    //
    // casper.on('resource.received', function(resource) {
    //     var status = resource.status;
    //     if(status >= 400) {
    //         casper.log('Resource ' + resource.url + ' failed to load (' + status + ')', 'error');
    //     }
    // });

    /* Set default viewportSize and UserAgent */
    casper.userAgent('Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36');
    casper.options.viewportSize = {width: defaultViewPortSizes["width"], height: defaultViewPortSizes["height"]};

    utilsHiPay.setTypeCC(casper.cli.get('type-cc'));

    if (utilsHiPay.getTypeCC() === undefined) {
        utilsHiPay.setTypeCC("visa");
    }

    /* Say if BackOffice TPP credentials are set or not */
    if (loginBackend && passBackend) {
        test.info("Backend credentials set");
    } else {
        test.comment("No Backend credentials");
    }

    if (loginPaypal && passPaypal) {
        test.info("PayPal credentials set");
    } else {
        test.comment("No PayPal credentials");
    }

    casper.echo('Paramètres chargés !', 'INFO');
    test.done();
});
