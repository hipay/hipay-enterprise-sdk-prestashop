<h3><i class="icon icon-credit-card"></i> {l s='Local payment' mod='hipay_professional'}</h3>   

<div role="tabpanel">
    <ul class="nav nav-tabs" role="tablist">
        {foreach $config_hipay.payment.local_payment as $localPayment}
            <li role="presentation" class=" {if $localPayment@first} active {/if} ">
                <a href="#{$localPayment@key}" aria-controls="{$localPayment@key}" role="tab" data-toggle="tab">{l s=$localPayment["displayName"] mod='hipay_professional'}</a>
            </li>
        {/foreach}
    </ul>
    <form method="post" class="" action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" id="local_payment_form">
        <div class="tab-content">
            {foreach $config_hipay.payment.local_payment as $localPayment}
                <div role="tabpanel" class="tab-pane {if $localPayment@first} active {/if}" id="{$localPayment@key}">
                    <div class="panel">

                        <!-- SWITCH MODE START -->
                        <div class="row">
                            <label  class="control-label col-lg-3">
                                {l s='Activated' mod='hipay_professional'}
                            </label>
                            <div class="col-lg-9">
                                <span class="switch prestashop-switch fixed-width-lg">
                                    <input type="radio" name="{$localPayment@key}_activated" id="{$localPayment@key}_activated_on" value="1"
                                           {if $localPayment.activated }checked="checked"{/if}   >
                                    <label for="{$localPayment@key}_activated_on">{l s='Yes' mod='hipay_professional'}</label>
                                    <input type="radio" name="{$localPayment@key}_activated" id="{$localPayment@key}_activated_off" value="0"
                                           {if $localPayment.activated == false }checked="checked"{/if}  >
                                    <label for="{$localPayment@key}_activated_off">{l s='No' mod='hipay_professional'}</label>
                                    <a class="slide-button btn"></a>
                                </span>
                            </div>
                        </div>
                        <!-- SWITCH MODE END -->
                        <br />
                        {if $localPayment["currencySelectorReadOnly"]}
                            <div class="row">
                                <div class="form-group">
                                    <label class="control-label col-lg-3">{l s='Activated Currencies' mod='hipay_professional'}</label>
                                    {foreach  $localPayment["currencies"] as $currency }
                                        <p>{$limitedCurrencies[$currency]}</p>
                                        <input type="hidden" value="{$currency}" name="{$localPayment@key}_currencies[]" />
                                    {/foreach}
                                </div>
                            </div>

                        {else}
                            <div class="row">
                                <div class="form-group">
                                    <label class="control-label col-lg-3" >{l s='Activated Currencies' mod='hipay_professional'}</label>
                                    <div class="col-lg-9">
                                        {foreach $limitedCurrencies as $currency }
                                            <label class="control-label col-lg-2"> 
                                                <input type="checkbox" name="{$localPayment@key}_currencies[]" {if $currency@key|in_array:$localPayment.currencies } checked {/if} value="{$currency@key}" />
                                                <br/>
                                                {$currency@key}
                                                <br/>
                                                {$currency}
                                            </label>
                                        {/foreach}
                                    </div>
                                </div>
                            </div>
                        {/if}

                        {if $localPayment["countrySelectorReadOnly"]}
                            <div class="row">
                                <div class="form-group">
                                    <label class="control-label col-lg-3" >{l s='Activated Countries' mod='hipay_professional'}</label>
                                    {foreach  $localPayment["countries"] as $country }
                                        <p>{$limitedCountries[$country]}</p>
                                        <input type="hidden" readonly value="{$country}" name="{$localPayment@key}_countries[]" />
                                    {/foreach}
                                </div>    
                            </div>
                        {else}
                            <div class="row">
                                <div class="form-group">
                                    <select id="countries_{$localPayment@key}" multiple="multiple" size="10" name="{$localPayment@key}_countries[]" >
                                        {foreach $limitedCountries as $country}
                                            <option value="{$country@key}" {if $country@key|in_array:$localPayment.countries } selected {/if}  >{$country}</option>
                                        {/foreach}
                                    </select>
                                </div>    
                            </div>
                        {/if}
                        <div class="row">
                            <div class="col-md-12 col-xs-12">
                                <button type="submit" class="btn btn-default pull-left" name="submitCancel"><i
                                        class="process-icon-eraser"></i>{l s='Discard changes' mod='hipay_professional'}
                                </button>
                                <button type="submit" class="btn btn-default btn btn-default pull-right" name="localPaymentSubmit">
                                    <i class="process-icon-save"></i>{l s='Save configuration changes' mod='hipay_professional'}
                                </button>
                            </div>
                        </div>        
                    </div>
                </div>
            {/foreach}
        </div>
    </form>
</div>

<script>
    {foreach $config_hipay.payment.local_payment as $localPayment}
        {if !$localPayment["countrySelectorReadOnly"]}
    var local_{$localPayment@key|regex_replace:'/[^a-zA-Z0-9]/':""}_dualistbox = $('#countries_{$localPayment@key}').bootstrapDualListbox({
        showFilterInputs: false,
        moveOnSelect: false,
        nonSelectedListLabel: '{l s='Available countries' mod='hipay_professional'}',
        selectedListLabel: '{l s='Authorized countries' mod='hipay_professional'}',
        infoText: false
    });
        {/if}
    {/foreach}
</script>