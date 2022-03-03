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
{if isset($paymentProduct.displayName[$lang])}
    {assign var="productName" value=$paymentProduct.displayName[$lang]}
{elseif isset($paymentProduct.displayName) && !is_array($paymentProduct.displayName)}
    {assign var="productName" value=$paymentProduct.displayName}
{else}
    {assign var="productName" value=reset($paymentProduct.displayName)}
{/if}

<div class="row">
    <div class="col-xs-12 col-md-12">
        <form class="localpayment" method="post" action="{$paymentProduct.link}">
            <p class="payment_module" id="hipay_payment_button">
                <a href="javascript:void(0);" onclick="{literal}$(this).closest('form').submit();{/literal}"
                   title="{l s='Pay by ' mod='hipay_enterprise' } {$productName}"
                   style="padding-left: 13px;">
                    <img src="{$domain|cat:$paymentProduct.payment_button|escape:'html':'UTF-8'}"
                         style="max-width: 160px;max-height: 50px;" alt="{$productName}"/>
                    {l s='Pay by ' mod='hipay_enterprise' } {$productName}

                    <span>
                        {if (empty($paymentProduct.additionalFields) && (isset($paymentProduct.forceHpayment) && $paymentProduct.forceHpayment)) || ((isset($paymentProduct.handleHpayment) && $paymentProduct.handleHpayment && $isOperatingModeHostedPage))}
                            {if !(isset($paymentProduct.iframe) && $paymentProduct.iframe)}
                                <em>{l s='You will be redirected to an external payment page. Please do not refresh the page during the process' mod='hipay_enterprise'}</em>
                            {/if}
                        {/if}
                        {if isset($hipay_prod) && (!$hipay_prod)}
                            <em>{l s='(sandbox / test mode)' mod='hipay_enterprise'}</em>
                        {/if}
                    </span>
                </a>
                {if isset($paymentProduct.errorMsg) && !empty($paymentProduct.errorMsg)}
            <div id="error-js-oc" style="" class="alert alert-danger">
                <ol>
                    {foreach $paymentProduct.errorMsg as $msg}
                        <li class="error-oc">{$msg}</li>
                    {/foreach}
                </ol>
            </div>
            {/if}
            </p>
            <input id="ioBB_{$paymentProduct@key}" class="ioBB" type="hidden" name="ioBB">
        </form>
    </div>
</div>
