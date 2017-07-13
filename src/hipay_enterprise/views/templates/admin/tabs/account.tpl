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
    <form method="post" class="form-horizontal" action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" id="account_form">
        <div role="tabpanel">
            <div class="panel" id="fieldset_0">
                <div class="form-wrapper">
                    <h4>{l s='Mode configuration' mod='hipay_enterprise'}</h4>
                    <hr>
                    <!-- SWITCH MODE START -->
                    <div class="form-group">
                        <label class="control-label col-lg-3">
                            <span class="label-tooltip"
                                  data-toggle="tooltip"
                                  data-html="true"
                                  title=""
                                  data-original-title="{l s='When in test mode, payment cards are not really charged. Activate this options for testing purposes only.' mod='hipay_enterprise'}">
                                {l s='Mode' mod='hipay_enterprise'}
                            </span>
                        </label>
                        <div class="col-lg-9">
                            <span class="switch prestashop-switch fixed-width-lg">
                                <input type="radio" name="sandbox_mode" id="account_switchmode_on" value="1"
                                       {if $config_hipay.account.global.sandbox_mode }checked="checked"{/if}>
                                <label for="account_switchmode_on">{l s='Test' mod='hipay_enterprise'}</label>
                                <input type="radio" name="sandbox_mode" id="account_switchmode_off" value="0"
                                       {if $config_hipay.account.global.sandbox_mode == false}checked="checked"{/if}>
                                <label for="account_switchmode_off">{l s='Live' mod='hipay_enterprise'}</label>
                                <a class="slide-button btn"></a>
                            </span>
                            <p class="help-block">
                                {l s='When in test mode, payment cards are not really charged. Activate this options for testing purposes only.' mod='hipay_enterprise'}
                            </p>
                        </div>
                    </div>
                    <!-- SWITCH MODE END -->
                </div>
            </div>
            <div class="alert alert-info">
                {l s='Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud'  mod='hipay_enterprise'}
            </div>
            <div class="panel" id="fieldset_0">
                <div class="form-wrapper">
                    <a data-toggle="collapse" href="#collapseProduction" aria-expanded="true" aria-controls="collapseProduction" >
                        <h4>{l s='Production configuration' mod='hipay_enterprise'} <i id="chevronProd" class="pull-right chevron icon icon-chevron-up"></i></h4>
                        <hr>
                    </a>
                    <div class="collapse in" id="collapseProduction">
                        <h5 class="col-lg-offset-2 col-xs-offset-4" >{l s='Account credentials' mod='hipay_enterprise'}</h5>
                        <div class="form-group">
                            <label class="required control-label col-lg-4">
                                {l s='Username' mod='hipay_enterprise'}
                            </label>
                            <div class="col-lg-6">
                                <input class="form-control" type="text" name="api_username_production"
                                       value="{$config_hipay.account.production.api_username_production}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="required control-label col-lg-4">
                                {l s='Password' mod='hipay_enterprise'}
                            </label>
                            <div class="col-lg-6">
                                <input class="form-control" type="password" name="api_password_production"
                                       value="{$config_hipay.account.production.api_password_production}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="required control-label col-lg-4">
                                {l s='Secret passphrase' mod='hipay_enterprise'}
                            </label>
                            <div class="col-lg-6">
                                <input class="form-control" type="password"
                                       name="api_secret_passphrase_production"
                                       value="{$config_hipay.account.production.api_secret_passphrase_production}">
                            </div>
                        </div>

                        <h5 class="col-lg-offset-2 col-xs-offset-4" >{l s='MO/TO account credentials' mod='hipay_enterprise'}</h5>
                        <div class="form-group">
                            <label class="required control-label col-lg-4">
                                {l s='Username' mod='hipay_enterprise'}
                            </label>
                            <div class="col-lg-6">
                                <input class="form-control" type="text" name="api_moto_username_production"
                                       value="{$config_hipay.account.production.api_moto_username_production}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="required control-label col-lg-4">
                                {l s='Password' mod='hipay_enterprise'}
                            </label>
                            <div class="col-lg-6">
                                <input class="form-control" type="text" name="api_moto_password_production"
                                       value="{$config_hipay.account.production.api_moto_password_production}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="required control-label col-lg-4">
                                {l s='Secret passphrase' mod='hipay_enterprise'}
                            </label>
                            <div class="col-lg-6">
                                <input class="form-control" type="text"
                                       name="api_moto_secret_passphrase_production"
                                       value="{$config_hipay.account.production.api_moto_secret_passphrase_production}">
                            </div>
                        </div>

                        <h5 class="col-lg-offset-2 col-xs-offset-4">{l s='Tokenization account credentials' mod='hipay_enterprise'}</h5>
                        <div class="form-group">
                            <label class="required control-label col-lg-4">
                                {l s='Username' mod='hipay_enterprise'}
                            </label>
                            <div class="col-lg-6">
                                <input class="form-control" type="text"
                                       name="api_tokenjs_username_production"
                                       value="{$config_hipay.account.production.api_tokenjs_username_production}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="required control-label col-lg-4">
                                {l s='Password/Public Key ' mod='hipay_enterprise'}
                            </label>
                            <div class="col-lg-6">
                                <input class="form-control" type="password"
                                       name="api_tokenjs_password_publickey_production"
                                       value="{$config_hipay.account.production.api_tokenjs_password_publickey_production}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel" id="fieldset_0">
                <div class="form-wrapper">
                    <a data-toggle="collapse" href="#collapseSandbox" aria-expanded="true" aria-controls="collapseSandbox" >
                        <h4>{l s='Sandbox configuration' mod='hipay_enterprise'} <i id="chevronSand" class="pull-right chevron icon icon-chevron-up"></i></h4>
                        <hr>
                    </a>
                    <div class="collapse in" id="collapseSandbox">
                        <h5 class="col-lg-offset-2 col-xs-offset-4" >{l s='Account credentials' mod='hipay_enterprise'}</h5>
                        <div class="form-group">
                            <label class="required control-label col-lg-4">
                                {l s='Username' mod='hipay_enterprise'}
                            </label>
                            <div class="col-lg-6">
                                <input class="form-control" type="text" name="api_username_sandbox"
                                       value="{$config_hipay.account.sandbox.api_username_sandbox}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="required control-label col-lg-4">
                                {l s='Password' mod='hipay_enterprise'}
                            </label>
                            <div class="col-lg-6">
                                <input class="form-control" type="password" name="api_password_sandbox"
                                       value="{$config_hipay.account.sandbox.api_password_sandbox}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="required control-label col-lg-4">
                                {l s='Secret passphrase' mod='hipay_enterprise'}
                            </label>
                            <div class="col-lg-6">
                                <input class="form-control" type="password" name="api_secret_passphrase_sandbox"
                                       value="{$config_hipay.account.sandbox.api_secret_passphrase_sandbox}">
                            </div>
                        </div>

                        <h5 class="col-lg-offset-2 col-xs-offset-4" >{l s='MO/TO account credentials' mod='hipay_enterprise'}</h5>
                        <div class="form-group">
                            <label class="required control-label col-lg-4">
                                {l s='Username' mod='hipay_enterprise'}
                            </label>
                            <div class="col-lg-6">
                                <input class="form-control" type="text" name="api_moto_username_sandbox"
                                       value="{$config_hipay.account.sandbox.api_moto_username_sandbox}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="required control-label col-lg-4">
                                {l s='Password' mod='hipay_enterprise'}
                            </label>
                            <div class="col-lg-6">
                                <input class="form-control" type="text" name="api_moto_password_sandbox"
                                       value="{$config_hipay.account.sandbox.api_moto_password_sandbox}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="required control-label col-lg-4">
                                {l s='Secret passphrase' mod='hipay_enterprise'}
                            </label>
                            <div class="col-lg-6">
                                <input class="form-control" type="text"
                                       name="api_moto_secret_passphrase_sandbox"
                                       value="{$config_hipay.account.sandbox.api_moto_secret_passphrase_sandbox}">
                            </div>
                        </div>

                        <h5 class="col-lg-offset-2 col-xs-offset-4" >{l s='Tokenization account credentials' mod='hipay_enterprise'}</h5>
                        <div class="form-group">
                            <label class="required control-label col-lg-4">
                                {l s='Username' mod='hipay_enterprise'}
                            </label>
                            <div class="col-lg-6">
                                <input class="form-control" type="text" name="api_tokenjs_username_sandbox"
                                       value="{$config_hipay.account.sandbox.api_tokenjs_username_sandbox}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="required control-label col-lg-4">
                                {l s='Password/Public Key ' mod='hipay_enterprise'}
                            </label>
                            <div class="col-lg-6">
                                <input class="form-control" type="password"
                                       name="api_tokenjs_password_publickey_sandbox"
                                       value="{$config_hipay.account.sandbox.api_tokenjs_password_publickey_sandbox}">
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="panel" id="fieldset_0">
                <div class="form-wrapper">
                    <a data-toggle="collapse" href="#collapseTechnical" aria-expanded="false" aria-controls="collapseTechnical" >
                        <h4>{l s='Technical configuration' mod='hipay_enterprise'} <i id="chevronTec" class="pull-right chevron icon icon-chevron-down"></i></h4>
                        <hr>
                    </a>
                    <div class="collapse" id="collapseTechnical">
                        <div class="alert alert-info">
                            {l s='Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud'  mod='hipay_enterprise'}
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-2">
                                {l s='Host proxy' mod='hipay_enterprise'}
                            </label>
                            <div class="col-lg-9">
                                <input class="form-control" type="text" name="host_proxy"
                                       value="{$config_hipay.account.global.host_proxy}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-lg-2">
                                {l s='Port proxy' mod='hipay_enterprise'}
                            </label>
                            <div class="col-lg-9">
                                <input class="form-control" type="text" name="port_proxy"
                                       value="{$config_hipay.account.global.port_proxy}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-lg-2">
                                {l s='User proxy' mod='hipay_enterprise'}
                            </label>
                            <div class="col-lg-9">
                                <input class="form-control" type="text" name="user_proxy"
                                       value="{$config_hipay.account.global.user_proxy}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-lg-2">
                                {l s='Password proxy' mod='hipay_enterprise'}
                            </label>
                            <div class="col-lg-9">
                                <input class="form-control" type="text" name="password_proxy"
                                       value="{$config_hipay.account.global.password_proxy}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <div class="col-md-12 col-xs-12">
                <button type="submit" class="btn btn-default pull-left" name="submitCancel"><i
                        class="process-icon-eraser"></i>{l s='Discard changes' mod='hipay_enterprise'}
                </button>
                <button type="submit" class="btn btn-default btn btn-default pull-right"
                        name="submitAccount">
                    <i class="process-icon-save"></i>{l s='Save configuration changes' mod='hipay_enterprise'}
                </button>
            </div>
        </div>
    </form>
</div>
<script>

    $('#collapseProduction').on('shown.bs.collapse', function () {
        $("#chevronProd").addClass('icon-chevron-up').removeClass('icon-chevron-down');
    });

    $('#collapseProduction').on('hidden.bs.collapse', function () {
        $("#chevronProd").addClass('icon-chevron-down').removeClass('icon-chevron-up');
    });

    $('#collapseSandbox').on('shown.bs.collapse', function () {
        $("#chevronSand").addClass('icon-chevron-up').removeClass('icon-chevron-down');
    });

    $('#collapseSandbox').on('hidden.bs.collapse', function () {
        $("#chevronSand").addClass('icon-chevron-down').removeClass('icon-chevron-up');
    });

    $('#collapseTechnical').on('shown.bs.collapse', function () {
        $("#chevronTec").addClass('icon-chevron-up').removeClass('icon-chevron-down');
    });

    $('#collapseTechnical').on('hidden.bs.collapse', function () {
        $("#chevronTec").addClass('icon-chevron-down').removeClass('icon-chevron-up');
    });
</script>