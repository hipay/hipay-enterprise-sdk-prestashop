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
<div class="panel  hipay-tabs" id="panel-global-settings">
    <form method="post" class="defaultForm form-horizontal"
        action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" id="global_form">
        <div class="form-wrapper">
            <div class="panel-heading">
                <i class="icon icon-credit-card"></i> {l s='Global settings' mod='hipay_enterprise'}
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3">
                    <span>
                        {l s='Operating mode' mod='hipay_enterprise'}
                    </span>
                </label>
                <div class="col-lg-9">
                    <div class="row">
                        <select name="operating_mode" class="col-lg-2" id="operating_mode">
                            <option value="hosted_page"
                                {if $config_hipay.payment.global.operating_mode.UXMode == "hosted_page"}selected="selected"
                                {/if}>{l s='Hosted page' mod='hipay_enterprise'}</option>
                            <option value="hosted_fields"
                                {if $config_hipay.payment.global.operating_mode.UXMode == "hosted_fields"}selected="selected"
                                {/if}>{l s='Hosted Fields' mod='hipay_enterprise'}</option>
                        </select>
                    </div>
                    <p class="help-block">
                    <ul class="hipay-notice-list">
                        <li><b>{l s='Hosted page' mod='hipay_enterprise'}</b>
                            :
                            {l s='The customer is redirected to a secured payment page hosted by HiPay.' mod='hipay_enterprise'}
                        </li>
                        <li><b>{l s='Hosted Fields' mod='hipay_enterprise'}</b>
                            :
                            {l s='The customer will fill his bank information directly on merchants website but fields are hosted by HiPay. This mode is only available for credit card, other payement method will be handled in the API mode' mod='hipay_enterprise'}
                        </li>
                    </ul>
                    </p>
                </div>
            </div>

            {* Hosted Page Form *}
            {include file='../partials/hosted_page.forms.partial.tpl'}
            {*/ Hosted Page Form /*}

            {* Hosted Fields Form *}
            {include file='../partials/hosted_fields.forms.partial.tpl'}
            {*/ Hosted Fields Form /*}

            <div class="form-group">
                <label class="control-label col-lg-3">
                    {l s='Capture' mod='hipay_enterprise'}
                </label>
                <div class="col-lg-9">
                    <div class="row">
                        <select name="capture_mode" class="col-lg-2" id="capture_mode">
                            <option value="automatic"
                                {if $config_hipay.payment.global.capture_mode == "automatic"}selected="selected" {/if}>
                                {l s='Automatic' mod='hipay_enterprise'}</option>
                            <option value="manual"
                                {if $config_hipay.payment.global.capture_mode == "manual"}selected="selected" {/if}>
                                {l s='Manual' mod='hipay_enterprise'}</option>
                        </select>
                    </div>
                    <p class="help-block">
                    <ul class="hipay-notice-list">
                        <li><b>Manual</b>
                            :
                            {l s='All transactions will be captured manually either from the Hipay Back office or from your admin prestashop' mod='hipay_enterprise'}
                        </li>
                        <li><b>Automatic</b>
                            :{l s='All transactions will be captured automatically.' mod='hipay_enterprise'}</li>
                    </ul>
                    </p>
                </div>
            </div>


            <!-- SWITCH MODE START -->
            <div class="form-group">
                <label class="control-label col-lg-3">
                    <span class="label-tooltip" data-toggle="tooltip" data-html="true" title=""
                        data-original-title="{l s='Allow users to save their card and use saved cards.' mod='hipay_enterprise'}">
                        {l s='Use Oneclick' mod='hipay_enterprise'}
                    </span>
                </label>
                <div class="col-lg-9">
                    {if $config_hipay.account.global.sandbox_mode}
                        {assign var="oneclickAvailable" value=(!empty($config_hipay.account.sandbox.api_tokenjs_username_sandbox) && !empty($config_hipay.account.sandbox.api_tokenjs_password_publickey_sandbox))}
                    {else}
                        {assign var="oneclickAvailable" value=(!empty($config_hipay.account.production.api_tokenjs_username_production) && !empty($config_hipay.account.production.api_tokenjs_password_publickey_production))}
                    {/if}

                    <span class="switch prestashop-switch fixed-width-lg label-tooltip" {if !$oneclickAvailable}
                            data-toggle="tooltip" data-html="true" title=""
                            data-original-title="{l s='Public credentials must be set in module settings to use Oneclick' mod='hipay_enterprise'}"
                        {/if}>
                        <input type="radio" name="card_token" id="card_token_switchmode_on" value="1"
                            {if $config_hipay.payment.global.card_token && $oneclickAvailable}checked="checked" {/if}
                            {if !$oneclickAvailable}disabled{/if}>
                        <label for="card_token_switchmode_on">{l s='Yes' mod='hipay_enterprise'}</label>
                        <input type="radio" name="card_token" id="card_token_switchmode_off" value="0"
                            {if $config_hipay.payment.global.card_token == false || !$oneclickAvailable}checked="checked"
                                {/if} {if !$oneclickAvailable}disabled{/if}>
                            <label for="card_token_switchmode_off">{l s='No' mod='hipay_enterprise'}</label>
                            <a class="slide-button btn"></a>
                        </span>
                    </div>
                </div>
                <!-- SWITCH MODE END -->
                <!-- SWITCH MODE START -->
                <div class="form-group">
                    <label class="control-label col-lg-3">
                        <span class="label-tooltip" data-toggle="tooltip" data-html="true" title=""
                            data-original-title="{l s='Send cart informations on HiPay API call.' mod='hipay_enterprise'}">
                            {l s='Customer\'s cart sending' mod='hipay_enterprise'}
                        </span>
                    </label>
                    <div class="col-lg-9">
                        <span class="switch prestashop-switch fixed-width-lg">
                            <input type="radio" name="activate_basket" id="activate_basket_switchmode_on" value="1"
                                {if $config_hipay.payment.global.activate_basket }checked="checked" {/if}>
                            <label for="activate_basket_switchmode_on">{l s='Yes' mod='hipay_enterprise'}</label>
                            <input type="radio" name="activate_basket" id="activate_basket_switchmode_off" value="0"
                                {if $config_hipay.payment.global.activate_basket == false}checked="checked" {/if}>
                            <label for="activate_basket_switchmode_off">{l s='No' mod='hipay_enterprise'}</label>
                            <a class="slide-button btn"></a>
                        </span>
                        <p class="help-block">
                            <i class='icon icon-warning text-danger'></i>
                            {l s='If \'Round on the total\' is activated in prestashop configuration, cart will not be sent and payment method that force cart to be send will be disabled.' mod='hipay_enterprise'}
                        </p>
                    </div>
                </div>
                <!-- SWITCH MODE END -->


                <!-- SWITCH MODE START -->
                <div class="form-group">
                    <label class="control-label col-lg-3">
                        <span>
                            {l s='Keep cart when payment fails' mod='hipay_enterprise'}
                        </span>
                    </label>
                    <div class="col-lg-9">
                        <span class="switch prestashop-switch fixed-width-lg">
                            <input type="radio" name="regenerate_cart_on_decline"
                                id="regenerate_cart_on_decline_switchmode_on" value="1"
                                {if $config_hipay.payment.global.regenerate_cart_on_decline }checked="checked" {/if}>
                            <label for="regenerate_cart_on_decline_switchmode_on">{l s='Yes' mod='hipay_enterprise'}</label>
                            <input type="radio" name="regenerate_cart_on_decline"
                                id="regenerate_cart_on_decline_switchmode_off" value="0"
                                {if $config_hipay.payment.global.regenerate_cart_on_decline == false}checked="checked"
                                {/if}>
                            <label for="regenerate_cart_on_decline_switchmode_off">{l s='No' mod='hipay_enterprise'}</label>
                            <a class="slide-button btn"></a>
                        </span>
                    </div>
                </div>
                <!-- SWITCH MODE END -->
                <div class="form-group">
                    <label class="control-label col-lg-3">
                        <span class="label-tooltip" data-toggle="tooltip" data-html="true" title=""
                            data-original-title="{l s='Logs information' mod='hipay_enterprise'}">
                            {l s='Logs information' mod='hipay_enterprise'}
                        </span>
                    </label>
                    <div class="col-lg-9">
                        <span class="switch prestashop-switch fixed-width-lg">
                            <input type="radio" name="log_infos" id="log_infos_switchmode_on" value="1"
                                {if $config_hipay.payment.global.log_infos }checked="checked" {/if}>
                            <label for="log_infos_switchmode_on">{l s='Yes' mod='hipay_enterprise'}</label>
                            <input type="radio" name="log_infos" id="log_infos_switchmode_off" value="0"
                                {if $config_hipay.payment.global.log_infos == false}checked="checked" {/if}>
                            <label for="log_infos_switchmode_off">{l s='No' mod='hipay_enterprise'}</label>
                            <a class="slide-button btn"></a>
                        </span>
                    </div>
                </div>

                <div id="sdk_js_url" class="form-group">
                    <label class="control-label col-lg-3">
                        <span>
                            {l s='SDK JS url' mod='hipay_enterprise'}
                        </span>
                    </label>
                    <div class="col-lg-4">
                        <input class="form-control" type="text" name="sdk_js_url"
                            value="{$config_hipay.payment.global.sdk_js_url}">
                    </div>
                </div>

                {* 3DS Form *}
                {include file='../partials/3ds.forms.partial.tpl'}
                {*/ 3DS Form /*}
                <div class="form-group">
                    <label class="control-label col-lg-3">
                        <span>
                            {l s='Send url Notification' mod='hipay_enterprise'}
                        </span>
                    </label>
                    <div class="col-lg-9">
                        <div class="row">
                            <span class="switch prestashop-switch fixed-width-lg">
                                <input type="radio" name="send_url_notification" id="send_url_notification_switchmode_on"
                                    value="1" {if $config_hipay.payment.global.send_url_notification }checked="checked"
                                    {/if}>
                                <label for="send_url_notification_switchmode_on">{l s='Yes' mod='hipay_enterprise'}</label>
                                <input type="radio" name="send_url_notification" id="send_url_notification_switchmode_off"
                                    value="0"
                                    {if $config_hipay.payment.global.send_url_notification == false}checked="checked" {/if}>
                                <label for="send_url_notification_switchmode_off">{l s='No' mod='hipay_enterprise'}</label>
                                <a class="slide-button btn"></a>
                            </span>
                            <p class="help-block">
                                <i class='icon icon-warning text-danger'></i>
                                {l s='If so, then the URL of your site is sent during the payment and notifications will be sent to this URL. To use only for multi site.' mod='hipay_enterprise'}
                            </p>
                        </div>
                    </div>
                </div>

            </div>
            <div class="panel-footer">
                <button type="submit" class="btn btn-default pull-left" name="submitCancel"><i
                        class="process-icon-eraser"></i>{l s='Discard changes' mod='hipay_enterprise'}
                </button>
                <button type="submit" class="btn btn-default btn btn-default pull-right" name="submitGlobalPaymentMethods">
                    <i class="process-icon-save"></i> {l s='Save configuration changes' mod='hipay_enterprise'}
                </button>
            </div>
        </form>
    </div>

    <script>
        $(document).ready(function() {

            // Display or hide specific input for hosted_page or Hosted_fields
            if ($("#operating_mode").val() == "hosted_page") {
                $("#hostedfieldsconf").hide();
                $("#sdk_js_url").hide();
            } else if ($("#operating_mode").val() == "hosted_fields") {
                $("#hostedconf").hide();
            } else {
                $("#hostedconf").hide();
                $("#hostedfieldsconf").hide();
            }

            $("#operating_mode").change(function() {
                if ($("#operating_mode").val() == "hosted_page") {
                    $("#hostedconf").show();
                    $("#hostedfieldsconf").hide();
                    $("#sdk_js_url").hide();
                } else if ($("#operating_mode").val() == "hosted_fields") {
                    $("#hostedconf").hide();
                    $("#hostedfieldsconf").show();
                    $("#sdk_js_url").show();
                } else {
                    $("#sdk_js_url").show();
                    $("#hostedconf").hide();
                    $("#hostedfieldsconf").hide();
                }
            });

            //Display 3ds config form
            if ($("#activate_3d_secure").val() == "0") {
                $("#3ds-rules").hide();
            }
            $("#activate_3d_secure").change(function() {
                if ($("#activate_3d_secure").val() == "0") {
                    $("#3ds-rules").hide();
                } else {
                    $("#3ds-rules").show();
                }
            });
        });
    </script>
