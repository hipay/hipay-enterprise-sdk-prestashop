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
                {l s='The order has not been fully captured.' mod='hipay_enterprise'} <br/>
                {l s='To generate the invoice, you must capture the remaining amount due which will generate an invoice once the order full amount has been captured.' mod='hipay_enterprise'}
            </p>
        {/if}
        <p><b>{l s='Amount already captured'  mod='hipay_enterprise'} :</b> <span class="badge badge-success">{$refundableAmountDisplay}</span>
        </p>
        <p><b>{l s='Amount still to be captured'  mod='hipay_enterprise'} :</b> <span
                    class="badge badge-success">{$stillToCaptureDisplay}</span></p>
        <p class="help-block">
            <sup>*</sup> {l s='Amount will be updated once the refund will be confirmed by HiPay Enterprise' mod='hipay_enterprise'}</p>
        <form action="{$captureLink}" method="post" id="hipay_capture_form" class="form-horizontal">
            <input type="hidden" name="id_order" value="{$orderId}"/>
            <input type="hidden" name="id_emp" value="{$employeeId}"/>
            <input type="hidden" name="token" value="{$tokenCapture}"/>
            <div class="form-group ">
                <label class="control-label " for="hipay_capture_type">{l s='Capture type' mod='hipay_enterprise'}</label>
                <select id="hipay_capture_type" name="hipay_capture_type" class="form-control ">
                    {if !$partiallyCaptured }
                        <option value="complete">{l s='Complete' mod='hipay_enterprise'}</option>
                    {/if}
                    <option value="partial">{l s='Partial' mod='hipay_enterprise'}</option>
                </select>
            </div>
            <div id="block-capture-amount" {if !$partiallyCaptured }style="display:none;" {/if} class="form-group">
                {if !$basket}
                    <label class="control-label " for="hipay_capture_amount">{l s='Capture amount' mod='hipay_enterprise'}</label>
                    <input type="text" name="hipay_capture_amount" value="{$stillToCapture}"/>
                {else}
                    <table class="table">
                        <tr>
                            <th>{l s='Product name' mod='hipay_enterprise'}</th>
                            <th>{l s='Already captured' mod='hipay_enterprise'}</th>
                            <th>{l s='Quantity remaining' mod='hipay_enterprise'}</th>
                        </tr>
                        {foreach $products as $item}
                            {if !empty($capturedItems) && isset($capturedItems[$item["product_id"]])}
                                {assign var="remainQty" value=$item["product_quantity"] - $capturedItems[$item["product_id"]]["quantity"]}
                            {else}
                                {assign var="remainQty" value=$item["product_quantity"]}
                            {/if}
                            <tr>
                                <td>
                                    <input type="hidden" {if $remainQty == 0} disabled {/if}
                                           name="hipaycapture[{$item["product_id"]}]"
                                           value="{$item["product_id"]}"/>{$item["product_name"]}
                                </td>
                                <td>
                                    {if !empty($capturedItems) && isset($capturedItems[$item["product_id"]])}
                                        <span class="badge {if $remainQty == 0}badge-success{else}badge-warning{/if}">
                                            {$capturedItems[$item["product_id"]]["quantity"]}
                                            ({$capturedItems[$item["product_id"]]["amount"]})
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
                                            <input name="hipaycapture[{$item["product_id"]}]" type="number" min="0"
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
                            {if !$capturedFees}
                                <label>
                                    <input type="checkbox" name="hipay_capture_fee"> {l s='Capture fee(s)' mod='hipay_enterprise'}
                                </label>
                            {else}
                                <span>{l s='Shipping captured' mod='hipay_enterprise'}</span>
                            {/if}
                        {else}
                            <span>{l s='Shipping is free' mod='hipay_enterprise'}</span>
                        {/if}
                    </div>
                {/if}
            </div>
            <div class="form-group">
                <button type="submit" name="{if !$basket}hipay_capture_submit{else}hipay_capture_basket_submit{/if}"
                        class="btn btn-primary pull-right">
                    {l s='Capture' mod='hipay_enterprise'}
                </button>
            </div>
        </form>
    </fieldset>
