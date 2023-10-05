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
    {if $status_error=='200'}
        <p class="error"></p>
    {else if $status_error=='400'}
        <p class="error">
            {l s='The request was rejected due to a validation error. Please verify the card details you entered.' mod='hipay_enterprise'}
        </p>
    {else if $status_error=='503'}
        <p class="error">{l s='HiPay TPP is temporarily unable to process the request. Try again later.' mod='hipay_enterprise'}
        </p>
    {else if $status_error=='403'}
        <p class="error">{l s='A forbidden action has been identified, process has been cancelled.' mod='hipay_enterprise'}</p>
    {else if $status_error=='999'}
        <p class="error">{l s='Please select one of the memorized card before continuing.' mod='hipay_enterprise'}</p>
    {else if $status_error=='404'}
        <p class="error">
            {l s='This credit card type or the order currency is not supported. Please choose a other payment method.' mod='hipay_enterprise'}
        </p>
    {else}
        <p class="error">
            <strong>{l s='Error code' mod='hipay_enterprise'} : {$status_error}</strong>
            <br />
            {l s='An error occured, process has been cancelled.' mod='hipay_enterprise'}
        </p>
    {/if}
    <form id="{$localPaymentName}" enctype="application/x-www-form-urlencoded" action="{$action|escape:'html'}"
        class="form-horizontal col-lg-4 col-lg-offset-4" method="post" name="tokenizerForm" id="tokenizerForm"
        autocomplete="off">
        <div class="order_carrier_content box">
            <h2 class="page-subheading">{l s='Pay with %s' sprintf=$methodName mod='hipay_enterprise'}</h2>
            <div class="control-group">
                <p><strong>{l s='Amount to pay ' mod='hipay_enterprise'}:</strong> {$amount} {$currency->iso_code} </p>
                <div style="clear: both;"></div>
            </div>
            <br />
            {assign "psVersion" "16"}
            {include file="$hipay_enterprise_tpl_dir/hook/paymentLocalForm.tpl"}
            <p class="cart_navigation clearfix">
                <button id="pay-button" type="submit" name="processCarrier"
                    class="button btn btn-default standard-checkout button-medium col-lg-12 col-md-12 col-xs-12" style="">
                    <span>
                        {l s='Pay' mod='hipay_enterprise'}
                    </span>
                </button>
            </p>
        </div>

    </form>
    {include file="$hipay_enterprise_tpl_dir/front/partial/js.strings.tpl"}
    <script>
        $("#{$localPaymentName}").submit(function (e) {
            // prevent form from being submitted 
            e.preventDefault();
            e.stopPropagation();

            if (hiPayInputControl.HiPay_checkControl('{$localPaymentName}')) {
                this.submit();
            }
        });
    </script>
{/if}