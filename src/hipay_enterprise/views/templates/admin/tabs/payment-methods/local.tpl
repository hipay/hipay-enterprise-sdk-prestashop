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

<div class="panel hipay-tabs" id="panel-local-payment">
    <div class="form-wrapper">
        <a data-toggle="collapse" href="#collapseLocalPayment" aria-expanded="false" aria-controls="collapseLocalPayment" >
            <h3><i class="icon icon-credit-card"></i> {l s='Local payment' mod='hipay_enterprise'}<i id="chevronLocal" class="pull-right chevron icon icon-chevron-down"></i></h3>
        </a>
        <div class="collapse" id="collapseLocalPayment">
            <div role="tabpanel">
                <ul class="nav nav-pills nav-stacked col-md-2" role="tablist">
                    <li role="presentation" class="disabled"><a class="credit-card-title" href="#">
                            {l s='Local payment type' mod='hipay_enterprise'}</a>
                    </li>
                    {foreach $config_hipay.payment.local_payment as $localPayment}
                        <li role="presentation" class=" {if $localPayment@first} active {/if} ">
                            <a href="#{$localPayment@key}" aria-controls="{$localPayment@key}" role="tab"
                               data-toggle="tab">{l s=$localPayment["displayNameBO"] mod='hipay_enterprise'}</a>
                        </li>
                    {/foreach}
                </ul>
                <form method="post" class="form-horizontal" action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" id="local_payment_form">
                    <div class="tab-content col-md-10">
                        {foreach $config_hipay.payment.local_payment as $localPayment}
                            <div role="tabpanel" class="tab-pane {if $localPayment@first} active {/if}" id="{$localPayment@key}">
                                <div class="panel">
                                    <div class="row">
                                        <h4 class="col-lg-4 col-lg-offset-2">
                                            {l s=$localPayment["displayNameBO"] mod='hipay_enterprise'}
                                        </h4>
                                    </div>
                                    <div class="row">
                                        <label class="control-label col-lg-2">
                                            {l s='Activated' mod='hipay_enterprise'}
                                        </label>
                                        <div class="col-lg-9">
                                            <span class="switch prestashop-switch fixed-width-lg">
                                                <input type="radio" name="{$localPayment@key}_activated"
                                                       id="{$localPayment@key}_activated_on" value="1"
                                                       {if $localPayment.activated }checked="checked"{/if} >
                                                <label for="{$localPayment@key}_activated_on">{l s='Yes' mod='hipay_enterprise'}</label>
                                                <input type="radio" name="{$localPayment@key}_activated"
                                                       id="{$localPayment@key}_activated_off" value="0"
                                                       {if $localPayment.activated == false }checked="checked"{/if} >
                                                <label for="{$localPayment@key}_activated_off">{l s='No' mod='hipay_enterprise'}</label>
                                                <a class="slide-button btn"></a>
                                            </span>
                                        </div>
                                    </div>
                                    <!-- SWITCH MODE END -->
                                    <br/>
                                    <div class="row">
                                        <div class="form-group">
                                            <label class="control-label col-lg-2">{l s='Display name' mod='hipay_enterprise'}</label>
                                            <div class="col-lg-3">
                                                <input type="text" name="{$localPayment@key}_displayName" value="{$localPayment.displayName}"/>
                                            </div>
                                        </div>
                                    </div>
                                    <br/>
                                    <div class="row">
                                        <div class="form-group">
                                            <label class="control-label col-lg-2">{l s='Minimum order amount' mod='hipay_enterprise'}</label>
                                            <div class="col-lg-1">
                                                <input type="text" name="{$localPayment@key}_minAmount[EUR]" value="{$localPayment.minAmount.EUR}"/>
                                            </div>
                                        </div>
                                    </div>
                                    <br/>
                                    <div class="row">
                                        <div class="form-group">
                                            <label class="control-label col-lg-2">{l s='Maximum order amount' mod='hipay_enterprise'}</label>
                                            <div class="col-lg-1">
                                                <input type="text" name="{$localPayment@key}_maxAmount[EUR]" value="{$localPayment.maxAmount.EUR}"/>
                                            </div>
                                        </div>
                                    </div>
                                    <br/>
                                    {if $localPayment["currencySelectorReadOnly"]}
                                        <div class="row">
                                            <div class="form-group">
                                                <label class="control-label col-lg-2">{l s='Activated Currencies' mod='hipay_enterprise'}</label>
                                                {foreach  $localPayment["currencies"] as $currency }
                                                    {if isset($limitedCurrencies[$currency])}
                                                        <p>{$limitedCurrencies[$currency]}</p>
                                                        <input type="hidden" value="{$currency}"
                                                               name="{$localPayment@key}_currencies[]"/>
                                                    {else}
                                                        <p>{$currency}
                                                            {l s='This currency is not activated in your prestashop shop' mod='hipay_enterprise'}
                                                            <input type="hidden" value="{$currency}"
                                                                   name="{$localPayment@key}_currencies[]"/>
                                                        </p>
                                                    {/if}
                                                {/foreach}
                                            </div>
                                        </div>
                                    {else}
                                        <div class="row">
                                            <div class="form-group">
                                                <label class="control-label col-lg-2">{l s='Activated Currencies' mod='hipay_enterprise'}</label>
                                                <div class="col-lg-9">
                                                    <select id="multiselect-{$localPayment@key}" name="{$localPayment@key}_currencies[]" multiple="multiple" class="multiselect-currency">
                                                        {foreach $limitedCurrencies as $currency }
                                                            <option value="{$currency@key}" {if $currency@key|in_array:$localPayment.currencies } selected {/if} >{$currency@key} - {$currency} </option>
                                                        {/foreach}
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    {/if}

                                    {if $localPayment["countrySelectorReadOnly"]}
                                        <div class="row">
                                            <div class="form-group">
                                                <label class="control-label col-lg-2">{l s='Activated Countries' mod='hipay_enterprise'}</label>
                                                {foreach  $localPayment["countries"] as $country }
                                                    <label class="col-lg-2">{$limitedCountries[$country]}</label>
                                                    <input type="hidden" readonly value="{$country}"
                                                           name="{$localPayment@key}_countries[]"/>
                                                {/foreach}
                                            </div>
                                        </div>
                                    {else}
                                        <div class="row">
                                            <div class="form-group">
                                                <label class="control-label col-lg-2">{l s='Countries' mod='hipay_enterprise'}</label>
                                                <div class="col-lg-6">
                                                    <select id="countries_{$localPayment@key}" multiple="multiple" size="10"
                                                            name="{$localPayment@key}_countries[]">
                                                        {foreach $limitedCountries as $country}
                                                           <option value="{$country@key}" {if !empty($localPayment.countries) && $country@key|in_array:$localPayment.countries } selected {/if} >{$country}</option>
                                                        {/foreach}
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    {/if}
                                </div>
                            </div>
                        {/foreach}
                    </div>
                    <span style="clear: left;display: block;" ></span>
                    <div class="panel-footer">
                        <div class="col-md-12 col-xs-12">
                            <button type="submit" class="btn btn-default pull-left" name="submitCancel"><i
                                    class="process-icon-eraser"></i>{l s='Discard changes' mod='hipay_enterprise'}
                            </button>
                            <button type="submit" class="btn btn-default btn btn-default pull-right"
                                    name="localPaymentSubmit">
                                <i class="process-icon-save"></i>{l s='Save configuration changes' mod='hipay_enterprise'}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    {foreach $config_hipay.payment.local_payment as $localPayment}
        {if !$localPayment["countrySelectorReadOnly"]}
    var local_{$localPayment@key|regex_replace:'/[^a-zA-Z0-9]/':""}_dualistbox = $('#countries_{$localPayment@key}').bootstrapDualListbox({
        showFilterInputs: false,
        moveOnSelect: false,
        nonSelectedListLabel: '{l s='Available countries' mod='hipay_enterprise'}',
        selectedListLabel: '{l s='Authorized countries' mod='hipay_enterprise'}',
        infoText: false
    });
        {/if}
    {/foreach}
</script>