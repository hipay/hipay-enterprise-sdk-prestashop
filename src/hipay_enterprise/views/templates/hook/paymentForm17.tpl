<form action="{$action}" enctype="application/x-www-form-urlencoded" class="form-horizontal hipay-form-17" method="post" name="tokenizerForm" id="tokenizerForm" autocomplete="off">
    <p class="error" ></p>
    {include file="$hipay_enterprise_tpl_dir/paymentForm.tpl"}
</form>
<script>

    {if $confHipay.account.global.sandbox_mode}
        var api_tokenjs_mode = 'stage';
        var api_tokenjs_username = '{$confHipay.account.sandbox.api_tokenjs_username_sandbox}';
        var api_tokenjs_password_publickey = '{$confHipay.account.sandbox.api_tokenjs_password_publickey_sandbox}';
    {else}
        var api_tokenjs_mode = 'production';
        var api_tokenjs_username = '{$confHipay.account.production.api_tokenjs_username_production}';
        var api_tokenjs_password_publickey = '{$confHipay.account.production.api_tokenjs_password_publickey_production}';
    {/if}
</script>