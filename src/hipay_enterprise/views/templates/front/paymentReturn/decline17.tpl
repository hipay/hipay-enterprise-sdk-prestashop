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
{extends "$layout"}
{block name="content"}
    <h2>{l s='Payment Summary' mod='hipay_enterprise'}</h2>
    <h3>{l s='HiPay payment.' mod='hipay_enterprise'}</h3>
    <p class="warning">
        {l s='Your order has been declined' mod='hipay_enterprise'}
    </p>
    <p><a href="{$link->getPageLink('order', true)}">{l s='Back to cart' mod='hipay_enterprise'}</a></p>
{/block}