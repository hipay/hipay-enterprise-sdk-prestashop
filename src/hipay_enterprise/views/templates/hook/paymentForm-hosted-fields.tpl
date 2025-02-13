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

<!--[if IE 9]>
<div class="ie9 hipayHF-container" id="hipayHF-container">
<![endif]-->
<!--[if gt IE 9]><!-->
<div class="hipayHF-container" id="hipayHF-container">
    <!--<![endif]-->
    <div id="hipayHF-saved-cards-container hidden" class="hipayHF-saved-cards">
        <div id="hipayHF-card-saved-cards"></div>
    </div>
    <div id="hipayHF-card-form-container" class="hipayHF-card-form-container hidden">
        <div class="hipayHF-row">
            <div class="hipayHF-field-container">
                <div class="hipayHF-field" id="hipayHF-card-holder"></div>
                <label class="hipayHF-label" for="hipayHF-card-holder">{l s='Fullname' mod='hipay_enterprise'}</label>
                <div class="hipayHF-baseline"></div>
                <div class="hipay-field-error" data-hipay-id='hipay-card-field-error-cardHolder'></div>
            </div>
        </div>
        <div class="hipayHF-row">
            <div class="hipayHF-field-container">
                <div class="hipayHF-field" id="hipayHF-card-number"></div>
                <label class="hipayHF-label" for="hipayHF-card-number">{l s='Card Number' mod='hipay_enterprise'}</label>
                <div class="hipayHF-baseline"></div>
                <div class="hipay-field-error" data-hipay-id='hipay-card-field-error-cardNumber'></div>
            </div>
        </div>
        <div class="hipayHF-row">
            <div class="hipayHF-field-container hipayHF-field-container-half">
                <div class="hipayHF-field" id="hipayHF-date-expiry"></div>
                <label class="hipayHF-label" for="hipayHF-date-expiry">{l s='Expiry date' mod='hipay_enterprise'}</label>
                <div class="hipayHF-baseline"></div>
                <div class="hipay-field-error" data-hipay-id='hipay-card-field-error-expiryDate'></div>
            </div>
            <div class="hipayHF-field-container hipayHF-field-container-half">
                <div class="hipayHF-field" id="hipayHF-cvc"></div>
                <label class="hipayHF-label" for="hipayHF-cvc">{l s='CVC' mod='hipay_enterprise'}</label>
                <div class="hipayHF-baseline"></div>
                <div class="hipay-field-error" data-hipay-id='hipay-card-field-error-cvc'></div>
            </div>
        </div>
        <div class="hipayHF-elements-container">
            <div id="hipayHF-help-cvc"></div>
        </div>
        {if !$HiPay_is_guest}
            <div id="hipayHF-saved-card-btn"></div>
       {/if}
    </div>
</div>
{include file="$hipay_enterprise_tpl_dir/front/partial/cc.hidden.inputs.tpl"}
