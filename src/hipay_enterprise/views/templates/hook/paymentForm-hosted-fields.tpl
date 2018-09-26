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
<div class="hipayHF-container" id="hipayHF-container">
        <div class="hipayHF-field-container">
            <div class="hipayHF-field" id="hipayHF-card-holder"></div>
            <label class="hipayHF-label" for="hipayHF-card-holder">{l s='Fullname' mod='hipay_enterprise'}</label>
        </div>
        <div class="hipayHF-field-container">
            <div class="hipayHF-field" id="hipayHF-card-number"></div>
            <label class="hipayHF-label" for="hipayHF-card-number">{l s='Card Number' mod='hipay_enterprise'}</label>
        </div>
        <div class="grid-2">
            <div class="hipayHF-field-container">
                <div class="hipayHF-field" id="hipayHF-date-expiry"></div>
                <label class="hipayHF-label" for="hipayHF-date-expiry">{l s='Expiry date' mod='hipay_enterprise'}</label>
            </div>
            <div class="hipayHF-field-container">
                <div class="hipayHF-field" id="hipayHF-cvc"></div>
                <label class="hipayHF-label" id="hipayHF-label-cvc" for="hipayHF-cvc">{l s='CVC' mod='hipay_enterprise'}</label>
            </div>
        </div>
        <div id="hipayHF-help-cvc"></div>
</div>
{include file="$hipay_enterprise_tpl_dir/front/partial/cc.hidden.inputs.tpl"}
