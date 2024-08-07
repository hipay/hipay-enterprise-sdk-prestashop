{literal}
    <script>
      var placeOrderButton = true;
      var methodsInstance = {};
      var checkbox;

      /**
       * If One page Checkout module is actived,
       *
       * @type boolean
       */
      var OPC_enabled = typeof PaymentOPC !== 'undefined';

      document.addEventListener(
        'DOMContentLoaded',
        function () {
          checkbox = document.querySelector('input[id^="conditions_to_approve"]');
          submitButton = !OPC_enabled
            ? $('#payment-confirmation button')
            : $('#btn_place_order');
          handleSubmitButton(false);
          if (
            getSelectedLocalPaymentMethod() === 'paypal-v2-hipay' ||
            getSelectedLocalPaymentMethod() === 'applepay-hipay'
          ) {
            handlePaymentOptionChange(false);
          }
        },
        false,
      );

      //For OPC Checkout 4
      if (typeof OPC !== 'undefined') {
        new Promise((resolve) => {
          prestashop.on('opc-payment-getPaymentList-complete', resolve);
        }).then(() => {
          jQuery(document).ready(function ($) {
            if (
                    getSelectedLocalPaymentMethod() === 'paypal-v2-hipay' ||
                    getSelectedLocalPaymentMethod() === 'applepay-hipay'
            ) {
              handlePaymentOptionChange(false);
            }
            checkbox = document.querySelector('input[id^="conditions_to_approve"]');
            submitButton = !OPC_enabled
                    ? $('#payment-confirmation button')
                    : $('#btn_place_order');
            $(document).on('click', '.payment-option', function () {
              const $radioButton = $(this).find('input[name="payment-option"]');
              if ($radioButton.length) {
                $radioButton.prop('checked', true);
                $radioButton.trigger('change');
              }
            });
          });
        });
      }

      function initButtonInstance(paymentFormId) {
        if (paymentFormId === 'applepay-hipay') {
          initApplePayInstance();
        } else if (paymentFormId === 'paypal-v2-hipay') {
          initPaypalInstance();
        }
      }

      function handlePaymentAndReview(
              event,
              initPaymentInstance,
              handleTermsOfService,
      ) {
        // After page checkout is totally loaded and updated
        ajaxCompleteCheckoutReview().then(({ event, xhr, settings }) => {
          checkbox = document.querySelector('input[id^="conditions_to_approve"]');
          submitButton = !OPC_enabled
                  ? $('#payment-confirmation button')
                  : $('#btn_place_order');
          handleSubmitButton(false);
          handleTermsOfService();
          initPaymentInstance();
          handlePaymentOptionChange(false);
        });
      }

      /**
         * Handles the payment option change event by calling paypalHandlePaymentOptionChange.
         */
        function handleSubmitButton(reinitButtonInstance = true) {
          $('input[name="payment-option"]').off('change');
          $(document).on('change', 'input[name="payment-option"]', function () {
            // Your code here
            handlePaymentOptionChange(reinitButtonInstance);
          });
        }

        /**
         * Shows/hides the ApplePay button based on the selected payment option (Paypal, Apple Pay, or others).
         *
         * @param reinitButtonInstance (reinit ApplePay button instance)
         */
        function handlePaymentOptionChange(reinitButtonInstance = true) {
          let paymentOptionId = $('input[name="payment-option"]:checked').attr('id');
          let paymentFormId = $('#pay-with-' + paymentOptionId + '-form form').attr(
            'id',
          );
          destroyMethods(methodsInstance)
            .then(() => {
              if (
                paymentFormId === 'applepay-hipay' ||
                      (paymentFormId === 'paypal-v2-hipay')
              ) {
                placeOrderButton = false;
                // If the displayed payment method is Apple Pay or PayPal, remove the payment button
                submitButton.remove();
                if (OPC_enabled) {
                  $('#btn_place_order').remove();
                }
                if (reinitButtonInstance) {
                  initButtonInstance(paymentFormId);
                }
              } else {
                placeOrderButton = true;
              }
              if (
                !$('#payment-confirmation button').length ||
                !$('#btn_place_order').length
              ) {
                if (placeOrderButton) {
                  $('#payment-confirmation .ps-shown-by-js').append(submitButton);
                  if (OPC_enabled) {
                    $('#buttons_footer_review div').append(submitButton);
                  }
                }
              }
            })
            .catch((error) => {
              console.error('Failed to destroy methods:', error);
            });
        }

        /**
         * Destroy already created instance for applePay
         *
         * @param methodsInstance
         */
        function destroyMethods(methodsInstance) {
          return new Promise((resolve, reject) => {
            try {
              const destroyPromises = Object.values(methodsInstance).map((method) => {
                if (
                  method &&
                  typeof method.destroy === 'function' &&
                  (method === 'paypal' || method === 'applepay')
                ) {
                  return method.destroy();
                }
                return Promise.resolve();
              });

              Promise.all(destroyPromises)
                .then(() => {
                  methodsInstance = {};
                  resolve();
                })
                .catch((error) => {
                  reject(error);
                });
            } catch (error) {
              reject(error);
            }
          });
        }

        /**
         * Get current selected local payment when radio button is on page Ready by default
         *
         * @returns string
         */
        function getSelectedLocalPaymentMethod() {
          let paymentOptionId = $('input[name="payment-option"]:checked').attr('id');
          return $('#pay-with-' + paymentOptionId + '-form form').attr('id');
        }

        /**
         * One Page Checkout Module
         *
         * @returns {Promise<unknown>}
         */
        function ajaxCompleteCheckoutReview() {
          return new Promise((resolve, reject) => {
            $(document).ajaxComplete((event, xhr, settings) => {
              if (
                settings.url.includes(prestashop.urls.pages.order) &&
                typeof settings.data === 'string' && // Check if data is a string
                settings.data.includes('loadReview')
              ) {
                resolve({ event, xhr, settings });
              }
            });
          });
        }

        function _validateOPC() {
          if (OPC_enabled) {
            const validFormAddress = window.OnePageCheckoutPS.IS_LOGGED
              ? {
                valid_form_address_delivery: false,
                valid_form_address_invoice: false,
              }
              : { valid_form_address_delivery: true, valid_form_address_invoice: true };
            window.Fronted.validateOPC({
              valid_form_customer: true,
              valid_privacy: false,
              valid_gdpr: false,
              ...validFormAddress,
            });
            if (!window.AppOPC.is_valid_opc) return false;
          }
          return true;
        }

    </script>
{/literal}