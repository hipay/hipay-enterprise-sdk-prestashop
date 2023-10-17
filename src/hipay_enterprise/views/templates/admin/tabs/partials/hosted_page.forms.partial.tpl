<div id="hostedconf">
    <div class="form-group">
        <label class="control-label col-lg-3">
            <span>
                {l s='Enable API V2' mod='hipay_enterprise'}
            </span>
        </label>
        <div class="col-lg-9">
            <span class="switch prestashop-switch fixed-width-lg">
                <input type="radio" name="enable_api_v2" id="api_v2_switchmode_on" value="1"
                    {if $HiPay_config_hipay.payment.global.enable_api_v2 }checked="checked"{/if}>
                <label for="api_v2_switchmode_on">{l s='Yes' mod='hipay_enterprise'}</label>
                <input type="radio" name="enable_api_v2" id="api_v2_switchmode_off" value="0"
                    {if $HiPay_config_hipay.payment.global.enable_api_v2 == false}checked="checked"{/if}>
                <label for="api_v2_switchmode_off">{l s='No' mod='hipay_enterprise'}</label>
                <a class="slide-button btn"></a>
            </span>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-lg-3">
            <span>
                {l s='Display Hosted Page' mod='hipay_enterprise'}
            </span>
        </label>
        <div class="col-lg-2">
            <select name="display_hosted_page" class="" id="display_hosted_page">
                <option value="redirect"
                    {if $HiPay_config_hipay.payment.global.display_hosted_page == "redirect"}selected="selected" {/if} >{l s='Redirect' mod='hipay_enterprise'}</option>
                <option value="iframe"
                    {if $HiPay_config_hipay.payment.global.display_hosted_page == "iframe"}selected="selected" {/if} >{l s='Iframe' mod='hipay_enterprise'}</option>
            </select>
        </div>
    </div>

    <!-- SWITCH MODE START -->
    <div class="form-group">
        <label class="control-label col-lg-3">
            <span class="label-tooltip"
                    data-toggle="tooltip"
                    data-html="true"
                    title=""
                    data-original-title="{l s='Display card selector on iFrame or hosted page.' mod='hipay_enterprise'}">
                {l s='Display card selector' mod='hipay_enterprise'}
            </span>
        </label>
        <div class="col-lg-9">
            <span class="switch prestashop-switch fixed-width-lg">
                <input type="radio" name="display_card_selector" id="card_selector_switchmode_on" value="1"
                    {if $HiPay_config_hipay.payment.global.display_card_selector }checked="checked"{/if}>
                <label for="card_selector_switchmode_on">{l s='Yes' mod='hipay_enterprise'}</label>
                <input type="radio" name="display_card_selector" id="card_selector_switchmode_off" value="0"
                    {if $HiPay_config_hipay.payment.global.display_card_selector == false}checked="checked"{/if}>
                <label for="card_selector_switchmode_off">{l s='No' mod='hipay_enterprise'}</label>
                <a class="slide-button btn"></a>
            </span>
        </div>
    </div>
    <!-- SWITCH MODE END -->

    <div class="form-group">
        <label class="control-label col-lg-3">
            <span>
                {l s='CSS url' mod='hipay_enterprise'}
            </span>
        </label>
        <div class="col-lg-4">
            <input class="form-control" type="text" name="css_url"
                   value="{$HiPay_config_hipay.payment.global.css_url}">
            <p class="help-block">
                {l s='URL to your CSS (style sheet) to customize your hosted page or iFrame (Important: the HTTPS protocol is required).' mod='hipay_enterprise'}
            </p>
        </div>
    </div>
</div>
