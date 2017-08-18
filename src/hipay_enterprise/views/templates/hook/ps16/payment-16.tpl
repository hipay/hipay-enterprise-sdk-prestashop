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

{if !empty($sortedPaymentProducts)}
    <input id="ioBB" type="hidden" name="ioBB">
    {foreach $sortedPaymentProducts as $name => $paymentProduct}
        {if $name == "credit_card"}
           {include file="../../front/partial/cc.paymentoption.tpl"}
        {else}    
            {include file="../../front/partial/local.paymentoption.tpl"}
        {/if}
    {/foreach}
{/if}    

<script>
    // add device fincgerprint before sending local payment form
    $(".localpayment").submit(function () {
        $(".ioBB").val($("#ioBB").val());
    });
</script>