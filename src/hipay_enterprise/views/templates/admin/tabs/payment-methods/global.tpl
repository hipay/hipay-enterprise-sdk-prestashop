<div class="panel" id="fieldset_0">
    <form method="post" class="defaultForm form-horizontal" action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" id="credit_card_form">
        <div class="form-wrapper">
            <h4><i class="icon icon-credit-card"></i> {l s='Global settings' mod='hipay_enterprise'}</h4>
            <hr/>
            <div class="form-group">
                <label class="control-label col-lg-3">
                    <span class="label-tooltip"
                          data-toggle="tooltip"
                          data-html="true"
                          title=""
                          data-original-title="{l s='API : The customer will fill his bank information directly on merchants’ website. <br/> Hosted : The customer is redirected to a secured payment page hosted by HiPay.' mod='hipay_enterprise'}">
                        {l s='Operating mode' mod='hipay_enterprise'}
                    </span>
                </label>
                <div class="col-lg-9">
                    <select name="operating_mode" class="col-lg-6" id="operating_mode">
                        <option value="api" {if $config_hipay.payment.global.operating_mode == "api"}selected="selected" {/if} >{l s='Api' mod='hipay_enterprise'}</option>
                        <option value="hosted_page" {if $config_hipay.payment.global.operating_mode == "hosted_page"}selected="selected" {/if} >{l s='Hosted page' mod='hipay_enterprise'}</option>
                    </select>
                </div>
            </div>

            <div id="hostedconf">

                <div class="form-group">
                    <label class="control-label col-lg-3">
                        <span >
                            {l s='Display Hosted Page' mod='hipay_enterprise'}
                        </span>
                    </label>
                    <div class="col-lg-9">
                        <select name="display_hosted_page" class="col-lg-6" id="display_hosted_page">
                            <option value="redirect" {if $config_hipay.payment.global.display_hosted_page == "redirect"}selected="selected" {/if} >{l s='Redirect' mod='hipay_enterprise'}</option>
                            <option value="iframe" {if $config_hipay.payment.global.display_hosted_page == "iframe"}selected="selected" {/if} >{l s='Iframe' mod='hipay_enterprise'}</option>
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
                                   {if $config_hipay.payment.global.display_card_selector }checked="checked"{/if}>
                            <label for="card_selector_switchmode_on">{l s='Yes' mod='hipay_enterprise'}</label>
                            <input type="radio" name="display_card_selector" id="card_selector_switchmode_off" value="0"
                                   {if $config_hipay.payment.global.display_card_selector == false}checked="checked"{/if}>
                            <label for="card_selector_switchmode_off">{l s='No' mod='hipay_enterprise'}</label>
                            <a class="slide-button btn"></a>
                        </span>
                    </div>
                </div>
                <!-- SWITCH MODE END -->

                <div class="form-group">
                    <label class="control-label col-lg-3">
                        <span class="label-tooltip"
                              data-toggle="tooltip"
                              data-html="true"
                              title=""
                              data-original-title="{l s='URL to your CSS (style sheet) to customize your hosted page or iFrame (Important: the HTTPS protocol is required).' mod='hipay_enterprise'}">
                            {l s='CSS url' mod='hipay_enterprise'}
                        </span>
                    </label>
                    <div class="col-lg-9">
                        <input class="form-control" type="text" name="css_url"
                               value="{$config_hipay.payment.global.css_url}">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3">
                    {l s='Capture' mod='hipay_enterprise'}
                </label>
                <div class="col-lg-9">
                    <select name="capture_mode" class="col-lg-6" id="capture_mode">
                        <option value="manual" {if $config_hipay.payment.global.capture_mode == "manual"}selected="selected" {/if} >{l s='Manual mode' mod='hipay_enterprise'}</option>
                        <option value="automatic" {if $config_hipay.payment.global.capture_mode == "automatic"}selected="selected" {/if} >{l s='Automatic mode' mod='hipay_enterprise'}</option>
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
                          data-original-title="{l s='Allow users to save their card and use saved cards.' mod='hipay_enterprise'}">
                        {l s='Use Oneclick' mod='hipay_enterprise'}
                    </span>
                </label>
                <div class="col-lg-9">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" name="card_token" id="card_token_switchmode_on" value="1"
                               {if $config_hipay.payment.global.card_token }checked="checked"{/if}>
                        <label for="card_token_switchmode_on">{l s='Yes' mod='hipay_enterprise'}</label>
                        <input type="radio" name="card_token" id="card_token_switchmode_off" value="0"
                               {if $config_hipay.payment.global.card_token == false}checked="checked"{/if}>
                        <label for="card_token_switchmode_off">{l s='No' mod='hipay_enterprise'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                </div>
            </div>
            <!-- SWITCH MODE END -->        


            <!-- SWITCH MODE START -->
            <div class="form-group">
                <label class="control-label col-lg-3">
                    <span >
                        {l s='Enable electronic signature for SEPA Direct Debit' mod='hipay_enterprise'}
                    </span>
                </label>
                <div class="col-lg-9">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" name="electronic_signature" id="electronic_signature_switchmode_on" value="1"
                               {if $config_hipay.payment.global.electronic_signature }checked="checked"{/if}>
                        <label for="electronic_signature_switchmode_on">{l s='Yes' mod='hipay_enterprise'}</label>
                        <input type="radio" name="electronic_signature" id="electronic_signature_switchmode_off" value="0"
                               {if $config_hipay.payment.global.electronic_signature == false}checked="checked"{/if}>
                        <label for="electronic_signature_switchmode_off">{l s='No' mod='hipay_enterprise'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                </div>
            </div>
            <!-- SWITCH MODE END -->        


            <!-- SWITCH MODE START -->
            <div class="form-group">
                <label class="control-label col-lg-3">
                    <span class="label-tooltip"
                          data-toggle="tooltip"
                          data-html="true"
                          title=""
                          data-original-title="{l s='Send cart informations on HiPay API call.' mod='hipay_enterprise'}">
                        {l s='Customer\'s cart sending' mod='hipay_enterprise'}
                    </span>
                </label>
                <div class="col-lg-9">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" name="activate_basket" id="activate_basket_switchmode_on" value="1"
                               {if $config_hipay.payment.global.activate_basket }checked="checked"{/if}>
                        <label for="activate_basket_switchmode_on">{l s='Yes' mod='hipay_enterprise'}</label>
                        <input type="radio" name="activate_basket" id="activate_basket_switchmode_off" value="0"
                               {if $config_hipay.payment.global.activate_basket == false}checked="checked"{/if}>
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
                    <span >
                        {l s='Keep cart when payment fail' mod='hipay_enterprise'}
                    </span>
                </label>
                <div class="col-lg-9">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" name="regenerate_cart_on_decline" id="regenerate_cart_on_decline_switchmode_on" value="1"
                               {if $config_hipay.payment.global.regenerate_cart_on_decline }checked="checked"{/if}>
                        <label for="regenerate_cart_on_decline_switchmode_on">{l s='Yes' mod='hipay_enterprise'}</label>
                        <input type="radio" name="regenerate_cart_on_decline" id="regenerate_cart_on_decline_switchmode_off" value="0"
                               {if $config_hipay.payment.global.regenerate_cart_on_decline == false}checked="checked"{/if}>
                        <label for="regenerate_cart_on_decline_switchmode_off">{l s='No' mod='hipay_enterprise'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                </div>
            </div>
            <!-- SWITCH MODE END -->      

            <div class="form-group">
                <label class="control-label col-lg-3">
                    {l s='Activate 3-D secure' mod='hipay_enterprise'}
                </label>
                <div class="col-lg-9">
                    <select name="activate_3d_secure" class="col-lg-6" id="activate_3d_secure">
                        <option value="0"
                                {if $config_hipay.payment.global.activate_3d_secure == "0"}selected="selected" {/if} >{l s='Disabled' mod='hipay_enterprise'}</option>
                        <option value="1"
                                {if $config_hipay.payment.global.activate_3d_secure == "1"}selected="selected" {/if} >{l s='Try to enable for all transactions' mod='hipay_enterprise'}</option>
                        <option value="2"
                                {if $config_hipay.payment.global.activate_3d_secure == "2"}selected="selected" {/if} >{l s='Try to enable for configured 3ds rules' mod='hipay_enterprise'}</option>
                        <option value="3"
                                {if $config_hipay.payment.global.activate_3d_secure == "3"}selected="selected" {/if} >{l s='Force for configured 3ds rules' mod='hipay_enterprise'}</option>
                        <option value="4"
                                {if $config_hipay.payment.global.activate_3d_secure == "4"}selected="selected" {/if} >{l s='Force for all transactions' mod='hipay_enterprise'}</option>
                    </select>
                </div>
            </div>
            <div class="panel col-lg-offset-3" id="3ds-rules">
                <h5><i class="icon icon-credit-card"></i> {l s='3-D secure rules' mod='hipay_enterprise'}</h5>
                <hr/>
                <div class="form-group">
                    <div class="col-lg-3">
                        <input type="text" readonly name="3d_secure_rules[total_price][field]" value="total_price">
                    </div>
                    <div class="col-lg-2">
                        <select name="3d_secure_rules[total_price][operator]" id="3d_secure_rules">
                            <option value=">"
                                    {if $config_hipay.payment.global.3d_secure_rules[0].operator|@html_entity_decode == ">"}selected="selected" {/if} >{l s='Greater than' mod='hipay_enterprise'}</option>
                            <option value=">="
                                    {if $config_hipay.payment.global.3d_secure_rules[0].operator|@html_entity_decode == ">="}selected="selected" {/if} >{l s='Greater than or equals to' mod='hipay_enterprise'}</option>
                            <option value="<"
                                    {if $config_hipay.payment.global.3d_secure_rules[0].operator|@html_entity_decode == "<"}selected="selected" {/if} >{l s='Lower than' mod='hipay_enterprise'}</option>
                            <option value="<="
                                    {if $config_hipay.payment.global.3d_secure_rules[0].operator|@html_entity_decode == "<="}selected="selected" {/if} >{l s='Lower than or equals to' mod='hipay_enterprise'}</option>
                            <option value="=="
                                    {if $config_hipay.payment.global.3d_secure_rules[0].operator|@html_entity_decode == "=="}selected="selected" {/if} >{l s='Equals to' mod='hipay_enterprise'}</option>
                            <option value="!="
                                    {if $config_hipay.payment.global.3d_secure_rules[0].operator|@html_entity_decode == "!="}selected="selected" {/if} >{l s='Not equals to' mod='hipay_enterprise'}</option>
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <input type="text" name="3d_secure_rules[total_price][value]"
                               value="{$config_hipay.payment.global.3d_secure_rules[0].value}">
                    </div>
                </div>
            </div>


        </div>
        <div class="panel-footer">
            <button type="submit" class="btn btn-default pull-left" name="submitCancel"><i
                    class="process-icon-eraser"></i>{l s='Discard changes' mod='hipay_enterprise'}
            </button>
            <button type="submit" class="btn btn-default btn btn-default pull-right" name="submitGlobalPaymentMethods"><i
                    class="process-icon-save"></i> {l s='Save configuration changes' mod='hipay_enterprise'}</button>
        </div>
    </form>
</div>

<script>
    $(document).ready(function () {
        if ($("#operating_mode").val() == "api") {
            $("#hostedconf").hide();
        }
        $("#operating_mode").change(function () {
            if ($("#operating_mode").val() == "api") {
                $("#hostedconf").hide();
            } else {
                $("#hostedconf").show();
            }
        });
        
        if ($("#activate_3d_secure").val() == "0") {
            $("#3ds-rules").hide();
        }
        $("#activate_3d_secure").change(function () {
            if ($("#activate_3d_secure").val() == "0") {
                $("#3ds-rules").hide();
            } else {
                $("#3ds-rules").show();
            }
        });
    });

</script>