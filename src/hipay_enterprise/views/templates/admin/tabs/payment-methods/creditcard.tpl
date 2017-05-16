<h3><i class="icon icon-credit-card"></i> {l s='Production configuration' mod='hipay_professional'}</h3>   

<div role="tabpanel">
    <ul class="nav nav-tabs" role="tablist">
        {foreach $config_hipay.payment.credit_card as $creditCard}
            <li role="presentation" class="{if $creditCard@first} active {/if} ">
                <a href="#{$creditCard@key}" aria-controls="{$creditCard@key}" role="tab" data-toggle="tab">{l s=$creditCard@key mod='hipay_professional'}</a>
            </li>
        {/foreach}
    </ul>
    <form method="post" class="" action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" id="credit_card_form">
        <div class="tab-content">
            {foreach $config_hipay.payment.credit_card as $creditCard}
                <div role="tabpanel" class="tab-pane {if $creditCard@first} active {/if}"id="{$creditCard@key}">
                    <div class="panel">
                        <!-- SWITCH MODE START -->
                        <div class="row">
                            <label class="control-label col-lg-3">
                                {l s='Activated' mod='hipay_professional'}
                            </label>
                            <div class="col-lg-9">
                                <span class="switch prestashop-switch fixed-width-lg">
                                    <input type="radio" name="{$creditCard@key}_activated" id="{$creditCard@key}_activated_on" value="1"
                                         {if $creditCard.activated }checked="checked"{/if}  >
                                    <label for="{$creditCard@key}_activated_on">{l s='Yes' mod='hipay_professional'}</label>
                                    <input type="radio" name="{$creditCard@key}_activated" id="{$creditCard@key}_activated_off" value="0"
                                          {if $creditCard.activated == false }checked="checked"{/if}  >
                                    <label for="{$creditCard@key}_activated_off">{l s='No' mod='hipay_professional'}</label>
                                    <a class="slide-button btn"></a>
                                </span>
                            </div>
                        </div>
                        <br/>
                        <!-- SWITCH MODE END -->
                        <div class="row">
                            <div class="form-group">
                                <label class="control-label col-lg-3">{l s='Currencies' mod='hipay_professional'}</label>
                                {foreach $limitedCurrencies as $currency }
                                    <label class="control-label col-lg-1"> 
                                        <input type="checkbox" name="{$creditCard@key}_currencies[]" {if $currency@key|in_array:$creditCard.currencies } checked {/if} value="{$currency@key}" />
                                        {$currency}
                                    </label>
                                {/foreach}
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group">
                                <select id="countries_{$creditCard@key}" multiple="multiple" size="10" name="{$creditCard@key}_countries[]">
                                    {foreach $limitedCountries as $country}
                                        <option value="{$country}" {if $country|in_array:$creditCard.countries } selected {/if} >{$country}</option>
                                    {/foreach}
                                </select>
                            </div>    
                        </div>

                        <div class="row">
                            <div class="col-md-12 col-xs-12">
                                <button type="submit" class="btn btn-default pull-left" name="submitCancel"><i
                                        class="process-icon-eraser"></i>{l s='Discard changes' mod='hipay_professional'}
                                </button>
                                <button type="submit" class="btn btn-default btn btn-default pull-right" name="creditCardSubmit">
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
    {foreach $config_hipay.payment.credit_card as $creditCard}
    var {$creditCard@key}_dualistbox = $('#countries_{$creditCard@key}').bootstrapDualListbox({
        showFilterInputs: false,
        moveOnSelect: false,
        nonSelectedListLabel: '{l s='Available countries' mod='hipay_professional'}',
        selectedListLabel: '{l s='Authorized countries' mod='hipay_professional'}',
        infoText: false
    });
    {/foreach}
</script>