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
<div class="product-row row" id="hipay">
    <div class="col-md-12 left-column">
        <div class="card mt-2 d-print-none">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="material-icons mi-payment">payment</i>
                    {l s='Hipay actions' mod='hipay_enterprise'}
                </div>
            </div>
            <div class="card-body">
                {if $HiPay_errorHipay }
                    <p class="alert alert-danger">{$HiPay_errorHipay}</p>
                {/if}
                {if $HiPay_messagesHipay }
                    <p class="alert alert-success">{$HiPay_messagesHipay}</p>
                {/if}
                {if $HiPay_showMoto}
                    <div class="col-lg-6">
                        {include file='../admin/actions/moto.partial.tpl'}
                    </div>
                {/if}
                {if $HiPay_showChallenge}
                    <div class="col-lg-12 panel">
                        {include file='../admin/actions/panel-challenge.tpl'}
                    </div>
                {/if}
                {if $HiPay_showCapture && $HiPay_stillToCapture > 0 && $HiPay_manualCapture}
                    <div class="col-lg-12 panel">
                        {include file='../admin/actions/capture.partial.tpl'}
                    </div>
                {/if}
                {if $HiPay_showRefund && $HiPay_alreadyCaptured && $HiPay_refundableAmount > 0}
                    <div class="col-lg-12 panel">
                        {include file='../admin/actions/refund.partial.tpl'}
                    </div>
                {/if}
                {if  !$HiPay_showMoto && !$HiPay_showChallenge && !$HiPay_showCapture && !$HiPay_showRefund}
                    <p class="alert alert-warning">{l s='No actions available' mod='hipay_enterprise'}</p>
                    {if $HiPay_refundRequestedOS }
                        <p class="alert alert-warning">{l s='A refund has been requested, actions are disabled during validation process.' mod='hipay_enterprise'}</p>
                    {/if}
                    {if $HiPay_refundStartedFromBo }
                        <p class="alert alert-warning">{l s='A refund or a capture has been validated from HiPay back-office, you must proceed refund from HiPay back-office.' mod='hipay_enterprise'}</p>
                    {/if}
                {/if}
            </div>
        </div>
    </div>

    <script>
      $("#hipay_refund_type").change(function () {
        if ($(this).val() == "complete") {
          $("#block-refund-amount").hide();
        } else {
          $("#block-refund-amount").show();
        }
      });

      $("#hipay_capture_type").change(function () {
        if ($(this).val() == "complete") {
          $("#block-capture-amount").hide();
        } else {
          $("#block-capture-amount").show();
        }
      });

    </script>
</div>