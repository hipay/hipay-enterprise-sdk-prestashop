{**
  * 2016 HiPay
  *
  * NOTICE OF LICENSE
  *
  *
  * @author    HiPay <support.wallet@hipay.com>
  * @copyright 2016 HiPay
  * @license   https://github.com/hipay/hipay-wallet-sdk-prestashop/blob/master/LICENSE.md
  *
  *}

  <div class="panel">
    <!-- MARKETING START -->
    {* include file marketing.tpl *}
    {include file='./marketing.tpl'}
    <!-- MARKETING END -->
  </div>
  <!-- ALERTS START -->
  {* include file alerts.tpl *}
  {*include file='./alerts.tpl'*}
  <!-- ALERTS END -->
  <div role="tabpanel">
    <ul class="nav nav-tabs" role="tablist">
      <li role="presentation"
      class=" {if ((isset($active_tab) == false) || ($active_tab == 'settings_form'))} active{/if}"><a
      href="#account_form" aria-controls="settings_form" role="tab" data-toggle="tab">
        <span class="icon icon-cogs"></span> {l s='Account informations' mod='hipay_professional'}</a>
      </li>
      <li role="presentation"
      class=" {if ((isset($active_tab) == true) && ($active_tab == 'button_form'))} active{/if}"><a
      href="#button_form" aria-controls="button_form" role="tab" data-toggle="tab">
        <span class="icon icon-money"></span> {l s='Payment methods' mod='hipay_professional'}</a>
      </li>
      <li role="presentation"><a href="#fraud" aria-controls="fraud" role="tab" data-toggle="tab">
        <span class="icon icon-money"></span> {l s='Fraud' mod='hipay_professional'}</a>
      </li>
      <li role="presentation"><a href="#category_mapping" aria-controls="category_mapping" role="tab" data-toggle="tab">
        <span class="icon icon-money"></span> {l s='Category Mapping' mod='hipay_professional'}</a>
      </li>
      <li role="presentation"><a href="#carrier_mapping" aria-controls="carrier_mapping" role="tab" data-toggle="tab">
        <span class="icon icon-money"></span> {l s='Carrier Mapping' mod='hipay_professional'}</a>
      </li>
      <li role="presentation"><a href="#faq" aria-controls="faq" role="tab" data-toggle="tab">
        <span class="icon icon-money"></span> {l s='FAQ' mod='hipay_professional'}</a>
      </li>
      <li role="presentation"><a href="#logs" aria-controls="logs" role="tab" data-toggle="tab">
        <span class="icon icon-money"></span> {l s='Logs' mod='hipay_professional'}</a>
      </li>
</ul>

<div class="tab-content">
  <div role="tabpanel"
  class="tab-pane  {if ((isset($active_tab) == false) || ($active_tab == 'settings_form'))} active{/if}"
  id="settings_form">
  {*include file='./settings.tpl'*}
</div>
<div role="tabpanel"
class="tab-pane  {if ((isset($active_tab) == true) && ($active_tab == 'button_form'))} active{/if}"
id="button_form">
{*include file='./payment-button.tpl'*}
</div>
<div role="tabpanel" class="tab-pane" id="fraud">
  {include file='./fraud.tpl'}
</div>
<div role="tabpanel" class="tab-pane" id="faq">
  {include file='./faq.tpl'}
</div>
<div role="tabpanel" class="tab-pane" id="logs">
  {*include file='./logs.tpl'*}
</div>
</div>
</div>
