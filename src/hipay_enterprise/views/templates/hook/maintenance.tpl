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
<div class="row">
    <div class="col-lg-12">
        <div class="panel">
            <div class="panel-heading">
                <i class="icon-credit-card"></i>
                {l s='Hipay actions' mod='hipay_enterprise'}
            </div>
            {if $errorHipay }
                <p class="alert alert-danger">{$errorHipay}</p>
            {/if}
            {if $messagesHipay }
                <p class="alert alert-success">{$messagesHipay}</p>
            {/if}
            <div class="well hidden-print row">
                {if $showMoto}
                    <div class="col-lg-6">
                        {include file='../admin/actions/moto.partial.tpl'}
                    </div>
                {/if}
                {if $showChallenge}
                    <div class="col-lg-12 panel">
                        {include file='../admin/actions/panel-challenge.tpl'}
                    </div>
                {/if}
                {if $showCapture && $stillToCapture > 0 && $manualCapture}

                    <div class="col-lg-12 panel">
                        {include file='../admin/actions/capture.partial.tpl'}
                    </div>
                {/if}
                {if $showRefund && $alreadyCaptured && $refundableAmount > 0}
                    <div class="col-lg-12 panel">
                        {include file='../admin/actions/refund.partial.tpl'}
                    </div>
                {/if}
                {if  !$showMoto && !$showChallenge && !$showCapture && !$showRefund }
                    <p class="alert alert-warning">{l s='No actions available' mod='hipay_enterprise'}</p>
                    {if $refundRequestedOS }
                        <p class="alert alert-warning">{l s='A refund has been requested, actions are disabled during validation process.' mod='hipay_enterprise'}</p>
                    {/if}
                    {if $refundStartedFromBo }
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