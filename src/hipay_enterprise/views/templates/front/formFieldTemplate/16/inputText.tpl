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

<label class="{if isset($field.required) && $field.required}required{/if}">
    {$field["label"][$language]}
</label>
<input id="{$localPaymentName}-{$name}"
       class="form-control {if isset($formErrors) && isset($formErrors[$name])} error-input-hp {/if}" name="{$name}"
       type="text" value="" {if isset($field.required) && $field.required}required{/if}>
{if isset($formErrors) && isset($formErrors[$name])}
    <p class="error-text-hp">{$formErrors[$name]}</p>
{/if}

