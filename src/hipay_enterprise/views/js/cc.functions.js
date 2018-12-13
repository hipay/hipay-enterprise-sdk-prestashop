function afterTokenization(result) {
    var token = result.token;
    var brand = result.payment_product;
    var pan = result.pan;
    var card_expiry_month = result.card_expiry_month;
    var card_expiry_year = result.card_expiry_year;
    var card_holder = result.card_holder;
    var issuer = result.issuer;
    var country = result.country;

    // set tokenization response
    $("#card-token").val(token);
    $("#card-brand").val(brand);
    $("#card-pan").val(pan);
    $("#card-holder").val(card_holder);
    $("#card-expiry-month").val(card_expiry_month);
    $("#card-expiry-year").val(card_expiry_year);
    $("#card-issuer").val(issuer);
    $("#card-country").val(country);


    return true;
}

function displayLoadingDiv() {
    $("#tokenizerForm").hide();
    $("#payment-loader-hp").show();
    $("#payment-confirmation > .ps-shown-by-js > button").prop("disabled", true);
}

function clearCCForm() {
    // we empty the form so we don't send credit card informations to the server
    $("#card-number").val("");
    $("#cvc").val("");
    $("input[name=expiry-month]").val("");
    $("input[name=expiry-year]").val("");
    $("#the-card-name-id").val("");
}

function isCardTypeOk(result) {
    return (activatedCreditCard.indexOf(result.payment_product) !== -1);
}

function displaySecureVaultErrors(errors) {
    // An error occurred
    $("#error-js").show();
    if (typeof errors.message != "undefined") {
        var message = i18nBadRequest;
        switch (errors.code) {
            case 416:
                message = i18nTokenisationError416;
                break;
        }
        $(".error").text(message);
    } else {
        $(".error").text(i18nBadRequest);
    }
}

function isOneClickSelected() {
    return $(".radio-with-token:checked").length;
}

function oneClickSelected(form) {
    // at least one of the radio buttons was checked
    $("#tokenizerForm").hide();
    $("#payment-loader-hp").show();
    $("#payment-confirmation > .ps-shown-by-js > button").prop("disabled", true);

    form.submit();
}
