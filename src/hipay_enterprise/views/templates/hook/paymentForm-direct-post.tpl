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
<div class="card-js " data-icon-colour="#158CBA">
    <input id="card-number" class="card-number my-custom-class" name="card-number">
    <input id="the-card-name-id" class="name" name="card-holders-name" value="{$customerFirstName} {$customerLastName}">
    <input id="expiry-month" class="expiry-month" name="expiry-month">
    <input id="expiry-year" required class="expiry-year" name="expiry-year">
    <input id="cvc" class="cvc" data-toggle="tooltip"
           title=""
           name="cvc">
    <input name="text" type="text">
</div>

{include file="$hipay_enterprise_tpl_dir/front/partial/cc.hidden.inputs.tpl"}

<script>
    (function () {
        hiPayInputControl.addInput('cc', 'card-number', 'creditcardnumber', true);
        hiPayInputControl.addInput('cc', 'the-card-name-id', null, true);
        hiPayInputControl.addInput('cc', 'cvc', 'cvc', true);
    })();

    document.addEventListener("DOMContentLoaded", function(event) {
        var hipay = HiPay({
            username: api_tokenjs_username,
            password: api_tokenjs_password_publickey,
            environment: api_tokenjs_mode,
            lang: lang
        });

        $('#browserInfo').val(JSON.stringify(hipay.getBrowserInfo()));
    });
</script>
