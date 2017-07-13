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

<div class="panel-heading">
    <i class="icon-credit-card"></i>
    {l s='Module informations' mod='hipay_enterprise'}
</div>
<p>{l s='HiPay module version' mod='hipay_enterprise'} : {$source['integration_version']}</p>
<p>{l s='Prestashop version' mod='hipay_enterprise'} : {$source['brand_version']}</p>
<p>{l s='IP Address' mod='hipay_enterprise'} : {$ipaddr}</p>
<p>{l s='Callback url' mod='hipay_enterprise'} : {$this_callback}</p>
<br/>
<p>
    {l s='To help you in your plateform and module configuration, you cand find the module documentation on our developper portal at the following adress : ' mod='hipay_enterprise'}
    <br/>
    <a href='https://developer.hipay.com/doc/hipay-enterprise-sdk-prestashop/'>https://developer.hipay.com/doc/hipay-enterprise-sdk-prestashop/</a>
</p>
<p>
    {l s='Our support team is available for any questions at the following adress : '}
    <br/>
    <a href="mailto:support.tpp@hipay.com">support.tpp@hipay.com</a>
</p>