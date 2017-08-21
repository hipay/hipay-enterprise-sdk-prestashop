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
    {if $stillToCapture}
        <p class="alert alert-warning">
            {l s='The order has not been fully captured.' mod='hipay_enterprise'} <br/>
            {l s='To generate the invoice, you must capture the remaining amount due which will generate an invoice once the order full amount has been captured.' mod='hipay_enterprise'}
        </p>
    {/if}

    <div class="row">
        <label class="col-lg-4">{l s='Amount already captured' mod='hipay_enterprise'}</label><span
                class="badge {if $refundableAmountDisplay > 0}badge-warning{else}badge-success{/if}">{displayPrice price=$refundableAmountDisplay currency=$id_currency}</span>
    </div>
    <div class="row">
        <label class="col-lg-4">{l s='Amount still to be captured' mod='hipay_enterprise'}</label><span
                class="badge {if $stillToCaptureDisplay > 0}badge-warning{else}badge-success{/if}">
                {displayPrice price=$stillToCaptureDisplay currency=$id_currency}</span>
    </div>
    <p class="help-block">
        <sup>*</sup> {l s='Amount will be updated once the capture will be confirmed by HiPay Enterprise' mod='hipay_enterprise'}
    </p>

    <form action="{$captureLink}" method="post" id="hipay_capture_form" class="">
        <input type="hidden" name="id_order" value="{$orderId}"/>
        <input type="hidden" name="id_emp" value="{$employeeId}"/>
        <input type="hidden" name="token" value="{$tokenCapture}"/>
        <div class="form-group">
            <label class="col-lg-4" for="hipay_capture_type">{l s='Capture type' mod='hipay_enterprise'}</label>
            <select id="hipay_capture_type" name="hipay_capture_type" class="col-lg-3">
                {if !$partiallyCaptured }
                    <option value="complete">{l s='Complete' mod='hipay_enterprise'}</option>
                {/if}
                <option value="partial">{l s='Partial' mod='hipay_enterprise'}</option>
            </select>
        </div>

        <div id="block-capture-amount" {if !$partiallyCaptured }style="display:none;" {/if}
             class="bloc-actions-hipay form-group">
            {if !$basket}
                <label class="control-label "
                       for="hipay_capture_amount">{l s='Capture amount' mod='hipay_enterprise'}</label>
                <input type="text" name="hipay_capture_amount" value="{$stillToCapture}"/>
            {else}
                <table class="table table-item-hipay">
                    <thead>
                    <tr>
                        <th>{l s='Reference' mod='hipay_enterprise'}</th>
                        <th>{l s='Product name' mod='hipay_enterprise'}</th>
                        <th>{l s='Unit price' mod='hipay_enterprise'}</th>
                        <th>{l s='Captured' mod='hipay_enterprise'}</th>
                        <th>{l s='Qty to be capture' mod='hipay_enterprise'}</th>
                    </tr>
                    </thead>
                    {foreach $products as $item}
                        {if !empty($capturedItems) && isset($capturedItems[$item["product_id"]])}
                            {assign var="remainQty" value=$item["product_quantity"] - $capturedItems[$item["product_id"]]["quantity"]}
                        {else}
                            {assign var="remainQty" value=$item["product_quantity"]}
                        {/if}
                        <tr>
                            <td>
                                {$item["reference"]}
                            </td>
                            <td>
                                <input type="hidden" {if $remainQty == 0} disabled {/if}
                                       name="hipaycapture[{$item["product_id"]}]"
                                       value="{$item["product_id"]}"/>{$item["product_name"]}
                            </td>
                            <td>
                                {displayPrice price=$item.product_price_wt currency=$id_currency}
                            </td>
                            <td>
                                {if !empty($capturedItems) && isset($capturedItems[$item["product_id"]])}
                                    <span class="badge {if $remainQty == 0}badge-success{else}badge-warning{/if}">
                                            {$capturedItems[$item["product_id"]]["quantity"]}
                                        </span>
                                {else}
                                    <span class="badge badge-warning">0</span>
                                {/if}
                                {if !empty($capturedItems) && isset($capturedItems[$item["product_id"]])}
                                    <span class="badge {if $remainQty == 0}badge-success{else}badge-warning{/if}">
                                            {displayPrice price=$capturedItems[$item["product_id"]]["amount"] currency=$id_currency}
                                        </span>
                                {else}
                                    <span class="badge badge-warning">{displayPrice price=0 currency=$id_currency}</span>
                                {/if}
                            </td>
                            <td>
                                <div class="col-lg-6 input-group">
                                    {if $remainQty > 0}
                                        <input data-unit-price="{$item.unit_price_tax_incl}"
                                               class="good-selector-capture" id="good-selector-{$item["product_id"]}"
                                               name="hipaycapture[{$item["product_id"]}]" type="number" min="0"
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
                                         {displayPrice price=$amountFees currency=$id_currency}
                                         <span>
                        </td>
                        <td>
                            {if $shippingCost > 0 }
                                {if !$capturedFees}
                                    <input id="capture-fee" data-amount="{$amountFees}" type="checkbox"
                                           name="hipay_capture_fee">
                                    {l s='Capture fee(s)' mod='hipay_enterprise'}
                                {else}
                                    <span class="badge badge-success">Captured</span>
                                {/if}
                            {else}
                                {displayPrice price=0 currency=$id_currency}
                            {/if}
                        </td>
                        <td></td>
                    </tr>
                    {if !empty($discount)}
                        <tr>
                            <td></td>
                            <td>{l s='Discount' mod='hipay_enterprise'} {$discount.name}</td>
                            <td>
                                         <span>
                                             {displayPrice price=-1*$discount.value currency=$id_currency}
                                             <span>
                            </td>
                            <td>
                                {if !$capturedDiscounts}
                                    <input id="capture-discount" data-amount="{$discount.value}" type="checkbox"
                                           name="hipay_capture_discount">
                                    {l s='Capture Discount' mod='hipay_enterprise'}
                                    <input type="hidden" name="capture-discount-amount" value="{$discount.value}"/>
                                {else}
                                    <span class="badge badge-success">Captured</span>
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
            <button type="submit" name="{if !$basket}hipay_capture_submit{else}hipay_capture_basket_submit{/if}"
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
        {if $capturedDiscounts}
        var capturedDiscount = true;
        {else}
        var capturedDiscount = false;
        {/if}

        var stillToCapture = {$stillToCapture};

        updatePrice();

        $(".good-selector-capture").on('keyup change click', function () {
            updatePrice();
        });

        $("#capture-fee").click(function () {
            updatePrice();
        })

        $("#capture-discount").click(function () {
            updatePrice();
        })
        function updatePrice() {
            amount = 0;
            $(".good-selector-capture").each(function () {
                amount += parseFloat($(this).data('unit-price')) * parseFloat($(this).val());
            });
            if ($("#capture-fee").is(":checked")) {
                amount = amount + parseFloat($("#capture-fee").data("amount"));
            }
            if ($("#capture-discount").val() != null && $("#capture-discount").is(":checked")) {
                amount = amount - parseFloat($("#capture-discount").data("amount"));
            }
            //fixed round errors
            amount = amount.toFixed(2);
            dif = stillToCapture - amount;
            if (dif.toFixed(2) == -0.01) {
                amount = amount - 0.01;
            }

            $("#total-capture").text(amount + " " + currencySign);
            $("#total-capture-input").val(amount);
        };

        function checkCaptureAmount() {

            if ($("#hipay_capture_type option:selected").val() == "complete") {
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
        };

        function displayError(text) {
            $("#danger-js").text(text);
            $("#danger-js").show();
        }

    });

</script>
