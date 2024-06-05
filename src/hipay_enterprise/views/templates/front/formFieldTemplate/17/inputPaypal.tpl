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
    var methodsInstance = {};
    var checkoutSubmitButton;
    var checkbox;

    /**
     * If One page Checkout module is actived,
     *
     * @type boolean
     */
    var OPC_enabled = typeof PaymentOPC !== 'undefined';

    //Classic Checkout Page
    document.addEventListener('DOMContentLoaded', function () {
        checkbox = document.querySelector('input[id^="conditions_to_approve"]');
        checkoutSubmitButton = (!OPC_enabled) ? $('#payment-confirmation button') : $('#btn_place_order');
        if (!OPC_enabled) {
            paypalHandleSubmitButton();
            initPaypalInstance();
        }

    }, false);

    //One Page Checkout Is activated
    if (OPC_enabled) {
        $(document).ready(function () {
            eventTarget.addEventListener('opc_update_card', handlePaypalAndReview);
        });
    }

    /**
     * One Page Checkout Module - Handles the Paypal payment process and review, including payment option change and terms of service checkbox events.
     *
     * @param event
     */
    function handlePaypalAndReview(event) {
        paypalHandleSubmitButton();
        paypalHandleTermsOfService();

        //After page checkout is totally loaded and updated
        ajaxCompleteCheckoutReview().then(({ event, xhr, settings }) => {
            checkbox = document.querySelector('input[id^="conditions_to_approve"]');
            checkoutSubmitButton = (!OPC_enabled) ? $('#payment-confirmation button') : $('#btn_place_order');
            initPaypalInstance();
            paypalHandlePaymentOptionChange(false);

        });
    }

    /**
     * Initializes the Paypal instance with appropriate credentials and configuration based on sandbox or production mode.     *
     */
    function initPaypalInstance() {

        {if $HiPay_confHipay.account.global.sandbox_mode}
        var api_tokenjs_mode = "stage";
        var api_tokenjs_username = "{$HiPay_confHipay.account.sandbox.api_tokenjs_username_sandbox}";
        var api_tokenjs_password_publickey = "{$HiPay_confHipay.account.sandbox.api_tokenjs_password_publickey_sandbox}";
        {else}
        var api_tokenjs_mode = "production";
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
            $('#paypal-error-message').text($('#paypal-terms-of-service-error-message').text());
        }

        paypalDestroyMethods(methodsInstance);
        createPaypalInstance(parameters);
        handlePaypalEvents(methodsInstance['paypal']);

    }

    /**
     * Handles the payment option change event by calling paypalHandlePaymentOptionChange.
     */
    function paypalHandleSubmitButton() {
        $('input[name="payment-option"]').on('change', function() {
            // If the displayed payment method is Paypal, remove the payment button
            paypalHandlePaymentOptionChange();
        });
    }

    /**
     * Shows/hides the Paypal button based on the selected payment option (Paypal, Apple Pay, or others).
     *
     * @param initPaypal (reinit Paypal button instance)
     */
    function paypalHandlePaymentOptionChange(initPaypal = true) {
        let paymentOptionId = $('input[name="payment-option"]:checked').attr('id');
        let paymentFormId = $('#pay-with-' + paymentOptionId + '-form form').attr('id');

        if (paymentFormId === 'applepay-hipay' || paymentFormId === 'paypal-hipay') {
            placeOrderButton = false;
            // If the displayed payment method is PayPal or Apple Pay, remove the payment button
            checkoutSubmitButton.remove();
            if (OPC_enabled) {
                $('#btn_place_order').remove();
            }
            if (initPaypal) {
                initPaypalInstance();
            }
        } else {
            placeOrderButton = true;
            paypalDestroyMethods(methodsInstance);
        }

        if (!$('#payment-confirmation button').length || !$('#btn_place_order').length) {
            if (placeOrderButton) {
                $('#payment-confirmation .ps-shown-by-js').append(checkoutSubmitButton);
                if (OPC_enabled) {
                    $('#buttons_footer_review div').append(checkoutSubmitButton);
                }
            }
            paypalDestroyMethods(methodsInstance);
        }
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
     * Destroy already created instance for paypal
     *
     * @param methodsInstance
     */
    function paypalDestroyMethods(methodsInstance) {
        // Iterate over the object values to call the destroy method
        Object.values(methodsInstance).forEach((method) => {
            if (method && typeof method.destroy === 'function') {
                method.destroy();
            }
        });
        // Reassign methodsInstance to a new empty object to clear all properties
        methodsInstance = {};
    }

    /**
     * Create Paypal event handlers
     * @param instancePaypalButton
     */
    function handlePaypalEvents(instancePaypalButton) {
        var form = $("#{$HiPay_localPaymentName}-hipay");
        instancePaypalButton.on('paymentAuthorized', function (hipayToken) {
            $("#{$HiPay_localPaymentName}-orderId").val(hipayToken.orderID);
            $("#{$HiPay_localPaymentName}-payment-product").val('paypal');
            $("#{$HiPay_localPaymentName}-browserInfo").val(JSON.stringify(hipayToken.browser_info));
            $("#{$HiPay_localPaymentName}-paymentmethod").val('paypal');
            $("#{$HiPay_localPaymentName}-productlist").val('paypal');
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
