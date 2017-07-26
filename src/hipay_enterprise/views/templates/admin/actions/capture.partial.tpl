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
        <legend>{l s='Manage Capture' }</legend>
        {if $stillToCapture}
            <p class="alert alert-warning">
                {l s='The order has not been fully captured.'} <br/>
                {l s='To generate the invoice, you must capture the remaining amount due which will generate an invoice once the order full amount has been captured.'}
            </p>
        {/if}
        <div class="row">
            <label class="col-lg-4">{l s='Amount already captured' }</label><span class="badge {if $refundableAmountDisplay > 0}badge-warning{else}badge-success{/if}">{$refundableAmountDisplay}</span>
        </div>
        <div class="row">
            <label class="col-lg-4">{l s='Amount still to be captured' }</label><span
                    class="badge {if $stillToCaptureDisplay > 0}badge-warning{else}badge-success{/if}">{$stillToCaptureDisplay}</span>
        </div>
        <p class="help-block">
            <sup>*</sup> {l s='Amount will be updated once the capture will be confirmed by HiPay Enterprise'}</p>
        <form action="{$captureLink}" method="post" id="hipay_capture_form" class="">
            <input type="hidden" name="id_order" value="{$orderId}"/>
            <input type="hidden" name="id_emp" value="{$employeeId}"/>
            <input type="hidden" name="token" value="{$tokenCapture}"/>
            <div class="form-group">
                <label class="col-lg-4" for="hipay_capture_type">{l s='Capture type'}</label>
                <select id="hipay_capture_type" name="hipay_capture_type" class="col-lg-3">
                    {if !$partiallyCaptured }
                        <option value="complete">{l s='Complete'}</option>
                    {/if}
                    <option value="partial">{l s='Partial'}</option>
                </select>
            </div>

            <div id="block-capture-amount" {if !$partiallyCaptured }style="display:none;" {/if} class="bloc-actions-hipay form-group">
                {if !$basket}
                    <label class="control-label " for="hipay_capture_amount">{l s='Capture amount'}</label>
                    <input type="text" name="hipay_capture_amount" value="{$stillToCapture}"/>
                {else}
                    <table class="table table-item-hipay">
                        <thead>
                            <tr>
                                <th>{l s='Reference'}</th>
                                <th>{l s='Product name'}</th>
                                <th>{l s='Captured'}</th>
                                <th>{l s='Qty to be capture'}</th>
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
                                                <input name="hipaycapture[{$item["product_id"]}]" type="number" min="0"
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
                                <td>{l s='Shipping'}</td>
                                <td>
                                     <span>
                                         {displayPrice price=$amountFees currency=$id_currency}
                                    <span>
                                </td>
                                <td>
                                    {if $shippingCost > 0 }
                                        {if !$capturedFees}
                                            <input type="checkbox" name="hipay_capture_fee"> {l s='Capture fee(s)'}
                                        {else}
                                            <span class="badge badge-success">Captured</span>
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
                <button type="submit" name="{if !$basket}hipay_capture_submit{else}hipay_capture_basket_submit{/if}"
                        class="btn btn-primary pull-right">
                    {l s='Capture'}
                </button>
            </div>
        </form>
    </fieldset>

<script>
    $(document).ready(function () {
        $("#hipay_capture_form").submit(function() {
            msgConfirmation = '{l s='Are-you sure to capture for this order ?'}';
            if (!confirm(msgConfirmation)) {
                return false;
            }
        });
    });
</script>
