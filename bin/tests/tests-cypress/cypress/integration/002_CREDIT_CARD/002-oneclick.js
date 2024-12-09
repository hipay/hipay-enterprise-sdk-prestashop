/**
 * Functionality tested
 *  - Oneclick card registration
 */
var utils = require('../../support/utils');
import cardDatas from '@hipay/hipay-cypress-utils/fixtures/payment-means/card.json';
describe('Oneclick card registration', function () {
  beforeEach(function () {
    this.cards = cardDatas;
    cy.fixture('notification').as('notification');
    let customerFixture = 'customerFR';
    cy.fixture(customerFixture).as('customer');
  });

  it('Makes an authenticated order with one-click', function () {
    cy.logToAdmin();
    cy.goToHipayModuleAdmin();
    cy.goToHipayModulePaymentMethodAdmin();
    cy.activateOneClick();

    cy.deleteClients();

    cy.changeProductStock(1, 300);
    cy.adminLogOut();

    cy.selectShirtItem(5);
    cy.register();

    cy.goToCart();

    cy.fillShippingForm();

    cy.get(
      '#checkout-addresses-step > h1:nth-child(1) > span:nth-child(3)'
    ).click();
    cy.get('*[data-link-action="different-invoice-address"]').click();
    cy.get('#invoice-address input[name="address1"]').type(
      this.customer.streetAddress + '1'
    );
    cy.get('#invoice-address input[name="postcode"]').type(
      this.customer.zipCode
    );
    cy.get('#invoice-address input[name="city"]').type(this.customer.city);
    cy.get('#invoice-address select[name="id_country"]').select(
      this.customer.country,
      { force: true }
    );
    if (this.customer.state !== undefined) {
      cy.get('#billing_state').select(this.customer.state, { force: true });
    }
    cy.get('#invoice-address .form-footer > .continue').click();

    cy.selectShippingForm(undefined);

    cy.get('input[data-module-name="credit_card"]').click({ force: true });

    cy.get('#card-number').type(this.cards.visa.ok.cardNumber);
    cy.get('#expiry-month').select(this.cards.visa.ok.expiryMonth);
    cy.get('#expiry-year').select(this.cards.visa.ok.expiryYear.substr(2));
    cy.get('#cvc').type(this.cards.visa.ok.cvc);

    cy.get('#conditions-to-approve input').click({ force: true });
    cy.get('#payment-confirmation button').click({ force: true });

    cy.checkOrderSuccess();
    cy.saveLastOrderId();
  });

  it('Connect to BO stage and send authorization', function () {
    cy.fixture('order').then((order) => {
      // Send 116 notif to save card
      cy.connectAndSelectAccountOnHipayBO();

      cy.openTransactionOnHipayBO(order.lastOrderCartId + '(');
      cy.openNotificationOnHipayBO(116).then(() => {
        cy.sendNotification(this.notification.url, {
          data: this.data,
          hash: this.hash
        });
      });
    });
  });

  it('Starts an authenticated order and verifies card is saved', function () {
    cy.logToAdmin();
    cy.goToHipayModuleAdmin();
    cy.goToHipayModulePaymentMethodAdmin();
    cy.activateOneClick();

    cy.changeProductStock(1, 300);
    cy.adminLogOut();

    cy.visit('/index.php?controller=authentication&back=my-account');
    cy.get("#login-form input[name='email']").type(this.customer.email);
    cy.get("#login-form input[name='password']").type(this.customer.password);
    cy.get("#login-form button[data-link-action='sign-in']").click();

    cy.selectShirtItem(5);
    cy.goToCart();

    cy.get('*[data-link-action="different-invoice-address"]').click();
    cy.get('*[name="id_address_invoice"]:not(:checked)').click();
    cy.get('#checkout-addresses-step .continue').click();

    cy.selectShippingForm(undefined);

    cy.get('input[data-module-name="credit_card"]').click();
  });

  it('Makes an authenticated order with one-click', function () {
    cy.logToAdmin();
    cy.goToHipayModuleAdmin();
    cy.goToHipayModulePaymentMethodAdmin();
    cy.activateOneClick();

    cy.deleteClients();

    cy.changeProductStock(1, 300);
    cy.adminLogOut();

    cy.selectShirtItem(5);
    cy.register();

    cy.goToCart();

    cy.fillShippingForm();

    cy.get(
      '#checkout-addresses-step > h1:nth-child(1) > span:nth-child(3)'
    ).click();
    cy.get('*[data-link-action="different-invoice-address"]').click();
    cy.get('#invoice-address input[name="address1"]').type(
      this.customer.streetAddress + '1'
    );
    cy.get('#invoice-address input[name="postcode"]').type(
      this.customer.zipCode
    );
    cy.get('#invoice-address input[name="city"]').type(this.customer.city);
    cy.get('#invoice-address select[name="id_country"]').select(
      this.customer.country,
      { force: true }
    );
    if (this.customer.state !== undefined) {
      cy.get('#billing_state').select(this.customer.state, { force: true });
    }
    cy.get('#invoice-address .form-footer > .continue').click();

    cy.selectShippingForm(undefined);

    cy.get('input[data-module-name="credit_card"]').click({ force: true });

    cy.get('#card-number').type(this.cards.visa.ok.cardNumber);
    cy.get('#expiry-month').select(this.cards.visa.ok.expiryMonth);
    cy.get('#expiry-year').select(this.cards.visa.ok.expiryYear.substr(2));
    cy.get('#cvc').type(this.cards.visa.ok.cvc);

    cy.get('#conditions-to-approve input').click({ force: true });
    cy.get('#payment-confirmation button').click({ force: true });

    cy.checkOrderSuccess();
    cy.saveLastOrderId();
  });

  it('Connect to BO stage and send authorization', function () {
    cy.fixture('order').then((order) => {
      // Send 118 notif to save card
      cy.connectAndSelectAccountOnHipayBO();

      cy.openTransactionOnHipayBO(order.lastOrderCartId + '(');
      cy.openNotificationOnHipayBO(118).then(() => {
        cy.sendNotification(this.notification.url, {
          data: this.data,
          hash: this.hash
        });
      });
    });
  });

  it('Starts an authenticated order and verifies card is saved', function () {
    cy.logToAdmin();
    cy.goToHipayModuleAdmin();
    cy.goToHipayModulePaymentMethodAdmin();
    cy.activateOneClick();

    cy.changeProductStock(1, 300);
    cy.adminLogOut();

    cy.visit('/index.php?controller=authentication&back=my-account');
    cy.get("#login-form input[name='email']").type(this.customer.email);
    cy.get("#login-form input[name='password']").type(this.customer.password);
    cy.get("#login-form button[data-link-action='sign-in']").click();

    cy.selectShirtItem(5);
    cy.goToCart();

    cy.get('*[data-link-action="different-invoice-address"]').click();
    cy.get('*[name="id_address_invoice"]:not(:checked)').click();
    cy.get('#checkout-addresses-step .continue').click();

    cy.selectShippingForm(undefined);

    cy.get('input[data-module-name="credit_card"]').click();
  });
});
