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
{capture name=path}{l s='HiPay payment.' mod='hipay_enterprise'}{/capture}

<h2>{l s='Payment Summary' mod='hipay_enterprise'}</h2>
<h3>{l s='HiPay payment.' mod='hipay_enterprise'}</h3>

<p class="warning">
    {l s='We noticed a problem with your order. Please contact our ' mod='hipay_enterprise'}
    <strong><a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='customer support' mod='hipay_enterprise'}</a></strong>.
</p>
<p><a href="{$link->getPageLink('order', true)}">{l s='Back to cart' mod='hipay_enterprise'}</a></p>