<div class="row">

    <div class="col-lg-6">
        <div class="panel">
            <div class="panel-heading">
                <i class="icon-credit-card"></i>
                {l s='Hipay Refund' mod='hipay_tpp'}
            </div>
            <div class="well hidden-print">
                {if $error }
                    {if $error == "ok"}
                        <p class="alert alert-danger">Text</p>
                    {else}
                        <p class="alert alert-success">{$error}</p>
                    {/if}
                {/if}
                {if $showRefund && $alreadyCaptured && $refundableAmount > 0}
                    <fieldset>
                        <legend>{l s='Refund this order' }</legend>
                        <p><b>{l s='Amount that can be refunded' } :</b> <span class="badge badge-success">{$refundableAmountDisplay}</span></p>
                        <p class="help-block"><sup>*</sup> {l s='Amount will be updated once the refund will be confirmed by HiPay Enterprise'}</p>
                        <form action="#" method="post" id="hipay_refund_form" class="form-horizontal ">
                            <input type="hidden" name="id_order" value="" />
                            <input type="hidden" name="id_emp" value="" />
                            <input type="hidden" name="token" value="" />
                            <div class="form-group ">
                                <label class="control-label " for="hipay_refund_type">{l s='Refund type'}</label>
                                <select id="hipay_refund_type" name="hipay_refund_type" class="form-control ">
                                    {if !$partiallyRefunded}
                                        <option value="complete" >{l s="Complete"}</option>
                                    {/if}
                                    <option value="partial" >{l s="Partial"}</option>
                                </select>
                            </div>
                            <div id="block-refund-amount" {if !$partiallyRefunded} style="display:none;" {/if} class="form-group">
                                <label class="control-label " for="hipay_refund_amount">{l s='Refund amount'}</label>
                                <input type="text" name="hipay_refund_amount" value="{$refundableAmount}" />
                            </div>
                            <div class="form-group">
                                <button type="submit"  name="hipay_refund_submit" class="btn btn-primary pull-right" >
                                    {l s="Refund" }
                                </button>
                            </div>
                        </form>
                    </fieldset>
                {else}
                    <p class="alert alert-warning">
                        {l s="This order has already been fully refunded, cannot be refunded or waiting authorization for refund"} <br/>
                    </p>
                {/if}
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
                {if $error }
                    {if $error == "ok"}
                        <p class="alert alert-danger">Text</p>
                    {else}
                        <p class="alert alert-success">{$error}</p>
                    {/if}
                {/if}
                {if $showCapture && $stillToCapture > 0 && $manualCapture}
                    <fieldset>
                        <legend>{l s='Capture this order' }</legend>
                        {if $stillToCapture}
                            <p class="alert alert-warning">
                                {l s="The order has not been fully captured."} <br/>
                                {l s="To generate the invoice, you must capture the remaining amount due which will generate an invoice once the order full amount has been captured."} 
                            </p>
                        {/if}
                        <p><b>{l s='Amount already captured' } :</b> <span class="badge badge-success">{$refundableAmount}</span></p>
                        <p><b>{l s='Amount still to be captured' } :</b> <span class="badge badge-success">{$stillToCaptureDisplay}</span></p>
                        <p class="help-block"><sup>*</sup> {l s='Amount will be updated once the refund will be confirmed by HiPay Enterprise'}</p>
                        <form action="#" method="post" id="hipay_capture_form" class="form-horizontal">
                            <input type="hidden" name="id_order" value="" />
                            <input type="hidden" name="id_emp" value="" />
                            <input type="hidden" name="token" value="" />
                            <div class="form-group ">
                                <label class="control-label " for="hipay_capture_type">{l s='Capture type'}</label>
                                <select id="hipay_capture_type" name="hipay_capture_type" class="form-control ">
                                    {if !$partiallyCaptured }
                                        <option value="complete" >{l s="Complete"}</option>
                                    {/if}
                                    <option value="partial" >{l s="Partial"}</option>
                                </select>
                            </div>
                            <div id="block-capture-amount" {if !$partiallyCaptured }style="display:none;" {/if} class="form-group">
                                <label class="control-label " for="hipay_capture_amount">{l s='Capture amount'}</label>
                                <input type="text" name="hipay_capture_amount" value="{$stillToCapture}" />
                            </div>
                            <div class="form-group">
                                <button type="submit"  name="hipay_capture_submit" class="btn btn-primary pull-right" >
                                    {l s="Capture" }
                                </button>
                            </div>
                        </form>
                    </fieldset>
                {else}
                    <p class="alert alert-warning">
                        {l s="This order has already been fully captured, cannot be captured or waiting authorization for capture"} <br/>
                    </p>
                {/if}
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