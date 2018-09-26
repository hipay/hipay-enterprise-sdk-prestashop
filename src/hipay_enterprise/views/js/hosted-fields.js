$(document).ready(function () {
    $("#card-number").focus(function () {
        $('#radio-no-token').prop('checked', true);
    });

    $('#radio-no-token').change(function () {
        $('#credit-card-group').collapse('show');
    });

    $('.radio-with-token').change(function () {
        $('#credit-card-group').collapse('hide');
    });

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

            hipayHF.createToken()
                .then(function (response) {
                        if (isCardTypeOk(response)) {
                            displayLoadingDiv();
                            afterTokenization(response);
                            //submit the form
                            form.submit();
                            return true;
                        } else {
                            $("#error-js").show();
                            $("#error-js").text(activatedCreditCardError);
                            return false;
                        }
                    },
                    function (error) {
                        $("#error-js").show();
                        $("#error-js").text(error);
                    }
                );
        }
    });
});
var hipayHF;

document.addEventListener('DOMContentLoaded', initHostedFields, false);

function initHostedFields() {

    var hipay = HiPay({
        username: api_tokenjs_username,
        password: api_tokenjs_password_publickey,
        environment: api_tokenjs_mode,
        lang: lang
    });

    var config = {
        selector: "hipayHF-container",
        multi_use: oneClick,
        fields: {
            cardHolder: {
                selector: "hipayHF-card-holder"
            },
            cardNumber: {
                selector: "hipayHF-card-number"
            },
            expiryDate: {
                selector: "hipayHF-date-expiry"
            },
            cvc: {
                selector: "hipayHF-cvc",
                helpButton: true,
                helpSelector: "hipayHF-help-cvc"
            }
        },
        styles: {
            base: style.base,
            invalid: {
                color: "#D50000",
                caretColor: "#D50000"
            }
        }
    };

    hipayHF = hipay.create("card", config);

    hipayHF.on("change", function (data) {
        handleErrorhipayHF(data.valid, data.error);
    });

}

// Function to call when card change
// It display/hide the error message
function handleErrorhipayHF(valid, error) {
    if (error) {
        $("#error-js").show();
    } else {
        $("#error-js").hide();
    }

    document.getElementById("error-js").innerHTML = error
        ? '<i class="material-icons"></i>' + error
        : error;
}
