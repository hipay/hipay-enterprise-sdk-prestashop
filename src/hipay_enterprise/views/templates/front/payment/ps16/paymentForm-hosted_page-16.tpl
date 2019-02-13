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
{include file="$hipay_enterprise_tpl_dir/front/partial/js.strings.tpl"}
{capture name=path}{l s='Payment.' mod='hipay_enterprise'}{/capture}
<h2>{l s='Order summary' mod='hipay_enterprise'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if $nbProducts <= 0}
    <p class="warning">{l s='Your shopping cart is empty.' mod='hipay_enterprise'}</p>
{else}
    <h3>{l s='HiPay payment.' mod='hipay_enterprise'}</h3>
    <form enctype="application/x-www-form-urlencoded"
          action="{$link->getModuleLink('hipay_enterprise', 'redirect', [], true)|escape:'html'}"
          class="form-horizontal col-lg-6 col-lg-offset-3" method="post" name="hpaymentForm" id="hpaymentForm"
          autocomplete="off">
        <div class="order_carrier_content box">
            <h2 class="page-subheading">{l s='Pay by credit card' mod='hipay_enterprise'}</h2>
            {include file="$hipay_enterprise_tpl_dir/front/partial/paymentError.tpl"}
            <h5><strong>{l s='Amount to pay ' mod='hipay_enterprise'}:</strong> {$amount} {$currency->iso_code} </h5>

            {if $confHipay.payment.global.card_token}
                {include file="$hipay_enterprise_tpl_dir/front/partial/ps16/oneclick.tpl"}
            {/if}
            <div id="error-js" style="display:none" class="alert alert-danger">
                <ul>
                    <li class="error"></li>
                </ul>
            </div>
            {if $savedCC &&  $confHipay.payment.global.card_token}
                <div class="option_payment">
                <span class="custom-radio">
                    <input type="radio" id="radio-no-token" name="ccTokenHipay" value="noToken"/>
                    <span></span>
                </span>
                    <label for="radio-no-token"><strong>{l s='Pay with a new credit card' mod='hipay_enterprise'}</strong></label>
                </div>
            {/if}

            <div class="col-lg-12 col-md-12 col-xs-12" id="group-without-token" style="{if $savedCC && $confHipay.payment.global.card_token}display:none;{/if}">
                {if $confHipay.payment.global.display_hosted_page != 'iframe'}
                    <p>{l s='You will be redirected to an external payment page. Please do not refresh the page during the process' mod='hipay_enterprise'}</p>
                {else}
                    <p>{l s='Confirm your order to go to the payment page' mod='hipay_enterprise'}</p>
                    <input type="hidden" id="iframe-generate" name="iframeCall" value="1" />
                {/if}

                {if $confHipay.payment.global.card_token && !$is_guest}
                    {include file="$hipay_enterprise_tpl_dir/front/partial/ps16/savetoken.tpl"}
                    {if !$savedCC}
                        <input type="hidden" id="radio-no-token" name="ccTokenHipay" value="noToken" />
                    {/if}
                {/if}
            </div>

            <div class="block-pay">
                <button id="pay-button" type="submit" name="processCarrierHipay"
                        class="button btn btn-default standard-checkout button-medium col-lg-12 col-md-12 col-xs-12"
                        style="">
                    <span>
                        {l s='Pay' mod='hipay_enterprise'}
                    </span>
                </button>
            </div>
        </div>

    </form>
    <p id="payment-loader-hp" style='text-align: center; display:none;'>
        <strong>{l s='Your payment is being processed. Please wait.' mod='hipay_enterprise'}</strong> <br/>
        <img src="{$this_path_ssl}/views/img/loading.gif">
    </p>
{/if}
<script>
    document.addEventListener('DOMContentLoaded',
        function() {
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


