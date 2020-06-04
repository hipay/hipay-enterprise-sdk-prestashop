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
<div role="tabpanel" class="tab-pane fade in {if $first} active {/if}" id="{$key}">
    <div class="panel">
        <div class="row">
            <h4 class="col-lg-5 col-lg-offset-2">
                {l s=$method["displayNameBO"] mod='hipay_enterprise'}
            </h4>
        </div>
        <div class="row">
            <div class="form-group">
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
        </div>
        {if "displayName"|in_array:$method.displayConfigurationFields}
            <div class="row">
                <div class="form-group">
                    <label class="control-label col-lg-2">{l s='Display name' mod='hipay_enterprise'}</label>

                    {foreach from=$languages item=language key=id}
                        <div class="col-lg-3 {if $languages|count > 1} translatable-field lang-{$language.iso_code} {/if}" {if $id > 0}style="display: none" {/if}>

                            <input type="text" name="{$key}_displayName[{$language.iso_code}]"
                                   class="translatable-field lang-{$language.iso_code}"
                                    {if isset($method.displayName[$language.iso_code])}
                                        value="{$method.displayName[$language.iso_code]}"
                                    {elseif isset($method.displayName) && !is_array($method.displayName)}
                                        value="{$method.displayName}"
                                    {else}
                                        value="{reset($method.displayName)}"
                                    {/if}
                            />
                        </div>
                        {if $languages|count > 1}
                            <div class="col-lg-2 translatable-field lang-{$language.iso_code} " {if $id > 0}style="display: none" {/if}>
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
                                        tabindex="-1">
                                    {$language.iso_code}
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu">
                                    {foreach from=$languages item=language}
                                        <li>
                                            <a href="javascript:selectLanguageHipay('{$language.iso_code}');">{$language.name}</a>
                                        </li>
                                    {/foreach}
                                </ul>
                            </div>
                        {/if}
                    {/foreach}

                </div>
            </div>
        {/if}
        <div class="row">
            <div class="form-group">
                <label class="control-label col-lg-2">{l s='Front positioning' mod='hipay_enterprise'}</label>
                <div class="col-lg-1" style='width:45px;'>
                    <input type="text" class="money-type" name="{$key}_frontPosition" value="{$method.frontPosition}"/>
                </div>
            </div>
        </div>
        <br/>
        {if "iframe"|in_array:$method.displayConfigurationFields}
            <div class="row">
                <div class="form-group">
                    <label class="control-label col-lg-2">
                        {l s='Iframe' mod='hipay_enterprise'}
                    </label>
                    <div class="col-lg-9">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" name="{$key}_iframe"
                               id="{$key}_iframe_on" value="1"
                               {if $method.iframe }checked="checked"{/if} >
                        <label for="{$key}_iframe_on">{l s='Yes' mod='hipay_enterprise'}</label>
                        <input type="radio" name="{$key}_iframe"
                               id="{$key}_iframe_off" value="0"
                               {if $method.iframe == false }checked="checked"{/if} >
                        <label for="{$key}_iframe_off">{l s='No' mod='hipay_enterprise'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                    </div>
                </div>
            </div>
        {/if}
        {if "minAmount"|in_array:$method.displayConfigurationFields}
            <div class="row">
                <div class="form-group">
                    <label class="control-label col-lg-2">{l s='Minimum order amount' mod='hipay_enterprise'}</label>
                    <div class="input-group col-lg-2">
                        <input type="text" class="money-type" name="{$key}_minAmount[EUR]"
                               value="{$method.minAmount.EUR}"/>
                        <span class="input-group-addon">{Currency::getDefaultCurrency()->sign}</span>
                    </div>
                </div>
            </div>
        {/if}
        {if "maxAmount"|in_array:$method.displayConfigurationFields}
            <div class="row">
                <div class="form-group">
                    <label class="control-label col-lg-2">{l s='Maximum order amount' mod='hipay_enterprise'}</label>
                    <div class="input-group col-lg-2">
                        <input type="text" class="money-type" name="{$key}_maxAmount[EUR]"
                               value="{$method.maxAmount.EUR}"/>
                        <span class="input-group-addon">{Currency::getDefaultCurrency()->sign}</span>
                    </div>
                </div>
            </div>
        {/if}
        {if "orderExpirationTime"|in_array:$method.displayConfigurationFields}
            <div class="row">
                <div class="form-group">
                    <label class="control-label col-lg-2">{l s='Order expiration date' mod='hipay_enterprise'}</label>
                    <div class="input-group col-lg-2">
                        <select name="{$key}_orderExpirationTime">
                            <option value="3" {if isset($method.orderExpirationTime) && $method.orderExpirationTime == "3"}selected="selected"{/if}>{l s='3 days' mod='hipay_enterprise'}</option>
                            <option value="30" {if isset($method.orderExpirationTime) && $method.orderExpirationTime == "30"}selected="selected"{/if}>{l s='30 days' mod='hipay_enterprise'}</option>
                            <option value="90" {if isset($method.orderExpirationTime) && $method.orderExpirationTime == "90"}selected="selected"{/if}>{l s='90 days' mod='hipay_enterprise'}</option>
                        </select>
                    </div>
                </div>
            </div>
        {/if}
        {if "merchantPromotion"|in_array:$method.displayConfigurationFields}
            <div class="row">
                <div class="form-group">
                    <label class="control-label col-lg-2">{l s='Merchant Promotion' mod='hipay_enterprise'}</label>
                    <div class="input-group col-lg-2">
                        <input title="OPC provided by Oney" type="text" name="{$key}_merchantPromotion" value="{$method.merchantPromotion}"/>
                    </div>
                </div>
            </div>
        {/if}
        {if $method["currencySelectorReadOnly"]}
            <div class="row">
                <div class="form-group">
                    <label class="control-label col-lg-2">{l s='Activated Currencies' mod='hipay_enterprise'}</label>
                    {foreach  $method["currencies"] as $currency }
                        {if isset($limitedCurrencies[$currency])}
                            <span class="label-value col-lg-2">{$limitedCurrencies[$currency]}</span>
                            <input type="hidden" value="{$currency}" name="{$key}_currencies[]"/>
                        {else}
                            <span class="label-value col-lg-3">{$currency}
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
                        <select id="multiselect-{$key}" name="{$key}_currencies[]" multiple="multiple"
                                class="multiselect-currency">
                            {foreach $limitedCurrencies as $currency }
                                <option value="{$currency@key}" {if !empty($method.currencies) && $currency@key|in_array:$method.currencies } selected {/if} >{$currency@key}
                                    - {$currency} </option>
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
