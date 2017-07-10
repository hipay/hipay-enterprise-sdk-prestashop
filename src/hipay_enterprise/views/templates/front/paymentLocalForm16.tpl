{**
* 2017 HiPay
*
* NOTICE OF LICENSE
*
*
* @author    HiPay <support.wallet@hipay.com>
* @copyright 2017 HiPay
* @license   https://github.com/hipay/hipay-wallet-sdk-prestashop/blob/master/LICENSE.md
*
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
        <p class="error">{l s='The request was rejected due to a validation error. Please verify the card details you entered.' mod='hipay_enterprise'}</p>
    {else if $status_error=='503'}
        <p class="error">{l s='HiPay TPP is temporarily unable to process the request. Try again later.' mod='hipay_enterprise'}</p>
    {else if $status_error=='403'}
        <p class="error">{l s='A forbidden action has been identified, process has been cancelled.' mod='hipay_enterprise'}</p>
    {else if $status_error=='999'}
        <p class="error">{l s='Please select one of the memorized card before continuing.' mod='hipay_enterprise'}</p>
    {else if $status_error=='404'}
        <p class="error">{l s='This credit card type or the order currency is not supported. Please choose a other payment method.' mod='hipay_enterprise'}</p>
    {else}
        <p class="error">
            <strong>{l s='Error code' mod='hipay_enterprise'} : {$status_error}</strong>
            <br/>
            {l s='An error occured, process has been cancelled.' mod='hipay_enterprise'}
        </p>
    {/if}
    <form enctype="application/x-www-form-urlencoded" action="{$action|escape:'html'}" class="form-horizontal"
          method="post" name="tokenizerForm" id="tokenizerForm" autocomplete="off">
        <div class="order_carrier_content box">
            <div class="control-group">
                <label class="control-label"
                       style="float: left; margin: 0 0px 0 0; font-size: 15px; font-weight: bold;">{l s='Order' mod='hipay_enterprise'}
                    :&nbsp;</label>
                <div class="controls" style="float: left; font-size: 13px; font-weight: bold;">
                    #{$cart_id}<span id="cartIdMessage"></span>
                    <input type="hidden" class="input-medium" name="cartId" id="cartId" value="{$cart_id}">
                </div>
                <div style="clear: both;"></div>
            </div>
            <br/>
            <div class="control-group">
                <label class="control-label"
                       style="float: left; margin: 0 0px 0 0; font-size: 15px; font-weight: bold;">{l s='Amount' mod='hipay_enterprise'}
                    :&nbsp;</label>
                <div class="controls" style="float: left; font-weight:bold; color:#072959;font-size:15px;">
                    {$amount} {$currency->iso_code}
                </div>
                <div style="clear: both;"></div>
            </div>
            <br/>
            {include file="$hipay_enterprise_tpl_dir/hook/paymentLocalForm.tpl"}
        </div>
        <p class="cart_navigation clearfix">
            <button id="pay-button" type="submit" name="processCarrier"
                    class="button btn btn-default standard-checkout button-medium" style="">
                <span>
                    {l s='Pay' mod='hipay_enterprise'}
                </span>
            </button>
        </p>
    </form>
{/if}