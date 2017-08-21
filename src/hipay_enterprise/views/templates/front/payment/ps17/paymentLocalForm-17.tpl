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
<form id="{$localPaymentName}-hipay" action="{$action}" enctype="application/x-www-form-urlencoded"
      class="form-horizontal hipay-form-17" method="post" name="local"
      autocomplete="off">
    {assign "psVersion" "17"}
    {include file="$hipay_enterprise_tpl_dir/hook/paymentLocalForm.tpl"}
    <input class="ioBB" type="hidden" name="ioBB">
</form>
{include file="$hipay_enterprise_tpl_dir/front/partial/js.strings.tpl"}
<script>
    document.addEventListener('DOMContentLoaded', formListener{$localPaymentName|regex_replace:'/[^a-zA-Z0-9]/':""}, false);

    function formListener{$localPaymentName|regex_replace:'/[^a-zA-Z0-9]/':""}() {
        $("#{$localPaymentName}-hipay").submit(function (e) {
            // prevent form from being submitted 
            e.preventDefault();
            e.stopPropagation();

            if (hiPayInputControl.checkControl('{$localPaymentName}')) {
                this.submit();
            }

        });
    }
</script>