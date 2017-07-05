<div class="card-js ">
    <input id="card-number" class="card-number my-custom-class" name="card-number">
    <input id="the-card-name-id" class="name"  name="card-holders-name" placeholder="Name on card">
    <input id="expiry-month" class="expiry-month" name="expiry-month">
    <input id="expiry-year" class="expiry-year" name="expiry-year">
    <input id="cvc" class="cvc" name="cvc">
</div>
<input id="card-token" type='hidden' name='card-token' value='' />
<input id="card-brand" type='hidden' name='card-brand' value='' />
<input id="card-pan" type='hidden' name='card-pan' value='' />
<input id="card-holder" type='hidden' name='card-holder' value='' />
<input id="card-expiry-month" type='hidden' name='card-expiry-month' value='' />
<input id="card-expiry-year" type='hidden' name='card-expiry-year' value='' />
<input id="card-issuer" type='hidden' name='card-issuer' value='' />
<input id="card-country" type='hidden' name='card-country' value='' />
<input id="ioBB" type="hidden" name="ioBB">
<p id="payment-loader-hp" style='text-align: center; display:none;'>
    <strong>{l s='Your payment is being proceded. Please wait.'}</strong> <br/>
    <img src="{$this_path_ssl}/views/img/loading.gif">
</p>