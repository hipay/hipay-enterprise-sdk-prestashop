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

<div class="panel-heading">
    <i class="icon-credit-card"></i>
    {l s='Module informations' mod='hipay_enterprise'}
</div>
<div class="row " id="module-informations">
    <div class="col-lg-6">
        <div class="row">
            <span class="col-lg-12">{l s='To help you in your plateform and module configuration, you cand find the module documentation on our developper portal at the following adress : ' mod='hipay_enterprise'}
                <a href='https://developer.hipay.com/doc/hipay-enterprise-sdk-prestashop/'>https://developer.hipay.com/doc/hipay-enterprise-sdk-prestashop/</a></span>
        </div>
        <div class="row">
            <span class="col-lg-12">{l s='Our support team is available for any questions at the following adress: ' mod='hipay_enterprise'}
                <a href="mailto:support.tpp@hipay.com">support.tpp@hipay.com</a></span>
        </div>
        <div class="row technical-information">
            <label class="col-lg-3">{l s='HiPay module' mod='hipay_enterprise'}</label><span
                    class="col-lg-9">: {$source['integration_version']}</span>
        </div>
        <div class="row">
            <label class="col-lg-3">{l s='Prestashop version' mod='hipay_enterprise'}</label><span
                    class="col-lg-9">: {$source['brand_version']}</span>
        </div>
        <div class="row">
            <label class="col-lg-3">{l s='IP Address' mod='hipay_enterprise'}</label><span
                    class="col-lg-9">: {$ipaddr}</span>
        </div>
        <div class="row">
            <label class="col-lg-3">{l s='Callback url' mod='hipay_enterprise'}</label><span
                    class="col-lg-9">: {$this_callback}</span>
        </div>

    </div>

    <div class="panel col-lg-6 ">
        <div class="row technicals-requirements panel-heading">
            <label class="col-lg-4">{l s='Technicals warning' mod='hipay_enterprise'}</label>
        </div>

        {* Showing update alert if needed *}
        {if $updateNotif->getVersion() != $updateNotif->getNewVersion()}
            <div class="row">
                <div class="alert alert-danger">
                    {l s='There is a new version of HiPay Enterprise module available.' mod='hipay_enterprise'}
                    <a href="{$updateNotif->getReadMeUrl()}">{l s='View version %s details' mod='hipay_enterprise' sprintf=$updateNotif->getNewVersion()}</a>
                    {l s='or' mod='hipay_enterprise'} <a href="{$updateNotif->getDownloadUrl()}">{l s='update now' mod='hipay_enterprise'}</a>.
                </div>
            </div>
        {/if}

        {if $config_hipay.account.global.sandbox_mode && (empty($config_hipay.account.sandbox.api_username_sandbox) || empty($config_hipay.account.sandbox.api_password_sandbox) || empty($config_hipay.account.sandbox.api_secret_passphrase_sandbox) )}
            <div class="row">
                <div class="alert alert-danger">
                    {l s='The API gateway is configured in TEST mode but your test IDs are not filled in.' mod='hipay_enterprise'}
                </div>
            </div>
        {/if}

        {if !$config_hipay.account.global.sandbox_mode && (empty($config_hipay.account.production.api_username_production) || empty($config_hipay.account.production.api_password_production) || empty($config_hipay.account.production.api_secret_passphrase_production) )}
            <div class="row">
                <div class="alert alert-warning">
                    {l s='The API gateway is configured in PRODUCTION mode but your test IDs are not filled in.' mod='hipay_enterprise'}
                </div>
            </div>
        {/if}

        {if empty($mappedCarriers) || empty($mappedCategories)}
            <div class="row">
                <div class="alert alert-warning">
                    {l s='You have to map yours product categories and delivery method to hipay categories' mod='hipay_enterprise'}
                </div>
            </div>
        {/if}

        {if !empty($technicalErrors)}
            <div class="row">
                <div class="alert alert-danger">
                    {$technicalErrors}
                </div>
            </div>
        {/if}
    </div>
</div>