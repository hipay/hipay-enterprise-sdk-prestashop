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
{capture name=path}{l s='HiPay payment.' mod='hipay_enterprise'}{/capture}
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
        <div class="order_carrier_content box">
            {if $confHipay.payment.global.card_token}
                {if $savedCC}
                    <h2 class="page-subheading">{l s='Pay with a saved credit card' mod='hipay_enterprise'}</h2>
                    <div id="error-js-oc" style="display:none" class="alert alert-danger">
                        <p>There is 1 error</p>
                        <ol>
                            <li class="error-oc"></li>
                        </ol>
                    </div>
                    {if $status_error_oc == '400'}
                        <div class="alert alert-danger">
                            <p>There is 1 error</p>
                            <ol>
                                <li>{l s='The request was rejected due to a validation error. Please verify the card details you entered.' mod='hipay_enterprise'}</li>
                            </ol>
                        </div>
                    {/if}

                    {foreach $savedCC as $cc}
                        <div class="">
                            <label>
                                <input type="radio" name="ccTokenHipay" id="ccTokenHipay" value="{$cc.token}"/>
                                {$cc.pan} ({"%02d"|sprintf:$cc.card_expiry_month} / {$cc.card_expiry_year})
                                - {$cc.card_holder} <img src="{$this_path_ssl}/views/img/{$cc.brand|lower}_small.png"/>
                            </label>
                        </div>
                        <br/>
                    {/foreach}
                    <button id="pay-button-one-click" type="submit" name="processCarrierHipay"
                            class="button btn btn-default standard-checkout button-medium col-lg-12 col-md-12 col-xs-12"
                            style="">
                        <span>
                            {l s='Pay' mod='hipay_enterprise'}
                        </span>
                    </button>
                {/if}
            {/if}

            <h2 class="page-subheading">{l s='Pay by credit card' mod='hipay_enterprise'}</h2>
            {include file="$hipay_enterprise_tpl_dir/../front/partial/paymentError.tpl"}
            <div class="control-group">
                <p><strong>{l s='Amount to pay ' mod='hipay_enterprise'}:</strong> {$amount} {$currency->iso_code} </p>
                <div style="clear: both;"></div>
            </div>
            <br/>
            {include file="$hipay_enterprise_tpl_dir/paymentForm.tpl"}
            <br/>
            {if $confHipay.payment.global.card_token && !$is_guest}
                <div class="checkbox">
                    <label for="newsletter">
                        <div class="checker" id="uniform-newsletter">
                            <span class="">
                                <input id="saveTokenHipay" type="checkbox" name="saveTokenHipay">
                            </span>
                        </div>
                        {l s='Save credit card (One click payment)' mod='hipay_enterprise'}
                    </label>
                </div>
            {/if}
            <br/>
            <button id="pay-button" type="submit" name="processCarrierHipay"
                    class="button btn btn-default standard-checkout button-medium col-lg-12 col-md-12 col-xs-12"
                    style="">
                <span>
                    {l s='Pay' mod='hipay_enterprise'}
                </span>
            </button>
        </div>
    </form>
    <p id="payment-loader-hp" style='text-align: center; display:none;'>
        <strong>{l s='Your payment is being processed. Please wait.' mod='hipay_enterprise'}</strong> <br/>
        <img src="{$this_path_ssl}/views/img/loading.gif">
    </p>
    {include file="$hipay_enterprise_tpl_dir/../front/partial/js.strings.tpl"}
    <script>
        var activatedCreditCard = JSON.parse('{$activatedCreditCard}');
        var activatedCreditCardError = "{l s='This credit card type or the order currency is not supported. Please choose a other payment method.' mod='hipay_enterprise'}";
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
    </script>
    <script type="text/javascript" src="{$this_path_ssl}views/js/card-js.min.js"></script>
    <script type="text/javascript" src="{$this_path_ssl}views/js/card-tokenize.js"></script>
{/if}