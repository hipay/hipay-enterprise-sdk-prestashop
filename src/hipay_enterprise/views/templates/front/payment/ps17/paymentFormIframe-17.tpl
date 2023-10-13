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
    <section>
        <iframe sandbox="allow-modals allow-top-navigation allow-same-origin allow-scripts allow-forms"
            src="{$HiPay_url|escape:'htmlall':'UTF-8'}" width="100%" height="650"></iframe>
    </section>
{/block}