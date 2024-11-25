<script>
  const createHiPayPaymentHandler = function(config) {

    const initialized = {
      alma: false,
      paypal: false
    };
    let paymentProducts = null;

    const getCredentials = () => {
      const { global, sandbox, production } = config;
      const isSandbox = global.sandbox_mode;

      return {
        username: isSandbox ? sandbox.api_username_sandbox : production.api_username_production,
        password: isSandbox ? sandbox.api_password_sandbox : production.api_password_production,
        isSandbox
      };
    };

    const initializePaymentProducts = (products, options = {}) => {
      const credentials = getCredentials();
      paymentProducts = availablePaymentProducts();

      paymentProducts.setCredentials(
        credentials.username,
        credentials.password,
        credentials.isSandbox
      );

      paymentProducts.updateConfig('payment_product', products);
      paymentProducts.updateConfig('with_options', true);

      // Set additional options if provided
      Object.entries(options).forEach(([key, value]) => {
        paymentProducts.updateConfig(key, value);
      });
    };

    const togglePayPalFields = (PayPalMerchantData) => {
      const options = PayPalMerchantData.options;
      const fields = ['buttonColor', 'buttonShape', 'buttonLabel', 'buttonHeight', 'bnpl'];

      if (options?.provider_architecture_version === 'v1' && options?.payer_id.length > 0) {
        fields.forEach(fieldId => {
          document.getElementById(fieldId).classList.remove('readonly');
        });
        $('#paypal_v2_support').hide();
      } else {
        fields.forEach(fieldId => {
          document.getElementById(fieldId).classList.add('readonly');
        });
        $('#paypal_v2_support').show();
      }
    };

    //Initialize payment products (Alma, PayPal)
    return {
      initializeAlma: function() {
        if (initialized.alma) return;

        initializePaymentProducts(['alma-3x', 'alma-4x'], {
          currency: ['EUR']
        });

        $('.alma-container').each(function() {
          $(this).prepend('<div class="loader"></div>');
          $(this).find('h4').hide();
        });

        // Fetch and process products
        return paymentProducts.getAvailableProducts()
          .then(result => {
            initialized.alma = true;
            result.forEach(product => {
              if (product.code === 'alma-3x') {
                const basketMax3x = product.options?.basketAmountMax3x;
                const basketMin3x = product.options?.basketAmountMin3x;
                $('#alma-3x_minAmount span').html(basketMin3x + ' &euro;');
                $('#alma-3x_maxAmount span').html(basketMax3x + ' &euro;');
              } else if (product.code === 'alma-4x') {
                const basketMax4x = product.options?.basketAmountMax4x;
                const basketMin4x = product.options?.basketAmountMin4x;
                $('#alma-4x_minAmount span').html(basketMin4x + ' &euro;');
                $('#alma-4x_maxAmount span').html(basketMax4x + ' &euro;');
              }
            });

            $('.alma-container').each(function() {
              $(this).find('.loader').remove();
              $(this).find('h4').show();
            });
          })
          .catch(error => {
            console.error('Error fetching Alma products:', error);
            $('.alma-container').each(function() {
              $(this).find('.loader').remove();
              $(this).find('h4').html('Error loading data').show();
            });
          });
      },

      initializePayPal: function() {
        if (initialized.paypal) return;

        initializePaymentProducts(['paypal']);

        return paymentProducts.getAvailableProducts()
          .then(data => {
            if (data?.length > 0) {
              initialized.paypal = true;
              togglePayPalFields(data[0]);
            }
          })
          .catch(error => console.error('Error fetching PayPal products:', error));
      }
    };
  };

  $(document).ready(function() {
    const hipayHandler = createHiPayPaymentHandler({$HiPay_config_hipay.account|json_encode nofilter});

    // Handle Alma initialization
    if ($('#payment_form__alma').hasClass('active')) {
      hipayHandler.initializeAlma();
    }
    $('a[href="#payment_form__alma"]').on('shown.bs.tab', function(e) {
      hipayHandler.initializeAlma();
    });

    // Handle PayPal initialization
    if ($('#payment_form__paypal').hasClass('active')) {
      hipayHandler.initializePayPal();
    }
    $('a[href="#payment_form__paypal"]').on('shown.bs.tab', function(e) {
      hipayHandler.initializePayPal();
    });
  });
</script>