{extends "$layout"}
{block name="content"}
    <h2>{l s='Payment Summary' mod='hipay_enterprise'}</h2>
    <h3>{l s='HiPay payment.' mod='hipay_enterprise'}</h3>
    <p class="warning">
        {l s='Your order has been declined' mod='hipay_enterprise'}
    </p>
    <p><a href="{$link->getPageLink('order', true)}">{l s='Back to cart' mod='hipay_enterprise'}</a></p>
{/block}