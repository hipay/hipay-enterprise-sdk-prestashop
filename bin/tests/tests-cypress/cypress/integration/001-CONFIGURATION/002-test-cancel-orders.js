/**
 * Functionality tested
 *  - Cancel order on prestashops cancels transaction on BO
 */
var utils = require('../../support/utils');
import cardDatas from '@hipay/hipay-cypress-utils/fixtures/payment-means/card.json';

describe('Order cancellation', function () {
    beforeEach(function () {
        this.cards = cardDatas;
        cy.fixture('notification').as("notification");
        let customerFixture = "customerFR";
        cy.fixture(customerFixture).as("customer");
    });

    it('Test cancel on an order without transaction number', function () {
        cy.logToAdmin();
        cy.deleteClients();
        cy.changeProductStock(1, 300);
        cy.changeProductStock(6, 300);
        cy.changeProductStock(13, 300);
        cy.goToHipayModuleAdmin().goToHipayModulePaymentMethodAdmin().setCaptureMode('automatic');
        cy.adminLogOut();

        cy.selectShirtItem(5);
        cy.selectMugItem(3);
        cy.selectVirtualItem(2);

        cy.register();

        cy.goToCart();

        cy.fillShippingForm();
        cy.selectShippingForm(undefined);

        cy.get('input[data-module-name="credit_card"]').click({force: true});

        cy.get('#card-number').type(this.cards.visa.ok.cardNumber);
        cy.get('#expiry-month').select(this.cards.visa.ok.expiryMonth);
        cy.get('#expiry-year').select(this.cards.visa.ok.expiryYear.substr(2));
        cy.get('#cvc').type(this.cards.visa.ok.cvc);

        cy.get('#conditions-to-approve input').click({force: true});
        cy.get('#payment-confirmation button').click({force: true});

        cy.checkOrderSuccess();

        cy.window().then((win) => {
            let idOrder = new URL(win.location.href).searchParams.get('id_order');

            cy.logAndGoToDetailOrder(idOrder);
            cy.get('#id_order_state').select('Annulé', {force: true});
            cy.get('#submit_state').click();

            cy.get('.message-item-text').then(($msgText) => {
                expect($msgText.text()).to.match(/La transaction HiPay n'a pas été annulée car il n'y a pas de numéro de transaction. Vous pouvez consulter et directement annuler la transaction depuis le back-office HiPay \(https:\/\/merchant.hipay-tpp.com\/default\/auth\/login\)/);
            });
        });
    });

    it('Test cancel on a captured order', function () {
        cy.logToAdmin();
        cy.deleteClients();
        cy.changeProductStock(1, 300);
        cy.changeProductStock(6, 300);
        cy.changeProductStock(13, 300);
        cy.goToHipayModuleAdmin().goToHipayModulePaymentMethodAdmin().setCaptureMode('automatic');
        cy.adminLogOut();

        cy.selectShirtItem(5);
        cy.selectMugItem(3);
        cy.selectVirtualItem(2);

        cy.register();

        cy.goToCart();

        cy.fillShippingForm();
        cy.selectShippingForm(undefined);

        cy.get('input[data-module-name="credit_card"]').click({force: true});

        cy.get('#card-number').type(this.cards.visa.ok.cardNumber);
        cy.get('#expiry-month').select(this.cards.visa.ok.expiryMonth);
        cy.get('#expiry-year').select(this.cards.visa.ok.expiryYear.substr(2));
        cy.get('#cvc').type(this.cards.visa.ok.cvc);

        cy.get('#conditions-to-approve input').click({force: true});
        cy.get('#payment-confirmation button').click({force: true});

        cy.checkOrderSuccess();
        cy.saveLastOrderId();

        cy.window().then((win) => {
            let idOrder = new URL(win.location.href).searchParams.get('id_order');

            cy.connectOnHipayBOFromAnotherDomain();
            cy.openTransactionOnHipayBO(idOrder + "(");
            cy.openNotificationOnHipayBO(116).then(() => {
                cy.sendNotification(this.notification.url, {data: this.data, hash: this.hash}).then(() => {
                    cy.logAndGoToDetailOrder(idOrder);
                    cy.get('#id_order_state').select('Annulé', {force: true});
                    cy.get('#submit_state').click();

                    cy.get('.message-item-text').then(($msgText) => {
                        expect($msgText.text()).to.match(/Une erreur est survenue lors de l'annulation de la transaction HiPay. Vous pouvez consulter et directement annuler la transaction depuis le back-office HiPay \(https:\/\/merchant.hipay-tpp.com\/default\/auth\/login\)\nLe message d'erreur est : \[Action denied : Wrong transaction status]/);
                    });
                });
            });

        });
    });

    it('Test cancel on a cancellable order', function () {
        cy.logToAdmin();
        cy.deleteClients();
        cy.changeProductStock(1, 300);
        cy.changeProductStock(6, 300);
        cy.changeProductStock(13, 300);
        cy.goToHipayModuleAdmin().goToHipayModulePaymentMethodAdmin().setCaptureMode('manual');
        cy.adminLogOut();

        cy.selectShirtItem(5);
        cy.selectMugItem(3);
        cy.selectVirtualItem(2);

        cy.register();

        cy.goToCart();

        cy.fillShippingForm();
        cy.selectShippingForm(undefined);

        cy.get('input[data-module-name="credit_card"]').click({force: true});

        cy.get('#card-number').type(this.cards.visa.ok.cardNumber);
        cy.get('#expiry-month').select(this.cards.visa.ok.expiryMonth);
        cy.get('#expiry-year').select(this.cards.visa.ok.expiryYear.substr(2));
        cy.get('#cvc').type(this.cards.visa.ok.cvc);

        cy.get('#conditions-to-approve input').click({force: true});
        cy.get('#payment-confirmation button').click({force: true});

        cy.checkOrderSuccess();
        cy.saveLastOrderId();

        cy.window().then((win) => {
            let idOrder = new URL(win.location.href).searchParams.get('id_order');

            cy.connectOnHipayBOFromAnotherDomain();
            cy.openTransactionOnHipayBO(idOrder + "(");
            cy.openNotificationOnHipayBO(116).then(() => {
                cy.sendNotification(this.notification.url, {data: this.data, hash: this.hash}).then(() => {
                    cy.logAndGoToDetailOrder(idOrder);
                    cy.get('#id_order_state').select('Annulé', {force: true});
                    cy.get('#submit_state').click();
                    cy.adminLogOut();

                    cy.connectOnHipayBOFromAnotherDomain();
                    cy.openTransactionOnHipayBO(idOrder + "(");

                    cy.get('#data-status-message').then(($msgText) => {
                        expect($msgText.text()).to.match(/Authorization cancellation requested/);
                    });

                    cy.logToAdmin();
                    cy.goToHipayModuleAdmin().goToHipayModulePaymentMethodAdmin().setCaptureMode('automatic');
                    cy.adminLogOut();
                });
            });
        });

    });
});
