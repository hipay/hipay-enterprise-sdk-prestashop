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

{* Include SDK script with SRI support *}
{include file="$hipay_enterprise_tpl_dir/front/partial/sdk-script.tpl"}
<form id="tokenizerForm" action="{$HiPay_action}" enctype="application/x-www-form-urlencoded"
    class="form-horizontal hipay-form-17" method="post" name="tokenizerForm" autocomplete="off">
    <div id="error-js" style="display:none" class="alert alert-danger">
        <ul>
            <li class="error"></li>
        </ul>
    </div>
    <div id="credit-card-group"
        class="form-group group-card">
        <div class="row">
            <div class="col-md-11">
                {include file="$hipay_enterprise_tpl_dir/hook/paymentForm-hosted-fields.tpl"}
            </div>
        </div>
    </div>
</form>
<div id="payment-loader-hp" style='text-align: center; display:none; margin-top: 32px; margin-bottom: 32px'>
    <div style='margin-bottom: 12px'><strong>{l s='Your payment is being processed. Please wait.'  mod='hipay_enterprise'}</strong></div>
    <img src="{$HiPay_this_path_ssl}/views/img/loading.gif" alt="loading payment">
</div>

<script>
    document.addEventListener('DOMContentLoaded', setSelectedPaymentMethod, false);

    {if $HiPay_confHipay.account.global.sandbox_mode}
        var api_tokenjs_mode = "stage";
        var api_tokenjs_username = "{$HiPay_confHipay.account.sandbox.api_tokenjs_username_sandbox}";
        var api_tokenjs_password_publickey = "{$HiPay_confHipay.account.sandbox.api_tokenjs_password_publickey_sandbox}";
    {else}
        var api_tokenjs_mode = "production";
        var api_tokenjs_username = "{$HiPay_confHipay.account.production.api_tokenjs_username_production}";
        var api_tokenjs_password_publickey = "{$HiPay_confHipay.account.production.api_tokenjs_password_publickey_production}";
    {/if}

    var isOneClickEnabled = typeof PaymentOPC !== 'undefined'
    ? (prestashop.customer.is_logged ? '{$HiPay_confHipay.payment.global.card_token}' : '')
    : '{$HiPay_confHipay.payment.global.card_token}';
    isOneClickEnabled = Boolean(Number(isOneClickEnabled));

    var number_saved_cards_displayed = {$HiPay_confHipay.payment.global.number_saved_cards_displayed|@json_encode nofilter};

    var cardHolderFirstName = typeof PaymentOPC !== 'undefined'
    ? (prestashop.customer.is_logged ? '{$HiPay_customerFirstName}' : '')
    : '{$HiPay_customerFirstName}';

    var cardHolderLastName = typeof PaymentOPC !== 'undefined'
    ? (prestashop.customer.is_logged ? '{$HiPay_customerLastName}' : '')
    : '{$HiPay_customerLastName}';

    var paymentProducts = {$HiPay_confHipay.payment.credit_card|@json_encode nofilter}
    var paymentProductsActivated = Object.keys(paymentProducts).filter(key => paymentProducts[key].activated === "1");

    var style = {$HiPay_confHipay.payment.global.hosted_fields_style|@json_encode nofilter};

    var savedCards = {$HiPay_savedCC|@json_encode nofilter};

    var activatedCreditCard = [];
    {foreach $HiPay_activatedCreditCard as $cc}
        activatedCreditCard.push("{$cc}");
    {/foreach}
    var activatedCreditCardError = "{l s='This credit card type or the order currency is not supported. Please choose an other payment method.' mod='hipay_enterprise'}";

    var lang = "{$HiPay_languageIsoCode}";

    var myPaymentMethodSelected = false;

    function setSelectedPaymentMethod() {
        myPaymentMethodSelected = $("#checkout-payment-step").find(
            "input[data-module-name='credit_card']").is(
            ":checked");
        $(document).on("change", "#checkout-payment-step", function() {
            myPaymentMethodSelected = $("#checkout-payment-step").find(
                "input[data-module-name='credit_card']").is(
                ":checked");
        });
    }

    if (typeof OPC !== 'undefined') {
        new Promise((resolve) => {
            prestashop.on('opc-payment-getPaymentList-complete', resolve);
        }).then(() => {
            jQuery(document).ready(function($) {
                updatePaymentMethodSelected();
                initEventsHostedFields();
                initHostedFields();
            });
        });
    }
</script>