{capture name=path}{l s='HiPay payment.' mod='hipay_tpp'}{/capture}
<h2>{l s='Order summary' mod='hipay_tpp'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if $nbProducts <= 0}
    <p class="warning">{l s='Your shopping cart is empty.' mod='hipay_tpp'}</p>
{else}
    <h3>{l s='HiPay payment.' mod='hipay_professional'}</h3>
    <section>
        <iframe src="{$url|escape:'html':'UTF-8'}" width="100%" height="650"></iframe>
    </section>
{/if}
