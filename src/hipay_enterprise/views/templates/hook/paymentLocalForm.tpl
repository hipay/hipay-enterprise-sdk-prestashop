{foreach $methodFields as $name => $field}

    {if $field["type"] eq "text"}
         {include file="$hipay_enterprise_tpl_dir/front/formFieldTemplate/inputText.tpl"}
    
    {else if $field["type"] eq "gender"}
         {include file="$hipay_enterprise_tpl_dir/front/formFieldTemplate/inputGender.tpl"}
    {/if}
<br />
{/foreach}
{if !empty($methodFields)}
<input type="hidden"  name="localSubmit" />
{/if}
