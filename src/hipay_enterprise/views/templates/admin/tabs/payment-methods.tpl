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
<div class="panel">
    <script type="text/javascript" src="{$module_dir}views/js/jquery.bootstrap-duallistbox.min.js"></script>
    <h3><i class="icon icon-credit-card"></i> {l s='Global settings' mod='hipay_enterprise'}</h3>

    {$global_payment_methods_form}

    {include file='./payment-methods/3d-secure.tpl'}
    {include file='./payment-methods/creditcard.tpl'}
    {include file='./payment-methods/local.tpl'}

</div>
    
