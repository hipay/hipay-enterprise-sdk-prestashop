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
            <label for="hipay_refund_type">{l s='Refund type'  mod='hipay_enterprise'}</label>
            <select id="hipay_refund_type" name="hipay_refund_type">
                {if !$HiPay_partiallyRefunded}
                    <option value="complete">{l s='Complete' mod='hipay_enterprise'}</option>
                {/if}
                {if $HiPay_refundedAmountWithoutBasket == 0}
                    <option value="partial">{l s='Partial' mod='hipay_enterprise'}</option>
                {/if}
                <option value="partialWithoutBasket">{l s='Partial without basket' mod='hipay_enterprise'}</option>
            </select>
        </div>

        <div id="block-refund-amount" style="display:none;" class="form-group bloc-actions-hipay">
            <table class="table table-item-hipay refund-table">
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

                    {assign var="itemId" value=($item["id_product"]|cat:$item["product_attribute_id"])|intval}
                    {if empty($HiPay_capturedItems) && !empty($HiPay_refundedItems) && isset($HiPay_refundedItems[$itemId])}
                        {assign var="remainQty" value=$item["product_quantity"] - $HiPay_refundedItems[$itemId]["quantity"] }
                    {else if empty($HiPay_capturedItems) && empty($HiPay_refundedItems) }
                        {assign var="remainQty" value=$item["product_quantity"] - $item["product_quantity_refunded"] }
                    {else if empty($HiPay_capturedItems) && !empty($HiPay_refundedItems) &&  !isset($HiPay_refundedItems[$itemId])}
                        {assign var="remainQty" value=$item["product_quantity"] }
                    {else if empty($HiPay_capturedItems) || !isset($HiPay_capturedItems[$itemId]) }
                        {assign var="remainQty" value=0}
                    {else if !empty($HiPay_refundedItems) && isset($HiPay_refundedItems[$item["proproduct_attribute_idduct_id"]]) }
                        {assign var="remainQty" value=$HiPay_capturedItems[$itemId]["quantity"] - $HiPay_refundedItems[$itemId]["quantity"]}
                    {else}
                        {assign var="remainQty" value=$HiPay_capturedItems[$itemId]["quantity"]}
                    {/if}
                    <tr>
                        <td>
                            {$item["reference"]}
                        </td>
                        <td>
                            <input type="hidden" {if $remainQty == 0} disabled {/if}
                                    name="hipayrefund[{$itemId}]"
                                    value="{$itemId}"/>
                            {$item["product_name"]}
                        </td>
                        <td>
                            {displayPrice price=$item.product_price_wt currency=$HiPay_id_currency}
                        </td>
                        <td>
                            {if !empty($HiPay_refundedItems) && isset($HiPay_refundedItems[$itemId])}
                            <span class="badge {if $remainQty == 0}badge-success{else}badge-warning{/if}">
                                        {$HiPay_refundedItems[$itemId]["quantity"]}
                                {/if}
                                {if !empty($HiPay_refundedItems) && isset($HiPay_refundedItems[$itemId])}
                                    <span class="badge {if $remainQty == 0}badge-success{else}badge-warning{/if}">
                                        {displayPrice price=$HiPay_refundedItems[$itemId]["amount"] currency=$HiPay_id_currency}
                                    </span>
                                {else}
                                    <span class="badge badge-warning">{displayPrice price=0 currency=$HiPay_id_currency}</span>
                                {/if}
                        </td>
                        <td>
                            {if $remainQty > 0}
                                <div class="input-group refund-quantity-selector">
                                    <input data-unit-price="{$item.unit_price_tax_incl}"
                                            data-id="{$itemId}"
                                            class="good-selector-refund" name="hipayrefund[{$itemId}]"
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
        </div>

        <div id="block-refund-amount-without-basket" style="display:none;" class="form-group bloc-actions-hipay">
            <p id="warning-js" class="alert alert-warning">
                {l s='Refund without basket means that, from now on,
                refunds will be unbundled from products and stocks will no longer be managed.
                You will no longer be able to make classic partial refunds. '}
            </p>
            <table class="table table-item-hipay">
                <thead>
                <tr>
                    <th>{l s='Reference'  mod='hipay_enterprise'}</th>
                    <th>{l s='Product name'  mod='hipay_enterprise'}</th>
                    <th>{l s='Unit price' mod='hipay_enterprise'}</th>
                    <th>{l s='Already refunded'  mod='hipay_enterprise'}</th>
                </tr>
                </thead>
                {foreach $HiPay_products as $item}
                    {assign var="itemId" value=($item["id_product"]|cat:$item["product_attribute_id"])|intval}
                    {if empty($HiPay_capturedItems) && !empty($HiPay_refundedItems) && isset($HiPay_refundedItems[$itemId])}
                        {assign var="remainQty" value=$item["product_quantity"] - $HiPay_refundedItems[$itemId]["quantity"] }
                    {else if empty($HiPay_capturedItems) && empty($HiPay_refundedItems) }
                        {assign var="remainQty" value=$item["product_quantity"] - $item["product_quantity_refunded"] }
                    {else if empty($HiPay_capturedItems) && !empty($HiPay_refundedItems) &&  !isset($HiPay_refundedItems[$itemId])}
                        {assign var="remainQty" value=$item["product_quantity"] }
                    {else if empty($HiPay_capturedItems) || !isset($HiPay_capturedItems[$itemId]) }
                        {assign var="remainQty" value=0}
                    {else if !empty($HiPay_refundedItems) && isset($HiPay_refundedItems[$item["proproduct_attribute_idduct_id"]]) }
                        {assign var="remainQty" value=$HiPay_capturedItems[$itemId]["quantity"] - $HiPay_refundedItems[$itemId]["quantity"]}
                    {else}
                        {assign var="remainQty" value=$HiPay_capturedItems[$itemId]["quantity"]}
                    {/if}
                    <tr>
                        <td>
                            {$item["reference"]}
                        </td>
                        <td>
                            {$item["product_name"]}
                        </td>
                        <td>
                            {displayPrice price=$item.product_price_wt currency=$HiPay_id_currency}
                        </td>
                        <td>
                            {if !empty($HiPay_refundedItems) && isset($HiPay_refundedItems[$itemId])}
                                <span class="badge {if $remainQty == 0}badge-success{else}badge-warning{/if}">
                                    {$HiPay_refundedItems[$itemId]["quantity"]}
                            {/if}
                            {if !empty($HiPay_refundedItems) && isset($HiPay_refundedItems[$itemId])}
                                <span class="badge {if $remainQty == 0}badge-success{else}badge-warning{/if}">
                                    {displayPrice price=$HiPay_refundedItems[$itemId]["amount"] currency=$HiPay_id_currency}
                                </span>
                            {else}
                                <span class="badge badge-warning">
                                    {displayPrice price=0 currency=$HiPay_id_currency}
                                </span>
                            {/if}
                        </td>
                    </tr>
                {/foreach}
                <tfoot>
                    <tr>
                        <td></td>
                        <td>
                            {l s='Shipping'  mod='hipay_enterprise'}
                        </td>
                        <td>
                            <span>
                                {displayPrice price=$HiPay_amountFees currency=$HiPay_id_currency}
                            <span>
                        </td>
                        <td>
                            {if $HiPay_shippingCost > 0 }
                                {if !$HiPay_capturedFees && $HiPay_manualCapture}
                                    <span class="badge badge-warning">{l s='Not captured'  mod='hipay_enterprise'}</span>
                                {else}
                                    {if $HiPay_refundedFees}
                                        {displayPrice price=$HiPay_amountFees currency=$HiPay_id_currency}
                                    {else}
                                        {displayPrice price=0 currency=$HiPay_id_currency}
                                    {/if}
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
                            <td>
                                {l s='Wrapping gift' mod='hipay_enterprise'}
                            </td>
                            <td>
                                <span>
                                    {displayPrice price=$HiPay_wrapping.value currency=$HiPay_id_currency}
                                <span>
                            </td>
                            <td>
                                {if !$HiPay_wrapping.captured && $HiPay_manualCapture}
                                    <span class="badge badge-warning">
                                        {l s='Not captured'  mod='hipay_enterprise'}
                                    </span>
                                {else}
                                    <span>
                                        {if $HiPay_wrapping.refunded}
                                            {displayPrice price=$HiPay_wrapping.value currency=$HiPay_id_currency}
                                        {else}
                                            {displayPrice price=0 currency=$HiPay_id_currency}
                                        {/if}
                                    <span>
                                {/if}
                            </td>
                            <td></td>
                        </tr>
                    {/if }
                    {if not empty($HiPay_discount)}
                        <tr>
                            <td></td>
                            <td>
                                {l s='Discount' mod='hipay_enterprise'} {$HiPay_discount.name}
                            </td>
                            <td>
                                <span>
                                    {displayPrice price=-1*$HiPay_discount.value currency=$HiPay_id_currency}
                                <span>
                            </td>
                            <td>
                                {if !$HiPay_capturedDiscounts && $HiPay_manualCapture}
                                    <span class="badge badge-warning">{l s='Not captured'  mod='hipay_enterprise'}</span>
                                {else}
                                    {if $HiPay_refundedDiscounts}
                                        {displayPrice price=-1*$HiPay_discount.value currency=$HiPay_id_currency}
                                    {else}
                                        {displayPrice price=0 currency=$HiPay_id_currency}
                                    {/if}
                                {/if}
                            </td>
                            <td></td>
                        </tr>
                    {/if }
                    <tr>
                        <td></td>
                        <td>
                            {l s='Amount already refunded without basket' mod='hipay_enterprise'}
                        </td>
                        <td></td>
                        <td>
                            {displayPrice price=$HiPay_refundedAmountWithoutBasket currency=$HiPay_id_currency}
                        <td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            {l s='Total refunded' mod='hipay_enterprise'}
                        </td>
                        <td></td>
                        <td>
                            {displayPrice price=$HiPay_refundedAmount currency=$HiPay_id_currency}
                        </td>
                    </tr>
                </tfoot>
            </table>
            <label class="control-label"
                    for="hipay_refund_amount">{l s='Refund amount' mod='hipay_enterprise'}</label>
            <input type="text" id="hipay_refund_amount" name="hipay_refund_amount" value="{$HiPay_refundableAmount}"/>
        </div>
        <p style="display:none;" id="danger-js" class="alert alert-danger"></p>
        <div class="form-group">
            {if !$HiPay_totallyRefunded}
                <button id="submitButton" style="display:none;"  type="submit" name="hipay_refund_submit"
                        class="btn btn-primary pull-right">
                    {l s='Refund'  mod='hipay_enterprise'}
                </button>
                <button id="basketSubmitButton" style="display:none;"  type="submit" name="hipay_refund_basket_submit"
                        class="btn btn-primary pull-right">
                    {l s='Refund'  mod='hipay_enterprise'}
                </button>
            {/if}
        </div>
    </form>
</fieldset>

<script>
    $(document).ready(function () {

        handleRefundTypeDisplay($("#hipay_refund_type").find(":selected").val());
        addEvents();

        var currencySign = "€";
        var refundableAmount = {$HiPay_refundableAmount};
        {if $HiPay_refundedDiscounts}
            var refundedDiscount = true;
        {else}
            var refundedDiscount = false;
        {/if}
        updateRefundPrice();

        function updateRefundPrice() {
            var items = [];
            $(".good-selector-refund").each(function () {
                var item = {
                    id: $(this).data('id'),
                    qty: $(this).val()
                };
                items.push(item);
            });

            $("#total-refund").text("- " + currencySign);
            $("#total-refund-input").val(0);

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
                    if (response && typeof response === 'object' && 'amount' in response) {
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

            if ($("#hipay_refund_type option:selected").val() == "partial") {
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
            }

            if ($("#hipay_refund_type option:selected").val() == "partialWithoutBasket") {
                console.log('test', parseFloat($("#hipay_refund_amount").val()));
                if(parseFloat($("#hipay_refund_amount").val()) == 0 || $("#hipay_refund_amount").val() > refundableAmount){
                    displayError("{l s='Refund amount must be lower than the amount still to be refunded.' mod='hipay_enterprise'}");
                    return false
                }

                return true;
            }

            return false;
        };

        function addEvents() {
            $(".good-selector-refund").on('keyup change click', function () {
                setTimeout(() => {
                    updateRefundPrice();
                }, "500");
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

            $("#hipay_refund_type").change(function(e) {
                handleRefundTypeDisplay(e.target.value);
            })
        }

        function displayError(text) {
            $("#danger-js").text(text);
            $("#danger-js").show();
        }

        function handleRefundTypeDisplay(type){
            switch (type) {
                case 'complete':
                    $("#block-refund-amount").css("display", "none");
                    $("#block-refund-amount-without-basket").css("display", "none");
                    $("#basketSubmitButton").css("display", "none");
                    $("#submitButton").css("display", "block");
                    break;
                case 'partial':
                    $("#block-refund-amount-without-basket").css("display", "none");
                    $("#block-refund-amount").css("display", "block");
                    $("#submitButton").css("display", "none");
                    $("#basketSubmitButton").css("display", "block");
                    break;
                case 'partialWithoutBasket':
                    $("#block-refund-amount").css("display", "none");
                    $("#block-refund-amount-without-basket").css("display", "block");
                    $("#basketSubmitButton").css("display", "none");
                    $("#submitButton").css("display", "block");
                    break;
                default:
                    break;
            }
        }

    });
</script>
