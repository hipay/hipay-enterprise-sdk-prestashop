<form action="{$action}" enctype="application/x-www-form-urlencoded" class="form-horizontal" method="post" name="local"
      id="local" autocomplete="off">
    {include file="$hipay_enterprise_tpl_dir/hook/paymentLocalForm.tpl"}
    <input class="ioBB" type="hidden" name="ioBB">
</form>