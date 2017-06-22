{if $error }
    {if $error == "ok"}
        <p class="alert alert-success">{l s="Request successfully sent"}</p>
    {else}
        <p class="alert alert-danger">{$error}</p>
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
        <p><b>{l s='Amount already captured' } :</b> <span class="badge badge-success">{$refundableAmountDisplay}</span></p>
        <p><b>{l s='Amount still to be captured' } :</b> <span class="badge badge-success">{$stillToCaptureDisplay}</span></p>
        <p class="help-block"><sup>*</sup> {l s='Amount will be updated once the refund will be confirmed by HiPay Enterprise'}</p>
        <form action="{$captureLink}" method="post" id="hipay_capture_form" class="form-horizontal">
            <input type="hidden" name="id_order" value="{$orderId}" />
            <input type="hidden" name="id_emp" value="{$employeeId}" />
            <input type="hidden" name="token" value="{$tokenCapture}" />
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
                {if !$basket}
                    <label class="control-label " for="hipay_capture_amount">{l s='Capture amount'}</label>
                    <input type="text" name="hipay_capture_amount" value="{$stillToCapture}" />
                {else}
                    <table class="table">
                        <tr>
                            <th>{l s="Product name"}</th>
                            <th>{l s="Already captured"}</th>
                            <th>{l s="Quantity remaining"}</th>
                        </tr>
                        {foreach $products as $item}
                            {if !empty($capturedItems) && isset($capturedItems[$item["product_id"]])}
                                {assign var="remainQty" value={$item["product_quantity"]} - $capturedItems[$item["product_id"]]["quantity"]}
                            {else}
                                {assign var="remainQty" value=$item["product_quantity"]}
                            {/if}
                            <tr >
                                <td>
                                    <input type="hidden" {if $remainQty == 0} disabled {/if} name="hipaycapture[{$item["product_id"]}]" value="{$item["product_id"]}"/>{$item["product_name"]}
                                </td>
                                <td>
                                    {if !empty($capturedItems) && isset($capturedItems[$item["product_id"]])}
                                        <span class="badge {if $remainQty == 0}badge-success{else}badge-warning{/if}">
                                            {$capturedItems[$item["product_id"]]["quantity"]} ({$capturedItems[$item["product_id"]]["amount"]})
                                        </span>
                                    {else}
                                        <span class="badge badge-warning">
                                            0
                                        </span>
                                    {/if}
                                </td>
                                <td >
                                    {if $remainQty > 0}
                                        <div class="col-lg-6 input-group">
                                            <input name="hipaycapture[{$item["product_id"]}]" type="number" min="0" max="{$remainQty}" name="" value="0">
                                            <div class="input-group-addon">/ {$remainQty}</div>
                                        </div>
                                    {/if}
                                </td>
                            </tr>
                        {/foreach}
                    </table>
                {/if}
            </div>
            <div class="form-group">
                <button type="submit"  name="{if !$basket}hipay_capture_submit{else}hipay_capture_basket_submit{/if}" class="btn btn-primary pull-right" >
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