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
<div class="panel credit-card hipay-tabs" id="panel-credit-card">
    <div class="form-wrapper">
        <div class="panel-heading">
            <a data-toggle="collapse" href="#payment_form__collapseCC" aria-expanded="true"
                aria-controls="payment_form__collapseCC">
                <i class="icon icon-credit-card"></i> {l s='Credit card' mod='hipay_enterprise'} <i id="chevronCC"
                    class="pull-right chevron icon icon-chevron-down"></i>
            </a>
        </div>
        <div class="collapse in" id="payment_form__collapseCC">
            <form method="post" class="form-horizontal" action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}"
                id="credit_card_form">
                <div class="panel">
                    <div class="form-group">
                        <label class="control-label col-lg-2">{l s='Display name' mod='hipay_enterprise'}</label>
                        {foreach from=$languages item=language key=id}
                            <div class="col-lg-3 {if $languages|count > 1} translatable-field lang-{$language.iso_code} {/if}"
                                {if $id > 0}style="display: none" {/if}>

                                <div class="row">
                                    <input id="ccDisplayName-{$language.iso_code}" type="text"
                                        name="ccDisplayName[{$language.iso_code}]"
                                        class="translatable-field lang-{$language.iso_code}"
                                        {if isset($config_hipay.payment.global.ccDisplayName[$language.iso_code])}
                                            value="{$config_hipay.payment.global.ccDisplayName[$language.iso_code]}"
                                        {elseif isset($config_hipay.payment.global.ccDisplayName) && !is_array($config_hipay.payment.global.ccDisplayName)}
                                        value="{$config_hipay.payment.global.ccDisplayName}" {else}
                                        value="{reset($config_hipay.payment.global.ccDisplayName)}" {/if} />
                                </div>

                                <p class="help-block ">
                                    {l s='Display name for payment by credit card on your checkout page.' mod='hipay_enterprise'}
                                </p>
                            </div>
                            {if $languages|count > 1}
                                <div class="col-lg-2 translatable-field lang-{$language.iso_code} "
                                    {if $id > 0}style="display: none" {/if}>
                                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
                                        tabindex="-1">
                                        {$language.iso_code}
                                        <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        {foreach from=$languages item=language}
                                            <li>
                                                <a
                                                    href="javascript:selectLanguageHipay('{$language.iso_code}');">{$language.name}</a>
                                            </li>
                                        {/foreach}
                                    </ul>
                                </div>
                            {/if}
                        {/foreach}
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-2">{l s='Front positioning' mod='hipay_enterprise'}</label>
                        <div class="col-lg-1" style='width:45px;'>
                            <div class="row">
                                <input type="text" class="money-type" name="ccFrontPosition"
                                    value="{$config_hipay.payment.global.ccFrontPosition}" />
                            </div>
                        </div>
                    </div>
                </div>
                <div role="tabpanel">
                    <ul class="nav nav-pills nav-stacked col-md-2" role="tablist">
                        <li role="presentation" class="disabled summary credit-card-title"></li>
                        {foreach $config_hipay.payment.credit_card as $creditCard}
                            <li role="presentation" class="{if $creditCard@first} active {/if} ">
                                <a href="#payment_form__{$creditCard@key}" aria-controls="payment_form__{$creditCard@key}"
                                    role="tab" data-toggle="tab">{l s=$creditCard["displayName"] mod='hipay_enterprise'}</a>
                            </li>
                        {/foreach}
                    </ul>


                    <div class="tab-content col-md-10">
                        {foreach $config_hipay.payment.credit_card as $creditCard}
                            <div role="tabpanel" class="tab-pane {if $creditCard@first} active {/if}"
                                id="payment_form__{$creditCard@key}">
                                <div class="panel">
                                    <div class="row">
                                        <h4 class="col-lg-4 col-lg-offset-2">
                                            {l s=$creditCard["displayName"] mod='hipay_enterprise'}
                                        </h4>
                                    </div>
                                    <div class="row">
                                        <label class="control-label col-lg-2">
                                            {l s='Activated' mod='hipay_enterprise'}
                                        </label>
                                        <div class="col-lg-9">
                                            <span class="switch prestashop-switch fixed-width-lg">
                                                <input type="radio" name="{$creditCard@key}_activated"
                                                    id="{$creditCard@key}_activated_on" value="1"
                                                    {if $creditCard.activated }checked="checked" {/if}>
                                                <label
                                                    for="{$creditCard@key}_activated_on">{l s='Yes' mod='hipay_enterprise'}</label>
                                                <input type="radio" name="{$creditCard@key}_activated"
                                                    id="{$creditCard@key}_activated_off" value="0"
                                                    {if $creditCard.activated == false }checked="checked" {/if}>
                                                <label
                                                    for="{$creditCard@key}_activated_off">{l s='No' mod='hipay_enterprise'}</label>
                                                <a class="slide-button btn"></a>
                                            </span>
                                        </div>
                                    </div>
                                    <br />
                                    <!-- SWITCH MODE END -->
                                    <div class="row">
                                        <div class="form-group">
                                            <label
                                                class="control-label col-lg-2">{l s='Minimum order amount' mod='hipay_enterprise'}</label>
                                            <div class="input-group col-lg-2">
                                                <input type="text" class="money-type"
                                                    name="{$creditCard@key}_minAmount[EUR]"
                                                    value="{$creditCard.minAmount.EUR}" />
                                                <span
                                                    class="input-group-addon">{Currency::getDefaultCurrency()->sign}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <br />
                                    <div class="row">
                                        <div class="form-group">
                                            <label
                                                class="control-label col-lg-2">{l s='Maximum order amount' mod='hipay_enterprise'}</label>
                                            <div class="input-group col-lg-2">
                                                <input type="text" class="money-type"
                                                    name="{$creditCard@key}_maxAmount[EUR]"
                                                    value="{$creditCard.maxAmount.EUR}" />
                                                <span
                                                    class="input-group-addon">{Currency::getDefaultCurrency()->sign}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <br />
                                    <div class="row">
                                        <div class="form-group">
                                            <label
                                                class="control-label col-lg-2">{l s='Currencies' mod='hipay_enterprise'}</label>
                                            <div class="col-lg-9">
                                                <select id="multiselect-{$creditCard@key}"
                                                    name="{$creditCard@key}_currencies[]" multiple="multiple"
                                                    class="multiselect-currency">
                                                    {foreach $limitedCurrencies as $currency }
                                                        <option value="{$currency@key}"
                                                            {if !empty($creditCard.currencies) && $currency@key|in_array:$creditCard.currencies }
                                                            selected {/if}>{$currency@key}
                                                            - {$currency} </option>
                                                    {/foreach}
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group">
                                            <label
                                                class="control-label col-lg-2">{l s='Countries' mod='hipay_enterprise'}</label>
                                            <div class="col-lg-6">
                                                <select id="countries_{$creditCard@key}" multiple="multiple" size="10"
                                                    name="{$creditCard@key}_countries[]">
                                                    {foreach $limitedCountries as $country}
                                                        <option value="{$country@key}"
                                                            {if !empty($creditCard.countries) && $country@key|in_array:$creditCard.countries }
                                                            selected {/if}>{$country}</option>
                                                    {/foreach}
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {/foreach}
                    </div>

                </div>
                <span style="clear: left;display: block;"></span>
                <div class="panel-footer">
                    <div class="col-md-12 col-xs-12">
                        <button type="submit" class="btn btn-default pull-left" name="submitCancel"><i
                                class="process-icon-eraser"></i>{l s='Discard changes' mod='hipay_enterprise'}
                        </button>
                        <button type="submit" class="btn btn-default btn btn-default pull-right"
                            name="creditCardSubmit">
                            <i class="process-icon-save"></i>{l s='Save configuration changes' mod='hipay_enterprise'}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    {foreach $config_hipay.payment.credit_card as $creditCard}
        var cc_{$creditCard@key|regex_replace:'/[^a-zA-Z0-9]/':""}_dualistbox = $('#countries_{$creditCard@key}').bootstrapDualListbox({
        showFilterInputs: false,
            moveOnSelect: false,
            nonSelectedListLabel: '{l s='Available countries' mod='hipay_enterprise'}',
            selectedListLabel: '{l s='Authorized countries' mod='hipay_enterprise'}',
            infoText: false
        });
    {/foreach}

    function selectLanguageHipay(iso_code) {
        $('.translatable-field').hide();
        $('.lang-' + iso_code).show();
    }
</script>
