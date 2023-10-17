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
{if $HiPay_savedCC}
    <div class="option_payment saved-card"><label
                for="radio-with-token"><strong>{l s='Pay with a saved credit card' mod='hipay_enterprise'}</strong></label>
    </div>
    <div id="error-js-oc" style="display:none" class="alert alert-danger">
        <span>There is 1 error</span>
        <ol>
            <li class="error-oc"></li>
        </ol>
    </div>
    {foreach $HiPay_savedCC as $cc}
        <div class="form-group row group-card">
            <div class="col-md-1">
                        <span class="custom-radio">
                            <input type="radio" class="radio-with-token" name="ccTokenHipay" checked="checked"
                                   value="{$cc.token}"/>
                            <span></span>
                        </span>
            </div>
            <div class="col-md-5">
                <div class="row">
                    <label for="radio-with-token">
                        <span class="hipay-img col-md-2 col-xs-3"><img class="card-img"
                                                                       src="{$HiPay_this_path_ssl}/views/img/{$cc.brand|lower}_small.png"/> </span>
                        <span class="hipay-pan col-md-10 col-xs-9">{$cc.pan}</span>
                        <span class="hipay-exp-date col-md-10 col-xs-9">{l s='Exp. date'  mod='hipay_enterprise'}
                            : {"%02d"|sprintf:$cc.card_expiry_month}/{$cc.card_expiry_year}</span>
                        <span class="hipay-card-holder col-md-10 col-xs-9">{$cc.card_holder}</span>
                    </label>
                </div>
            </div>
        </div>
    {/foreach}
{/if}
