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

<div class="form-group ">
    <label class="{if isset($field.required) && $field.required}required{/if}">
        {$field["label"]}
    </label>
    <input id="{$localPaymentName}-{$name}" class="form-control" name="{$name}" type="text" value="" {if isset($field.required) && $field.required}required{/if}>
</div>