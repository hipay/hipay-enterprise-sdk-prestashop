{if $error }
    {if $error == "ok"}
        <p class="alert alert-success">{l s="Request successfully sent"}</p>
    {else}
        <p class="alert alert-danger">{$error}</p>
    {/if}
{/if}
{if $showRefund && $alreadyCaptured && $refundableAmount > 0}
    <fieldset>
        <legend>{l s='Refund this order' }</legend>
        <p><b>{l s='Amount that can be refunded' } :</b> <span class="badge badge-success">{$refundableAmountDisplay}</span></p>
        <p class="help-block"><sup>*</sup> {l s='Amount will be updated once the refund will be confirmed by HiPay Enterprise'}</p>
        <form action="{$refundLink}" method="post" id="hipay_refund_form" class="form-horizontal ">
            <input type="hidden" name="id_order" value="{$orderId}" />
            <input type="hidden" name="id_emp" value="{$employeeId}" />
            <input type="hidden" name="token" value="{$tokenRefund}" />
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
                {if !$basket}
                    <label class="control-label " for="hipay_refund_amount">{l s='Refund amount'}</label>
                    <input type="text" name="hipay_refund_amount" value="{$refundableAmount}" />
                {else}
                    <table class="table">
                        <tr>
                            <th>{l s="Product name"}</th>
                            <th>{l s="Already refunded"}</th>
                            <th>{l s="Quantity refundable"}</th>
                        </tr>
                        {foreach $products as $item}
                            {if !empty($capturedItems) && isset($capturedItems[$item["product_id"]])}
                                {if !empty($refundedItems) && isset($refundedItems[$item["product_id"]])}
                                    {assign var="remainQty" value=$capturedItems[$item["product_id"]]["quantity"] - $refundedItems[$item["product_id"]]["quantity"]}
                                {else}
                                    {assign var="remainQty" value=$capturedItems[$item["product_id"]]["quantity"]}
                                {/if}
                                <tr >
                                    <td>
                                        <input type="hidden" {if $remainQty == 0} disabled {/if} name="hipayrefund[{$item["product_id"]}]" value="{$item["product_id"]}"/>{$item["product_name"]}
                                    </td>
                                    <td>
                                        {if !empty($refundedItems) && isset($refundedItems[$item["product_id"]])}
                                            <span class="badge {if $remainQty == 0}badge-success{else}badge-warning{/if}">
                                                {$refundedItems[$item["product_id"]]["quantity"]} ({$refundedItems[$item["product_id"]]["amount"]})
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
                                                <input name="hipayrefund[{$item["product_id"]}]" type="number" min="0" max="{$remainQty}" name="" value="0">
                                                <div class="input-group-addon">/ {$remainQty}</div>
                                            </div>
                                        {/if}
                                    </td>
                                </tr>
                            {/if}
                        {/foreach}
                    </table>
                    <div class="checkbox">
                        {if $shippingCost > 0 } 
                            {if $capturedFees && !$refundedFees}
                                <label>
                                    <input type="checkbox" name="hipay_refund_fee" > {l s="Refund fee(s)"}
                                </label>
                            {else}
                                <span>{l s="Shipping refunded"}</span>
                            {/if}
                        {else}
                            <span>{l s="Shipping is free"}</span>
                        {/if}
                    </div>
                {/if}
            </div>
            <div class="form-group">
                <button type="submit"  name="{if !$basket}hipay_refund_submit{else}hipay_refund_basket_submit{/if}" class="btn btn-primary pull-right" >
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