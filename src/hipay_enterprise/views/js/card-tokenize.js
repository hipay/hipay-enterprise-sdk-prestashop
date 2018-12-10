/**
 * HiPay Enterprise SDK Prestashop
 *
 * 2017 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright 2017 HiPay
 * @license   https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 */

$(document).ready(function () {
    $(".ioBB").val($("#ioBB").val());
    if ($("#credit-card-group").length) {
        $('<a href="#" class="tooltips">' + i18nCVCLabelLocal + '<span>' + i18nCVCTooltipLocal + '</span></a>').insertAfter('#cvc');
        $('.card-js #card-number').attr('placeholder', i18nCardNumberLocal);
        $('.card-js #the-card-name-id').attr('placeholder', i18nNameOnCardLocal);
        $('.card-js .expiry').attr('placeholder', i18nDateLocal);
    }
});

$("#card-number").focus(function () {
    $('#radio-no-token').prop('checked', true);
});

$('#radio-no-token').change(function () {
    $('#credit-card-group').collapse('show');
});

$('.radio-with-token').change(function () {
    $('#credit-card-group').collapse('hide');
});

function checkPaymentDate() {
    if ($(".expiry").val() === null || $(".expiry").val() === "") {
        $(".expiry").addClass("error-input-hp");
        var pInsert = $("<span>" + i18nFieldIsMandatory + "</span>");
        $(".expiry").after(pInsert);
        pInsert.addClass("error-text-hp");
        return false;
    }
    return true;
}

$("#tokenizerForm").submit(function (e) {
    var form = this;
    // prevent form from being submitted 
    e.preventDefault();
    e.stopPropagation();

    if (myPaymentMethodSelected) {
        if (isOneClickSelected()) {
            oneClickSelected(form);
            return true; // allow whatever action that would normally happen to continue
        }

        var formErrors = !hiPayInputControl.checkControl("cc");
        formErrors = !checkPaymentDate() || formErrors;

        if (formErrors) {
            return false;
        }
        var multiUse = 0;
        if ($("#saveTokenHipay").is(":checked")) {
            multiUse = 1;
        }


        //set param for Api call
        var params = {
            cardNumber: $("#card-number").val().replace(/ /g, ''),
            cvc: $("#cvc").val(),
            expiryMonth: $("select[name=expiry-month]").val(),
            expiryYear: $("select[name=expiry-year]").val(),
            cardHolder: $("#the-card-name-id").val(),
            multiUse: multiUse
        };

        var hipay = HiPay({
            username: api_tokenjs_username,
            password: api_tokenjs_password_publickey,
            environment: api_tokenjs_mode,
            lang: lang
        });


        hipay.tokenize(params).then(function (result) {

                if (isCardTypeOk(result)) {

                    clearCCForm();
                    displayLoadingDiv();
                    afterTokenization(result);

                    //submit the form
                    form.submit();

                    return true;
                } else {
                    $("#error-js").show();
                    $(".error").text(activatedCreditCardError);
                    return false;
                }

            }, function (errors) {
                displaySecureVaultErrors(errors);
                return false;
            }
        );

    }
});
