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

    <form enctype="application/x-www-form-urlencoded" action="{$link->getModuleLink('hipay_enterprise', 'redirect', [], true)|escape:'html'}" class="form-horizontal col-lg-6 col-lg-offset-3" method="post" name="tokenizerForm" id="tokenizerForm" autocomplete="off">
        <div class="order_carrier_content box">
            <h1 class="page-subheading">{l s="Pay by credit or debit card" mod="hipay_enterprise"}</h1>
            <div class="control-group">
                <p><strong>{l s='Amount to pay ' mod='hipay_enterprise'}:</strong> {$amount} {$currency->iso_code} </p>

                <div style="clear: both;"></div>
            </div>
            <br />
            {include file="$hipay_enterprise_tpl_dir/paymentForm.tpl"}
            <br/>
            <button id="pay-button" type="submit" name="processCarrier" class="button btn btn-default standard-checkout button-medium col-lg-12 col-md-12 col-xs-12" style="">
                <span>
                    {l s='Pay' mod='hipay_tpp'}
                </span>
            </button>

        </div>
    </form>
    <script>
        {if $confHipay.account.global.sandbox_mode}
            var api_tokenjs_mode = 'stage';
            var api_tokenjs_username = '{$confHipay.account.sandbox.api_tokenjs_username_sandbox}';
            var api_tokenjs_password_publickey = '{$confHipay.account.sandbox.api_tokenjs_password_publickey_sandbox}';
        {else}
            var api_tokenjs_mode = 'production';
            var api_tokenjs_username = '{$confHipay.account.production.api_tokenjs_username_production}';
            var api_tokenjs_password_publickey = '{$confHipay.account.production.api_tokenjs_password_publickey_production}';
        {/if}
            
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


            HiPay.setTarget(api_tokenjs_mode); // default is production/live

            HiPay.setCredentials(api_tokenjs_username, api_tokenjs_password_publickey);

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
                        $('#card-holder').val($('#the-card-name-id').val());
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
