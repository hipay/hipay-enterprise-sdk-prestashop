{capture name=path}{l s='HiPay payment.' mod='hipay_enterprise'}{/capture}
<h2>{l s='Order summary' mod='hipay_enterprise'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if $nbProducts <= 0}
    <p class="warning">{l s='Your shopping cart is empty.' mod='hipay_enterprise'}</p>
{else}
    <h3>{l s='HiPay payment.' mod='hipay_enterprise'}</h3>
    <form enctype="application/x-www-form-urlencoded"
          action="{$link->getModuleLink('hipay_enterprise', 'redirect', [], true)|escape:'html'}"
          class="form-horizontal col-lg-6 col-lg-offset-3" method="post" name="tokenizerForm" id="tokenizerForm"
          autocomplete="off">
        <div class="order_carrier_content box">
            {if $confHipay.payment.global.card_token}
                {if $savedCC}
                    <h2 class="page-subheading">{l s='Pay with a saved credit or debit card' mod='hipay_enterprise'}</h2>
                    <div id="error-js-oc" style="display:none" class="alert alert-danger">
                        <p>There is 1 error</p>
                        <ol>
                            <li class="error-oc"></li>
                        </ol>
                    </div>
                    {if $status_error_oc == '400'}
                        <div class="alert alert-danger">
                            <p>There is 1 error</p>
                            <ol>
                                <li>{l s='The request was rejected due to a validation error. Please verify the card details you entered.' mod='hipay_enterprise'}</li>
                            </ol>
                        </div>
                    {/if}

                    {foreach $savedCC as $cc}
                        <div class="">
                            <label>
                                <input type="radio" name="ccTokenHipay" id="ccTokenHipay" value="{$cc.token}"/>
                                {$cc.pan} ({"%02d"|sprintf:$cc.card_expiry_month} / {$cc.card_expiry_year})
                                - {$cc.card_holder} <img src="{$this_path_ssl}/views/img/{$cc.brand|lower}_small.png"/>
                            </label>
                        </div>
                        <br/>
                    {/foreach}
                    <button id="pay-button-one-click" type="submit" name="processCarrierHipay"
                            class="button btn btn-default standard-checkout button-medium col-lg-12 col-md-12 col-xs-12"
                            style="">
                        <span>
                            {l s='Pay' mod='hipay_enterprise'}
                        </span>
                    </button>
                {/if}
            {/if}

            <h2 class="page-subheading">{l s='Pay by credit or debit card' mod='hipay_enterprise'}</h2>
            {include file="$hipay_enterprise_tpl_dir/../front/partial/paymentError.tpl"}
            <div class="control-group">
                <p><strong>{l s='Amount to pay ' mod='hipay_enterprise'}:</strong> {$amount} {$currency->iso_code} </p>

                <div style="clear: both;"></div>
            </div>
            <br/>
            {include file="$hipay_enterprise_tpl_dir/paymentForm.tpl"}
            <br/>
            <div class="checkbox">
                <label for="newsletter">
                    <div class="checker" id="uniform-newsletter">
                        <span class="">
                            <input type="checkbox" name="saveTokenHipay" checked>
                        </span>
                    </div>
                    {l s='Save credit card (One click payment)' mod='hipay_enterprise'}
                </label>
            </div>
            <br/>
            <button id="pay-button" type="submit" name="processCarrierHipay"
                    class="button btn btn-default standard-checkout button-medium col-lg-12 col-md-12 col-xs-12"
                    style="">
                <span>
                    {l s='Pay' mod='hipay_enterprise'}
                </span>
            </button>
        </div>
    </form>
    <p id="payment-loader-hp" style='text-align: center; display:none;'>
        <strong>{l s='Your payment is being processed. Please wait.' mod='hipay_enterprise'}</strong> <br/>
        <img src="{$this_path_ssl}/views/img/loading.gif">
    </p>
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

        $("#pay-button-one-click").click(function (e) {
            console.log($('input[name=ccTokenHipay]:checked').length);
            // prevent form from being submitted 
            e.preventDefault();
            e.stopPropagation();


            if ($('input[name=ccTokenHipay]:checked').length) {
                // at least one of the radio buttons was checked
                $('#tokenizerForm').hide();
                $('#payment-loader-hp').show();
                $("#pay-button-one-click").prop('disabled', true);
                $("#pay-button").prop('disabled', true);

                $("#tokenizerForm").submit();
                return true; // allow whatever action would normally happen to continue
            } else {
                // no radio button was checked
                $("#error-js-oc").show();
                $(".error-oc").text("{l s='You must choose one of the saved card.' mod='hipay_enterprise'}");
                return false; // stop whatever action would normally happen
            }

        });

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
                    $('#tokenizerForm').hide();
                    $('#payment-loader-hp').show();
                    $("#pay-button-one-click").prop('disabled', true);
                    $("#pay-button").prop('disabled', true);

                    // The card has been successfully tokenized
                    token = result.token;
                    if (result.hasOwnProperty('domestic_network')) {
                        brand = result.domestic_network;
                    } else {
                        brand = result.brand;
                    }
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
                    // An error occurred
                    $("#error-js").show();
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
