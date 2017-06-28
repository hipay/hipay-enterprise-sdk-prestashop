{capture name=path}{l s='HiPay payment.' mod='hipay_tpp'}{/capture}
<h2>{l s='Order summary' mod='hipay_tpp'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if $nbProducts <= 0}
    <p class="warning">{l s='Your shopping cart is empty.' mod='hipay_tpp'}</p>
{else}
    <h3>{l s='HiPay payment.' mod='hipay_tpp'}</h3>
    {if $status_error=='200'}
        <p class="error"></p>
    {else if $status_error=='400'}
        <p class="error">{l s='The request was rejected due to a validation error. Please verify the card details you entered.' mod='hipay_tpp'}</p>
    {else if $status_error=='503'}
        <p class="error">{l s='HiPay TPP is temporarily unable to process the request. Try again later.' mod='hipay_tpp'}</p>
    {else if $status_error=='403'}
        <p class="error">{l s='A forbidden action has been identified, process has been cancelled.' mod='hipay_tpp'}</p>
    {else if $status_error=='999'}
        <p class="error">{l s='Please select one of the memorized card before continuing.' mod='hipay_tpp'}</p>
    {else if $status_error=='404'}
        <p class="error">{l s='This credit card type or the order currency is not supported. Please choose a other payment method.' mod='hipay_tpp'}</p>
    {else}
        <p class="error">
            <strong>{l s='Error code' mod='hipay_tpp'} : {$status_error}</strong>
            <br />
            {l s='An error occured, process has been cancelled.' mod='hipay_tpp'}
        </p>
    {/if}

    <form enctype="application/x-www-form-urlencoded" action="{$link->getModuleLink('hipay_enterprise', 'redirect', [], true)|escape:'html'}" class="form-horizontal" method="post" name="tokenizerForm" id="tokenizerForm" autocomplete="off">
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
        $("#pay-button").click(function (e) {
            //set param for Api call
            var params = {
                card_number: $('#card-number').val(),
                cvc: $('#cvc').val(),
                card_expiry_month: $('input[name=expiry-month]').val(),
                card_expiry_year: $('input[name=expiry-year]').val(),
                card_holder: $('#the-card-name-id').val(),
                multi_use: '0'
            };


            HiPay.setTarget('stage'); // default is production/live

            HiPay.setCredentials('{$confHipay.account.sandbox.api_tokenjs_username_sandbox}', '{$confHipay.account.sandbox.api_tokenjs_password_publickey_sandbox}');

            HiPay.create(params,
                    function (result) {
                        // The card has been successfully tokenized
                        token = result.token;
                        brand = result.brand;
                        pan = result.pan;
                        card_expiry_month = result.card_expiry_month;
                        card_expiry_year = result.card_expiry_year;
                        card_holder = result.card_holder;
                        issuer = result.issuer;
                        country = result.country;
                        
                        // set tokenization response
                        $('#card-token').val(token);
                        $('#card-brand').val(brand);
                        $('#card-pan').val(pan);
                        $('#card-expiry-month').val(card_expiry_month);
                        $('#card-expiry-year').val(card_expiry_year);
                        $('#card-issuer').val(issuer);
                        $('#card-country').val(country);

                        // we empty the form so we don't send credit card informations to the server
                        $('#card-number').val("");
                        $('#cvc').val("");
                        $('input[name=expiry-month]').val("");
                        $('input[name=expiry-year]').val("");
                        $('#the-card-name-id').val("");

                        //submit the form
                        $("#tokenizerForm").submit();

                        return true;
                    },
                    function (errors) {
                        console.log(errors);
                        // An error occurred

                        if (typeof errors.message != "undefined") {
                            $(".error").text("Error: " + errors.message);
                        } else {
                            $(".error").text("An error occurred with the request.");
                        }
                        return false;
                    }
            );

            // prevent form from being submitted 
            e.preventDefault();
            e.stopPropagation();
        });
    </script>
{/if}
