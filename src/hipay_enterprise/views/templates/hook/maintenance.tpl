<div class="row">
    <div class="col-lg-6">
        <div class="panel">
            <div class="panel-heading">
                <i class="icon-credit-card"></i>
                {l s='Hipay Refund' mod='hipay_tpp'}
            </div>
            <div class="well hidden-print">
                <a style="position: relative; top: -200px;" id="hipay"></a>
                {include file='../admin/partial/refund.partial.tpl'}
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="panel">
            <div class="panel-heading">
                <i class="icon-credit-card"></i>
                {l s='Hipay Capture' mod='hipay_tpp'}
            </div>
            <div class="well hidden-print">
                {include file='../admin/partial/capture.partial.tpl'}
            </div>
        </div>
    </div>
</div>                          

<script>
    $("#hipay_refund_type").change(function () {
        if ($(this).val() == "complete") {
            $("#block-refund-amount").hide();
        } else {
            $("#block-refund-amount").show();
        }
    });

    $("#hipay_capture_type").change(function () {
        if ($(this).val() == "complete") {
            $("#block-capture-amount").hide();
        } else {
            $("#block-capture-amount").show();
        }
    });

</script>