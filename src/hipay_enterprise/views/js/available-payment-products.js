const availablePaymentProducts = () => {
  const createConfig = () => ({
    operation: ['4'],
    payment_product: [],
    eci: ['7'],
    with_options: false,
    customer_country: [],
    currency: [],
    payment_product_category: [],
    apiUsername: '',
    apiPassword: '',
    baseUrl: '',
    authorizationHeader: ''
  });

  const generateAuthorizationHeader = (username, password) => {
    const credentials = `${username}:${password}`;
    const encodedCredentials = btoa(credentials);
    return `Basic ${encodedCredentials}`;
  };

  const toQueryString = (config) => {
    const params = {
      operation: config.operation,
      payment_product: config.payment_product,
      eci: config.eci,
      with_options: config.with_options ? 'true' : 'false',
      customer_country: config.customer_country,
      currency: config.currency,
      payment_product_category: config.payment_product_category
    };

    const filteredParams = Object.entries(params).reduce((acc, [key, value]) => {
      if (Array.isArray(value) && value.length > 0) {
        acc[key] = value.join(',');
      } else if (value !== '' && value !== false && value.length !== 0) {
        acc[key] = value;
      }
      return acc;
    }, {});

    return new URLSearchParams(filteredParams).toString();
  };

  let config = createConfig();

  return {
    setCredentials: (username, password, isSandbox = false) => {
      config.apiUsername = username;
      config.apiPassword = password;
      config.baseUrl = isSandbox
        ? 'https://stage-secure-gateway.hipay-tpp.com/rest/v2/'
        : 'https://secure-gateway.hipay-tpp.com/rest/v2/';
      config.authorizationHeader = generateAuthorizationHeader(username, password);
    },

    updateConfig: (key, value) => {
      config[key] = value;
    },

    getAvailableProducts: async () => {
      if (!config.baseUrl || !config.authorizationHeader) {
        throw new Error('Credentials must be set before calling getAvailableProducts');
      }

      const url = new URL(`${config.baseUrl}available-payment-products.json`);
      url.search = toQueryString(config);

      try {
        const response = await fetch(url, {
          method: 'GET',
          headers: {
            'Authorization': config.authorizationHeader,
            'Accept': 'application/json'
          }
        });

        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }

        return await response.json();
      } catch (error) {
        console.error('There was a problem with the fetch operation:', error);
        throw error;
      }
    }
  };
};