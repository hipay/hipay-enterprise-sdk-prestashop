{capture name=path}{l s='HiPay payment.' mod='hipay_tpp'}{/capture}
<h2>{l s='Order summary' mod='hipay_tpp'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if $nbProducts <= 0}
    <p class="warning">{l s='Your shopping cart is empty.' mod='hipay_tpp'}</p>
{else}
    <h3>{l s='HiPay payment.' mod='hipay_tpp'}</h3>
    {if $status_error=='200'}
        {*}No display of errors{*}
    {else if $status_error=='400'}
        <p class="error">{l s='The request was rejected due to a validation error. Please verify the card details you entered.' mod='hipay_tpp'}</p>
    {else if $status_error=='503'}
        <p class="error">{l s='HiPay TPP is temporarily unable to process the request. Try again later.' mod='hipay_tpp'}</p>
    {else if $status_error=='403'}
        <p class="error">{l s='A forbidden action has been identified, process has been cancelled.' mod='hipay_tpp'}</p>
    {else if $status_error=='999'}
        <p class="error">{l s='Please select one of the memorized card before continuing.' mod='hipay_tpp'}</p>
    {else}
        <p class="error">
            <strong>{l s='Error code' mod='hipay_tpp'} : {$status_error}</strong>
            <br />
            {l s='An error occured, process has been cancelled.' mod='hipay_tpp'}
        </p>
    {/if}

    <form enctype="application/x-www-form-urlencoded" action="{$link->getModuleLink('hipay_enterprise', 'validation', [], true)|escape:'html'}" class="form-horizontal" method="post" name="tokenizerForm" id="tokenizerForm" autocomplete="off">
        <div class="order_carrier_content box">
            <div class="control-group">
                <label class="control-label" style="float: left; margin: 0 0px 0 0; font-size: 15px; font-weight: bold;">{l s='Order' mod='hipay_tpp'}:&nbsp;</label>
                <div class="controls" style="float: left; font-size: 13px; font-weight: bold;">
                    #{$cart_id}<span id="cartIdMessage"></span>
                    <input type="hidden" class="input-medium" name="cartId" id="cartId" value="{$cart_id}">
                </div>
                <div style="clear: both;"></div>
            </div>
            <br />
            <div class="control-group">
                <label class="control-label" style="float: left; margin: 0 0px 0 0; font-size: 15px; font-weight: bold;">{l s='Amount' mod='hipay_tpp'}:&nbsp;</label>
                <div class="controls" style="float: left; font-weight:bold; color:#072959;font-size:15px;">
                    {$amount} {$currency->iso_code}
                </div>
                <div style="clear: both;"></div>
            </div>
            <br />
            {include file="$hipay_enterprise_tpl_dir/paymentForm.tpl"}
        </div>
        <p class="cart_navigation clearfix">
            <button id="pay-button" type="submit" name="processCarrier" class="button btn btn-default standard-checkout button-medium" style="">
                <span>
                    {l s='Pay' mod='hipay_tpp'}
                </span>
            </button>
        </p>
    </form>
    <script>
        $("#pay-button").click(function () {
            var params = {
                card_number: $('#card-number')[0].value,
                cvc: $('#cvv')[0].value,
                card_expiry_month: $('#expiry-month')[0].value,
                card_expiry_year: $('#expiry-year')[0].value,
                card_holder: $('#the-card-name-id')[0].value,
                multi_use: '0'
            };


            HiPay.setTarget('stage'); // default is production/live

            // These are fake credentials, put your own credentials here (HiPay Enterprise back office > Integration > Security settings and create credentials with public visibility)
            HiPay.setCredentials('11111111.stage-secure-gateway.hipay-tpp.com', 'Test_pMAjfghBqya7TA9jqhYah56');

            HiPay.create(params,
                    function (result) {

                    // The card has been successfully tokenized

                        token = result.token;

                    },
                    function (errors) {

                        // An error occurred

                        if (typeof errors.message != "undefined") {
                            $("#error").text("Error: " + errors.message);
                        } else {
                            $("#error").text("An error occurred with the request.");
                        }
                    }
            );

            return false;
        });
    </script>
{/if}
