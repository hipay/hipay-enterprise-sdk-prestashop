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
<div class="card-js " data-icon-colour="#158CBA">
    <input id="card-number" class="card-number my-custom-class" name="card-number">
    <input id="the-card-name-id" class="name" name="card-holders-name" placeholder="Name on card">
    <input id="expiry-month" class="expiry-month" name="expiry-month">
    <input id="expiry-year" required class="expiry-year" name="expiry-year">
    <input id="cvc" class="cvc" data-toggle="tooltip"
           title="3-digit security code usually found on the back of your card. American Express cards have a 4-digit code located on the front." name="cvc">
</div>

<input id="card-token" type='hidden' name='card-token' value=''/>
<input id="card-brand" type='hidden' name='card-brand' value=''/>
<input id="card-pan" type='hidden' name='card-pan' value=''/>
<input id="card-holder" type='hidden' name='card-holder' value=''/>
<input id="card-expiry-month" type='hidden' name='card-expiry-month' value=''/>
<input id="card-expiry-year" type='hidden' name='card-expiry-year' value=''/>
<input id="card-issuer" type='hidden' name='card-issuer' value=''/>
<input id="card-country" type='hidden' name='card-country' value=''/>
<input id="ioBB" type="hidden" name="ioBB">
<script>
    (function () {
        hiPayInputControl.addInput('cc', 'card-number', 'creditcardnumber', true);
        hiPayInputControl.addInput('cc', 'the-card-name-id', null, true);
        hiPayInputControl.addInput('cc', 'cvc', null, true);
     })();
</script>