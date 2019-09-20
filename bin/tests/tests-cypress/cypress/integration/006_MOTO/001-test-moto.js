/**
 * Functionality tested
 *  - Can create and pay order via MOTO
 *  - Payment outputs error when MOTO credentials are not set
 *
 */
var utils = require('../../support/utils');
import cardDatas from '@hipay/hipay-cypress-utils/fixtures/payment-means/card.json';

describe('Tests basic MOTO', function () {

    beforeEach(function () {
        this.cards = cardDatas;
        cy.fixture('order').as("order");
        cy.fixture('basket').as("basket");
        cy.fixture('notification').as("notification");
    });

    /**
     * Process an payment with MOTO ( Transaction should be OK )
     */
    it('Succeed Transaction', function () {
        cy.logToAdmin();
        cy.goToHipayModuleAdmin();
        cy.deleteClients();

        cy.visit("/admin-hipay/index.php?controller=AdminOrders&addorder");
        cy.get('.btn-continue').click();

        cy.fixture('customerFR').then((customer) => {
            cy.get('#search-customer-form-group a.fancybox_customer').click();

            cy.wait(5000).get(".fancybox-inner").find("iframe").then(($iframe) => {
                // Get iframe content
                const content = $iframe.contents();
                // Get input
                let inputFirstName = content.find("#customer_first_name")[0];
                let inputLastName = content.find("#customer_last_name")[0];
                let inputEmail = content.find("#customer_email")[0];
                let inputPassword = content.find("#customer_password")[0];
                let btnSave = content.find("div.card-footer button.btn:nth-child(2)")[0];

                // Click on input and fill it with value, then blur
                cy.wrap(inputFirstName).click().type(customer.firstName).blur({force: true});
                cy.wrap(inputLastName).click().type(customer.lastName).blur({force: true});
                cy.wrap(inputEmail).click().type(customer.email).blur({force: true});
                cy.wrap(inputPassword).click().type(customer.password).blur({force: true});
                cy.wrap(btnSave).click();

            }).then(() => {
                cy.get('#product').type('t-shirt');
                cy.get('#submitAddProduct').click();

                cy.get('#new_address').click();

                cy.wait(5000).get(".fancybox-inner").find("iframe").then(($iframe) => {
                    // Get iframe content
                    const content = $iframe.contents();
                    // Get input
                    let inputAlias = content.find("#alias")[0];
                    let inputAddress = content.find("#address1")[0];
                    let inputPostCode = content.find("#postcode")[0];
                    let inputCity = content.find("#city")[0];
                    let btnSave = content.find("#address_form_submit_btn")[0];

                    // Click on input and fill it with value, then blur
                    cy.wrap(inputAlias).click().type(customer.streetAddress).blur({force: true});
                    cy.wrap(inputAddress).click().type(customer.streetAddress).blur({force: true});
                    cy.wrap(inputPostCode).click().type(customer.zipCode).blur({force: true});
                    cy.wrap(inputCity).click().type(customer.city).blur({force: true});
                    cy.wrap(btnSave).click();
                }).then(() => {
                    cy.get('#payment_module_name').select('hipay_enterprise');
                    cy.get('#id_order_state').select('En attente de paiement MO/TO (HiPay)');

                    cy.get('#order_submit_btn').click();

                    cy.get('button[name="motoPayment"]').click();

                    cy.fill_hostedfields_input('#cardNumber', this.cards.visa.ok.cardNumber);
                    cy.fill_hostedfields_input('#cardExpiryDate', this.cards.visa.ok.expiryMonth + this.cards.visa.ok.expiryYear.substr(2));
                    cy.fill_hostedfields_input('#cardSecurityCode', this.cards.visa.ok.cvc);

                    cy.get('#submit-button').click();

                    cy.get('#status table tr:nth-child(1) td:nth-child(2)').then(($span) => {
                        expect($span.text()).to.match(/En attente d'autorisation \(HiPay\)/);
                    });
                });
            });
        });
    });
});