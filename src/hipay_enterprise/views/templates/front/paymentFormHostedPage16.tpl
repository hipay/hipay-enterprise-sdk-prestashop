{capture name=path}{l s='HiPay payment.' mod='hipay_tpp'}{/capture}
<h2>{l s='Order summary' mod='hipay_tpp'}</h2>
{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}
