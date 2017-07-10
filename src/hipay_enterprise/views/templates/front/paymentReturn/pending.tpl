{capture name=path}{l s='HiPay payment.' mod='hipay_enterprise'}{/capture}

<h2>{l s='Payment Summary' mod='hipay_enterprise'}</h2>

<h3>{l s='HiPay payment.' mod='hipay_enterprise'}</h3>

<p>{l s='Your order is awaiting confirmation from the bank.' mod='hipay_enterprise'}
    <br/><br/>{l s='Once it is approved it will be available in your' mod='hipay_enterprise'} <strong><a
                href="{$link->getPageLink('history', true)}">{l s='order history' mod='hipay_enterprise'}</a></strong>
</p>
<p><a href="index.php">{l s='Back to home' mod='hipay_enterprise'}</a></p>