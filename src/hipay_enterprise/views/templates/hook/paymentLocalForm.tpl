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
{foreach $methodFields as $name => $field}

    {if $field["type"] eq "text"}
        {include file="$hipay_enterprise_tpl_dir/front/formFieldTemplate/inputText.tpl"}

    {else if $field["type"] eq "gender"}
        {include file="$hipay_enterprise_tpl_dir/front/formFieldTemplate/inputGender.tpl"}
    {/if}
    <br/>
{/foreach}
{if !empty($methodFields)}
    <input type="hidden" name="localSubmit"/>
{/if}
