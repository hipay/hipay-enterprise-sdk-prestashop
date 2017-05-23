<form action="{$action}" enctype="application/x-www-form-urlencoded" class="form-horizontal" method="post" name="tokenizerForm" id="tokenizerForm" autocomplete="off">
    {include file="$hipay_enterprise_tpl_dir/paymentForm.tpl"}
</form>
<script>
    var api_tokenjs_username_sandbox = '{$confHipay.account.sandbox.api_tokenjs_username_sandbox}';
    var api_tokenjs_password_publickey_sandbox = '{$confHipay.account.sandbox.api_tokenjs_password_publickey_sandbox}';
</script>