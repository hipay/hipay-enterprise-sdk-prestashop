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
{capture name=path}{l s='Payment.' mod='hipay_enterprise'}{/capture}
<h2>{l s='Order summary' mod='hipay_enterprise'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if $nbProducts <= 0}
    <p class="warning">{l s='Your shopping cart is empty.' mod='hipay_enterprise'}</p>
{else}
    <h3>{l s='HiPay payment.' mod='hipay_enterprise'}</h3>
    <form enctype="application/x-www-form-urlencoded"
          action="{$link->getModuleLink('hipay_enterprise', 'redirect', [], true)|escape:'html'}"
          class="form-horizontal col-lg-6 col-lg-offset-3" method="post" name="tokenizerForm" id="tokenizerForm"
          autocomplete="off">
        <div class="order_carrier_content">
            <h2 class="hipay-form-16-title">{l s='Pay by credit card' mod='hipay_enterprise'}</h2>
            {include file="$hipay_enterprise_tpl_dir/front/partial/paymentError.tpl"}
            <h5><strong>{l s='Amount to pay ' mod='hipay_enterprise'}:</strong> {$amount} {$currency->iso_code} </h5>

            {if $confHipay.payment.global.card_token}
                {include file="$hipay_enterprise_tpl_dir/front/partial/ps16/oneclick.tpl"}
            {/if}
            <div id="error-js" style="display:none" class="alert alert-danger">
                <ul>
                    <li class="error"></li>
                </ul>
            </div>
            {if $savedCC &&  $confHipay.payment.global.card_token}
                <div class="option_payment">
                <span class="custom-radio">
                    <input type="radio" id="radio-no-token" name="ccTokenHipay" value="noToken"/>
                    <span></span>
                </span>
                    <label for="radio-no-token"><strong>{l s='Pay with a new credit card' mod='hipay_enterprise'}</strong></label>
                </div>
            {/if}
            <div id="credit-card-group" class="credit-card-group-ps16" >
                {include file="$hipay_enterprise_tpl_dir/hook/paymentForm-hosted-fields.tpl"}

                {if $confHipay.payment.global.card_token && !$is_guest}
                    <div class="checkbox one-click" >
                    <span for="newsletter">
                        <div class="checker" id="uniform-newsletter">
                            <span class="">
                                <input id="saveTokenHipay" type="checkbox" name="saveTokenHipay">
                            </span>
                        </div>
                        <label for="saveTokenHipay">{l s='Save credit card (One click payment)' mod='hipay_enterprise'}</label>
                    </span>
                    </div>
                {/if}
            </div>
            <div class="block-pay">
                <button id="pay-button" type="submit" name="processCarrierHipay"
                        class="button btn btn-default standard-checkout button-medium col-lg-12 col-md-12 col-xs-12"
                        style="">
                    <span>
                        {l s='Pay' mod='hipay_enterprise'}
                    </span>
                </button>
            </div>
        </div>

    </form>
    <p id="payment-loader-hp" style='text-align: center; display:none;'>
        <strong>{l s='Your payment is being processed. Please wait.' mod='hipay_enterprise'}</strong> <br/>
        <img src="{$this_path_ssl}/views/img/loading.gif">
    </p>
    <script type="text/javascript">
        {if $savedCC &&  $confHipay.payment.global.card_token}
        $('#credit-card-group').collapse('hide');
        {/if}
        var cardHolderFirstName = "{$customerFirstName}";
        var cardHolderLastName = "{$customerLastName}";
        var lang = "{$lang_iso}";
        var activatedCreditCard = JSON.parse('{$activatedCreditCard}');
        var activatedCreditCardError = "{l s='This credit card type or the order currency is not supported. Please choose an other payment method.' mod='hipay_enterprise'}";
        var myPaymentMethodSelected = true;
        {if $confHipay.account.global.sandbox_mode}
        var api_tokenjs_mode = "stage";
        var api_tokenjs_username = "{$confHipay.account.sandbox.api_tokenjs_username_sandbox}";
        var api_tokenjs_password_publickey = "{$confHipay.account.sandbox.api_tokenjs_password_publickey_sandbox}";
        {else}
        var api_tokenjs_mode = "production";
        var api_tokenjs_username = "{$confHipay.account.production.api_tokenjs_username_production}";
        var api_tokenjs_password_publickey = "{$confHipay.account.production.api_tokenjs_password_publickey_production}";
        {/if}
        var oneClick = !!{$confHipay.payment.global.card_token};
        var style = {$confHipay.payment.global.hosted_fields_style|@json_encode nofilter};
    </script>
{/if}
