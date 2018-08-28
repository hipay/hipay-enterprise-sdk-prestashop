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
<div class="panel hipay-tabs">
    <form method="post" class="form-horizontal" action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}"
          id="account_form">
        <div role="tabpanel">
            <div class="panel" id="fieldset_0">
                <div class="form-wrapper">
                    <div class="panel-heading">{l s='Gateway configuration' mod='hipay_enterprise'}</div>
                    <!-- SWITCH MODE START -->
                    <div class="form-group">
                        <label class="control-label col-lg-4">
                            <span>{l s='Mode' mod='hipay_enterprise'}</span>
                        </label>
                        <div class="col-lg-8">
                            <span class="switch prestashop-switch fixed-width-lg">
                                <input type="radio" name="sandbox_mode" id="account_switchmode_on" value="1"
                                       {if $config_hipay.account.global.sandbox_mode }checked="checked"{/if}>
                                <label for="account_switchmode_on">{l s='Test' mod='hipay_enterprise'}</label>
                                <input type="radio" name="sandbox_mode" id="account_switchmode_off" value="0"
                                       {if $config_hipay.account.global.sandbox_mode == false}checked="checked"{/if}>
                                <label for="account_switchmode_off">{l s='Live' mod='hipay_enterprise'}</label>
                                <a class="slide-button btn production-switch"></a>
                            </span>
                            <p class="help-block">
                                {l s='When in test mode, payment cards are not really charged. Enable this option for testing purposes only.' mod='hipay_enterprise'}
                            </p>
                        </div>
                    </div>
                    <!-- SWITCH MODE END -->
                </div>
            </div>
            <div class="panel" id="fieldset_0">
                <div class="form-wrapper">
                    <div class="panel-heading">
                        <a data-toggle="collapse" href="#collapseProduction" aria-expanded="true"
                           aria-controls="collapseProduction">
                            {l s='Production configuration' mod='hipay_enterprise'} <i id="chevronProd"
                                                                                       class="pull-right chevron icon icon-chevron-up"></i>
                        </a>
                    </div>
                    <div class="collapse in" id="collapseProduction">
                        <div class="form-group">
                            <div class="col-lg-6 col-lg-offset-2">
                                <div class="alert alert-info">
                                    {l s='Generated in your HiPay Enterprise back office' mod='hipay_enterprise'} (<a
                                            title="BO Hipay" target="_blank" href="https://merchant.hipay-tpp.com">https://merchant.hipay-tpp.com</a>)
                                    via </br >
                                    <strong>"Integration” => “Security Settings” => “Api credentials” => “Credentials
                                        accessibility”</strong>, {l s='these API credentials are required to use the HiPay Enterprise module.'  mod='hipay_enterprise'}</br >
                                    {l s='You must generate public and private credentials.' mod='hipay_enterprise'}
                                    {l s='You can also set specific credentials for your Mail Order to Order payments. If they are defined then they will be used when making your payments via the back office' mod='hipay_enterprise'}
                                </div>
                            </div>
                        </div>
                        <h5 class="col-lg-offset-2 col-xs-offset-4">{l s='Account (Private)' mod='hipay_enterprise'}</h5>
                        <div class="form-group">
                            <label class="required control-label col-lg-2">
                                {l s='Username' mod='hipay_enterprise'}
                            </label>
                            <div class="col-lg-6">
                                <input class="form-control" type="text" name="api_username_production"
                                       value="{$config_hipay.account.production.api_username_production}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="required control-label col-lg-2">
                                {l s='Password' mod='hipay_enterprise'}
                            </label>
                            <div class="col-lg-6">
                                <input class="form-control" type="password" name="api_password_production"
                                       value="{$config_hipay.account.production.api_password_production}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="required control-label col-lg-2">
                                {l s='Secret passphrase' mod='hipay_enterprise'}
                            </label>
                            <div class="col-lg-6">
                                <input class="form-control" type="password"
                                       name="api_secret_passphrase_production"
                                       value="{$config_hipay.account.production.api_secret_passphrase_production}">
                            </div>
                        </div>
                        <h5 class="col-lg-offset-2 col-xs-offset-4">{l s='Tokenization (Public)' mod='hipay_enterprise'}</h5>
                        <div class="form-group">
                            <label class="required control-label col-lg-2">
                                {l s='Username' mod='hipay_enterprise'}
                            </label>
                            <div class="col-lg-6">
                                <input class="form-control" type="text"
                                       name="api_tokenjs_username_production"
                                       value="{$config_hipay.account.production.api_tokenjs_username_production}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="required control-label col-lg-2">
                                {l s='Password' mod='hipay_enterprise'}
                            </label>
                            <div class="col-lg-6">
                                <input class="form-control" type="password"
                                       name="api_tokenjs_password_publickey_production"
                                       value="{$config_hipay.account.production.api_tokenjs_password_publickey_production}">
                            </div>
                        </div>
                        <h5 class="col-lg-offset-2 col-xs-offset-4">{l s='MO/TO account credentials' mod='hipay_enterprise'}</h5>
                        <div class="form-group">
                            <label class="control-label col-lg-2">
                                {l s='Username' mod='hipay_enterprise'}
                            </label>
                            <div class="col-lg-6">
                                <input class="form-control" type="text" name="api_moto_username_production"
                                       value="{$config_hipay.account.production.api_moto_username_production}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-lg-2">
                                {l s='Password' mod='hipay_enterprise'}
                            </label>
                            <div class="col-lg-6">
                                <input class="form-control" type="text" name="api_moto_password_production"
                                       value="{$config_hipay.account.production.api_moto_password_production}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-lg-2">
                                {l s='Secret passphrase' mod='hipay_enterprise'}
                            </label>
                            <div class="col-lg-6">
                                <input class="form-control" type="text"
                                       name="api_moto_secret_passphrase_production"
                                       value="{$config_hipay.account.production.api_moto_secret_passphrase_production}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel" id="fieldset_0">
                <div class="form-wrapper">
                    <div class="panel-heading">
                        <a data-toggle="collapse" href="#collapseSandbox" aria-expanded="true"
                           aria-controls="collapseSandbox">
                            {l s='Sandbox configuration' mod='hipay_enterprise'} <i id="chevronSand"
                                                                                    class="pull-right chevron icon icon-chevron-up"></i>
                        </a>
                    </div>
                    <div class="collapse in" id="collapseSandbox">
                        <div class="form-group">
                            <div class="col-lg-6 col-lg-offset-2">
                                <div class="alert alert-info">
                                    {l s='Generated in your HiPay Enterprise back office'  mod='hipay_enterprise'} (<a
                                            title="BO Hipay" target="_blank" href="https://merchant.hipay-tpp.com">https://merchant.hipay-tpp.com</a>)
                                    via </br >
                                    <strong>"Integration” => “Security Settings” => “Api credentials” => “Credentials
                                        accessibility”</strong>, {l s='these API credentials are required to use the HiPay Enterprise module.'  mod='hipay_enterprise'}</br >
                                    {l s='You must generate public and private credentials.'  mod='hipay_enterprise'}
                                    {l s='You can also set specific credentials for your Mail Order to Order payments. If they are defined then they will be used when making your payments via the back office' mod='hipay_enterprise'}
                                </div>
                            </div>
                        </div>
                        <h5 class="col-lg-offset-2 col-xs-offset-4">{l s='Account (Private)' mod='hipay_enterprise'}</h5>
                        <div class="form-group">
                            <label class="required control-label col-lg-2">
                                {l s='Username' mod='hipay_enterprise'}
                            </label>
                            <div class="col-lg-6">
                                <input class="form-control" type="text" name="api_username_sandbox"
                                       value="{$config_hipay.account.sandbox.api_username_sandbox}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="required control-label col-lg-2">
                                {l s='Password' mod='hipay_enterprise'}
                            </label>
                            <div class="col-lg-6">
                                <input class="form-control" type="password" name="api_password_sandbox"
                                       value="{$config_hipay.account.sandbox.api_password_sandbox}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="required control-label col-lg-2">
                                {l s='Secret passphrase' mod='hipay_enterprise'}
                            </label>
                            <div class="col-lg-6">
                                <input class="form-control" type="password" name="api_secret_passphrase_sandbox"
                                       value="{$config_hipay.account.sandbox.api_secret_passphrase_sandbox}">
                            </div>
                        </div>
                        <h5 class="col-lg-offset-2 col-xs-offset-4">{l s='Tokenization (Public)' mod='hipay_enterprise'}</h5>
                        <div class="form-group">
                            <label class="required control-label col-lg-2">
                                {l s='Username' mod='hipay_enterprise'}
                            </label>
                            <div class="col-lg-6">
                                <input class="form-control" type="text" name="api_tokenjs_username_sandbox"
                                       value="{$config_hipay.account.sandbox.api_tokenjs_username_sandbox}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="required control-label col-lg-2">
                                {l s='Password' mod='hipay_enterprise'}
                            </label>
                            <div class="col-lg-6">
                                <input class="form-control" type="password"
                                       name="api_tokenjs_password_publickey_sandbox"
                                       value="{$config_hipay.account.sandbox.api_tokenjs_password_publickey_sandbox}">
                            </div>
                        </div>
                        <h5 class="col-lg-offset-2 col-xs-offset-4">{l s='MO/TO account credentials' mod='hipay_enterprise'}</h5>
                        <div class="form-group">
                            <label class="control-label col-lg-2">
                                {l s='Username' mod='hipay_enterprise'}
                            </label>
                            <div class="col-lg-6">
                                <input class="form-control" type="text" name="api_moto_username_sandbox"
                                       value="{$config_hipay.account.sandbox.api_moto_username_sandbox}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-lg-2">
                                {l s='Password' mod='hipay_enterprise'}
                            </label>
                            <div class="col-lg-6">
                                <input class="form-control" type="text" name="api_moto_password_sandbox"
                                       value="{$config_hipay.account.sandbox.api_moto_password_sandbox}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-lg-2">
                                {l s='Secret passphrase' mod='hipay_enterprise'}
                            </label>
                            <div class="col-lg-6">
                                <input class="form-control" type="text"
                                       name="api_moto_secret_passphrase_sandbox"
                                       value="{$config_hipay.account.sandbox.api_moto_secret_passphrase_sandbox}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel" id="hashAlgorithm">
                <div class="form-wrapper">
                    <div class="panel-heading">
                        <a data-toggle="collapse" href="#collapseSandbox" aria-expanded="true"
                           aria-controls="collapseSandbox">
                            {l s='Hash Algorithm configuration' mod='hipay_enterprise'} <i id="chevronSand"
                                                                                    class="pull-right chevron icon icon-chevron-up"></i>
                        </a>
                    </div>
                    <div class="collapse in" id="collapseSandbox">
                        <div class="form-group">
                            <div class="col-lg-6 col-lg-offset-2">
                                <div class="alert alert-info">
                                    {l s='If the hash configuration is different than the one set in your Hipay back office, then the notifications will not work. Check that both values match.'  mod='hipay_enterprise'}
                                 </div>
                            </div>
                        </div>
                        <h5 class="col-lg-offset-2 col-xs-offset-4">{l s='Hash Algorithm' mod='hipay_enterprise'}</h5>
                        <div class="form-group">
                            <label class="required control-label col-lg-2">
                                {l s='Production' mod='hipay_enterprise'}
                            </label>
                            <div class="col-lg-6">
                                <div class="form-group">
                                        <div class="">
                                            <select name="hash_algorithm_production" class="col-lg-2" id="hash_algorithm_production" disabled="disabled">
                                                <option value="SHA1"
                                                        {if $config_hipay.account.hash_algorithm.production == "SHA1"}selected="selected" {/if} >SHA-1</option>
                                                <option value="SHA256"
                                                    {if $config_hipay.account.hash_algorithm.production == "SHA256"}selected="selected" {/if} >SHA-256</option>
                                                <option value="SHA512"
                                                        {if $config_hipay.account.hash_algorithm.production == "SHA512"}selected="selected" {/if} >SHA-512</option>
                                            </select>
                                        </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="required control-label col-lg-2">
                                {l s='Test' mod='hipay_enterprise'}
                            </label>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <div class="">
                                        <select name="hash_algorithm_test" class="col-lg-2" id="hash_algorithm_test" disabled="disabled">
                                            <option value="SHA1"
                                                    {if $config_hipay.account.hash_algorithm.test == "SHA1"}selected="selected" {/if} >SHA-1</option>
                                            <option value="SHA256"
                                                {if $config_hipay.account.hash_algorithm.test == "SHA256"}selected="selected" {/if} >SHA-256</option>
                                            <option value="SHA512"
                                                    {if $config_hipay.account.hash_algorithm.test == "SHA512"}selected="selected" {/if} >SHA-512</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="required control-label col-lg-2">
                                {l s='MO/TO Production ' mod='hipay_enterprise'}
                            </label>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <div class="">
                                        <select name="hash_algorithm_production_moto" class="col-lg-2" id="hash_algorithm_production_moto" disabled="disabled">
                                            <option value="SHA1"
                                                    {if $config_hipay.account.hash_algorithm.production_moto == "SHA1"}selected="selected" {/if} >SHA-1</option>
                                            <option value="SHA256"
                                                {if $config_hipay.account.hash_algorithm.production_moto == "SHA256"}selected="selected" {/if} >SHA-256</option>
                                            <option value="SHA512"
                                                    {if $config_hipay.account.hash_algorithm.production_moto == "SHA512"}selected="selected" {/if} >SHA-512</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="required control-label col-lg-2">
                                {l s='MO/TO Test' mod='hipay_enterprise'}
                            </label>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <div class="">
                                        <select name="hash_algorithm_test_moto" class="col-lg-2" id="hash_algorithm_test_moto" disabled="disabled">
                                            <option value="SHA1"
                                                    {if $config_hipay.account.hash_algorithm.test_moto == "SHA1"}selected="selected" {/if} >SHA-1</option>
                                            <option value="SHA256"
                                                {if $config_hipay.account.hash_algorithm.test_moto == "SHA256"}selected="selected" {/if} >SHA-256</option>
                                            <option value="SHA512"
                                                    {if $config_hipay.account.hash_algorithm.test_moto == "SHA512"}selected="selected" {/if} >SHA-512</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <span class="col-lg-2"></span>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <input type="button" value="{l s='Synchronize Hash Algorithm' mod='hipay_enterprise'}" id="synchronize-hash" class="btn btn-default" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel" id="fieldset_0">
                <div class="form-wrapper">
                    <div class="panel-heading">
                        <a data-toggle="collapse" href="#collapseTechnical" aria-expanded="false"
                           aria-controls="collapseTechnical">
                            {l s='Technical configuration' mod='hipay_enterprise'} <i id="chevronTec"
                                                                                      class="pull-right chevron icon icon-chevron-down"></i>
                        </a>
                    </div>
                    <div class="collapse" id="collapseTechnical">
                        <div class="test">
                            <div class="row">
                                <div class="col-lg-6 col-lg-offset-2">
                                    <div class="alert alert-info">
                                        {l s='If your server is behind a proxy, populate its information so that calls to the HiPay gateway can work.'  mod='hipay_enterprise'}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-lg-2">
                                </label>
                                <div class="col-lg-6">
                                    <h5>Proxy settings</h5>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-lg-2">
                                    {l s='Host' mod='hipay_enterprise'}
                                </label>
                                <div class="col-lg-6">
                                    <input class="form-control" type="text" name="host_proxy"
                                           value="{$config_hipay.account.global.host_proxy}">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-lg-2">
                                    {l s='Port' mod='hipay_enterprise'}
                                </label>
                                <div class="col-lg-6">
                                    <input class="form-control" type="text" name="port_proxy"
                                           value="{$config_hipay.account.global.port_proxy}">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-lg-2">
                                    {l s='User' mod='hipay_enterprise'}
                                </label>
                                <div class="col-lg-6">
                                    <input class="form-control" type="text" name="user_proxy"
                                           value="{$config_hipay.account.global.user_proxy}">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-lg-2">
                                    {l s='Password' mod='hipay_enterprise'}
                                </label>
                                <div class="col-lg-6">
                                    <input class="form-control" type="text" name="password_proxy"
                                           value="{$config_hipay.account.global.password_proxy}">
                                </div>
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

<script type="text/javascript">
    $(document).ready(function() {

        function updateValueHashingAlgorithm(plaform,value) {
            $("div#hashAlgorithm select#hash_algorithm_" + plaform).val(value);
        }

        $('#synchronize-hash').on('click', function() {
            if (confirm("{l s='Are you sure you want to sync the hashing configuration for notifications ?' mod='hipay_enterprise'}")) {
                $.get('{$syncLink}&ajax=1&action=SynchronizeHashing',
                        function (response) {
                            for (var platform in response) {
                                if(response[platform].status == "success") {
                                    showSuccessMessage(response[platform].message);
                                }else{
                                    showErrorMessage(response[platform].message);
                                }

                                if (response[platform].hasOwnProperty("value")) {
                                    updateValueHashingAlgorithm(platform, response[platform].value);
                                }
                            }
                        }
                )
                        .fail(function () {
                            showErrorMessage("{l s='An error has occured. Please try again' mod='hipay_enterprise'}");
                        });
            }
        });


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

    });
</script>

