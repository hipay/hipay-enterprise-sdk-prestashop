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
{if $savedCC}
    <div id="error-js-oc" style="display:none" class="alert alert-danger">
        <p>There is 1 error</p>
        <ol>
            <li class="error-oc"></li>
        </ol>
    </div>
    {if $status_error_oc == '400'}
        <div class="alert alert-danger">
            <p>There is 1 error</p>
            <ol>
                <li>{l s='The request was rejected due to a validation error. Please verify the card details you entered.' mod='hipay_enterprise'}</li>
            </ol>
        </div>
    {/if}

    {foreach $savedCC as $cc}
        <div class="">
            <input type="radio" name="ccTokenHipay" class="radio-with-token" checked="checked" value="{$cc.token}"/>
            <label for="ccTokenHipay">
                <img src="{$this_path_ssl}/views/img/{$cc.brand|lower}_small.png"/>
                {$cc.pan} ({"%02d"|sprintf:$cc.card_expiry_month} / {$cc.card_expiry_year})
                - {$cc.card_holder}
            </label>
        </div>
    {/foreach}
{/if}
