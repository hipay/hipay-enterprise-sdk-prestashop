/**
 * Oney Common JavaScript - Shared functionality for all Oney payment methods
 */

window.OneyCommon = {
  // Shared variables
  methodsInstance: {},
  isInitializing: false,
  setupTimeout: null,
  
  /**
   * Initialize Oney for a specific payment method
   */
  init: function(methodName) {
    // Prevent multiple simultaneous initializations
    if (this.isInitializing) {
      return;
    }
    
    this.isInitializing = true;
    
    const parameters = {
      totalAmount: Number(window.HiPayCartTotalAmount || 0)
    };

    this.createInstance(methodName, parameters);
  },
  
  /**
   * Create Oney instance for a specific method
   */
  createInstance: function(methodName, parameters) {
    // Destroy ALL existing Oney widgets before creating new one
    this.destroyAllOneyWidgets();
    
    // Check if container exists and is properly set up
    const container = document.getElementById('oney-versions-' + methodName);
    if (!container) {
      console.error('Container not found for:', methodName);
      this.isInitializing = false;
      return;
    }
    
    // Ensure container is completely empty and ready
    try {
      // Remove all child nodes
      while (container.firstChild) {
        container.removeChild(container.firstChild);
      }
      // Also clear innerHTML as backup
      container.innerHTML = '';
    } catch (e) {
      console.error('Error clearing container for:', methodName, e.message);
      this.isInitializing = false;
      return;
    }

    // Get API credentials from global variables
    let apiTokenjsMode, apiTokenjsUsername, apiTokenjsPasswordPublickey;
    
    if (window.HiPaySandboxMode) {
      apiTokenjsMode = "stage";
      apiTokenjsUsername = window.HiPaySandboxUsername;
      apiTokenjsPasswordPublickey = window.HiPaySandboxPasswordPublickey;
    } else {
      apiTokenjsMode = "production";
      apiTokenjsUsername = window.HiPayProductionUsername;
      apiTokenjsPasswordPublickey = window.HiPayProductionPasswordPublickey;
    }
    
    const oneyInstance = new HiPay({
      environment: apiTokenjsMode,
      username: apiTokenjsUsername,
      password: apiTokenjsPasswordPublickey
    });

    const config = {
      template: 'auto',
      selector: 'oney-versions-' + methodName,
      request: { 
        amount: parameters.totalAmount.toString()
      }
    };

    // Add a longer delay to ensure DOM is ready and container is completely empty
    setTimeout(() => {
      try {
        // Double-check that container is empty before creating widget
        const container = document.getElementById('oney-versions-' + methodName);
        if (container) {
          // Force clear the container one more time
          container.innerHTML = '';
          while (container.firstChild) {
            container.removeChild(container.firstChild);
          }
          
          if (container.children.length === 0) {
            this.methodsInstance['oney-' + methodName] = this.createWidget(oneyInstance, methodName, config);
          } else {
            console.error('Container is not empty, cannot create widget for:', methodName);
          }
        }
        
        // Reset the initialization flag
        this.isInitializing = false;
      } catch (e) {
        console.error('Error creating widget for:', methodName, e.message);
        // Reset the initialization flag on error too
        this.isInitializing = false;
      }
    }, 500);
  },
  
  /**
   * Create Oney widget
   */
  createWidget: function(oneyInstance, methodName, config) {
    // Validate inputs
    if (!oneyInstance || !methodName || !config) {
      console.error('Invalid parameters for createWidget');
      return null;
    }
    
    // Check if container still exists
    const container = document.getElementById(config.selector);
    if (!container) {
      console.error('Container not found during widget creation for:', methodName);
      return null;
    }
    
    try {
      const hfInstance = oneyInstance.create(methodName, config);
      
      if (hfInstance && typeof hfInstance.on === 'function') {
        hfInstance.on('ready', function () {
          // Widget is ready
        });

        hfInstance.on('error', function (error) {
          console.error('Widget error for:', methodName, error);
        });
      }

      return hfInstance;
    } catch (error) {
      console.error('Error creating widget for:', methodName, error);
      return null;
    }
  },
  
  /**
   * Destroy ALL Oney widgets and clear ALL containers
   */
  destroyAllOneyWidgets: function() {
    // Destroy all Oney instances
    Object.keys(this.methodsInstance).forEach((key) => {
      if (key.startsWith('oney-')) {
        try {
          if (this.methodsInstance[key] && typeof this.methodsInstance[key].destroy === 'function') {
            this.methodsInstance[key].destroy();
            delete this.methodsInstance[key];
          }
        } catch (e) {
          console.error('Error destroying widget:', key, e.message);
        }
      }
    });
    
    // Clear all Oney containers and any HiPay-related elements
    const containers = document.querySelectorAll('[id^="oney-versions-"]');
    containers.forEach((container) => {
      try {
        // Remove all child nodes
        while (container.firstChild) {
          container.removeChild(container.firstChild);
        }
        // Also clear innerHTML as backup
        container.innerHTML = '';
        
        // Remove any HiPay-related elements that might be attached
        const hipayElements = container.querySelectorAll('[class*="hipay"], [id*="hipay"], [data-hipay]');
        hipayElements.forEach((element) => {
          if (element.parentNode) {
            element.parentNode.removeChild(element);
          }
        });
      } catch (e) {
        console.error('Error clearing container:', container.id, e.message);
      }
    });
    
    // Also remove any HiPay elements that might be outside the containers
    const allHipayElements = document.querySelectorAll('[class*="hipay"][id*="oney"], [id*="hipay"][id*="oney"], [data-hipay][id*="oney"]');
    allHipayElements.forEach((element) => {
      try {
        if (element.parentNode) {
          element.parentNode.removeChild(element);
        }
      } catch (e) {
        console.error('Error removing HiPay element:', e.message);
      }
    });
  },
  
  /**
   * Clear Oney widget (alias for destroyAllOneyWidgets)
   */
  clearWidget: function() {
    this.destroyAllOneyWidgets();
  },
  
  /**
   * Setup payment method change listener
   */
  setupPaymentMethodChangeListener: function(methodName) {
    // Listen for radio button changes
    document.addEventListener('change', (event) => {
      if (event.target.name === 'payment-option' && event.target.checked) {
        // Check if this is the specified payment method
        if (event.target.getAttribute('data-module-name') === 'local_payment_hipay') {
          const formId = event.target.id.replace('payment-option-', '');
          const form = document.getElementById('pay-with-payment-option-' + formId + '-form');
          
          if (form) {
            const formElement = form.querySelector('form[id*="-hipay"]');
            if (formElement) {
              const action = formElement.getAttribute('action');
              if (action && action.includes('method=' + methodName)) {
                setTimeout(() => {
                  this.init(methodName);
                }, 200);
              } else {
                this.clearWidget();
              }
            }
          }
        }
      }
    });
  },
  
  /**
   * Setup PrestaShop event listeners
   */
  setupPrestaShopListeners: function(methodName) {
    if (typeof prestashop !== 'undefined' && prestashop && typeof prestashop.on === 'function') {
      prestashop.on('updatedPaymentForm', (event) => {
        // Debounce the setup to prevent multiple calls
        if (this.setupTimeout) {
          clearTimeout(this.setupTimeout);
        }
        this.setupTimeout = setTimeout(() => {
          this.setupPaymentMethodChangeListener(methodName);
        }, 100);
      });
    }
  }
};
