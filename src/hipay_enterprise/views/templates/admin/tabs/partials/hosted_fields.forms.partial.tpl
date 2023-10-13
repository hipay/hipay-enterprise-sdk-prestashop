<div id="hostedfieldsconf">
    <label class="control-label col-lg-3">
        {l s='Hosted Fields style' mod='hipay_enterprise'}
    </label>
    <div class="panel col-lg-offset-3" id="hostedfields-setings">
        <h5><i class="icon icon-file-text"></i> {l s='Hosted Fields style settings' mod='hipay_enterprise'}</h5>
        <hr/>
        <div class="form-group">
            <div class="col-lg-2">
                <label>{l s='Color' mod='hipay_enterprise'}</label>
                <input type="text" name="hosted_fields_style[base][color]" value="{$HiPay_config_hipay.payment.global.hosted_fields_style.base.color}">
            </div>
            <div class="col-lg-2">
                <label>{l s='Font family' mod='hipay_enterprise'}</label>
                <input type="text" name="hosted_fields_style[base][fontFamily]" value="{$HiPay_config_hipay.payment.global.hosted_fields_style.base.fontFamily}">
            </div>
            <div class="col-lg-2">
                <label>{l s='Font size' mod='hipay_enterprise'}</label>
                <input type="text" name="hosted_fields_style[base][fontSize]" value="{$HiPay_config_hipay.payment.global.hosted_fields_style.base.fontSize}">
            </div>
            <div class="col-lg-2">
                <label>{l s='Font weight' mod='hipay_enterprise'}</label>
                <input type="text" name="hosted_fields_style[base][fontWeight]" value="{$HiPay_config_hipay.payment.global.hosted_fields_style.base.fontWeight}">
            </div>
            <div class="col-lg-2">
                <label>{l s='Placeholder color' mod='hipay_enterprise'}</label>
                <input type="text" name="hosted_fields_style[base][placeholderColor]" value="{$HiPay_config_hipay.payment.global.hosted_fields_style.base.placeholderColor}">
            </div>
            <div class="col-lg-2">
                <label>{l s='Caret color' mod='hipay_enterprise'}</label>
                <input type="text" name="hosted_fields_style[base][caretColor]" value="{$HiPay_config_hipay.payment.global.hosted_fields_style.base.caretColor}">
            </div>
            <div class="col-lg-2">
                <label>{l s='Icon color' mod='hipay_enterprise'}</label>
                <input type="text" name="hosted_fields_style[base][iconColor]" value="{$HiPay_config_hipay.payment.global.hosted_fields_style.base.iconColor}">
            </div>
        </div>
    </div>
</div>
