{extends file='customer/page.tpl'}

{block name='page_title'}
    {l s='Manage saved credit/debit card' d='hipay_enterprise'}
{/block}

{block name='page_content'}
    <h6>{l s='Saved credit/debit card' d='hipay_enterprise'}</h6>

    {foreach $savedCC as $cc}
        <p>{$cc.pan}</p>
    {/foreach}

{/block}