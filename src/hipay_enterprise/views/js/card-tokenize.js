$(document).ready(function () {
    $(".ioBB").val($("#ioBB").val());
});

$('#payment-confirmation > .ps-shown-by-js > button').click(function (e) {

     e.preventDefault();
        e.stopPropagation();

    var myPaymentMethodSelected = $('.payment-options').find("input[data-module-name='credit_card']").is(':checked');

    if (myPaymentMethodSelected) {

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
    }
});