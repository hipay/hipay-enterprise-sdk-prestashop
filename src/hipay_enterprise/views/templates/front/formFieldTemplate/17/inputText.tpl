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

<div class="form-group row">
    <label class="col-md-3 form-control-label {if isset($field.required) && $field.required}required{/if}">
        {$field["label"]}
    </label>
    <div class="col-md-9">
        <input id="{$localPaymentName}-{$name}" class="form-control input-hp" name="{$name}" type="text" value=""
               {if isset($field.required) && $field.required}required{/if}>
    </div>
</div>