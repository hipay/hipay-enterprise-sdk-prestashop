{**
* 2017 HiPay
*
* NOTICE OF LICENSE
*
*
* @author    HiPay <support.wallet@hipay.com>
* @copyright 2017 HiPay
* @license   https://github.com/hipay/hipay-wallet-sdk-prestashop/blob/master/LICENSE.md
*
*}
<form action="{$action}" enctype="application/x-www-form-urlencoded" class="form-horizontal hipay-form-17" method="post" name="local"
      id="local" autocomplete="off">
    {assign "psVersion" "17"}
    {include file="$hipay_enterprise_tpl_dir/hook/paymentLocalForm.tpl"}
    <input class="ioBB" type="hidden" name="ioBB">
</form>