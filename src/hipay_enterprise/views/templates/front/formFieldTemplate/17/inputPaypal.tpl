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
    <div class="col-md-9" id="paypal-form">
        <div id="paypal-field" style="height: 30px;"></div>
        <p id="paypal-info-message" style="display: none;"></p>
        <span class="error-text-hp" id="paypal-error-message"></span>
        <p id="paypal-terms-of-service-error-message" style="display: none">
            {l s='Please accept the terms of service.' mod='hipay_enterprise'}
        </p>
    </div>
</div>
<br>
<script>

    // For Classic Checkout Page when OPC is not enabled
    document.addEventListener('DOMContentLoaded', function () {
        if (!OPC_enabled) {
            paypalHandleTermsOfService();
            initPaypalInstance();
        }
    }, false);

    //For OPC Checkout 4
    if (typeof OPC !== 'undefined') {
      new Promise((resolve) => {
        prestashop.on('opc-payment-getPaymentList-complete', resolve);
      }).then(() => {
        paypalHandleTermsOfService();
        jQuery(document).ready(function ($) {
          initPaypalInstance();
          handleSubmitButton(false);
        });
      });
    }

    //For OPC Checkout 5
    if (OPC_enabled) {
        jQuery(document).ready(function ($) {
                eventTarget.addEventListener('opc_update_card', handlePaypalAndReview);
        });
    }

    /**
     * One Page Checkout Module - Handles the Paypal payment process and review, including payment option change and terms of service checkbox events.
     *
     * @param event
     */
    function handlePaypalAndReview(event) {
      handlePaymentAndReview(event, initPaypalInstance, paypalHandleTermsOfService);
    }

    /**
     * Initializes the Paypal instance with appropriate credentials and configuration based on sandbox or production mode.     *
     */
    function initPaypalInstance() {

        {if $HiPay_confHipay.account.global.sandbox_mode}
        var api_tokenjs_mode = 'stage';
        var api_tokenjs_username = "{$HiPay_confHipay.account.sandbox.api_tokenjs_username_sandbox}";
        var api_tokenjs_password_publickey = "{$HiPay_confHipay.account.sandbox.api_tokenjs_password_publickey_sandbox}";
        {else}
        var api_tokenjs_mode = 'production';
        var api_tokenjs_username = "{$HiPay_confHipay.account.production.api_tokenjs_username_production}";
        var api_tokenjs_password_publickey = "{$HiPay_confHipay.account.production.api_tokenjs_password_publickey_production}";
        {/if}
        const parameters = {
            username: api_tokenjs_username,
            password: api_tokenjs_password_publickey,
            environment: api_tokenjs_mode,
            language_iso_code: '{$HiPay_language_iso_code}',
            buttonShape: '{$HiPay_paypalFields.buttonShape[0]}',
            buttonLabel: '{$HiPay_paypalFields.buttonLabel[0]}',
            buttonColor: '{$HiPay_paypalFields.buttonColor[0]}',
            buttonHeight: '{$HiPay_paypalFields.buttonHeight}',
            bnpl:{$HiPay_paypalFields.bnpl},
            merchantId: '{$HiPay_paypalFields.merchantId}',
            totalAmount: '{$HiPay_cart.totalAmount}',
            shopName: '{Configuration::get('PS_SHOP_NAME')}'
        };

        initPaypal(parameters);
    }

    /**
     * Creates the Paypal Pay button, handles terms of service checkbox, destroys existing Paypal methods, and handles Paypal events.
     */
    function initPaypal(parameters) {
      paypalHandleTermsOfService();

      if (checkbox && !checkbox.checked) {
        $('#paypal-field').hide();
        $('#paypal-info-message').hide();

        $('#paypal-error-message').css('display', 'inline');
        $('#paypal-error-message').text(
          $('#paypal-terms-of-service-error-message').text(),
        );
      }

      destroyMethods(methodsInstance)
        .then(() => {
          createPaypalInstance(parameters);
          handlePaypalEvents(methodsInstance['paypal']);
        })
        .catch((error) => {
          console.error('Failed to destroy methods:', error);
        });
    }

    /**
     * Handles the terms of service checkbox change event by calling paypalCheckTermeOfService.
     */
    function paypalHandleTermsOfService() {
        if (OPC_enabled) {
            $('input[id^=conditions_to_approve][required]:checkbox').prop('checked', false);
        }
        $('input[id^=conditions_to_approve][required]:checkbox').on('change', function () {
            paypalCheckTermeOfService();
        });
    }

    function paypalCheckTermeOfService() {
        const paypalField = $('#paypal-field');
        const paypalErrorMessage = $('#paypal-error-message');
        const submitButton = $("#payment-confirmation button[type=submit]");

        if (checkbox && !checkbox.checked) {
            paypalField.hide();
            paypalErrorMessage.css('display', 'inline').text($('#paypal-terms-of-service-error-message').text());
            submitButton.show();
        } else {
            paypalField.show();
            paypalErrorMessage.hide();
            submitButton.attr('disabled', 'true').hide();
        }
    }

    /**
     * Create Paypal button instance
     * @returns paymentRequestButton
     */
    function createPaypalInstance(parameters) {

        const paypalInstance = new HiPay({
            username: parameters.username,
            password: parameters.password,
            environment: parameters.environment,
            lang: parameters.language_iso_code
        });

        const request = {
            locale: prestashop.language.locale.replace(/-/g, '_'),
            currency: prestashop.currency.iso_code,
            amount: Number(parameters.totalAmount)
        };

        const paypalButtonStyle = {
            shape: parameters.buttonShape,
            height: Number(parameters.buttonHeight),
            color: parameters.buttonColor,
            label: parameters.buttonLabel,
        };

        const options = {
            template: 'auto',
            request: request,
            paypalButtonStyle: paypalButtonStyle,
            selector: 'paypal-field',
            merchantPaypalId: parameters.merchantId,
            canPayLater: Boolean(parameters.bnpl)
        };

        return methodsInstance['paypal'] = paypalInstance.create(
            'paypal',
            options
        );
    }

    /**
     * Create Paypal event handlers
     * @param instancePaypalButton
     */
    function handlePaypalEvents(instancePaypalButton) {
      var form = $('#{$HiPay_localPaymentName}-hipay');
      instancePaypalButton.on('paymentAuthorized', function (hipayToken) {
        if (_validateOPC() === false) {
          return false;
        }
        $('#{$HiPay_localPaymentName}-orderId').val(hipayToken.orderID);
        $('#{$HiPay_localPaymentName}-payment-product').val('paypal');
        $('#{$HiPay_localPaymentName}-browserInfo').val(
          JSON.stringify(hipayToken.browser_info),
        );
        $('#{$HiPay_localPaymentName}-paymentmethod').val('paypal');
        $('#{$HiPay_localPaymentName}-productlist').val('paypal');
        form.submit();
        return true;
      });
    }
</script>
<input type="hidden" name="localSubmit" />
<input class="ioBB" type="hidden" name="ioBB" />
<input id="{$HiPay_localPaymentName}-browserInfo" type="hidden" name="browserInfo" />
<input id="{$HiPay_localPaymentName}-orderId" type="hidden" name="paypalOrderId" />
<input id="{$HiPay_localPaymentName}-payment-product" type="hidden" name="method"/>
<input id="{$HiPay_localPaymentName}-payment-method" type="hidden" name="paymentmethod" />
<input id="{$HiPay_localPaymentName}-productlist" type="hidden" name="productlist" />
