{**
* 2016 HiPay
*
* NOTICE OF LICENSE
*
*
* @author    HiPay <support.wallet@hipay.com>
* @copyright 2016 HiPay
* @license   https://github.com/hipay/hipay-wallet-sdk-prestashop/blob/master/LICENSE.md
*
*}
<div class="panel">
  <div id="setting-image-error" class="img-error"></div>
  <div id="setting-image-success" class="img-success"></div>
  <div class="row">
    <div class="col-md-12 col-xs-12">
      <form method="post" class="form-horizontal" action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" id="account_form">
      <!-- SWITCH MODE START -->
      <div class="row">
          <label class="control-label col-lg-3">
              <span class="label-tooltip"
                    data-toggle="tooltip"
                    data-html="true"
                    title=""
                    data-original-title="{l s='When in test mode, payment cards are not really charged. Activate this options for testing purposes only.' mod='hipay_professional'}">
                  {l s='Use test mode' mod='hipay_professional'}
              </span>
          </label>
          <div class="col-lg-9">
              <span class="switch prestashop-switch fixed-width-lg">
                  <input type="radio" name="sandbox_mode" id="account_switchmode_on" value="1"
                         {if $config_hipay.account.global.sandbox_mode }checked="checked"{/if}>
                  <label for="account_switchmode_on">{l s='Yes' mod='hipay_professional'}</label>
                  <input type="radio" name="sandbox_mode" id="account_switchmode_off" value="0"
                         {if $config_hipay.account.global.sandbox_mode == false}checked="checked"{/if}>
                  <label for="account_switchmode_off">{l s='No' mod='hipay_professional'}</label>
                  <a class="slide-button btn"></a>
              </span>
          </div>
      </div>
      <div class="row">
          <div class="col-md-12 col-xs-12">
              <p>
                  {l s='When in test mode, payment cards are not really charged. Activate this options for testing purposes only.' mod='hipay_professional'}
              </p>
          </div>
      </div>
      <!-- SWITCH MODE END -->
      <div class="row">

        <!-- GLOBAL FORM START -->
        <div class="col-md-12 ">
          <h4>{l s='Global configuration' mod='hipay_professional'}</h4>
          <hr>
          <div class="form-group">
            <label class="control-label col-lg-2">
                {l s='Host proxy' mod='hipay_professional'}
            </label>
            <div class="col-lg-9">
              <input class="form-control" type="text" name="host_proxy" value="{$config_hipay.account.global.host_proxy}">
            </div>
          </div>

          <div class="form-group">
            <label class="control-label col-lg-2">
                {l s='Port proxy' mod='hipay_professional'}
            </label>
            <div class="col-lg-9">
              <input class="form-control" type="text" name="port_proxy" value="{$config_hipay.account.global.port_proxy}">
            </div>
          </div>

          <div class="form-group">
            <label class="control-label col-lg-2">
                {l s='User proxy' mod='hipay_professional'}
            </label>
            <div class="col-lg-9">
              <input class="form-control" type="text" name="user_proxy" value="{$config_hipay.account.global.user_proxy}">
            </div>
          </div>

          <div class="form-group">
            <label class="control-label col-lg-2">
                {l s='Password proxy' mod='hipay_professional'}
            </label>
            <div class="col-lg-9">
              <input class="form-control" type="text" name="password_proxy" value="{$config_hipay.account.global.password_proxy}">
            </div>
          </div>
        </div>
        <!-- GLOBAL FORM END -->

        <!-- PRODUCTION FORM START -->
        <div class="col-md-6 trait">
            <h4>{l s='Production configuration' mod='hipay_professional'}</h4>
            <hr>

            <div class="form-group">
            	<label class="control-label col-lg-4">
            			{l s='Api username' mod='hipay_professional'}
            	</label>
            	<div class="col-lg-6">
            		<input class="form-control" type="text" name="api_username_production" value="{$config_hipay.account.production.api_username_production}">
            	</div>
            </div>

            <div class="form-group">
            	<label class="control-label col-lg-4">
            			{l s='Api password' mod='hipay_professional'}
            	</label>
            	<div class="col-lg-6">
            		<input class="form-control" type="text" name="api_password_production" value="{$config_hipay.account.production.api_password_production}">
            	</div>
            </div>

            <div class="form-group">
            	<label class="control-label col-lg-4">
            			{l s='Api TokenJS Username' mod='hipay_professional'}
            	</label>
            	<div class="col-lg-6">
            		<input class="form-control" type="text" name="api_tokenjs_username_production" value="{$config_hipay.account.production.api_tokenjs_username_production}">
            	</div>
            </div>

            <div class="form-group">
            	<label class="control-label col-lg-4">
            			{l s='Api TokenJS Password/Public Key ' mod='hipay_professional'}
            	</label>
            	<div class="col-lg-6">
            		<input class="form-control" type="text" name="api_tokenjs_password_publickey_production" value="{$config_hipay.account.production.api_tokenjs_password_publickey_production}">
            	</div>
            </div>

            <div class="form-group">
            	<label class="control-label col-lg-4">
            			{l s='Secret passphrase' mod='hipay_professional'}
            	</label>
            	<div class="col-lg-6">
            		<input class="form-control" type="text" name="api_secret_passphrase_production" value="{$config_hipay.account.production.api_secret_passphrase_production}">
            	</div>
            </div>

            <div class="form-group">
            	<label class="control-label col-lg-4">
            			{l s='Api MO/TO username' mod='hipay_professional'}
            	</label>
            	<div class="col-lg-6">
            		<input class="form-control" type="text" name="api_moto_username_production" value="{$config_hipay.account.production.api_moto_username_production}">
            	</div>
            </div>

            <div class="form-group">
            	<label class="control-label col-lg-4">
            			{l s='Api MO/TO password' mod='hipay_professional'}
            	</label>
            	<div class="col-lg-6">
            		<input class="form-control" type="text" name="api_moto_password_production" value="{$config_hipay.account.production.api_moto_password_production}">
            	</div>
            </div>

            <div class="form-group">
              <label class="control-label col-lg-4">
                  {l s='MO/TO Secret passphrase' mod='hipay_professional'}
              </label>
              <div class="col-lg-6">
                <input class="form-control" type="text" name="api_moto_secret_passphrase_production" value="{$config_hipay.account.production.api_moto_secret_passphrase_production}">
              </div>
            </div>

        </div>
        <!-- PRODUCTION FORM END -->
        <!-- SANDBOX FORM START -->
        <div class="col-md-6 ">
            <h4>{l s='Sandbox configuration' mod='hipay_professional'}</h4>
            <hr>
            <div class="form-group">
            	<label class="control-label col-lg-4">
            			{l s='Api username' mod='hipay_professional'}
            	</label>
            	<div class="col-lg-6">
            		<input class="form-control" type="text" name="api_username_sandbox" value="{$config_hipay.account.sandbox.api_username_sandbox}">
            	</div>
            </div>

            <div class="form-group">
            	<label class="control-label col-lg-4">
            			{l s='Api password' mod='hipay_professional'}
            	</label>
            	<div class="col-lg-6">
            		<input class="form-control" type="text" name="api_password_sandbox" value="{$config_hipay.account.sandbox.api_password_sandbox}">
            	</div>
            </div>

            <div class="form-group">
            	<label class="control-label col-lg-4">
            			{l s='Api TokenJS Username' mod='hipay_professional'}
            	</label>
            	<div class="col-lg-6">
            		<input class="form-control" type="text" name="api_tokenjs_username_sandbox" value="{$config_hipay.account.sandbox.api_tokenjs_username_sandbox}">
            	</div>
            </div>

            <div class="form-group">
            	<label class="control-label col-lg-4">
            			{l s='Api TokenJS Password/Public Key ' mod='hipay_professional'}
            	</label>
            	<div class="col-lg-6">
            		<input class="form-control" type="text" name="api_tokenjs_password_publickey_sandbox" value="{$config_hipay.account.sandbox.api_tokenjs_password_publickey_sandbox}">
            	</div>
            </div>

            <div class="form-group">
            	<label class="control-label col-lg-4">
            			{l s='Secret passphrase' mod='hipay_professional'}
            	</label>
            	<div class="col-lg-6">
            		<input class="form-control" type="text" name="api_secret_passphrase_sandbox" value="{$config_hipay.account.sandbox.api_secret_passphrase_sandbox}">
            	</div>
            </div>

            <div class="form-group">
            	<label class="control-label col-lg-4">
            			{l s='Api MO/TO username' mod='hipay_professional'}
            	</label>
            	<div class="col-lg-6">
            		<input class="form-control" type="text" name="api_moto_username_sandbox" value="{$config_hipay.account.sandbox.api_moto_username_sandbox}">
            	</div>
            </div>

            <div class="form-group">
            	<label class="control-label col-lg-4">
            			{l s='Api MO/TO password' mod='hipay_professional'}
            	</label>
            	<div class="col-lg-6">
            		<input class="form-control" type="text" name="api_moto_password_sandbox" value="{$config_hipay.account.sandbox.api_moto_password_sandbox}">
            	</div>
            </div>

            <div class="form-group">
              <label class="control-label col-lg-4">
                  {l s='MO/TO Secret passphrase' mod='hipay_professional'}
              </label>
              <div class="col-lg-6">
                <input class="form-control" type="text" name="api_moto_secret_passphrase_sandbox" value="{$config_hipay.account.sandbox.api_moto_secret_passphrase_sandbox}">
              </div>
            </div>

        </div>
        <!-- SANDBOX FORM END -->
        <div class="row">
            <div class="col-md-12 col-xs-12">
                <button type="submit" class="btn btn-default pull-left" name="submitCancel"><i
                            class="process-icon-eraser"></i>{l s='Discard changes' mod='hipay_professional'}
                </button>
                <button type="submit" class="btn btn-default btn btn-default pull-right" name="submitAccount">
                    <i class="process-icon-save"></i>{l s='Save configuration changes' mod='hipay_professional'}
                </button>
            </div>
        </div>
      </div>
    </form>
    </div>
  </div>
</div>
