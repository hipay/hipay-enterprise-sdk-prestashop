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
<div class="panel">
    <script type="text/javascript" src="{$module_dir}views/js/bootstrap-multiselect.js"></script>
    <script type="text/javascript" src="{$module_dir}views/js/jquery.bootstrap-duallistbox.min.js"></script>

    {include file='./payment-methods/global.tpl'}
    {include file='./payment-methods/creditcard.tpl'}
    {include file='./payment-methods/local.tpl'}

</div>
<script>
    $(document).ready(function() {
        $(".money-type").change(function validate() {
            var inputValue = hiPayInputControl.HiPay_normalizePrice($(this).val());
            var parsedValue = truncateDecimals(inputValue, 6);
            $(this).val(parsedValue);
        });
    });

    $('.multiselect-currency').multiselect();

    $('#collapseCC').on('shown.bs.collapse', function() {
        $("#chevronCC").addClass('icon-chevron-up').removeClass('icon-chevron-down');
    });

    $('#collapseCC').on('hidden.bs.collapse', function() {
        $("#chevronCC").addClass('icon-chevron-down').removeClass('icon-chevron-up');
    });

    $('#collapseLocalPayment').on('shown.bs.collapse', function() {
        $("#chevronLocal").addClass('icon-chevron-up').removeClass('icon-chevron-down');
    });

    $('#collapseLocalPayment').on('hidden.bs.collapse', function() {
        $("#chevronLocal").addClass('icon-chevron-down').removeClass('icon-chevron-up');
    });

    $('#collapse3ds').on('shown.bs.collapse', function() {
        $("#chevron3ds").addClass('icon-chevron-up').removeClass('icon-chevron-down');
    });

    $('#collapse3ds').on('hidden.bs.collapse', function() {
        $("#chevron3ds").addClass('icon-chevron-down').removeClass('icon-chevron-up');
    });
</script>