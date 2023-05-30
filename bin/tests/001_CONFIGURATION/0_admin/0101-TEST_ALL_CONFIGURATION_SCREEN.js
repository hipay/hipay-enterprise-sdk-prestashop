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
 *                         VALIDATION TEST ADMIN CONFIGURATION
 *
 *
 *  @purpose    : This scenario makes it possible to test that the configuration screens of the module are well constructed and that all the fields are visible.
 *  @Scenario   : Check that all fields are present, visible  and functional
 *  @screen     : Module Settings
 *
/**********************************************************************************************/

var paymentType = 'HiPay Enterprise Credit Card',
  currentBrandCC = utilsHiPay.getTypeCC();

casper.test.begin('Test admin configuration screens', function (test) {
  casper
    .start(baseURL)
    .then(function () {
      this.echo('Start test', 'INFO');
      if (
        (typeof casper.cli.get('type-cc') == 'undefined' &&
          currentBrandCC == 'visa') ||
        typeof casper.cli.get('type-cc') != 'undefined'
      ) {
        adminMod.logToBackend();
        adminMod.gotToHiPayConfiguration();
      }
    })
    .then(function () {
      /* Panel Module settings */
      this.echo("Test panel : 'Module Settings'", 'INFO');
      this.waitForSelector(
        'form#account_form',
        function success() {
          test.assertExists('form#account_form input[name="sandbox_mode"]');
          test.assertExists(
            'form#account_form input[name="api_username_production"]'
          );
          test.assertExists(
            'form#account_form input[name="api_password_production"]'
          );
          test.assertExists(
            'form#account_form input[name="api_secret_passphrase_production"]'
          );
          test.assertExists(
            'form#account_form input[name="api_tokenjs_username_production"]'
          );
          test.assertExists(
            'form#account_form input[name="api_tokenjs_password_publickey_production"]'
          );
          test.assertExists(
            'form#account_form input[name="api_moto_username_production"]'
          );
          test.assertExists('form#account_form div.alert');

          test.assertExists('form#account_form div.alert');
          test.assertExists(
            'form#account_form input[name="api_username_sandbox"]'
          );
          test.assertExists(
            'form#account_form input[name="api_password_sandbox"]'
          );
          test.assertExists(
            'form#account_form input[name="api_secret_passphrase_sandbox"]'
          );
          test.assertExists(
            'form#account_form input[name="api_tokenjs_username_sandbox"]'
          );
          test.assertExists(
            'form#account_form input[name="api_tokenjs_password_publickey_sandbox"]'
          );
          test.assertExists(
            'form#account_form input[name="api_moto_username_sandbox"]'
          );
          test.assertExists('form#account_form div#collapseTechnical');
          test.info("Panel 'Module Settings' is correct");
        },
        function fail() {
          test.assertExists(
            'form#account_form',
            'Formular Module Settings exists'
          );
        },
        35000
      );
    })
    .then(function () {
      /* Panel Payment method */
      this.echo("Test panel : 'Payment method'", 'INFO');
      this.waitForSelector(
        'ul.hipay-enterprise li a[href="#payment_form"]',
        function success() {
          this.click('ul.hipay-enterprise li a[href="#payment_form"]');
          test.assertExists('form#credit_card_form');
          test.assertExists(
            'form#credit_card_form select[name="operating_mode"]'
          );
          test.assertExists(
            'form#credit_card_form select#capture_mode.col-lg-2'
          );
          test.assertExists(
            'form#credit_card_form input#card_token_switchmode_on'
          );
          test.assertExists(
            'form#credit_card_form input#activate_basket_switchmode_on'
          );
          test.assertExists(
            'form#credit_card_form input#regenerate_cart_on_decline_switchmode_on'
          );
          test.assertExists(
            'form#credit_card_form input#log_infos_switchmode_on'
          );
          test.assertExists(
            'form#credit_card_form select[name="activate_3d_secure"]'
          );
          test.assertExists(
            'form#credit_card_form button[name="submitGlobalPaymentMethods"]'
          );
          test.assertExists('div#panel-credit-card');

          test.assertExists(
            'form#credit_card_form input[name="ccFrontPosition"]'
          );
          test.assertExists('form#credit_card_form div#visa');
          test.assertExists(
            'form#credit_card_form input[name="visa_activated"]'
          );
          test.assertExists(
            'form#credit_card_form input[name="visa_minAmount[EUR]"]'
          );
          test.assertExists(
            'form#credit_card_form input[name="visa_maxAmount[EUR]"]'
          );
          test.assertExists(
            'form#credit_card_form select[name="visa_currencies[]"]'
          );
          test.assertExists(
            'form#credit_card_form select[name="visa_countries[]_helper1"]'
          );
          test.assertExists(
            'form#credit_card_form button[name="creditCardSubmit"]'
          );

          test.assertExists('div#panel-local-payment');
          test.assertExists('div#panel-local-payment a[href="#oney"]');
          test.assertExists('div#panel-local-payment a[href="#giropay"]');
          test.assertExists('div#panel-local-payment a[href="#ideal"]');
          test.assertExists('div#panel-local-payment a[href="#paypal"]');
          test.assertExists('div#panel-local-payment a[href="#postfinance"]');
          test.assertExists('div#panel-local-payment a[href="#przelewy24"]');
          test.assertExists('div#panel-local-payment a[href="#sdd"]');
          test.assertExists(
            'div#panel-local-payment a[href="#sofort-uberweisung"]'
          );

          this.click('div#panel-local-payment a[href="#oney"]');
          test.assertExists('div#panel-local-payment #oney.active');

          test.assertExists(
            'div#panel-local-payment #oney.active input[name="3xcb-no-fees_activated"]'
          );
          test.assertExists(
            'div#panel-local-payment #oney.active input[name="3xcb-no-fees_frontPosition"]'
          );
          test.assertExists(
            'div#panel-local-payment #oney.active input[name="3xcb-no-fees_minAmount[EUR]"]'
          );
          test.assertExists(
            'div#panel-local-payment #oney.active input[name="3xcb-no-fees_maxAmount[EUR]"]'
          );
          test.assertExists(
            'div#panel-local-payment #oney.active input[name="3xcb-no-fees_currencies[]"]'
          );
          test.assertExists(
            'div#panel-local-payment #oney.active input[name="3xcb-no-fees_countries[]"]'
          );
        }
      );
    })
    .then(function () {
      /* Panel Fraud method */
      this.echo("Test panel : 'Fraud'", 'INFO');
      this.waitForSelector(
        'ul.hipay-enterprise li a[href="#fraud"]',
        function success() {
          this.click('ul.hipay-enterprise li a[href="#fraud"]');
          test.assertExists('form#configuration_form');
          test.assertExists(
            'form#configuration_form input[name="send_payment_fraud_email_copy_to"]'
          );
          test.assertExists(
            'form#configuration_form select[name="send_payment_fraud_email_copy_method"]'
          );
          test.assertExists(
            'form#configuration_form button[name="fraudSubmit"]'
          );
        }
      );
    })
    .then(function () {
      /* Panel FAQ method */
      this.echo("Test panel : 'FAQ'", 'INFO');
      this.waitForSelector(
        'ul.hipay-enterprise li a[href="#faq"]',
        function success() {
          this.click('ul.hipay-enterprise li a[href="#faq"]');
          test.assertExists('div#faq');
          test.assertExists('div#faq div#collapseOne');
          test.assertExists('div#faq div#collapseTwo');
        }
      );
    })
    .then(function () {
      /* Panel LOGS method */
      this.echo("Test panel : 'LOGS'", 'INFO');
      this.waitForSelector(
        'ul.hipay-enterprise li a[href="#logs"]',
        function success() {
          this.click('ul.hipay-enterprise li a[href="#logs"]');
          test.assertExists('div#logs');
          test.assertTextExists('Error Logs');
          test.assertTextExists('Info Logs');
          test.assertTextExists('Callback Logs');
          test.assertTextExists('Request Logs');
        }
      );
    })
    .run(function () {
      test.done();
    });
});
