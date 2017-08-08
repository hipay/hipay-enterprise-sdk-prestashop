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
        <div class="collapse in" id="collapseLocalPayment">
            <div role="tabpanel">
                <ul class="nav nav-pills nav-stacked col-md-2" role="tablist">
                    <li role="presentation" class="disabled credit-card-title"></li>
                    {assign var="groupSummary" value=""}
                    {foreach $config_hipay.payment.local_payment as $localPayment}
                        {if 'group'|array_key_exists:$localPayment}
                            {assign var="itemInGroup" value="true"}
                            {if $groupSummary != $localPayment["group"]["code"] }
                                {assign var="groupSummary" value=$localPayment["group"]["code"]}
                                <li role="presentation" class=" {if $localPayment@first} active {/if} ">
                                    <a href="#{$localPayment["group"]["code"]}" aria-controls="{$localPayment["group"]["code"]}" role="tab"
                                       data-toggle="tab">{l s={$localPayment["group"]["label"]} mod='hipay_enterprise'}</a>
                                </li>
                            {/if}
                        {else}
                            <li role="presentation" class=" {if $localPayment@first} active {/if} ">
                                <a href="#{$localPayment@key}" aria-controls="{$localPayment@key}" role="tab"
                                   data-toggle="tab">{l s=$localPayment["displayNameBO"] mod='hipay_enterprise'}</a>
                            </li>
                        {/if}

                    {/foreach}
                </ul>
                <form method="post" class="form-horizontal" action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" id="local_payment_form">
                    <div class="tab-content col-md-10">
                        {assign var="groupSummary" value=""}
                        {foreach $config_hipay.payment.local_payment as $localPayment}
                            {assign var="itemInGroup" value=false}
                            {if 'group'|array_key_exists:$localPayment}
                                {assign var="itemInGroup" value=true}
                                {if $groupSummary != $localPayment["group"]["code"] }
                                    <div role="tabpanel" class="tab-pane {if $localPayment@first} active {/if}" id="{$localPayment["group"]["code"]}">
                                            <div role="tabpanel">
                                                <ul class="hipay-enterprise nav nav-tabs" role="tablist">
                                                    {foreach $config_hipay.payment.local_payment as $localPaymentGroup}
                                                        {if 'group'|array_key_exists:$localPaymentGroup}
                                                            {if $localPayment["group"]["code"] == $localPaymentGroup["group"]["code"]}
                                                                    {assign var="groupSummary" value=$localPayment["group"]["code"]}
                                                                    <li role="presentation" class="{if $localPaymentGroup@first} active {/if}">
                                                                            <a href="#{$localPaymentGroup@key}" aria-controls="{$localPaymentGroup@key}" role="tab"
                                                                               data-toggle="tab">{l s=$localPaymentGroup["displayNameBO"] mod='hipay_enterprise'}</a>
                                                                    </li>
                                                            {/if}
                                                        {/if}
                                                    {/foreach}
                                                </ul>
                                            </div>
                                            <div class="tab-content">
                                                {foreach $config_hipay.payment.local_payment as $localPaymentDetail}
                                                    {if 'group'|array_key_exists:$localPaymentDetail}
                                                        {if $localPayment["group"]["code"] == $localPaymentDetail["group"]["code"]}
                                                            {assign var="groupSummary" value=$localPayment["group"]["code"]}
                                                            <div role="tabpanel" class="tab-pane {if $localPaymentDetail@first} active {/if}" id="{$localPaymentDetail@key}">
                                                                {include file='./detail-local-payment.tpl' method=$localPaymentDetail
                                                                key=$localPaymentDetail@key first=$localPaymentDetail@first}
                                                            </div>
                                                        {/if}
                                                    {/if}
                                                {/foreach}
                                            </div>
                                    </div>
                                {/if}
                            {/if}

                            {if !$itemInGroup}
                                {include file='./detail-local-payment.tpl' method=$localPayment key=$localPayment@key first=$localPayment@first}
                            {/if}
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