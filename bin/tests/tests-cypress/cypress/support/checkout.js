/**
 * GO to Home
 */
Cypress.Commands.add("goToFront", () => {
    cy.visit('/');
});

Cypress.Commands.add("goToCart", () => {
    cy.visit('/index.php?controller=cart&action=show');
    cy.get('.cart-summary .checkout a').click();
});

/**
 * Select just an item (Album) and add it to the cart
 */
Cypress.Commands.add("selectItemAndGoToCart", (qty) => {
    cy.selectShirtItem(qty);
    cy.goToCart();
});

Cypress.Commands.add("selectShirtItem", (qty) => {
    cy.selectItem(
        '/index.php?id_product=1&rewrite=hummingbird-printed-t-shirt&controller=product#/1-taille-s/8-couleur-blanc',
        qty
    );
});

Cypress.Commands.add("selectMugItem", (qty) => {
    cy.selectItem(
        '/index.php?id_product=6&rewrite=mug-the-best-is-yet-to-come&controller=product',
        qty
    );
});

Cypress.Commands.add("selectVirtualItem", (qty) => {
    cy.selectItem(
        '/index.php?id_product=13&rewrite=illustration-vectorielle-ours-brun&controller=product',
        qty
    );
});


Cypress.Commands.add("selectItem", (url, qty) => {

    cy.server();
    cy.route('POST', "/index.php?fc=module&module=ps_shoppingcart&controller=ajax").as("addToCart");

    cy.visit(url);

    if (qty !== undefined) {
        cy.get("#quantity_wanted").clear({force: true});
        cy.get("#quantity_wanted").type(qty);
    }

    cy.get('button[data-button-action="add-to-cart"]').click();

    cy.wait("@addToCart");
});


/**
 * Fill Billing from in checkout
 */
Cypress.Commands.add("fillBillingForm", (country) => {

    let customerFixture = "customerFR";

    if (country !== undefined) {
        customerFixture = "customer" + country
    }

    cy.fixture(customerFixture).then((customer) => {
        cy.get('#customer-form input[name="firstname"]').type(customer.firstName);
        cy.get('#customer-form input[name="lastname"]').type(customer.lastName);
        cy.get('#customer-form input[name="email"]').type(customer.email);
        cy.get("#customer-form input[name='psgdpr']").click();

        cy.get('#customer-form > .form-footer > .continue').click();
    });
});

/**
 * Fill Shipping from in checkout
 */
Cypress.Commands.add("fillShippingForm", (country) => {

    let customerFixture = "customerFR";

    if (country !== undefined) {
        customerFixture = "customer" + country
    }

    cy.fixture(customerFixture).then((customer) => {
        cy.get('#delivery-address input[name="address1"]').type(customer.streetAddress);
        cy.get('#delivery-address input[name="postcode"]').type(customer.zipCode);
        cy.get('#delivery-address input[name="city"]').type(customer.city);
        cy.get('#delivery-address select[name="id_country"]').select(customer.country, {force: true});
        if (customer.state !== undefined) {
            cy.get('#billing_state').select(customer.state, {force: true});
        }


        cy.get('#delivery-address .form-footer > .continue').click();
    });
});

/**
 * Fill Shipping from in checkout
 */
Cypress.Commands.add("selectShippingForm", (wrappingGift) => {
    cy.get('#delivery_option_1').click({force: true});

    if(wrappingGift !== undefined){
        cy.get('#input_gift').click();
    }

    cy.get('#js-delivery > .continue').click();
});

/**
 * Check page for redirection sucess
 */
Cypress.Commands.add("checkOrderSuccess", () => {
    cy.location('search', {timeout: 50000}).should('include', '?controller=order-confirmation');
});

Cypress.Commands.add("saveLastOrderId", () => {

    cy.window().then((win) => {
        let idCart = new URL(win.location.href).searchParams.get('id_cart');
        let idOrder = new URL(win.location.href).searchParams.get('id_order');

        if (idCart !== undefined && idOrder !== undefined) {
            cy.fixture('order').then((order) => {
                order.lastOrderId = idOrder;
                order.lastOrderCartId = idCart;
                cy.writeFile('cypress/fixtures/order.json', order);
            });
        }

    })
});

/**
 * Check Authorization
 */
Cypress.Commands.add("checkAuthorizationStatusMessage", () => {
    cy.get(".message-item").contains("Statut HiPay :116");
    cy.get("#id_order_state_chosen span").contains("Paiement autorisé (HiPay)");
});

/**
 * Check Capture
 */
Cypress.Commands.add("checkCaptureStatusMessage", () => {
    cy.get(".message-item").contains("Statut HiPay :118");
    cy.get("#id_order_state_chosen span").contains("Paiement accepté");
});

/**
 * Check Refund
 */
Cypress.Commands.add("checkRefundStatusMessage", () => {
    cy.get(".message-item").contains("Statut HiPay :124");
    cy.get("#id_order_state_chosen span").contains("Remboursement demandé (HiPay)");
});

/**
 * Register account
 */
Cypress.Commands.add("register", (customerLang) => {
    let customerFixture = "customerFR";

    if(customerLang != undefined){
        customerFixture = "customer" + customerLang;
    }

    cy.fixture(customerFixture).then((customer) => {
        cy.visit("/index.php?controller=authentication&create_account=1");
        cy.get("#customer-form input[name='firstname']").type(customer.firstName);
        cy.get("#customer-form input[name='lastname']").type(customer.lastName);
        cy.get("#customer-form input[name='email']").type(customer.email);
        cy.get("#customer-form input[name='password']").type(customer.password);
        cy.get("#customer-form input[name='psgdpr']").click();
        cy.get("#customer-form button[data-link-action='save-customer']").click();
    });
});