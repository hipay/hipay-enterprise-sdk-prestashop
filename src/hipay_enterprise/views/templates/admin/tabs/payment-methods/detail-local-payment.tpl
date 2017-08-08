<div role="tabpanel" class="tab-pane fade in {if $first} active {/if}" id="{$key}">
    <div class="panel">
        <div class="row">
            <h4 class="col-lg-5 col-lg-offset-2">
                {l s=$method["displayNameBO"] mod='hipay_enterprise'}
            </h4>
        </div>
        <div class="row">
            <label class="control-label col-lg-2">
                {l s='Activated' mod='hipay_enterprise'}
            </label>
            <div class="col-lg-9">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input type="radio" name="{$key}_activated"
                           id="{$key}_activated_on" value="1"
                           {if $method.activated }checked="checked"{/if} >
                    <label for="{$key}_activated_on">{l s='Yes' mod='hipay_enterprise'}</label>
                    <input type="radio" name="{$key}_activated"
                           id="{$key}_activated_off" value="0"
                           {if $method.activated == false }checked="checked"{/if} >
                    <label for="{$key}_activated_off">{l s='No' mod='hipay_enterprise'}</label>
                    <a class="slide-button btn"></a>
                </span>
            </div>
        </div>
        <br/>
        <div class="row">
            <div class="form-group">
                <label class="control-label col-lg-2">{l s='Display name' mod='hipay_enterprise'}</label>
                <div class="col-lg-3">
                    <input type="text" name="{$key}_displayName" value="{$method.displayName}"/>
                </div>
            </div>
        </div>
        <br/>
        <div class="row">
            <div class="form-group">
                <label class="control-label col-lg-2">{l s='Front positioning' mod='hipay_enterprise'}</label>
                <div class="col-lg-3">
                    <input type="text" name="{$key}_frontPosition" value="{$method.frontPosition}"/>
                </div>
            </div>
        </div>
        <br/>
        <div class="row">
            <div class="form-group">
                <label class="control-label col-lg-2">{l s='Minimum order amount' mod='hipay_enterprise'}</label>
                <div class="col-lg-1">
                    <input type="text" name="{$key}_minAmount[EUR]" value="{$method.minAmount.EUR}"/>
                </div>
            </div>
        </div>
        <br/>
        <div class="row">
            <div class="form-group">
                <label class="control-label col-lg-2">{l s='Maximum order amount' mod='hipay_enterprise'}</label>
                <div class="col-lg-1">
                    <input type="text" name="{$key}_maxAmount[EUR]" value="{$method.maxAmount.EUR}"/>
                </div>
            </div>
        </div>
        <br/>
        {if $method["currencySelectorReadOnly"]}
            <div class="row">
                <div class="form-group">
                    <label class="control-label col-lg-2">{l s='Activated Currencies' mod='hipay_enterprise'}</label>
                    {foreach  $method["currencies"] as $currency }
                        {if isset($limitedCurrencies[$currency])}
                            <span class="label-value col-lg-2">{$limitedCurrencies[$currency]}</span>
                            <input type="hidden" value="{$currency}"
                                   name="{$key}_currencies[]"/>
                        {else}
                            <span class="label-value col-lg-2">{$currency}
                                {l s='This currency is not activated in your prestashop shop' mod='hipay_enterprise'}
                            </span>
                            <input type="hidden" value="{$currency}"
                                   name="{$key}_currencies[]"/>
                        {/if}
                    {/foreach}
                </div>
            </div>
        {else}
            <div class="row">
                <div class="form-group">
                    <label class="control-label col-lg-2">{l s='Activated Currencies' mod='hipay_enterprise'}</label>
                    <div class="col-lg-9">
                        <select id="multiselect-{$key}" name="{$key}_currencies[]" multiple="multiple" class="multiselect-currency">
                            {foreach $limitedCurrencies as $currency }
                                <option value="{$currency@key}" {if $currency@key|in_array:$method.currencies } selected {/if} >{$currency@key} - {$currency} </option>
                            {/foreach}
                        </select>
                    </div>
                </div>
            </div>
        {/if}

        {if $method["countrySelectorReadOnly"]}
            <div class="row">
                <div class="form-group">
                    <label class="control-label col-lg-2">{l s='Activated Countries' mod='hipay_enterprise'}</label>
                    {foreach  $method["countries"] as $country }
                        {if isset($limitedCountries[$country])}
                            <span class="col-lg-2 label-value">{$limitedCountries[$country]}</span>
                            <input type="hidden" readonly value="{$country}"
                                   name="{$key}_countries[]"/>
                        {else}
                            <span class="label-value col-lg-4">{$country}
                                {l s='This country is not activated in your prestashop shop' mod='hipay_enterprise'}
                            </span>
                            <input type="hidden" value="{$country}"
                                   name="{$key}_countries[]"/>
                        {/if}
                    {/foreach}
                </div>
            </div>
        {else}
            <div class="row">
                <div class="form-group">
                    <label class="control-label col-lg-2">{l s='Countries' mod='hipay_enterprise'}</label>
                    <div class="col-lg-6">
                        <select id="countries_{$key}" multiple="multiple" size="10"
                                name="{$key}_countries[]">
                            {foreach $limitedCountries as $country}
                                <option value="{$country@key}" {if !empty($method.countries) && $country@key|in_array:$method.countries } selected {/if} >{$country}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
            </div>
        {/if}
    </div>
</div>