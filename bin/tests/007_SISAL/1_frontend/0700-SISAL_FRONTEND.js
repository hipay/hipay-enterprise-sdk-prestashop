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
 *                       VALIDATION TEST METHOD : SISAL
 *
 *  To launch test, please pass two arguments URL (BASE URL)  and TYPE_CC ( CB,VI,MC )
 *
 /**********************************************************************************************/

var paymentType = 'HiPay Enterprise Mooney';

casper.test.begin('Test Checkout ' + paymentType, function (test) {
  var label;

  phantom.clearCookies();

  casper
    .start(baseURL)
    .then(function () {
      adminMod.logToBackend(test);
    })
    .then(function () {
      adminMod.gotToHiPayConfiguration(test);
    })
    .then(function () {
      adminMod.activateMethod(test, 'sisal');
    })
    .then(function () {
      this.waitForSelector(
        'input[name="sisal_displayName[fr]"]',
        function success() {
          label = this.getElementAttribute(
            'input[name="sisal_displayName[fr]"]',
            'value'
          );
          test.info('Display name in checkout should be :' + label);
        },
        function fail() {
          test.assertExists(
            'input[name="sisal_displayName[fr]"]',
            'Input name exist'
          );
        }
      );
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
      checkoutMod.selectMethodInCheckout(test, 'Payer par ' + label, true);
    })
    .then(function () {
      paymentLibHiPay.fillPaymentFormularByPaymentProduct('sisal', test);
    })
    .then(function () {
      adminMod.orderResultSuccess(test);
    })
    .run(function () {
      test.done();
    });
});
