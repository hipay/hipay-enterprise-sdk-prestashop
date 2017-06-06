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
<div class="panel">
    <form method="post" class="form-horizontal" action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" id="category_form">
        <table class="table">
            <thead>
            <th>Prestashop carrier</th>
            <th>Order preparation estimate time</th>
            <th>Delivery estimate time</th>
            <th>Hipay carrier</th>
            </thead>
            <tbody>
                {foreach $psCarriers as $car}
                    <tr>
                        <td>
                            <input type="hidden" value="{$car["id_reference"]}" name="ps_map_{$car["id_reference"]}"/>
                            {$car["name"]}
                        </td>
                        <td>
                            <input type="text" value="{if isset($mappedCarriers[$car["id_reference"]])}{$mappedCarriers[$car["id_reference"]]["preparation_eta"]}{/if}" name="ps_map_prep_eta_{$car["id_reference"]}"/>
                        </td>
                        <td>
                            <input type="text" value="{if isset($mappedCarriers[$car["id_reference"]])}{$mappedCarriers[$car["id_reference"]]["delivery_eta"]}{/if}" name="ps_map__delivery_eta_{$car["id_reference"]}"/>
                        </td>
                        <td>
                            <select name="hipay_map_{$car["id_reference"]}">
                                {if !isset($mappedCarriers[$car["id_reference"]])}
                                    <option value="" >{l s="Unclassified" mod="hipay_professional"}</option>
                                {/if}
                                {foreach $hipayCarriers as $hpcar}
                                    <option {if isset($mappedCarriers[$car["id_reference"]]) && $mappedCarriers[$car["id_reference"]]["id"] eq  $hpcar->getCode()} selected {/if} value="{$hpcar->getCode()}" >{$hpcar->getShipping()}</option>
                                {/foreach}
                            </select>
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
        <div class="row">
            <div class="col-md-12 col-xs-12">
                <button type="submit" class="btn btn-default pull-left" name="submitCancel"><i
                        class="process-icon-eraser"></i>{l s='Discard changes' mod='hipay_professional'}
                </button>
                <button type="submit" class="btn btn-default btn btn-default pull-right" name="submitCarrierMapping">
                    <i class="process-icon-save"></i>{l s='Save configuration changes' mod='hipay_professional'}
                </button>
            </div>
        </div>
    </form>
</div>