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
    <div role="tabpanel">
        <div class="alert alert-info">
            <p>{l s='You have to map yours delivery methods with HiPay\'s delivery methods.' mod='hipay_enterprise'}</p>
            <p>{l s='Delivery methods mapping are mandatory for Oney payment methods or if you enable the option Customer\'s cart sending.' mod='hipay_enterprise'}</p>
        </div>
        <form method="post" class="form-horizontal" action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}"
              id="category_form">
            <div class="panel">
                <div class="form-wrapper">
                    <h3>{l s='Delivery method mapping' mod='hipay_enterprise'}</h3>
                    <div class="form-group">
                        <table class="table">
                            <thead>
                            <th>{l s='Prestashop delivery method' mod='hipay_enterprise'}</th>
                            <th>{l s='Order preparation estimate time' mod='hipay_enterprise'}</th>
                            <th>{l s='Delivery estimate time' mod='hipay_enterprise'}</th>
                            <th>{l s='Hipay delivery mode' mod='hipay_enterprise'}</th>
                            <th>{l s='Hipay delivery method' mod='hipay_enterprise'}</th>
                            </thead>
                            <tbody>
                                {foreach $psCarriers as $car}
                                    <tr>
                                        <td>
                                            <input type="hidden" value="{$car["id_carrier"]}"
                                                   name="ps_map_{$car["id_carrier"]}"/>
                                            {$car["name"]}
                                        </td>
                                        <td>
                                            <input type="text"
                                                   value="{if isset($mappedCarriers[$car["id_carrier"]])}{$mappedCarriers[$car["id_carrier"]]["preparation_eta"]}{/if}"
                                                   name="ps_map_prep_eta_{$car["id_carrier"]}"/>
                                        </td>
                                        <td>
                                            <input type="text"
                                                   value="{if isset($mappedCarriers[$car["id_carrier"]])}{$mappedCarriers[$car["id_carrier"]]["delivery_eta"]}{/if}"
                                                   name="ps_map__delivery_eta_{$car["id_carrier"]}"/>
                                        </td>
                                        <td>
                                            <select name="hipay_map_mode_{$car["id_carrier"]}">
                                                {if !isset($mappedCarriers[$car["id_carrier"]])}
                                                    <option value="">{l s='- Select carrier mode -' mod='hipay_enterprise'}</option>
                                                {/if}
                                                {foreach $hipayCarriers["mode"] as $hpcarmode}
                                                    <option {if isset($mappedCarriers[$car["id_carrier"]]) && $mappedCarriers[$car["id_carrier"]]["mode"] eq  $hpcarmode->getCode()} selected {/if}
                                                                                                                                                                                     value="{$hpcarmode->getCode()}">{$hpcarmode->getDisplayName($lang|upper)} </option>
                                                {/foreach}
                                            </select>
                                        </td>
                                        <td>
                                            <select name="hipay_map_shipping_{$car["id_carrier"]}">
                                                {if !isset($mappedCarriers[$car["id_carrier"]])}
                                                    <option value="">{l s='- Select carrier shipping -' mod='hipay_enterprise'}</option>
                                                {/if}
                                                {foreach $hipayCarriers["shipping"] as $hpcarmode}
                                                    <option {if isset($mappedCarriers[$car["id_carrier"]]) && $mappedCarriers[$car["id_carrier"]]["shipping"] eq  $hpcarmode->getCode()} selected {/if}
                                                                                                                                                                                         value="{$hpcarmode->getCode()}">{$hpcarmode->getDisplayName($lang|upper)} </option>
                                                {/foreach}
                                            </select>
                                        </td>
                                    </tr>
                                {/foreach}
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="col-md-12 col-xs-12">
                        <button type="submit" class="btn btn-default pull-left" name="submitCancel"><i
                                class="process-icon-eraser"></i>{l s='Discard changes' mod='hipay_enterprise'}
                        </button>
                        <button type="submit" class="btn btn-default btn btn-default pull-right"
                                name="submitCarrierMapping">
                            <i class="process-icon-save"></i>{l s='Save configuration changes' mod='hipay_enterprise'}
                        </button>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>