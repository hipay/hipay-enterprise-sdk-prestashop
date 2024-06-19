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
{include file="$hipay_enterprise_tpl_dir/front/partial/js.strings.tpl"}

<form id="tokenizerForm" action="{$HiPay_action}" enctype="application/x-www-form-urlencoded"
    class="form-horizontal hipay-form-17" method="post" name="tokenizerForm" autocomplete="off">
    {if $HiPay_confHipay.payment.global.card_token}
        {include file="$hipay_enterprise_tpl_dir/front/partial/ps17/oneclick.tpl"}
    {/if}
    <div id="error-js" style="display:none" class="alert alert-danger">
        <ul>
            <li class="error"></li>
        </ul>
    </div>

    {if $HiPay_savedCC &&  $HiPay_confHipay.payment.global.card_token}
        <div class="option_payment">
            <span class="custom-radio">
                <input type="radio" id="radio-no-token" name="ccTokenHipay" value="noToken" />
                <span></span>
            </span>
            <label for="radio-no-token"><strong>{l s='Pay with a new credit card' mod='hipay_enterprise'}</strong></label>
        </div>
    {/if}
    <div id="credit-card-group"
        class="form-group group-card  {if $HiPay_savedCC &&  $HiPay_confHipay.payment.global.card_token}collapse{/if}">
        <div class="row">
            {if $HiPay_savedCC}
                <div class="col-md-1"></div>
            {/if}
            <div class="col-md-11">
                {include file="$hipay_enterprise_tpl_dir/hook/paymentForm-direct-post.tpl"}

                {if $HiPay_confHipay.payment.global.card_token && !$HiPay_is_guest }
                    <div class="row">
                        <span class="custom-checkbox" id="save-credit-card">
                            <input id="saveTokenHipay" type="checkbox" name="saveTokenHipay">
                            <span><i class="material-icons checkbox-checked"></i></span>
                            <label
                                for="saveTokenHipay">{l s='Save credit card (One click payment)' mod='hipay_enterprise'}</label>
                        </span>
                    </div>
                {/if}
            </div>
        </div>
    </div>
</form>
<div id="payment-loader-hp" style='text-align: center; display:none;'>
    <div><strong>{l s='Your payment is being processed. Please wait.'  mod='hipay_enterprise'}</strong></div>
    <img src="{$HiPay_this_path_ssl}/views/img/loading.gif" alt="loading payment">
</div>

<script>
   document.addEventListener('DOMContentLoaded', setSelectedPaymentMethod, false);

    var activatedCreditCard = [];
    {foreach $HiPay_activatedCreditCard as $cc}
        activatedCreditCard.push("{$cc}");
    {/foreach}
    var lang = "{$HiPay_languageIsoCode}";
    var activatedCreditCardError = "{l s='This credit card type or the order currency is not supported. Please choose an other payment method.' mod='hipay_enterprise'}";
    var myPaymentMethodSelected = false;

    function setSelectedPaymentMethod() {
        myPaymentMethodSelected = $(".payment-options").find(
            "input[data-module-name='credit_card']").is(
            ":checked");
        $(".payment-options").change(function() {
            myPaymentMethodSelected = $(".payment-options").find("input[data-module-name='credit_card']").is(
                ":checked");
        });
    }
    {if $HiPay_confHipay.account.global.sandbox_mode}
        var api_tokenjs_mode = "stage";
        var api_tokenjs_username = "{$HiPay_confHipay.account.sandbox.api_tokenjs_username_sandbox}";
        var api_tokenjs_password_publickey = "{$HiPay_confHipay.account.sandbox.api_tokenjs_password_publickey_sandbox}";
    {else}
        var api_tokenjs_mode = "production";
        var api_tokenjs_username = "{$HiPay_confHipay.account.production.api_tokenjs_username_production}";
        var api_tokenjs_password_publickey = "{$HiPay_confHipay.account.production.api_tokenjs_password_publickey_production}";
    {/if}
</script>
