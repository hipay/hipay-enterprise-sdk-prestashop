/**
 * HiPay Enterprise SDK Prestashop
 *
 * 2017 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright 2017 HiPay
 * @license   https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 */

jQuery(($) => {
  // Preserve hash in URL with history
  $('a[href^="#"]').on('click', function (e) {
    if (e.originalEvent && window.location.href !== this.href) {
      history.pushState({}, document.title, this.href);
    }
  });

  $('form[action]').on('submit', function () {
    var _this = $(this);
    _this.attr('action', _this.attr('action') + window.location.hash);
    return true;
  });

  // If hash, click on clickable elements parsing hash value
  if (window.location.hash) {
    var hash = window.location.hash;
    var previousPart = '';

    for (var el of hash.split('__')) {
      var clEl = $('a[href="' + previousPart + el + '"]');

      if (clEl.is(':visible')) {
        if (clEl.data('toggle') === 'collapse') {
          if (
            clEl.attr('aria-expanded') == 'false' ||
            clEl.hasClass('collapsed')
          ) {
            clEl.click();
          }
        } else {
          clEl.click();
        }
      }

      previousPart += el + '__';
    }
  }
});
