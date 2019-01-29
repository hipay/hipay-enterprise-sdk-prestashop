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
<form action="{$action}" id="hpaymentForm" class="form-horizontal hipay-form-17" method="post">
    {if $savedCC && $confHipay.payment.global.card_token}
        {include file="$hipay_enterprise_tpl_dir/front/partial/ps17/oneclick.tpl"}

            <div class="option_payment">
                    <span class="custom-radio">
                        <input type="radio" id="radio-no-token" name="ccTokenHipay" value="noToken"/>
                        <span></span>
                    </span>
                <label for="radio-no-token"><strong>{l s='Pay with a new credit card' mod='hipay_enterprise'}</strong></label>
            </div>
    {/if}
    <div class="row" id="group-without-token" style="{if $savedCC && $confHipay.payment.global.card_token}display:none;{/if}">
        {if $confHipay.payment.global.display_hosted_page != 'iframe'}
            <p>{l s='You will be redirected to an external payment page. Please do not refresh the page during the process' mod='hipay_enterprise'}</p>
        {else}
            <p>{l s='Confirm your order to go to the payment page' mod='hipay_enterprise'}</p>
        {/if}

        {if $confHipay.payment.global.card_token && !$is_guest }
            <div class="row">
                    <span class="custom-checkbox" id="save-credit-card">
                        <input id="saveTokenHipay" type="checkbox" name="saveTokenHipay">
                        <span><i class="material-icons checkbox-checked"></i></span>
                        <label for="saveTokenHipay">{l s='Save credit card (One click payment)' mod='hipay_enterprise'}</label>
                    </span>
            </div>
        {/if}
    </div>

</form>
<div id="payment-loader-hp" style='text-align: center; display:none;'>
    <div><strong>{l s='Your payment is being processed. Please wait.'  mod='hipay_enterprise'}</strong></div>
    <img src="{$this_path_ssl}/views/img/loading.gif" alt="loading payment">
</div>

<script>
    document.addEventListener('DOMContentLoaded',
        function() {
            $("#hpaymentForm").submit(function (e) {
                var form = this;
                e.preventDefault();
                e.stopPropagation();

                if (isOneClickSelected()) {
                    $("#hpaymentForm").hide();
                    $("#payment-loader-hp").show();
                    $("#payment-confirmation > .ps-shown-by-js > button").prop("disabled", true);
                }

                form.submit();
                return true;
            });

            $('#radio-no-token').change(function () {
                if ($('#radio-no-token').is(":checked")) {
                    $('#group-without-token').show();
                } else {
                    $('#group-without-token').hide();
                }

            });
        },
        false
    );
</script>

