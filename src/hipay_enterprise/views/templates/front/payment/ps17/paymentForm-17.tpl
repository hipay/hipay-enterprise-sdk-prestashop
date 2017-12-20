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
<form id="tokenizerForm" action="{$action}" enctype="application/x-www-form-urlencoded"
      class="form-horizontal hipay-form-17" method="post"
      name="tokenizerForm" autocomplete="off">
    {if $confHipay.payment.global.card_token}
        {if $savedCC}
            <div class="option_payment saved-card"><label for="radio-with-token"><strong>{l s='Pay with a saved credit card' mod='hipay_enterprise'}</strong></label></div>
            <div id="error-js-oc" style="display:none" class="alert alert-danger">
                <span>There is 1 error</span>
                <ol>
                    <li class="error-oc"></li>
                </ol>
            </div>
            {foreach $savedCC as $cc}
                <div class="form-group row group-card">
                    <div class="col-md-1">
                        <span class="custom-radio">
                            <input type="radio" class="radio-with-token" name="ccTokenHipay" checked="checked" value="{$cc.token}"/>
                            <span></span>
                        </span>
                    </div>
                    <div class="col-md-5">
                            <div class="row">
                                <label for="radio-with-token">
                                    <span class="hipay-img col-md-2 col-xs-3"><img class="card-img" src="{$this_path_ssl}/views/img/{$cc.brand|lower}_small.png"/> </span>
                                    <span class="hipay-pan col-md-10 col-xs-9">{$cc.pan}</span>
                                    <span class="hipay-exp-date col-md-10 col-xs-9">{l s='Exp. date'  mod='hipay_enterprise'}
                                        : {"%02d"|sprintf:$cc.card_expiry_month}/{$cc.card_expiry_year}</span>
                                    <span class="hipay-card-holder col-md-10 col-xs-9">{$cc.card_holder}</span>
                                </label>
                            </div>
                    </div>
                </div>
            {/foreach}
        {/if}
    {/if}
    <div id="error-js" style="display:none" class="alert alert-danger">
        <ul>
            <li class="error"></li>
        </ul>
    </div>

    {if $savedCC &&  $confHipay.payment.global.card_token}
        <div class="option_payment">
                <span class="custom-radio">
                    <input type="radio" id="radio-no-token" name="ccTokenHipay" value="noToken" />
                    <span></span>
                </span>
            <label for="radio-no-token"><strong>{l s='Pay with a new credit card' mod='hipay_enterprise'}</strong></label>
        </div>
    {/if}
    <div id="credit-card-group" class="form-group group-card  {if $savedCC &&  $confHipay.payment.global.card_token}collapse{/if}">
        <div class="row">
            {if $savedCC}
            <div class="col-md-1"></div>
            {/if}
            <div class="col-md-11">
                {include file="$hipay_enterprise_tpl_dir/hook/paymentForm.tpl"}

                {if $confHipay.payment.global.card_token && !$is_guest }
                    <div class="row">
                        <span class="custom-checkbox" id="save-credit-card">
                            <input id="saveTokenHipay" type="checkbox" name="saveTokenHipay">
                            <span><i class="material-icons checkbox-checked"></i></span>
                            <label for="saveTokenHipay">{l s='Save credit card (One click payment)' mod='hipay_enterprise'}</label>
                        </span>
                    </div>
                {/if}
            </div>
        </div>
    </div>
</form>
<div id="payment-loader-hp" style='text-align: center; display:none;'>
    <div><strong>{l s='Your payment is being processed. Please wait.'  mod='hipay_enterprise'}</strong></div>
    <img src="{$this_path_ssl}/views/img/loading.gif" alt="loading payment">
</div>

<script>
    document.addEventListener('DOMContentLoaded', setSelectedPaymentMethod, false);

    var activatedCreditCard = [];
    {foreach $activatedCreditCard as $cc}
        activatedCreditCard.push("{$cc}");
    {/foreach}

    var activatedCreditCardError = "{l s='This credit card type or the order currency is not supported. Please choose a other payment method.' mod='hipay_enterprise'}";
    var myPaymentMethodSelected = false;
    function setSelectedPaymentMethod() {
        $(".payment-options").change(function () {
            myPaymentMethodSelected = $(".payment-options").find("input[data-module-name='credit_card']").is(":checked");
        });
    }
    {if $confHipay.account.global.sandbox_mode}
    var api_tokenjs_mode = "stage";
    var api_tokenjs_username = "{$confHipay.account.sandbox.api_tokenjs_username_sandbox}";
    var api_tokenjs_password_publickey = "{$confHipay.account.sandbox.api_tokenjs_password_publickey_sandbox}";
    {else}
    var api_tokenjs_mode = "production";
    var api_tokenjs_username = "{$confHipay.account.production.api_tokenjs_username_production}";
    var api_tokenjs_password_publickey = "{$confHipay.account.production.api_tokenjs_password_publickey_production}";
    {/if}
</script>
