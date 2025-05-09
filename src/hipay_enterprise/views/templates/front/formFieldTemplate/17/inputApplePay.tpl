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
  // For Classic Checkout Page
  document.addEventListener(
    'DOMContentLoaded',
    function() {
      if (!OPC_enabled) {
        handleTermsOfService();
        initApplePayInstance();
      }
    },
    false,
  );

  //For OPC Checkout 4
  if (typeof OPC !== 'undefined') {
    new Promise((resolve) => {
      prestashop.on('opc-payment-getPaymentList-complete', resolve);
    }).then(() => {
      handleTermsOfService();
      jQuery(document).ready(function($) {
        initApplePayInstance();
        handleSubmitButton(false);
      });
    })
  }

  // For OPC checkout 5.0
  if (OPC_enabled) {
    // Use jQuery's ready method because DOMContentLoaded doesn't work well with OPC
    jQuery(document).ready(function($) {
      eventTarget.addEventListener('opc_update_card', handleApplePayAndReview);
    });
  }

  /**
   * One Page Checkout - Handles the Applepay payment process and review, including payment option change and terms of service checkbox events.
   *
   * @param event
   */
  function handleApplePayAndReview(event) {
    handlePaymentAndReview(event, initApplePayInstance, handleTermsOfService);
  }

  /**
   * Initializes the ApplePay instance with appropriate credentials and configuration based on sandbox or production mode.
   */
  function initApplePayInstance() {

    const parameters = {
      api_apple_pay_username: '{$HiPay_credentials.api_apple_pay_username}',
      api_apple_pay_password: '{$HiPay_credentials.api_apple_pay_password}',
      environment: '{$HiPay_environment}',
      language_iso_code: '{$HiPay_language_iso_code}',
      buttonType: '{$HiPay_appleFields.buttonType[0]}',
      buttonStyle: '{$HiPay_appleFields.buttonStyle[0]}',
      totalAmount: '{$HiPay_cart.totalAmount}',
      currencyCode: '{$HiPay_cart.currencyCode}',
      countryCode: '{$HiPay_cart.countryCode}',
      shopName: '{Configuration::get('PS_SHOP_NAME')}'
    };

    initApplePay(parameters);
  }

  /**
   * Create Apple Pay button
   */
  function initApplePay(parameters) {
    if (canMakeApplePayPayment()) {
      handleTermsOfService();
      if (checkbox && !checkbox.checked) {
        $('#apple-pay-button').hide();
        $('#apple-pay-info-message').hide();

        $('#apple-pay-error-message').css('display', 'inline');
        $('#apple-pay-error-message').text($('#apple-pay-termes-of-service-error-message').text());
      }

      destroyMethods(methodsInstance)
        .then(() => {
          createApplePayInstance(parameters);
          handleApplePayEvents(methodsInstance['applepay']);
        })
        .catch((error) => {
          console.error("Failed to destroy methods:", error);
        });
    } else {
      $('#apple-pay-button').hide();

      $('#apple-pay-info-message')
        .show()
        .html(
          '{l s='This browser does not handle Apple Pay.' mod='hipay_enterprise'}'
          +'<br />'
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

      $('#apple-pay-form input[required]').each(function(index, element) {
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

  /**
   * Handles the terms of service checkbox change event by calling paypalCheckTermeOfService.
   */
  function handleTermsOfService() {
    if (OPC_enabled) {
      $('input[id^=conditions_to_approve][required]:checkbox').prop('checked', false);
    }
    $('input[id^=conditions_to_approve][required]:checkbox').on('change', function() {
      checkTermeOfService();
    });
  }

  /**
   * Shows/hides the Applepay button and error message based on the state of the terms of service checkbox.
   */
  function checkTermeOfService() {
    const applePayButton = $('#apple-pay-button');
    const applePayErrorMessage = $('#apple-pay-error-message');
    const submitButton = $('#payment-confirmation button[type=submit]');
    const applePayContainer= $('form[id=applepay-hipay]').parent();


    if(applePayContainer.is(':visible')){
      if (checkbox && !checkbox.checked) {
        applePayButton.hide();
        applePayErrorMessage
          .css('display', 'inline')
          .text($('#apple-pay-termes-of-service-error-message').text());
        submitButton.show();
      } else {
        applePayButton.show();
        applePayErrorMessage.hide();
        submitButton.attr('disabled', 'true').hide();
      }
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
      countryCode: parameters.countryCode,
      currencyCode: parameters.currencyCode,
      total: total
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

    return methodsInstance['applepay'] = appleHipay.create(
      'paymentRequestButton',
      options
    );
  }

  /**
   * Create Apple Pay event handlers
   * @param intanceApplePayButton
   */
  function handleApplePayEvents(intanceApplePayButton) {
    intanceApplePayButton.on('paymentAuthorized', function(hipayToken) {
      if (_validateOPC() === false) {
        intanceApplePayButton.completePaymentWithFailure();
        return false;
      } else {
        afterApplePayTokenization(hipayToken);
        intanceApplePayButton.completePaymentWithSuccess();

        handlePaymentApplePay();
      }
    });

    intanceApplePayButton.on('cancel', function() {
      // The user has cancelled its payment
      intanceApplePayButton.completePaymentWithFailure();
    });

    intanceApplePayButton.on('paymentUnauthorized', function() {
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
    $('#apple-pay-card-token').val(token);
    $('#apple-pay-card-brand').val(brand);
    $('#apple-pay-card-pan').val(pan);
    $('#apple-pay-card-holder').val(card_holder);
    $('#apple-pay-card-expiry-month').val(card_expiry_month);
    $('#apple-pay-card-expiry-year').val(card_expiry_year);
    $('#apple-pay-card-issuer').val(issuer);
    $('#apple-pay-card-country').val(country);
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
      if (OPC_enabled) {
        Review.placeOrder();
      } else {
        $('form#applepay-hipay').submit();
      }
      return true;
    }
  }
</script>