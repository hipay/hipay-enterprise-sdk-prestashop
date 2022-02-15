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

{if !$forceHpayment && !empty($methodFields)}

    {foreach $methodFields as $name => $field}

        {if $field["type"] eq "text"}
            {include file="$hipay_enterprise_tpl_dir/front/formFieldTemplate/$psVersion/inputText.tpl"}

        {elseif $field["type"] eq "gender"}
            {include file="$hipay_enterprise_tpl_dir/front/formFieldTemplate/$psVersion/inputGender.tpl"}
        {/if}
        <br/>
    {/foreach}
    <input type="hidden" name="localSubmit"/>
    <script>
        (function () {
            {foreach $methodFields as $name => $field}
            {if isset($field.controlType)}
            hiPayInputControl.addInput('{$localPaymentName}', '{$localPaymentName}-{$name}', '{$field.controlType}', {if isset($field.required)}{$field.required}{else}false{/if});
            {/if}
            {/foreach}
        })();

    </script>
{elseif $forceHpayment}
    {if $iframe}
        <p>{l s='Confirm your order to go to the payment page' mod='hipay_enterprise'}</p>
    {else}
        <p>{l s='You will be redirected to an external payment page. Please do not refresh the page during the process' mod='hipay_enterprise'}</p>
    {/if}
{/if}
