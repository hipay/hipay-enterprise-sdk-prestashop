<div id="oney-versions-3xcb"></div>

<script>
  // Set global variables for OneyCommon to use
  window.HiPayCartTotalAmount = {$HiPay_cart.totalAmount};
  {if $HiPay_confHipay.account.global.sandbox_mode}
    window.HiPaySandboxMode = true;
    window.HiPaySandboxUsername = "{$HiPay_confHipay.account.sandbox.api_tokenjs_username_sandbox}";
    window.HiPaySandboxPasswordPublickey = "{$HiPay_confHipay.account.sandbox.api_tokenjs_password_publickey_sandbox}";
  {else}
    window.HiPaySandboxMode = false;
    window.HiPayProductionUsername = "{$HiPay_confHipay.account.production.api_tokenjs_username_production}";
    window.HiPayProductionPasswordPublickey = "{$HiPay_confHipay.account.production.api_tokenjs_password_publickey_production}";
  {/if}

  // Initialize on page load
  document.addEventListener('DOMContentLoaded', function() {
    // Setup event listeners for 3xcb
    OneyCommon.setupPaymentMethodChangeListener('3xcb');
    OneyCommon.setupPrestaShopListeners('3xcb');
  });
</script>
