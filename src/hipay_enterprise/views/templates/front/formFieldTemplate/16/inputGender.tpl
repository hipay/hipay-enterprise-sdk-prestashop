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
<label class="{if isset($field.required) && $field.required}required{/if}" >{$field["label"]} </label>
<select id="{$localPaymentName}-{$name}" class="form-control col-lg-6" {if isset($field.required) && $field.required}required{/if}  name="{$name}">
    <option value="1">{l s='Mr' mod='hipay_enterprise'}</option>
    <option value="2">{l s='Mrs' mod='hipay_enterprise'}</option>
</select>