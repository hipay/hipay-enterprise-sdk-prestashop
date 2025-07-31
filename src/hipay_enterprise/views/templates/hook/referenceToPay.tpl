{**
 * HiPay Enterprise SDK Prestashop
 *
 * 2019 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright 2019 HiPay
 * @license   https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 *}

{* Include SDK script with SRI support *}
{include file="$hipay_enterprise_tpl_dir/front/partial/sdk-script.tpl"}
<section id="hipay-order-details" class="box">
    <h3>{l s='HIPAY DETAILS' mod='hipay_enterprise'}</h3>
    <div class="referenceContainer">
        <div id="referenceToPay"></div>
    </div>
</section>
<script type="text/javascript">
    window.onload = function() {
        var referenceToPay = JSON.parse('{$HiPay_referenceToPay nofilter}');
        var lang = '{$HiPay_lang}';
        var hipaySdk = new HiPay({
            username: 'hosted',
            password: 'hosted',
            environment: 'production',
            lang
        });

        {if $HiPay_method === 'multibanco'}
            hipaySdk.createReference('multibanco', {
                selector: 'referenceToPay',
                reference: referenceToPay.reference,
                entity: referenceToPay.entity,
                amount: referenceToPay.amount,
                expirationDate: referenceToPay.expirationDate,
            });
        {else}
            hipaySdk.createReference('sisal', {
                selector: 'referenceToPay',
                reference: referenceToPay.reference,
                barCode: referenceToPay.barCode
            });
        {/if}
    }
</script>