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
    <legend>{l s='Manage Capture' mod='hipay_enterprise'}</legend>
    {if $HiPay_stillToCapture}
        <p class="alert alert-warning">
            {l s='The order has not been fully captured.' mod='hipay_enterprise'} <br/>
            {l s='To generate the invoice, you must capture the remaining amount due which will generate an invoice once the order full amount has been captured.' mod='hipay_enterprise'}
        </p>
    {/if}

    <div class="row">
        <label class="col-lg-4">{l s='Amount already captured' mod='hipay_enterprise'}</label><span
                class="badge {if $HiPay_capturedAmount > 0}badge-warning{else}badge-success{/if}">{displayPrice price=$HiPay_capturedAmount currency=$HiPay_id_currency}</span>
    </div>
    <div class="row">
        <label class="col-lg-4">{l s='Amount still to be captured' mod='hipay_enterprise'}</label><span
                class="badge {if $HiPay_stillToCapture > 0}badge-warning{else}badge-success{/if}">
                {displayPrice price=$HiPay_stillToCapture currency=$HiPay_id_currency}</span>
    </div>
    <p class="help-block">
        <sup>*</sup> {l s='Amount will be updated once the capture will be confirmed by HiPay Enterprise' mod='hipay_enterprise'}
    </p>

    <form action="{$HiPay_captureLink}" method="post" id="hipay_capture_form" class="">
        <input type="hidden" name="id_order" value="{$HiPay_orderId}"/>
        <input type="hidden" name="id_emp" value="{$HiPay_employeeId}"/>
        <input type="hidden" name="token" value="{$HiPay_tokenCapture}"/>
        <div class="form-group">
            <label class="col-lg-4" for="hipay_capture_type">{l s='Capture type' mod='hipay_enterprise'}</label>
            {if $HiPay_canPartiallyCapture}
            <select id="hipay_capture_type" name="hipay_capture_type" class="col-lg-3">
                {if !$HiPay_partiallyCaptured }
                    <option value="complete">{l s='Complete' mod='hipay_enterprise'}</option>
                {/if}
                <option value="partial">{l s='Partial' mod='hipay_enterprise'}</option>
            </select>
            {else}
                <b>{l s='Complete' mod='hipay_enterprise'}</b><input type="hidden" id="hipay_capture_type" name="hipay_capture_type" value="complete" />
            {/if}
        </div>

        <div id="block-capture-amount" {if !$HiPay_partiallyCaptured }style="display:none;" {/if}
             class="bloc-actions-hipay form-group">
            {if !$HiPay_basket}
                <label class="control-label "
                       for="hipay_capture_amount">{l s='Capture amount' mod='hipay_enterprise'}</label>
                <input type="text" name="hipay_capture_amount" value="{$HiPay_stillToCapture}"/>
            {else}
                <table class="table table-item-hipay">
                    <thead>
                    <tr>
                        <th>{l s='Reference' mod='hipay_enterprise'}</th>
                        <th>{l s='Product name' mod='hipay_enterprise'}</th>
                        <th>{l s='Unit price' mod='hipay_enterprise'}</th>
                        <th>{l s='Captured' mod='hipay_enterprise'}</th>
                        <th>{l s='Qty to be captured' mod='hipay_enterprise'}</th>
                    </tr>
                    </thead>
                    {foreach $HiPay_products as $item}
                    
                        {assign var="itemId" value=($item["id_product"]|cat:$item["product_attribute_id"])|intval}

                        {if !empty($HiPay_capturedItems) && isset($HiPay_capturedItems[$itemId])}
                            {assign var="remainQty" value=$item["product_quantity"] - $HiPay_capturedItems[$itemId]["quantity"]}
                        {else}
                            {assign var="remainQty" value=$item["product_quantity"]}
                        {/if}
                        <tr>
                            <td>
                                {$item["reference"]}
                            </td>
                            <td>
                                <input type="hidden" {if $remainQty == 0} disabled {/if}
                                       name="hipaycapture[{$itemId}]"
                                       value="{$itemId}"/>{$item["product_name"]}
                            </td>
                            <td>
                                {displayPrice price=$item.product_price_wt currency=$HiPay_id_currency}
                            </td>
                            <td>
                                {if !empty($HiPay_capturedItems) && isset($HiPay_capturedItems[$itemId])}
                                    <span class="badge {if $remainQty == 0}badge-success{else}badge-warning{/if}">
                                            {$HiPay_capturedItems[$itemId]["quantity"]}
                                        </span>
                                {else}
                                    <span class="badge badge-warning">0</span>
                                {/if}
                                {if !empty($HiPay_capturedItems) && isset($HiPay_capturedItems[$itemId])}
                                    <span class="badge {if $remainQty == 0}badge-success{else}badge-warning{/if}">
                                            {displayPrice price=$HiPay_capturedItems[$itemId]["amount"] currency=$HiPay_id_currency}
                                        </span>
                                {else}
                                    <span class="badge badge-warning">{displayPrice price=0 currency=$HiPay_id_currency}</span>
                                {/if}
                            </td>
                            <td>
                                <div class="col-lg-6 input-group">
                                    {if $remainQty > 0}
                                        <input data-unit-price="{$item.unit_price_tax_incl}"
                                               data-id="{$itemId}"
                                               class="good-selector-capture" id="good-selector-{$itemId}"
                                               name="hipaycapture[{$itemId}]" type="number" min="0"
                                               max="{$remainQty}" name="" value="0">
                                        <div class="input-group-addon">/ {$remainQty}</div>
                                    {else}
                                        <span class="badge badge-danger">0</span>
                                    {/if}
                                </div>
                            </td>
                        </tr>
                    {/foreach}
                    <tfoot>
                    <tr>
                        <td></td>
                        <td>{l s='Shipping' mod='hipay_enterprise'}</td>
                        <td>
                                     <span>
                                         {displayPrice price=$HiPay_amountFees currency=$HiPay_id_currency}
                                         <span>
                        </td>
                        <td>
                            {if $HiPay_shippingCost > 0 }
                                {if !$HiPay_capturedFees}
                                    <input id="capture-fee" data-amount="{$HiPay_amountFees}" type="checkbox"
                                           name="hipay_capture_fee">
                                    {l s='Capture fee(s)' mod='hipay_enterprise'}
                                {else}
                                    <span class="badge badge-success">Captured</span>
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
                                {if !$HiPay_wrapping.captured}
                                    <input id="capture-wrapping" data-amount="{$HiPay_wrapping.value}" type="checkbox"
                                           name="hipay_capture_wrapping">
                                    {l s='Capture Wrapping gift' mod='hipay_enterprise'}
                                {else}
                                    <span class="badge badge-success">{l s='Captured'  mod='hipay_enterprise'}</span>
                                {/if}
                            </td>
                            <td></td>
                        </tr>
                    {/if }
                    {if !empty($HiPay_discount)}
                        <tr>
                            <td></td>
                            <td>{l s='Discount' mod='hipay_enterprise'} {$HiPay_discount.name}</td>
                            <td>
                                         <span>
                                             {displayPrice price=-1*$HiPay_discount.value currency=$HiPay_id_currency}
                                             <span>
                            </td>
                            <td>
                                {if !$HiPay_capturedDiscounts}
                                    <input id="capture-discount" data-amount="{$HiPay_discount.value}" type="checkbox"
                                           name="hipay_capture_discount">
                                    {l s='Capture Discount' mod='hipay_enterprise'}
                                    <input type="hidden" name="capture-discount-amount" value="{$HiPay_discount.value}"/>
                                {else}
                                    <span class="badge badge-success">{l s='Captured'  mod='hipay_enterprise'}</span>
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
                            <span id="total-capture"> </span>
                            <input type="hidden" id="total-capture-input" name="total-capture-input" value="0.00"/>
                        </td>
                    </tr>
                    </tfoot>
                </table>
            {/if}
        </div>
        <p style="display:none;" id="danger-js" class="alert alert-danger"></p>
        <div class="form-group">
            <button type="submit" name="{if !$HiPay_basket}hipay_capture_submit{else}hipay_capture_basket_submit{/if}"
                    class="btn btn-primary pull-right">
                {l s='Capture' mod='hipay_enterprise'}
            </button>
        </div>
    </form>
</fieldset>

<script>
    $(document).ready(function () {
        $("#hipay_capture_form").submit(function () {

            if (checkCaptureAmount()) {
                msgConfirmation = '{l s='Are-you sure to capture for this order ?' mod='hipay_enterprise'}';
                if (!confirm(msgConfirmation)) {
                    return false;
                }
            } else {
                return false;
            }

        });

        var currencySign = "€";
        {if $HiPay_capturedDiscounts}
        var capturedDiscount = true;
        {else}
        var capturedDiscount = false;
        {/if}

        var stillToCapture = {$HiPay_stillToCapture};

        updatePrice();

        $(".good-selector-capture").on('keyup change click', function () {
            updatePrice();
        });

        $("#capture-fee").click(function () {
            updatePrice();
        });

        $("#capture-discount").click(function () {
            updatePrice();
        });

        $("#capture-wrapping").click(function () {
            updatePrice();
        });

        function updatePrice() {
            var items = [];
            $(".good-selector-capture").each(function () {
                var item = {
                    id: $(this).data('id'),
                    qty: $(this).val()
                };
                items.push(item);
            });

            $.post('{$HiPay_ajaxCalculatePrice}&ajax=1&action=CalculatePrice',
                {
                    "captureRefundFee": $("#capture-fee").is(":checked"),
                    "captureRefundDiscount": $("#capture-discount").is(":checked"),
                    "captureRefundWrapping": $("#capture-wrapping").is(":checked"),
                    "items": items,
                    "operation": "capture",
                    "cartId": {$HiPay_cartId},
                    "orderId": {$HiPay_orderId}
                },
                function (response) {
                    if (response.amount) {
                        amount = response.amount.toFixed(2);
                        remain = stillToCapture - amount;
                        if (remain.toFixed(2) == -0.01) {
                            amount = amount - 0.01;
                        }

                        $("#total-capture").text(amount + " " + currencySign);
                        $("#total-capture-input").val(amount);
                    }
                }
            );
        }

        function checkCaptureAmount() {

            if ($("#hipay_capture_type").val() == "complete") {
                return true;
            }

            if (parseFloat($("#total-capture-input").val()) <= 0) {
                displayError("{l s='Capture amount must be greater than zero.' mod='hipay_enterprise'}");
                return false;
            }

            if (capturedDiscount) {
                return true;
            }

            if (parseFloat($("#total-capture-input").val()) > stillToCapture + 0.01) {
                displayError("{l s='Capture amount must be lower than the amount still to be captured.' mod='hipay_enterprise'}");
                return false;
            }

            if ($("#capture-discount").val() != null && !$("#capture-discount").is(":checked") && (stillToCapture - parseFloat($("#total-capture-input").val()) <= parseFloat($("#capture-discount").data("amount")))) {
                displayError("{l s='You must capture discount because next capture amount will be lower than total discount amount.' mod='hipay_enterprise'}");
                return false;
            }

            return true;
        }

        function displayError(text) {
            $("#danger-js").text(text);
            $("#danger-js").show();
        }

    });

</script>
