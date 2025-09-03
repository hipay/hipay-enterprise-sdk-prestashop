{**
 * Oney Payment Method Template (PS16)
 * Handles 3xcb, 3xcb-no-fees, 4xcb, 4xcb-no-fees
 *}

<div id="hipay-container-hosted-fields-{$HiPay_localPaymentName}"></div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Initialize Oney widget for {$HiPay_localPaymentName}
        var container = "hipay-container-hosted-fields-{$HiPay_localPaymentName}";
        var amount = "{$HiPay_cart.totalAmount}";

        {if $HiPay_confHipay.account.global.sandbox_mode}
            var api_tokenjs_mode = "stage";
            var api_tokenjs_username = "{$HiPay_confHipay.account.sandbox.api_tokenjs_username_sandbox}";
            var api_tokenjs_password_publickey = "{$HiPay_confHipay.account.sandbox.api_tokenjs_password_publickey_sandbox}";
        {else}
            var api_tokenjs_mode = "production";
            var api_tokenjs_username = "{$HiPay_confHipay.account.production.api_tokenjs_username_production}";
            var api_tokenjs_password_publickey = "{$HiPay_confHipay.account.production.api_tokenjs_password_publickey_production}";
        {/if}

        var oneyInstance = new HiPay({
            username: api_tokenjs_username,
            password: api_tokenjs_password_publickey,
            environment: api_tokenjs_mode,
            lang: "{$HiPay_languageIsoCode}"
        });

        var config = {
            template: 'auto',
            selector: container,
            request: {
                amount: amount.toString()
            }
        };

        // Create Oney widget
        try {
            console.log('Creating Oney widget for {$HiPay_localPaymentName} with config:', config);
            var oneyWidget = oneyInstance.create("{$HiPay_localPaymentName}", config);

            if (oneyWidget && typeof oneyWidget.on === 'function') {
                oneyWidget.on('error', function(error) {
                    console.warn('Oney widget error for {$HiPay_localPaymentName}:', error);
                });
            } else {
                console.warn('Oney widget creation failed for {$HiPay_localPaymentName}');
            }
        } catch (error) {
            console.warn('Error creating Oney widget for {$HiPay_localPaymentName}:', error);
        }
    });
</script>

<!-- Add the missing form structure for Oney methods -->
<input type="hidden" name="localSubmit" />
<input class="ioBB" type="hidden" name="ioBB" />
<input id="{$HiPay_localPaymentName}-paymentProductCode" type="hidden" name="HF-paymentProductCode" />
<input id="{$HiPay_localPaymentName}-browserInfo" type="hidden" name="HF-browserInfo" />