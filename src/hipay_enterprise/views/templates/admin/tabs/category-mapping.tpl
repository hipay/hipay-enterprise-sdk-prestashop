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
		{l s='You must map your shop category to hipay category. Category mapping is mandatory for Oney payment method'  mod='hipay_enterprise'}
	</div>
        <form method="post" class="form-horizontal" action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" id="category_form">
            <div class="panel" id="fieldset_0">
                <div class="form-wrapper">
                    <h3>Category mapping</h3>
                    <div class="form-group">
                        <table class="table">
                            <thead>
                            <th>Prestashop category</th>
                            <th>Hipay category</th>
                            </thead>
                            <tbody>
                                {foreach $psCategories as $cat}
                                    <tr>
                                        <td>
                                            <input type="hidden" value="{$cat["id_category"]}" name="ps_map_{$cat["id_category"]}"/>
                                            {$cat["name"]}
                                        </td>
                                        <td>
                                            <select name="hipay_map_{$cat["id_category"]}">
                                                {if !isset($mappedCategories[$cat["id_category"]])}
                                                    <option value="" >{l s='-- Select category" mod="hipay_enterprise' mod='hipay_enterprise'}</option>
                                                {/if}
                                                {foreach $hipayCategories as $hpcat}
                                                    <option {if isset($mappedCategories[$cat["id_category"]]) && $mappedCategories[$cat["id_category"]] eq  $hpcat->getCode()} selected {/if} value="{$hpcat->getCode()}" >{$hpcat->getLocal($lang|upper)}</option>
                                                {/foreach}
                                            </select>
                                        </td>
                                    </tr>
                                {/foreach}
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
            <div class="panel-footer">
                <div class="col-md-12 col-xs-12">
                    <button type="submit" class="btn btn-default pull-left" name="submitCancel"><i
                            class="process-icon-eraser"></i>{l s='Discard changes' mod='hipay_enterprise'}
                    </button>
                    <button type="submit" class="btn btn-default btn btn-default pull-right" name="submitCategoryMapping">
                        <i class="process-icon-save"></i>{l s='Save configuration changes' mod='hipay_enterprise'}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>