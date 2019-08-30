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

        let deviceFingerprintInput = $('#realFingerprint');
        if(deviceFingerprintInput.length === 0) {
            deviceFingerprintInput = $('<input/>', {
                id: 'realFingerprint',
                type: 'hidden',
                name: 'ioBB'
            });
            $("#ioBB").attr('name', "ioBB_old");
            $("#ioBB").parent().append(deviceFingerprintInput);
        }
        deviceFingerprintInput.val(hipay.getDeviceFingerprint());
        if (hipay.getDeviceFingerprint() === undefined) {
            let retryCounter = 0;
            let interval = setInterval(function timeoutFunc() {
                retryCounter++;
                // If global_info init send event
                if (hipay.getDeviceFingerprint() !== undefined) {
                    deviceFingerprintInput.val(hipay.getDeviceFingerprint());
                    clearInterval(interval);
                }
                // Max retry = 3
                if (retryCounter > 3) {
                    clearInterval(interval);
                }
            }, 1000);
        }

        document.getElementById("browserInfo").value = JSON.stringify(hipay.getBrowserInfo());
    });
</script>
