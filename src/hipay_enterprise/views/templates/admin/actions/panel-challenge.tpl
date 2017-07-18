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

<fieldset>
    <legend>{l s='Manage challenge' }</legend>
    <p class="alert alert-danger">The payment was challenged by your fraud ruleset and is pending.
        Please review the fraud screening result and choose whether you want to accept or deny the payment transaction.</p>
    <form action="{$challengeLink}" method="post" id="hipay_challenge_form" class="form-horizontal">
        <input type="hidden" name="id_order" value="{$orderId}"/>
        <div class="form-group">
            <button type="submit" name="btn-challenge" id="challenge_accept" value="accept"
                    class="btn btn-success btn-accept col-lg-5">{l s='Accept payment'}</button>
            <button type="submit" name="btn-challenge" id="challenge_refuse" value="deny"
                    class="btn btn-danger col-lg-5 pull-right">{l s='Deny payment'}</button>
        </div>
    </form>
</fieldset>

<script>
    $(document).ready(function () {
        $("#hipay_challenge_form").submit(function() {
            msgConfirmation = '{l s='Are-you sure to $action for this order ?'}';
            if (!confirm(msgConfirmation.replace('$action',$('#' + document.activeElement.id).html()))) {
                return false;
            }
        });
    });
</script>
