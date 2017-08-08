<div class="row">
    <div class="col-xs-12 col-md-12">
        <form class="localpayment" method="post" action="{$paymentProduct.link}">
            <p class="payment_module" id="hipay_payment_button">
                <a href="javascript:void(0);" onclick="{literal}$(this).closest('form').submit();{/literal}"
                   title="{l s='Pay by ' mod='hipay_enterprise' } {$paymentProduct.displayName}">
                    <img src="{$domain|cat:$paymentProduct.payment_button|escape:'html':'UTF-8'}"
                         style="max-width: 70px; max-height: 40px;" alt="{$paymentProduct.displayName}"/>
                    {l s='Pay by' mod='hipay_enterprise' } {$paymentProduct.displayName}
                    <span>
                        {if $configHipay.payment.global.operating_mode != 'api'}
                            <em>{l s='You will be redirected to an external payment page. Please do not refresh the page during the process' mod='hipay_enterprise'}</em>
                        {/if}
                        {if isset($hipay_prod) && (!$hipay_prod)}
                            <em>{l s='(sandbox / test mode)' mod='hipay_enterprise'}</em>
                        {/if}
                    </span>
                </a>
            </p>
            <input id="ioBB_{$paymentProduct@key}" class="ioBB" type="hidden" name="ioBB">
        </form>
    </div>
</div>