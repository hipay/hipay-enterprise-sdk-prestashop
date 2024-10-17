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

<div class="panel hipay-tabs" id="panel-local-payment">
    <div class="form-wrapper">
        <div class="panel-heading">
            <a data-toggle="collapse" href="#payment_form__collapseLocalPayment" aria-expanded="true"
                aria-controls="payment_form__collapseLocalPayment">
                <i class="icon icon-credit-card"></i> {l s='Local payment' mod='hipay_enterprise'}<i id="chevronLocal"
                        class="pull-right chevron icon icon-chevron-down"></i>
                
            </a>
        </div>
        <div class="collapse in" id="payment_form__collapseLocalPayment">
            <div role="tabpanel">
                <ul class="nav nav-pills nav-stacked col-md-2" role="tablist">
                    <li role="presentation" class="disabled credit-card-title"></li>
                    {assign var="groupSummary" value=""}
                    {assign var="groupSummaryDone" value=""}
                    {foreach $HiPay_config_hipay.payment.local_payment as $localPayment}
                        {if empty($localPayment["minPrestashopVersion"]) || $localPayment["minPrestashopVersion"] <= $HiPay_prestashopVersion}
                            {if 'group'|arrayKeyExists:$localPayment}
                                {assign var="itemInGroup" value="true"}
                                {if $groupSummary != $localPayment["group"]["code"] }
                                    {assign var="arrayGroupSummaryDone"  value=','|explode:$groupSummaryDone}
                                    {if !$localPayment["group"]["code"]|inArray:$arrayGroupSummaryDone}
                                        {assign var="groupSummary" value=$localPayment["group"]["code"]}
                                        <li role="presentation" class=" {if $localPayment@first} active {/if} ">
                                            <a href="#payment_form__{$groupSummary}" aria-controls="payment_form__{$groupSummary}"
                                                role="tab" data-toggle="tab">{l s={$localPayment["group"]["label"]}
                                                mod='hipay_enterprise'}</a>
                                        </li>
                                        {assign var="groupSummaryDone" value="$groupSummaryDone,$groupSummary"}
                                    {/if}
                                {/if}
                            {else}
                                <li role="presentation" class=" {if $localPayment@first} active {/if} ">
                                    <a href="#payment_form__{$localPayment@key}" aria-controls="payment_form__{$localPayment@key}"
                                        role="tab" data-toggle="tab">{l s=$localPayment["displayNameBO"] mod='hipay_enterprise'}</a>
                                </li>
                            {/if}
                        {/if}
                    {/foreach}
                </ul>
                <form method="post" class="form-horizontal"
                    action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" id="local_payment_form">
                    <div class="tab-content col-md-10">
                        {assign var="groupSummary" value=""}
                        {assign var="groupSummaryDone" value=""}
                        {foreach $HiPay_config_hipay.payment.local_payment as $localPayment}
                            {if empty($localPayment["minPrestashopVersion"]) || $localPayment["minPrestashopVersion"] <= $HiPay_prestashopVersion}
                                {assign var="itemInGroup" value=false}
                                {if 'group'|arrayKeyExists:$localPayment}
                                    {assign var="itemInGroup" value=true}
                                    {if $groupSummary != $localPayment["group"]["code"] }
                                        {assign var="arrayGroupSummaryDone"  value=','|explode:$groupSummaryDone}
                                        {if !$localPayment["group"]["code"]|inArray:$arrayGroupSummaryDone}
                                            {assign var="groupSummary" value=$localPayment["group"]["code"]}
                                            {assign var="groupSummaryDone" value="$groupSummaryDone,$groupSummary"}
                                            <div role="tabpanel" class="tab-pane {if $localPayment@first} active {/if}"
                                                id="payment_form__{$localPayment["group"]["code"]}">
                                                <div role="tabpanel">
                                                    <ul class="hipay-enterprise nav nav-tabs" role="tablist">
                                                        {assign var="firstElement" value=true}
                                                        {foreach $HiPay_config_hipay.payment.local_payment as $localPaymentGroup}
                                                            {if 'group'|arrayKeyExists:$localPaymentGroup}
                                                                {if $localPayment["group"]["code"] == $localPaymentGroup["group"]["code"]}
                                                                    {assign var="groupSummary" value=$localPayment["group"]["code"]}
                                                                    <li role="presentation" class="{if $firstElement} active {/if}">
                                                                        <a href="#payment_form__{$groupSummary}__{$localPaymentGroup@key}"
                                                                            aria-controls="payment_form__{$groupSummary}__{$localPaymentGroup@key}"
                                                                            role="tab"
                                                                            data-toggle="tab">{l s=$localPaymentGroup["displayNameBO"] mod='hipay_enterprise'}</a>
                                                                    </li>
                                                                    {assign var="firstElement" value=false}
                                                                {/if}
                                                            {/if}
                                                        {/foreach}
                                                    </ul>
                                                </div>
                                                <div class="tab-content">
                                                    {assign var="firstElement" value=true}
                                                    {foreach $HiPay_config_hipay.payment.local_payment as $localPaymentDetail}
                                                        {if 'group'|arrayKeyExists:$localPaymentDetail}
                                                            {if $localPayment["group"]["code"] == $localPaymentDetail["group"]["code"]}
                                                                <div role="tabpanel" class="tab-pane {if $firstElement} active {/if}"
                                                                    id="payment_form__{$groupSummary}__{$localPaymentDetail@key}">
                                                                    {include file='./detail-local-payment.tpl' method=$localPaymentDetail key=$localPaymentDetail@key first=$localPaymentDetail@first}
                                                                </div>
                                                                {assign var="firstElement" value=false}
                                                            {/if}
                                                        {/if}
                                                    {/foreach}
                                                </div>
                                            </div>
                                        {/if}
                                    {/if}
                                {/if}

                                {if !$itemInGroup}
                                    <div role="tabpanel" class="tab-pane {if $localPayment@first} active {/if}"
                                        id="payment_form__{$localPayment@key}">
                                        {assign var="templateRenderer" value=$localPayment["template"]|default:'./detail-local-payment.tpl'}
                                        {include file=$templateRenderer method=$localPayment key=$localPayment@key first=$localPayment@first}
                                    </div>
                                {/if}
                            {/if}
                        {/foreach}
                    </div>
                    <span style="clear: left;display: block;"></span>
                    <div class="panel-footer">
                        <div class="col-md-12 col-xs-12">
                            <button type="submit" class="btn btn-default pull-left" name="submitCancel"><i
                                    class="process-icon-eraser"></i>{l s='Discard changes' mod='hipay_enterprise'}
                            </button>
                            <button type="submit" class="btn btn-default btn btn-default pull-right"
                                name="localPaymentSubmit">
                                <i
                                    class="process-icon-save"></i>{l s='Save configuration changes' mod='hipay_enterprise'}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    {foreach $HiPay_config_hipay.payment.local_payment as $localPayment}
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
<script>
  $(document).ready(function() {
    function initializeHiPayAlma() {
      if (window.hipayInitialized) return;
      window.hipayInitialized = true;

      var prestashopConfig = {$HiPay_config_hipay.account|json_encode nofilter};
      const hipayAvailablePaymentProducts = availablePaymentProducts();

      var apiUsername, apiPassword;
      if (prestashopConfig.global.sandbox_mode) {
        apiUsername = prestashopConfig.sandbox.api_username_sandbox;
        apiPassword = prestashopConfig.sandbox.api_password_sandbox;
      } else {
        apiUsername = prestashopConfig.production.api_username_production;
        apiPassword = prestashopConfig.production.api_password_production;
      }

      hipayAvailablePaymentProducts.setCredentials(
        apiUsername,
        apiPassword,
        prestashopConfig.global.sandbox_mode
      );

      hipayAvailablePaymentProducts.updateConfig('payment_product', ['alma-3x','alma-4x']);
      hipayAvailablePaymentProducts.updateConfig('currency', ['EUR']);
      hipayAvailablePaymentProducts.updateConfig('with_options', true);

      $('.alma-container').each(function() {
        $(this).prepend('<div class="loader"></div>');
        $(this).find('h4').hide();
      });

      hipayAvailablePaymentProducts.getAvailableProducts()
        .then(result => {
          result.forEach(product => {
            if (product.code === 'alma-3x') {
              const basketMax3x = product.options?.basketAmountMax3x;
              const basketMin3x = product.options?.basketAmountMin3x;
              $('#alma-3x_minAmount span').html(basketMin3x + ' &euro;');
              $('#alma-3x_maxAmount span').html(basketMax3x + ' &euro;');
            } else if (product.code === 'alma-4x') {
              const basketMax4x = product.options?.basketAmountMax4x;
              const basketMin4x = product.options?.basketAmountMin4x;
              $('#alma-4x_minAmount span').html(basketMin4x + ' &euro;');
              $('#alma-4x_maxAmount span').html(basketMax4x + ' &euro;');
            }
          });

          $('.alma-container').each(function() {
            $(this).find('.loader').remove();
            $(this).find('h4').show();
          });
        })
        .catch(error => {
          console.error('Error fetching available products:', error);
          $('.alma-container').each(function() {
            $(this).find('.loader').remove();
            $(this).find('h4').html('Error loading data').show();
          });
        });
    }

    // Check on document ready
    if ($('#payment_form__alma').hasClass('active')) {
      initializeHiPayAlma();
    }

    // Check when Alma tab is shown
    $('a[href="#payment_form__alma"]').on('shown.bs.tab', function (e) {
      initializeHiPayAlma();
    });
  });
</script>