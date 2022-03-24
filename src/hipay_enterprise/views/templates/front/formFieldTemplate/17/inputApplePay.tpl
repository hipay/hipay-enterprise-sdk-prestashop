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
    <div class="col-md-9">
        <div id="apple-pay-button"></div>
    </div>
</div>
<script>
  var hipay = HiPay({
    username: '{$credentials.api_username}',
    password: '{$credentials.api_password}',
    environment: '{$environment}',
    lang: '{$language_iso_code}'
  });

  const request = {
    countryCode: 'FR',
    currencyCode: 'EUR',
    total: {$cart.totalAmount},
    supportedNetworks: ['visa', 'masterCard']
  };

  const applePayStyle = {
    type: 'plain',
    color: 'black'
  };

  const options = {
    displayName: '{Configuration::get('PS_SHOP_NAME')}',
    request: request,
    applePayStyle: applePayStyle,
    selector: 'apple-pay-button'
  };

  var intanceApplePayButton = hipay.create(
    'paymentRequestButton',
    options
  );

  if (intanceApplePayButton) {
    intanceApplePayButton.on('paymentAuthorized', function (hipayToken) {
      handlePayment(hipayToken)
        .then(function (response) {
          // Order processed
          // Handle response
          intanceApplePayButton.completePaymentWithSuccess();
        })
        .catch(function (error) {
          // Handle error
          intanceApplePayButton.completePaymentWithFailure();
        });
    });

    intanceApplePayButton.on('cancel', function() {
      // The user has cancelled its payment
    }

    intanceApplePayButton.on('paymentUnauthorized', function(error) {
      // The payment is not authorized (Token creation has failed, domain validation has failed...)
    });
  }
</script>