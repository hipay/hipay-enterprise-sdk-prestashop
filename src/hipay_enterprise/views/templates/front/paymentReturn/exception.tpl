
{capture name=path}{l s='HiPay payment.' mod='hipay_tpp'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{l s='Payment Summary' mod='hipay_tpp'}</h2>

<h3>{l s='HiPay payment.' mod='hipay_tpp'}</h3>

<p class="warning">
    {l s='We noticed a problem with your order. Please contact our ' mod='hipay_tpp'} 
    <strong><a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='customer support' mod='hipay_tpp'}</a></strong>.
</p>
<p><a href="{$link->getPageLink('order', true)}">{l s='Back to cart' mod='hipay_tpp'}</a></p>