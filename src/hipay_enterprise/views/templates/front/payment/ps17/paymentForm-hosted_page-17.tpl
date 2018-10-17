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
<form action="{$action}" class="form-horizontal hipay-form-17" method="post">
    {if $confHipay.payment.global.display_hosted_page != 'iframe'}
        <p>{l s='You will be redirected to an external payment page. Please do not refresh the page during the process' mod='hipay_enterprise'}</p>
    {else}
        <p>{l s='Confirm your order to go to the payment page' mod='hipay_enterprise'}</p>
    {/if}
</form>
