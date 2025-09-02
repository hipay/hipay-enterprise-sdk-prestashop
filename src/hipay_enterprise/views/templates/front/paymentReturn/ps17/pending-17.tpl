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

    {$HiPay_sdk_script_tag nofilter}

    <h2>{l s='Payment Summary' mod='hipay_enterprise'}</h2>
    <h3>{l s='HiPay payment.' mod='hipay_enterprise'}</h3>

    {if {isset($smarty.get.referenceToPay) and $smarty.get.referenceToPay}}
        <style>
            @media print {
                body * {
                    visibility: hidden;
                }

                #referenceToPay,
                #referenceToPay * {
                    visibility: visible;
                }
            }
        </style>
        <div class="referenceContainer">
            <div id="referenceToPay"></div>
            <button class="btn btn-primary print" onclick="window.print()">{l s='PRINT' mod='hipay_enterprise'}</button>
        </div>
        <script type="text/javascript">
            window.onload = function() {
                var lang = '{$language.locale}';
                var hipaySdk = new HiPay({
                    username: 'hosted',
                    password: 'hosted',
                    environment: 'production',
                    lang: lang.length > 2 ? lang.substr(0, 2) : 'en'
                });
                {if $smarty.get.method === 'multibanco' }
                    hipaySdk.createReference('multibanco', {
                        selector: 'referenceToPay',
                        reference: '{$smarty.get.reference}',
                        entity: '{$smarty.get.entity}',
                        amount: '{$smarty.get.amount}',
                        expirationDate: '{$smarty.get.expirationDate}',
                    });
                {else}
                    hipaySdk.createReference('sisal', {
                        selector: 'referenceToPay',
                        reference: '{$smarty.get.reference}',
                        barCode: '{$smarty.get.barCode}'
                    });
                {/if}
            }
        </script>
    {else}
        <p>{l s='Your order is awaiting confirmation from the bank.' mod='hipay_enterprise'}
            <br /><br />{l s='Once it is approved it will be available in your' mod='hipay_enterprise'} <strong><a
                    href="{$link->getPageLink('history', true)}">{l s='order history' mod='hipay_enterprise'}</a></strong>
        </p>
    {/if}

    <p><a href="index.php">{l s='Back to home' mod='hipay_enterprise'}</a></p>
{/block}