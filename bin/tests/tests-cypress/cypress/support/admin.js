/**
 * Log to Prestashop Admin
 */
Cypress.Commands.add("logToAdmin", () => {
    cy.visit('/admin-hipay');
    cy.get('#email').type("demo@hipay.com");
    cy.get('#passwd').type('hipay123');
    cy.get('#submit_login').click();

    cy.get('#header_logo');
});

Cypress.Commands.add("adminLogOut", () => {
    cy.get('#header_logout').click({force: true});
});

Cypress.Commands.add("goToHipayModuleAdmin", () => {
    cy.visit('/admin-hipay/index.php?controller=AdminModules&configure=hipay_enterprise');
    cy.get('.btn-continue').click();
});

Cypress.Commands.add("goToHipayModulePaymentMethodAdmin", () => {
    cy.get('a[aria-controls="payment_form"]').click();
});

Cypress.Commands.add("resetProductionConfigForm", () => {
    cy.get('input[name="api_username_production"]').clear({force: true});
    cy.get('input[name="api_password_production"]').clear({force: true});
    cy.get('input[name="api_secret_passphrase_production"]').clear({force: true});
    cy.get('input[name="api_tokenjs_username_production"]').clear({force: true});
    cy.get('input[name="api_tokenjs_password_publickey_production"]').clear({force: true});
    cy.get('input[name="api_moto_username_production"]').clear({force: true});
    cy.get('input[name="api_moto_password_production"]').clear({force: true});
    cy.get('input[name="api_moto_secret_passphrase_production"]').clear({force: true});
});


/**
 *  Activate Payment Methods
 */
Cypress.Commands.add("activateLocalPaymentMethods", (method) => {
    cy.get('#' + method + '_activated_on').then(($input) => {
        if ($input.attr('checked') === undefined) {
            $input.click();
        }
    });
    cy.get('#local_payment_form > .panel-footer > .col-md-12 > .pull-right').click();
});

/**
 *  Activate Basket
 */
Cypress.Commands.add("activateBasket", () => {
    cy.get('#activate_basket_switchmode_on').then(($input) => {
        if ($input.attr('checked') === undefined) {
            $input.click();
        }
    });
});

Cypress.Commands.add("importCountry", (country) => {
    cy.visit('/admin-hipay/index.php/improve/international/localization/');
    cy.get('.btn-outline-danger').click();

    cy.get("#import_localization_pack_iso_localization_pack").select(country, {force: true});
    cy.get("form[name='import_localization_pack'] .card-footer .btn").click();
});

/**
 *  Connect and go to detail of an order
 */
Cypress.Commands.add("logAndGoToDetailOrder", (lastOrderId) => {
    cy.logToAdmin();
    cy.goToDetailOrder(lastOrderId);
});

/**
 * Go to Order detail
 */
Cypress.Commands.add("goToDetailOrder", (id) => {
    cy.visit('/admin-hipay/index.php?controller=AdminOrders&id_order=' + id + '&vieworder');
    cy.get('.btn-continue').click();
});

Cypress.Commands.add("activateWrappingGift", () => {
    cy.visit('/admin-hipay/index.php/configure/shop/order-preferences/');
    cy.get('.btn-outline-danger').click();

    cy.get('#form_gift_options_enable_gift_wrapping_1').then(($input) => {
        if ($input.attr('checked') === undefined) {
            $input.click();
        }
    });

    cy.setWrappingGiftPrice(10);

    cy.get(':nth-child(1) > .card > .card-footer > .d-flex > .btn').click();
});

Cypress.Commands.add("setWrappingGiftPrice", (price) => {
    cy.get("#form_gift_options_gift_wrapping_price").clear();
    cy.get("#form_gift_options_gift_wrapping_price").type(price);
});
