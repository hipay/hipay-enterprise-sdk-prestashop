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
      <form>
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
                  <input type="radio" name="settings_switchmode" id="settings_switchmode_on" value="1"
                         {if $config_hipay.sandbox_mode }checked="checked"{/if}>
                  <label for="settings_switchmode_on">{l s='Yes' mod='hipay_professional'}</label>
                  <input type="radio" name="settings_switchmode" id="settings_switchmode_off" value="0"
                         {if $config_hipay.sandbox_mode == false}checked="checked"{/if}>
                  <label for="settings_switchmode_off">{l s='No' mod='hipay_professional'}</label>
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
        <!-- PRODUCTION FORM START -->
        <div class="col-md-6 trait">
            <h4>{l s='Production configuration' mod='hipay_professional'}</h4>
            
        </div>
        <!-- PRODUCTION FORM END -->
        <!-- SANDBOX FORM START -->
        <div class="col-md-6 ">
            <h4>{l s='Sandbox configuration' mod='hipay_professional'}</h4>
        </div>
        <!-- SANDBOX FORM END -->
      </div>
    </form>
    </div>
  </div>
</div>
