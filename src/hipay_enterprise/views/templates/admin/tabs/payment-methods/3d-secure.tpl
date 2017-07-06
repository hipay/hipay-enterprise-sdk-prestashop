<h3><i class="icon icon-credit-card"></i> {l s='3-D secure configurations' mod='hipay_professional'}</h3>   

<div role="tabpanel">
    <form method="post" class="defaultForm form-horizontal" action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" id="credit_card_form">
        <div class="panel" id="fieldset_0">
            <div class="form-wrapper">
                <div class="form-group">
                    <label class="control-label col-lg-3">
                        Activate 3-D secure
                    </label>
                    <div class="col-lg-9">
                        <select name="activate_3d_secure" class="col-lg-6" id="activate_3d_secure">
                            <option value="0" {if $config_hipay.payment.global.activate_3d_secure == "0"}selected="selected" {/if} >{l s="Disabled" mod="hipay_enterprise"}</option>
                            <option value="1" {if $config_hipay.payment.global.activate_3d_secure == "1"}selected="selected" {/if} >{l s="Try to enable for all transactions" mod="hipay_enterprise"}</option>
                            <option value="2" {if $config_hipay.payment.global.activate_3d_secure == "2"}selected="selected" {/if} >{l s="Try to enable for configured 3ds rules" mod="hipay_enterprise"}</option>
                            <option value="3" {if $config_hipay.payment.global.activate_3d_secure == "3"}selected="selected" {/if} >{l s="Force for configured 3ds rules" mod="hipay_enterprise"}</option>
                            <option value="4" {if $config_hipay.payment.global.activate_3d_secure == "4"}selected="selected" {/if} >{l s="Force for all transactions" mod="hipay_enterprise"}</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-3">
                        3-D secure rules
                    </label>
                    <div class="col-lg-3">
                        <input type="text" readonly name="3d_secure_rules[total_price][field]" value="total_price">
                    </div>
                    <div class="col-lg-2">
                        <select name="3d_secure_rules[total_price][operator]"  id="3d_secure_rules">
                            <option value=">" {if $config_hipay.payment.global.3d_secure_rules[0].operator|@html_entity_decode == ">"}selected="selected" {/if} >{l s="Greater than" mod="hipay_enteprise"}</option>
                            <option value=">=" {if $config_hipay.payment.global.3d_secure_rules[0].operator|@html_entity_decode == ">="}selected="selected" {/if} >{l s="Greater than or equals to" mod="hipay_enteprise"}</option>
                            <option value="<" {if $config_hipay.payment.global.3d_secure_rules[0].operator|@html_entity_decode == "<"}selected="selected" {/if} >{l s="Lower than" mod="hipay_enteprise"}</option>
                            <option value="<=" {if $config_hipay.payment.global.3d_secure_rules[0].operator|@html_entity_decode == "<="}selected="selected" {/if} >{l s="Lower than or equals to" mod="hipay_enteprise"}</option>
                            <option value="==" {if $config_hipay.payment.global.3d_secure_rules[0].operator|@html_entity_decode == "=="}selected="selected" {/if} >{l s="Equals to" mod="hipay_enteprise"}</option>
                            <option value="!=" {if $config_hipay.payment.global.3d_secure_rules[0].operator|@html_entity_decode == "!="}selected="selected" {/if} >{l s="Not equals to" mod="hipay_enteprise"}</option>
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <input type="text"  name="3d_secure_rules[total_price][value]" value="{$config_hipay.payment.global.3d_secure_rules[0].value}">
                    </div>

                </div>
            </div>
            <div class="panel-footer">
                <button type="submit" class="btn btn-default btn btn-default pull-right" name="submit3DSecure"><i class="process-icon-save"></i> Save</button>
            </div>
        </div><!-- /.form-wrapper -->
    </form>
</div>


<script>
</script>