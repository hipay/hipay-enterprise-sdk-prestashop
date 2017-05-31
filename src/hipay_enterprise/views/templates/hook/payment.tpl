{**
* 2017 HiPay
*
* NOTICE OF LICENSE
*
*
* @author    HiPay <support.wallet@hipay.com>
* @copyright 2016 HiPay
* @license   https://github.com/hipay/hipay-wallet-sdk-prestashop/blob/master/LICENSE.md
*
*}

{if !empty($activated_credit_card)}
    <div class="row">
        <div class="col-xs-12 col-md-12">
            <p class="payment_module" id="hipay_payment_button">
                {if $cart->getOrderTotal() < $min_amount}
                    <a href="#">
                        <img src="{$domain|cat:$payment_button|escape:'htmlall':'UTF-8'}"
                             alt="{l s='Pay by credit or debit card' mod='hipay_professional'} "
                             />

                        <span>
                            {l s='Pay by credit or debit card' mod='hipay_professional' }
                            {l s='Minimum amount required in order to pay by credit card:' mod='hipay_professional' } {convertPrice price=$min_amount}
                            {if isset($hipay_prod) && (!$hipay_prod)}
                                <em>{l s='(sandbox / test mode)' mod='hipay_professional'}</em>
                            {/if}
                        </span>
                    </a>
                {else}
                    <a href="{$link->getModuleLink('hipay_enterprise', 'redirect', array(), true)|escape:'htmlall':'UTF-8'}"
                       title="{l s='Pay by credit or debit card' mod='hipay_professional' }">

                        <img src="{$domain|cat:$payment_button|escape:'html':'UTF-8'}" alt="{l s='Pay by credit or debit card' mod='hipay_professional'}" />
                        {l s='Pay by credit or debit card' mod='hipay_professional' }
                        <span>

                            {if isset($hipay_prod) && (!$hipay_prod)}
                                <em>{l s='(sandbox / test mode)' mod='hipay_professional'}</em>
                            {/if}
                        </span>
                    </a>
                {/if}
            </p>
        </div>
    </div>
{/if}
<input id="ioBB" type="hidden" name="ioBB">
{if !empty($activated_local_payment)}
    {foreach $activated_local_payment as $name => $local_payment}
        {if $cart->getOrderTotal() < $min_amount}
            <div class="row">
                <div class="col-xs-12 col-md-12">
                    <p class="payment_module" id="hipay_payment_button">
                        <a href="#">
                            <img src="{$domain|cat:$local_payment.payment_button|escape:'html':'UTF-8'}" alt="{$local_payment.displayName}" class="" />
                            <span>
                                {l s='Pay by credit or debit card' mod='hipay_professional' }
                                {l s='Minimum amount required in order to pay by credit card:' mod='hipay_professional' } {convertPrice price=$min_amount}
                                {if isset($hipay_prod) && (!$hipay_prod)}
                                    <em>{l s='(sandbox / test mode)' mod='hipay_professional'}</em>
                                {/if}
                            </span>
                        </a>
                    </p>
                </div>
            </div>
        {else}
            <div class="row">
                <div class="col-xs-12 col-md-12">
                    <form class="localpayment" method="post" action="{$local_payment.link}">
                        <p class="payment_module" id="hipay_payment_button">
                            <a href="javascript:void(0);" onclick="{literal}$(this).closest('form').submit();{/literal}" title="{l s='Pay by ' mod='hipay_professional' } {$local_payment.displayName}">
                                <img src="{$domain|cat:$local_payment.payment_button|escape:'html':'UTF-8'}" alt="{$local_payment.displayName}"  />
                                {l s='Pay by' mod='hipay_professional' } {$local_payment.displayName}
                                <span>
                                    {if isset($hipay_prod) && (!$hipay_prod)}
                                        <em>{l s='(sandbox / test mode)' mod='hipay_professional'}</em>
                                    {/if}
                                </span>
                            </a>
                        </p>
                        <input id="ioBB_{$local_payment@key}" class="ioBB" type="hidden" name="ioBB">
                    </form>
                </div>
            </div>
        {/if}
    {/foreach}
{/if}
<script>
    // add device fincgerprint before sending local payment form
    $(".localpayment").submit(function () {
        $(".ioBB").val($("#ioBB").val());
    });
</script>