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

<fieldset>
    <legend>{l s='Manual order payment (MO/TO)' }</legend>

    <p>{l s='You\'ll be redirected to HiPay payment page to complete this order payment' mod='hipay_enterprise'}</p>
    <form action="{$motoLink}" method="post" id="hipay_capture_form" class="form-horizontal">
        <input type="hidden" name="cart_id" value="{$cartId}"/>
        <button type="submit" name="motoPayment"
                class="btn btn-primary ">
            {l s='Pay Moto'}
        </button>
    </form>    
</fieldset>