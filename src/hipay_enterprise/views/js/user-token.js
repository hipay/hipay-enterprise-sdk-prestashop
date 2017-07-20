/**
 * 2017 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.wallet@hipay.com>
 * @copyright 2017 HiPay
 * @license   https://github.com/hipay/hipay-wallet-sdk-prestashop/blob/master/LICENSE.md
 */


$(document).ready(function () {
    $(".delTokenForm").submit(function (e) {
        e.preventDefault();
        e.stopPropagation();
        if (confirm("Are you sure ?")) {
            this.submit();
        }
    });
});