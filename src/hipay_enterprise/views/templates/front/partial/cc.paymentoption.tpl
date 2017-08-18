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
<div class="row">
    <div class="col-xs-12 col-md-12">
        <p class="payment_module" id="hipay_payment_button">
            <a href="{$link->getModuleLink('hipay_enterprise', 'redirect', array(), true)|escape:'htmlall':'UTF-8'}"
               title="{l s='Pay by credit card' mod='hipay_enterprise' }" style="padding-left: 13px;">

                <img src="{$domain|cat:$payment_button|escape:'html':'UTF-8'}"
                     style="max-width: 160px;max-height: 50px;"
                     alt="{l s='Pay by credit card' mod='hipay_enterprise'}"/>
                {l s='Pay by ' mod='hipay_enterprise' } {$configHipay.payment.global.ccDisplayName}
                <span>
                    {if $configHipay.payment.global.operating_mode != 'api'}
                        <em>{l s='You will be redirected to an external payment page. Please do not refresh the page during the process' mod='hipay_enterprise'}</em>
                    {/if}
                    {if isset($hipay_prod) && (!$hipay_prod)}
                        <em>{l s='(Test mode)' mod='hipay_enterprise'}</em>
                    {/if}
                </span>
            </a>
        </p>
    </div>
</div>