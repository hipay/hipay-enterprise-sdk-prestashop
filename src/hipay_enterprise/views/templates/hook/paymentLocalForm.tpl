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
{if !empty($methodFields)}

    {foreach $methodFields as $name => $field}

        {if $field["type"] eq "text"}
            {include file="$hipay_enterprise_tpl_dir/front/formFieldTemplate/$psVersion/inputText.tpl"}

        {else if $field["type"] eq "gender"}
            {include file="$hipay_enterprise_tpl_dir/front/formFieldTemplate/$psVersion/inputGender.tpl"}
        {/if}
        <br/>
    {/foreach}

    <input type="hidden" name="localSubmit"/>
    <script>
        var inputControl = {literal}{}{/literal};
        {foreach $methodFields as $name => $field}
            {if isset($field.controlType)}
                inputControl['{$name}'] = '{$field.controlType}';
            {/if}
        {/foreach}
        console.log(inputControl);
    </script>
{/if}