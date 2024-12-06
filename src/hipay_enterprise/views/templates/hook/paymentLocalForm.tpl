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
{if ($HiPay_localPaymentName eq "paypal" && (isset($HiPay_Hosted_PayPal_v2) && $HiPay_Hosted_PayPal_v2)) OR $HiPay_localPaymentName eq "applepay"}
    {include file="$hipay_enterprise_tpl_dir/front/formFieldTemplate/$psVersion/paymentButtonForm.tpl"}
{/if}
{if $HiPay_localPaymentName eq "applepay"}
    {include file="$hipay_enterprise_tpl_dir/front/formFieldTemplate/$psVersion/inputApplePay.tpl"}
{elseif $HiPay_localPaymentName eq "paypal" && (isset($HiPay_Hosted_PayPal_v2) && $HiPay_Hosted_PayPal_v2)}
    {include file="$hipay_enterprise_tpl_dir/front/formFieldTemplate/$psVersion/inputPaypal.tpl"}
{elseif !$HiPay_forceHpayment}
    <div id="hipay-container-hosted-fields-{$HiPay_localPaymentName}"></div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var activatedLocalError = "{l s="This payment mean is unavailable or the order currency is not supported. Please choose an other payment method." mod="hipay_enterprise"}";
            {if $HiPay_confHipay.account.global.sandbox_mode}
                var api_tokenjs_mode = "stage";
                var api_tokenjs_username = "{$HiPay_confHipay.account.sandbox.api_tokenjs_username_sandbox}";
                var api_tokenjs_password_publickey = "{$HiPay_confHipay.account.sandbox.api_tokenjs_password_publickey_sandbox}";
            {else}
                var api_tokenjs_mode = "production";
                var api_tokenjs_username = "{$HiPay_confHipay.account.production.api_tokenjs_username_production}";
                var api_tokenjs_password_publickey = "{$HiPay_confHipay.account.production.api_tokenjs_password_publickey_production}";
            {/if}

            var container = "hipay-container-hosted-fields-{$HiPay_localPaymentName}";
            var amount = "{$HiPay_cart.totalAmount}";
            var options = {
                selector: container,
                template: "auto",
                isPaymentPageV2: true
            };

            // Only add request property if container includes 'alma'
            if (container.toLowerCase().includes('alma')) {
                options.request = { amount: Number(amount) };
            }

            var localHipay = new HiPay({
                username: api_tokenjs_username,
                password: api_tokenjs_password_publickey,
                environment: api_tokenjs_mode,
                lang: "{$HiPay_languageIsoCode}"
            });

            var localHF = localHipay.create("{$HiPay_localPaymentName}", options);
            var extraFields = [];

            // Create input for each additionnal Hosted Field
            setTimeout(function() {
                $("[data-hipay-id^=hipay-{$HiPay_localPaymentName}-field-row-]")
                .each(function(i, el) {
                    var field = $(el).data("hipay-id").split("row-")[1];
                    extraFields.push(field);

                    $("#{$HiPay_localPaymentName}-hipay")
                    .append('<input id="{$HiPay_localPaymentName}-' + field + '" type="hidden" name="HF-' + field + '" />');
                });
            }, 1000);

            $("#{$HiPay_localPaymentName}-hipay").submit(function(e) 
            {
                var form = this;
                e.preventDefault();
                e.stopPropagation();

                localHF.getPaymentData().then(
                    function(response) {
                        // Hide formular and show loader
                        $("#{$HiPay_localPaymentName}-hipay").hide();
                        $("#{$HiPay_localPaymentName}-payment-loader-hp").show();
                        $("#payment-confirmation > .ps-shown-by-js > button").prop("disabled", true);

                        // Fill hidden fields to send to server
                        $("#{$HiPay_localPaymentName}-paymentProductCode").val(response.payment_product);
                        $("#{$HiPay_localPaymentName}-browserInfo").val(JSON.stringify(response.browser_info));
                        extraFields.forEach(function(field) {
                            $("#{$HiPay_localPaymentName}-" + field).val(response[field]);
                        });

                        form.submit();
                        return true;
                    },
                    function(errors) {
                        $("#error-js").show();
                        $("#error-js").text(activatedLocalError);
                        return false;
                    }
                );
            });
        });
    </script>
    <input type="hidden" name="localSubmit" />
    <input class="ioBB" type="hidden" name="ioBB" />
    <input id="{$HiPay_localPaymentName}-paymentProductCode" type="hidden" name="HF-paymentProductCode" />
    <input id="{$HiPay_localPaymentName}-browserInfo" type="hidden" name="HF-browserInfo" />
{else}
    {if $HiPay_iframe}
        <p>{l s="Confirm your order to go to the payment page" mod="hipay_enterprise"}</p>
    {else}
        <p>{l s="You will be redirected to an external payment page. Please do not refresh the page during the process" mod="hipay_enterprise"}
        </p>
    {/if}
{/if}
