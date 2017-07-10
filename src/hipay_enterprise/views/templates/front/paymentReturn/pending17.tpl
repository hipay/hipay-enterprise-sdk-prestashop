{extends "$layout"}
{block name="content"}
    <h2>{l s='Payment Summary' mod='hipay_tpp'}</h2>
    <h3>{l s='HiPay payment.' mod='hipay_tpp'}</h3>
    <p>{l s='Your order is awaiting confirmation from the bank.' mod='hipay_tpp'}
        <br/><br/>{l s='Once it is approved it will be available in your' mod='hipay_tpp'} <strong><a
                    href="{$link->getPageLink('history', true)}">{l s='order history' mod='hipay_tpp'}</a></strong>
    </p>
    <p><a href="index.php">{l s='Back to home' mod='hipay_tpp'}</a></p>
{/block}