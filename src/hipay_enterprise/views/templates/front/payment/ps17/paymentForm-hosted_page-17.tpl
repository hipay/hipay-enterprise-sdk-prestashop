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
<form action="{$HiPay_action}" id="hpaymentForm" class="form-horizontal hipay-form-17" method="post">
    <div class="row" id="group-without-token"
        style="{if $HiPay_savedCC && $HiPay_confHipay.payment.global.card_token}display:none;{/if}">
        {if $HiPay_confHipay.payment.global.display_hosted_page != 'iframe'}
            <p class="col-md-12">
                {l s='You will be redirected to an external payment page. Please do not refresh the page during the process' mod='hipay_enterprise'}
            </p>
        {else}
            <p class="col-md-12">{l s='Confirm your order to go to the payment page' mod='hipay_enterprise'}</p>
            <input type="hidden" id="iframe-generate" name="iframeCall" value="1" />
        {/if}
    </div>
    <input id="ioBB" type="hidden" name="ioBB">
    <input id="browserInfo" type="hidden" name="browserInfo">
</form>
<div id="payment-loader-hp" style='text-align: center; display:none;'>
    <div><strong>{l s='Your payment is being processed. Please wait.'  mod='hipay_enterprise'}</strong></div>
    <img src="{$HiPay_this_path_ssl}/views/img/loading.gif" alt="loading payment">
</div>
<script>
    document.addEventListener('DOMContentLoaded',
        function() {
            {if $HiPay_confHipay.account.global.sandbox_mode}
                var api_tokenjs_mode = "stage";
                var api_tokenjs_username = "{$HiPay_confHipay.account.sandbox.api_tokenjs_username_sandbox}";
                var api_tokenjs_password_publickey = "{$HiPay_confHipay.account.sandbox.api_tokenjs_password_publickey_sandbox}";
            {else}
                var api_tokenjs_mode = "production";
                var api_tokenjs_username = "{$HiPay_confHipay.account.production.api_tokenjs_username_production}";
                var api_tokenjs_password_publickey = "{$HiPay_confHipay.account.production.api_tokenjs_password_publickey_production}";
            {/if}

            var lang = "{$HiPay_languageIsoCode}";

            var hipay = new HiPay({
                username: api_tokenjs_username,
                password: api_tokenjs_password_publickey,
                environment: api_tokenjs_mode,
                lang: lang
            });

            document.getElementById("browserInfo").value = JSON.stringify(hipay.getBrowserInfo());

            $("#hpaymentForm").submit(function(e) {
                var form = this;
                e.preventDefault();
                e.stopPropagation();

                if (isOneClickSelected()) {
                    $("#hpaymentForm").hide();
                    $("#payment-loader-hp").show();
                    $("#payment-confirmation > .ps-shown-by-js > button").prop("disabled", true);
                }

                form.submit();
                return true;
            });
        },
        false
    );
</script>