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
        <div id="paypal-field" style="height: 30px"></div>
        <p id="paypal-info-message" style="display: none;"></p>
        <span class="error-text-hp" id="paypal-error-message"></span>
        <p id="paypal-terms-of-service-error-message" style="display: none">
            {l s='Please accept the terms of service.' mod='hipay_enterprise'}
        </p>
    </div>
</div>

<script>

    var submitButton;
    document.addEventListener('DOMContentLoaded', function () {
        submitButton = $('#payment-confirmation button');
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
            merchantId: '{$HiPay_paypalFields.merchantId}',
            totalAmount: '{$HiPay_cart.totalAmount}',
            shopName: '{Configuration::get('PS_SHOP_NAME')}'
        };

        initPaypal(parameters);
    }, false);

    /**
     * Create Paypal Pay button
     */
    function initPaypal(parameters) {
        handleSubmitButton();

        if (canMakePaypalPayment()) {
            handleTermsOfService();

            $('#paypal-field').hide();
            $('#paypal-info-message').hide();

            $('#paypal-error-message').css('display', 'inline');
            $('#paypal-error-message').text($('#paypal-terms-of-service-error-message').text());
            const instancePaypalButton = createPaypalInstance(parameters);

            handlePaypalEvents(instancePaypalButton);
        } else {
            $('#paypal-field').hide();

            $('#paypal-info-message')
                .show()
                .html(
                    '{l s='This browser does not handle Apple Pay.' mod='hipay_enterprise'}'
                    + '<br />'
                    + '{l s='Please use another payment method.' mod='hipay_enterprise'}'
                );

            $('#paypal-error-message').hide();

            $("#payment-confirmation button[type=submit]").attr('disabled', 'true');
        }

        $('form#paypal-hipay').on('submit', function(e) {
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
                $('#paypal-error-message').hide();
                form.submit();
                return true;
            } else {
                $('#paypal-error-message').css('display', 'inline');
                $('#paypal-error-message').text('{l s='Please select a card to complete the Apple Pay payment.' mod='hipay_enterprise'}');
                return false;
            }
        });
    }

    function handleSubmitButton() {
        $('input[name="payment-option"]').on('change', function() {
            // If the displayed payment method is apple pay, remove the payment button
            if ($('#pay-with-' + $(this).attr('id') + '-form form').attr('id') === 'paypal-hipay') {
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
            $('#paypal-field').hide();
            $('#paypal-error-message').css('display', 'inline');
            $('#paypal-error-message').text($('#paypal-terms-of-service-error-message').text());
            $("#payment-confirmation button[type=submit]").show();
        } else {
            $('#paypal-field').show();
            $('#paypal-error-message').hide();
            $("#payment-confirmation button[type=submit]").attr('disabled', 'true');
            $("#payment-confirmation button[type=submit]").hide();
        }
    }

    /**
     * Check if card is available for this merchantID or if browser handles Paypal
     * @returns boolean
     */
    function canMakePaypalPayment() {
        try {
            return true
        } catch (e) {
            return false;
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
            locale: 'fr_FR',
            currency: 'EUR',
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
            canPayLater: true
        };

        return paypalInstance.create(
            'paypal',
            options
        );
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
            console.log(hipayToken);
            form.submit();
            return true;
        });
    }

</script>
<input type="hidden" name="localSubmit" />

<input class="ioBB" type="hidden" name="ioBB" />
<input id="{$HiPay_localPaymentName}-browserInfo" type="hidden" name="browserInfo" />
<input id="{$HiPay_localPaymentName}-orderId" type="hidden" name="orderId" />
<input id="{$HiPay_localPaymentName}-payment-product" type="hidden" name="method"/>
<input id="{$HiPay_localPaymentName}-payment-method" type="hidden" name="paymentmethod" />
<input id="{$HiPay_localPaymentName}-productlist" type="hidden" name="productlist" />
