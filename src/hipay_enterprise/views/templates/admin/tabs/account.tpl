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
        <div class="row">
            <div class="col-md-12 col-xs-12">
                <form method="post" class="form-horizontal" action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" id="account_form">
                    <div class="panel" id="fieldset_0">
                        <div class="form-wrapper">
                            <!-- SWITCH MODE START -->
                            <div class="row">
                                <label class="control-label col-lg-3">
                                    <span class="label-tooltip"
                                          data-toggle="tooltip"
                                          data-html="true"
                                          title=""
                                          data-original-title="{l s='When in test mode, payment cards are not really charged. Activate this options for testing purposes only.' mod='hipay_enterprise'}">
                                        {l s='Use test mode' mod='hipay_enterprise'}
                                    </span>
                                </label>
                                <div class="col-lg-9">
                                    <span class="switch prestashop-switch fixed-width-lg">
                                        <input type="radio" name="sandbox_mode" id="account_switchmode_on" value="1"
                                               {if $config_hipay.account.global.sandbox_mode }checked="checked"{/if}>
                                        <label for="account_switchmode_on">{l s='Yes' mod='hipay_enterprise'}</label>
                                        <input type="radio" name="sandbox_mode" id="account_switchmode_off" value="0"
                                               {if $config_hipay.account.global.sandbox_mode == false}checked="checked"{/if}>
                                        <label for="account_switchmode_off">{l s='No' mod='hipay_enterprise'}</label>
                                        <a class="slide-button btn"></a>
                                    </span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 col-xs-12">
                                    <p>
                                        {l s='When in test mode, payment cards are not really charged. Activate this options for testing purposes only.' mod='hipay_enterprise'}
                                    </p>
                                </div>
                            </div>
                            <!-- SWITCH MODE END -->
                            <div class="row">

                                <!-- GLOBAL FORM START -->
                                <div class="col-md-12 ">
                                    <h4>{l s='Global configuration' mod='hipay_enterprise'}</h4>
                                    <hr>
                                    <div class="form-group">
                                        <label class="control-label col-lg-2">
                                            {l s='Host proxy' mod='hipay_enterprise'}
                                        </label>
                                        <div class="col-lg-9">
                                            <input class="form-control" type="text" name="host_proxy" value="{$config_hipay.account.global.host_proxy}">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label col-lg-2">
                                            {l s='Port proxy' mod='hipay_enterprise'}
                                        </label>
                                        <div class="col-lg-9">
                                            <input class="form-control" type="text" name="port_proxy" value="{$config_hipay.account.global.port_proxy}">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label col-lg-2">
                                            {l s='User proxy' mod='hipay_enterprise'}
                                        </label>
                                        <div class="col-lg-9">
                                            <input class="form-control" type="text" name="user_proxy" value="{$config_hipay.account.global.user_proxy}">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label col-lg-2">
                                            {l s='Password proxy' mod='hipay_enterprise'}
                                        </label>
                                        <div class="col-lg-9">
                                            <input class="form-control" type="text" name="password_proxy" value="{$config_hipay.account.global.password_proxy}">
                                        </div>
                                    </div>
                                </div>
                                <!-- GLOBAL FORM END -->

                                <!-- PRODUCTION FORM START -->
                                <div class="col-md-6 trait">
                                    <h4>{l s='Production configuration' mod='hipay_enterprise'}</h4>
                                    <hr>

                                    <div class="form-group">
                                        <label class="control-label col-lg-4">
                                            {l s='Api username' mod='hipay_enterprise'}
                                        </label>
                                        <div class="col-lg-6">
                                            <input class="form-control" type="text" name="api_username_production" value="{$config_hipay.account.production.api_username_production}">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label col-lg-4">
                                            {l s='Api password' mod='hipay_enterprise'}
                                        </label>
                                        <div class="col-lg-6">
                                            <input class="form-control" type="text" name="api_password_production" value="{$config_hipay.account.production.api_password_production}">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label col-lg-4">
                                            {l s='Api TokenJS Username' mod='hipay_enterprise'}
                                        </label>
                                        <div class="col-lg-6">
                                            <input class="form-control" type="text" name="api_tokenjs_username_production" value="{$config_hipay.account.production.api_tokenjs_username_production}">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label col-lg-4">
                                            {l s='Api TokenJS Password/Public Key ' mod='hipay_enterprise'}
                                        </label>
                                        <div class="col-lg-6">
                                            <input class="form-control" type="text" name="api_tokenjs_password_publickey_production" value="{$config_hipay.account.production.api_tokenjs_password_publickey_production}">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label col-lg-4">
                                            {l s='Secret passphrase' mod='hipay_enterprise'}
                                        </label>
                                        <div class="col-lg-6">
                                            <input class="form-control" type="text" name="api_secret_passphrase_production" value="{$config_hipay.account.production.api_secret_passphrase_production}">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label col-lg-4">
                                            {l s='Api MO/TO username' mod='hipay_enterprise'}
                                        </label>
                                        <div class="col-lg-6">
                                            <input class="form-control" type="text" name="api_moto_username_production" value="{$config_hipay.account.production.api_moto_username_production}">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label col-lg-4">
                                            {l s='Api MO/TO password' mod='hipay_enterprise'}
                                        </label>
                                        <div class="col-lg-6">
                                            <input class="form-control" type="text" name="api_moto_password_production" value="{$config_hipay.account.production.api_moto_password_production}">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label col-lg-4">
                                            {l s='MO/TO Secret passphrase' mod='hipay_enterprise'}
                                        </label>
                                        <div class="col-lg-6">
                                            <input class="form-control" type="text" name="api_moto_secret_passphrase_production" value="{$config_hipay.account.production.api_moto_secret_passphrase_production}">
                                        </div>
                                    </div>

                                </div>
                                <!-- PRODUCTION FORM END -->
                                <!-- SANDBOX FORM START -->
                                <div class="col-md-6 ">
                                    <h4>{l s='Sandbox configuration' mod='hipay_enterprise'}</h4>
                                    <hr>
                                    <div class="form-group">
                                        <label class="control-label col-lg-4">
                                            {l s='Api username' mod='hipay_enterprise'}
                                        </label>
                                        <div class="col-lg-6">
                                            <input class="form-control" type="text" name="api_username_sandbox" value="{$config_hipay.account.sandbox.api_username_sandbox}">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label col-lg-4">
                                            {l s='Api password' mod='hipay_enterprise'}
                                        </label>
                                        <div class="col-lg-6">
                                            <input class="form-control" type="text" name="api_password_sandbox" value="{$config_hipay.account.sandbox.api_password_sandbox}">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label col-lg-4">
                                            {l s='Api TokenJS Username' mod='hipay_enterprise'}
                                        </label>
                                        <div class="col-lg-6">
                                            <input class="form-control" type="text" name="api_tokenjs_username_sandbox" value="{$config_hipay.account.sandbox.api_tokenjs_username_sandbox}">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label col-lg-4">
                                            {l s='Api TokenJS Password/Public Key ' mod='hipay_enterprise'}
                                        </label>
                                        <div class="col-lg-6">
                                            <input class="form-control" type="text" name="api_tokenjs_password_publickey_sandbox" value="{$config_hipay.account.sandbox.api_tokenjs_password_publickey_sandbox}">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label col-lg-4">
                                            {l s='Secret passphrase' mod='hipay_enterprise'}
                                        </label>
                                        <div class="col-lg-6">
                                            <input class="form-control" type="text" name="api_secret_passphrase_sandbox" value="{$config_hipay.account.sandbox.api_secret_passphrase_sandbox}">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label col-lg-4">
                                            {l s='Api MO/TO username' mod='hipay_enterprise'}
                                        </label>
                                        <div class="col-lg-6">
                                            <input class="form-control" type="text" name="api_moto_username_sandbox" value="{$config_hipay.account.sandbox.api_moto_username_sandbox}">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label col-lg-4">
                                            {l s='Api MO/TO password' mod='hipay_enterprise'}
                                        </label>
                                        <div class="col-lg-6">
                                            <input class="form-control" type="text" name="api_moto_password_sandbox" value="{$config_hipay.account.sandbox.api_moto_password_sandbox}">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label col-lg-4">
                                            {l s='MO/TO Secret passphrase' mod='hipay_enterprise'}
                                        </label>
                                        <div class="col-lg-6">
                                            <input class="form-control" type="text" name="api_moto_secret_passphrase_sandbox" value="{$config_hipay.account.sandbox.api_moto_secret_passphrase_sandbox}">
                                        </div>
                                    </div>

                                </div>
                                <!-- SANDBOX FORM END -->
                            </div>

                        </div>
                    </div>
                    <div class="panel-footer">
                        <div class="col-md-12 col-xs-12">
                            <button type="submit" class="btn btn-default pull-left" name="submitCancel"><i
                                    class="process-icon-eraser"></i>{l s='Discard changes' mod='hipay_enterprise'}
                            </button>
                            <button type="submit" class="btn btn-default btn btn-default pull-right" name="submitAccount">
                                <i class="process-icon-save"></i>{l s='Save configuration changes' mod='hipay_enterprise'}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
