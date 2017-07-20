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

{if (isset($form_errors)) && (count($form_errors) > 0)}
    <div class="alert alert-danger">
        <h4>{l s='Error!' mod='hipay_enterprise'}</h4>
        <ul class="list-unstyled">
            {foreach from=$form_errors item='message'}
                <li>{$message|escape:'html':'UTF-8'}</li>
                {/foreach}
        </ul>
    </div>
{/if}

{if (isset($form_infos)) && (count($form_infos) > 0)}
    <div class="alert alert-warning">
        <h4>{l s='Notice!' mod='hipay_enterprise'}</h4>
        <ul class="list-unstyled">
            {foreach from=$form_infos item='message'}
                <li>{$message|escape:'html':'UTF-8'}</li>
                {/foreach}
        </ul>
    </div>
{/if}

{if (isset($form_successes)) && (count($form_successes) > 0)}
    <div class="alert alert-success">
        <h4>{l s='Success!' mod='hipay_enterprise'}</h4>
        <ul class="list-unstyled">
            {foreach from=$form_successes item='message'}
                <li>{$message|escape:'html':'UTF-8'}</li>
                {/foreach}
        </ul>
    </div>
{/if}

{if $config_hipay.account.global.sandbox_mode && (empty($config_hipay.account.sandbox.api_username_sandbox) || empty($config_hipay.account.sandbox.api_password_sandbox) || empty($config_hipay.account.sandbox.api_secret_passphrase_sandbox) )}
    <div class="alert alert-danger">
        <h4>{l s='Error!' mod='hipay_enterprise'}</h4>
        <p>
            {l s='You\'re on test mode but your sanbox credentials are not filled' mod='hipay_enterprise'}
        </p>
    </div>
{/if}

{if !$config_hipay.account.global.sandbox_mode && (empty($config_hipay.account.production.api_username_production) || empty($config_hipay.account.production.api_password_production) || empty($config_hipay.account.production.api_secret_passphrase_production) )}
    <div class="alert alert-danger">
        <h4>{l s='Error!' mod='hipay_enterprise'}</h4>
        <p>
            {l s='You\'re on live mode but your production credentials are not filled' mod='hipay_enterprise'}
        </p>
    </div>
{/if}
{if $ps_round_total}
    <div class="alert alert-danger">
        <h4>{l s='Error!' mod='hipay_enterprise'}</h4>
        <p>
            {l s='\'Round on the total\' is activated in prestashop configuration, cart will not be sent and payment method that force cart to be send will be disabled.' mod='hipay_enterprise'}
        </p>
    </div>
{/if}
{if empty($mappedCarriers) || empty($mappedCategories)}
    <div class="alert alert-danger">
        <h4>{l s='Error!' mod='hipay_enterprise'}</h4>
        <p>
            {l s='You must map your shop category and carrier to hipay category and carrier' mod='hipay_enterprise'}
        </p>
    </div>
{/if}