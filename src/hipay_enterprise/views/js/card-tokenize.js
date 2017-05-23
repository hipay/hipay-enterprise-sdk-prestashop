$('#payment-confirmation > .ps-shown-by-js > button').click(function (e) {

    var myPaymentMethodSelected = $('.payment-options').find("input[data-module-name='hipay_enterprise']").is(':checked');

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

        HiPay.setTarget('stage'); // default is production/live

        HiPay.setCredentials(api_tokenjs_username_sandbox, api_tokenjs_password_publickey_sandbox);

        HiPay.create(params,
                function (result) {
                    // The card has been successfully tokenized
                    token = result.token;
                    brand = result.brand;
                    pan = result.pan;

                    // set tokenization response
                    $('#card-token').val(token);
                    $('#card-brand').val(brand);
                    $('#card-pan').val(brand);

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