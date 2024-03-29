{**
 * HiPay Enterprise SDK Prestashop
 *
 * 2017 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright 2017 HiPay
 * @license   https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 *}
<fieldset>
    <legend>{l s='Refund this order'  mod='hipay_enterprise'}</legend>
    <p><b>{l s='Amount that can be refunded' mod='hipay_enterprise' } :</b> <span class="badge badge-success">
                {displayPrice price=$HiPay_refundableAmount currency=$HiPay_id_currency}</span></p>
    <p class="help-block">
        <sup>*</sup> {l s='Amount will be updated once the refund will be confirmed by HiPay Enterprise' mod='hipay_enterprise'}
    </p>
    <form action="{$HiPay_refundLink}" method="post" id="hipay_refund_form" class="form-horizontal ">
        <input type="hidden" name="id_order" value="{$HiPay_orderId}"/>
        <input type="hidden" name="id_emp" value="{$HiPay_employeeId}"/>
        <input type="hidden" name="token" value="{$HiPay_tokenRefund}"/>
        <div class="form-group">
            <label class="col-lg-4" for="hipay_refund_type">{l s='Refund type'  mod='hipay_enterprise'}</label>
            <select id="hipay_refund_type" name="hipay_refund_type" class="col-lg-3">
                {if !$HiPay_partiallyRefunded}
                    <option value="complete">{l s='Complete' mod='hipay_enterprise'}</option>
                {/if}
                <option value="partial">{l s='Partial' mod='hipay_enterprise'}</option>
            </select>
        </div>
        <div id="block-refund-amount" {if !$HiPay_partiallyRefunded} style="display:none;" {/if}
             class="form-group bloc-actions-hipay">
            {if !$HiPay_basket}
                <label class="control-label "
                       for="hipay_refund_amount">{l s='Refund amount' mod='hipay_enterprise'}</label>
                <input type="text" name="hipay_refund_amount" value="{$HiPay_refundableAmount}"/>
            {else}
                <table class="table table-item-hipay">
                    <thead>
                    <tr>
                        <th>{l s='Reference'  mod='hipay_enterprise'}</th>
                        <th>{l s='Product name'  mod='hipay_enterprise'}</th>
                        <th>{l s='Unit price' mod='hipay_enterprise'}</th>
                        <th>{l s='Refunded'  mod='hipay_enterprise'}</th>
                        <th>{l s='Qty to be refunded'  mod='hipay_enterprise'}</th>
                    </tr>
                    </thead>
                    {foreach $HiPay_products as $item}
                        {if empty($HiPay_capturedItems) && !empty($HiPay_refundedItems) && isset($HiPay_refundedItems[$item["product_id"]])}
                            {assign var="remainQty" value=$item["product_quantity"] - $HiPay_refundedItems[$item["product_id"]]["quantity"]}
                        {else if empty($HiPay_capturedItems) && empty($HiPay_refundedItems) }
                            {assign var="remainQty" value=$item["product_quantity"] }
                        {else if empty($HiPay_capturedItems) && !empty($HiPay_refundedItems) &&  !isset($HiPay_refundedItems[$item["product_id"]])}
                            +                                {assign var="remainQty" value=$item["product_quantity"] }
                        {else if empty($HiPay_capturedItems) || !isset($HiPay_capturedItems[$item["product_id"]]) }
                            {assign var="remainQty" value=0}
                        {else if !empty($HiPay_refundedItems) && isset($HiPay_refundedItems[$item["product_id"]]) }
                            {assign var="remainQty" value=$HiPay_capturedItems[$item["product_id"]]["quantity"] - $HiPay_refundedItems[$item["product_id"]]["quantity"]}
                        {else}
                            {assign var="remainQty" value=$HiPay_capturedItems[$item["product_id"]]["quantity"]}
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
                                {displayPrice price=$item.product_price_wt currency=$HiPay_id_currency}
                            </td>
                            <td>
                                {if !empty($HiPay_refundedItems) && isset($HiPay_refundedItems[$item["product_id"]])}
                                <span class="badge {if $remainQty == 0}badge-success{else}badge-warning{/if}">
                                            {$HiPay_refundedItems[$item["product_id"]]["quantity"]}
                                    {/if}
                                    {if !empty($HiPay_refundedItems) && isset($HiPay_refundedItems[$item["product_id"]])}
                                        <span class="badge {if $remainQty == 0}badge-success{else}badge-warning{/if}">
                                            {displayPrice price=$HiPay_refundedItems[$item["product_id"]]["amount"] currency=$HiPay_id_currency}
                                        </span>
                                    {else}
                                        <span class="badge badge-warning">{displayPrice price=0 currency=$HiPay_id_currency}</span>
                                    {/if}
                            </td>
                            <td>
                                {if $remainQty > 0}
                                    <div class="col-lg-6 input-group">
                                        <input data-unit-price="{$item.unit_price_tax_incl}"
                                               data-id="{$item["product_id"]}"
                                               class="good-selector-refund" name="hipayrefund[{$item["product_id"]}]"
                                               type="number" min="0"
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
                        <td>{l s='Shipping'  mod='hipay_enterprise'}</td>
                        <td>
                                        <span>
                                            {displayPrice price=$HiPay_amountFees currency=$HiPay_id_currency}
                                            <span>
                        </td>
                        <td>
                            {if $HiPay_shippingCost > 0 }
                                {if !$HiPay_capturedFees && $HiPay_manualCapture}
                                    <span class="badge badge-warning">{l s='Not captured'  mod='hipay_enterprise'}</span>
                                {elseif ($HiPay_capturedFees && !$HiPay_refundedFees) || ($HiPay_stillToCapture <= 0 && !$HiPay_refundedFees)}
                                    <input id="refund-fee" data-amount="{$HiPay_amountFees}" type="checkbox"
                                           name="hipay_refund_fee">
                                    {l s='Refund fee(s)'  mod='hipay_enterprise'}
                                {else}
                                    <span class="badge badge-success">{l s='Refunded'  mod='hipay_enterprise'}</span>
                                {/if}
                            {else}
                                {displayPrice price=0 currency=$HiPay_id_currency}
                            {/if}
                        </td>
                        <td></td>
                    </tr>
                    {if $HiPay_wrappingGift}
                        <tr>
                            <td></td>
                            <td>{l s='Wrapping gift' mod='hipay_enterprise'}</td>
                            <td>
                                     <span>
                                         {displayPrice price=$HiPay_wrapping.value currency=$HiPay_id_currency}
                                         <span>
                            </td>
                            <td>
                                {if !$HiPay_wrapping.captured && $HiPay_manualCapture}
                                    <span class="badge badge-warning">{l s='Not captured'  mod='hipay_enterprise'}</span>
                                {elseif !$HiPay_wrapping.refunded}
                                    <input id="refund-wrapping" data-amount="{$HiPay_wrapping.value}" type="checkbox"
                                           name="hipay_refund_wrapping">
                                    {l s='Refund Wrapping gift' mod='hipay_enterprise'}
                                {else}
                                    <span class="badge badge-success">{l s='Refunded'  mod='hipay_enterprise'}</span>
                                {/if}
                            </td>
                            <td></td>
                        </tr>
                    {/if }
                    {if not empty($HiPay_discount)}
                        <tr>
                            <td></td>
                            <td>{l s='Discount' mod='hipay_enterprise'} {$HiPay_discount.name}</td>
                            <td>
                                     <span>
                                         {displayPrice price=-1*$HiPay_discount.value currency=$HiPay_id_currency}
                                         <span>
                            </td>
                            <td>
                                {if !$HiPay_capturedDiscounts && $HiPay_manualCapture}
                                    <span class="badge badge-warning">{l s='Not captured'  mod='hipay_enterprise'}</span>
                                {elseif !$HiPay_refundedDiscounts}
                                    <input id="refund-discount" data-amount="{$HiPay_discount.value}" type="checkbox"
                                           name="hipay_refund_discount">
                                    {l s='Refund Discount' mod='hipay_enterprise'}
                                {else}
                                    <span class="badge badge-success">{l s='Refunded'  mod='hipay_enterprise'}</span>
                                {/if}
                            </td>
                            <td></td>
                        </tr>
                    {/if }
                    <tr>
                        <td></td>
                        <td>{l s='Total' mod='hipay_enterprise'} </td>
                        <td></td>
                        <td></td>
                        <td>
                            <span id="total-refund"> </span>
                            <input type="hidden" id="total-refund-input" name="total-refund-input" value="0.00"/>
                        </td>
                    </tr>
                    </tfoot>
                </table>
            {/if}
        </div>
        <p style="display:none;" id="danger-js" class="alert alert-danger"></p>
        <div class="form-group">
            {if !$HiPay_totallyRefunded}
                <button type="submit" name="{if !$HiPay_basket}hipay_refund_submit{else}hipay_refund_basket_submit{/if}"
                        class="btn btn-primary pull-right">
                    {l s='Refund'  mod='hipay_enterprise'}
                </button>
            {/if}
        </div>
    </form>
</fieldset>

<script>
    $(document).ready(function () {

        $("#hipay_refund_form").submit(function (e) {
            if (checkRefundAmount()) {
                msgConfirmation = '{l s='Are-you sure to refund for this order ?' mod='hipay_enterprise'}';
                if (!confirm(msgConfirmation)) {
                    return false;
                }
            } else {
                return false;
            }
        });

        var currencySign = "€";
        var refundableAmount = {$HiPay_refundableAmount};
        {if $HiPay_refundedDiscounts}
        var refundedDiscount = true;
        {else}
        var refundedDiscount = false;
        {/if}
        updateRefundPrice();

        $(".good-selector-refund").on('keyup change click', function () {
            updateRefundPrice();
        });

        $("#refund-fee").click(function () {
            updateRefundPrice();
        });

        $("#refund-discount").click(function () {
            updateRefundPrice();
        });

        $("#refund-wrapping").click(function () {
            updateRefundPrice();
        });

        function updateRefundPrice() {
            var items = [];
            $(".good-selector-refund").each(function () {
                var item = {
                    id: $(this).data('id'),
                    qty: $(this).val()
                };
                items.push(item);
            });

            $.post('{$HiPay_ajaxCalculatePrice}&ajax=1&action=CalculatePrice',
                {
                    "captureRefundFee": $("#refund-fee").is(":checked"),
                    "captureRefundDiscount": $("#refund-discount").is(":checked"),
                    "captureRefundWrapping": $("#refund-wrapping").is(":checked"),
                    "items": items,
                    "operation": "refund",
                    "cartId": {$HiPay_cartId},
                    "orderId": {$HiPay_orderId}
                },
                function (response) {

                    if (response.amount) {
                        amount = response.amount.toFixed(2);
                        remain = refundableAmount - amount;
                        if (remain.toFixed(2) == -0.01) {
                            amount = amount - 0.01;
                        }

                        $("#total-refund").text(amount + " " + currencySign);
                        $("#total-refund-input").val(amount);
                    }
                }
            );
        }

        function checkRefundAmount() {

            if ($("#hipay_refund_type option:selected").val() == "complete") {
                return true;
            }

            if (parseFloat($("#total-refund-input").val()) <= 0) {
                displayError("{l s='Refund amount must be greater than zero.' mod='hipay_enterprise'}");
                return false;
            }

            if (refundedDiscount) {
                return true;
            }

            if (parseFloat($("#total-refund-input").val()) > refundableAmount + 0.01) {
                displayError("{l s='Refund amount must be lower than the amount still to be refunded.' mod='hipay_enterprise'}");
                return false;
            }

            if (!$("#refund-discount").is(":checked") && (refundableAmount - parseFloat($("#total-refund-input").val()) <= parseFloat($("#refund-discount").data("amount")))) {
                displayError("{l s='You must refund discount because next refund amount will be lower than total discount amount.' mod='hipay_enterprise'}");
                return false;
            }

            return true;
        };

        function displayError(text) {
            $("#danger-js").text(text);
            $("#danger-js").show();
        }

    });
</script>
