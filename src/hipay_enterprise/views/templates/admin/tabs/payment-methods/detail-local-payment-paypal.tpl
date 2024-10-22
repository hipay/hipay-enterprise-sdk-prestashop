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
                        <input type="radio" name="{$key}_activated" id="{$key}_activated_on" value="1"
                            {if $method.activated }checked="checked" {/if}>
                        <label for="{$key}_activated_on">{l s='Yes' mod='hipay_enterprise'}</label>
                        <input type="radio" name="{$key}_activated" id="{$key}_activated_off" value="0"
                            {if $method.activated == false }checked="checked" {/if}>
                        <label for="{$key}_activated_off">{l s='No' mod='hipay_enterprise'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                </div>
            </div>
        </div>
        {if "displayName"|inArray:$method.displayConfigurationFields}
            <div class="row">
                <div class="form-group">
                    <label class="control-label col-lg-2">{l s='Display name' mod='hipay_enterprise'}</label>

                    {foreach from=$HiPay_languages item=language key=id}
                        <div class="col-lg-3 {if $HiPay_languages|count > 1} translatable-field lang-{$language.iso_code} {/if}"
                            {if $id > 0}style="display: none" {/if}>

                            <input type="text" name="{$key}_displayName[{$language.iso_code}]"
                                class="translatable-field lang-{$language.iso_code}"
                                {if isset($method.displayName[$language.iso_code])}
                                    value="{$method.displayName[$language.iso_code]}"
                                {elseif isset($method.displayName) && !is_array($method.displayName)}
                                value="{$method.displayName}" {else} value="
                                    {reset($method.displayName)}" 
                                {/if} />
                        </div>
                        {if $HiPay_languages|count > 1}
                            <div class="col-lg-2 translatable-field lang-{$language.iso_code} " {if $id > 0}style="display: none"
                                {/if}>
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" tabindex="-1">
                                    {$language.iso_code}
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu">
                                    {foreach from=$HiPay_languages item=language}
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
                <div class="col-lg-1">
                    <input type="text" class="money-type" name="{$key}_frontPosition" value="{$method.frontPosition}" />
                </div>
            </div>
        </div>
        <br />
        {if "iframe"|inArray:$method.displayConfigurationFields}
            <div class="row">
                <div class="form-group">
                    <label class="control-label col-lg-2">
                        {l s='Iframe' mod='hipay_enterprise'}
                    </label>
                    <div class="col-lg-9">
                        <span class="switch prestashop-switch fixed-width-lg">
                            <input type="radio" name="{$key}_iframe" id="{$key}_iframe_on" value="1"
                                {if $method.iframe }checked="checked" {/if}>
                            <label for="{$key}_iframe_on">{l s='Yes' mod='hipay_enterprise'}</label>
                            <input type="radio" name="{$key}_iframe" id="{$key}_iframe_off" value="0"
                                {if $method.iframe == false }checked="checked" {/if}>
                            <label for="{$key}_iframe_off">{l s='No' mod='hipay_enterprise'}</label>
                            <a class="slide-button btn"></a>
                        </span>
                    </div>
                </div>
            </div>
        {/if}
        {if "minAmount"|inArray:$method.displayConfigurationFields}
            <div class="row">
                <div class="form-group">
                    <label class="control-label col-lg-2">{l s='Minimum order amount' mod='hipay_enterprise'}</label>
                    <div class="input-group col-lg-2">
                        <input type="text" class="money-type" name="{$key}_minAmount[EUR]" value="{$method.minAmount.EUR}"
                            {if $method.minAmount.fixed|default:false eq true } readonly {/if} />
                        {if isset($method.minAmount.fixed)}
                            <input type="hidden" name="{$key}_minAmount[fixed]" value="{$method.minAmount.fixed}" />
                        {/if}
                        <span class="input-group-addon">{Currency::getDefaultCurrency()->sign}</span>
                    </div>
                </div>
            </div>
        {/if}
        {if "maxAmount"|inArray:$method.displayConfigurationFields}
            <div class="row">
                <div class="form-group">
                    <label class="control-label col-lg-2">{l s='Maximum order amount' mod='hipay_enterprise'}</label>
                    <div class="input-group col-lg-2">
                        <input type="text" class="money-type" name="{$key}_maxAmount[EUR]" value="{$method.maxAmount.EUR}"
                            {if $method.maxAmount.fixed|default:false eq true } readonly {/if} />
                        {if isset($method.maxAmount.fixed)}
                            <input type="hidden" name="{$key}_maxAmount[fixed]" value="{$method.maxAmount.fixed}" />
                        {/if}
                        <span class="input-group-addon">{Currency::getDefaultCurrency()->sign}</span>
                    </div>
                </div>
            </div>
        {/if}
        {if "orderExpirationTime"|inArray:$method.displayConfigurationFields}
            <div class="row">
                <div class="form-group">
                    <label class="control-label col-lg-2">{l s='Order expiration date' mod='hipay_enterprise'}</label>
                    <div class="input-group col-lg-2">
                        <select name="{$key}_orderExpirationTime">
                            <option value="3"
                                {if isset($method.orderExpirationTime) && $method.orderExpirationTime == "3"}selected="selected"
                                {/if}>{l s='3 days' mod='hipay_enterprise'}</option>
                            <option value="30"
                                {if isset($method.orderExpirationTime) && $method.orderExpirationTime == "30"}selected="selected"
                                {/if}>{l s='30 days' mod='hipay_enterprise'}</option>
                            <option value="90"
                                {if isset($method.orderExpirationTime) && $method.orderExpirationTime == "90"}selected="selected"
                                {/if}>{l s='90 days' mod='hipay_enterprise'}</option>
                        </select>
                    </div>
                </div>
            </div>
        {/if}
        {if "merchantPromotion"|inArray:$method.displayConfigurationFields}
            <div class="row">
                <div class="form-group">
                    <label class="control-label col-lg-2">{l s='Merchant Promotion' mod='hipay_enterprise'}</label>
                    <div class="input-group col-lg-2">
                        <input title="OPC provided by Oney" type="text" name="{$key}_merchantPromotion"
                            value="{$method.merchantPromotion}" />
                    </div>
                </div>
            </div>
        {/if}
        {if $method["currencySelectorReadOnly"]}
            <div class="row">
                <div class="form-group">
                    <label class="control-label col-lg-2">{l s='Activated Currencies' mod='hipay_enterprise'}</label>
                    {foreach  $method["currencies"] as $currency }
                        {if isset($HiPay_limitedCurrencies[$currency])}
                            <span class="label-value col-lg-2">{$HiPay_limitedCurrencies[$currency]}</span>
                            <input type="hidden" value="{$currency}" name="{$key}_currencies[]" />
                        {else}
                            <span class="label-value col-lg-3">{$currency}
                                {l s='This currency is not activated in your prestashop shop' mod='hipay_enterprise'}
                            </span>
                            <input type="hidden" value="{$currency}" name="{$key}_currencies[]" />
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
                            {foreach $HiPay_limitedCurrencies as $currency }
                                <option value="{$currency@key}"
                                    {if !empty($method.currencies) && $currency@key|inArray:$method.currencies } selected {/if}>
                                    {$currency@key}
                                    - {$currency} </option>
                            {/foreach}
                        </select>
                    </div>
                </div>
            </div>
        {/if}

        {if $method["countrySelectorReadOnly"]}
            <div class="row">
                <div class="form-group vertical-align">
                    <label class="control-label col-lg-2">{l s='Activated Countries' mod='hipay_enterprise'}</label>
                    <div class="inline-grid">
                        {foreach  $method["countries"] as $country }
                            {if isset($HiPay_limitedCountries[$country])}
                                <span class="col-lg-6 label-value">{$HiPay_limitedCountries[$country]}</span>
                                <input type="hidden" readonly value="{$country}" name="{$key}_countries[]" />
                            {else}
                                <span class="label-value col-lg-8">{$country}
                                    {l s='This country is not activated in your prestashop shop' mod='hipay_enterprise'}
                                </span>
                                <input type="hidden" value="{$country}" name="{$key}_countries[]" />
                            {/if}
                        {/foreach}
                    </div>
                </div>
            </div>
        {else}
            <div class="row">
                <div class="form-group">
                    <label class="control-label col-lg-2">{l s='Countries' mod='hipay_enterprise'}</label>
                    <div class="col-lg-6">
                        <select id="countries_{$key}" multiple="multiple" size="10" name="{$key}_countries[]">
                            {foreach $HiPay_limitedCountries as $country}
                                <option value="{$country@key}"
                                    {if !empty($method.countries) && $country@key|inArray:$method.countries } selected {/if}>
                                    {$country}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
            </div>
        {/if}
        <div id="paypal_v2_support" style="display:none">
            <div class="row">
                <div class="form-group">
                    <div class="col-lg-8">
                        <br>
                        <p class="alert alert-warning">
                            <b>{l s='NEW' mod='hipay_enterprise'}</b><br />
                            {l s='The new PayPal integration allows you to pay with PayPal without redirection and to offer payment with installments.' mod='hipay_enterprise'}<br /><br />
                            {l s='Available by ' mod='hipay_enterprise'}
                            <b>{l s='invitation only' mod='hipay_enterprise'}</b>
                            {l s='at this time, please contact our support or your account manager for more information.' mod='hipay_enterprise'}
                        </p>
                    </div>
                </div>
            </div>
        </div>
        {if "buttonShape"|inArray:$method.displayConfigurationFields}
            <div class="row">
                <div class="form-group">
                    <label class="control-label col-lg-2">{l s="Button Shape" mod='hipay_enterprise'}</label>
                    <div class="col-lg-2">
                        <select id="buttonShape" name="{$key}_buttonShape[]" class="select-buttonShape">
                            <option value="rect" {if $method.buttonShape[0] == "rect"}selected{/if}>
                                {l s="Rectangular" mod='hipay_enterprise'}
                            </option>
                            <option value="pill" {if $method.buttonShape[0] == "pill"}selected{/if}>
                                {l s="Rounded" mod='hipay_enterprise'}
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        {/if}

        {if "buttonLabel"|inArray:$method.displayConfigurationFields}
            <div class="row">
                <div class="form-group">
                    <label class="control-label col-lg-2">{l s="Button Label" mod='hipay_enterprise'}</label>
                    <div class="col-lg-2">
                        <select id="buttonLabel" name="{$key}_buttonLabel[]" class="select-buttonLabel">
                            <option value="paypal" {if $method.buttonLabel[0] == "paypal"}selected{/if}>
                                {l s="Paypal" mod='hipay_enterprise'}
                            </option>
                            <option value="pay" {if $method.buttonLabel[0] == "pay"}selected{/if}>
                                {l s="Pay" mod='hipay_enterprise'}
                            </option>
                            <option value="subscribe" {if $method.buttonLabel[0] == "subscribe"}selected{/if}>
                                {l s="Subscribe" mod='hipay_enterprise'}
                            </option>
                            <option value="checkout" {if $method.buttonLabel[0] == "checkout"}selected{/if}>
                                {l s="Checkout" mod='hipay_enterprise'}
                            </option>
                            <option value="buynow" {if $method.buttonLabel[0] == "buynow"}selected{/if}>
                                {l s="Buy Now" mod='hipay_enterprise'}
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        {/if}
        {if "buttonColor"|inArray:$method.displayConfigurationFields}
            <div class="row">
                <div class="form-group">
                    <label class="control-label col-lg-2">{l s="Button Color" mod='hipay_enterprise'}</label>
                    <div class="col-lg-2">
                        <select id="buttonColor" name="{$key}_buttonColor[]" class="select-_buttonColor">
                            <option value="gold" {if $method.buttonColor[0] == "gold"}selected{/if}>
                                {l s="Gold" mod='hipay_enterprise'}
                            </option>
                            <option value="blue" {if $method.buttonColor[0] == "blue"}selected{/if}>
                                {l s="Blue" mod='hipay_enterprise'}
                            </option>
                            <option value="black" {if $method.buttonColor[0] == "black"}selected{/if}>
                                {l s="Black" mod='hipay_enterprise'}
                            </option>
                            <option value="silver" {if $method.buttonColor[0] == "silver"}selected{/if}>
                                {l s="Silver" mod='hipay_enterprise'}
                            </option>
                            <option value="white" {if $method.buttonColor[0] == "white"}selected{/if}>
                                {l s="White" mod='hipay_enterprise'}
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        {/if}
        {if "buttonHeight"|inArray:$method.displayConfigurationFields}
            <div class="row">
                <div class="form-group">
                    <label class="control-label col-lg-2">{l s="Button Height" mod='hipay_enterprise'}</label>
                    <div class="col-lg-2">
                        <input type="number" id="buttonHeight" class="buttonHeight form-control" min="25" max="55"
                            name="{$key}_buttonHeight" value="{$method.buttonHeight}" />
                    </div>
                </div>
            </div>
        {/if}
        {if "bnpl"|inArray:$method.displayConfigurationFields}
            <div class="row">
                <div class="form-group">
                    <label class="control-label col-lg-2">
                        {l s='Pay Later Button' mod='hipay_enterprise'}
                    </label>
                    <div class="col-lg-9">
                        <span id="bnpl" class="switch prestashop-switch fixed-width-lg">
                            <input type="radio" name="{$key}_bnpl" id="bnpl_on" value="1" {if $method.bnpl}checked="checked"
                                {/if}>
                            <label for="{$key}_bnpl_on">{l s='Yes' mod='hipay_enterprise'}</label>
                            <input type="radio" name="{$key}_bnpl" id="bnpl_off" value="0"
                                {if $method.bnpl === false }checked="checked" {/if}>
                            <label for="{$key}_bnpl_off">{l s='No' mod='hipay_enterprise'}</label>
                            <a class="slide-button btn"></a>
                        </span>
                        <br>
                        <p class="alert alert-info">
                            {l s="\"Buy now, Pay later\" feature is only available if the store currency is euros and if the basket amount is between 30 and 2000" mod='hipay_enterprise'}
                        </p>
                    </div>
                </div>
            </div>
        {/if}
    </div>
</div>

<script>
    {literal}
        class HipayAvailablePaymentProducts {
            constructor(hipayConfig) {
                this.config = hipayConfig;
                this.setCredentialsAndUrl();
                this.generateAuthorizationHeader();
            }

            setCredentialsAndUrl() {
                if (this.config.account.global.sandbox_mode) {
                    this.apiUsername = this.config.account.sandbox.api_username_sandbox;
                    this.apiPassword = this.config.account.sandbox.api_password_sandbox;
                    this.baseUrl = 'https://stage-secure-gateway.hipay-tpp.com/rest/v2/';
                } else {
                    this.apiUsername = this.config.account.production.api_username_production;
                    this.apiPassword = this.config.account.production.api_password_production;
                    this.baseUrl = 'https://secure-gateway.hipay-tpp.com/rest/v2/';
                }
            }

            generateAuthorizationHeader() {
                const credentials = `${this.apiUsername}:${this.apiPassword}`;
                const encodedCredentials = btoa(credentials);
                this.authorizationHeader = `Basic ${encodedCredentials}`;
            }

            async getAvailablePaymentProducts(
                paymentProduct = 'paypal',
                eci = '7',
                operation = '4',
                withOptions = 'true'
            ) {
                const url = new URL(`${this.baseUrl}available-payment-products.json`);
                url.searchParams.append('eci', eci);
                url.searchParams.append('operation', operation);
                url.searchParams.append('payment_product', paymentProduct);
                url.searchParams.append('with_options', withOptions);

                try {
                    const response = await fetch(url, {
                        method: 'GET',
                        headers: {
                            'Authorization': this.authorizationHeader,
                            'Accept': 'application/json'
                        }
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    return await response.json();
                } catch (error) {
                    console.error('There was a problem with the fetch operation:', error);
                    throw error;
                }
            }
        }
    {/literal}

    $(document).ready(function() {
        var initPaypalV2 = false;

        function toggleFields(PayPalMerchantData) {
            const options = PayPalMerchantData.options;
            if (options?.provider_architecture_version === 'v1' &&
                options?.payer_id.length > 0) {
                ['buttonColor', 'buttonShape', 'buttonLabel', 'buttonHeight', 'bnpl'].forEach(function(
                    fieldId) {
                    var field = document.getElementById(fieldId);
                    field.classList.remove('readonly');
                    $('#paypal_v2_support').hide();
                });
            } else {
                ['buttonColor', 'buttonShape', 'buttonLabel', 'buttonHeight', 'bnpl'].forEach(function(
                    fieldId) {
                    var field = document.getElementById(fieldId);
                    field.classList.add('readonly');
                    $('#paypal_v2_support').show()
                });
            }
        }

        function fetchAndToggleFields() {
            if (!initPaypalV2) {
                const hipayProducts = new HipayAvailablePaymentProducts({$HiPay_config_hipay|json_encode nofilter});

                hipayProducts.getAvailablePaymentProducts()
                    .then(data => {
                        if (data?.length > 0) {
                            initPaypalV2 = true;
                            toggleFields(data[0]);
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
        }

        if ($('#payment_form__paypal').hasClass('active')) {
            fetchAndToggleFields();
        }

        $('a[href="#payment_form__paypal"]').on('shown.bs.tab', function(e) {
            fetchAndToggleFields();
        });
    });
</script>
