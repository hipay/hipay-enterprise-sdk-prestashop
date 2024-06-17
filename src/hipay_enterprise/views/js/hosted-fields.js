jQuery(document).ready(function ($) {
  initEventsHostedFields();
  if (typeof PaymentOPC !== typeof undefined) {
    initHostedFields();
    ajaxCompleteCheckoutPlaceOrder().then(({ event, xhr, settings }) => {
      setMyPaymentMethodSelected();
      $('#tokenizerForm').submit();
    });
  }
});
function initEventsHostedFields() {
  $('#card-number').focus(function () {
    $('#radio-no-token').prop('checked', true);
  });

  $('#radio-no-token').change(function () {
    $('#credit-card-group').collapse('show');
  });

  $('.radio-with-token').change(function () {
    $('#credit-card-group').collapse('hide');
  });

  $('#saveTokenHipay').change(function () {
    hipayHF.setMultiUse(allowMultiUse(this));
  });

  $('#tokenizerForm').submit(function (e) {
    var form = this;
    // prevent form from being submitted
    e.preventDefault();
    e.stopPropagation();

    if (myPaymentMethodSelected) {
      if (isOneClickSelected()) {
        oneClickSelected(form);
        return true; // allow whatever action that would normally happen to continue
      }

      hipayHF.getPaymentData().then(
        function (response) {
          if (isCardTypeOk(response)) {
            displayLoadingDiv();
            afterTokenization(response);
            //submit the form
            form.submit();
            return true;
          } else {
            $('#error-js').show();
            $('#error-js').text(activatedCreditCardError);
            return false;
          }
        },
        function (errors) {
          handleErrorhipayHF(errors);
        },
      );
    }
  });
}

var hipayHF;

//Support module One Page Checkout PS - PresTeamShop - v4.1.1 - PrestaShop >= 1.7.6.X
//--------------------------------
if (window.opc_dispatcher && window.opc_dispatcher.events) {
  window.opc_dispatcher.events.addEventListener(
    'payment-getPaymentList-complete',
    () => {
      initEventsHostedFields();
      initHostedFields();
    },
  );
} else {
  document.addEventListener('DOMContentLoaded', initHostedFields, false);
}
//--------------------------------

function allowMultiUse(saveTokenEl) {
  return oneClick && $(saveTokenEl).is(':checked');
}

function initHostedFields() {
  if (
    typeof api_tokenjs_username !== 'undefined' &&
    typeof api_tokenjs_password_publickey !== 'undefined' &&
    typeof api_tokenjs_mode !== 'undefined'
  ) {
    var hipay = new HiPay({
      username: api_tokenjs_username,
      password: api_tokenjs_password_publickey,
      environment: api_tokenjs_mode,
      lang,
    });

    var config = {
      selector: 'hipayHF-container',
      multi_use: allowMultiUse('#saveTokenHipay'),
      fields: {
        cardHolder: {
          selector: 'hipayHF-card-holder',
          defaultFirstname: cardHolderFirstName,
          defaultLastname: cardHolderLastName,
        },
        cardNumber: {
          selector: 'hipayHF-card-number',
        },
        expiryDate: {
          selector: 'hipayHF-date-expiry',
        },
        cvc: {
          selector: 'hipayHF-cvc',
          helpButton: true,
          helpSelector: 'hipayHF-help-cvc',
        },
      },
      styles: {
        base: style.base,
      },
    };

    hipayHF = hipay.create('card', config);

    hipay.injectBaseStylesheet();

    hipayHF.on('blur', function (data) {
      // Get error container
      var domElement = document.querySelector(
        "[data-hipay-id='hipay-card-field-error-" + data.element + "']",
      );

      // Finish function if no error DOM element
      if (!domElement) {
        return;
      }

      // If not valid & not empty add error
      if (!data.validity.valid && !data.validity.empty) {
        domElement.innerText = data.validity.error;
      } else {
        domElement.innerText = '';
      }
    });

    hipayHF.on('inputChange', function (data) {
      // Get error container
      var domElement = document.querySelector(
        "[data-hipay-id='hipay-card-field-error-" + data.element + "']",
      );

      // Finish function if no error DOM element
      if (!domElement) {
        return;
      }

      // If not valid & not potentiallyValid add error (input is focused)
      if (!data.validity.valid && !data.validity.potentiallyValid) {
        domElement.innerText = data.validity.error;
      } else {
        domElement.innerText = '';
      }
    });

    let deviceFingerprintInput = $('#realFingerprint');
    if (deviceFingerprintInput.length === 0) {
      deviceFingerprintInput = $('<input/>', {
        id: 'realFingerprint',
        type: 'hidden',
        name: 'ioBB',
      });
      $('#ioBB').attr('name', 'ioBB_old');
      $('#ioBB').parent().append(deviceFingerprintInput);
    }
    deviceFingerprintInput.val(hipay.getDeviceFingerprint());
    $('.ioBB').val(deviceFingerprintInput.val());
    if (hipay.getDeviceFingerprint() === undefined) {
      let retryCounter = 0;
      let interval = setInterval(function timeoutFunc() {
        retryCounter++;
        // If global_info init send event
        if (hipay.getDeviceFingerprint() !== undefined) {
          deviceFingerprintInput.val(hipay.getDeviceFingerprint());
          $('.ioBB').val(deviceFingerprintInput.val());
          clearInterval(interval);
        }
        // Max retry = 3
        if (retryCounter > 3) {
          clearInterval(interval);
        }
      }, 1000);
    }
    $('#browserInfo').val(JSON.stringify(hipay.getBrowserInfo()));
  }
}

function handleErrorhipayHF(errors) {
  for (var error in errors) {
    var domElement = document.querySelector(
      "[data-hipay-id='hipay-card-field-error-" + errors[error].field + "']",
    );

    // If DOM element add error inside
    if (domElement) {
      domElement.innerText = errors[error].error;
    }
  }
}

function ajaxCompleteCheckoutPlaceOrder() {
  return new Promise((resolve, reject) => {
    $(document).ajaxComplete((event, xhr, settings) => {
      if (
        settings.url.includes(prestashop.urls.pages.order) &&
        typeof settings.data === 'string' && // Check if data is a string
        settings.data.includes('placeOrder')
      ) {
        resolve({ event, xhr, settings });
      }
    });
  });
}

function setMyPaymentMethodSelected() {
  // Your existing code here
  var primarySelector = '#onepagecheckoutps_step_three_container';
  var fallbackSelector = '#payment_method_container';
  var container = $(primarySelector).length
    ? $(primarySelector)
    : $(fallbackSelector);

  myPaymentMethodSelected = container
    .find("input[data-module-name='credit_card']")
    .is(':checked');

  $(document).on('change', 'input[name="payment-option"]', function () {
    container = $(primarySelector).length
      ? $(primarySelector)
      : $(fallbackSelector);
    myPaymentMethodSelected = container
      .find("input[data-module-name='credit_card']")
      .is(':checked');
  });
}
