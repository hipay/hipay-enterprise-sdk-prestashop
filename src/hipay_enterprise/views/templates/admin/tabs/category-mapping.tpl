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
<div class="panel">
    <div role="tabpanel">
        <div class="alert alert-info">
            <p>{l s='You must map your shop categories with Hipay categories.' mod='hipay_enterprise'}</p>
            <p>{l s='Categories mapping are mandatory for Oney payment methods or if you enable the option Customer\'s cart sending.' mod='hipay_enterprise'}</p>
        </div>
        <form method="post" class="form-horizontal" action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}"
              id="category_form">
            <div class="panel" id="fieldset_0">
                <div class="form-wrapper">
                    <h3>{l s='Categories mapping' mod='hipay_enterprise'}</h3>
                    <div class="form-group">
                        <table class="table">
                            <thead>
                            <th>{l s='Prestashop category' mod='hipay_enterprise'}</th>
                            <th>{l s='HiPay category' mod='hipay_enterprise'}</th>
                            </thead>
                            <tbody>
                            {foreach $HiPay_psCategories as $cat}
                                <tr>
                                    <td>
                                        <input type="hidden" value="{$cat["id_category"]}"
                                               name="ps_map_{$cat["id_category"]}"/>
                                        {$cat["name"]}
                                    </td>
                                    <td>
                                        <select name="hipay_map_{$cat["id_category"]}">
                                            {if !isset($HiPay_mappedCategories[$cat["id_category"]])}
                                                <option value="">{l s='- Select category - ' mod='hipay_enterprise'}</option>
                                            {/if}
                                            {foreach $HiPay_hipayCategories as $hpcat}
                                                <option {if isset($HiPay_mappedCategories[$cat["id_category"]]) && $HiPay_mappedCategories[$cat["id_category"]] eq  $hpcat->getCode()} selected {/if}
                                                        value="{$hpcat->getCode()}">{$hpcat->getLocal($HiPay_lang|upper)}</option>
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
                                name="submitCategoryMapping">
                            <i class="process-icon-save"></i>{l s='Save configuration changes' mod='hipay_enterprise'}
                        </button>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>