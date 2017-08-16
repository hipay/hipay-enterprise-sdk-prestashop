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

<fieldset>
    <legend>{l s='Manual order payment (MO/TO)' mod='hipay_enterprise'}</legend>
    {if ( $config_hipay.account.global.sandbox_mode && (empty($config_hipay.account.sandbox.api_moto_username_sandbox) || empty($config_hipay.account.sandbox.api_moto_password_sandbox)) )}
        <p class="alert alert-warning">
            {l s='Your Sandbox MO/TO credentials are empty.' mod='hipay_enterprise'} <br/>
            {l s='To generate the invoice, you must capture the remaining amount due which will generate an invoice once the order full amount has been captured.' mod='hipay_enterprise'}
        </p>
    {else if (  !$config_hipay.account.global.sandbox_mode && (empty($config_hipay.account.sandbox.api_moto_username_production) || empty($config_hipay.account.sandbox.api_moto_password_production)) )}
        <p class="alert alert-warning">
            {l s='Your Production MO/TO credentials are empty.' mod='hipay_enterprise'} <br/>
            {l s='To generate the invoice, you must capture the remaining amount due which will generate an invoice once the order full amount has been captured.' mod='hipay_enterprise'}
        </p>
    {/if}
    <p>{l s='You\'ll be redirected to HiPay payment page to complete this order payment' mod='hipay_enterprise'}</p>
    <form action="{$motoLink}" method="post" id="hipay_capture_form" class="form-horizontal">
        <input type="hidden" name="cart_id" value="{$cartId}"/>
        <button type="submit" name="motoPayment"
                class="btn btn-primary ">
            {l s='Pay Moto' mod='hipay_enterprise'}
        </button>
    </form>    
</fieldset>