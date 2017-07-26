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
        <legend>{l s='Refund this order'  mod='hipay_enterprise'}</legend>
        <p><b>{l s='Amount that can be refunded' mod='hipay_enterprise' } :</b> <span
                    class="badge badge-success">{$refundableAmountDisplay}</span></p>
        <p class="help-block">
            <sup>*</sup> {l s='Amount will be updated once the refund will be confirmed by HiPay Enterprise' mod='hipay_enterprise'}</p>
        <form action="{$refundLink}" method="post" id="hipay_refund_form" class="form-horizontal ">
            <input type="hidden" name="id_order" value="{$orderId}"/>
            <input type="hidden" name="id_emp" value="{$employeeId}"/>
            <input type="hidden" name="token" value="{$tokenRefund}"/>
            <div class="form-group ">
                <label class="control-label " for="hipay_refund_type">{l s='Refund type' mod='hipay_enterprise'}</label>
                <select id="hipay_refund_type" name="hipay_refund_type" class="form-control ">
                    {if !$partiallyRefunded}
                        <option value="complete">{l s='Complete' mod='hipay_enterprise'}</option>
                    {/if}
                    <option value="partial">{l s='Partial' mod='hipay_enterprise'}</option>
                </select>
            </div>
            <div id="block-refund-amount" {if !$partiallyRefunded} style="display:none;" {/if} class="form-group">
                {if !$basket}
                    <label class="control-label " for="hipay_refund_amount">{l s='Refund amount' mod='hipay_enterprise'}</label>
                    <input type="text" name="hipay_refund_amount" value="{$refundableAmount}"/>
                {else}
                    <table class="table">
                        <tr>
                            <th>{l s='Product name' mod='hipay_enterprise'}</th>
                            <th>{l s='Already refunded' mod='hipay_enterprise'}</th>
                            <th>{l s='Quantity refundable' mod='hipay_enterprise'}</th>
                        </tr>
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
                                    <input type="hidden" {if $remainQty == 0} disabled {/if}
                                           name="hipayrefund[{$item["product_id"]}]"
                                           value="{$item["product_id"]}"/>{$item["product_name"]}
                                </td>
                                <td>
                                    {if !empty($refundedItems) && isset($refundedItems[$item["product_id"]])}
                                        <span class="badge {if $remainQty == 0}badge-success{else}badge-warning{/if}">
                                            {$refundedItems[$item["product_id"]]["quantity"]}
                                            ({$refundedItems[$item["product_id"]]["amount"]})
                                        </span>
                                    {else}
                                        <span class="badge badge-warning">
                                            0
                                        </span>
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
                    </table>
                    <div class="checkbox">
                        {if $shippingCost > 0 }
                            {if ($capturedFees && !$refundedFees) || ($stillToCaptureDisplay <= 0 && !$refundedFees)}
                                <label>
                                    <input type="checkbox" name="hipay_refund_fee"> {l s='Refund fee(s)' mod='hipay_enterprise'}
                                </label>
                            {else}
                                <span>{l s='Shipping refunded' mod='hipay_enterprise'}</span>
                            {/if}
                        {else}
                            <span>{l s='Shipping is free' mod='hipay_enterprise'}</span>
                        {/if}
                    </div>
                {/if}
            </div>
            <div class="form-group">
                {if !$totallyRefunded}
                    <button type="submit" name="{if !$basket}hipay_refund_submit{else}hipay_refund_basket_submit{/if}"
                            class="btn btn-primary pull-right">
                        {l s='Refund'  mod='hipay_enterprise'}
                    </button>
                {/if}
            </div>
        </form>
    </fieldset>