{**
* 2016 HiPay
*
* NOTICE OF LICENSE
*
*
* @author    HiPay <support.wallet@hipay.com>
* @copyright 2016 HiPay
* @license   https://github.com/hipay/hipay-wallet-sdk-prestashop/blob/master/LICENSE.md
*
*}
<div class="row">
    <div class="col-xs-12 col-md-12">
        <p class="payment_module" id="hipay_payment_button">
            {if $cart->getOrderTotal() < $min_amount}
                <a href="#">
                    <img src="{$domain|cat:$payment_button|escape:'htmlall':'UTF-8'}"
                             alt="{l s='Pay by credit or debit card' mod='hipay_professional'} "
                             class="pull-left" width="234px" height="57px"/>

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

                        <img src="{$domain|cat:$payment_button|escape:'html':'UTF-8'}" alt="{l s='Pay by credit or debit card' mod='hipay_professional'}" class="" width="234px" height="57px"/>
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
