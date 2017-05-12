{**
* 2017 HiPay
*
* NOTICE OF LICENSE
*
*
* @author    HiPay <support.wallet@hipay.com>
* @copyright 2017 HiPay
* @license   https://github.com/hipay/hipay-wallet-sdk-prestashop/blob/master/LICENSE.md
*
*}
<div class="panel">
    <h3><i class="icon icon-credit-card"></i> {l s='Production configuration' mod='hipay_professional'}</h3>   

    {$global_payment_methods_form}


    <h3><i class="icon icon-credit-card"></i> {l s='Production configuration' mod='hipay_professional'}</h3>   

    <div role="tabpanel">
        <ul class="nav nav-tabs" role="tablist">

            {foreach $config_hipay.payment.credit_card as $creditCard}
                <li role="presentation"
                    class=" "><a
                        href="#{$creditCard@key}" aria-controls="{$creditCard@key}" role="tab" data-toggle="tab">
                         {l s=$creditCard@key mod='hipay_professional'}</a>
                </li>
            {/foreach}

        </ul>

        <div class="tab-content">
            {foreach $config_hipay.payment.credit_card as $creditCard}
                <div role="tabpanel"
                     class="tab-pane "
                     id="{$creditCard@key}">
                    <div class="panel">
                        {$creditCard@key}
                    </div>
                </div>
            {/foreach}
        </div>
    </div>
</div>
