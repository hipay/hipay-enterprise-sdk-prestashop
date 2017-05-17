<form enctype="application/x-www-form-urlencoded" class="form-horizontal" method="post" name="tokenizerForm" id="tokenizerForm" autocomplete="off">
    {include file="$hipay_enterprise_tpl_dir/paymentForm.tpl"}
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