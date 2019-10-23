/**
 * Functionality tested
 *  - Multibanco payment
 */
var utils = require('../../support/utils');
import cardDatas from '@hipay/hipay-cypress-utils/fixtures/payment-means/card.json';
describe('Oneclick card registration', function () {
    beforeEach(function () {
        this.cards = cardDatas;
        let customerFixture = "customerPT";
        cy.fixture(customerFixture).as("customer");
    });

    it('Makes an non authenticated order via multibanco with 3 days expiration', function () {
        cy.logToAdmin();
        cy.goToHipayModuleAdmin();
        cy.goToHipayModulePaymentMethodAdmin();
        cy.activateLocalPaymentMethods('multibanco');
        cy.configurePaymentMethods('multibanco', 'orderExpirationTime', '3');
        cy.activateCountry(this.customer.country);

        cy.deleteClients();

        cy.changeProductStock(1, 300);
        cy.adminLogOut();

        cy.selectShirtItem(5);
        cy.register();

        cy.goToCart();

        cy.fillShippingForm('PT');

        cy.selectShippingForm(undefined);

        cy.get('label').contains("Payer par Multibanco").click({force: true});

        cy.get('#conditions-to-approve input').click({force: true});
        cy.get('#payment-confirmation button').click({force: true});


        let d = new Date();

        let expDate = new Date(d.setDate(d.getDate() + 3));
        let expDateStr = (expDate.getDate() < 10 ? "0" : "") + (expDate.getDate()) + "/" + (expDate.getMonth() < 9 ? "0" : "") +
            (expDate.getMonth() + 1) + "/" + expDate.getFullYear();

        cy.get('#comprafacil-logo-multibanco').then(($p) => {
            expect($p).to.exist;
        });

        cy.get('.reference-content > div:nth-child(5)').then(($div) => {
            expect($div.text()).to.contain(expDateStr);
        });

        cy.get('a.btn:nth-child(1)').click();

        cy.checkOrderSuccess();
    });

    it('Makes an non authenticated order via multibanco with 30 days expiration', function () {
        cy.logToAdmin();
        cy.goToHipayModuleAdmin();
        cy.goToHipayModulePaymentMethodAdmin();
        cy.activateLocalPaymentMethods('multibanco');
        cy.configurePaymentMethods('multibanco', 'orderExpirationTime', '30');
        cy.activateCountry(this.customer.country);

        cy.deleteClients();

        cy.changeProductStock(1, 300);
        cy.adminLogOut();

        cy.selectShirtItem(5);
        cy.register();

        cy.goToCart();

        cy.fillShippingForm('PT');

        cy.selectShippingForm(undefined);

        cy.get('label').contains("Payer par Multibanco").click({force: true});

        cy.get('#conditions-to-approve input').click({force: true});
        cy.get('#payment-confirmation button').click({force: true});


        let d = new Date();

        let expDate = new Date(d.setDate(d.getDate() + 30));
        let expDateStr = (expDate.getDate() < 10 ? "0" : "") + (expDate.getDate()) + "/" + (expDate.getMonth() < 9 ? "0" : "") +
            (expDate.getMonth() + 1) + "/" + expDate.getFullYear();

        cy.get('#comprafacil-logo-multibanco').then(($p) => {
            expect($p).to.exist;
        });

        cy.get('.reference-content > div:nth-child(5)').then(($div) => {
            expect($div.text()).to.contain(expDateStr);
        });

        cy.get('a.btn:nth-child(1)').click();

        cy.checkOrderSuccess();
    });

    it('Makes an non authenticated order via multibanco with 90 days expiration', function () {
        cy.logToAdmin();
        cy.goToHipayModuleAdmin();
        cy.goToHipayModulePaymentMethodAdmin();
        cy.activateLocalPaymentMethods('multibanco');
        cy.configurePaymentMethods('multibanco', 'orderExpirationTime', '90');
        cy.activateCountry(this.customer.country);

        cy.deleteClients();

        cy.changeProductStock(1, 300);
        cy.adminLogOut();

        cy.selectShirtItem(5);
        cy.register();

        cy.goToCart();

        cy.fillShippingForm('PT');

        cy.selectShippingForm(undefined);

        cy.get('label').contains("Payer par Multibanco").click({force: true});

        cy.get('#conditions-to-approve input').click({force: true});
        cy.get('#payment-confirmation button').click({force: true});


        let d = new Date();

        let expDate = new Date(d.setDate(d.getDate() + 90));
        let expDateStr = (expDate.getDate() < 10 ? "0" : "") + (expDate.getDate()) + "/" + (expDate.getMonth() < 9 ? "0" : "") +
            (expDate.getMonth() + 1) + "/" + expDate.getFullYear();

        cy.get('#comprafacil-logo-multibanco').then(($p) => {
            expect($p).to.exist;
        });

        cy.get('.reference-content > div:nth-child(5)').then(($div) => {
            expect($div.text()).to.contain(expDateStr);
        });

        cy.get('a.btn:nth-child(1)').click();

        cy.checkOrderSuccess();
    });
});