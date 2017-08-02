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

<div class="form-group row">
    <label class="col-md-3 form-control-label {if isset($field.required) && $field.required}required{/if}" >{$field["label"]}</label>

    <div class="col-md-9">
        <select id="{$localPaymentName}-{$name}" class="form-control" {if isset($field.required) && $field.required}required{/if} name="{$name}">
            <option value="1">{l s='Mr' mod='hipay_enterprise'}</option>
            <option value="2">{l s='Mrs' mod='hipay_enterprise'}</option>
        </select>
    </div>
</div>