{**
* 2017 HiPay
*
* NOTICE OF LICENSE
*
*
* @author    HiPay <support.wallet@hipay.com>
* @copyright 2017 HiPay
* @license   https://github.com/hipay/hipay-wallet-sdk-prestashop/blob/master/LICENSE.md
*
*}

    <fieldset>
        <legend>{l s='Manage refund' }</legend>
        <p><b>{l s='Amount that can be refunded' } :</b> <span
                    class="badge badge-success">{$refundableAmountDisplay}</span></p>
        <p class="help-block">
            <sup>*</sup> {l s='Amount will be updated once the refund will be confirmed by HiPay Enterprise'}</p>
        <form action="{$refundLink}" method="post" id="hipay_refund_form" class="form-horizontal ">
            <input type="hidden" name="id_order" value="{$orderId}"/>
            <input type="hidden" name="id_emp" value="{$employeeId}"/>
            <input type="hidden" name="token" value="{$tokenRefund}"/>
            <div class="form-group">
                <label class="col-lg-4" for="hipay_refund_type">{l s='Refund type'}</label>
                <select id="hipay_refund_type" name="hipay_refund_type" class="col-lg-3">
                    {if !$partiallyRefunded}
                        <option value="complete">{l s='Complete'}</option>
                    {/if}
                    <option value="partial">{l s='Partial'}</option>
                </select>
            </div>
            <div id="block-refund-amount" {if !$partiallyRefunded} style="display:none;" {/if} class="form-group bloc-actions-hipay">
                {if !$basket}
                    <label class="control-label " for="hipay_refund_amount">{l s='Refund amount'}</label>
                    <input type="text" name="hipay_refund_amount" value="{$refundableAmount}"/>
                {else}
                    <table class="table table-item-hipay">
                        <thead>
                            <tr>
                                <th>{l s='Reference'}</th>
                                <th>{l s='Product name'}</th>
                                <th>{l s='Refunded'}</th>
                                <th>{l s='Qty to be refund'}</th>
                            </tr>
                        </thead>
                        {foreach $products as $item}
                            {if empty($capturedItems) && !empty($refundedItems) && isset($refundedItems[$item["product_id"]])}
                                {assign var="remainQty" value=$item["product_quantity"] - $refundedItems[$item["product_id"]]["quantity"]}
                            {else if empty($capturedItems)}
                                {assign var="remainQty" value=$item["product_quantity"]}
                            {else if !empty($refundedItems) && isset($refundedItems[$item["product_id"]])}
                                {assign var="remainQty" value=$capturedItems[$item["product_id"]]["quantity"] - $refundedItems[$item["product_id"]]["quantity"]}
                            {else}
                                {assign var="remainQty" value=$capturedItems[$item["product_id"]]["quantity"]}
                            {/if}
                            <tr>
                                <td>
                                    {$item["reference"]}
                                </td>
                                <td>
                                    <input type="hidden" {if $remainQty == 0} disabled {/if}
                                           name="hipayrefund[{$item["product_id"]}]"
                                           value="{$item["product_id"]}"/>{$item["product_name"]}
                                </td>
                                <td>
                                    {if !empty($refundedItems) && isset($refundedItems[$item["product_id"]])}
                                    <span class="badge {if $remainQty == 0}badge-success{else}badge-warning{/if}">
                                            {$refundedItems[$item["product_id"]]["quantity"]}
                                    {/if}
                                    {if !empty($refundedItems) && isset($refundedItems[$item["product_id"]])}
                                        <span class="badge {if $remainQty == 0}badge-success{else}badge-warning{/if}">
                                            {displayPrice price=$refundedItems[$item["product_id"]]["amount"] currency=$id_currency}
                                        </span>
                                    {else}
                                        <span class="badge badge-warning">{displayPrice price=0 currency=$id_currency}</span>
                                    {/if}
                                </td>
                                <td>
                                    {if $remainQty > 0}
                                        <div class="col-lg-6 input-group">
                                            <input name="hipayrefund[{$item["product_id"]}]" type="number" min="0"
                                                   max="{$remainQty}" name="" value="0">
                                            <div class="input-group-addon">/ {$remainQty}</div>
                                        </div>
                                    {/if}
                                </td>
                            </tr>

                        {/foreach}
                        <tfoot>
                        <tr>
                            <td></td>
                            <td>{l s='Shipping'}</td>
                            <td>
                                        <span>
                                            {displayPrice price=$amountFees currency=$id_currency}
                                            <span>
                            </td>
                            <td>
                                {if $shippingCost > 0 }
                                    {if ($capturedFees && !$refundedFees) || ($stillToCaptureDisplay <= 0 && !$refundedFees)}
                                        <input type="checkbox" name="hipay_refund_fee"> {l s='Refund fee(s)'}
                                    {else}
                                        <span class="badge badge-success">Refunded</span>
                                    {/if}
                                {else}
                                    {displayPrice price=0 currency=$id_currency}
                                {/if}
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                {/if}
            </div>
            <div class="form-group">
                {if !$totallyRefunded}
                    <button type="submit" name="{if !$basket}hipay_refund_submit{else}hipay_refund_basket_submit{/if}"
                            class="btn btn-primary pull-right">
                        {l s='Refund' }
                    </button>
                {/if}
            </div>
        </form>
    </fieldset>

<script>
    $(document).ready(function () {
        $("#hipay_refund_form").submit(function() {
            msgConfirmation = '{l s='Are-you sure to refund for this order ?'}';
            if (!confirm(msgConfirmation)) {
                return false;
            }
        });
    });
</script>
