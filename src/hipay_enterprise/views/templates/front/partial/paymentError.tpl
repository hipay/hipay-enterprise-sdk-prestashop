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
<div id="error-js" style="display:none" class="alert alert-danger">
    <span>{l s='An error has occurred' mod='hipay_enterprise'}</span>
    <ol>
{if $status_error=='200'}
    <li class="error"></li>
{else if $status_error=='400'}
    <li>{l s='The request was rejected due to a validation error. Please verify the card details you entered.' mod='hipay_enterprise'}</li>
{else if $status_error=='503'}
    <li>{l s='HiPay TPP is temporarily unable to process the request. Try again later.' mod='hipay_enterprise'}</li>
{else if $status_error=='403'}
    <li>{l s='A forbidden action has been identified, process has been cancelled.' mod='hipay_enterprise'}</li>
{else if $status_error=='999'}
    <li>{l s='Please select one of the memorized card before continuing.' mod='hipay_enterprise'}</li>
{else if $status_error=='404'}
    <li>{l s='This credit card type or the order currency is not supported. Please choose a other payment method.' mod='hipay_enterprise'}</li>
{else}
    <li>{l s='An error occured, process has been cancelled.' mod='hipay_enterprise'}</li>
{/if}
    </ol>
</div>