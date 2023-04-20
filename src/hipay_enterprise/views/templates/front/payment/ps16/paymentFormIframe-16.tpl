{**
 * HiPay Enterprise SDK Prestashop
 *
 * 2017 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright 2017 HiPay
 * @license   https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 *}
{capture name=path}{l s='HiPay payment.' mod='hipay_enterprise'}{/capture}
<h2>{l s='Order summary' mod='hipay_enterprise'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if $nbProducts <= 0}
    <p class="warning">{l s='Your shopping cart is empty.' mod='hipay_enterprise'}</p>
{else}
    <h3>{l s='HiPay payment.' mod='hipay_enterprise'}</h3>
    <section>
        <iframe sandbox="allow-modals allow-top-navigation allow-same-origin allow-scripts allow-forms"
            src="{$url|escape:'htmlall':'UTF-8'}" width="100%" height="650"></iframe>
    </section>
{/if}