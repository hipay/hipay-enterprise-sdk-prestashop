<div class="row">
    <div class="col-xs-12 col-md-12">
        <p class="payment_module" id="hipay_payment_button">
            <a href="{$link->getModuleLink('hipay_enterprise', 'redirect', array(), true)|escape:'htmlall':'UTF-8'}"
               title="{l s='Pay by credit card' mod='hipay_enterprise' }">

                <img src="{$domain|cat:$payment_button|escape:'html':'UTF-8'}"
                     style="max-width: 70px; max-height: 40px;"
                     alt="{l s='Pay by credit card' mod='hipay_enterprise'}"/>
                {l s='Pay by ' mod='hipay_enterprise' }{$configHipay.payment.global.ccDisplayName}
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