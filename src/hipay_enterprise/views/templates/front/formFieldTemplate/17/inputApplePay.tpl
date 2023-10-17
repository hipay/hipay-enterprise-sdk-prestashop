{**
 * HiPay Enterprise SDK Prestashop
 *
 * 2017 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright 2017 HiPay
 * @license   https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 *}
<div class="form-group row">
    <div class="col-md-9" id="apple-pay-form">
        <div id="apple-pay-button" style="height: 30px"></div>
        <input type="hidden" name="card-token" id="apple-pay-card-token" value="" required />
        <input type="hidden" name="card-brand" id="apple-pay-card-brand" value="" required />
        <input type="hidden" name="card-pan" id="apple-pay-card-pan" value="" required />
        <input type="hidden" name="card-holder" id="apple-pay-card-holder" value="" />
        <input type="hidden" name="card-expiry-month" id="apple-pay-card-expiry-month" value="" required />
        <input type="hidden" name="card-expiry-year" id="apple-pay-card-expiry-year" value="" required />
        <input type="hidden" name="card-issuer" id="apple-pay-card-issuer" value="" />
        <input type="hidden" name="card-country" id="apple-pay-card-country" value="" />
        <input type="hidden" name="is-apple-pay" value="true" />
        <p id="apple-pay-info-message" style="display: none;"></p>
        <span class="error-text-hp" id="apple-pay-error-message"></span>
        <p id="apple-pay-termes-of-service-error-message" style="display: none">
          {l s='Please accept the terms of service.' mod='hipay_enterprise'}
        </p>
    </div>
</div>
<script>
  var submitButton;
  document.addEventListener('DOMContentLoaded', function () {
    submitButton = $('#payment-confirmation button');

    const parameters = {
      api_apple_pay_username: '{$HiPay_credentials.api_apple_pay_username}',
      api_apple_pay_password: '{$HiPay_credentials.api_apple_pay_password}',
      environment: '{$HiPay_environment}',
      language_iso_code: '{$HiPay_language_iso_code}',
      buttonType: '{$HiPay_appleFields.buttonType[0]}',
      buttonStyle: '{$HiPay_appleFields.buttonStyle[0]}',
      totalAmount: '{$HiPay_cart.totalAmount}',
      shopName: '{Configuration::get('PS_SHOP_NAME')}'
    };

    initApplePay(parameters);
  }, false);

  /**
   * Create Apple Pay button
   */
  function initApplePay(parameters) {
    handleSubmitButton();


    if (canMakeApplePayPayment()) {
      handleTermsOfService();

      $('#apple-pay-button').hide();
      $('#apple-pay-info-message').hide();

      $('#apple-pay-error-message').css('display', 'inline');
      $('#apple-pay-error-message').text($('#apple-pay-termes-of-service-error-message').text());
      const intanceApplePayButton = createApplePayInstance(parameters);

      handleApplePayEvents(intanceApplePayButton);
    } else {
      $('#apple-pay-button').hide();

      $('#apple-pay-info-message')
        .show()
        .html(
          '{l s='This browser does not handle Apple Pay.' mod='hipay_enterprise'}'
          + '<br />'
          + '{l s='Please use another payment method.' mod='hipay_enterprise'}'
        );

      $('#apple-pay-error-message').hide();

      $("#payment-confirmation button[type=submit]").attr('disabled', 'true');
    }

    $('form#applepay-hipay').on('submit', function(e) {
      e.preventDefault();
      e.stopPropagation();

      var form = this;
      var isFormOk = true;

      $('#apple-pay-form input[required]').each(function (index, element) {
        isFormOk = $(element).val();
        // jQuery each breaks if return == false
        return isFormOk;
      });

      if (isFormOk) {
        $('#apple-pay-error-message').hide();
        form.submit();
        return true;
      } else {
        $('#apple-pay-error-message').css('display', 'inline');
        $('#apple-pay-error-message').text('{l s='Please select a card to complete the Apple Pay payment.' mod='hipay_enterprise'}');
        return false;
      }
    });
  }

  function handleSubmitButton() {
    $('input[name="payment-option"]').on('change', function() {
      // If the displayed payment method is apple pay, remove the payment button
      if ($('#pay-with-' + $(this).attr('id') + '-form form').attr('id') === 'applepay-hipay') {
        $('#payment-confirmation button').remove();
      } else if (!$('#payment-confirmation button').length) {
        $('#payment-confirmation .ps-shown-by-js').append(submitButton);
      }
    });
  }

  function handleTermsOfService() {
    $('input[id^=conditions_to_approve][required]:checkbox').on('change', function () {
      checkTermeOfService();
    });
  }

  function checkTermeOfService() {
    if ($('input[id^=conditions_to_approve][required]:checkbox:not(:checked)').length) {
      $('#apple-pay-button').hide();
      $('#apple-pay-error-message').css('display', 'inline');
      $('#apple-pay-error-message').text($('#apple-pay-termes-of-service-error-message').text());
      $("#payment-confirmation button[type=submit]").show();
    } else {
      $('#apple-pay-button').show();
      $('#apple-pay-error-message').hide();
      $("#payment-confirmation button[type=submit]").attr('disabled', 'true');
      $("#payment-confirmation button[type=submit]").hide();
    }
  }

  /**
   * Check if card is available for this merchantID or if browser handles Apple Pay
   * @returns boolean
   */
  function canMakeApplePayPayment() {    
    try {
      return window.ApplePaySession !== undefined && window.ApplePaySession.canMakePayments();
    } catch (e) {
      console.error('Error on ApplePaySession.canMakePayments', e);
      return false;
    }
  }

  /**
   * Create Apple Pay button instance
   * @returns paymentRequestButton
   */
  function createApplePayInstance(parameters) {

    const appleHipay = new HiPay({
      username: parameters.api_apple_pay_username,
      password: parameters.api_apple_pay_password,
      environment: parameters.environment,
      lang: parameters.language_iso_code
    });

    const total = {
      label: 'Total',
      amount: parameters.totalAmount
    }

    const request = {
      countryCode: 'FR',
      currencyCode: 'EUR',
      total: total,
      supportedNetworks: ['visa', 'masterCard']
    };

    const applePayStyle = {
      type: parameters.buttonType,
      color: parameters.buttonStyle
    };

    const options = {
      displayName: parameters.shopName,
      request: request,
      applePayStyle: applePayStyle,
      selector: 'apple-pay-button'
    };

    return appleHipay.create(
      'paymentRequestButton',
      options
    );
  }

  /**
   * Create Apple Pay event handlers
   * @param intanceApplePayButton
   */
  function handleApplePayEvents(intanceApplePayButton) {
    intanceApplePayButton.on('paymentAuthorized', function (hipayToken) {
      afterApplePayTokenization(hipayToken);
      intanceApplePayButton.completePaymentWithSuccess();

      handlePaymentApplePay();
    });

    intanceApplePayButton.on('cancel', function () {
      // The user has cancelled its payment
      intanceApplePayButton.completePaymentWithFailure();
    });

    intanceApplePayButton.on('paymentUnauthorized', function () {
      // The payment is not authorized (Token creation has failed, domain validation has failed...)
      intanceApplePayButton.completePaymentWithFailure();
    });
  }

  /**
   * Feeds infos retrieved from Apple Pay to hidden inputs to emulate credit card payment
   * @param hipayToken
   */
  function afterApplePayTokenization(hipayToken) {
    var token = hipayToken.token;
    var brand = hipayToken.brand.toLowerCase().replace(/ /g, '-') || 'cb';
    var pan = hipayToken.pan;
    var card_expiry_month = hipayToken.card_expiry_month;
    var card_expiry_year = hipayToken.card_expiry_year;
    var card_holder = hipayToken.card_holder;
    var issuer = hipayToken.issuer;
    var country = hipayToken.country;

    // set tokenization response
    $("#apple-pay-card-token").val(token);
    $("#apple-pay-card-brand").val(brand);
    $("#apple-pay-card-pan").val(pan);
    $("#apple-pay-card-holder").val(card_holder);
    $("#apple-pay-card-expiry-month").val(card_expiry_month);
    $("#apple-pay-card-expiry-year").val(card_expiry_year);
    $("#apple-pay-card-issuer").val(issuer);
    $("#apple-pay-card-country").val(country);
  }

  /**
   * Trigger payment form submission
   */
  function handlePaymentApplePay() {
    $("#payment-confirmation button[type=submit]").attr('disabled', 'true');

    if ($('input[id^=conditions_to_approve][required]:checkbox:not(:checked)').length) {

      $('#apple-pay-error-message').css('display', 'inline');
      $('#apple-pay-error-message').text($('#apple-pay-termes-of-service-error-message').text());

      return false;
    } else {
      $('#apple-pay-error-message').hide();
      $('form#applepay-hipay').submit();
      return true;
    }
  }
</script>