jQuery(document).ready(function ($) {
  updatePaymentMethodSelected();
  initEventsHostedFields();
  if (typeof PaymentOPC !== typeof undefined) {
    initHostedFields();
    ajaxCompleteCheckoutPlaceOrder().then(() => {
      updatePaymentMethodSelected();
      $(document).on(
        'change',
        'input[name="payment-option"]',
        updatePaymentMethodSelected
      );
      $('#tokenizerForm').submit();
    });
  }
});

function initEventsHostedFields() {
  $('#tokenizerForm').submit(function (e) {
    var form = this;
    // prevent form from being submitted
    e.preventDefault();
    e.stopPropagation();

    if (myPaymentMethodSelected) {
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
        }
      );
    }
  });
}

var hipayHF;

document.addEventListener('DOMContentLoaded', initHostedFields, false);

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
      lang
    });

    isCardsDisplayedAreLimited =
      Number(number_saved_cards_displayed) > 0 &&
      number_saved_cards_displayed != null &&
      number_saved_cards_displayed != '';

    isCustomerHasCards =
      savedCards.length > 0 &&
      (paymentProductsActivated.length == 0 ||
        savedCards.some((card) =>
          paymentProductsActivated.includes(card.brand)
        ));

    var config = {
      selector: 'hipayHF-container',
      brand: activatedCreditCard,
      one_click: {
        enabled: isOneClickEnabled,
        ...(isCardsDisplayedAreLimited && {
          cards_display_count: Number(number_saved_cards_displayed)
        }),
        cards: isCustomerHasCards ? savedCards : []
      },
      fields: {
        savedCards: {
          selector: 'hipayHF-card-saved-cards'
        },
        cardHolder: {
          selector: 'hipayHF-card-holder',
          defaultFirstname: cardHolderFirstName,
          defaultLastname: cardHolderLastName
        },
        cardNumber: {
          selector: 'hipayHF-card-number'
        },
        expiryDate: {
          selector: 'hipayHF-date-expiry'
        },
        cvc: {
          selector: 'hipayHF-cvc',
          helpButton: true,
          helpSelector: 'hipayHF-help-cvc'
        },
        savedCardButton: {
          selector: 'hipayHF-saved-card-btn'
        }
      },
      styles: {
        base: style.base,
        components: style.components
      }
    };

    hipayHF = hipay.create('card', config);

    hipay.injectBaseStylesheet();

    if (isOneClickEnabled) {
      hipayHF.on('ready', function () {
        var cardForm = document.getElementById('hipayHF-card-form-container');
        if (isCustomerHasCards) {
          document
            .getElementById('pay-other-card')
            .addEventListener('click', (e) => {
              cardForm.classList.toggle('hidden');
            });
        } else {
          document.getElementById('pay-other-card');
          cardForm.classList.toggle('hidden');
        }

        let savedCardsElements = document.getElementsByClassName('saved-card');
        for (let i = 0; i < savedCardsElements.length; i++) {
          savedCardsElements[i].onclick = function () {
            cardForm.classList.add('hidden');
          };
        }
      });
    } else {
      var cardForm = document.getElementById('hipayHF-card-form-container');
      cardForm.classList.toggle('hidden');
    }

    hipayHF.on('blur', function (data) {
      // Get error container
      var domElement = document.querySelector(
        "[data-hipay-id='hipay-card-field-error-" + data.element + "']"
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
        "[data-hipay-id='hipay-card-field-error-" + data.element + "']"
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
        name: 'ioBB'
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
      "[data-hipay-id='hipay-card-field-error-" + errors[error].field + "']"
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

function getCheckoutPaymentContainer() {
  var container = $('#onepagecheckoutps_step_three_container');
  if (!container.length) {
    container = $('#payment_method_container');
  }
  return container;
}

function updatePaymentMethodSelected() {
  var container = getCheckoutPaymentContainer();
  myPaymentMethodSelected = container
    .find("input[data-module-name='credit_card']")
    .is(':checked');
}
